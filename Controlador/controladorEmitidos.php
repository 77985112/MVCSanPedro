<?php
require_once __DIR__ . "/../Modelo/modeloEmitidos.php";

class CertificadoControlador {

    private $modelo;

    public function __construct(){
        $this->modelo = new CertificadoModelo();
    }

    public function mostrarTodos() {
        return $this->modelo->obtenerTodosCertificados();
    }

    public function buscarPorFecha($fecha) {
        return $this->modelo->buscarPorFechaEmision($fecha);
    }

    public function buscarPorFechaCal($fecha, $descripcion){
        return $this->modelo->buscarPorFechaCal($fecha, $descripcion);
    }
}
?>