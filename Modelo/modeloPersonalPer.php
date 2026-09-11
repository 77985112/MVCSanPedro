<?php
require_once '../Conexion/Conexion.php';

class ModPersonal
{
    private $conn;

    public function __construct()
    {
        $conexion = new Conexion();
        $this->conn = $conexion->getConnection();
    }

    public function getPersonal()
    {
        $sql = "SELECT per.*, CONCAT(per.Nombre, ' ', per.Paterno, ' ', per.Materno) AS NombreCompleto, car.DescripCar
                FROM Personal per
                INNER JOIN cargo car ON car.CodCar = per.CodCar
                WHERE per.Estado = 'Activo'";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();

        $PersonalActivo = [];
        while ($row = $result->fetch_assoc()) {
            $PersonalActivo[] = $row;
        }

        $stmt->close();
        return $PersonalActivo;
    }

    public function PerfildePersonal($CiPer)
    {
        $sql = "SELECT per.*, CONCAT(per.Nombre, ' ', per.Paterno, ' ', per.Materno) AS NombreCompleto, car.DescripCar
                FROM Personal per
                INNER JOIN cargo car ON car.CodCar = per.CodCar
                WHERE per.Estado = 'Activo' AND per.CiPersonal=?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $CiPer);
        
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
