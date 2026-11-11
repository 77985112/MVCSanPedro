<?php
require_once '../Conexion/Conexion.php';

class CatequistaModel
{
    private $conexion;

    public function __construct()
    {
        $this->conexion = new Conexion();
    }

    public function obtenerCatequistas($sacramento)
    {
        $conn = $this->conexion->getConnection();

        $sql = "SELECT asig.Gestion, c.CiCat, p.Nombre, p.ApPaterno, p.ApMaterno, asig.IdGrupo, c.Sacramento, asig.RolCat, gr.NombreGrupo
                FROM catequista c
                INNER JOIN `persona` p ON c.CiCat = p.CiPersona
                INNER JOIN `asignacion` asig ON c.CiCat = asig.CiCat
                INNER JOIN `grupo` gr ON asig.IdGrupo = gr.IdGrupo
                WHERE c.Sacramento = ?
                ORDER BY asig.IdGrupo,
                         FIELD(asig.RolCat, 'Coordinador', 'Titular', 'Catequista'),
                         p.Nombre, p.ApPaterno, p.ApMaterno";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $sacramento);
        $stmt->execute();
        $result = $stmt->get_result();

        $catequistas = [];
        while ($row = $result->fetch_assoc()) {
            $catequistas[] = $row;
        }

        $stmt->close();
        return $catequistas;
    }

    public function obtenerAsistencia($ciCat, $idGrupo, $fecha)
    {
        $conn = $this->conexion->getConnection();

        $sql = "SELECT DetalleAsis FROM asistenciacat WHERE CiCat = ? AND IdGrupo = ? AND FechaAsisCat = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sis', $ciCat, $idGrupo, $fecha);
        $stmt->execute();
        $result = $stmt->get_result();

        $asistencia = $result->fetch_assoc();
        $stmt->close();

        return $asistencia ? $asistencia['DetalleAsis'] : null;
    }

    public function ActualizarAsistencia($Gestion, $ciCat, $idGrupo, $detalleAsis, $fechaAsis)
    {
        $conn = $this->conexion->getConnection();
        $detalleAsis = in_array($detalleAsis, ['Presente', 'Falta', 'Permiso'], true) ? $detalleAsis : 'Falta';

        $sqlUpdate = "UPDATE asistenciacat SET DetalleAsis = ? WHERE Gestion = ? AND CiCat = ? AND IdGrupo = ? AND FechaAsisCat = ?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param('ssiss', $detalleAsis, $Gestion, $ciCat, $idGrupo, $fechaAsis);
        $stmtUpdate->execute();
        $stmtUpdate->close();
    }

    public function registrarAsistencia($Gestion, $ciCat, $idGrupo, $detalleAsis, $fechaAsis) {
        $conn = $this->conexion->getConnection();
        $detalleAsis = in_array($detalleAsis, ['Presente', 'Falta', 'Permiso'], true) ? $detalleAsis : 'Falta';
    
        // Verificar si el registro ya existe
        $sqlCheck = "SELECT COUNT(*) FROM asistenciacat WHERE Gestion = ? AND CiCat = ? AND IdGrupo = ? AND FechaAsisCat = ?";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bind_param('ssis', $Gestion, $ciCat, $idGrupo, $fechaAsis);
        $stmtCheck->execute();
        $stmtCheck->bind_result($count);
        $stmtCheck->fetch();
        $stmtCheck->close();

        if ($count > 0) {
            // Si existe, actualizar
            $this->ActualizarAsistencia($Gestion, $ciCat, $idGrupo, $detalleAsis, $fechaAsis);
        } else {
            // Si no existe, insertar
            $sqlInsert = "INSERT INTO asistenciacat (Gestion, CiCat, IdGrupo, DetalleAsis, FechaAsisCat) VALUES (?, ?, ?, ?, ?)";
            $stmtInsert = $conn->prepare($sqlInsert);
            $stmtInsert->bind_param('ssiss', $Gestion, $ciCat, $idGrupo, $detalleAsis, $fechaAsis);
            $stmtInsert->execute();
            $stmtInsert->close();
        }
    }

    public function eliminarAsistenciaPorFecha($sacramento, $fechaAsis) {
        $conn = $this->conexion->getConnection();

        $sql = "DELETE a FROM asistenciacat a
                INNER JOIN catequista c ON c.CiCat = a.CiCat
                WHERE c.Sacramento = ? AND a.FechaAsisCat = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $sacramento, $fechaAsis);
        $ok = $stmt->execute();
        $afectados = $stmt->affected_rows;
        $stmt->close();

        return $ok ? $afectados : false;
    }
}
