<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../logout.php");
    exit();
}
$usuario = $_SESSION['usuario'];

require_once('../fpdf/fpdf.php');
require_once '../Controlador/controladorReportesAll.php';
require_once('../Conexion/Conexion.php');

$conexion = new Conexion();
$controller = new controladorReporteGeneral($conexion);
$certificados = $controller->mostrarReporteEmitidos();



class CertificadoPDF extends FPDF
{
    public function generarPDF($certificados)
    {
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

        $this->AddPage();
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Reporte de Certificados Emitidos en la '.iconv('UTF-8', 'Windows-1252//IGNORE', 'gestión ') . date('Y'), 0, 1, 'C');
        $this->Ln(5);

        $dia = date('d');
        $mes = $meses[date('n')];
        $año = date('Y');

        $fechaActual = "$dia de $mes de $año";
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 10, $fechaActual, 0, 1, 'R');
        $this->Ln(5);

        $this->SetFont('Arial', 'B', 10);
        $this->Cell(8, 5, '#', 1);
        $this->Cell(15, 5, 'Estado', 1);
        $this->Cell(25, 5, 'Sacramento', 1);
        $this->Cell(25, 5, 'Documento', 1);
        $this->Cell(30, 5,  iconv('UTF-8', 'Windows-1252//IGNORE', 'Fecha Emisión'), 1);
        $this->Cell(25, 5, 'Cargo', 1);
        $this->Cell(60, 5, 'Datos de Emisor', 1);
        $this->Ln();

        $this->SetFont('Arial', '', 10);
        $cont=0;
        foreach ($certificados as $fila) {
            $cont++;
            switch ($fila['DescripSac']) {
                case 'Bautizo':
                    $fillColor = [255, 255, 255];
                    break;
                case 'Confirmación':
                    $fillColor = [240, 128, 128];
                    break;
                case 'Matrimonio':
                    $fillColor = [175, 238, 238];
                    break;
                default:
                    $fillColor = [255, 255, 255];
                    break;
            }
            $this->SetFillColor($fillColor[0], $fillColor[1], $fillColor[2]);
            $this->Cell(8, 5, $cont, 1, 0, '', true);
            $this->Cell(15, 5, $fila['EstadoCert'], 1, 0, '', true);
            $this->Cell(25, 5,  iconv('UTF-8', 'Windows-1252//IGNORE', $fila['DescripSac']), 1, 0, '', true);
            $this->Cell(25, 5,  iconv('UTF-8', 'Windows-1252//IGNORE', $fila['NroEmision']), 1, 0, '', true);
            $this->Cell(30, 5, $fila['FechaEmision'], 1, 0, '', true);
            $this->Cell(25, 5,  iconv('UTF-8', 'Windows-1252//IGNORE', $fila['DescripCar']), 1, 0, '', true);
            $this->Cell(60, 5,  iconv('UTF-8', 'Windows-1252//IGNORE', $fila['DatosEm']), 1, true);
        }
        $this->Output();
    }
}

$pdf = new CertificadoPDF();
$pdf->generarPDF($certificados);
