<?php
require_once '../Conexion/Conexion.php';
class modeloReporteGeneral {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion->getConnection();
    }

    public function obtenerCertificadosEmitidos() {
        $sql = "CALL DatosEmitidos()";
        $result = $this->conn->query($sql);

        $certificados = [];
        if ($result && $result->num_rows > 0) {
            while ($fila = $result->fetch_assoc()) {
                $datosEmisor = trim((string) ($fila['DatosEm'] ?? ''));
                $fila['NombreEmisor'] = trim((string) preg_replace('/\s*\([^)]*\)\s*$/u', '', $datosEmisor));
                unset($fila['DatosEm']);
                $certificados[] = $fila;
            }
        }
        return $certificados;
    }
    
    public function obtenerReservas() {
        $sql = "CALL DatosReserva()";
        $result = $this->conn->query($sql);

        $certificados = [];
        if ($result && $result->num_rows > 0) {
            while ($fila = $result->fetch_assoc()) {
                if (($fila['TipoCel'] ?? '') === 'Misa') {
                    $fila['DescripSac'] = 'Misa';
                }
                $certificados[] = $fila;
            }
        }
        return $certificados;
    }

    public function obtenerReservasMes($anio, $mes) {
        $stmt = $this->conn->prepare("CALL DatosReservaPorMes(?, ?)");
        $stmt->bind_param("ii", $anio, $mes);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $certificados = [];
        while ($fila = $result->fetch_assoc()) {
            if (($fila['TipoCel'] ?? '') === 'Misa') {
                $fila['DescripSac'] = 'Misa';
            }
            $certificados[] = $fila;
        }
        return $certificados;
    }
    
    public function obtenerReservasDia($anio, $mes, $dia) {
        $stmt = $this->conn->prepare("CALL DatosReservaPorDia(?, ?, ?)");
        $stmt->bind_param("iii", $anio, $mes, $dia);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $certificados = [];
        while ($fila = $result->fetch_assoc()) {
            if (($fila['TipoCel'] ?? '') === 'Misa') {
                $fila['DescripSac'] = 'Misa';
            }
            $certificados[] = $fila;
        }
        return $certificados;
    }

    /**
     * Reservas del reporte con el nombre y documento de la persona a nombre
     * de quien se hizo la reserva. Se consulta directo para no modificar los
     * procedimientos que usan otras pantallas.
     */
    public function obtenerReservasReporte($tipo = 'anual', $anio = null, $mes = null, $dia = null) {
        $sql = "SELECT ts.DescripSac, r.EstadoRes, r.FechaReal, r.HoraReal, r.TipoCel,
                       car.DescripCar,
                       CONCAT(p.Nombre, ' ', p.Paterno, ' (', p.CiPersonal, ')') AS DatosEm,
                       r.Quien,
                       TRIM(CONCAT_WS(' ', pr.Nombre, pr.ApPaterno, pr.ApMaterno)) AS ReservanteNombre,
                       COALESCE(NULLIF(TRIM(pr.NumeroDocumento), ''), pr.CiPersona) AS ReservanteDocumento,
                       pr.CiPersona AS ReservanteCi,
                       pr.TipoDocumento AS ReservanteTipoDoc
                FROM `reserva` r
                INNER JOIN inssacramento ins ON r.CodIns = ins.CodIns
                INNER JOIN `personal` p ON ins.CodPer = p.CodPer
                INNER JOIN tiposacramento ts ON ins.CodSac = ts.CodSac
                INNER JOIN `cargo` car ON p.CodCar = car.CodCar
                LEFT JOIN `persona` pr ON pr.CiPersona = r.CiPersona
                WHERE ";

        if ($tipo === 'mensual') {
            $sql .= "YEAR(r.FechaReal) = ? AND MONTH(r.FechaReal) = ?";
        } elseif ($tipo === 'diario') {
            $sql .= "YEAR(r.FechaReal) = ? AND MONTH(r.FechaReal) = ? AND DAY(r.FechaReal) = ?";
        } else {
            $sql .= "YEAR(ins.FechaIns) = YEAR(CURDATE())";
        }
        $sql .= " ORDER BY r.FechaReal, r.HoraReal";

        $reservas = [];

        if ($tipo === 'anual') {
            $result = $this->conn->query($sql);
            if ($result && $result->num_rows > 0) {
                while ($fila = $result->fetch_assoc()) {
                    $reservas[] = $this->normalizarFilaReserva($fila);
                }
            }
            return $reservas;
        }

        $stmt = $this->conn->prepare($sql);
        if ($tipo === 'diario') {
            $stmt->bind_param("iii", $anio, $mes, $dia);
        } else {
            $stmt->bind_param("ii", $anio, $mes);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            while ($fila = $result->fetch_assoc()) {
                $reservas[] = $this->normalizarFilaReserva($fila);
            }
        }

        return $reservas;
    }

    private function normalizarFilaReserva($fila) {
        if (($fila['TipoCel'] ?? '') === 'Misa') {
            $fila['DescripSac'] = 'Misa';
        }

        $fila['ReservanteNombre'] = trim((string) ($fila['ReservanteNombre'] ?? ''));
        $fila['ReservanteDocumento'] = trim((string) ($fila['ReservanteDocumento'] ?? ''));

        if ($fila['ReservanteNombre'] === '') {
            $fila['ReservanteNombre'] = trim((string) ($fila['Quien'] ?? ''));
        }

        return $fila;
    }

    public function obtenerCatesActivos($data) {
        $sql = "SELECT a.RolCat,
                       CONCAT(p.Nombre, ' ', p.ApPaterno, ' ', p.ApMaterno) AS DatosCatequistaCoor,
                       p.Contacto AS ContactoCatequista,
                       g.NombreGrupo
                FROM catequista c
                INNER JOIN asignacion a ON a.CiCat = c.CiCat
                INNER JOIN grupo g ON g.IdGrupo = a.IdGrupo
                INNER JOIN persona p ON p.CiPersona = c.CiCat
                WHERE c.Sacramento = ? AND c.EstadoCat = 'Activo'
                ORDER BY g.IdGrupo,
                         FIELD(a.RolCat, 'Coordinador', 'Titular', 'Catequista'),
                         p.ApPaterno, p.ApMaterno, p.Nombre";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $data);
        $stmt->execute();

        $result = $stmt->get_result();
        $catequistas = [];

        if ($result && $result->num_rows > 0) {
            while ($fila = $result->fetch_assoc()) {
                $catequistas[] = $fila;
            }
        }

        return $catequistas;
    }


    public function obtenerCatesInactivos($data) {
        $sql = "SELECT e.ApoyoEst,
                       a.RolCat,
                       CONCAT(p.Nombre, ' ', p.ApPaterno, ' ', p.ApMaterno) AS DatosCatequistaCoor,
                       p.Contacto AS ContactoCatequista,
                       g.NombreGrupo
                FROM catequista c
                INNER JOIN asignacion a ON a.CiCat = c.CiCat
                INNER JOIN grupo g ON g.IdGrupo = a.IdGrupo
                INNER JOIN persona p ON p.CiPersona = c.CiCat
                LEFT JOIN estrellacat e ON e.CiCatEst = c.CiCat
                WHERE c.Sacramento = ? AND c.EstadoCat = 'Inactivo'
                ORDER BY g.IdGrupo,
                         FIELD(a.RolCat, 'Coordinador', 'Titular', 'Catequista'),
                         p.ApPaterno, p.ApMaterno, p.Nombre";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $data);
        $stmt->execute();

        $result = $stmt->get_result();
        $catequistas = [];

        if ($result && $result->num_rows > 0) {
            while ($fila = $result->fetch_assoc()) {
                $catequistas[] = $fila;
            }
        }

        return $catequistas;
    }

    public function obtenerCeleApp($data1, $data2) {
        $sql = "SELECT
                    p_cel.Nombre AS NombreCelebrante,
                    CONCAT(p_cel.ApPaterno, ' ', p_cel.ApMaterno) AS ApellidoCelebrante,
                    d.ParroquiaDoc, d.LibroDoc, d.PaginaDoc, d.PartidaDoc, d.FechaDoc,
                    CONCAT_WS(' y ',
                        (SELECT GROUP_CONCAT(CONCAT(pp.Nombre, ' ', pp.ApPaterno, ' ', IFNULL(pp.ApMaterno, '')) SEPARATOR ' y ')
                         FROM insfamiliar inf
                         INNER JOIN familiar f ON inf.CiFamiliar = f.CiFamiliar
                         INNER JOIN persona pp ON f.CiFamiliar = pp.CiPersona
                         WHERE inf.IdInscripcion = i.IdInscripcion AND inf.RolFam = 'Padrino'),
                        (SELECT GROUP_CONCAT(CONCAT(pmd.Nombre, ' ', pmd.Apellido) SEPARATOR ' y ')
                         FROM padrinocel pmd
                         WHERE pmd.IdInscripcion = i.IdInscripcion AND pmd.RolFam = 'Padrino')
                    ) AS NombrePadrino,
                    CONCAT_WS(' y ',
                        (SELECT GROUP_CONCAT(CONCAT(pm.Nombre, ' ', pm.ApPaterno, ' ', IFNULL(pm.ApMaterno, '')) SEPARATOR ' y ')
                         FROM insfamiliar inf
                         INNER JOIN familiar f ON inf.CiFamiliar = f.CiFamiliar
                         INNER JOIN persona pm ON f.CiFamiliar = pm.CiPersona
                         WHERE inf.IdInscripcion = i.IdInscripcion AND inf.RolFam = 'Madrina'),
                        (SELECT GROUP_CONCAT(CONCAT(pmd2.Nombre, ' ', pmd2.Apellido) SEPARATOR ' y ')
                         FROM padrinocel pmd2
                         WHERE pmd2.IdInscripcion = i.IdInscripcion AND pmd2.RolFam = 'Madrina')
                    ) AS NombreMadrina,
                    p_cel.FechaNac
                FROM celebrante ce
                INNER JOIN inscripcion i ON i.CiCel = ce.CiCel
                INNER JOIN persona p_cel ON p_cel.CiPersona = ce.CiCel
                LEFT JOIN documento d ON d.IdInscripcion = i.IdInscripcion
                    AND d.idRegistro = (
                        SELECT d2.idRegistro
                        FROM documento d2
                        WHERE d2.IdInscripcion = i.IdInscripcion
                        ORDER BY (d2.DetalleDoc = 'Bautizo') DESC, d2.idRegistro ASC
                        LIMIT 1
                    )
                WHERE i.IdGrupo = ? AND ce.EstadoCel = 'Activo'
                ORDER BY p_cel.Nombre, p_cel.ApPaterno, p_cel.ApMaterno";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $data2);
        $stmt->execute();

        $result = $stmt->get_result();
        $celebrantes = [];

        if ($result && $result->num_rows > 0) {
            while ($fila = $result->fetch_assoc()) {
                $celebrantes[] = $fila;
            }
        }

        return $celebrantes;
    }

    public function obtenerCeleAppIn($data1, $data2) {
        $stmt = $this->conn->prepare("CALL DatosGFinalR(?,?)");
        $stmt->bind_param("is", $data2, $data1);
        $stmt->execute();

        $result = $stmt->get_result();
        $celebrantesR = [];
        
        if ($result && $result->num_rows > 0) {
            while ($fila = $result->fetch_assoc()) {
                $celebrantesR[] = $fila;
            }
        }
        
        return $celebrantesR;
    }

    public function obtenerCatApp($data3, $data4) {
        $stmt = $this->conn->prepare("CALL DatosGCats(?, ?)");
        $stmt->bind_param("is", $data4, $data3);
        $stmt->execute();
    
        $result = $stmt->get_result();
        $cates = [];
        
        if ($result && $result->num_rows > 0) {
            while ($fila = $result->fetch_assoc()) {
                $cates[] = $fila;
            }
        }
        
        return $cates;
    }

    public function obtenerNotas($data) {
        $stmt = $this->conn->prepare("CALL MiReporte(?)");
        $stmt->bind_param("s", $data);
        $stmt->execute();

        $result = $stmt->get_result();
        $celeb = [];
        
        if ($result && $result->num_rows > 0) {
            while ($fila = $result->fetch_assoc()) {
                $celeb[] = $fila;
            }
        }
        
        return $celeb;
    }

    public function obtenerAsistencias($data) {
        $stmt = $this->conn->prepare(
            "SELECT FechaAsisConf, DetalleAsis, TipoAsis
             FROM asistenciacel
             WHERE IdInscripcion = ?
             ORDER BY FechaAsisConf, HoraAsisConf"
        );
        $stmt->bind_param("i", $data);
        $stmt->execute();

        $result = $stmt->get_result();
        $celebAsis = [];
        
        if ($result && $result->num_rows > 0) {
            while ($fila = $result->fetch_assoc()) {
                $celebAsis[] = $fila;
            }
        }

        $stmt->close();
        return $celebAsis;
    }

    public function obtenerReuniones($data) {
        $stmt = $this->conn->prepare(
            "SELECT r.FechaReu, aa.DetalleAsis, aa.MontoAsis
             FROM asistenciaapoderado aa
             INNER JOIN reunioncel r ON aa.IdReunion = r.IdReunion
             WHERE aa.IdInscripcion = ?
             ORDER BY r.FechaReu, r.HoraReu"
        );
        $stmt->bind_param("i", $data);
        $stmt->execute();

        $result = $stmt->get_result();
        $celebReu = [];
        
        if ($result && $result->num_rows > 0) {
            while ($fila = $result->fetch_assoc()) {
                $celebReu[] = $fila;
            }
        }

        $stmt->close();
        return $celebReu;
    }
}
?>
