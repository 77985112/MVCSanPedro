<?php
require_once '../Modelo/modeloCatAdmin.php';

class CatequistaController {
    private $catequistaModel;

    public function __construct() {
        $this->catequistaModel = new Catequista();
    }

    public function buscarCatSinGr($SacCat) {
        return $this->catequistaModel->buscarCatSinGr($SacCat);
    }

    public function buscarCatequista($ciCat, $SacAdm) {
        return $this->catequistaModel->buscarCatequista($ciCat, $SacAdm);
    }

    public function registrarCatequista($datos) {
        $this->catequistaModel->registrarCatequista($datos);
    }

    public function modificarCatequista($CiCat, $datosCatequista, $datosAsignacion) {
        $this->catequistaModel->modificarCatequista($CiCat, $datosCatequista, $datosAsignacion);
    }
}
?>