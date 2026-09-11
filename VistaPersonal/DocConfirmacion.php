<?php
require_once '../Controlador/controladorSolicitud.php';
require_once('../fpdf/fpdf.php');
require_once('../FPDI-master/src/autoload.php');

$controller = new ControladorInsSacramento();
$certificadoData = [];

use setasign\Fpdi\Fpdi;

$meses = [
    1 => 'Enero',
    2 => 'Febrero',
    3 => 'Marzo',
    4 => 'Abril',
    5 => 'Mayo',
    6 => 'Junio',
    7 => 'Julio',
    8 => 'Agosto',
    9 => 'Septiembre',
    10 => 'Octubre',
    11 => 'Noviembre',
    12 => 'Diciembre'
];

if (isset($_GET['CodIns'])) {
    $codIns = $_GET['CodIns'];
    $certificadoData = $controller->VerCertificadoConfirmacion($codIns);
    $CiNacimiento = $certificadoData['CiPersona'];
    $certificadoExt = $controller->VerCertificadoExtraC($CiNacimiento, 'Celebrante');

    $pdf = new Fpdi();
    $pdf->setSourceFile('../assets/Documentos/CertificadoConfirmacion.pdf');
    $tplIdx = $pdf->importPage(1);
    $pdf->AddPage();
    $pdf->useTemplate($tplIdx);


    if ($certificadoExt) {

        $pdf->SetFont('Arial', '', 12);
        $pdf->SetXY(55, 81);
        $pdf->Cell(0, 10, $certificadoData['PresbiteroEm']);




        $pdf->SetFont('Arial', '', 16);
        $pdf->SetTextColor(0, 0, 0);

        $pdf->SetFont('Arial', 'B', 16);
        $textWidth = $pdf->GetStringWidth($certificadoData['Celebrante']);
        $pageWidth = $pdf->GetPageWidth();
        $x = ($pageWidth - $textWidth) / 2;
        $pdf->SetXY($x, 143);
        $pdf->Write(0, $certificadoData['Celebrante']);


        $pdf->SetFont('Arial', '', 12);
        $pdf->SetXY(137, 99);
        $pdf->Cell(0, 10,  $certificadoData['NroLibro']);

        $pdf->SetXY(155, 99);
        $pdf->Cell(0, 10,  $certificadoData['NroPag']);

        $pdf->SetXY(174, 99);
        $pdf->Cell(0, 10,  $certificadoData['NroPart']);


        $fechaC = strtotime($certificadoData['FechaReal']);
        $diac = date("d", $fechaC);
        $mesc = $meses[date("n", $fechaC)];
        $añoc = date("Y", $fechaC);
    
        $pdf->SetXY(128, 117);
        $pdf->Cell(0, 10, $diac);
    
        $pdf->SetXY(157, 117);
        $pdf->Cell(0, 10, $mesc);
    
        $pdf->SetXY(50, 123);
        $pdf->Cell(0, 10, $añoc);

        $pdf->SetXY(77, 123);
        $pdf->Cell(0, 10,  $certificadoData['CelebrantePres']);

        $pdf->SetXY(80, 152);
        $pdf->Cell(0, 10, $certificadoData['NombrePadrino']. ' y ' .$certificadoData['NombreMadrina']);


        $pdf->SetXY(78, 158);
        $pdf->Cell(0, 10, $certificadoExt['ParroquiaBau']);

        $pdf->SetXY(78, 164);
        $pdf->Cell(0, 10, $certificadoExt['NrLib']);

        $pdf->SetXY(102, 164);
        $pdf->Cell(0, 10, $certificadoExt['NrPag']);

        $pdf->SetXY(138, 164);
        $pdf->Cell(0, 10, $certificadoExt['NrPart']);



        $fechaEmi = strtotime($certificadoExt['FechaBau']);
        $diat = date("d", $fechaEmi);
        $mest = $meses[date('n', $fechaEmi)];
        $añot = date('Y', $fechaEmi);

        $pdf->SetXY(136, 158);
        $pdf->Cell(0, 10, $diat);

        $pdf->SetXY(150, 158);
        $pdf->Cell(0, 10, $mest);

        $pdf->SetXY(22, 164);
        $pdf->Cell(0, 10, $añot);

        $pdf->SetXY(45, 170);
        $pdf->Cell(0, 10, $certificadoData['PresbiteroEm']);

        $pdf->SetXY(60, 181);
        $pdf->Cell(0, 10, $certificadoData['Observacion']);


        $dia = date('d');
        $mes = $meses[date('n')];
        $año = $año = date('Y');

        $pdf->SetXY(77, 205);
        $pdf->Cell(0, 10, $dia);

        $pdf->SetXY(93, 205);
        $pdf->Cell(0, 10, $mes);

        $pdf->SetXY(127, 205);
        $pdf->Cell(0, 10, $año);
    } else {
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetXY(80, 140);
        $pdf->Write(0, 'Datos No encontrados...');

        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetXY(45, 145);
        $pdf->Write(0, 'Primero debe registrar todos los documentos necesarios...');
    }


    $tmpFile = tempnam(sys_get_temp_dir(), 'cert_conf_');
    $pdf->Output('F', $tmpFile);

    try {
        require_once __DIR__ . '/../Controlador/controladorGoogleDrive.php';
        $drive = new ControladorGoogleDrive();
        if ($drive->estaConectado()) {
            $pdfContent = file_get_contents($tmpFile);
            $drive->respaldarPDF($pdfContent, 'Confirmacion', $certificadoData['Celebrante']);
        }
    } catch (Exception $e) {
    }

    @unlink($tmpFile);
    $pdf->Output('I', $certificadoData['Celebrante'] . '-Certificado-Confirmacion.pdf');
} else {
    echo "No se recibieron datos.";
}
