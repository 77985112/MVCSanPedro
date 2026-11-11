<?php
require('../fpdf/fpdf.php');
session_start();

if (!isset($_SESSION['CiPersona'])) {
    header("Location: ../logout.php");
    exit();
}

// El ticket solo se genera para el catequista recién creado o para una
// contraseña de un solo uso recién restablecida.
$catequista = $_SESSION['registroCatequista'] ?? null;
if (!$catequista || empty($catequista['ClaveCat'])) {
    header("Location: CatequistaAdm.php");
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

// Imagen del santo.
$pdf->Image('../assets/images/Santo/SanPedro.png', 8, 20, 20, 20);

$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(34, 20);
$pdf->Cell(0, 5, $texto('Identificación: ') . $texto($catequista['Nombre']), 0, 1);
$pdf->SetX(34);
$pdf->Cell(0, 5, $texto('Nombre: ') . $texto($catequista['NombreCompleto']), 0, 1);
$pdf->SetX(34);
$pdf->Cell(0, 5, $texto('Grupo: ') . $texto($catequista['NombreGrupo']), 0, 1);
$pdf->SetX(34);
$pdf->Cell(0, 5, $texto('Sacramento: ') . $texto($catequista['Sacramento']), 0, 1);
$pdf->SetX(34);
$pdf->Cell(0, 5, $texto('Usuario: ') . $texto($catequista['UsuarioCat']), 0, 1);
$pdf->SetX(34);
$pdf->Cell(0, 5, $texto('Contraseña: ') . $texto($catequista['ClaveCat']), 0, 1);

// Nota de contraseña de un solo uso.
$pdf->SetFont('Arial', 'I', 6);
$pdf->SetTextColor(120, 120, 120);
$pdf->SetXY(8, 68);
$pdf->Cell(84, 4, $texto('Contraseña de un solo uso. Crea tu contraseña al ingresar.'), 0, 0, 'C');

// Se genera el PDF y se envían cabeceras propias para evitar que el
// navegador reutilice un ticket anterior desde la caché.
$documento = $pdf->Output('S');

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="ticket.pdf"');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

echo $documento;
?>
