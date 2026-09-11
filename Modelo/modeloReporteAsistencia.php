<?php
require_once '../Conexion/Conexion.php';

class ReporteAsistenciaModelo {
    private $conexion;
    
    public function __construct() {
        $this->conexion = new Conexion();
    }
    
    public function obtenerCatequistasPorSacramento($sacramento) {
        $conn = $this->conexion->getConnection();
        
        $sql = "SELECT p.CiPersona, p.Nombre, p.ApPaterno, p.ApMaterno, c.Sacramento, 
                a.IdGrupo, g.NombreGrupo, a.RolCat
                FROM persona p
                INNER JOIN catequista c ON p.CiPersona = c.CiCat
                INNER JOIN asignacion a ON c.CiCat = a.CiCat
                INNER JOIN grupo g ON a.IdGrupo = g.IdGrupo
                WHERE c.Sacramento = ? AND c.EstadoCat = 'Activo'
                ORDER BY p.ApPaterno, p.ApMaterno, p.Nombre";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $sacramento);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $catequistas = [];
        while ($row = $result->fetch_assoc()) {
            $catequistas[] = $row;
        }
        
        return $catequistas;
    }
    
    public function obtenerFechasAsistencia($sacramento, $anio) {
        $conn = $this->conexion->getConnection();
        
        $sql = "SELECT DISTINCT ac.FechaAsisCat
                FROM asistenciacat ac
                INNER JOIN catequista c ON ac.CiCat = c.CiCat
                WHERE c.Sacramento = ? AND YEAR(ac.FechaAsisCat) = ?
                ORDER BY ac.FechaAsisCat ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $sacramento, $anio);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $fechas = [];
        while ($row = $result->fetch_assoc()) {
            $fechas[] = $row;
        }
        
        return $fechas;
    }
    
    public function obtenerAsistenciasCatequistas($sacramento, $anio) {
        $conn = $this->conexion->getConnection();
        
        $sql = "SELECT ac.CiCat, ac.FechaAsisCat, ac.DetalleAsis
                FROM asistenciacat ac
                INNER JOIN catequista c ON ac.CiCat = c.CiCat
                WHERE c.Sacramento = ? AND YEAR(ac.FechaAsisCat) = ?
                ORDER BY ac.CiCat, ac.FechaAsisCat";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $sacramento, $anio);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $asistencias = [];
        while ($row = $result->fetch_assoc()) {
            $asistencias[] = $row;
        }
        
        return $asistencias;
    }
}
?>