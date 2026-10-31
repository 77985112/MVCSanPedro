<?php
require_once '../Conexion/Conexion.php';

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
            FROM `reserva` 
            WHERE FechaReal = ? AND HoraReal = ? AND (EstadoRes IS NULL OR EstadoRes <> 'Cancelado')
                AND CodRes <> ? AND (? <> 'Comunitario' OR Realizacion <> 'Comunitario')
        ");
        $stmt->bind_param('ssis', $fecha, $hora, $codRes, $realizacion);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result['total'] > 0;
    }

    public function buscarPersonaPorIdentificacion($identificacion)
    {
        $stmt = $this->conn->prepare(
            'SELECT CiPersona, Nombre, ApPaterno, ApMaterno FROM `persona` WHERE CiPersona = ? OR NumeroDocumento = ? LIMIT 1'
        );
        $stmt->bind_param('ss', $identificacion, $identificacion);
        $stmt->execute();
        $persona = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $persona ?: null;
    }

    public function obtenerCelebrantesActivos($incluirDiaconos = false)
    {
        $cargos = $incluirDiaconos ? '3, 5, 7' : '3, 5';
        $resultado = $this->conn->query("
            SELECT p.CodPer, p.Nombre, p.Paterno, p.Materno, c.DescripCar, p.CodCar
            FROM `personal` p
            INNER JOIN `cargo` c ON c.CodCar = p.CodCar
            WHERE p.Estado = 'Activo'
              AND (p.FechaFinGestion IS NULL OR p.FechaFinGestion >= CURRENT_DATE)
              AND p.CodCar IN ($cargos)
            ORDER BY FIELD(p.CodCar, 3, 5, 7), p.Nombre, p.Paterno
        ");
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function esCelebranteActivo($codPer, $incluirDiacono = false)
    {
        $cargos = $incluirDiacono ? '3, 5, 7' : '3, 5';
        $stmt = $this->conn->prepare("
            SELECT 1
            FROM `personal`
            WHERE CodPer = ?
              AND Estado = 'Activo'
              AND (FechaFinGestion IS NULL OR FechaFinGestion >= CURRENT_DATE)
              AND CodCar IN ($cargos)
            LIMIT 1
        ");
        $stmt->bind_param('i', $codPer);
        $stmt->execute();
        $valido = (bool) $stmt->get_result()->fetch_row();
        $stmt->close();
        return $valido;
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
        $stmt = $this->conn->prepare('UPDATE `reserva` SET CorreoSolicitante = ? WHERE CodRes = ?');
        $stmt->bind_param('si', $correo, $codRes);
        if (!$stmt->execute()) throw new RuntimeException('No se pudo guardar el correo de la reserva.');
        $stmt->close();
    }

    private function guardarReservanteReserva($codRes, $ciReservante)
    {
        $stmt = $this->conn->prepare('UPDATE `reserva` SET CiReservante = ? WHERE CodRes = ?');
        $stmt->bind_param('si', $ciReservante, $codRes);
        if (!$stmt->execute()) throw new RuntimeException('No se pudo vincular el reservante con la reserva.');
        $stmt->close();
    }

    private function guardarCelebranteReserva($codRes, $codPerCelebrante)
    {
        $stmt = $this->conn->prepare('UPDATE `reserva` SET CodPerCelebrante = ? WHERE CodRes = ?');
        $stmt->bind_param('ii', $codPerCelebrante, $codRes);
        if (!$stmt->execute()) throw new RuntimeException('No se pudo vincular el celebrante con la reserva.');
        $stmt->close();
    }

    /** Mantiene el filtro de Persona alineado con la reserva recién creada. */
    private function actualizarSolicitudPersona($data)
    {
        $tipo = $data['TipoCel'] ?? '';
        $solicitud = $tipo === 'Misa' ? 'Reserva de misa' : 'Reserva de ' . $tipo;
        $identificaciones = [];
        $identificaciones[] = $data['CiReservante'] ?? '';
        if ($tipo === 'Bautizo') {
            $identificaciones[] = $data['CiPersonaCelebranteB'] ?? '';
        } elseif ($tipo === 'Matrimonio') {
            $identificaciones[] = $data['CiPersonaNovio'] ?? '';
            $identificaciones[] = $data['CiPersonaNovia'] ?? '';
        } elseif ($tipo === 'Misa') {
            $identificaciones[] = $data['CiPersonademas'] ?? '';
        }
        $stmt = $this->conn->prepare('UPDATE `persona` SET SacramentoSolicita = ? WHERE CiPersona = ?');
        foreach (array_unique(array_filter($identificaciones)) as $ciPersona) {
            $stmt->bind_param('ss', $solicitud, $ciPersona);
            if (!$stmt->execute()) {
                $stmt->close();
                throw new RuntimeException('No se pudo actualizar el seguimiento de la persona.');
            }
        }
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
            $this->guardarReservanteReserva($codRes, $data['CiReservante']);
            $this->guardarCelebranteReserva($codRes, $data['CodPerCelebrante']);
            $this->actualizarSolicitudPersona($data);
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
            $consulta = $this->conn->prepare('SELECT * FROM `reserva` WHERE CodRes = ? AND TipoCel = ? FOR UPDATE');
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
                CONCAT(pers.Nombre, ' ', pers.Paterno, ' ', IFNULL(pers.Materno, '')) AS NombrePersonal,
                CONCAT(cele.Nombre, ' ', cele.Paterno, ' ', IFNULL(cele.Materno, '')) AS NombreCelebranteAsignado,
                carcele.DescripCar AS CargoCelebranteAsignado
            FROM 
                `reserva` r
                JOIN `solicitante` s ON r.CiPersona = s.CiPersona AND r.CodIns = s.CodIns
                JOIN inssacramento ins ON ins.CodIns = r.CodIns
                JOIN `personal` pers ON pers.CodPer = ins.CodPer
                JOIN `cargo` car ON car.CodCar = pers.CodCar
                LEFT JOIN `personal` cele ON cele.CodPer = r.CodPerCelebrante
                LEFT JOIN `cargo` carcele ON carcele.CodCar = cele.CodCar
                JOIN tiposacramento sac ON sac.CodSac = ins.CodSac
                JOIN `persona` p1 ON p1.CiPersona = s.CiPersona
                LEFT JOIN `persona` p2 ON p2.CiPersona = (SELECT CiPersona FROM `solicitante` WHERE CodIns = r.CodIns AND Rol = 0x506170C3A1)
                LEFT JOIN `persona` p3 ON p3.CiPersona = (SELECT CiPersona FROM `solicitante` WHERE CodIns = r.CodIns AND Rol = 0x4D616DC3A1)
                LEFT JOIN `persona` p4 ON p4.CiPersona = (SELECT CiPersona FROM `solicitante` WHERE CodIns = r.CodIns AND Rol = 'Padrino')
                LEFT JOIN `persona` p5 ON p5.CiPersona = (SELECT CiPersona FROM `solicitante` WHERE CodIns = r.CodIns AND Rol = 'Madrina')
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
                sac.CostoSac,
                CONCAT(cele.Nombre, ' ', cele.Paterno, ' ', IFNULL(cele.Materno, '')) AS NombreCelebranteAsignado,
                carcele.DescripCar AS CargoCelebranteAsignado
            FROM 
                `reserva` r
                JOIN `solicitante` s ON r.CiPersona = s.CiPersona AND r.CodIns = s.CodIns
                JOIN inssacramento ins ON ins.CodIns = r.CodIns
                JOIN `personal` pers ON pers.CodPer = ins.CodPer
                JOIN `cargo` cars ON cars.CodCar = pers.CodCar
                LEFT JOIN `personal` cele ON cele.CodPer = r.CodPerCelebrante
                LEFT JOIN `cargo` carcele ON carcele.CodCar = cele.CodCar
                JOIN tiposacramento sac ON sac.CodSac = ins.CodSac
                JOIN `persona` p1 ON p1.CiPersona = (SELECT CiPersona FROM `solicitante` WHERE CodIns = r.CodIns AND Rol = 'Novio')
                LEFT JOIN `persona` p2 ON p2.CiPersona = (SELECT CiPersona FROM `solicitante` WHERE CodIns = r.CodIns AND Rol = 'Novia')
                LEFT JOIN `persona` p3 ON p3.CiPersona = (SELECT CiPersona FROM `solicitante` WHERE CodIns = r.CodIns AND Rol = 0x506170C3A1204E6F76696F)
                LEFT JOIN `persona` p4 ON p4.CiPersona = (SELECT CiPersona FROM `solicitante` WHERE CodIns = r.CodIns AND Rol = 0x4D616DC3A1204E6F76696F)
                LEFT JOIN `persona` p5 ON p5.CiPersona = (SELECT CiPersona FROM `solicitante` WHERE CodIns = r.CodIns AND Rol = 0x506170C3A1204E6F766961)
                LEFT JOIN `persona` p6 ON p6.CiPersona = (SELECT CiPersona FROM `solicitante` WHERE CodIns = r.CodIns AND Rol = 0x4D616DC3A1204E6F766961)
                LEFT JOIN `persona` p7 ON p7.CiPersona = (SELECT CiPersona FROM `solicitante` WHERE CodIns = r.CodIns AND Rol = 'Padrino')
                LEFT JOIN `persona` p8 ON p8.CiPersona = (SELECT CiPersona FROM `solicitante` WHERE CodIns = r.CodIns AND Rol = 'Madrina')
                LEFT JOIN `persona` p9 ON p9.CiPersona = (SELECT CiPersona FROM `solicitante` WHERE CodIns = r.CodIns AND Rol = 'Testigo Novio')
                LEFT JOIN `persona` p10 ON p10.CiPersona = (SELECT CiPersona FROM `solicitante` WHERE CodIns = r.CodIns AND Rol = 'Testigo Novia')
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
                r.Quien, r.CiReservante, r.FechaReal, r.HoraReal, r.Realizacion, r.EstadoRes, r.CorreoSolicitante,
                p1.CiPersona AS CelebranteCiPersona, 
                CONCAT(p1.Nombre, ' ', p1.ApPaterno, ' ', IFNULL(p1.ApMaterno, '')) AS NombreCelebrante,
                CONCAT(pr.Nombre, ' ', pr.ApPaterno, ' ', IFNULL(pr.ApMaterno, '')) AS NombreReservante,
                CASE WHEN r.TipoCel = 'Misa' THEN 'Misa' ELSE sac.DescripSac END AS DescripSac, sac.CostoSac,
                CONCAT(cele.Nombre, ' ', cele.Paterno, ' ', IFNULL(cele.Materno, '')) AS NombreCelebranteAsignado,
                carcele.DescripCar AS CargoCelebranteAsignado
            FROM `reserva` r
            JOIN `solicitante` s ON r.CiPersona = s.CiPersona AND r.CodIns = s.CodIns
            JOIN inssacramento ins ON ins.CodIns = r.CodIns
            JOIN `personal` per ON per.CodPer = ins.CodPer
            JOIN `cargo` car ON car.CodCar = per.CodCar
            LEFT JOIN `personal` cele ON cele.CodPer = r.CodPerCelebrante
            LEFT JOIN `cargo` carcele ON carcele.CodCar = cele.CodCar
            JOIN tiposacramento sac ON sac.CodSac = ins.CodSac
            JOIN `persona` p1 ON p1.CiPersona = s.CiPersona
            LEFT JOIN `persona` pr ON pr.CiPersona = r.CiReservante
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
            FROM `reserva` r
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
            FROM `reserva` r
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
                p.CodPres,
                p.CodificDoc, 
                p.CodTDoc,
                p.Dequien,
                p.FechaPres,
                p.Archivo,
                t.DescripTDoc,
                d.ParroquiaBau,
                d.NrLib,
                d.NrPag,
                d.NrPart,
                d.FechaBau
            FROM 
                presentaciondoc p
            JOIN 
                tipodocumento t ON p.CodTDoc = t.CodTDoc  
            LEFT JOIN
                docpresbau d ON d.CodPres = p.CodPres
            WHERE 
                p.CodIns = ?
            ORDER BY p.CodPres
        ");
        $stmt->bind_param("i", $codIns);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /** Inscripción (sacramento) a la que pertenece el certificado. */
    public function obtenerInscripcionSacramento($codIns) {
        $stmt = $this->conn->prepare("
            SELECT ins.CodIns, ins.CodSac, ins.FechaIns, ts.DescripSac,
                   (SELECT MAX(d.FechaBau)
                    FROM presentaciondoc p
                    INNER JOIN docpresbau d ON d.CodPres = p.CodPres
                    WHERE p.CodIns = ins.CodIns) AS FechaSacramento
            FROM inssacramento ins
            INNER JOIN tiposacramento ts ON ts.CodSac = ins.CodSac
            WHERE ins.CodIns = ?
            LIMIT 1
        ");
        $stmt->bind_param("i", $codIns);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $fila;
    }

    /** Personas que participan del sacramento, con su rol y datos de identidad. */
    public function obtenerPersonasSacramento($codIns) {
        $stmt = $this->conn->prepare("
            SELECT s.Rol, s.CiPersona,
                   p.Nombre, p.ApPaterno, p.ApMaterno,
                   p.NumeroDocumento, p.Estado_per, p.id_certificado_nacimiento
            FROM `solicitante` s
            INNER JOIN `persona` p ON p.CiPersona = s.CiPersona
            WHERE s.CodIns = ?
            ORDER BY FIELD(s.Rol,
                'Bautizado', 'Celebrante', 'Confirmante', 'Novio', 'Novia',
                'Papá', 'Mamá', 'Padre', 'Madre',
                'Papá Novio', 'Mamá Novio', 'Papá Novia', 'Mamá Novia',
                'Padrino', 'Madrina', 'Testigo Novio', 'Testigo Novia')
        ");
        $stmt->bind_param("i", $codIns);
        $stmt->execute();
        $filas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $filas;
    }

    /** Bautizo o confirmación registrado de una persona (para reutilizar sus datos). */
    public function obtenerSacramentoPersona($ciPersona, $codSac) {
        $tipoDocumento = (int) $codSac === 1 ? 3 : 5;
        $stmt = $this->conn->prepare("
            SELECT ins.CodIns, ins.FechaIns,
                   dpb.ParroquiaBau, dpb.NrLib, dpb.NrPag, dpb.NrPart, dpb.FechaBau
            FROM `solicitante` s
            INNER JOIN inssacramento ins ON ins.CodIns = s.CodIns
            LEFT JOIN presentaciondoc pre ON pre.CodIns = ins.CodIns AND pre.CodTDoc = ?
            LEFT JOIN docpresbau dpb ON dpb.CodPres = pre.CodPres
            WHERE s.CiPersona = ? AND ins.CodSac = ?
              AND s.Rol IN ('Bautizado', 'Celebrante', 'Confirmante', 'Novio', 'Novia')
            ORDER BY ins.CodIns DESC
            LIMIT 1
        ");
        $stmt->bind_param("isi", $tipoDocumento, $ciPersona, $codSac);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $fila;
    }

    /** Tipos de documento disponibles en el catálogo. */
    public function obtenerTiposDocumento() {
        $resultado = $this->conn->query("SELECT CodTDoc, DescripTDoc FROM tipodocumento ORDER BY CodTDoc");
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    /** Busca la fila de un documento ya presentado para esa inscripción, tipo y rol. */
    public function buscarDocumentoPresentado($codIns, $codTDoc, $dequien) {
        $stmt = $this->conn->prepare("
            SELECT CodPres, Archivo
            FROM presentaciondoc
            WHERE CodIns = ? AND CodTDoc = ? AND Dequien = ?
            ORDER BY CodPres DESC
            LIMIT 1
        ");
        $stmt->bind_param("iis", $codIns, $codTDoc, $dequien);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $fila;
    }

    /**
     * Inserta un documento nuevo (con archivo opcional) y devuelve su CodPres.
     */
    public function registrarDocumentoConArchivo($data, $archivo) {
        $stmt = $this->conn->prepare("
            INSERT INTO presentaciondoc (Archivo, FechaPres, dequien, CodificDoc, CodTDoc, CodIns)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $fechaPres = (string) ($data['FechaPres'] ?? '');
        $dequien = (string) ($data['Dequien'] ?? '');
        $codificDoc = (string) ($data['CodificDoc'] ?? '');
        $codTDoc = (int) ($data['CodTDoc'] ?? 0);
        $codIns = (int) ($data['CodIns'] ?? 0);

        $stmt->bind_param("ssssii", $archivo, $fechaPres, $dequien, $codificDoc, $codTDoc, $codIns);
        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }
        $codPres = (int) $this->conn->insert_id;
        $stmt->close();
        return $codPres;
    }

    /** Guarda o reemplaza solo el archivo de un documento requerido. */
    public function actualizarArchivoDocumento($codIns, $codTDoc, $dequien, $archivo) {
        $existente = $this->buscarDocumentoPresentado($codIns, $codTDoc, $dequien);
        if ($existente) {
            $stmt = $this->conn->prepare("UPDATE presentaciondoc SET Archivo = ? WHERE CodPres = ?");
            $stmt->bind_param("si", $archivo, $existente['CodPres']);
            $resultado = $stmt->execute();
            $stmt->close();
            return $resultado ? (int) $existente['CodPres'] : false;
        }

        $stmt = $this->conn->prepare("
            INSERT INTO presentaciondoc (Archivo, FechaPres, dequien, CodificDoc, CodTDoc, CodIns)
            VALUES (?, ?, ?, '', ?, ?)
        ");
        $hoy = date('Y-m-d');
        $stmt->bind_param("sssii", $archivo, $hoy, $dequien, $codTDoc, $codIns);
        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }
        $codPres = (int) $this->conn->insert_id;
        $stmt->close();
        return $codPres;
    }

    /** Guarda (reemplazando) los datos de partida del documento. */
    public function guardarDocPresBau($codPres, $data) {
        $stmt = $this->conn->prepare("DELETE FROM docpresbau WHERE CodPres = ?");
        $stmt->bind_param("i", $codPres);
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare("
            INSERT INTO docpresbau (ParroquiaBau, NrLib, NrPag, NrPart, FechaBau, CodPres)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssssi",
            $data['ParroquiaBau'],
            $data['NroLibro'],
            $data['NroPag'],
            $data['NroPart'],
            $data['FechaBau'],
            $codPres
        );
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }
}
?>
