<?php
require_once __DIR__ . '/../Conexion/Conexion.php';

class Actividad
{
    private $conn;
    /** Evita repetir la sincronización de estados varias veces en la misma petición. */
    private static $estadosSincronizados = false;

    public function __construct()
    {
        $this->conn = (new Conexion())->getConnection();
    }

    public function obtenerActividades($tipo = null, $orden = null)
    {
        $this->sincronizarEstadosActividades();
        if ($tipo) {
            $stmt = $this->conn->prepare("SELECT * FROM actividades WHERE TipoActividad = ? AND EstActividad <> 'Eliminado'");
            $stmt->bind_param("s", $tipo);
        } else {
            $stmt = $this->conn->prepare("SELECT * FROM actividades WHERE EstActividad <> 'Eliminado'");
        }
        $stmt->execute();
        $actividades = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $this->liberarResultadosPendientes();
        $actividades = $this->adjuntarImagenesExtras($actividades);
        return self::ordenarPorPublicacion($actividades, $orden);
    }

    /**
     * El estado de la actividad ya no se elige a mano: depende de su fecha.
     * Si la fecha ya pasó queda "Concluido"; si aún no pasa (hoy o futura), "Activo".
     */
    private function sincronizarEstadosActividades()
    {
        if (self::$estadosSincronizados) {
            return;
        }
        self::$estadosSincronizados = true;
        $hoy = (new DateTimeImmutable('now', new DateTimeZone('America/La_Paz')))->format('Y-m-d');
        $stmt = $this->conn->prepare("UPDATE actividades SET EstActividad = 'Concluido'
            WHERE FechaActividad < ? AND EstActividad <> 'Concluido' AND EstActividad <> 'Eliminado'");
        $stmt->bind_param('s', $hoy);
        try {
            $stmt->execute();
        } finally {
            $stmt->close();
        }
        $stmt = $this->conn->prepare("UPDATE actividades SET EstActividad = 'Activo'
            WHERE FechaActividad >= ? AND EstActividad <> 'Activo' AND EstActividad <> 'Eliminado'");
        $stmt->bind_param('s', $hoy);
        try {
            $stmt->execute();
        } finally {
            $stmt->close();
        }
    }

    /**
     * Agrega a cada actividad sus imágenes adicionales (2 a 7):
     * 'ImagenesExtras' con los nombres e 'ImagenesExtrasFilas' con idActImg incluido.
     */
    private function adjuntarImagenesExtras(array $actividades)
    {
        if (!$actividades) {
            return $actividades;
        }
        $filasPorActividad = [];
        $resultado = $this->conn->query("SELECT idActImg, idActividad, ImagenActividad
            FROM actividad_imagenes ORDER BY idActividad, orden, idActImg");
        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $filasPorActividad[(int) $fila['idActividad']][] = $fila;
            }
        }
        foreach ($actividades as $indice => $actividad) {
            $filas = $filasPorActividad[(int) $actividad['idActividad']] ?? [];
            $actividades[$indice]['ImagenesExtrasFilas'] = $filas;
            $actividades[$indice]['ImagenesExtras'] = array_column($filas, 'ImagenActividad');
        }
        return $actividades;
    }

    /**
     * Ordena por fecha de publicación: 'publicacion_asc' (más antigua primero)
     * o 'publicacion_desc' (más reciente primero).
     */
    private static function ordenarPorPublicacion(array $actividades, $orden)
    {
        if ($orden !== 'publicacion_asc' && $orden !== 'publicacion_desc') {
            return $actividades;
        }
        $signo = $orden === 'publicacion_asc' ? 1 : -1;
        usort($actividades, function ($a, $b) use ($signo) {
            $cmp = strcmp((string) ($a['Fecha_Public'] ?? ''), (string) ($b['Fecha_Public'] ?? ''));
            if ($cmp === 0) {
                $cmp = (int) $a['idActividad'] <=> (int) $b['idActividad'];
            }
            return $cmp * $signo;
        });
        return $actividades;
    }

    public function registrarActividad($data)
    {
        $stmt = $this->conn->prepare("CALL InsertarActividad(?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param(
            "sssssssss",
            $data['TituloActividad'],
            $data['FechaActividad'],
            $data['DetalleActividad'],
            $data['ImagenActividad'],
            $data['VideoActividad'],
            $data['LugarAct'],
            $data['HoraAct'],
            $data['CiCat'],
            $data['TipoActividad']
        );

        $this->conn->begin_transaction();
        try {
            try {
                $stmt->execute();
            } finally {
                $stmt->close();
            }
            $this->liberarResultadosPendientes();
            $idActividad = $this->ultimoIdInsertado();
            $this->guardarVideoUrl($idActividad, $data['VideoUrl'] ?? null);
            $this->insertarImagenesExtras($idActividad, $data['ImagenesExtras'] ?? []);
            $this->conn->commit();
            return $idActividad;
        } catch (Throwable $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    /** Guarda el enlace del video; el procedimiento de alta no lo recibe. */
    private function guardarVideoUrl($idActividad, $videoUrl)
    {
        if ($videoUrl === null || $videoUrl === '') {
            return;
        }
        $stmt = $this->conn->prepare("UPDATE actividades SET VideoUrl = ? WHERE idActividad = ?");
        $stmt->bind_param("si", $videoUrl, $idActividad);
        try {
            $stmt->execute();
        } finally {
            $stmt->close();
        }
    }

    /** id de la última fila insertada en esta conexión (también tras un procedimiento). */    private function ultimoIdInsertado()
    {
        $resultado = $this->conn->query("SELECT LAST_INSERT_ID() AS id");
        $fila = $resultado ? $resultado->fetch_assoc() : null;
        $id = (int) ($fila['id'] ?? 0);
        if ($id <= 0) {
            throw new RuntimeException("No se pudo identificar la actividad guardada.");
        }
        return $id;
    }

    private function liberarResultadosPendientes()
    {
        while ($this->conn->more_results() && $this->conn->next_result()) {
            if ($resultado = $this->conn->store_result()) {
                $resultado->free();
            }
        }
    }

    /** Registra las imágenes adicionales (2 a 7) de una actividad. Devuelve cuántas guardó. */
    public function insertarImagenesExtras($idActividad, array $imagenes, $ordenInicial = 2)
    {
        $imagenes = array_values(array_filter($imagenes, static function ($nombre) {
            return is_string($nombre) && $nombre !== '' && $nombre !== 'Ninguno';
        }));
        if (!$imagenes || $idActividad <= 0) {
            return 0;
        }
        $stmt = $this->conn->prepare("INSERT INTO actividad_imagenes (idActividad, ImagenActividad, orden)
            VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $idActividad, $nombre, $orden);
        try {
            foreach ($imagenes as $indice => $nombre) {
                $orden = $ordenInicial + $indice;
                if (!$stmt->execute()) {
                    throw new Exception("Error al guardar las imágenes adicionales: " . $stmt->error);
                }
            }
        } finally {
            $stmt->close();
        }
        return count($imagenes);
    }

    /** Filas (idActImg + nombre) de las imágenes adicionales de una actividad. */
    public function imagenesExtras($idActividad)
    {
        $stmt = $this->conn->prepare("SELECT idActImg, ImagenActividad FROM actividad_imagenes
            WHERE idActividad = ? ORDER BY orden, idActImg");
        $stmt->bind_param("i", $idActividad);
        $stmt->execute();
        $filas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $filas;
    }

    /** Elimina imágenes adicionales; sin lista elimina todas las de la actividad. */
    public function eliminarImagenesExtras($idActividad, array $idsActImg = null)
    {
        if ($idsActImg === null) {
            $stmt = $this->conn->prepare("DELETE FROM actividad_imagenes WHERE idActividad = ?");
            $stmt->bind_param("i", $idActividad);
        } else {
            $idsActImg = array_values(array_unique(array_filter(array_map('intval', $idsActImg))));
            if (!$idsActImg) {
                return 0;
            }
            $marcadores = implode(',', array_fill(0, count($idsActImg), '?'));
            $stmt = $this->conn->prepare("DELETE FROM actividad_imagenes WHERE idActividad = ? AND idActImg IN ($marcadores)");
            $parametros = array_merge([(int) $idActividad], $idsActImg);
            $tipos = 'i' . str_repeat('i', count($idsActImg));
            $stmt->bind_param($tipos, ...$parametros);
        }
        try {
            $stmt->execute();
            return $stmt->affected_rows;
        } finally {
            $stmt->close();
        }
    }

    public function puedePublicarParroquia($codPer)
    {
        // Cualquier personal activo con acceso al módulo Inicio puede publicar
        // actividades de Parroquia.
        $stmt = $this->conn->prepare("SELECT p.CodPer FROM `personal` p
            LEFT JOIN permiso_acceso pa ON pa.CodPer = p.CodPer
            WHERE p.CodPer = ? AND p.Estado = 'Activo'
              AND (pa.CodPer IS NULL OR pa.Inicio = 1)");
        $stmt->bind_param("i", $codPer);
        $stmt->execute();
        $permitido = $stmt->get_result()->num_rows === 1;
        $stmt->close();
        return $permitido;
    }

    public function puedeGestionarParroquia($codPer)
    {
        // La edición y eliminación de actividades sigue reservada al Párroco.
        return $this->esParroco($codPer);
    }

    /**
     * Verifica si el personal es Párroco activo.
     */
    public function esParroco($codPer)
    {
        $stmt = $this->conn->prepare("SELECT p.CodPer FROM `personal` p
            INNER JOIN `cargo` c ON c.CodCar = p.CodCar
            WHERE p.CodPer = ? AND p.Estado = 'Activo' AND c.DescripCar = 'Párroco'");
        $stmt->bind_param("i", $codPer);
        $stmt->execute();
        $permitido = $stmt->get_result()->num_rows === 1;
        $stmt->close();
        return $permitido;
    }

    /**
     * Verifica si el usuario puede editar una actividad de Parroquia específica.
     * Puede editar si es Párroco o si es el autor y la actividad está activa.
     */
    public function puedeEditarActividadParroquia($idActividad, $codPer)
    {
        $stmt = $this->conn->prepare("SELECT idActividad FROM actividades
            WHERE idActividad = ? AND TipoActividad = 'Parroquia'
              AND LOWER(TRIM(EstActividad)) = 'activo'
              AND EstActividad <> 'Eliminado'
              AND (CodPer = ? OR EXISTS (
                  SELECT 1 FROM `personal` p INNER JOIN `cargo` c ON c.CodCar = p.CodCar
                  WHERE p.CodPer = ? AND p.Estado = 'Activo' AND c.DescripCar = 'Párroco'
              ))");
        $stmt->bind_param("iii", $idActividad, $codPer, $codPer);
        $stmt->execute();
        $permitido = $stmt->get_result()->num_rows === 1;
        $stmt->close();
        return $permitido;
    }

    /**
     * Verifica si el usuario puede eliminar una actividad de Parroquia específica.
     * Puede eliminar si es Párroco o si es el autor.
     */
    public function puedeEliminarActividadParroquia($idActividad, $codPer)
    {
        $stmt = $this->conn->prepare("SELECT idActividad FROM actividades
            WHERE idActividad = ?
              AND EstActividad <> 'Eliminado'
              AND (CodPer = ? OR EXISTS (
                  SELECT 1 FROM `personal` p INNER JOIN `cargo` c ON c.CodCar = p.CodCar
                  WHERE p.CodPer = ? AND p.Estado = 'Activo' AND c.DescripCar = 'Párroco'
              ))");
        $stmt->bind_param("iii", $idActividad, $codPer, $codPer);
        $stmt->execute();
        $permitido = $stmt->get_result()->num_rows === 1;
        $stmt->close();
        return $permitido;
    }

    public function registrarActividadParroquia($data, $codPer)
    {
        // La categoría y el autor no se toman de los campos del formulario.
        $stmt = $this->conn->prepare("INSERT INTO actividades
            (TituloActividad, FechaActividad, DetalleActividad, ImagenActividad, VideoActividad, VideoUrl,
             LugarAct, HoraAct, CiCat, CodPer, TipoActividad)
            SELECT ?, ?, ?, ?, ?, ?, ?, ?, NULL, p.CodPer, 'Parroquia'
            FROM `personal` p
            LEFT JOIN permiso_acceso pa ON pa.CodPer = p.CodPer
            WHERE p.CodPer = ? AND p.Estado = 'Activo'
              AND (pa.CodPer IS NULL OR pa.Inicio = 1)");
        $stmt->bind_param("ssssssssi", $data['TituloActividad'], $data['FechaActividad'],
            $data['DetalleActividad'], $data['ImagenActividad'], $data['VideoActividad'],
            $data['VideoUrl'], $data['LugarAct'], $data['HoraAct'], $codPer);
        $this->conn->begin_transaction();
        try {
            try {
                $stmt->execute();
                if ($stmt->affected_rows !== 1) {
                    throw new DomainException("No tienes permiso para publicar actividades de Parroquia.");
                }
            } finally {
                $stmt->close();
            }
            $idActividad = $this->ultimoIdInsertado();
            $this->insertarImagenesExtras($idActividad, $data['ImagenesExtras'] ?? []);
            $this->conn->commit();
            return $idActividad;
        } catch (Throwable $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function obtenerActividadPorId($idActividad)
    {
        $this->sincronizarEstadosActividades();
        $stmt = $this->conn->prepare("SELECT * FROM actividades WHERE idActividad = ? AND EstActividad <> 'Eliminado'");
        $stmt->bind_param("i", $idActividad);
        $stmt->execute();
        $actividad = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($actividad) {
            $actividad = $this->adjuntarImagenesExtras([$actividad])[0];
        }
        return $actividad;
    }

    public function eliminarActividad($idActividad)
    {
        $stmt = $this->conn->prepare("UPDATE actividades SET EstActividad = 'Eliminado' WHERE idActividad = ? AND EstActividad <> 'Eliminado'");
        $stmt->bind_param("i", $idActividad);
        try {
            $stmt->execute();
            return $stmt->affected_rows > 0;
        } finally {
            $stmt->close();
        }
    }

    public function actualizarActividadConImagenes($data, $actual, $codPer = null)
    {
        $this->conn->begin_transaction();
        try {
            $id = (int) $actual['idActividad'];
            $stmt = $this->conn->prepare("SELECT idActividad FROM actividades WHERE idActividad = ? AND EstActividad <> 'Eliminado' FOR UPDATE");
            $stmt->bind_param("i", $id);
            try {
                $stmt->execute();
                $stmt->get_result()->free();
            } finally {
                $stmt->close();
            }
            $vigente = $this->obtenerActividadPorId($id);
            if (!$vigente || $vigente['ImagenActividad'] !== $actual['ImagenActividad']
                || $vigente['VideoActividad'] !== $actual['VideoActividad']
                || $vigente['ImagenesExtrasFilas'] !== $actual['ImagenesExtrasFilas']) {
                throw new DomainException("Los archivos de esta actividad cambiaron. Recargue la página antes de guardar.");
            }
            $resultado = $codPer === null ? $this->actualizarActividad($data)
                : $this->actualizarActividadParroquia($data, $codPer);
            if ($vigente['ImagenesExtras'] !== $data['ImagenesExtras']) {
                $this->eliminarImagenesExtras($id);
                $this->insertarImagenesExtras($id, $data['ImagenesExtras']);
            }
            $this->conn->commit();
            return $resultado;
        } catch (Throwable $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function actualizarActividadParroquia($data, $codPer)
    {
        // La condición se comprueba también al escribir: una actividad concluida no se modifica.
        // El Párroco puede editar cualquiera; el autor solo la suya si está activa.
        $condicion = "idActividad = ? AND TipoActividad = 'Parroquia'
            AND LOWER(TRIM(EstActividad)) = 'activo'
            AND EstActividad <> 'Eliminado'
            AND (CodPer = ? OR EXISTS (
                SELECT 1 FROM `personal` p INNER JOIN `cargo` c ON c.CodCar = p.CodCar
                WHERE p.CodPer = ? AND p.Estado = 'Activo' AND c.DescripCar = 'Párroco'
            ))";
        $stmt = $this->conn->prepare("UPDATE actividades SET TituloActividad = ?, FechaActividad = ?,
            DetalleActividad = ?, ImagenActividad = ?, VideoActividad = ?, VideoUrl = ?, LugarAct = ?, HoraAct = ?
            WHERE " . $condicion);
        $stmt->bind_param("ssssssssiii", $data['TituloActividad'], $data['FechaActividad'],
            $data['DetalleActividad'], $data['ImagenActividad'], $data['VideoActividad'],
            $data['VideoUrl'], $data['LugarAct'], $data['HoraAct'], $data['idActividad'], $codPer, $codPer);
        try {
            $stmt->execute();
            $modificada = $stmt->affected_rows > 0;
        } finally {
            $stmt->close();
        }
        if (!$modificada) {
            // Guardar sin cambiar valores es válido; distingue ese caso de un bloqueo.
            $stmt = $this->conn->prepare("SELECT idActividad FROM actividades WHERE " . $condicion);
            $stmt->bind_param("iii", $data['idActividad'], $codPer, $codPer);
            try {
                $stmt->execute();
                if ($stmt->get_result()->num_rows !== 1) {
                    throw new DomainException("La actividad ya no está disponible para editar. Recargue la página.");
                }
            } finally {
                $stmt->close();
            }
        }
        // No se cambian el autor, la categoría, el estado ni la fecha de publicación.
        return true;
    }

    public function actualizarActividad($data)
    {
        // Conserva el autor original, sea catequista o párroco.
        $stmt = $this->conn->prepare("UPDATE actividades SET TituloActividad = ?, FechaActividad = ?,
            DetalleActividad = ?, ImagenActividad = ?, VideoActividad = ?, VideoUrl = ?, LugarAct = ?, HoraAct = ?,
            TipoActividad = ?, EstActividad = ? WHERE idActividad = ? AND EstActividad <> 'Eliminado'");
        $stmt->bind_param("ssssssssssi", $data['TituloActividad'], $data['FechaActividad'],
            $data['DetalleActividad'], $data['ImagenActividad'], $data['VideoActividad'],
            $data['VideoUrl'], $data['LugarAct'], $data['HoraAct'], $data['TipoActividad'], $data['EstActividad'],
            $data['idActividad']);
        try {
            return $stmt->execute();
        } finally {
            $stmt->close();
        }
    }
}
