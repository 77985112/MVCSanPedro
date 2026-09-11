<?php
require_once '../Conexion/Conexion.php';

class CatequistaModelAsis {
    private $conn;

    public function __construct() {
        $db = new Conexion();
        $this->conn = $db->getConnection();
    }

    public function obtenerCatequistas($ciCat) {
        $stmt = $this->conn->prepare("CALL CapturarCatCoor(?)");
        if (!$stmt) {
            die("Error al preparar la consulta: " . $this->conn->error);
        }
        $stmt->bind_param("s", $ciCat);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $catequistas = $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();
        return $catequistas;
    }

    public function CatequsitasSac($SacramentoCat) {
        $stmt = $this->conn->prepare("CALL CatSacramento(?)");
        if (!$stmt) {
            die("Error al preparar la consulta: " . $this->conn->error);
        }
        $stmt->bind_param("s", $SacramentoCat);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $SacramentoCat = $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();
        return $SacramentoCat;
    }

    public function CatequsitasSacCoor($SacramentoCat) {
        $stmt = $this->conn->prepare("CALL CatSacramentoCoor(?)");
        if (!$stmt) {
            die("Error al preparar la consulta: " . $this->conn->error);
        }
        $stmt->bind_param("s", $SacramentoCat);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $SacramentoCat = $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();
        return $SacramentoCat;
    }
}
?>