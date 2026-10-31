<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../logout.php");
    exit();
}
$usuario = $_SESSION['usuario'];
require_once __DIR__ . '/../Controlador/controladorAcceso.php';
ControladorAcceso::exigir('celebracion');

require_once('../fpdf/fpdf.php');
require_once __DIR__ . '/ReporteReservasPDF.php';
require_once '../Controlador/controladorReportesAll.php';
require_once('../Conexion/Conexion.php');

$conexion = new Conexion();
$controller = new controladorReporteGeneral($conexion);
$reservados = $controller->obtenerReservasReporte('anual');

usort($reservados, function($a, $b) {
    $fechaA = strtotime(($a['FechaReal'] ?? '') . ' ' . ($a['HoraReal'] ?? '00:00:00'));
    $fechaB = strtotime(($b['FechaReal'] ?? '') . ' ' . ($b['HoraReal'] ?? '00:00:00'));
    return $fechaA - $fechaB;
});

$gestion = date('Y');
$pdf = new ReporteReservasPDF();
$pdf->generar(
    $reservados,
    'Anual',
    $gestion,
    '',
    'Reservas_anual_' . $gestion . '.pdf'
);
