<?php
require_once '../Conexion/Conexion.php';

class Persona {
    private $conn;

    public function __construct() {
        $this->conn = (new Conexion())->getConnection();
    }

    public function crear($ciPersona, $nombre, $apPaterno, $apMaterno, $sexo, $fechaNac, $direccion, $contacto, $estado, $certificado = '', $correo = '', $zonaSector = '', $telefonoAlternativo = '', $sacramentoSolicita = '', $observaciones = '', $tipoDocumento = 'CI boliviano', $numeroDocumento = null, $nacionalidad = 'Boliviana', $antecedentes = []) {
        $this->conn->begin_transaction();
        try {
            $stmt = $this->conn->prepare('SELECT CiPersona FROM `persona` WHERE CiPersona = ? OR (NumeroDocumento IS NOT NULL AND TipoDocumento = ? AND NumeroDocumento = ?) LIMIT 1');
            $stmt->bind_param('sss', $ciPersona, $tipoDocumento, $numeroDocumento);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $stmt->close();
                throw new RuntimeException('Ya existe una persona con ese documento.');
            }
            $stmt->close();

            $sql = 'INSERT INTO `persona` (CiPersona, TipoDocumento, NumeroDocumento, Nacionalidad, Nombre, ApPaterno, ApMaterno, Sexo, FechaNac, Direccion, ZonaSector, Contacto, TelefonoAlternativo, Correo, Estado_per, SacramentoSolicita, Observaciones, id_certificado_nacimiento) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('ssssssssssssssssss', $ciPersona, $tipoDocumento, $numeroDocumento, $nacionalidad, $nombre, $apPaterno, $apMaterno, $sexo, $fechaNac, $direccion, $zonaSector, $contacto, $telefonoAlternativo, $correo, $estado, $sacramentoSolicita, $observaciones, $certificado);
            if (!$stmt->execute()) {
                throw new RuntimeException('No se pudo registrar la persona.');
            }
            $stmt->close();

            $this->guardarAntecedentes($ciPersona, $antecedentes);
            $this->conn->commit();
            return true;
        } catch (Throwable $e) {
            $this->conn->rollback();
            error_log('Error al registrar persona: ' . $e->getMessage());
            throw $e;
        }
    }

    private function guardarAntecedentes($ciPersona, array $antecedentes) {
        if (empty($antecedentes)) {
            return;
        }

        $sql = 'INSERT INTO antecedente_sacramental
            (CiPersona, Sacramento, Pais, Departamento, Provincia, Municipio, Parroquia, Libro, Pagina, Partida, Fecha, Documento, NombreConyuge, Observacion)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
              Pais = VALUES(Pais), Departamento = VALUES(Departamento), Provincia = VALUES(Provincia),
              Municipio = VALUES(Municipio), Parroquia = VALUES(Parroquia), Libro = VALUES(Libro),
              Pagina = VALUES(Pagina), Partida = VALUES(Partida), Fecha = VALUES(Fecha),
              Documento = CASE WHEN VALUES(Documento) = \'\' THEN Documento ELSE VALUES(Documento) END,
              NombreConyuge = VALUES(NombreConyuge), Observacion = VALUES(Observacion)';
        $stmt = $this->conn->prepare($sql);

        foreach ($antecedentes as $antecedente) {
            $sacramento = $antecedente['Sacramento'];
            $pais = $antecedente['Pais'];
            $departamento = $antecedente['Departamento'];
            $provincia = $antecedente['Provincia'];
            $municipio = $antecedente['Municipio'];
            $parroquia = $antecedente['Parroquia'];
            $libro = $antecedente['Libro'];
            $pagina = $antecedente['Pagina'];
            $partida = $antecedente['Partida'];
            $fecha = $antecedente['Fecha'];
            $documento = $antecedente['Documento'];
            $nombreConyuge = $antecedente['NombreConyuge'];
            $observacion = $antecedente['Observacion'];
            $stmt->bind_param('ssssssssssssss', $ciPersona, $sacramento, $pais, $departamento, $provincia, $municipio, $parroquia, $libro, $pagina, $partida, $fecha, $documento, $nombreConyuge, $observacion);
            if (!$stmt->execute()) {
                throw new RuntimeException('No se pudo guardar el antecedente de ' . $sacramento . '.');
            }
        }
        $stmt->close();
    }

    public function buscar($identificacion) {
        $stmt = $this->conn->prepare('SELECT * FROM `persona` WHERE CiPersona = ? OR NumeroDocumento = ? OR id_certificado_nacimiento LIKE ? ORDER BY (CiPersona = ?) DESC LIMIT 1');
        $certificado = '%' . $identificacion . '%';
        $stmt->bind_param('ssss', $identificacion, $identificacion, $certificado, $identificacion);
        $stmt->execute();
        $persona = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($persona) {
            $persona['AntecedentesSacramentales'] = $this->obtenerAntecedentes($persona['CiPersona']);
            $persona['ProcesosPastorales'] = $this->obtenerProcesosPastorales($persona['CiPersona']);
        }
        return $persona;
    }

    public function obtenerAntecedentes($ciPersona) {
        $stmt = $this->conn->prepare('SELECT * FROM antecedente_sacramental WHERE CiPersona = ? ORDER BY FIELD(Sacramento, \'Bautizo\', \'Primera Comunión\', \'Confirmación\', \'Matrimonio anterior\')');
        $stmt->bind_param('s', $ciPersona);
        $stmt->execute();
        $filas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $filas;
    }

    public function buscarCoincidencias($termino, $limite = 15) {
        $limite = max(1, min(50, (int) $limite));
        $termino = preg_replace('/\s+/u', ' ', trim($termino));
        $busqueda = '%' . $termino . '%';
        $sql = "SELECT CiPersona, TipoDocumento, NumeroDocumento, Nombre, ApPaterno, ApMaterno, FechaNac, Contacto, Direccion, Estado_per, SacramentoSolicita
                FROM `persona`
                WHERE CiPersona LIKE ? OR NumeroDocumento LIKE ? OR Nombre LIKE ? OR ApPaterno LIKE ? OR ApMaterno LIKE ?
                   OR CONCAT_WS(' ', TRIM(Nombre), TRIM(ApPaterno), TRIM(ApMaterno)) LIKE ?
                   OR CONCAT_WS(' ', TRIM(ApPaterno), TRIM(ApMaterno), TRIM(Nombre)) LIKE ?
                ORDER BY ApPaterno, ApMaterno, Nombre LIMIT {$limite}";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('sssssss', $busqueda, $busqueda, $busqueda, $busqueda, $busqueda, $busqueda, $busqueda);
        $stmt->execute();
        $filas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $filas;
    }

    private function obtenerProcesosPastorales($ciPersona) {
        $procesos = ['estadoActual' => 'Registrado', 'reservas' => [], 'catequesis' => [], 'sacramentos' => []];

        $stmt = $this->conn->prepare("SELECT DISTINCT r.CodRes, r.TipoCel, r.EstadoRes, r.FechaReal FROM `reserva` r WHERE r.CiPersona = ? OR r.CiReservante = ? OR EXISTS (SELECT 1 FROM `solicitante` s WHERE s.CodIns = r.CodIns AND s.CiPersona = ?) ORDER BY r.FechaReal DESC, r.CodRes DESC");
        $stmt->bind_param('sss', $ciPersona, $ciPersona, $ciPersona);
        $stmt->execute();
        $procesos['reservas'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $stmt = $this->conn->prepare("SELECT c.Sacramento, c.EstadoCel, i.IdInscripcion, i.IdGrupo FROM `celebrante` c LEFT JOIN inscripcion i ON i.CiCel = c.CiCel WHERE c.CiCel = ? ORDER BY i.IdInscripcion DESC");
        $stmt->bind_param('s', $ciPersona);
        $stmt->execute();
        $procesos['catequesis'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $stmt = $this->conn->prepare("SELECT DISTINCT ins.CodIns, ts.DescripSac, ins.FechaIns, CASE WHEN cert.NroCertificado IS NULL THEN 'Realizado' ELSE 'Certificado emitido' END AS EstadoProceso, cert.NroCertificado FROM `solicitante` s INNER JOIN inssacramento ins ON ins.CodIns = s.CodIns INNER JOIN tiposacramento ts ON ts.CodSac = ins.CodSac LEFT JOIN certificado cert ON cert.CodIns = ins.CodIns AND cert.EstadoCert = 'Activo' WHERE s.CiPersona = ? AND ((ins.CodSac = 1 AND s.Rol IN ('Bautizado', 'Celebrante')) OR (ins.CodSac = 3 AND s.Rol = 'Confirmante') OR (ins.CodSac = 4 AND s.Rol IN ('Novio', 'Novia'))) ORDER BY ins.FechaIns DESC, ins.CodIns DESC");
        $stmt->bind_param('s', $ciPersona);
        $stmt->execute();
        $procesos['sacramentos'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        foreach ($procesos['reservas'] as $reserva) {
            if (($reserva['EstadoRes'] ?? '') === 'Reservado') {
                $procesos['estadoActual'] = 'Reservado';
                break;
            }
        }
        if (!empty($procesos['catequesis'])) {
            $procesos['estadoActual'] = 'Inscrito';
        }
        foreach ($procesos['sacramentos'] as $sacramento) {
            $procesos['estadoActual'] = $sacramento['EstadoProceso'];
            if ($sacramento['EstadoProceso'] === 'Certificado emitido') {
                break;
            }
        }
        return $procesos;
    }

    public function actualizar($ciPersona, $nombre, $apPaterno, $apMaterno, $sexo, $fechaNac, $direccion, $contacto, $estado, $correo = null) {
        if ($correo === null) {
            $stmt = $this->conn->prepare('UPDATE `persona` SET Nombre = ?, ApPaterno = ?, ApMaterno = ?, Sexo = ?, FechaNac = ?, Direccion = ?, Contacto = ?, Estado_per = ? WHERE CiPersona = ?');
            $stmt->bind_param('sssssssss', $nombre, $apPaterno, $apMaterno, $sexo, $fechaNac, $direccion, $contacto, $estado, $ciPersona);
        } else {
            $stmt = $this->conn->prepare('UPDATE `persona` SET Nombre = ?, ApPaterno = ?, ApMaterno = ?, Sexo = ?, FechaNac = ?, Direccion = ?, Contacto = ?, Correo = ?, Estado_per = ? WHERE CiPersona = ?');
            $stmt->bind_param('ssssssssss', $nombre, $apPaterno, $apMaterno, $sexo, $fechaNac, $direccion, $contacto, $correo, $estado, $ciPersona);
        }
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    /**
     * Reemplaza el identificador provisional (la partida del certificado de
     * nacimiento o un identificador SINCI) por el CI definitivo de la persona.
     * Las tablas relacionadas tienen ON UPDATE CASCADE sobre persona.CiPersona.
     */
    public function cambiarIdentificacion($ciPersona, $nuevoCi, $tipoDocumento = 'CI boliviano') {
        $this->conn->begin_transaction();
        try {
            $stmt = $this->conn->prepare('SELECT CiPersona FROM `persona` WHERE (CiPersona = ? OR NumeroDocumento = ?) AND CiPersona <> ? LIMIT 1');
            $stmt->bind_param('sss', $nuevoCi, $nuevoCi, $ciPersona);
            $stmt->execute();
            $duplicado = $stmt->get_result()->num_rows > 0;
            $stmt->close();
            if ($duplicado) {
                throw new RuntimeException('Ya existe otra persona registrada con ese CI.');
            }

            $stmt = $this->conn->prepare('UPDATE `persona` SET CiPersona = ?, NumeroDocumento = ?, TipoDocumento = ? WHERE CiPersona = ?');
            $stmt->bind_param('ssss', $nuevoCi, $nuevoCi, $tipoDocumento, $ciPersona);
            if (!$stmt->execute()) {
                throw new RuntimeException('No se pudo actualizar el CI de la persona.');
            }
            $stmt->close();
            $this->conn->commit();
            return true;
        } catch (Throwable $e) {
            $this->conn->rollback();
            throw $e;
        }
    }
}
?>
