<?php
require('../fpdf/fpdf.php');
session_start();

if (!isset($_SESSION['CiPersona'])) {
    header("Location: ../logout.php");
    exit();
}

$Catequista = $_SESSION['CiPersona'];
$rolActual = $Catequista['RolCat'] ?? '';

if ($rolActual !== 'Coordinador') {
    $destino = isset($Catequista['RolCat'])
        ? '../VistaCatequista/VistaCatequista.php'
        : '../VistaCelebrante/VistaCelebrante.php';
    header('Location: ' . $destino);
    exit();
}
require_once '../Controlador/controladorCatAsis.php';
$controlador = new CatequistaAsisControlador();

$permitidos = ['Primera Comunión', 'Confirmación'];
$sacramento = $_GET['sacramento'] ?? $Catequista['Sacramento'];
if (!in_array($sacramento, $permitidos, true)) {
    $sacramento = $Catequista['Sacramento'];
}

$fecha = $_GET['fecha'] ?? date('Y-m-d');
$dt = DateTime::createFromFormat('Y-m-d', $fecha);
if (!$dt || $dt->format('Y-m-d') !== $fecha) {
    $fecha = date('Y-m-d');
    $dt = new DateTime($fecha);
}

$catequistas = $controlador->obtenerCatequistas($sacramento);

$texto = function ($valor) {
    return iconv('UTF-8', 'Windows-1252//IGNORE', (string) $valor);
};

$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();

// Encabezado.
$pdf->Image('../assets/images/logo.png', 10, 8, 25);
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetXY(0, 10);
$pdf->Cell(0, 8, $texto('Parroquia San Pedro de Sacaba'), 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 6, $texto('Registro de Asistencia de Catequistas'), 0, 1, 'C');
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(0, 5, $texto('Sacramento: ' . $sacramento), 0, 1, 'C');
$pdf->Cell(0, 5, $texto('Fecha: ' . $dt->format('d/m/Y')), 0, 1, 'C');
$pdf->Ln(4);

$cabeceraTabla = function () use ($pdf, $texto) {
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetFillColor(40, 40, 40);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(18, 7, $texto('Grupo'), 1, 0, 'C', true);
    $pdf->Cell(35, 7, $texto('Cargo'), 1, 0, 'C', true);
    $pdf->Cell(107, 7, $texto('Nombre Completo'), 1, 0, 'C', true);
    $pdf->Cell(30, 7, $texto('Estado'), 1, 1, 'C', true);
    $pdf->SetTextColor(0, 0, 0);
};

$cabeceraTabla();
$pdf->SetFont('Arial', '', 8);

$resumen = [];
foreach ($catequistas as $cat) {
    if ($pdf->GetY() > 250) {
        $pdf->AddPage();
        $cabeceraTabla();
        $pdf->SetFont('Arial', '', 8);
    }

    $estado = $controlador->obtenerAsistencia($cat['CiCat'], $cat['IdGrupo'], $fecha);
    if ($estado !== 'Presente' && $estado !== 'Falta' && $estado !== 'Permiso') {
        $estado = 'Sin marcar';
    }

    $grupo = (string) $cat['NombreGrupo'];
    if (!isset($resumen[$grupo])) {
        $resumen[$grupo] = ['Presente' => 0, 'Falta' => 0, 'Permiso' => 0, 'Sin marcar' => 0];
    }
    $resumen[$grupo][$estado]++;

    $pdf->Cell(18, 6, $texto($grupo), 1, 0, 'C');
    $pdf->Cell(35, 6, $texto($cat['RolCat']), 1, 0, 'C');
    $pdf->Cell(107, 6, $texto(trim($cat['Nombre'] . ' ' . $cat['ApPaterno'] . ' ' . $cat['ApMaterno'])), 1, 0, 'L');
    $pdf->Cell(30, 6, $texto($estado), 1, 1, 'C');
}

if (!$catequistas) {
    $pdf->Cell(0, 8, $texto('No hay catequistas registrados para este sacramento.'), 1, 1, 'C');
}

// Resumen por grupo.
if ($pdf->GetY() > 220) {
    $pdf->AddPage();
}

$pdf->Ln(6);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 6, $texto('Resumen por grupo'), 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 8);
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(25, 6, $texto('Grupo'), 1, 0, 'C', true);
$pdf->Cell(25, 6, $texto('Presentes'), 1, 0, 'C', true);
$pdf->Cell(22, 6, $texto('Faltas'), 1, 0, 'C', true);
$pdf->Cell(22, 6, $texto('Permisos'), 1, 0, 'C', true);
$pdf->Cell(25, 6, $texto('Sin marcar'), 1, 0, 'C', true);
$pdf->Cell(25, 6, $texto('Total'), 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 8);
$totales = ['Presente' => 0, 'Falta' => 0, 'Permiso' => 0, 'Sin marcar' => 0];
foreach ($resumen as $grupo => $conteo) {
    $totalGrupo = $conteo['Presente'] + $conteo['Falta'] + $conteo['Permiso'] + $conteo['Sin marcar'];
    $pdf->Cell(25, 6, $texto($grupo), 1, 0, 'C');
    $pdf->Cell(25, 6, $conteo['Presente'], 1, 0, 'C');
    $pdf->Cell(22, 6, $conteo['Falta'], 1, 0, 'C');
    $pdf->Cell(22, 6, $conteo['Permiso'], 1, 0, 'C');
    $pdf->Cell(25, 6, $conteo['Sin marcar'], 1, 0, 'C');
    $pdf->Cell(25, 6, $totalGrupo, 1, 1, 'C');

    foreach ($totales as $clave => $valor) {
        $totales[$clave] += $conteo[$clave];
    }
}

$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(0, 7, $texto(
    'Total general: Presentes ' . $totales['Presente'] .
    ' | Faltas ' . $totales['Falta'] .
    ' | Permisos ' . $totales['Permiso'] .
    ' | Sin marcar ' . $totales['Sin marcar']
), 0, 1, 'L');

$pdf->AliasNbPages();
$pdf->SetY(-15);
$pdf->SetFont('Arial', 'I', 8);
$pdf->Cell(0, 10, $texto('Página ' . $pdf->PageNo() . ' de {nb}'), 0, 0, 'C');

$nombreArchivo = 'Asistencia_Catequistas_' .
    preg_replace('/[^A-Za-z0-9]+/', '_', iconv('UTF-8', 'ASCII//TRANSLIT', $sacramento)) .
    '_' . $fecha . '.pdf';

$pdf->Output('I', $nombreArchivo);
