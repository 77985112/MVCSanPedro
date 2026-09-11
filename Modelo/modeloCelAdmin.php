<?php
require_once '../Conexion/Conexion.php';

class CelebranteAdmin
{
    private $conn;

    public function __construct()
    {
        $database = new Conexion();
        $this->conn = $database->getConnection();
    }

    public function verificarIns($SacramentoIns)
    {
        $sql = "SELECT * FROM fechassacramento WHERE Sacramento = ? AND EstadoIns = 'Online' AND ActividadSac = 'Inscripción'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $SacramentoIns);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function buscarCeleGR($sacCel)
    {
        $sql = "CALL CelebranteSinGrupo(?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $sacCel);

        if (!$stmt->execute()) {
            throw new Exception("Error al ejecutar el procedimiento: " . $stmt->error);
        }
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function buscarCelebrante($ciCel, $sacramentoCel)
    {
        $sql = "CALL BuscarCelebrante(?, ?)";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $this->conn->error);
        }

        $stmt->bind_param("ss", $ciCel, $sacramentoCel);

        if (!$stmt->execute()) {
            throw new Exception("Error al ejecutar el procedimiento: " . $stmt->error);
        }

        return $stmt->get_result()->fetch_assoc();
    }

    public function registrarCelebrante($datos)
    {
        $sql = "CALL RegistrarCelebrante(?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $this->conn->error);
        }

        $stmt->bind_param(
            "ssssisi",
            $datos['CiCel'],
            $datos['SacramentoCel'],
            $datos['UsuarioCel'],
            $datos['ClaveCel'],
            $datos['IdGrupo'],
            $datos['PreValor'],
            $datos['CodTipoItem']
        );

        if (!$stmt->execute()) {
            throw new Exception("Error al ejecutar el procedimiento: " . $stmt->error);
        }

        return true;
    }

    public function modificarCelebrante($ciCel, $datosCelebrante, $datosInscripcion)
    {
        $sql = "CALL ModificarCelebrante(?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $this->conn->error);
        }

        $stmt->bind_param(
            "ssssssii",
            $ciCel,
            $datosCelebrante['Sacramento'],
            $datosCelebrante['EstadoCel'],
            $datosCelebrante['PerfilCel'],
            $datosCelebrante['FondoCel'],
            $datosInscripcion['IdGrupo'],
            $datosInscripcion['PreValor'],
            $datosInscripcion['CodTipoItem']
        );

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
}

class DocumentoAdm
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Conexion())->getConnection();
    }

    public function obtenerDocumentosPorInscripcion($idInscripcion)
    {
        $sql = "SELECT * FROM Documento WHERE IdInscripcion = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idInscripcion);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function registrarDocumento($gestion, $ciCat, $idGrupo, $idInscripcion, $numeroDoc, $detalleDoc, $libroDoc, $paginaDoc, $partidaDoc, $parroquiaDoc)
    {
        $sql = "INSERT INTO Documento (Gestion, CiCat, IdGrupo, IdInscripcion, NumeroDoc, DetalleDoc, LibroDoc, PaginaDoc, PartidaDoc, ParroquiaDoc)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssiissssss", $gestion, $ciCat, $idGrupo, $idInscripcion, $numeroDoc, $detalleDoc, $libroDoc, $paginaDoc, $partidaDoc, $parroquiaDoc);
        return $stmt->execute();
    }

    public function eliminarDocumento($gestion, $ciCat, $idGrupo, $idInscripcion)
    {
        $sql = "DELETE FROM Documento WHERE Gestion = ? AND CiCat = ? AND IdGrupo = ? AND IdInscripcion = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssii", $gestion, $ciCat, $idGrupo, $idInscripcion);
        return $stmt->execute();
    }
}

class FamiliarAdm
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Conexion())->getConnection();
    }

    public function obtenerFamiliarPorCi($ciFamiliar)
    {
        $sql = "SELECT * FROM Familiar WHERE CiFamiliar = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $ciFamiliar);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function obtenerFamiliaresPorInscripcion($idInscripcion)
    {
        $sql = "SELECT f.CiFamiliar, CONCAT(p.Nombre, ' ', p.ApPaterno, ' ', p.ApMaterno) AS NombreCompleto, 
                       insf.RolFam, insf.Parentesco, p.Contacto 
                FROM insfamiliar insf 
                JOIN familiar f ON insf.CiFamiliar = f.CiFamiliar 
                JOIN persona p ON f.CiFamiliar = p.CiPersona 
                WHERE insf.IdInscripcion = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idInscripcion);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function registrarFamiliar($ciFamiliar, $idInscripcion, $rolFam, $parentesco)
    {
        $sql = "INSERT IGNORE INTO familiar (CiFamiliar) VALUES (?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $ciFamiliar);
        $stmt->execute();

        $sql = "INSERT INTO insfamiliar (CiFamiliar, IdInscripcion, RolFam, Parentesco) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("siss", $ciFamiliar, $idInscripcion, $rolFam, $parentesco);
        return $stmt->execute();
    }

    public function eliminarFamiliar($ciFamiliar, $idInscripcion)
    {
        $sql = "DELETE FROM insfamiliar WHERE CiFamiliar = ? AND IdInscripcion = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $ciFamiliar, $idInscripcion);
        return $stmt->execute();
    }
}
class Inscripcion
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Conexion())->getConnection();
    }

    public function obtenerInscripcionPorId($idInscripcion)
    {
        $sql = "SELECT * FROM inscripcion WHERE IdInscripcion = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idInscripcion);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}
