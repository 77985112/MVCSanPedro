<?php
require('../fpdf/fpdf.php');
session_start();

// Solo el Párroco puede generar tickets de acceso.
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['DescripCar'] != 'Párroco') {
    header("Location: ../logout.php");
    exit();
}

// El ticket se genera al crear un personal nuevo, con su contraseña de un solo uso.
$personal = $_SESSION['registroPersonal'] ?? null;
if (!$personal || empty($personal['ClavePer'])) {
    header("Location: vistaGesPersonal.php");
    exit();
}

// Conversión a ISO-8859-1 para las fuentes incluidas de FPDF.
$txt = function ($cadena) {
    return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $cadena);
};

$cargo = $personal['DescripCar'] ?? '';

$pdf = new FPDF('P', 'mm', array(100, 100));
$pdf->SetMargins(0, 0, 0);
$pdf->SetAutoPageBreak(false);
$pdf->AddPage();

// Barra superior dorada.
$pdf->SetFillColor(189, 145, 50);
$pdf->Rect(0, 0, 100, 3, 'F');

// Logo de la parroquia (proporción original 1665x945).
$pdf->Image('../assets/images/logo.png', 5, 5.4, 14, 7.95);

// Título.
$pdf->SetTextColor(45, 45, 45);
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetXY(0, 6.5);
$pdf->Cell(100, 6, $txt('Parroquia San Pedro de Sacaba'), 0, 0, 'C');

$pdf->SetFont('Arial', '', 7);
$pdf->SetTextColor(120, 120, 120);
$pdf->SetXY(0, 14);
$pdf->Cell(100, 4, $txt('Ticket de acceso'), 0, 0, 'C');

// Cargo, destacado debajo del título.
if ($cargo !== '') {
    $pdf->SetFont('Arial', 'B', 8);
    $ancho = $pdf->GetStringWidth($txt($cargo)) + 14;
    $x = (100 - $ancho) / 2;
    $pdf->SetFillColor(189, 145, 50);
    $pdf->Rect($x, 20, $ancho, 6.5, 'F');
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetXY($x, 20);
    $pdf->Cell($ancho, 6.5, $txt($cargo), 0, 0, 'C');
}

// Datos del acceso.
$pdf->SetDrawColor(222, 222, 222);
$pdf->SetLineWidth(0.2);
$filas = [
    'Nombre' => $personal['Nombre'],
    'Usuario' => $personal['UsuarioPer'],
    'Contraseña' => $personal['ClavePer'],
];
$y = 31;
foreach ($filas as $etiqueta => $valor) {
    $pdf->SetXY(12, $y);
    $pdf->SetTextColor(120, 120, 120);
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->Cell(18, 6, $txt($etiqueta), 0, 0);
    $pdf->SetTextColor(40, 40, 40);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(70, 6, $txt($valor), 0, 0);
    $pdf->Line(12, $y + 6.5, 88, $y + 6.5);
    $y += 9;
}

// Imagen de San Pedro, más abajo.
$pdf->Image('../assets/images/Santo/SanPedro.png', 37, 56, 26, 26);

// Nota de contraseña de un solo uso.
$pdf->SetXY(8, 82.5);
$pdf->SetTextColor(120, 120, 120);
$pdf->SetFont('Arial', 'I', 5.5);
$pdf->Cell(84, 3, $txt('Contraseña de un solo uso. Crea tu contraseña al ingresar.'), 0, 0, 'C');

// Pie del ticket.
$pdf->SetDrawColor(189, 145, 50);
$pdf->Line(12, 86, 88, 86);
$pdf->SetTextColor(140, 140, 140);
$pdf->SetFont('Arial', '', 6);
$pdf->SetXY(0, 88);
$pdf->Cell(100, 4, $txt('Documento de uso interno · Parroquia San Pedro de Sacaba'), 0, 0, 'C');

// Se genera el PDF y se envían cabeceras propias para que el navegador no
// reutilice un ticket anterior desde la caché.
$documento = $pdf->Output('S');

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="ticket.pdf"');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

echo $documento;
?>