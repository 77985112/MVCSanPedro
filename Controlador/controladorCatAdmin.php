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

    public function obtenerTodosPorSacramento($sacramento) {
        return $this->catequistaModel->obtenerTodosPorSacramento($sacramento);
    }

    public function registrarCatequista($datos) {
        // La contraseña se guarda cifrada; el ticket muestra la clave temporal.
        $datos['ClaveCat'] = password_hash((string) $datos['ClaveCat'], PASSWORD_DEFAULT);
        return $this->catequistaModel->registrarCatequista($datos);
    }

    /** Genera una contraseña de un solo uso legible para imprimir en el ticket. */
    public function generarClaveTemporal() {
        return 'Tmp' . strtoupper(bin2hex(random_bytes(4)));
    }

    /** Genera y guarda una nueva clave de un solo uso para un catequista existente. */
    public function restablecerClaveCatequista($ciCat, $sacramento) {
        $registro = $this->catequistaModel->buscarCatequista($ciCat, $sacramento);
        if (!$registro) {
            return false;
        }
        $nueva = $this->generarClaveTemporal();
        if (!$this->catequistaModel->restablecerClave($ciCat, password_hash($nueva, PASSWORD_DEFAULT))) {
            return false;
        }
        return ['clave' => $nueva, 'registro' => $registro];
    }

    public function modificarCatequista($CiCat, $datosCatequista, $datosAsignacion) {
        $this->catequistaModel->modificarCatequista($CiCat, $datosCatequista, $datosAsignacion);
    }

    public function usuarioDisponible($usuario, $ciExcluir = '') {
        return $this->catequistaModel->usuarioDisponible($usuario, $ciExcluir);
    }
}
?>