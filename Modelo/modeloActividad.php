<?php
require_once '../conexion/Conexion.php';

class Actividad
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Conexion())->getConnection();
    }

    public function obtenerActividades($tipo = null)
    {
        if ($tipo) {
            $stmt = $this->conn->prepare("CALL ObtenerActividadesPorTipo(?)");
            $stmt->bind_param("s", $tipo);
        } else {
            $stmt = $this->conn->prepare("SELECT * FROM actividades");
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function registrarActividad($data)
    {
        $stmt = $this->conn->prepare("CALL InsertarActividad(?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param(
            "ssssssss",
            $data['TituloActividad'],
            $data['FechaActividad'],
            $data['DetalleActividad'],
            $data['ImagenActividad'],
            $data['LugarAct'],
            $data['HoraAct'],
            $data['CiCat'],
            $data['TipoActividad']
        );

        if ($stmt->execute()) {
            return true;
        } else {
            throw new Exception("Error al insertar la actividad en la base de datos: " . $stmt->error);
        }
    }

    public function actualizarActividad($data)
    {
        $stmt = $this->conn->prepare("CALL ActualizarActividad(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "isssssssss",
            $data['idActividad'],
            $data['TituloActividad'],
            $data['FechaActividad'],
            $data['DetalleActividad'],
            $data['ImagenActividad'],
            $data['LugarAct'],
            $data['HoraAct'],
            $data['CiCat'],
            $data['TipoActividad'],
            $data['EstActividad']
        );

        return $stmt->execute();
    }
}
