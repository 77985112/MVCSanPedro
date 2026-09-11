<?php
class Celebrante {
    private $conn;
    private $table_name = "Celebrante";

    public $CiCel;
    public $Nombre;
    public $ApPaterno;
    public $ApMaterno;
    public $ClaveCel;
    public $PerfilCel;
    public $FondoCel;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function updateImage($column, $fileName) {
        $query = "UPDATE " . $this->table_name . " SET " . $column . " = ? WHERE CiCel = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $fileName, $this->CiCel);
        return $stmt->execute();
    }

    public function updateContact($contact) {
        $query = "UPDATE Persona SET Contacto = ? WHERE CiPersona = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $contact, $this->CiCel);
        return $stmt->execute();
    }


    public function updatePassword($newPassword) {
        $query = "UPDATE " . $this->table_name . " SET ClaveCel = ? WHERE CiCel = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $newPassword, $this->CiCel);
        return $stmt->execute();
    }

    public function deactivateAccount() {
        $query = "UPDATE " . $this->table_name . " SET EstadoCel = 'Inactivo' WHERE CiCel = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $this->CiCel);
        return $stmt->execute();
    }
}
?>
