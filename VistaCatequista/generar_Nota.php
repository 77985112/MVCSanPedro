<?php
require '../fpdf/fpdf.php';
require_once '../Controlador/controladorExamenCel.php';
session_start();

if (!isset($_SESSION['CiPersona'])) {
    header("Location: ../logout.php");
    exit();
}

$Catequista = $_SESSION['CiPersona'];
$sacramento = $Catequista['Sacramento'];
$idGrupo = $Catequista['IdGrupo'];

$contNota = new ContCelebranteExamen();
$celebrantes = $contNota->verCelebrantes($sacramento, $idGrupo);

$pdf = new FPDF();
$pdf->AddPage();

$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Reporte de Notas de Catequisandos G# '.$Catequista['NombreGrupo'], 0, 1, 'C');
$pdf->Ln(0);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Listado de notas asignadas a los catequisandos en cada examen.', 0, 1, 'C');
$pdf->Ln(0);

$meses = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];
$dia = date('d');
$mes = $meses[date('n')];
$año = date('Y');
$fechaActual = "$dia de $mes de $año";

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 10, "Fecha: $fechaActual", 0, 1, 'R');
$pdf->Ln(1);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(10, 10, '#', 1, 0, 'C');
$pdf->Cell(60, 10, 'Nombre Completo', 1, 0, 'C');
$pdf->Cell(20, 10, 'Bim. 1', 1, 0, 'C');
$pdf->Cell(20, 10, 'Bim. 2', 1, 0, 'C');
$pdf->Cell(20, 10, 'Bim. 3', 1, 0, 'C');
$pdf->Cell(20, 10, 'Bim. 4', 1, 0, 'C');
$pdf->Cell(20, 10, 'Promedio', 1, 1, 'C');

$pdf->SetFont('Arial', '', 10);
$Num=0;
foreach ($celebrantes as $celebrante) {
    $Num++;
    $nombreCompleto = $celebrante['Nombre'] . ' ' . $celebrante['ApPaterno'] . ' ' . $celebrante['ApMaterno'];
    $pdf->Cell(10, 10, $Num, 1, 0, 'C');
    $pdf->Cell(60, 10, $nombreCompleto, 1, 0, 'C');
    $pdf->Cell(20, 10, $celebrante['NotaExamen1'], 1, 0, 'C');
    $pdf->Cell(20, 10, $celebrante['NotaExamen2'], 1, 0, 'C');
    $pdf->Cell(20, 10, $celebrante['NotaExamen3'], 1, 0, 'C');
    $pdf->Cell(20, 10, $celebrante['NotaExamen4'], 1, 0, 'C');
    $pdf->Cell(20, 10, round($celebrante['Promedio']), 1, 1, 'C');
}

$pdf->Output('I', 'reporte_notas_celebrantes.pdf');
