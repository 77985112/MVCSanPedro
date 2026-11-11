<?php
require_once '../Conexion/Conexion.php';

class ModeloLogin {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }

    public function getPersonalData($username, $password) {
        $conn = $this->conexion->getConnection();
        $stmt = $conn->prepare(
            "SELECT p.*, c.*
             FROM personal p
             INNER JOIN cargo c ON c.CodCar = p.CodCar
             WHERE p.UsuarioPer = ? AND p.Estado = 'Activo'
             LIMIT 1"
        );
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $conn->close();

        if (!$data) {
            return null;
        }

        $claveGuardada = (string) ($data['ClavePer'] ?? '');
        $info = password_get_info($claveGuardada);
        $esHash = (int) ($info['algo'] ?? 0) !== 0;

        if ($esHash) {
            $claveValida = password_verify($password, $claveGuardada);
            // Las claves de un solo uso se copian del ticket: se tolera un
            // espacio accidental al inicio o al final durante el primer ingreso.
            if (!$claveValida && !empty($data['DebeCambiarClave'])) {
                $claveValida = password_verify(trim((string) $password), $claveGuardada);
            }
            if (!$claveValida) {
                return null;
            }
        } elseif (!hash_equals($claveGuardada, (string) $password)) {
            return null;
        }

        // No se conserva la contraseña ni su hash en la sesión.
        unset($data['ClavePer']);
        return $data;
    }

    public function getCatequistaData($username, $password) {
        $conn = $this->conexion->getConnection();
        $sql = "SELECT c.*, p.*,
                       a.Gestion, COALESCE(a.IdGrupo, 0) AS IdGrupo,
                       a.FechaAsigCat, a.FechaIniCat, a.FechaFinCat,
                       COALESCE(a.RolCat, 'Catequista') AS RolCat,
                       COALESCE(g.NombreGrupo, 'Sin grupo') AS NombreGrupo,
                       g.Color_Grupo, g.Santo_Grupo, g.Imagen_Grupo, g.Frase_Santo,
                       COALESCE(pc.PuedeAsistenciaApoderados, 0) AS PuedeAsistenciaApoderados,
                       COALESCE(pc.PuedePuntajeExamenes, 0) AS PuedePuntajeExamenes,
                       COALESCE(pc.PuedeRegistrarCatequizandos, 0) AS PuedeRegistrarCatequizandos
                FROM catequista c
                INNER JOIN `persona` p ON p.CiPersona = c.CiCat
                LEFT JOIN `asignacion` a ON a.CiCat = c.CiCat
                LEFT JOIN `grupo` g ON g.IdGrupo = a.IdGrupo
                LEFT JOIN `permisos_catequista` pc ON pc.CiCat = c.CiCat
                WHERE c.UsuarioCat = ? AND c.EstadoCat = 'Activo'
                ORDER BY a.Gestion DESC, a.FechaAsigCat DESC
                LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $conn->close();

        if (!$data) {
            return null;
        }

        $claveGuardada = (string) ($data['ClaveCat'] ?? '');
        $info = password_get_info($claveGuardada);
        $esHash = (int) ($info['algo'] ?? 0) !== 0;

        if ($esHash) {
            $claveValida = password_verify($password, $claveGuardada);
            // Las claves de un solo uso se copian del ticket: se tolera un
            // espacio accidental al inicio o al final durante el primer ingreso.
            if (!$claveValida && !empty($data['DebeCambiarClave'])) {
                $claveValida = password_verify(trim((string) $password), $claveGuardada);
            }
            if (!$claveValida) {
                return null;
            }
        } elseif (!hash_equals($claveGuardada, (string) $password)) {
            return null;
        }

        // No se conserva la contraseña ni su hash en la sesión.
        unset($data['ClaveCat']);
        return $data;
    }

    public function getCelebranteData($username, $password) {
        $conn = $this->conexion->getConnection();
        $sql = "SELECT c.*, g.*, p.*, t.*, i.*
                FROM celebrante c
                INNER JOIN persona p ON c.CiCel = p.CiPersona
                INNER JOIN inscripcion i ON c.CiCel = i.CiCel
                INNER JOIN grupo g ON i.IdGrupo = g.IdGrupo
                INNER JOIN detalleTipoins dti ON i.IdInscripcion = dti.IdInscripcion
                INNER JOIN TipoIns t ON dti.CodTipoItem = t.CodTipoItem
                WHERE c.UsuarioCel = ? AND c.EstadoCel = 'Activo'
                LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $conn->close();

        if (!$data) {
            return null;
        }

        $claveGuardada = (string) ($data['ClaveCel'] ?? '');
        $info = password_get_info($claveGuardada);
        $esHash = (int) ($info['algo'] ?? 0) !== 0;

        if ($esHash) {
            $claveValida = password_verify($password, $claveGuardada);
            // Las claves de un solo uso se copian del ticket: se tolera un
            // espacio accidental al inicio o al final durante el primer ingreso.
            if (!$claveValida && !empty($data['DebeCambiarClave'])) {
                $claveValida = password_verify(trim((string) $password), $claveGuardada);
            }
            if (!$claveValida) {
                return null;
            }
        } elseif (!hash_equals($claveGuardada, (string) $password)) {
            return null;
        }

        // No se conserva la contraseña ni su hash en la sesión.
        unset($data['ClaveCel']);
        return $data;
    }

    // Indica si el nombre de usuario existe y está activo en cualquiera de las
    // tres tablas de acceso (personal, catequista o celebrante). Permite al
    // controlador distinguir "usuario incorrecto" de "contraseña incorrecta".
    public function existeUsuario($username) {
        $conn = $this->conexion->getConnection();
        $consultas = [
            "SELECT 1 FROM `personal` WHERE UsuarioPer = ? AND Estado = 'Activo' LIMIT 1",
            "SELECT 1 FROM catequista WHERE UsuarioCat = ? AND EstadoCat = 'Activo' LIMIT 1",
            "SELECT 1 FROM `celebrante` WHERE UsuarioCel = ? AND EstadoCel = 'Activo' LIMIT 1",
        ];
        $existe = false;
        foreach ($consultas as $sql) {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $existe = true;
                $stmt->close();
                break;
            }
            $stmt->close();
        }
        $conn->close();
        return $existe;
    }
}
?>