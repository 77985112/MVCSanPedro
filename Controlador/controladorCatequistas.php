<?php
require_once 'Modelo/modeloCatequistas.php';

class CatequistaController {
    private $model;

    public function __construct() {
        $this->model = new CatequistaModel();
    }

    public function mostrarCatequistasActivos() {
        return $this->model->getActCatequistas();
    }

    public function buscarCatequista($ciCat) {
        return $this->model->buscarCatequista($ciCat);
    }

}
?>
