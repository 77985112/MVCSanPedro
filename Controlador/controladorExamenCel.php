<?php
require_once '../Modelo/modeloExamenCel.php';

class ContCelebranteExamen {
    private $model;

    public function __construct() {
        $this->model = new CelebranteExamen();
    }
    public function verCelebrantes($sacramento, $idGrupo) {
        return $this->model->getCelebrantes($sacramento, $idGrupo);
    }
    public function asignarNota($idCelEx, $numNota, $nota) {
        return $this->model->actualizarNota($idCelEx, $numNota, $nota);
    }

    public function listarExamenes() {
        return $this->model->getExamenes();
    }
}
?>