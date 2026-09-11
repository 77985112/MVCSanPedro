<?php
require_once '../Conexion/Conexion.php';

class UsuarioModel {
    private $conn;

    public function __construct() {
        $db = new Conexion();
        $this->conn = $db->getConnection();
    }

    public function actualizarImagen($ciPersonal, $campoImagen, $nombreArchivo) {
        $sql = "UPDATE personal SET $campoImagen = ? WHERE CiPersonal = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            echo "Error en la preparación: " . $this->conn->error;
            return false;
        }
        $stmt->bind_param("ss", $nombreArchivo, $ciPersonal);
        return $stmt->execute();
    }

    public function actualizarFrase($ciPersonal, $frase) {
        $sql = "UPDATE personal SET FrasePer = ? WHERE CiPersonal = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $frase, $ciPersonal);
        return $stmt->execute();
    }

    public function actualizarContacto($ciPersonal, $contacto) {
        $sql = "UPDATE personal SET ContactoPer = ? WHERE CiPersonal = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $contacto, $ciPersonal);
        return $stmt->execute();
    }

    public function actualizarContrasena($ciPersonal, $nuevaContrasena) {
        $sql = "UPDATE personal SET ClavePer = ? WHERE CiPersonal = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $nuevaContrasena, $ciPersonal);
        return $stmt->execute();
    }

    public function eliminarCuenta($ciPersonal) {
        $sql = "UPDATE personal SET Estado = 'Inactivo' WHERE CiPersonal = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $ciPersonal);
        return $stmt->execute();
    }

    public function obtenerUsuarioPorCI($ciPersonal) {
        $sql = "SELECT * FROM personal WHERE CiPersonal = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $ciPersonal);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
?>
