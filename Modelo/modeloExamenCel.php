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
        $cantidadNotas = (stripos((string) $sacramento, 'Confirmación') !== false) ? 3 : 2;
        $expresionPromedio = $cantidadNotas === 3
            ? "(cx.NotaExamen1 + cx.NotaExamen2 + cx.NotaExamen3) / 3"
            : "(cx.NotaExamen1 + cx.NotaExamen2) / 2";

        $sql = "
            SELECT 
            
                cx.IdCelEx,
                p.Nombre, p.ApPaterno, p.ApMaterno,
                cx.NotaExamen1, cx.NotaExamen2, cx.NotaExamen3, cx.NotaExamen4,
                $expresionPromedio as Promedio
            FROM `celebrante` c
            INNER JOIN `persona` p ON c.CiCel = p.CiPersona
            INNER JOIN inscripcion i ON c.CiCel = i.CiCel
            INNER JOIN `celeexamen` cx ON i.IdInscripcion = cx.IdInscripcion
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
        $numNota = (int) $numNota;
        $nota = (int) $nota;
        if ($numNota < 1 || $nota < 0 || $nota > 100) {
            return false;
        }

        $sqlSacramento = "SELECT c.Sacramento
                          FROM `celeexamen` cx
                          INNER JOIN inscripcion i ON i.IdInscripcion = cx.IdInscripcion
                          INNER JOIN celebrante c ON c.CiCel = i.CiCel
                          WHERE cx.IdCelEx = ? LIMIT 1";
        $stmt = $this->conn->prepare($sqlSacramento);
        $stmt->bind_param("i", $idCelEx);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$fila) {
            return false;
        }

        // Primera Comunión: 2 bimestres. Confirmación: 3 bimestres.
        $maxNotas = (stripos((string) $fila['Sacramento'], 'Confirmación') !== false) ? 3 : 2;
        if ($numNota > $maxNotas) {
            return false;
        }

        $campoNota = "NotaExamen" . $numNota;
        $sql = "UPDATE `celeexamen` SET $campoNota = ? WHERE IdCelEx = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $nota, $idCelEx);
        return $stmt->execute();
    }
}
?>