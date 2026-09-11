<?php
require_once '../Conexion/Conexion.php';

class ModeloLogin {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }

    public function getPersonalData($username, $password) {
        $conn = $this->conexion->getConnection();
        $stmt = $conn->prepare("CALL sp_login_personal(?, ?)");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        $conn->close();
        return $data;
    }

    public function getCatequistaData($username, $password) {
        $conn = $this->conexion->getConnection();
        $stmt = $conn->prepare("CALL sp_login_catequista(?, ?)");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        $conn->close();
        return $data;
    }

    public function getCelebranteData($username, $password) {
        $conn = $this->conexion->getConnection();
        $stmt = $conn->prepare("CALL sp_login_celebrante(?, ?)");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        $conn->close();
        return $data;
    }
}
?>