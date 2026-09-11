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


    public function registrarCatequista($datos) {
        $query = "CALL RegistrarCatequista(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "sssssssssssssss",
            $datos['CiCat'],
            $datos['Sacramento'],
            $datos['UsuarioCat'],
            $datos['ClaveCat'],
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
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
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
    }

    public function obtenerCatequistasDelGrupo($idGrupo, $ciCatActual)
    {
        $sql = "SELECT c.CiCat, CONCAT(p.Nombre, ' ', p.ApPaterno, ' ', p.ApMaterno) AS NombreCompleto, c.ImagenCat, a.RolCat
                FROM catequista c
                INNER JOIN persona p ON c.CiCat = p.CiPersona
                INNER JOIN asignacion a ON c.CiCat = a.CiCat
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
                INNER JOIN persona p ON c.CiCat = p.CiPersona
                INNER JOIN asignacion a ON c.CiCat = a.CiCat
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
