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
        $query = "UPDATE `persona` SET Contacto = ? WHERE CiPersona = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $contact, $this->CiCel);
        return $stmt->execute();
    }


    public function updatePassword($newPassword) {
        $query = "UPDATE " . $this->table_name . " SET ClaveCel = ?, DebeCambiarClave = 0 WHERE CiCel = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $newPassword, $this->CiCel);
        return $stmt->execute();
    }

    /** Comprueba la clave actual contra el hash o una clave antigua en texto plano. */
    public function verifyPassword($clave) {
        $query = "SELECT ClaveCel FROM " . $this->table_name . " WHERE CiCel = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("s", $this->CiCel);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $guardada = (string) ($fila['ClaveCel'] ?? '');
        $info = password_get_info($guardada);
        $esHash = (int) ($info['algo'] ?? 0) !== 0;

        return $esHash
            ? password_verify((string) $clave, $guardada)
            : hash_equals($guardada, (string) $clave);
    }

    public function deactivateAccount() {
        $query = "UPDATE " . $this->table_name . " SET EstadoCel = 'Inactivo' WHERE CiCel = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $this->CiCel);
        return $stmt->execute();
    }
}
?>
