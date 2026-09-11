<?php
require_once '../Conexion/Conexion.php';

class Modelo {
    private $conn;

    public function __construct() {
        $db = new Conexion();
        $this->conn = $db->getConnection();
    }

    public function obtenerCatequistasPorGrupo($IdGrupo) {
        $sql = "SELECT concat (p.Nombre,' ', p.ApPaterno,' ', p.ApMaterno) AS NombreCompleto, a.RolCat, c.CiCat, c.ImagenCat
                FROM catequista c
                INNER JOIN persona p ON c.CiCat = p.CiPersona 
                INNER JOIN asignacion a ON c.CiCat = a.CiCat
                WHERE a.IdGrupo = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $IdGrupo);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerCelebrantesPorGrupo($IdGrupo) {
        $sql = "SELECT concat (p.Nombre,' ', p.ApPaterno,' ', p.ApMaterno) AS NombreCompleto, tins.DescripItem, ce.PerfilCel, ce.CiCel
                FROM celebrante ce
                INNER JOIN persona p ON ce.CiCel = p.CiPersona
                INNER JOIN inscripcion ins ON ce.CiCel = ins.CiCel
                INNER JOIN detalletipoins dtins ON dtins.IdInscripcion = ins.IdInscripcion
                INNER JOIN TipoIns tins ON tins.CodTipoItem = dtins.CodTipoItem 
                WHERE ins.IdGrupo = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $IdGrupo);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerAsistenciasCatequesis($IdInscripcion) {
        $sql = "SELECT FechaAsisConf, HoraAsisConf, DetalleAsis, TipoAsis FROM asistenciacel WHERE IdInscripcion = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $IdInscripcion);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerAsistenciasApoderados($IdInscripcion) {
        $sql = "SELECT r.FechaReu, r.HoraReu, a.DetalleAsis, a.MontoAsis 
                FROM asistenciaapoderado a
                INNER JOIN ReunionCel r ON a.IdReunion = r.IdReunion
                WHERE a.IdInscripcion = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $IdInscripcion);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerApoderadosYPadrinos($IdInscripcion) {
        $sql = "SELECT p.Nombre, p.ApPaterno, p.ApMaterno, f.Parentesco, f.RolFam 
                FROM insfamiliar f
                INNER JOIN persona p ON f.CiFamiliar = p.CiPersona
                WHERE f.IdInscripcion = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $IdInscripcion);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerDocumentosPresentados($IdInscripcion) {
        $sql = "SELECT NumeroDoc, DetalleDoc, LibroDoc, PaginaDoc, PartidaDoc, ParroquiaDoc, FechaPres 
                FROM Documento 
                WHERE IdInscripcion = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $IdInscripcion);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>
