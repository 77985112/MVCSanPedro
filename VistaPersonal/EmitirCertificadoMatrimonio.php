<?php
require_once '../Controlador/controladorSolicitud.php';
require('../fpdf/fpdf.php');

$controller = new ControladorInsSacramento();
$mensaje = '';

if (isset($_GET['CodIns'])) {
    $codIns = $_GET['CodIns'];
    $certificadoData = $controller->VerCertificadoMatrimonio($codIns);

    if ($certificadoData) {
        $pdf = new FPDF('P', 'mm', array(150, 150));
        $pdf->AddPage();

        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'Detalle de Certificado de Matrimonio', 0, 1, 'C');

        $pdf->SetFont('Arial', '', 12);
        $offsetX = 50;

        $pdf->Cell($offsetX, 10, 'Novio:', 0, 0);
        $pdf->Cell(0, 10, ($certificadoData['NombreNovio']), 0, 1);

        $pdf->Cell($offsetX, 10, 'Novia:', 0, 0);
        $pdf->Cell(0, 10, ($certificadoData['NombreNovia']), 0, 1);

        $pdf->Cell($offsetX, 10, 'Padre del Novio', 0, 0);
        $pdf->Cell(0, 10, ($certificadoData['NombrePadreNovio']), 0, 1);

        $pdf->Cell($offsetX, 10, 'Madre del Novio', 0, 0);
        $pdf->Cell(0, 10, ($certificadoData['NombreMadreNovio']), 0, 1);

        $pdf->Cell($offsetX, 10, 'Padre del Novia', 0, 0);
        $pdf->Cell(0, 10, ($certificadoData['NombrePadreNovia']), 0, 1);

        $pdf->Cell($offsetX, 10, 'Madre del Novia', 0, 0);
        $pdf->Cell(0, 10, ($certificadoData['NombreMadreNovia']), 0, 1);

        $pdf->Cell($offsetX, 10, 'Padrino:', 0, 0);
        $pdf->Cell(0, 10, ($certificadoData['NombrePadrino']), 0, 1);

        $pdf->Cell($offsetX, 10, 'Madrina:', 0, 0);
        $pdf->Cell(0, 10, ($certificadoData['NombreMadrina']), 0, 1);

        $pdf->Cell($offsetX, 10, 'Fecha Realizado:', 0, 0);
        $pdf->Cell(0, 10, ($certificadoData['FechaReal']), 0, 1);

        $pdf->Cell($offsetX, 10, 'Hora Realizado:', 0, 0);
        $pdf->Cell(0, 10, ($certificadoData['HoraReal']), 0, 1);



        $pdf->Output('I', 'Certificado_Matrimonio.pdf');
        exit();
    } else {
        echo "No se encontraron datos para el certificado.";
    }
}
?>