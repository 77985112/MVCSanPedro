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
    $certificadoData = $controller->VerCertificadoBautizo($codIns);
    $CiNacimiento = $certificadoData['CiPersona'];
    $certificadoExt = $controller->VerCertificadoExtra($CiNacimiento);

    $pdf = new Fpdi();
    $pdf->setSourceFile('../assets/Documentos/CertificadoBautizo.pdf');
    $tplIdx = $pdf->importPage(1);
    $pdf->AddPage();
    $pdf->useTemplate($tplIdx);


    if ($certificadoExt)
    {


    $pdf->SetFont('Arial', '', 16);
    $pdf->SetTextColor(0, 0, 0);

    $pdf->SetXY(50, 50);
    $pdf->Cell(0, 10, 'San Pedro de Sacaba');



    $pdf->SetFont('Arial', 'B', 16);
    $textWidth = $pdf->GetStringWidth($certificadoData['NombreBautizado']);
    $pageWidth = $pdf->GetPageWidth();
    $x = ($pageWidth - $textWidth) / 2;
    $pdf->SetXY($x, 100);
    $pdf->Write(0,  iconv('UTF-8', 'Windows-1252//IGNORE', $certificadoData['NombreBautizado']));


    $pdf->SetFont('Arial', '', 12);
    $pdf->SetXY(166, 71);
    $pdf->Cell(0, 10,  $certificadoData['NroLibro']);

    $pdf->SetXY(43, 79);
    $pdf->Cell(0, 10,  $certificadoData['NroPag']);

    $pdf->SetXY(80, 79);
    $pdf->Cell(0, 10,  $certificadoData['NroPart']);

    $pdf->SetXY(80, 108);
    $pdf->Cell(0, 10, '"San Pedro de Sacaba"');

    $pdf->SetXY(50, 123);
    $pdf->Cell(0, 10,  $certificadoData['CelebrantePres']);

    $pdf->SetXY(50, 128);
    $pdf->Cell(0, 10, $certificadoData['NombreCelebrante']);

    $fechaComoEntero = strtotime($certificadoData['FechaReal']);
    $diab = date("d", $fechaComoEntero);
    $mesb = $meses[date("n", $fechaComoEntero)];
    $añob = date("Y", $fechaComoEntero);

    $pdf->SetXY(65, 113);
    $pdf->Cell(0, 10, $diab);

    $pdf->SetXY(140, 113);
    $pdf->Cell(0, 10, $mesb);

    $pdf->SetXY(60, 118);
    $pdf->Cell(0, 10, $añob);

    $pdf->SetXY(30, 133);
    $pdf->Cell(0, 10, $certificadoData['LugarNac']);



    $fechaComoEntero = strtotime($certificadoData['FechaNac']);
    $diaN = date("d", $fechaComoEntero);
    $mesN = $meses[date("n", $fechaComoEntero)];
    $añoN = date("Y", $fechaComoEntero);

    $pdf->SetXY(140, 133);
    $pdf->Cell(0, 10, $diaN);

    $pdf->SetXY(50, 138);
    $pdf->Cell(0, 10, $mesN);

    $pdf->SetXY(120, 138);
    $pdf->Cell(0, 10, $añoN);

    $pdf->SetXY(45, 142);
    $pdf->Cell(0, 10, iconv('UTF-8', 'Windows-1252//IGNORE', 'Legítimo(a)'));

    $pdf->SetXY(90, 142);
    $pdf->Cell(0, 10, $certificadoData['NombrePadre']);

    $pdf->SetXY(50, 147);
    $pdf->Cell(0, 10, $certificadoData['NombreMadre']);

    $pdf->SetXY(50, 152);
    $pdf->Cell(0, 10, 'Esta');

    $pdf->SetXY(125, 152);
    $pdf->Cell(0, 10, $certificadoData['NombrePadrino']);

    $pdf->SetXY(85, 157);
    $pdf->Cell(0, 10, $certificadoData['NombreMadrina']);



    $pdf->SetXY(45, 162);
    $pdf->Cell(0, 10, $certificadoExt['ParroquiaBau']);

    $pdf->SetXY(93, 162);
    $pdf->Cell(0, 10, $certificadoExt['NrLib']);

    $pdf->SetXY(140, 162);
    $pdf->Cell(0, 10, $certificadoExt['NrPart']);

    $fechaEmi = strtotime($certificadoExt['FechaBau']);
    $añot = date("Y", $fechaEmi);
    $pdf->SetXY(175, 162);
    $pdf->Cell(0, 10, $añot);



    $pdf->SetXY(90, 167);
    $pdf->Cell(0, 10, $certificadoData['PresbiteroEm']);

    $pdf->SetXY(25, 179);
    $pdf->Cell(0, 10, $certificadoData['Observacion']);


    $dia = date('d');
    $mes = $meses[date('n')];
    $año = $año = substr(date('Y'), -2);

    $pdf->SetXY(70, 214);
    $pdf->Cell(0, 10, $dia);

    $pdf->SetXY(120, 214);
    $pdf->Cell(0, 10, $mes);

    $pdf->SetXY(175, 214);
    $pdf->Cell(0, 10, $año);
    }
    else
    {
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetXY(80, 45);
        $pdf->Write(0, 'Datos No encontrados...');

        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetXY(45, 50);
        $pdf->Write(0, 'Primero debe registrar todos los documentos necesarios...');
    }


    $pdf->Output('I', $certificadoData['NombreBautizado'] . '-Certificado-Bautizo.pdf');
} else {
    echo "No se recibieron datos.";
}
