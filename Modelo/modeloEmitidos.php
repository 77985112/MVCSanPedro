<?php
require_once "../Conexion/Conexion.php";

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

        $sql = "CALL buscarPorFechaCal(?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $fecha, $descripcion);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $certificados = [];
        while ($fila = $result->fetch_assoc()) {
            $certificados[] = $fila;
        }
        return $certificados;
    }
}
?>