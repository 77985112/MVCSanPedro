<?php
require_once '../Conexion/Conexion.php';

class AsistenciaApoderadoModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Conexion())->getConnection();
    }
    
    private function liberarResultados()
    {
        while ($this->conn->more_results()) {
            $this->conn->next_result();
            if ($res = $this->conn->store_result()) {
                $res->free();
            }
        }
    }

    public function obtenerCelebrantes($idGrupo)
    {
        $this->liberarResultados();
        $sql = "CALL ObtenerCelebrantes(?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idGrupo);
        $stmt->execute();
        $result = $stmt->get_result();

        $celebrantes = [];
        if ($result->num_rows > 0) {
            $celebrantes = $result->fetch_all(MYSQLI_ASSOC);
        }

        $stmt->free_result();
        $this->liberarResultados();

        return $celebrantes;
    }

    public function obtenerAsistenciaPorFechaYGrupo($fecha, $idGrupo)
    {
        $this->liberarResultados();
        $sql = "CALL ObtenerAsistenciaPorFechaYGrupo(?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $fecha, $idGrupo);
        $stmt->execute();

        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $data = $result->fetch_all(MYSQLI_ASSOC);
        } else {
            $data = [];
        }

        $stmt->free_result();
        $this->liberarResultados();

        return $data;
    }

    public function registrarAsistenciaApoderado($IdInscripcion, $IdReunion, $DetalleAsis, $MontoAsis)
    {
        $this->liberarResultados();
        $sql = "CALL RegistrarAsistenciaApoderado(?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiss", $IdInscripcion, $IdReunion, $DetalleAsis, $MontoAsis);
        return $stmt->execute();
    }

    public function modificarAsistenciaApoderado($IdInscripcion, $IdReunion, $DetalleAsis, $MontoAsis)
    {
        $this->liberarResultados();
        $sql = "CALL ModificarAsistenciaApoderado(?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiss", $IdInscripcion, $IdReunion, $DetalleAsis, $MontoAsis);
        return $stmt->execute();
    }

    public function obtenerIdReunionPorFechaYGrupo($fecha)
    {
        $this->liberarResultados();
        $sql = "CALL ObtenerIdReunionPorFechaYGrupo(?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $fecha);
        $stmt->execute();
        $result = $stmt->get_result();

        $idReunion = null;
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $idReunion = $row['IdReunion'];
        }

        $stmt->free_result();
        $this->liberarResultados();

        return $idReunion;
    }

    public function obtenerReunion()
    {
        $stmt = $this->conn->prepare("SELECT * FROM reunioncel");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }


    public function RegistrarReu($data) {
        $sql = "INSERT INTO ReunionCel (FechaReu, HoraReu, Sacramento, DetalleReu, Gestion, CiCat, IdGrupo) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("ssssssi", 
                $data['FechaR'], 
                $data['HoraR'], 
                $data['Sacramento'], 
                $data['Detalle'], 
                $data['Gestion'], 
                $data['CiCat'], 
                $data['IdGrupo']
            );

            return $stmt->execute();
        } else {
            echo "Error en la preparación de la consulta: " . $this->conn->error;
            return false;
        }
    }
}
?>