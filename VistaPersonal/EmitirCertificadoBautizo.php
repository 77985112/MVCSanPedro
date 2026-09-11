<?php
require_once '../Controlador/controladorSolicitud.php';
require('../fpdf/fpdf.php');

$controller = new ControladorInsSacramento();

if (isset($_GET['CodIns'])) {
    $codIns = $_GET['CodIns'];
    $certificadoData = $controller->VerCertificadoBautizo($codIns);

    if ($certificadoData) {
        $pdf = new FPDF('P', 'mm', array(150, 150));
        $pdf->AddPage();
        
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'Detalle de Certificado de Bautizo', 0, 1, 'C');

        $pdf->SetFont('Arial', '', 12);
        
        $offsetX = 50;

        $pdf->Cell($offsetX, 10, 'Bautizado:', 0, 0);
        $pdf->Cell(0, 10, ($certificadoData['NombreBautizado']), 0, 1);

        $pdf->Cell($offsetX, 10, iconv('UTF-8', 'Windows-1252//IGNORE','Papá:'), 0, 0);
        $pdf->Cell(0, 10, ($certificadoData['NombrePadre']), 0, 1);

        $pdf->Cell($offsetX, 10, iconv('UTF-8', 'Windows-1252//IGNORE','Mamá:'), 0, 0);
        $pdf->Cell(0, 10, ($certificadoData['NombreMadre']), 0, 1);

        $pdf->Cell($offsetX, 10, 'Padrino:', 0, 0);
        $pdf->Cell(0, 10, ($certificadoData['NombrePadrino']), 0, 1);

        $pdf->Cell($offsetX, 10, 'Madrina:', 0, 0);
        $pdf->Cell(0, 10, ($certificadoData['NombreMadrina']), 0, 1);

        $pdf->Cell($offsetX, 10, 'Lugar de Nacimiento:', 0, 0);
        $pdf->Cell(0, 10, ($certificadoData['LugarNac']), 0, 1);

        $pdf->Cell($offsetX, 10, 'Fecha Realizado:', 0, 0);
        $pdf->Cell(0, 10, ($certificadoData['FechaReal']), 0, 1);

        $pdf->Cell($offsetX, 10, 'Hora Realizado:', 0, 0);
        $pdf->Cell(0, 10, ($certificadoData['HoraReal']), 0, 1);

        $pdf->Output('I', $certificadoData['NombreBautizado'].'-'.$certificadoData['CiPersona'].'-Bautizo.pdf');
        exit();
    } else {
        echo "No se encontraron datos para el certificado.";
    }
}
?>