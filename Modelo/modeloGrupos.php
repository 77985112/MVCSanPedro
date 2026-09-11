<?php
require_once '../Conexion/Conexion.php';

class GrupoModel {
    private $conn;

    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->getConnection();
    }

    public function DatosGrupito($idGrIn) {
        $sql = "CALL Listado_Grupos(?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idGrIn);
        $stmt->execute();
        $result = $stmt->get_result();
    
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getDatosPorSacramento($inicio, $fin) {
        $sql = "
            SELECT 
                g.IdGrupo, 
                g.NombreGrupo,
                g.Color_Grupo,
                (SELECT COUNT(*) FROM inscripcion i WHERE i.IdGrupo = g.IdGrupo) as totalInscripciones,
                (SELECT COUNT(*) FROM asignacion a WHERE a.IdGrupo = g.IdGrupo) as totalAsignaciones
            FROM grupo g 
            WHERE g.IdGrupo BETWEEN $inicio AND $fin
        ";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerGrupoPorId($idGrupo) {
        $sql = "SELECT * FROM grupo WHERE IdGrupo = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idGrupo);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc();
    }
    
    public function actualizarGrupo($idGrupo, $nombre, $color, $santo, $imagen, $frase) {
        
        $sql = "UPDATE grupo SET NombreGrupo = ?, Color_Grupo = ?, Santo_Grupo = ?, Imagen_Grupo = ?, Frase_Santo = ? WHERE IdGrupo = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssi", $nombre, $color, $santo, $imagen, $frase, $idGrupo);
        return $stmt->execute();
    }
    
    public function obtenerCatequistas($idGrupo) {
        $sql = "
            SELECT 
                c.CiCat,
                CONCAT(p.Nombre, ' ', p.ApPaterno, ' ', COALESCE(p.ApMaterno, '')) AS NombreCompleto,
                a.RolCat AS Rol,
                c.EstadoCat AS Estado
            FROM 
                asignacion a
            JOIN 
                catequista c ON a.CiCat = c.CiCat
            JOIN 
                persona p ON c.CiCat = p.CiPersona
            WHERE 
                a.IdGrupo = ?
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idGrupo);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerCelebrantes($idGrupo) {
        $sql = "
            SELECT i.IdInscripcion,
                c.CiCel,
                CONCAT(p.Nombre, ' ', p.ApPaterno, ' ', COALESCE(p.ApMaterno, '')) AS NombreCompleto,
                ti.DescripItem AS DescriptItem,
                c.EstadoCel AS Estado
            FROM 
                inscripcion i
            INNER JOIN 
                celebrante c ON i.CiCel = c.CiCel
            INNER JOIN 
                persona p ON c.CiCel = p.CiPersona
            INNER JOIN 
                detalletipoins dti ON i.IdInscripcion = dti.IdInscripcion
            INNER JOIN
            	tipoins ti ON dti.CodTipoItem = ti.CodTipoItem
            WHERE 
                i.IdGrupo = ?
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idGrupo);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }


}
?>
