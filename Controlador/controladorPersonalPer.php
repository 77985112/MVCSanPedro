<?php
require_once '../Modelo/modeloPersonalPer.php';

class ContPersonal {
    private $model;

    public function __construct() {
        $this->model = new ModPersonal();
    }

    public function getPersonal() {
        return $this->model->getPersonal();
    }

    public function PerfildePersonal($CiPer) {
        return $this->model->PerfildePersonal($CiPer);
    }

    
}
?>
