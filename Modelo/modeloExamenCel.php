<?php
require_once '../Conexion/Conexion.php';

class CelebranteExamen {
    private $conn;

    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->getConnection();
    }

    // Obtener celebrantes con sus notas y promedios según sacramento e id de grupo
    public function getCelebrantes($sacramento, $idGrupo) {
        $sql = "
            SELECT 
            
                cx.IdCelEx,
                p.Nombre, p.ApPaterno, p.ApMaterno,
                cx.NotaExamen1, cx.NotaExamen2, cx.NotaExamen3, cx.NotaExamen4,
                (cx.NotaExamen1 + cx.NotaExamen2 + cx.NotaExamen3 + cx.NotaExamen4) / 4 as Promedio
            FROM celebrante c
            INNER JOIN persona p ON c.CiCel = p.CiPersona
            INNER JOIN inscripcion i ON c.CiCel = i.CiCel
            INNER JOIN CeleExamen cx ON i.IdInscripcion = cx.IdInscripcion
            WHERE c.Sacramento = ? AND i.IdGrupo = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $sacramento, $idGrupo);
        $stmt->execute();
        $result = $stmt->get_result();

        $celebrantes = array();
        while ($row = $result->fetch_assoc()) {
            $celebrantes[] = $row;
        }

        return $celebrantes;
    }

    // Asignar o actualizar una nota de examen
    public function actualizarNota($idCelEx, $numNota, $nota) {
        $campoNota = "NotaExamen" . $numNota;
        $sql = "UPDATE CeleExamen SET $campoNota = ? WHERE IdCelEx = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $nota, $idCelEx);
        return $stmt->execute();
    }

    // Obtener los exámenes registrados
    public function getExamenes() {
        $sql = "SELECT * FROM Examen";
        $result = $this->conn->query($sql);

        $examenes = array();
        while ($row = $result->fetch_assoc()) {
            $examenes[] = $row;
        }

        return $examenes;
    }
}
?>