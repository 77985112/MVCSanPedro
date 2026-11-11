<?php
require_once '../Conexion/Conexion.php';

class Catequista
{
    private $conn;

    public function __construct()
    {
        $database = new Conexion();
        $this->conn = $database->getConnection();
    }

    public function buscarCatSinGr($SacCat)
    {
        $query = "CALL CatequistaSinGrupo(?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $SacCat);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function buscarCatequista($ciCat, $SacAdm)
    {
        $query = "CALL BuscarCatequista(?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $ciCat, $SacAdm);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /** Lista todos los catequistas de un sacramento, ordenados por grupo. */
    public function obtenerTodosPorSacramento($sacramento)
    {
        $query = "SELECT c.CiCat, c.EstadoCat, c.UsuarioCat,
                         p.Nombre, p.ApPaterno, p.ApMaterno, p.Contacto,
                         a.IdGrupo, a.RolCat, g.NombreGrupo
                  FROM catequista c
                  INNER JOIN `persona` p ON p.CiPersona = c.CiCat
                  LEFT JOIN `asignacion` a ON a.CiCat = c.CiCat
                      AND a.Gestion = (
                          SELECT MAX(a2.Gestion) FROM `asignacion` a2 WHERE a2.CiCat = c.CiCat
                      )
                  LEFT JOIN `grupo` g ON g.IdGrupo = a.IdGrupo
                  WHERE c.Sacramento = ?
                    AND a.IdGrupo IS NOT NULL
                  ORDER BY (g.IdGrupo IS NULL), g.IdGrupo,
                           FIELD(a.RolCat, 'Coordinador', 'Titular', 'Catequista'),
                           p.ApPaterno, p.ApMaterno, p.Nombre";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $sacramento);
        $stmt->execute();
        $datos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $datos;
    }


    public function registrarCatequista($datos) {
        $claveHash = (string) $datos['ClaveCat'];

        $query = "CALL RegistrarCatequista(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        // El procedimiento recibe una clave corta; el hash definitivo se guarda
        // después porque su parámetro es más corto que un hash de password_hash.
        $placeholder = (string) $datos['CiCat'];
        $stmt->bind_param(
            "sssssssssssssss",
            $datos['CiCat'],
            $datos['Sacramento'],
            $datos['UsuarioCat'],
            $placeholder,
            $datos['EstadoCat'],
            $datos['ImagenCat'],
            $datos['FondoCat'],
            $datos['PoderCat'],
            $datos['FraseCat'],
            $datos['Gestion'],
            $datos['IdGrupo'],
            $datos['FechaAsigCat'],
            $datos['FechaIniCat'],
            $datos['FechaFinCat'],
            $datos['RolCat']
        );

        $this->conn->begin_transaction();
        try {
            if (!$stmt->execute()) {
                $this->conn->rollback();
                return false;
            }
            $stmt->close();

            $stmt = $this->conn->prepare(
                "UPDATE catequista SET ClaveCat = ?, DebeCambiarClave = 1 WHERE CiCat = ?"
            );
            $stmt->bind_param("ss", $claveHash, $datos['CiCat']);
            if (!$stmt->execute()) {
                $this->conn->rollback();
                return false;
            }
            $stmt->close();
            $this->conn->commit();
            return true;
        } catch (Throwable $e) {
            $this->conn->rollback();
            return false;
        }
    }

    /** Reemplaza la clave por una nueva de un solo uso y obliga a cambiarla. */
    public function restablecerClave($ciCat, $claveHash)
    {
        $stmt = $this->conn->prepare(
            "UPDATE catequista SET ClaveCat = ?, DebeCambiarClave = 1 WHERE CiCat = ?"
        );
        $stmt->bind_param("ss", $claveHash, $ciCat);
        return $stmt->execute();
    }

    public function modificarCatequista($CiCat, $datosCatequista, $datosAsignacion)
    {
        $query = "CALL ModificarCatequista(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "sssssssssss",
            $CiCat,
            $datosCatequista['Sacramento'],
            $datosCatequista['EstadoCat'],
            $datosCatequista['ImagenCat'],
            $datosCatequista['FondoCat'],
            $datosCatequista['PoderCat'],
            $datosCatequista['FraseCat'],
            $datosAsignacion['IdGrupo'],
            $datosAsignacion['FechaIniCat'],
            $datosAsignacion['FechaFinCat'],
            $datosAsignacion['RolCat']
        );
        $stmt->execute();
        $stmt->close();

        if (!empty($datosCatequista['UsuarioCat'])) {
            $stmtUsuario = $this->conn->prepare("UPDATE catequista SET UsuarioCat = ? WHERE CiCat = ?");
            $stmtUsuario->bind_param("ss", $datosCatequista['UsuarioCat'], $CiCat);
            $stmtUsuario->execute();
            $stmtUsuario->close();
        }
    }

    /** Indica si el usuario esta libre (true) o ya lo usa otro catequista (false). */
    public function usuarioDisponible($usuario, $ciExcluir = '')
    {
        $query = "SELECT COUNT(*) AS total FROM catequista WHERE UsuarioCat = ? AND CiCat <> ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $usuario, $ciExcluir);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int) $fila['total'] === 0;
    }

    public function obtenerCatequistasDelGrupo($idGrupo, $ciCatActual)
    {
        $sql = "SELECT c.CiCat, CONCAT(p.Nombre, ' ', p.ApPaterno, ' ', p.ApMaterno) AS NombreCompleto, c.ImagenCat, a.RolCat
                FROM catequista c
                INNER JOIN `persona` p ON c.CiCat = p.CiPersona
                INNER JOIN `asignacion` a ON c.CiCat = a.CiCat
                WHERE a.IdGrupo = ? AND c.CiCat != ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $idGrupo, $ciCatActual);
        $stmt->execute();
        $result = $stmt->get_result();

        $catequistas = [];
        while ($row = $result->fetch_assoc()) {
            $catequistas[] = $row;
        }

        return $catequistas;
    }

    public function CatequistasDelGrupoCel($idGrupo)
    {
        $sql = "SELECT c.CiCat, CONCAT(p.Nombre, ' ', p.ApPaterno, ' ', p.ApMaterno) AS NombreCompleto, c.ImagenCat, a.RolCat
                FROM catequista c
                INNER JOIN `persona` p ON c.CiCat = p.CiPersona
                INNER JOIN `asignacion` a ON c.CiCat = a.CiCat
                WHERE a.IdGrupo = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idGrupo);
        $stmt->execute();
        $result = $stmt->get_result();

        $catequistas = [];
        while ($row = $result->fetch_assoc()) {
            $catequistas[] = $row;
        }

        return $catequistas;
    }
}
