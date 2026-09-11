<?php
include_once '../Conexion/Conexion.php';

class Usuario {
    private $conn;
    private $table_name_catequista = "catequista";
    private $table_name_persona = "Persona";

    public $CiCat;
    public $ImagenCat;
    public $FondoCat;
    public $FraseCat;
    public $ClaveCat;
    
    public $CiPersona;
    public $Contacto;
    public $Estado_per;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function updateImage($column, $fileName) {
        $query = "UPDATE " . $this->table_name_catequista . " SET " . $column . " = ? WHERE CiCat = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $fileName, $this->CiCat);
        return $stmt->execute();
    }

    public function updateFrase($frase) {
        $query = "UPDATE " . $this->table_name_catequista . " SET FraseCat = ? WHERE CiCat = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $frase, $this->CiCat);
        return $stmt->execute();
    }

    public function updateClave($newClave) {
        $query = "UPDATE " . $this->table_name_catequista . " SET ClaveCat = ? WHERE CiCat = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $newClave, $this->CiCat);
        return $stmt->execute();
    }

    public function updateContacto($contacto) {
        $query = "UPDATE " . $this->table_name_persona . " SET Contacto = ? WHERE CiPersona = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $contacto, $this->CiPersona);
        return $stmt->execute();
    }

    public function updateEstado($estado) {
        $query = "UPDATE " . $this->table_name_persona . " SET Estado_per = ? WHERE CiPersona = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $estado, $this->CiPersona);
        return $stmt->execute();
    }
}
?>