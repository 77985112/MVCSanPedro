<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../logout.php");
    exit();
}
$usuario = $_SESSION['usuario'];
require_once __DIR__ . '/../Controlador/controladorAcceso.php';
ControladorAcceso::exigir('emitidos');

require_once('../fpdf/fpdf.php');
require_once __DIR__ . '/ReporteCertificadosPDF.php';
require_once '../Controlador/controladorReportesAll.php';
require_once('../Conexion/Conexion.php');

$conexion = new Conexion();
$controller = new controladorReporteGeneral($conexion);
$certificados = $controller->mostrarReporteEmitidos();

$gestion = date('Y');
$pdf = new ReporteCertificadosPDF();
$pdf->generar(
    $certificados,
    'Anual',
    $gestion,
    'Certificados_anual_' . $gestion . '.pdf'
);
