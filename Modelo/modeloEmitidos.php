<?php
require_once __DIR__ . "/../Conexion/Conexion.php";

class CertificadoModelo
{

    private $conn;

    public function __construct()
    {
        $conexion = new Conexion();
        $this->conn = $conexion->getConnection();
    }

    public function obtenerTodosCertificados()
    {
        $sql = "SELECT 
                    c.NroEmision,
                    ts.DescripSac,
                    c.FechaEmision,
                    c.PresbiteroEm,
                    c.EstadoCert
                FROM 
                    certificado c
                INNER JOIN 
                    inssacramento ins ON c.CodIns = ins.CodIns
                INNER JOIN 
                    tiposacramento ts ON ins.CodSac = ts.CodSac";
        $result = $this->conn->query($sql);
        return $result;
    }


    public function buscarPorFechaEmision($fecha)
    {
        $sql = "CALL BuscarPorFechaEmision(?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $fecha);
        $stmt->execute();
        return $stmt->get_result();
    }


    public function buscarPorFechaCal($fecha, $descripcion) {
        if (!is_string($fecha) || !preg_match('/^\d{4}-\d{2}-\d{2}$/D', $fecha)) {
            throw new InvalidArgumentException('Seleccione una fecha válida para generar el reporte.');
        }
        $fechaSeleccionada = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
        if (!$fechaSeleccionada || $fechaSeleccionada->format('Y-m-d') !== $fecha
            || (int) $fechaSeleccionada->format('Y') < 1000) {
            throw new InvalidArgumentException('Seleccione una fecha válida para generar el reporte.');
        }
        if (!in_array($descripcion, ['todos', 'mes'], true)) {
            throw new InvalidArgumentException('Seleccione un reporte diario o mensual.');
        }

        // "todos" conserva el enlace del reporte diario: todos los sacramentos de ese día.
        $inicio = $descripcion === 'mes'
            ? $fechaSeleccionada->modify('first day of this month')
            : $fechaSeleccionada;
        $fin = $inicio->modify($descripcion === 'mes' ? '+1 month' : '+1 day');
        $desde = $inicio->format('Y-m-d');
        $hasta = $fin->format('Y-m-d');

        $sql = "SELECT c.EstadoCert, ts.DescripSac, c.NroEmision, c.FechaEmision,
                       car.DescripCar,
                       CONCAT_WS(' ', p.Nombre, p.Paterno, NULLIF(p.Materno, '')) AS NombreEmisor
                FROM certificado c
                INNER JOIN inssacramento ins ON c.CodIns = ins.CodIns
                INNER JOIN tiposacramento ts ON ins.CodSac = ts.CodSac
                INNER JOIN `personal` p ON ins.CodPer = p.CodPer
                INNER JOIN `cargo` car ON p.CodCar = car.CodCar
                WHERE c.FechaEmision >= ? AND c.FechaEmision < ?
                ORDER BY c.FechaEmision, c.NroCertificado";
        $stmt = $this->conn->prepare($sql);
        try {
            $stmt->bind_param("ss", $desde, $hasta);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } finally {
            $stmt->close();
        }
    }
}
?>
