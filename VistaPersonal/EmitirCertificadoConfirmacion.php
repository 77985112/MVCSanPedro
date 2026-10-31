<?php
require_once '../Controlador/controladorSolicitud.php';
require('../fpdf/fpdf.php');

$controller = new ControladorInsSacramento();
$mensaje = '';

if (isset($_GET['CodIns'])) {
    $codIns = $_GET['CodIns'];
    $certificadoData = $controller->VerCertificadoConfirmacion($codIns);

    if ($certificadoData) {
        $certificadoData['NombrePadre'] = trim((string) ($certificadoData['NombrePadreManual'] ?? '')) !== ''
            ? $certificadoData['NombrePadreManual'] : ($certificadoData['NombrePadre'] ?? '');
        $certificadoData['NombreMadre'] = trim((string) ($certificadoData['NombreMadreManual'] ?? '')) !== ''
            ? $certificadoData['NombreMadreManual'] : ($certificadoData['NombreMadre'] ?? '');
        $certificadoData['NombrePadrino'] = trim((string) ($certificadoData['NombrePadrinoManual'] ?? '')) !== ''
            ? $certificadoData['NombrePadrinoManual'] : ($certificadoData['NombrePadrino'] ?? '');
        $certificadoData['NombreMadrina'] = trim((string) ($certificadoData['NombreMadrinaManual'] ?? '')) !== ''
            ? $certificadoData['NombreMadrinaManual'] : ($certificadoData['NombreMadrina'] ?? '');
        $pdf = new FPDF('P', 'mm', array(150, 150));
        $pdf->AddPage();
        
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, iconv('UTF-8', 'Windows-1252//IGNORE', 'Detalle de Certificado de Confirmación'), 0, 1, 'C');

        $pdf->SetFont('Arial', '', 12);
        $offsetX = 50;

        $pdf->Cell($offsetX, 10, 'Confirmante:', 0, 0);
        $pdf->Cell(0, 10, ($certificadoData['Celebrante']), 0, 1);

        $pdf->Cell($offsetX, 10, 'Padrino:', 0, 0);
        $pdf->Cell(0, 10, ($certificadoData['NombrePadrino']), 0, 1);

        $pdf->Cell($offsetX, 10, 'Madrina:', 0, 0);
        $pdf->Cell(0, 10, ($certificadoData['NombreMadrina']), 0, 1);

        $pdf->Cell($offsetX, 10, 'Fecha Realizado:', 0, 0);
        $pdf->Cell(0, 10, ($certificadoData['FechaReal']), 0, 1);

        $pdf->Output('I', $certificadoData['Celebrante'].'- Certificado_Confirmacion.pdf');
        exit();
    } else {
        echo "No se encontraron datos para el certificado.";
    }
}
?>