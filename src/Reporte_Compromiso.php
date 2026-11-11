<?php
require('../fpdf/fpdf.php');

$texto = function ($valor) {
    return iconv('UTF-8', 'Windows-1252//IGNORE', (string) $valor);
};

$anio = date('Y');

$pdf = new FPDF('P', 'mm', 'A4');
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(true, 15);
$pdf->AddPage();

// Logo y encabezado.
$pdf->Image('../assets/images/logo.png', 15, 10, 22);
$pdf->SetFont('Arial', 'B', 13);
$pdf->SetXY(42, 10);
$pdf->Cell(153, 8, $texto('COMPROMISO Y REGLAMENTO DE LOS PADRES DE FAMILIA'), 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetX(42);
$pdf->Cell(153, 6, $texto('Catequesis de Primera Comunión - Gestión ' . $anio), 0, 1, 'C');

$pdf->SetDrawColor(130, 130, 130);
$pdf->Line(15, 36, 195, 36);
$pdf->SetY(43);

// Introducción con espacios para llenar.
$pdf->SetFont('Arial', '', 11);
$pdf->MultiCell(0, 7, $texto('Yo, ........................................................................................'), 0, 'L');
$pdf->MultiCell(0, 7, $texto('con C.I. ........................................, en calidad de padre/madre/apoderado'), 0, 'L');
$pdf->MultiCell(0, 7, $texto('de mi hijo/a: ................................................................................'), 0, 'L');
$pdf->MultiCell(0, 7, $texto('me comprometo a cumplir y hacer cumplir los siguientes puntos:'), 0, 'L');
$pdf->Ln(3);

$compromisos = [
    'Enviar puntualmente a mi hijo/a a la catequesis y a la Santa Misa.',
    'Acompañar a mi hijo/a a la Santa Misa de 9:00 a 10:00 am.',
    'La catequesis se desarrolla de 10:00 am a 12:00 pm, además de la Misa.',
    'Asistir y participar en las actividades programadas durante el año de catequesis.',
    'No faltar a las actividades; en caso de ausencia, solicitar licencia o justificar previamente.',
    'Revisar materiales, tareas y comunicaciones; acompañar el proceso de formación.',
    'Mantener el respeto, la disciplina y el buen comportamiento.',
    'Colaborar con los catequistas y con las actividades de la parroquia.',
];

$pdf->SetFont('Arial', '', 11);
foreach ($compromisos as $indice => $compromiso) {
    $pdf->MultiCell(0, 7, $texto(($indice + 1) . '. ' . $compromiso), 0, 'L');
}

$pdf->Ln(3);
$pdf->MultiCell(0, 7, $texto('Entiendo que el incumplimiento de estos compromisos puede afectar el proceso de formación de mi hijo/a.'), 0, 'L');

$pdf->Ln(6);
$pdf->Cell(0, 7, $texto('Sacaba, ......... de ................................ de ' . $anio), 0, 1, 'R');

$pdf->Ln(24);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(95, 7, $texto('Firma: ..................................................'), 0, 0, 'L');
$pdf->Cell(85, 7, $texto('Nombre completo: .................................'), 0, 1, 'L');
$pdf->Ln(4);
$pdf->Cell(95, 7, $texto('C.I.: ......................................................'), 0, 1, 'L');

$pdf->Ln(14);
$pdf->SetFont('Arial', 'I', 9);
$pdf->Cell(0, 5, $texto('Parroquia San Pedro de Sacaba - Catequesis de Primera Comunión'), 0, 1, 'C');

$pdf->Output('I', 'Compromiso_Reglamento_Primera_Comunion_' . $anio . '.pdf');
