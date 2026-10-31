<?php
require_once '../Conexion/Conexion.php';

class ModeloLogin {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }

    public function getPersonalData($username, $password) {
        $conn = $this->conexion->getConnection();
        $stmt = $conn->prepare("CALL sp_login_personal(?, ?)");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        $conn->close();
        return $data;
    }

    public function getCatequistaData($username, $password) {
        $conn = $this->conexion->getConnection();
        $stmt = $conn->prepare("CALL sp_login_catequista(?, ?)");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();

        // El procedimiento exige una asignación con grupo. Si el catequista
        // todavía no tiene grupo, se valida su acceso directamente y se le
        // asigna el rol base para que pueda ingresar.
        if (!$data) {
            $sql = "SELECT c.*, p.*,
                           a.Gestion, COALESCE(a.IdGrupo, 0) AS IdGrupo,
                           a.FechaAsigCat, a.FechaIniCat, a.FechaFinCat,
                           COALESCE(a.RolCat, 'Catequista') AS RolCat,
                           COALESCE(g.NombreGrupo, 'Sin grupo') AS NombreGrupo,
                           g.Color_Grupo, g.Santo_Grupo, g.Imagen_Grupo, g.Frase_Santo
                    FROM catequista c
                    INNER JOIN `persona` p ON p.CiPersona = c.CiCat
                    LEFT JOIN `asignacion` a ON a.CiCat = c.CiCat
                    LEFT JOIN `grupo` g ON g.IdGrupo = a.IdGrupo
                    WHERE c.UsuarioCat = ? AND c.ClaveCat = ? AND c.EstadoCat = 'Activo'
                    ORDER BY a.Gestion DESC, a.FechaAsigCat DESC
                    LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $username, $password);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
            $stmt->close();
        }

        $conn->close();
        return $data;
    }

    public function getCelebranteData($username, $password) {
        $conn = $this->conexion->getConnection();
        $stmt = $conn->prepare("CALL sp_login_celebrante(?, ?)");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        $conn->close();
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