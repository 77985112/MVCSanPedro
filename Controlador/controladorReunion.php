<?php
require_once '../Modelo/modeloReuniones.php';

class AsistenciaApoderadoController
{
    private $modelo;

    public function __construct($modelo)
    {
        $this->modelo = $modelo;
    }

    public function registrarAsistencia()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registrar'])) {
            $idReunion = $_POST['IdReunion'];
            foreach ($_POST['IdInscripcion'] as $idInscripcion) {
                $detalleAsis = isset($_POST['DetalleAsis'][$idInscripcion]) ? $_POST['DetalleAsis'][$idInscripcion] : 'Falta';
                $montoAsis = isset($_POST['MontoAsis'][$idInscripcion]) ? $_POST['MontoAsis'][$idInscripcion] : 0;
                $this->modelo->registrarAsistenciaApoderado($idInscripcion, $idReunion, $detalleAsis, $montoAsis);
            }
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }
    }

    public function modificarAsistencia()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['modificar'])) {
            $idReunion = $_POST['IdReunion'];
            $IdInscripcionArray = $_POST['IdInscripcion'];
            $DetalleAsisArray = $_POST['DetalleAsis'];
            $MontoAsisArray = $_POST['MontoAsis'];

            foreach ($IdInscripcionArray as $idInscripcion) {
                $detalleAsis = isset($DetalleAsisArray[$idInscripcion]) ? $DetalleAsisArray[$idInscripcion] : 'Falta';
                $montoAsis = isset($MontoAsisArray[$idInscripcion]) ? $MontoAsisArray[$idInscripcion] : 0;

                $this->modelo->modificarAsistenciaApoderado($idInscripcion, $idReunion, $detalleAsis, $montoAsis);
            }

            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }
    }


    public function obtenerAsistenciaPorFechaYGrupo($fecha, $idGrupo)
    {
        return $this->modelo->obtenerAsistenciaPorFechaYGrupo($fecha, $idGrupo);
    }

    public function obtenerCelebrantes($idGrupo)
    {
        return $this->modelo->obtenerCelebrantes($idGrupo);
    }

    public function obtenerIdReunionPorFechaYGrupo($fecha)
    {
        return $this->modelo->obtenerIdReunionPorFechaYGrupo($fecha);
    }

    public function obtenerReunion()
    {
        return $this->modelo->obtenerReunion();
    }

    public function RegistrarReu($data)
    {
        return $this->modelo->RegistrarReu($data);
    }
}
