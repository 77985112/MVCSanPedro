<?php
require_once '../Conexion/Conexion.php';

class ModeloPermisosCatequista
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Conexion())->getConnection();
    }

    public function obtenerCompaneros($idGrupo, $gestion, $ciTitular)
    {
        // El IdGrupo ya identifica el grupo de la gestión actual. Se toma la
        // asignación más reciente de cada catequista para no excluir a quienes
        // fueron asignados en una fecha distinta a la del titular.
        $sql = "SELECT c.CiCat,
                       CONCAT(p.Nombre, ' ', p.ApPaterno, ' ', p.ApMaterno) AS NombreCompleto,
                       a.RolCat,
                       COALESCE(pc.PuedeAsistenciaApoderados, 0) AS PuedeAsistenciaApoderados,
                       COALESCE(pc.PuedePuntajeExamenes, 0) AS PuedePuntajeExamenes,
                       COALESCE(pc.PuedeRegistrarCatequizandos, 0) AS PuedeRegistrarCatequizandos
                FROM catequista c
                INNER JOIN persona p ON p.CiPersona = c.CiCat
                INNER JOIN asignacion a ON a.CiCat = c.CiCat
                LEFT JOIN permisos_catequista pc ON pc.CiCat = c.CiCat
                WHERE a.IdGrupo = ? AND c.CiCat <> ?
                  AND a.Gestion = (SELECT MAX(a2.Gestion) FROM asignacion a2 WHERE a2.CiCat = c.CiCat)
                  AND c.EstadoCat = 'Activo' AND a.RolCat IN ('Catequista', 'Titular')
                ORDER BY p.ApPaterno, p.ApMaterno, p.Nombre";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $idGrupo, $ciTitular);
        $stmt->execute();
        $filas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $filas;
    }

    public function verificarCompanero($ciCat, $idGrupo, $gestion, $ciTitular)
    {
        $sql = "SELECT COUNT(*) AS total
                FROM catequista c
                INNER JOIN asignacion a ON a.CiCat = c.CiCat
                WHERE c.CiCat = ? AND a.IdGrupo = ? AND c.CiCat <> ?
                  AND a.Gestion = (SELECT MAX(a2.Gestion) FROM asignacion a2 WHERE a2.CiCat = c.CiCat)
                  AND c.EstadoCat = 'Activo' AND a.RolCat IN ('Catequista', 'Titular')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sis", $ciCat, $idGrupo, $ciTitular);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int) ($fila['total'] ?? 0) > 0;
    }

    public function guardarPermisos($ciCat, $asistencia, $puntaje, $registrar, $otorgadoPor)
    {
        $sql = "INSERT INTO permisos_catequista
                    (CiCat, PuedeAsistenciaApoderados, PuedePuntajeExamenes, PuedeRegistrarCatequizandos, OtorgadoPor)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    PuedeAsistenciaApoderados = VALUES(PuedeAsistenciaApoderados),
                    PuedePuntajeExamenes = VALUES(PuedePuntajeExamenes),
                    PuedeRegistrarCatequizandos = VALUES(PuedeRegistrarCatequizandos),
                    OtorgadoPor = VALUES(OtorgadoPor),
                    ActualizadoEn = NOW()";
        $stmt = $this->conn->prepare($sql);
        $asistencia = (int) $asistencia;
        $puntaje = (int) $puntaje;
        $registrar = (int) $registrar;
        $stmt->bind_param("siiis", $ciCat, $asistencia, $puntaje, $registrar, $otorgadoPor);
        return $stmt->execute();
    }
}
?>
