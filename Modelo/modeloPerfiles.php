<?php
require_once '../Conexion/Conexion.php';

class modeloSacramentoPer {
    private $conn;

    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->getConnection();
    }

    public function buscarPerfilCatequista($ciCat) {
        $sql = "SELECT catequista.*, asig.*, gr.*, persona.Contacto, persona.FechaNac, CONCAT(persona.Nombre, ' ', persona.ApPaterno, ' ', persona.ApMaterno) AS NombreCompleto 
        FROM catequista 
        INNER JOIN persona ON catequista.CiCat = persona.CiPersona
        INNER JOIN asignacion asig ON asig.CiCat = catequista.CiCat
        INNER JOIN grupo gr ON gr.IdGrupo = asig.IdGrupo
        WHERE catequista.CiCat = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $ciCat);
        
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function buscarPerfilCelebrante($ciCel, $SacramentoCel)
    {
        $sql = "
            SELECT c.*, CONCAT(p.Nombre, ' ', p.ApPaterno, ' ', p.ApMaterno) AS NombreCompleto, p.Contacto, p.FechaNac,
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
