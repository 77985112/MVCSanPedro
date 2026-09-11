<?php
require_once '../conexion/Conexion.php';

class Personal
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Conexion())->getConnection();
    }

    public function registrarNuevoPersonal($data)
    {
        if (strtolower($data['Sexo']) === 'mujer' && in_array($data['CodCar'], [2, 3])) {
            $data['CodCar'] = 1;
        }
        $stmt = $this->conn->prepare("CALL RegistrarNuevoPersonal(?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "ssssssi",
            $data['Nombre'],
            $data['Paterno'],
            $data['Materno'],
            $data['CiPersonal'],
            $data['Sexo'],
            $data['ContactoPer'],
            $data['CodCar']
        );

        return $stmt->execute();
    }

    public function buscarPersonalPorCi($ci)
    {
        $stmt = $this->conn->prepare("CALL BuscarPersonalPorCi(?)");
        $stmt->bind_param("s", $ci);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function PersonalDemas($ciPersonal)
    {
        $stmt = $this->conn->prepare("CALL capturarPersonalExt(?)");
        $stmt->bind_param("s", $ciPersonal);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function modificarPersonalPorCi($data)
    {
        $stmt = $this->conn->prepare("CALL ModificarPersonalPorCi(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "sssssssssssi",
            $data['CiPersonal'],
            $data['ClavePer'],
            $data['Nombre'],
            $data['Paterno'],
            $data['Materno'],
            $data['Sexo'],
            $data['ContactoPer'],
            $data['PerfilPer'],
            $data['FondoPer'],
            $data['FrasePer'],
            $data['Estado'],
            $data['CodCar_2']
        );

        if (!$stmt->execute()) {
            echo "Error en la ejecución: " . $stmt->error;
            return false;
        }

        return true;
    }
}
?>