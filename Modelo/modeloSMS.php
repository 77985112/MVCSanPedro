
<?php
require_once '../Conexion/Conexion.php';

class Mensaje {

    private $conn;
    
    public function __construct() {
        $database = new Conexion();
        $this->conn = $database->getConnection();
    }

    public function insertarMensaje($nombre, $numCorr, $concepto, $detalle) {
        $sql = "INSERT INTO guardarconsulta (NombreMensaje, NumCorrMensaje, ConceptoMensaje, DetalleMensaje, FechaMensaje, EstadoMensaje) 
                VALUES (?, ?, ?, ?, NOW(), 'Pendiente')";

        $stmt = $this->conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ssss", $nombre, $numCorr, $concepto, $detalle);
            return $stmt->execute();
        }
        return false;
    }

    public function obtenerMensajes($estado = null) {
        if ($estado === 'Pendiente' || $estado === 'Leído') {
            $sql = "SELECT * FROM guardarconsulta WHERE EstadoMensaje = ? ORDER BY FechaMensaje DESC, idMensaje DESC";
            $stmt = $this->conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("s", $estado);
                $stmt->execute();
                return $stmt->get_result();
            }
            return null;
        }

        $sql = "SELECT * FROM guardarconsulta ORDER BY FechaMensaje DESC, idMensaje DESC";
        return $this->conn->query($sql);
    }

    public function contarMensajes($estado = null) {
        if ($estado === 'Pendiente' || $estado === 'Leído') {
            $sql = "SELECT COUNT(*) AS total FROM guardarconsulta WHERE EstadoMensaje = ?";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                return 0;
            }
            $stmt->bind_param("s", $estado);
            $stmt->execute();
            $total = (int) $stmt->get_result()->fetch_assoc()['total'];
            $stmt->close();
            return $total;
        }

        $result = $this->conn->query("SELECT COUNT(*) AS total FROM guardarconsulta");
        return $result ? (int) $result->fetch_assoc()['total'] : 0;
    }

    public function eliminarMensaje($id) {
        $sql = "DELETE FROM guardarconsulta WHERE idMensaje = ?";
        $stmt = $this->conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $id);
            return $stmt->execute();
        }
        return false;
    }

    public function obtenerMensajePorId($id) {
        $sql = "SELECT * FROM guardarconsulta WHERE idMensaje = ?";
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
        $sql = "UPDATE guardarconsulta SET EstadoMensaje = 'Leído', FechaLeido = NOW() WHERE idMensaje = ?";
        $stmt = $this->conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $id);
            return $stmt->execute();
        }
        return false;
    }
}
?>