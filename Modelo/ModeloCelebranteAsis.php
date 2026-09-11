<?php

require_once '../Conexion/Conexion.php';

class AsistenciaModel {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }

    private function liberarResultados() {
        while ($this->conn->more_results()) {
            $this->conn->next_result();
            if ($res = $this->conn->store_result()) {
                $res->free();
            }
        }
    }

    public function obtenerCelebrantes($idGrupo) {
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
    

    public function registrarAsistencia($IdInscripcion, $FechaAsisConf, $HoraAsisConf, $DetalleAsis, $TipoAsis) {
        $this->liberarResultados();
        $sql = "CALL RegistrarAsistencia(?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issss", $IdInscripcion, $FechaAsisConf, $HoraAsisConf, $DetalleAsis, $TipoAsis);
        return $stmt->execute();
    }
    

    public function modificarAsistencia($CodAsis, $TipoAsis) {
        $this->liberarResultados();
        $sql = "CALL ModificarAsistencia(?, ?)";
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            return false;
        }
        $stmt->bind_param("is", $CodAsis, $TipoAsis);
        return $stmt->execute();
    }

    public function obtenerAsistenciaPorFecha($fecha) {
        $this->liberarResultados();
        $sql = "CALL ObtenerAsistenciaPorFecha(?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $fecha);
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
}
?>
