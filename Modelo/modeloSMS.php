
<?php
require_once '../Conexion/Conexion.php';

class Mensaje {

    private $conn;
    
    public function __construct() {
        $database = new Conexion();
        $this->conn = $database->getConnection();
    }

    public function insertarMensaje($nombre, $numCorr, $concepto, $detalle) {
        $sql = "INSERT INTO mensajepublico (NombreMensaje, NumCorrMensaje, ConceptoMensaje, DetalleMensaje, FechaMensaje, EstadoMensaje) 
                VALUES (?, ?, ?, ?, NOW(), 'Pendiente')";

        $stmt = $this->conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ssss", $nombre, $numCorr, $concepto, $detalle);
            return $stmt->execute();
        }
        return false;
    }

    public function obtenerMensajes() {
        $sql = "SELECT * FROM mensajepublico";
        $result = $this->conn->query($sql);
        return $result;
    }

    public function obtenerMensajePorId($id) {
        $sql = "SELECT * FROM mensajepublico WHERE idMensaje = ?";
        $stmt = $this->conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        }
        return null;
    }

    public function marcarComoLeido($id) {
        $sql = "UPDATE mensajepublico SET EstadoMensaje = 'Leído', FechaLeido = NOW() WHERE idMensaje = ?";
        $stmt = $this->conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $id);
            return $stmt->execute();
        }
        return false;
    }
}
?>