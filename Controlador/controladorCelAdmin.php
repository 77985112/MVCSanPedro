<?php
require_once '../Modelo/modeloCelAdmin.php';

class CelebranteControllerAdmin {
    private $celebranteModel;

    public function __construct() {
        $this->celebranteModel = new CelebranteAdmin();
    }

    public function buscarCeleGR($SacCel) {
        return $this->celebranteModel->buscarCeleGR($SacCel);
    }

    public function buscarCelebrante($ciCel,$SacramentoCel) {
        return $this->celebranteModel->buscarCelebrante($ciCel,$SacramentoCel);
    }

    public function registrarCelebrante($datos) {
        return $this->celebranteModel->registrarCelebrante($datos);
    }

    public function modificarCelebrante($ciCel, $datosCelebrante, $datosInscripcion) {
        $this->celebranteModel->modificarCelebrante($ciCel, $datosCelebrante, $datosInscripcion);
    }

    public function verificarIns($SacramentoIns) {
        return $this->celebranteModel->verificarIns($SacramentoIns);
    }
}

class ControladorCelAdm {
    private $documentoModel;
    private $familiarModel;

    public function __construct() {
        $this->documentoModel = new DocumentoAdm();
        $this->familiarModel = new FamiliarAdm();
    }

    public function verDocumentos($idInscripcion) {
        return $this->documentoModel->obtenerDocumentosPorInscripcion($idInscripcion);
    }

    public function registrarDocumento($gestion, $ciCat, $idGrupo, $idInscripcion, $numeroDoc, $detalleDoc, $libroDoc, $paginaDoc, $partidaDoc, $parroquiaDoc) {
        return $this->documentoModel->registrarDocumento($gestion, $ciCat, $idGrupo, $idInscripcion, $numeroDoc, $detalleDoc, $libroDoc, $paginaDoc, $partidaDoc, $parroquiaDoc);
    }

    public function eliminarDocumento($gestion, $ciCat, $idGrupo, $idInscripcion) {
        return $this->documentoModel->eliminarDocumento($gestion, $ciCat, $idGrupo, $idInscripcion);
    }

    public function verFamiliar($ciFamiliar) {
        return $this->familiarModel->obtenerFamiliarPorCi($ciFamiliar);
    }

    public function verFamiliares($idInscripcion) {
        return $this->familiarModel->obtenerFamiliaresPorInscripcion($idInscripcion);
    }

    public function registrarFamiliar($ciFamiliar, $idInscripcion, $rolFam, $parentesco) {
        return $this->familiarModel->registrarFamiliar($ciFamiliar, $idInscripcion, $rolFam, $parentesco);
    }

    public function eliminarFamiliar($ciFamiliar, $idInscripcion) {
        return $this->familiarModel->eliminarFamiliar($ciFamiliar, $idInscripcion);
    }
}
?>