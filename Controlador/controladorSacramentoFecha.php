<?php
require_once '../Modelo/modeloSacramentoFecha.php';

class ControladorSacramento {
    private $model;

    public function __construct() {
        $this->model = new ModeloSacramento();
    }

    public function Comunion() {
        return $this->model->getSacramentoComunion();
    }

    public function Confirmacion() {
        return $this->model->getSacramentoConfirmacion();
    }
    
    public function modificarFechasSacramento($idSacramento, $fechaIni, $fechaFin, $horaIni, $horaFin, $ciCatCat, $estadito) {
        $fechaIniCompleta = $fechaIni . ' ' . $horaIni;
        $fechaFinCompleta = $fechaFin . ' ' . $horaFin;
        return $this->model->updateSacramento($idSacramento, $fechaIniCompleta, $fechaFinCompleta, $ciCatCat, $estadito);
    }

    public function obtenerSacramentoPorId($idSacramento) {
        return $this->model->getDatosSacramentoPorId($idSacramento);
    }
}
?>