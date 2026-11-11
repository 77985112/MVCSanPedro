<?php
require('../fpdf/fpdf.php');
session_start();

if (!isset($_SESSION['CiPersona'])) {
    header("Location: ../logout.php");
    exit();
}

$catequizando = $_SESSION['registroCatequizando'] ?? null;
if (!$catequizando || empty($catequizando['ClaveCel'])) {
    header("Location: VistaDocCel.php");
    exit();
}

$texto = function ($valor) {
    return iconv('UTF-8', 'Windows-1252//IGNORE', (string) $valor);
};

$pdf = new FPDF('P', 'mm', array(100, 100));
$pdf->AddPage();

// Logo de la parroquia en la esquina superior derecha.
$pdf->Image('../assets/images/logo.png', 75, 4, 20);

$pdf->SetFont('Arial', 'B', 12);
$pdf->SetXY(0, 6);
$pdf->Cell(0, 5, 'San Pedro de Sacaba', 0, 1, 'C');

$pdf->SetFont('Arial', 'B', 9);
$pdf->SetXY(0, 13);
$pdf->Cell(0, 5, 'CATEQUIZANDO', 0, 1, 'C');

// Imagen del santo.
$pdf->Image('../assets/images/Santo/SanPedro.png', 8, 20, 20, 20);

$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(34, 20);
$pdf->Cell(0, 5, $texto('Identificación: ') . $texto($catequizando['Nombre']), 0, 1);
$pdf->SetX(34);
$pdf->Cell(0, 5, $texto('Nombre: ') . $texto($catequizando['NombreCompleto']), 0, 1);
$pdf->SetX(34);
$pdf->Cell(0, 5, $texto('Grupo: ') . $texto($catequizando['NombreGrupo']), 0, 1);
$pdf->SetX(34);
$pdf->Cell(0, 5, $texto('Sacramento: ') . $texto($catequizando['Sacramento']), 0, 1);
$pdf->SetX(34);
$pdf->Cell(0, 5, $texto('Usuario: ') . $texto($catequizando['UsuarioCel']), 0, 1);
$pdf->SetX(34);
$pdf->Cell(0, 5, $texto('Contraseña: ') . $texto($catequizando['ClaveCel']), 0, 1);

// Nota de contraseña de un solo uso.
$pdf->SetFont('Arial', 'I', 6);
$pdf->SetTextColor(120, 120, 120);
$pdf->SetXY(8, 68);
$pdf->Cell(84, 4, $texto('Contraseña de un solo uso. Crea tu contraseña al ingresar.'), 0, 0, 'C');

$documento = $pdf->Output('S');

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="ticket.pdf"');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

echo $documento;
?>
