<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../logout.php");
    exit();
}

require_once('../fpdf/fpdf.php');
require_once __DIR__ . '/ReporteReservasPDF.php';
require_once '../Controlador/controladorReportesAll.php';
require_once('../Conexion/Conexion.php');

$meses = [
    1 => 'Enero',
    2 => 'Febrero',
    3 => 'Marzo',
    4 => 'Abril',
    5 => 'Mayo',
    6 => 'Junio',
    7 => 'Julio',
    8 => 'Agosto',
    9 => 'Septiembre',
    10 => 'Octubre',
    11 => 'Noviembre',
    12 => 'Diciembre'
];

$conexion = new Conexion();
$controller = new controladorReporteGeneral($conexion);

$tipoReporte = strtolower((string) ($_GET['tipo'] ?? 'anual'));

if ($tipoReporte === 'mensual') {
    $anio = (int) ($_GET['anio'] ?? date('Y'));
    $mes = (int) ($_GET['mes'] ?? date('n'));
    $reservas = $controller->obtenerReservasReporte('mensual', $anio, $mes);
    $detalle = $meses[$mes] . ' ' . $anio;
    $archivo = 'Reservas_mensual_' . $anio . '-' . str_pad((string) $mes, 2, '0', STR_PAD_LEFT) . '.pdf';
} elseif ($tipoReporte === 'diario') {
    $anio = (int) ($_GET['anio'] ?? date('Y'));
    $mes = (int) ($_GET['mes'] ?? date('n'));
    $dia = (int) ($_GET['dia'] ?? date('j'));
    $reservas = $controller->obtenerReservasReporte('diario', $anio, $mes, $dia);
    $detalle = str_pad((string) $dia, 2, '0', STR_PAD_LEFT) . '/' . str_pad((string) $mes, 2, '0', STR_PAD_LEFT) . '/' . $anio;
    $archivo = 'Reservas_diario_' . $anio . '-' . str_pad((string) $mes, 2, '0', STR_PAD_LEFT) . '-' . str_pad((string) $dia, 2, '0', STR_PAD_LEFT) . '.pdf';
} else {
    $tipoReporte = 'anual';
    $reservas = $controller->obtenerReservasReporte('anual');
    $detalle = '';
    $archivo = 'Reservas_anual_' . date('Y') . '.pdf';
}

usort($reservas, function($a, $b) {
    $fechaA = strtotime(($a['FechaReal'] ?? '') . ' ' . ($a['HoraReal'] ?? '00:00:00'));
    $fechaB = strtotime(($b['FechaReal'] ?? '') . ' ' . ($b['HoraReal'] ?? '00:00:00'));
    return $fechaA - $fechaB;
});

$pdf = new ReporteReservasPDF();
$pdf->generar(
    $reservas,
    ucfirst($tipoReporte),
    date('Y'),
    $detalle,
    $archivo
);
