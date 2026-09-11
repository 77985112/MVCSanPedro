<?php
require_once 'Conexion/Conexion.php';

class CatequistaModel {
    private $conn;

    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->getConnection();
    }

    public function getActCatequistas() {
        $sql = "SELECT catequista.CiCat, CONCAT(persona.Nombre, ' ', persona.ApPaterno, ' ', persona.ApMaterno) AS NombreCompleto, catequista.ImagenCat, catequista.Sacramento 
                FROM catequista 
                INNER JOIN persona ON catequista.CiCat = persona.CiPersona
                WHERE catequista.EstadoCat = 'Activo'
                ORDER BY RAND()
                LIMIT 10";
                
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();
                $result = $stmt->get_result();
        
                $catequistas = [];
                while ($row = $result->fetch_assoc()) {
                    $catequistas[] = $row;
                }
        
                $stmt->close();
                return $catequistas;
    }

    public function buscarCatequista($ciCat) {
        $sql = "SELECT catequista.CiCat, CONCAT(persona.Nombre, ' ', persona.ApPaterno, ' ', persona.ApMaterno) AS NombreCompleto, catequista.ImagenCat, catequista.Sacramento 
        FROM catequista 
        INNER JOIN persona ON catequista.CiCat = persona.CiPersona
        WHERE catequista.CiCat = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $ciCat);
        
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function buscarCelebrante($ciCel, $SacramentoCel)
    {
        $sql = "
            SELECT c.*, CONCAT(p.Nombre, ' ', p.ApPaterno, ' ', p.ApMaterno) AS NombreCompleto,
                   ins.*, g.NombreGrupo, ti.DescripItem, ti.Valor, ti.CodTipoItem
            FROM celebrante c
            INNER JOIN persona p ON p.CiPersona = c.CiCel
            INNER JOIN inscripcion ins ON ins.CiCel = c.CiCel
            INNER JOIN grupo g ON g.IdGrupo = ins.IdGrupo
            LEFT JOIN detalleTipoins dti ON dti.IdInscripcion = ins.IdInscripcion
            LEFT JOIN TipoIns ti ON ti.CodTipoItem = dti.CodTipoItem
            WHERE c.CiCel = ? AND c.Sacramento = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $ciCel, $SacramentoCel);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
?>
