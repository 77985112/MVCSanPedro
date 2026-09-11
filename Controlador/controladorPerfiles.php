<?php
require_once '../Modelo/modeloPerfiles.php';

class SacramentoPerfil{
    private $model;

    public function __construct() {
        $this->model = new modeloSacramentoPer();
    }

    public function buscarPerfilCatequista($ciCat) {
        return $this->model->buscarPerfilCatequista($ciCat);
    }

    public function buscarPerfilCelebrante($ciCel,$SacramentoCel) {
        return $this->model->buscarPerfilCelebrante($ciCel,$SacramentoCel);
    }

}
?>
