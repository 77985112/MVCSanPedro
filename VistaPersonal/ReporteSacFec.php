<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../logout.php");
    exit();
}

require_once __DIR__ . '/../Controlador/controladorAcceso.php';
ControladorAcceso::exigir('emitidos');

require_once __DIR__ . '/../fpdf/fpdf.php';
require_once __DIR__ . '/ReporteCertificadosPDF.php';
require_once __DIR__ . '/../Controlador/controladorEmitidos.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    header('Content-Type: text/plain; charset=UTF-8');
    exit('Abra el reporte desde el calendario de certificados emitidos.');
}

$fecha = $_GET['fecha'] ?? '';
$descripcion = $_GET['descripcion'] ?? '';
try {
    $controller = new CertificadoControlador();
    $certificados = $controller->buscarPorFechaCal($fecha, $descripcion);
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=UTF-8');
    exit($e->getMessage());
} catch (mysqli_sql_exception $e) {
    error_log('Error al consultar certificados emitidos: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    exit('No se pudo generar el reporte. Intente nuevamente.');
}

$meses = [
    1 => 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
];
$seleccionada = new DateTimeImmutable($fecha);
$gestion = $seleccionada->format('Y');

if ($descripcion === 'mes') {
    $periodo = 'Mensual - ' . $meses[(int) $seleccionada->format('n')] . ' ' . $gestion;
    $nombreArchivo = 'Certificados_mensual_' . $seleccionada->format('Y-m') . '.pdf';
} else {
    $periodo = 'Diario - ' . $seleccionada->format('d/m/Y');
    $nombreArchivo = 'Certificados_diario_' . $fecha . '.pdf';
}

$pdf = new ReporteCertificadosPDF();
$pdf->generar($certificados, $periodo, $gestion, $nombreArchivo);
