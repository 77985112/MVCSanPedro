<?php
require_once '../Conexion/Conexion.php';

class GrupoModel {
    private $conn;

    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->getConnection();
    }

    public function DatosGrupito($idGrIn) {
        $sql = "SELECT DISTINCT
                    p.Nombre AS nombrecel,
                    CONCAT_WS(' ', p.ApPaterno, NULLIF(p.ApMaterno, '')) AS ApellidoCel,
                    TIMESTAMPDIFF(YEAR, p.FechaNac, CURDATE()) AS Edad,
                    DATE_FORMAT(p.FechaNac, '%d/%m/%Y') AS FechaNac,
                    COALESCE(p.NumeroDocumento, '') AS FechaDoc,
                    COALESCE(NULLIF(p.Contacto, ''), p.TelefonoAlternativo, '') AS ContacTutor,
                    p.Sexo,
                    ti.DescripItem AS Sacramento,
                    g.SacramentoGrupo,
                    COALESCE(
                        (SELECT CONCAT(pt.Nombre, ' ', pt.ApPaterno, ' ', COALESCE(pt.ApMaterno, ''))
                         FROM insfamiliar it
                         INNER JOIN persona pt ON pt.CiPersona = it.CiFamiliar
                         WHERE it.IdInscripcion = i.IdInscripcion
                           AND it.RolFam IN ('Apoderado', 'Tutor')
                         ORDER BY it.CiFamiliar ASC
                         LIMIT 1),
                        (SELECT CONCAT(pt.Nombre, ' ', pt.ApPaterno, ' ', COALESCE(pt.ApMaterno, ''))
                         FROM insfamiliar it
                         INNER JOIN persona pt ON pt.CiPersona = it.CiFamiliar
                         WHERE it.IdInscripcion = i.IdInscripcion
                         ORDER BY it.CiFamiliar ASC
                         LIMIT 1)
                    ) AS TutorNombre,
                    COALESCE(
                        (SELECT COALESCE(NULLIF(pt.Contacto, ''), pt.TelefonoAlternativo, '')
                         FROM insfamiliar it
                         INNER JOIN persona pt ON pt.CiPersona = it.CiFamiliar
                         WHERE it.IdInscripcion = i.IdInscripcion
                           AND it.RolFam IN ('Apoderado', 'Tutor')
                         ORDER BY it.CiFamiliar ASC
                         LIMIT 1),
                        (SELECT COALESCE(NULLIF(pt.Contacto, ''), pt.TelefonoAlternativo, '')
                         FROM insfamiliar it
                         INNER JOIN persona pt ON pt.CiPersona = it.CiFamiliar
                         WHERE it.IdInscripcion = i.IdInscripcion
                         ORDER BY it.CiFamiliar ASC
                         LIMIT 1)
                    ) AS TutorContacto
                FROM inscripcion i
                INNER JOIN celebrante c ON i.CiCel = c.CiCel
                INNER JOIN persona p ON c.CiCel = p.CiPersona
                INNER JOIN detalletipoins dti ON i.IdInscripcion = dti.IdInscripcion
                INNER JOIN tipoins ti ON dti.CodTipoItem = ti.CodTipoItem
                INNER JOIN grupo g ON g.IdGrupo = i.IdGrupo
                WHERE i.IdGrupo = ?
                  AND (
                      g.SacramentoGrupo IS NULL
                      OR (g.SacramentoGrupo = 'Primera Comunión' AND ti.CodTipoItem IN (1, 2))
                      OR (g.SacramentoGrupo = 'Confirmación' AND ti.CodTipoItem IN (3, 4, 5))
                      OR (g.SacramentoGrupo NOT IN ('Primera Comunión', 'Confirmación') AND c.Sacramento = g.SacramentoGrupo)
                  )
                ORDER BY p.Nombre, p.ApPaterno, p.ApMaterno";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idGrIn);
        $stmt->execute();
        $result = $stmt->get_result();
        $datos = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $datos;
    }

    public function getDatosPorSacramento($sacramento) {
        $sql = "
            SELECT 
                g.IdGrupo, 
                g.NombreGrupo,
                g.Color_Grupo,
                (SELECT COUNT(*) FROM inscripcion i WHERE i.IdGrupo = g.IdGrupo) as totalInscripciones,
                (SELECT COUNT(*) FROM `asignacion` a WHERE a.IdGrupo = g.IdGrupo) as totalAsignaciones
            FROM `grupo` g 
            WHERE g.SacramentoGrupo = ?
            ORDER BY g.IdGrupo
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $sacramento);
        $stmt->execute();
        $result = $stmt->get_result();
        $datos = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $datos;
    }

    public function obtenerGrupoPorId($idGrupo) {
        $sql = "SELECT * FROM `grupo` WHERE IdGrupo = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idGrupo);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc();
    }

    /** Devuelve el siguiente nombre numerico disponible para un sacramento (01, 02, ...). */
    public function siguienteNombreGrupo($sacramento) {
        $sql = "SELECT COALESCE(MAX(CAST(NombreGrupo AS UNSIGNED)), 0) AS maximo
                FROM `grupo` WHERE SacramentoGrupo = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $sacramento);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return str_pad(((int) $fila['maximo']) + 1, 2, '0', STR_PAD_LEFT);
    }

    /** Crea un grupo vacio para el sacramento indicado. */
    public function crearGrupo($nombre, $sacramento) {
        $sql = "INSERT INTO `grupo`
                    (NombreGrupo, SacramentoGrupo, Color_Grupo, Santo_Grupo, Imagen_Grupo, Frase_Santo)
                VALUES (?, ?, 'Ninguno', 'Anonimo', 'Anonimo', '-')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $nombre, $sacramento);
        try {
            return $stmt->execute();
        } finally {
            $stmt->close();
        }
    }

    /** Cuenta catequizandos y catequistas ligados al grupo. */
    public function contarDatosGrupo($idGrupo) {
        $sql = "SELECT
                    (SELECT COUNT(*) FROM inscripcion WHERE IdGrupo = ?) AS inscripciones,
                    (SELECT COUNT(*) FROM `asignacion` WHERE IdGrupo = ?) AS asignaciones";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $idGrupo, $idGrupo);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $fila;
    }

    public function contarGruposPorSacramento($sacramento) {
        $sql = "SELECT COUNT(*) AS total FROM `grupo` WHERE SacramentoGrupo = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $sacramento);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int) $fila['total'];
    }

    public function eliminarGrupo($idGrupo) {
        $sql = "DELETE FROM `grupo` WHERE IdGrupo = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idGrupo);
        try {
            return $stmt->execute();
        } finally {
            $stmt->close();
        }
    }
    
    public function actualizarGrupo($idGrupo, $nombre, $color, $santo, $imagen, $frase) {
        
        $sql = "UPDATE `grupo` SET NombreGrupo = ?, Color_Grupo = ?, Santo_Grupo = ?, Imagen_Grupo = ?, Frase_Santo = ? WHERE IdGrupo = ?";
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
                `asignacion` a
            JOIN 
                catequista c ON a.CiCat = c.CiCat
            JOIN 
                `persona` p ON c.CiCat = p.CiPersona
            WHERE 
                a.IdGrupo = ?
            ORDER BY FIELD(a.RolCat, 'Coordinador', 'Titular', 'Catequista'),
                     p.Nombre, p.ApPaterno, p.ApMaterno
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
                `celebrante` c ON i.CiCel = c.CiCel
            INNER JOIN 
                `persona` p ON c.CiCel = p.CiPersona
            INNER JOIN 
                `detalletipoins` dti ON i.IdInscripcion = dti.IdInscripcion
            INNER JOIN
            	`tipoins` ti ON dti.CodTipoItem = ti.CodTipoItem
            INNER JOIN
                `grupo` g ON g.IdGrupo = i.IdGrupo
            WHERE 
                i.IdGrupo = ?
                AND (
                    g.SacramentoGrupo IS NULL
                    OR (g.SacramentoGrupo = 'Primera Comunión' AND ti.CodTipoItem IN (1, 2))
                    OR (g.SacramentoGrupo = 'Confirmación' AND ti.CodTipoItem IN (3, 4, 5))
                    OR (g.SacramentoGrupo NOT IN ('Primera Comunión', 'Confirmación') AND c.Sacramento = g.SacramentoGrupo)
                )
            ORDER BY p.Nombre, p.ApPaterno, p.ApMaterno
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idGrupo);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }


}
?>
