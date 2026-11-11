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
        $query = "UPDATE " . $this->table_name_catequista . " SET ClaveCat = ?, DebeCambiarClave = 0 WHERE CiCat = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $newClave, $this->CiCat);
        return $stmt->execute();
    }

    /** Comprueba la clave actual contra el hash o una clave antigua en texto plano. */
    public function verificarClave($clave) {
        $query = "SELECT ClaveCat FROM " . $this->table_name_catequista . " WHERE CiCat = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("s", $this->CiCat);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $guardada = (string) ($fila['ClaveCat'] ?? '');
        $info = password_get_info($guardada);
        $esHash = (int) ($info['algo'] ?? 0) !== 0;

        return $esHash
            ? password_verify((string) $clave, $guardada)
            : hash_equals($guardada, (string) $clave);
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

    public function obtenerPrivacidad($ciCat = null) {
        $ciCat = $ciCat !== null ? $ciCat : $this->CiCat;
        $default = [
            'MostrarContacto' => 1,
            'MostrarFechaNac' => 1,
            'MostrarEstado' => 1,
            'MostrarDireccion' => 1,
        ];

        $query = "SELECT MostrarContacto, MostrarFechaNac, MostrarEstado, MostrarDireccion
                  FROM privacidad_catequista WHERE CiCat = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            return $default;
        }

        $stmt->bind_param("s", $ciCat);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $fila ?: $default;
    }

    public function guardarPrivacidad($mostrarContacto, $mostrarFechaNac, $mostrarEstado, $mostrarDireccion) {
        $query = "INSERT INTO privacidad_catequista
                    (CiCat, MostrarContacto, MostrarFechaNac, MostrarEstado, MostrarDireccion, ActualizadoEn)
                  VALUES (?, ?, ?, ?, ?, NOW())
                  ON DUPLICATE KEY UPDATE
                    MostrarContacto = VALUES(MostrarContacto),
                    MostrarFechaNac = VALUES(MostrarFechaNac),
                    MostrarEstado = VALUES(MostrarEstado),
                    MostrarDireccion = VALUES(MostrarDireccion),
                    ActualizadoEn = NOW()";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            return false;
        }

        $contacto = $mostrarContacto ? 1 : 0;
        $fechaNac = $mostrarFechaNac ? 1 : 0;
        $estado = $mostrarEstado ? 1 : 0;
        $direccion = $mostrarDireccion ? 1 : 0;

        $stmt->bind_param("siiii", $this->CiCat, $contacto, $fechaNac, $estado, $direccion);
        return $stmt->execute();
    }
}
?>