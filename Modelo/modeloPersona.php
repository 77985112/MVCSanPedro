<?php
require_once '../conexion/Conexion.php';

class Persona {
    private $conn;

    public function __construct() {
        $this->conn = (new Conexion())->getConnection();
    }

    public function crear($ciPersona, $nombre, $apPaterno, $apMaterno, $sexo, $fechaNac, $direccion, $contacto, $estado, $certificado = '') {
        $sql = "SELECT CiPersona FROM persona WHERE CiPersona = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $ciPersona);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return true;
        }
        
        $sql = "INSERT INTO persona (CiPersona, Nombre, ApPaterno, ApMaterno, Sexo, FechaNac, Direccion, Contacto, Estado_per, id_certificado_nacimiento) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssssssss", $ciPersona, $nombre, $apPaterno, $apMaterno, $sexo, $fechaNac, $direccion, $contacto, $estado, $certificado);
        return $stmt->execute();
    }

    public function buscar($ciPersona) {
        // Dar prioridad al CI exacto al volver del registro de una persona.
        $stmt = $this->conn->prepare('SELECT * FROM persona WHERE CiPersona = ? OR id_certificado_nacimiento LIKE ? ORDER BY (CiPersona = ?) DESC LIMIT 1');
        $certificado = '%' . $ciPersona . '%';
        $stmt->bind_param('sss', $ciPersona, $certificado, $ciPersona);
        $stmt->execute();
        $persona = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $persona;
    }

    public function actualizar($ciPersona, $nombre, $apPaterno, $apMaterno, $sexo, $fechaNac, $direccion, $contacto, $estado) {
        $sql = "UPDATE persona SET 
                Nombre = '$nombre', 
                ApPaterno = '$apPaterno', 
                ApMaterno = '$apMaterno', 
                Sexo = '$sexo', 
                FechaNac = '$fechaNac', 
                Direccion = '$direccion', 
                Contacto = '$contacto', 
                Estado_per = '$estado' 
                WHERE CiPersona = '$ciPersona'";
        return $this->conn->query($sql);
    }
}
?>
