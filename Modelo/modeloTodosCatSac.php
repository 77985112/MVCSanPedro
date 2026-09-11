<?php
require_once '../Conexion/Conexion.php';

class CatequistaSac {
    private $conn;

    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->getConnection();
    }

    public function getCatequistasComunion() {
        $sql = "SELECT catequista.CiCat, CONCAT(persona.Nombre, ' ', persona.ApPaterno, ' ', persona.ApMaterno) AS NombreCompleto, catequista.ImagenCat, catequista.Sacramento, asig.RolCat, gr.NombreGrupo
                FROM catequista 
                INNER JOIN persona ON catequista.CiCat = persona.CiPersona
                INNER JOIN Asignacion asig ON catequista.CiCat = asig.CiCat
                INNER JOIN Grupo gr ON asig.IdGrupo = gr.IdGrupo
                WHERE catequista.EstadoCat = 'Activo' AND catequista.Sacramento = 'Primera Comunión'";
                
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();
                $result = $stmt->get_result();
        
                $catequistasCom = [];
                while ($row = $result->fetch_assoc()) {
                    $catequistasCom[] = $row;
                }
        
                $stmt->close();
                return $catequistasCom;
    }

    public function getCatequistasConfirmacion() {
        $sql = "SELECT catequista.CiCat, CONCAT(persona.Nombre, ' ', persona.ApPaterno, ' ', persona.ApMaterno) AS NombreCompleto, catequista.ImagenCat, catequista.Sacramento, asig.RolCat, gr.NombreGrupo
                FROM catequista 
                INNER JOIN persona ON catequista.CiCat = persona.CiPersona
                INNER JOIN Asignacion asig ON catequista.CiCat = asig.CiCat
                INNER JOIN Grupo gr ON asig.IdGrupo = gr.IdGrupo
                WHERE catequista.EstadoCat = 'Activo' AND catequista.Sacramento = 'Confirmación'";
                
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();
                $result = $stmt->get_result();
        
                $catequistasConf = [];
                while ($row = $result->fetch_assoc()) {
                    $catequistasConf[] = $row;
                }
        
                $stmt->close();
                return $catequistasConf;
    }
    
}
?>
