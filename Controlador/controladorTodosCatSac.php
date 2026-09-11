<?php
require_once '../Modelo/modeloTodosCatSac.php';

class ContCatequistaSac {
    private $model;

    public function __construct() {
        $this->model = new CatequistaSac();
    }

    public function mostrarCatCom() {
        return $this->model->getCatequistasComunion();
    }

    public function mostrarCatConf() {
        return $this->model->getCatequistasConfirmacion();
    }

    
}
?>
