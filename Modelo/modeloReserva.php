<?php
require_once '../conexion/Conexion.php';

class Reserva
{
    private $conn;
    private $cancelacionConfirmada = null;
    public function __construct()
    {
        $this->conn = (new Conexion())->getConnection();
    }

    public function existeReservaEnFechaHora($fecha, $hora, $realizacion = '', $codRes = 0)
    {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) AS total 
            FROM Reserva 
            WHERE FechaReal = ? AND HoraReal = ? AND (EstadoRes IS NULL OR EstadoRes <> 'Cancelado')
                AND CodRes <> ? AND (? <> 'Comunitario' OR Realizacion <> 'Comunitario')
        ");
        $stmt->bind_param('ssis', $fecha, $hora, $codRes, $realizacion);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result['total'] > 0;
    }

    public function modificarReservaBautizo($data)
    {
        $stmt = $this->conn->prepare("CALL ModificarReservaBautizo(?, ?, ?, ?)");
        $stmt->bind_param(
            "isss",
            $data['CodRes'],
            $data['FechaReal'],
            $data['HoraReal'],
            $data['EstadoRes']
        );

        return $this->ejecutarModificacionConCorreo($stmt, $data, 'Bautizo');
    }

    public function modificarReservaMatrimonio($data)
    {
        $stmt = $this->conn->prepare("CALL ModificarReservaMatrimonio(?, ?, ?, ?)");
        $stmt->bind_param(
            "isss",
            $data['CodRes'],
            $data['FechaReal'],
            $data['HoraReal'],
            $data['EstadoRes']
        );

        return $this->ejecutarModificacionConCorreo($stmt, $data, 'Matrimonio');
    }

    public function modificarReservaMisa($data)
    {
        $stmt = $this->conn->prepare("CALL ModificarReservaMisa(?, ?, ?, ?)");
        $stmt->bind_param(
            "isss",
            $data['CodRes'],
            $data['FechaReal'],
            $data['HoraReal'],
            $data['EstadoRes']
        );

        return $this->ejecutarModificacionConCorreo($stmt, $data, 'Misa');
    }

    public function reservarBautizo($data)
    {
        $stmt = $this->conn->prepare("CALL ReservarBautizo(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "sisssssssss",
            $data['CiPersonaCelebranteB'],
            $data['CodPer'],
            $data['CiPapa'],
            $data['CiMama'],
            $data['Quien'],
            $data['FechaReal'],
            $data['HoraReal'],
            $data['CiPadrino'],
            $data['CiMadrina'],
            $data['TipoCel'],
            $data['Realizacion']
        );

        return $this->ejecutarRegistroConCorreo($stmt, $data);
    }

    public function reservarMatrimonio($data)
    {
        $stmt = $this->conn->prepare("CALL ReservarMatrimonio(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "sssissssssssssss",
            $data['Quien'],
            $data['CiPersonaNovio'],
            $data['CiPersonaNovia'],
            $data['CodPer'],
            $data['FechaReal'],
            $data['HoraReal'],
            $data['CiPadrino'],
            $data['CiMadrina'],
            $data['CiTestigoNovio'],
            $data['CiTestigoNovia'],
            $data['TipoCel'],
            $data['CiPaNovio'],
            $data['CiMaNovio'],
            $data['CiPaNovia'],
            $data['CiMaNovia'],
            $data['Realizacion']
        );

        return $this->ejecutarRegistroConCorreo($stmt, $data);
    }

    public function reservarMisa($data)
    {
        $stmt = $this->conn->prepare("CALL ReservarMisa(?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "sssisss",
            $data['CiPersonademas'],
            $data['FechaReal'],
            $data['HoraReal'],
            $data['CodPer'],
            $data['Quien'],
            $data['TipoCel'],
            $data['Realizacion'],
        );

        return $this->ejecutarRegistroConCorreo($stmt, $data);
    }

    private function liberarResultadosReserva($stmt)
    {
        $stmt->close();
        while ($this->conn->more_results()) {
            $this->conn->next_result();
            $resultado = $this->conn->store_result();
            if ($resultado) $resultado->free();
        }
    }

    private function guardarCorreoReserva($codRes, $correo)
    {
        $correo = is_string($correo) && trim($correo) !== '' ? trim($correo) : null;
        $stmt = $this->conn->prepare('UPDATE reserva SET CorreoSolicitante = ? WHERE CodRes = ?');
        $stmt->bind_param('si', $correo, $codRes);
        if (!$stmt->execute()) throw new RuntimeException('No se pudo guardar el correo de la reserva.');
        $stmt->close();
    }

    private function bloquearHorarioReservas()
    {
        // Todas las altas y modificaciones coordinan la consulta y el guardado.
        $resultado = $this->conn->query("SELECT GET_LOCK(CONCAT('reservas:', SHA1(DATABASE())), 5) AS obtenido")->fetch_assoc();
        if ((int) $resultado['obtenido'] !== 1) {
            throw new DomainException('Se está procesando otra reserva. Intenta nuevamente en unos segundos.');
        }
    }

    private function liberarHorarioReservas()
    {
        $this->conn->query("SELECT RELEASE_LOCK(CONCAT('reservas:', SHA1(DATABASE())))");
    }

    private function comprobarHorarioReserva($fecha, $hora, $realizacion, $codRes = 0)
    {
        if ($this->existeReservaEnFechaHora($fecha, $hora, $realizacion, $codRes)) {
            throw new DomainException('La fecha y hora seleccionadas ya están ocupadas. Solo pueden compartir horario las reservas comunitarias entre sí.');
        }
    }

    private function ejecutarRegistroConCorreo($stmt, $data)
    {
        $this->bloquearHorarioReservas();
        try {
            $this->conn->begin_transaction();
            $this->comprobarHorarioReserva($data['FechaReal'], $data['HoraReal'], $data['Realizacion']);
            if (!$stmt->execute()) throw new RuntimeException('No se pudo registrar la reserva.');
            $this->liberarResultadosReserva($stmt);
            // Los tres procedimientos terminan insertando la reserva en esta conexión.
            $codRes = (int) $this->conn->query('SELECT LAST_INSERT_ID() AS id')->fetch_assoc()['id'];
            if ($codRes <= 0) throw new RuntimeException('No se pudo identificar la reserva registrada.');
            $this->guardarCorreoReserva($codRes, $data['CorreoSolicitante'] ?? null);
            $this->conn->commit();
            return true;
        } catch (Throwable $e) {
            $this->conn->rollback();
            throw $e;
        } finally {
            $this->liberarHorarioReservas();
        }
    }

    public function obtenerCancelacionConfirmada()
    {
        return $this->cancelacionConfirmada;
    }

    private function ejecutarModificacionConCorreo($stmt, $data, $tipo)
    {
        $this->cancelacionConfirmada = null;
        $this->bloquearHorarioReservas();
        try {
            $this->conn->begin_transaction();
            $consulta = $this->conn->prepare('SELECT * FROM reserva WHERE CodRes = ? AND TipoCel = ? FOR UPDATE');
            $consulta->bind_param('is', $data['CodRes'], $tipo);
            $consulta->execute();
            $anterior = $consulta->get_result()->fetch_assoc();
            $consulta->close();
            // Una segunda petición no modifica ni vuelve a notificar una cancelación.
            if (!$anterior || $anterior['EstadoRes'] !== 'Reservado') {
                $stmt->close();
                $this->conn->rollback();
                return false;
            }
            if ($data['EstadoRes'] === 'Cancelado') {
                $data['FechaReal'] = $anterior['FechaReal'];
                $data['HoraReal'] = $anterior['HoraReal'];
            } else {
                $this->comprobarHorarioReserva($data['FechaReal'], $data['HoraReal'], $anterior['Realizacion'], $data['CodRes']);
            }
            $stmt->bind_param('isss', $data['CodRes'], $data['FechaReal'], $data['HoraReal'], $data['EstadoRes']);
            if (!$stmt->execute()) throw new RuntimeException('No se pudo modificar la reserva.');
            $this->liberarResultadosReserva($stmt);
            if (array_key_exists('CorreoSolicitante', $data)) {
                $this->guardarCorreoReserva($data['CodRes'], $data['CorreoSolicitante']);
                $anterior['CorreoSolicitante'] = $data['CorreoSolicitante'];
            }
            $this->conn->commit();
            if ($data['EstadoRes'] === 'Cancelado') $this->cancelacionConfirmada = $anterior;
            return true;
        } catch (Throwable $e) {
            $this->conn->rollback();
            throw $e;
        } finally {
            $this->liberarHorarioReservas();
        }
    }

    public function obtenerReservaBautizo($codRes)
    {
        $stmt = $this->conn->prepare("
            SELECT 
                r.*,
                p1.CiPersona AS CelebranteCiPersona,
                CONCAT(p1.Nombre, ' ', p1.ApPaterno, ' ', IFNULL(p1.ApMaterno, '')) AS NombreCelebrante,
                p2.CiPersona AS PapaCiPersona,
                CONCAT(p2.Nombre, ' ', p2.ApPaterno, ' ', IFNULL(p2.ApMaterno, '')) AS NombrePapa,
                p3.CiPersona AS MamaCiPersona,
                CONCAT(p3.Nombre, ' ', p3.ApPaterno, ' ', IFNULL(p3.ApMaterno, '')) AS NombreMama,
                p4.CiPersona AS PadrinoCiPersona,
                CONCAT(p4.Nombre, ' ', p4.ApPaterno, ' ', IFNULL(p4.ApMaterno, '')) AS NombrePadrino,
                p5.CiPersona AS MadrinaCiPersona,
                CONCAT(p5.Nombre, ' ', p5.ApPaterno, ' ', IFNULL(p5.ApMaterno, '')) AS NombreMadrina,
                CASE WHEN r.TipoCel = 'Misa' THEN 'Misa' ELSE sac.DescripSac END AS DescripSac,
                sac.CostoSac,
                
                car.DescripCar,
                pers.CiPersonal,
                CONCAT(pers.Nombre, ' ', pers.Paterno, ' ', IFNULL(pers.Materno, '')) AS NombrePersonal
            FROM 
                Reserva r
                JOIN Solicitante s ON r.CiPersona = s.CiPersona AND r.CodIns = s.CodIns
                JOIN inssacramento ins ON ins.CodIns = r.CodIns
                JOIN personal pers ON pers.CodPer = ins.CodPer
                JOIN cargo car ON car.CodCar = pers.CodCar
                JOIN tiposacramento sac ON sac.CodSac = ins.CodSac
                JOIN persona p1 ON p1.CiPersona = s.CiPersona
                LEFT JOIN persona p2 ON p2.CiPersona = (SELECT CiPersona FROM Solicitante WHERE CodIns = r.CodIns AND Rol = 0x506170C3A1)
                LEFT JOIN persona p3 ON p3.CiPersona = (SELECT CiPersona FROM Solicitante WHERE CodIns = r.CodIns AND Rol = 0x4D616DC3A1)
                LEFT JOIN persona p4 ON p4.CiPersona = (SELECT CiPersona FROM Solicitante WHERE CodIns = r.CodIns AND Rol = 'Padrino')
                LEFT JOIN persona p5 ON p5.CiPersona = (SELECT CiPersona FROM Solicitante WHERE CodIns = r.CodIns AND Rol = 'Madrina')
            WHERE 
                r.CodRes = ? AND r.TipoCel = 'Bautizo';
        ");
        $stmt->bind_param("i", $codRes);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function obtenerReservaMatrimonio($codRes)
    {
        $stmt = $this->conn->prepare("
            SELECT 
                r.*,
                cars.DescripCar,
                pers.CiPersonal,
                CONCAT(pers.Nombre, ' ', pers.Paterno, ' ', IFNULL(pers.Materno, '')) AS NombrePersonal,
                p1.CiPersona AS NovioCiPersona,
                CONCAT(p1.Nombre, ' ', p1.ApPaterno, ' ', IFNULL(p1.ApMaterno, '')) AS NombreNovio,
                p2.CiPersona AS NoviaCiPersona,
                CONCAT(p2.Nombre, ' ', p2.ApPaterno, ' ', IFNULL(p2.ApMaterno, '')) AS NombreNovia,
                p3.CiPersona AS PapaNovioCiPersona,
                CONCAT(p3.Nombre, ' ', p3.ApPaterno, ' ', IFNULL(p3.ApMaterno, '')) AS NombrePapaNovio,
                p4.CiPersona AS MamaNovioCiPersona,
                CONCAT(p4.Nombre, ' ', p4.ApPaterno, ' ', IFNULL(p4.ApMaterno, '')) AS NombreMamaNovio,
                p5.CiPersona AS PapaNoviaCiPersona,
                CONCAT(p5.Nombre, ' ', p5.ApPaterno, ' ', IFNULL(p5.ApMaterno, '')) AS NombrePapaNovia,
                p6.CiPersona AS MamaNoviaCiPersona,
                CONCAT(p6.Nombre, ' ', p6.ApPaterno, ' ', IFNULL(p6.ApMaterno, '')) AS NombreMamaNovia,
                p7.CiPersona AS PadrinoCiPersona,
                CONCAT(p7.Nombre, ' ', p7.ApPaterno, ' ', IFNULL(p7.ApMaterno, '')) AS NombrePadrino,
                p8.CiPersona AS MadrinaCiPersona,
                CONCAT(p8.Nombre, ' ', p8.ApPaterno, ' ', IFNULL(p8.ApMaterno, '')) AS NombreMadrina,
                p9.CiPersona AS TestigoNovioCiPersona,
                CONCAT(p9.Nombre, ' ', p9.ApPaterno, ' ', IFNULL(p9.ApMaterno, '')) AS NombreTestigoNovio,
                p10.CiPersona AS TestigoNoviaCiPersona,
                CONCAT(p10.Nombre, ' ', p10.ApPaterno, ' ', IFNULL(p10.ApMaterno, '')) AS NombreTestigoNovia,
                CASE WHEN r.TipoCel = 'Misa' THEN 'Misa' ELSE sac.DescripSac END AS DescripSac,
                sac.CostoSac
            FROM 
                Reserva r
                JOIN Solicitante s ON r.CiPersona = s.CiPersona AND r.CodIns = s.CodIns
                JOIN inssacramento ins ON ins.CodIns = r.CodIns
                JOIN personal pers ON pers.CodPer = ins.CodPer
                JOIN cargo cars ON cars.CodCar = pers.CodCar
                JOIN tiposacramento sac ON sac.CodSac = ins.CodSac
                JOIN persona p1 ON p1.CiPersona = (SELECT CiPersona FROM Solicitante WHERE CodIns = r.CodIns AND Rol = 'Novio')
                LEFT JOIN persona p2 ON p2.CiPersona = (SELECT CiPersona FROM Solicitante WHERE CodIns = r.CodIns AND Rol = 'Novia')
                LEFT JOIN persona p3 ON p3.CiPersona = (SELECT CiPersona FROM Solicitante WHERE CodIns = r.CodIns AND Rol = 0x506170C3A1204E6F76696F)
                LEFT JOIN persona p4 ON p4.CiPersona = (SELECT CiPersona FROM Solicitante WHERE CodIns = r.CodIns AND Rol = 0x4D616DC3A1204E6F76696F)
                LEFT JOIN persona p5 ON p5.CiPersona = (SELECT CiPersona FROM Solicitante WHERE CodIns = r.CodIns AND Rol = 0x506170C3A1204E6F766961)
                LEFT JOIN persona p6 ON p6.CiPersona = (SELECT CiPersona FROM Solicitante WHERE CodIns = r.CodIns AND Rol = 0x4D616DC3A1204E6F766961)
                LEFT JOIN persona p7 ON p7.CiPersona = (SELECT CiPersona FROM Solicitante WHERE CodIns = r.CodIns AND Rol = 'Padrino')
                LEFT JOIN persona p8 ON p8.CiPersona = (SELECT CiPersona FROM Solicitante WHERE CodIns = r.CodIns AND Rol = 'Madrina')
                LEFT JOIN persona p9 ON p9.CiPersona = (SELECT CiPersona FROM Solicitante WHERE CodIns = r.CodIns AND Rol = 'Testigo Novio')
                LEFT JOIN persona p10 ON p10.CiPersona = (SELECT CiPersona FROM Solicitante WHERE CodIns = r.CodIns AND Rol = 'Testigo Novia')
            WHERE 
                r.CodRes = ? AND r.TipoCel = 'Matrimonio';
        ");
        $stmt->bind_param("i", $codRes);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function obtenerReservaMisa($codRes)
    {
        $stmt = $this->conn->prepare("
            SELECT 
                r.CodRes, car.DescripCar, per.CiPersonal,
                CONCAT(per.Nombre, ' ', per.Paterno, ' ', IFNULL(per.Materno, '')) AS NombrePersonal,
                r.Quien, r.FechaReal, r.HoraReal, r.Realizacion, r.EstadoRes, r.CorreoSolicitante,
                p1.CiPersona AS CelebranteCiPersona, 
                CONCAT(p1.Nombre, ' ', p1.ApPaterno, ' ', IFNULL(p1.ApMaterno, '')) AS NombreCelebrante,
                CASE WHEN r.TipoCel = 'Misa' THEN 'Misa' ELSE sac.DescripSac END AS DescripSac, sac.CostoSac
            FROM Reserva r
            JOIN Solicitante s ON r.CiPersona = s.CiPersona AND r.CodIns = s.CodIns
            JOIN inssacramento ins ON ins.CodIns = r.CodIns
            JOIN personal per ON per.CodPer = ins.CodPer
            JOIN cargo car ON car.CodCar = per.CodCar
            JOIN tiposacramento sac ON sac.CodSac = ins.CodSac
            JOIN persona p1 ON p1.CiPersona = s.CiPersona
            WHERE r.CodRes = ? AND r.TipoCel = 'Misa';
        ");
        $stmt->bind_param("i", $codRes);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }



    public function getReservasByFecha($fecha)
    {
        $sql = "
            SELECT r.HoraReal, r.CodRes, CASE WHEN r.TipoCel = 'Misa' THEN 'Misa' ELSE ts.DescripSac END AS DescripSac, r.EstadoRes, r.tipocel, r.Realizacion, i.CodIns
            FROM Reserva r
            JOIN inssacramento i ON r.CodIns = i.CodIns
            JOIN tiposacramento ts ON i.CodSac = ts.CodSac
            WHERE r.FechaReal = ?
            ORDER BY r.HoraReal;
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $fecha);
        $stmt->execute();
        $result = $stmt->get_result();
        $reservas = $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();
        return $reservas;
    }
    public function getReservasByMes($mes, $year)
    {
        $sql = "
            SELECT DISTINCT DAY(r.FechaReal) AS dia, r.TipoCel AS tipocel, ts.DescripSac
            FROM Reserva r
            JOIN inssacramento i ON r.CodIns = i.CodIns
            JOIN tiposacramento ts ON i.CodSac = ts.CodSac
            WHERE MONTH(r.FechaReal) = ? AND YEAR(r.FechaReal) = ?
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ii', $mes, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $diasReservados = $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();
        return $diasReservados;
    }

    public function registrarDocumento($data) {
        $stmt = $this->conn->prepare("INSERT INTO presentaciondoc (FechaPres, dequien, CodificDoc, CodTDoc, CodIns) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssii", 
            $data['FechaPres'], 
            $data['Dequien'],
            $data['CodificDoc'], 
            $data['CodTDoc'], 
            $data['CodIns']
        );

        return $stmt->execute();
    }

    public function registrarDocumentoExt($data) {
        $stmt = $this->conn->prepare("INSERT INTO presentaciondoc (FechaPres, dequien, CodificDoc, CodTDoc, CodIns) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssii", 
            $data['FechaPres'], 
            $data['Dequien'],
            $data['CodificDoc'], 
            $data['CodTDoc'], 
            $data['CodIns']
        );
    
        if (!$stmt->execute()) {
            echo "Error en la inserción de presentaciondoc: " . $stmt->error;
            return false;
        }
    
        $codPres = $this->conn->insert_id;
    
        $stmtBau = $this->conn->prepare("INSERT INTO docpresbau (ParroquiaBau, NrLib, NrPag, NrPart, FechaBau, CodPres) VALUES (?, ?, ?, ?, ?, ?)");
        $stmtBau->bind_param("sssssi", 
            $data['ParroquiaBau'], 
            $data['NroLibro'], 
            $data['NroPag'], 
            $data['NroPart'], 
            $data['FechaBau'], 
            $codPres
        );
    
        if (!$stmtBau->execute()) {
            echo "Error en la inserción de docpresbau: " . $stmtBau->error;
            return false;
        }
    
        return true;
    }
    


    public function obtenerDocumentosPorInscripcion($codIns) {
        $stmt = $this->conn->prepare("
            SELECT 
                p.CodificDoc, 
                t.DescripTDoc, 
                p.FechaPres,
                p.Dequien
            FROM 
                presentaciondoc p
            JOIN 
                tipodocumento t ON p.CodTDoc = t.CodTDoc  
            WHERE 
                p.CodIns = ?
        ");
        $stmt->bind_param("i", $codIns);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>
