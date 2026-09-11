<?php
session_start();
if (!isset($_SESSION['CiPersona'])) {
    header("Location: ../logout.php");
    exit();
}

$celebranteData = $_SESSION['CiPersona'];
$CelebranteNombre = $celebranteData['Nombre'];
$CelebranteApPaterno = $celebranteData['ApPaterno'];
$CelebranteApMaterno = $celebranteData['ApMaterno'];
$CelebranteSacramento = $celebranteData['Sacramento'];

require_once('../fpdf/fpdf.php');
require_once('../FPDI-master/src/autoload.php');
require_once '../Controlador/controladorReportesAll.php';
require_once('../Conexion/Conexion.php');

$conexion = new Conexion();
$controller = new controladorReporteGeneral($conexion);

$ins = $celebranteData['IdInscripcion'];
$celebrantes = $controller->obtenerReuniones($ins);
$catequistas = $controller->obtenerCatApp($celebranteData['Sacramento'], $celebranteData['IdGrupo']);

class CertificadoPDF extends FPDF
{
    public function generarPDF($celebrantes, $catequistas, $nombreCelebrante, $apPaterno, $apMaterno, $sacramento)
    {
        $this->AddPage('P', 'Letter');

        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, "Mi estado en Asistencia a Reuniones " . date('Y'), 0, 1, 'C');
        $this->Ln(0);

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
        $fechaActual = date('d') . ' de ' . $meses[date('n')] . ' de ' . date('Y');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 10, "Fecha: $fechaActual", 0, 1, 'R');
        $this->Ln(1);

        if (!empty($catequistas)) {
            $this->SetFont('Arial', '', 12);
            $nombresCatequistas = implode(', ', array_column($catequistas, 'Datoscat'));
            $this->MultiCell(0, 10, "Responsables: ". iconv('UTF-8', 'Windows-1252//IGNORE', $nombresCatequistas), 0, 'L');
        } else {
            $this->Cell(0, 10, 'No hay catequistas registrados.', 0, 1, 'L');
        }

        if (!empty($celebrantes)) {
            $this->SetFont('Arial', '', 12);
            $this->MultiCell(0, 10, "Catequisando: " . "$nombreCelebrante $apPaterno $apMaterno | " . iconv('UTF-8', 'Windows-1252//IGNORE', $sacramento), 0, 'L');
            $this->Ln(2);

            $this->SetFont('Arial', 'B', 12);
            $this->Cell(50, 5,  iconv('UTF-8', 'Windows-1252//IGNORE', 'Fecha de Reunión'), 1, 0, 'C');
            $this->Cell(70, 5, 'Detalle de Asistencia', 1, 0, 'C');
            $this->Cell(50, 5, 'Monto a Cancelar', 1, 0, 'C');
            $this->Ln();

            $montoAll=0;
            foreach ($celebrantes as $celeb) {
                $this->SetFont('Arial', '', 10);
                $this->Cell(50, 5, iconv('UTF-8', 'Windows-1252//IGNORE', $celeb['FechaReu']), 1);
                $this->Cell(70, 5, iconv('UTF-8', 'Windows-1252//IGNORE', $celeb['DetalleAsis']), 1);
                $this->Cell(50, 5, iconv('UTF-8', 'Windows-1252//IGNORE', $celeb['MontoAsis']), 1);
                $this->Ln();
                $montoAll=$montoAll+$celeb['MontoAsis'];
            }
            $this->Ln(5);
            $this->SetFont('Arial', 'B', 12);
            $this->Cell(50, 5, 'Monto Total', 0, 'C');
            $this->SetFont('Arial', 'B', 14);
            $this->Cell(50, 5, $montoAll.' Bs');
            $this->Ln(5);
        } else {
            $this->Cell(0, 10, 'No se encontraron Asistencias.', 0, 1, 'L');
        }

        $this->Ln(1);
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 10, iconv('UTF-8', 'Windows-1252//IGNORE', 'Este registro (impreso) es válido con el sello de coordinación...'), 0, 1, 'L');
        $this->Output();
    }
}

$pdf = new CertificadoPDF();
$pdf->generarPDF(
    $celebrantes,
    $catequistas,
    $CelebranteNombre,
    $CelebranteApPaterno,
    $CelebranteApMaterno,
    $CelebranteSacramento
);
?>