<?php
require_once '../Conexion/Conexion.php';

class ModeloCoordinadorAdmin {
    private $conn;

    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->getConnection();
    }

    public function obtenerCoordinadores() {
        $stmt = $this->conn->prepare("
            SELECT c.CiCat, c.Sacramento, c.EstadoCat, c.UsuarioCat, c.ClaveCat,
                   p.Nombre, p.ApPaterno, p.ApMaterno,
                   a.RolCat, a.IdGrupo, a.FechaIniCat, a.FechaFinCat,
                   g.NombreGrupo
            FROM catequista c
            INNER JOIN `persona` p ON c.CiCat = p.CiPersona
            INNER JOIN `asignacion` a ON c.CiCat = a.CiCat
            INNER JOIN `grupo` g ON a.IdGrupo = g.IdGrupo
            WHERE a.RolCat = 'Coordinador'
            ORDER BY c.Sacramento, p.ApPaterno
        ");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerGruposPorSacramento($sacramento) {
        $stmt = $this->conn->prepare("
            SELECT g.IdGrupo, g.NombreGrupo
            FROM `grupo` g
            INNER JOIN `asignacion` a ON g.IdGrupo = a.IdGrupo
            WHERE a.RolCat = 'Coordinador' AND a.CiCat IN (
                SELECT CiCat FROM catequista WHERE Sacramento = ?
            )
            GROUP BY g.IdGrupo, g.NombreGrupo
        ");
        $stmt->bind_param("s", $sacramento);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function buscarPersona($ci) {
        $stmt = $this->conn->prepare("
            SELECT p.CiPersona, p.TipoDocumento, p.NumeroDocumento, p.Nacionalidad, p.Nombre, p.ApPaterno, p.ApMaterno, p.Sexo, p.FechaNac, p.Direccion, p.ZonaSector,
                   p.Contacto, p.TelefonoAlternativo, p.Correo, p.Estado_per, p.SacramentoSolicita,
                   a.Documento AS DocumentoBautizo, p.Observaciones
            FROM `persona` p
            LEFT JOIN antecedente_sacramental a ON a.CiPersona = p.CiPersona AND a.Sacramento = 'Bautizo'
            WHERE p.CiPersona = ?
        ");
        $stmt->bind_param("s", $ci);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function verificarCoordinadorExistente($sacramento) {
        $stmt = $this->conn->prepare("
            SELECT c.CiCat
            FROM catequista c
            INNER JOIN `asignacion` a ON c.CiCat = a.CiCat
            WHERE a.RolCat = 'Coordinador' AND c.Sacramento = ?
        ");
        $stmt->bind_param("s", $sacramento);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function registrarCoordinador($ci, $sacramento, $idGrupo, $usuario, $clave) {
        $estado = 'Activo';
        $imagen = 'Ninguno';
        $fondo = 'Ninguno';
        $poder = 'Coordinador';
        $frase = 'La catequesis es como la lluvia que empapa el suelo y hace crecer las semillas.';
        $fecha = date('Y-m-d');
        $fechaFin = null;
        $rol = 'Coordinador';

        try {
            $this->conn->begin_transaction();

            $stmt = $this->conn->prepare("SELECT CiCat FROM catequista WHERE CiCat = ? FOR UPDATE");
            $stmt->bind_param("s", $ci);
            $stmt->execute();
            $yaEsCatequista = $stmt->get_result()->num_rows > 0;
            $stmt->close();

            if ($yaEsCatequista) {
                // Conservar la ficha y el perfil del catequista ya registrado.
                $stmt = $this->conn->prepare("
                    UPDATE catequista
                    SET Sacramento = ?, UsuarioCat = ?, ClaveCat = ?, EstadoCat = ?
                    WHERE CiCat = ?
                ");
                $stmt->bind_param("sssss", $sacramento, $usuario, $clave, $estado, $ci);
                try {
                    if (!$stmt->execute()) {
                        throw new RuntimeException('No se pudo actualizar el catequista.');
                    }
                } finally {
                    $stmt->close();
                }

                $stmt = $this->conn->prepare("
                    INSERT INTO `asignacion` (Gestion, CiCat, IdGrupo, FechaAsigCat, FechaIniCat, FechaFinCat, RolCat)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->bind_param("ssissss", $fecha, $ci, $idGrupo, $fecha, $fecha, $fechaFin, $rol);
            } else {
                $stmt = $this->conn->prepare("CALL RegistrarCatequista(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssssssssissss",
                    $ci, $sacramento, $usuario, $clave,
                    $estado, $imagen, $fondo, $poder, $frase,
                    $fecha, $idGrupo, $fecha, $fecha, $fechaFin, $rol
                );
            }

            try {
                if (!$stmt->execute()) {
                    throw new RuntimeException('No se pudo registrar la asignacion.');
                }
            } finally {
                $stmt->close();
                // CALL deja resultados pendientes antes de consultar de nuevo.
                while ($this->conn->more_results()) {
                    $this->conn->next_result();
                    $resultado = $this->conn->store_result();
                    if ($resultado) {
                        $resultado->free();
                    }
                }
            }

            $this->conn->commit();
            return true;
        } catch (mysqli_sql_exception | RuntimeException $e) {
            $this->conn->rollback();
            error_log('Error al asignar coordinador. Codigo: ' . $e->getCode());
            return false;
        }
    }

    public function buscarTodosLosGrupos() {
        $stmt = $this->conn->prepare("SELECT IdGrupo, NombreGrupo FROM `grupo` WHERE IdGrupo IN (2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40) ORDER BY IdGrupo");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function buscarCoordinador($ci) {
        $stmt = $this->conn->prepare("
            SELECT c.CiCat, c.Sacramento, c.EstadoCat, c.UsuarioCat,
                   p.Nombre, p.ApPaterno, p.ApMaterno,
                   a.RolCat, a.IdGrupo, g.NombreGrupo
            FROM catequista c
            INNER JOIN `persona` p ON c.CiCat = p.CiPersona
            INNER JOIN `asignacion` a ON c.CiCat = a.CiCat
            INNER JOIN `grupo` g ON a.IdGrupo = g.IdGrupo
            WHERE c.CiCat = ? AND a.RolCat = 'Coordinador'
        ");
        $stmt->bind_param("s", $ci);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function cambiarEstadoCoordinador($ci, $nuevoEstado) {
        $stmt = $this->conn->prepare("UPDATE catequista SET EstadoCat = ? WHERE CiCat = ?");
        $stmt->bind_param("ss", $nuevoEstado, $ci);
        return $stmt->execute();
    }

    public function buscarPersonasPorNombre($nombre) {
        $busqueda = "%{$nombre}%";
        $stmt = $this->conn->prepare("
            SELECT p.CiPersona, p.Nombre, p.ApPaterno, p.ApMaterno, p.Sexo, p.Contacto, p.Estado_per,
                   p.ZonaSector, p.SacramentoSolicita, a.Documento AS DocumentoBautizo
            FROM `persona` p
            LEFT JOIN antecedente_sacramental a ON a.CiPersona = p.CiPersona AND a.Sacramento = 'Bautizo'
            WHERE p.Nombre LIKE ? OR p.ApPaterno LIKE ? OR p.ApMaterno LIKE ? OR p.CiPersona LIKE ? OR p.NumeroDocumento LIKE ? OR p.Contacto LIKE ?
            LIMIT 10
        ");
        $stmt->bind_param("ssssss", $busqueda, $busqueda, $busqueda, $busqueda, $busqueda, $busqueda);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /** Búsqueda de personas combinando texto libre y filtros del registro. */
    public function buscarPersonasFiltradas($texto = '', $sacramento = '', $zona = '', $estadoCivil = '') {
        $busqueda = $texto === '' ? '' : "%{$texto}%";
        $stmt = $this->conn->prepare("
            SELECT p.CiPersona, p.Nombre, p.ApPaterno, p.ApMaterno, p.Sexo, p.Contacto, p.Estado_per,
                   p.ZonaSector, p.SacramentoSolicita, a.Documento AS DocumentoBautizo
            FROM `persona` p
            LEFT JOIN antecedente_sacramental a ON a.CiPersona = p.CiPersona AND a.Sacramento = 'Bautizo'
            WHERE (? = '' OR p.Nombre LIKE ? OR p.ApPaterno LIKE ? OR p.ApMaterno LIKE ? OR p.CiPersona LIKE ? OR p.NumeroDocumento LIKE ? OR p.Contacto LIKE ?)
              AND (? = '' OR p.SacramentoSolicita = ?)
              AND (? = '' OR p.ZonaSector = ?)
              AND (? = '' OR p.Estado_per = ?)
            ORDER BY p.ApPaterno, p.ApMaterno, p.Nombre
            LIMIT 50
        ");
        $stmt->bind_param(
            "sssssssssssss",
            $texto, $busqueda, $busqueda, $busqueda, $busqueda, $busqueda, $busqueda,
            $sacramento, $sacramento,
            $zona, $zona,
            $estadoCivil, $estadoCivil
        );
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerZonasSector() {
        $stmt = $this->conn->prepare("
            SELECT DISTINCT ZonaSector
            FROM `persona`
            WHERE ZonaSector IS NOT NULL AND ZonaSector <> ''
            ORDER BY ZonaSector
        ");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerSacramentosSolicitados() {
        $stmt = $this->conn->prepare("
            SELECT DISTINCT SacramentoSolicita
            FROM `persona`
            WHERE SacramentoSolicita IS NOT NULL AND SacramentoSolicita <> ''
            ORDER BY SacramentoSolicita
        ");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerEstadosCiviles() {
        $stmt = $this->conn->prepare("
            SELECT DISTINCT Estado_per
            FROM `persona`
            WHERE Estado_per IS NOT NULL AND Estado_per <> ''
            ORDER BY Estado_per
        ");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function registrarPersona($ci, $nombre, $apPaterno, $apMaterno, $sexo, $fechaNac, $direccion, $zonaSector, $contacto, $telefonoAlternativo, $correo, $estadoCivil, $sacramentoSolicita, $observaciones) {
        $stmt = $this->conn->prepare("
            INSERT INTO `persona` (CiPersona, Nombre, ApPaterno, ApMaterno, Sexo, FechaNac, Direccion, ZonaSector, Contacto, TelefonoAlternativo, Correo, Estado_per, SacramentoSolicita, Observaciones, id_certificado_nacimiento)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '')
        ");
        $stmt->bind_param("ssssssssssssss", $ci, $nombre, $apPaterno, $apMaterno, $sexo, $fechaNac, $direccion, $zonaSector, $contacto, $telefonoAlternativo, $correo, $estadoCivil, $sacramentoSolicita, $observaciones);
        return $stmt->execute();
    }
}
?>
