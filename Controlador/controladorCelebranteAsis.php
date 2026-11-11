<?php

require_once '../Modelo/ModeloCelebranteAsis.php';

class AsistenciaController
{
    private $modelo;

    public function __construct($modelo)
    {
        $this->modelo = $modelo;
    }

    public function registrarAsistencia()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registrar'])) {
            $horaActual = date('H:i:s');

            foreach ($_POST['IdInscripcion'] as $idInscripcion) {
                $TipoAsis = isset($_POST['TipoAsis'][$idInscripcion]) ? $_POST['TipoAsis'][$idInscripcion] : 'Falta';
                if (!in_array($TipoAsis, ['Presente', 'Falta', 'Licencia'], true)) {
                    $TipoAsis = 'Falta';
                }
                $detalleAsis = $_POST['DetalleAsis'];
                $FechaReg = $_POST['FechaReg'];
                $this->modelo->registrarAsistencia($idInscripcion, $FechaReg, $horaActual, $detalleAsis, $TipoAsis);
            }

            $fechaDestino = $_POST['FechaReg'] ?? date('Y-m-d');
            header('Location: ' . $_SERVER['PHP_SELF'] . '?fecha=' . urlencode($fechaDestino));
            exit();
        }
    }


    public function modificarAsistencia()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['modificar'])) {
            $CodAsisArray = $_POST['CodAsis'];
            $TipoAsisArray = $_POST['TipoAsis'];

            foreach ($CodAsisArray as $CodAsis) {
                $tipoAsis = isset($TipoAsisArray[$CodAsis]) ? $TipoAsisArray[$CodAsis] : 'Falta';
                if (!in_array($tipoAsis, ['Presente', 'Falta', 'Licencia'], true)) {
                    $tipoAsis = 'Falta';
                }

                $this->modelo->modificarAsistencia($CodAsis, $tipoAsis);
            }
            $fechaDestino = $_POST['FechaReg'] ?? date('Y-m-d');
            header('Location: ' . $_SERVER['PHP_SELF'] . '?fecha=' . urlencode($fechaDestino));
            exit();
        }
    }


    public function obtenerCelebrantes($idGrupo)
    {
        return $this->modelo->obtenerCelebrantes($idGrupo);
    }

    public function obtenerAsistenciaPorFecha($fecha)
    {
        return $this->modelo->obtenerAsistenciaPorFecha($fecha);
    }
}
