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
        $stmt = $this->conn->prepare("CALL CatsActCoor(?)");
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
        $stmt = $this->conn->prepare("CALL CatsInCoor(?)");
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
        $stmt = $this->conn->prepare("CALL DatosGFinal(?,?)");
        $stmt->bind_param("is", $data2, $data1);
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
        $stmt = $this->conn->prepare("CALL MiAsistencia(?)");
        $stmt->bind_param("s", $data);
        $stmt->execute();

        $result = $stmt->get_result();
        $celebAsis = [];
        
        if ($result && $result->num_rows > 0) {
            while ($fila = $result->fetch_assoc()) {
                $celebAsis[] = $fila;
            }
        }
        
        return $celebAsis;
    }

    public function obtenerReuniones($data) {
        $stmt = $this->conn->prepare("CALL MiReunion(?)");
        $stmt->bind_param("s", $data);
        $stmt->execute();

        $result = $stmt->get_result();
        $celebReu = [];
        
        if ($result && $result->num_rows > 0) {
            while ($fila = $result->fetch_assoc()) {
                $celebReu[] = $fila;
            }
        }
        
        return $celebReu;
    }
}
?>
