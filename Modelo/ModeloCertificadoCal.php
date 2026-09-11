<?php
require_once '../Conexion/Conexion.php';

class CertificadoModel {
    private $db;

    public function __construct() {
        $this->db = (new Conexion())->getConnection();
    }

    public function obtenerCertificadosPorFecha($fecha) {
        $query = "SELECT car.DescripCar, c.NroCertificado, c.NroEmision, c.FechaEmision, c.EstadoCert, 
                         p.Nombre, p.Paterno, p.Materno, ts.DescripSac
                  FROM certificado c
                  JOIN inssacramento i ON c.CodIns = i.CodIns
                  JOIN personal p ON i.CodPer = p.CodPer
                  JOIN tiposacramento ts ON i.CodSac = ts.CodSac
                  JOIN Cargo car ON car.CodCar = p.CodCar
                  WHERE c.FechaEmision = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $fecha);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }


    public function getReservasByMes($mes, $year) {
        $sql = "SELECT DAY(c.FechaEmision) AS dia 
                FROM certificado c 
                WHERE MONTH(c.FechaEmision) = ? AND YEAR(c.FechaEmision) = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ii', $mes, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $diasReservados = $result->fetch_all(MYSQLI_ASSOC);
    
        $stmt->close();
        return $diasReservados;
    }


    public function obtenerFechasConCertificados() {
        $query = "SELECT DATE(c.FechaEmision) AS Fecha 
                  FROM certificado c 
                  GROUP BY DATE(c.FechaEmision)";
        
        $result = $this->db->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>