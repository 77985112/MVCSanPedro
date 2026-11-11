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

    public function buscarCelebrantes($termino, $SacramentoCel) {
        return $this->celebranteModel->buscarCelebrantes($termino, $SacramentoCel);
    }

    public function registrarCelebrante($datos) {
        // La contraseña se guarda cifrada; el primer ingreso obliga a cambiarla.
        if (!empty($datos['ClaveCel'])) {
            $datos['ClaveCel'] = password_hash((string) $datos['ClaveCel'], PASSWORD_DEFAULT);
        }
        return $this->celebranteModel->registrarCelebrante($datos);
    }

    /** Genera una contraseña de un solo uso legible para imprimir en el ticket. */
    public function generarClaveTemporal() {
        return 'Tmp' . strtoupper(bin2hex(random_bytes(4)));
    }

    /** Genera y guarda una nueva clave de un solo uso para un catequizando existente. */
    public function restablecerClaveCelebrante($ciCel, $sacramento) {
        $registro = $this->celebranteModel->buscarCelebrante($ciCel, $sacramento);
        if (!$registro) {
            return false;
        }
        $nueva = $this->generarClaveTemporal();
        if (!$this->celebranteModel->restablecerClave($ciCel, password_hash($nueva, PASSWORD_DEFAULT))) {
            return false;
        }
        return ['clave' => $nueva, 'registro' => $registro];
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

    public function verDatosInscripcion($idInscripcion) {
        return $this->documentoModel->obtenerDatosInscripcion($idInscripcion);
    }

    public function verBautizoLocal($ciCel) {
        return $this->documentoModel->obtenerBautizoLocalPorCi($ciCel);
    }

    public function registrarDocumento($gestion, $ciCat, $idGrupo, $idInscripcion, $numeroDoc, $detalleDoc, $libroDoc, $paginaDoc, $partidaDoc, $parroquiaDoc, $fechaDoc = '') {
        return $this->documentoModel->registrarDocumento($gestion, $ciCat, $idGrupo, $idInscripcion, $numeroDoc, $detalleDoc, $libroDoc, $paginaDoc, $partidaDoc, $parroquiaDoc, $fechaDoc);
    }

    public function actualizarDocumentosPresentados($gestion, $ciCat, $idGrupo, $idInscripcion, array $presentados, $fechaBautizo = '') {
        return $this->documentoModel->actualizarDocumentosPresentados($gestion, $ciCat, $idGrupo, $idInscripcion, $presentados, $fechaBautizo);
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

    public function verPadrinosManuales($idInscripcion) {
        return $this->familiarModel->obtenerPadrinosManuales($idInscripcion);
    }

    public function registrarPadrinoManual($idInscripcion, $rolFam, $nombre, $apellido, $estadoCivil) {
        return $this->familiarModel->registrarPadrinoManual($idInscripcion, $rolFam, $nombre, $apellido, $estadoCivil);
    }

    public function eliminarPadrinoManual($idPadrino, $idInscripcion) {
        return $this->familiarModel->eliminarPadrinoManual($idPadrino, $idInscripcion);
    }
}
?>