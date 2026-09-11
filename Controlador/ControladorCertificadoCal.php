<?php
require_once '../Modelo/ModeloCertificadoCal.php';

class CertificadoController
{
    private $model;

    public function __construct()
    {
        $this->model = new CertificadoModel();
    }

    public function mostrarCertificados($fecha) {
        header('Content-Type: application/json');
        echo json_encode($this->model->obtenerCertificadosPorFecha($fecha));
    }

    public function getReservasByMes($mes, $year)
    {
        header('Content-Type: application/json');
        echo json_encode($this->model->getReservasByMes($mes, $year));
    }
}

if (isset($_GET['fecha'])) {
    $fecha = $_GET['fecha'];
    $controller = new CertificadoController();
    $controller->mostrarCertificados($fecha);
} elseif (isset($_GET['mes']) && isset($_GET['year'])) {
    $mes = $_GET['mes'];
    $year = $_GET['year'];
    $controller = new CertificadoController();
    $controller->getReservasByMes($mes, $year);
}
