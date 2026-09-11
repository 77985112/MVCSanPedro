<?php
require_once __DIR__ . '/../Modelo/modeloGoogleDrive.php';

class ControladorGoogleDrive {

    private $modelo;

    public function __construct() {
        $this->modelo = new ModeloGoogleDrive();
    }

    public function estaConectado() {
        return $this->modelo->estaConectado();
    }

    public function obtenerUrlConexion() {
        return $this->modelo->obtenerUrlConexion();
    }

    public function desconectar() {
        $this->modelo->desconectar();
    }

    public function respaldarPDF($pdfContent, $sacramento, $nombrePersona) {
        $carpetaRaiz = $this->modelo->obtenerOCrearCarpeta('SanPedro_Certificados');
        $carpetaSacramento = $this->modelo->obtenerOCrearCarpeta($sacramento, $carpetaRaiz);

        $fecha = date('Y-m-d_H-i-s');
        $nombreArchivo = "{$sacramento}_{$nombrePersona}_{$fecha}.pdf";

        return $this->modelo->subirPDF($pdfContent, $nombreArchivo, $carpetaSacramento);
    }

    public function listarArchivos($sacramento = null, $limite = 50) {
        $carpetaRaiz = $this->modelo->buscarCarpeta('SanPedro_Certificados');
        if (!$carpetaRaiz) {
            return [];
        }

        $carpetaId = null;
        if ($sacramento) {
            $carpetaId = $this->modelo->buscarCarpeta($sacramento);
        }

        $carpetaListar = $carpetaId ? $carpetaId : $carpetaRaiz;
        return $this->modelo->listarArchivos($carpetaListar, $limite);
    }

    public function eliminarArchivo($fileId) {
        return $this->modelo->eliminarArchivo($fileId);
    }

    public function descargarArchivo($fileId) {
        return $this->modelo->descargarArchivo($fileId);
    }
}
