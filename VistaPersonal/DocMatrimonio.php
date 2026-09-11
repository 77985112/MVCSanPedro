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
    $certificadoData = $controller->VerCertificadoMatrimonio($codIns);

    $CiNovio = $certificadoData['CiNovio'];
    $certificadoExt = $controller->VerCertificadoExtraB($CiNovio,"Novio");

    $CiNovia = $certificadoData['CiNovia'];
    $certificadoExtN = $controller->VerCertificadoExtraB($CiNovia,"Novia");

    $pdf = new Fpdi();
    $pdf->setSourceFile('../assets/Documentos/CertificadoMatrimonio.pdf');
    $tplIdx = $pdf->importPage(1);
    $pdf->AddPage();
    $pdf->useTemplate($tplIdx);

    if ($certificadoExt && $certificadoExtN) {


        $pdf->SetTextColor(0, 0, 0);

        $pdf->SetFont('Arial', 'B', 16);
        $textWidth = $pdf->GetStringWidth($certificadoData['NombreNovio'] . ' ' . $certificadoData['NombreNovia']);
        $pageWidth = $pdf->GetPageWidth();
        $x = ($pageWidth - $textWidth) / 2;
        $pdf->SetXY($x, 93);
        $pdf->Write(0, $certificadoData['NombreNovio'] . ' y ' . $certificadoData['NombreNovia']);

        $pdf->SetFont('Arial', '', 12);
        $pdf->SetXY(83, 68);
        $pdf->Cell(0, 10,  $certificadoData['NroLibro']);

        $pdf->SetXY(116, 68);
        $pdf->Cell(0, 10,  $certificadoData['NroPag']);

        $pdf->SetXY(144, 68);
        $pdf->Cell(0, 10,  $certificadoData['NroPart']);

        $fechaComoEntero = strtotime($certificadoData['FechaReal']);
        $diam = date("d", $fechaComoEntero);
        $mesm = $meses[date("n", $fechaComoEntero)];
        $añom = date("Y", $fechaComoEntero);

        $pdf->SetXY(40, 106);
        $pdf->Cell(0, 10, $diam);

        $pdf->SetXY(80, 106);
        $pdf->Cell(0, 10, $mesm);

        $pdf->SetXY(150, 106);
        $pdf->Cell(0, 10, $añom);

        $pdf->SetXY(105, 124);
        $pdf->Cell(0, 10,  $certificadoData['CelebrantePres']);

        $pdf->SetXY(35, 141);
        $pdf->Write(0, $certificadoData['NombreNovio']);

        $pdf->SetXY(150, 142);
        $pdf->Cell(0, 10, iconv('UTF-8', 'Windows-1252//IGNORE', 'Legítimo(a)'));

        $pdf->SetXY(35, 153);
        $pdf->Write(0, $certificadoData['NombrePadreNovio']);

        $pdf->SetXY(120, 153);
        $pdf->Write(0, $certificadoData['NombreMadreNovio']);


        $fechaNacNovio = new DateTime($certificadoData['fechaNovio']);
        $fechaRealNovio = new DateTime($certificadoData['FechaReal']);
        $edadNovio = $fechaRealNovio->diff($fechaNacNovio)->y;
        $pdf->SetXY(123, 141);
        $pdf->Write(0, $edadNovio);



        if ($certificadoExt) {
            $pdf->SetXY(76, 142);
            $pdf->Cell(0, 10, $certificadoExt['ParroquiaBau']);
        } else {
            $pdf->SetXY(76, 142);
            $pdf->Cell(0, 10, 'No Registrado .-');
        }

        if ($certificadoExtN) {
            $pdf->SetXY(76, 160);
            $pdf->Cell(0, 10, $certificadoExtN['ParroquiaBau']);
        } else {
            $pdf->SetXY(76, 160);
            $pdf->Cell(0, 10, 'No Registrado .-');
        }




        $fechaNacNovia = new DateTime($certificadoData['fechaNovia']);
        $fechaRealNovia = new DateTime($certificadoData['FechaReal']);
        $edadNovia = $fechaRealNovia->diff($fechaNacNovia)->y;
        $pdf->SetXY(125, 160);
        $pdf->Write(0, $edadNovia);


        $pdf->SetXY(35, 159);
        $pdf->Write(0, $certificadoData['NombreNovia']);

        $pdf->SetXY(150, 160);
        $pdf->Cell(0, 10, iconv('UTF-8', 'Windows-1252//IGNORE', 'Legítimo(a)'));

        $pdf->SetXY(35, 171);
        $pdf->Write(0, $certificadoData['NombrePadreNovia']);
        $pdf->SetXY(120, 171);
        $pdf->Write(0, $certificadoData['NombreMadreNovia']);


        $pdf->SetXY(80, 178);
        $pdf->Write(0, $certificadoData['NombreTestigoNovio']);
        $pdf->SetXY(40, 184);
        $pdf->Write(0, $certificadoData['NombreTestigoNovia']);


        $pdf->SetXY(77, 190);
        $pdf->Write(0, $certificadoData['NombrePadrino'] . ' y ' . $certificadoData['NombreMadrina']);

        $pdf->SetXY(85, 196);
        $pdf->Write(0, $certificadoData['PresbiteroEm']);

        $pdf->SetXY(60, 202);
        $pdf->Write(0, $certificadoData['Observacion']);



        $dia = date('d');
        $mes = $meses[date('n')];
        $año = date('Y');

        $pdf->SetXY(95, 220);
        $pdf->Cell(0, 10, $dia);

        $pdf->SetXY(112, 220);
        $pdf->Cell(0, 10, $mes);

        $pdf->SetXY(155, 220);
        $pdf->Cell(0, 10, $año);
    } else {
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetXY(80, 85);
        $pdf->Write(0, 'Datos No encontrados...');

        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetXY(45, 90);
        $pdf->Write(0, 'Primero debe registrar todos los documentos necesarios...');
    }


    $tmpFile = tempnam(sys_get_temp_dir(), 'cert_mat_');
    $pdf->Output('F', $tmpFile);

    try {
        require_once __DIR__ . '/../Controlador/controladorGoogleDrive.php';
        $drive = new ControladorGoogleDrive();
        if ($drive->estaConectado()) {
            $pdfContent = file_get_contents($tmpFile);
            $drive->respaldarPDF($pdfContent, 'Matrimonio', 'Certificado_Matrimonio');
        }
    } catch (Exception $e) {
    }

    @unlink($tmpFile);
    $pdf->Output('I', 'Certificado-Matrimonio.pdf');
} else {
    echo "No se recibieron datos.";
}
