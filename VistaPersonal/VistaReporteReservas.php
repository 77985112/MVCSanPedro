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
$reservados = $controller->obtenerReservas();

usort($reservados, function($a, $b) {
    return strtotime($a['FechaReal']) - strtotime($b['FechaReal']);
});



class CertificadoPDF extends FPDF
{
    public function generarPDF($reservados)
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
        $this->Cell(0, 10, 'Reporte de Reservas realizadas en la '.iconv('UTF-8', 'Windows-1252//IGNORE', 'gestión ') . date('Y'), 0, 1, 'C');
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
        $this->Cell(25, 5, 'Estado', 1);
        $this->Cell(34, 5, 'Sacramento/Misa', 1);
        $this->Cell(23, 5, 'Fecha', 1);
        $this->Cell(18, 5, 'Hora', 1);
        $this->Cell(22, 5, 'Cargo', 1);
        $this->Cell(60, 5, 'Datos de Reserva', 1);
        $this->Ln();

        $this->SetFont('Arial', '', 10);
        $cont=0;
        foreach ($reservados as $fila) {
            $cont++;
            
            $sacramento = strtolower($fila['DescripSac']);
            
            if ($fila['EstadoRes'] === 'Cancelado') {
                $fillColor = [0, 0, 0];
                $textColor = [255, 255, 255];
            } else {
                $textColor = [0, 0, 0];
                
                if (strpos($sacramento, 'confirmacion') !== false) {
                    $fillColor = [254, 202, 202];
                } elseif (strpos($sacramento, 'comunion') !== false || strpos($sacramento, 'primera') !== false) {
                    $fillColor = [254, 240, 138];
                } elseif (strpos($sacramento, 'matrimonio') !== false) {
                    $fillColor = [186, 230, 253];
                } elseif (strpos($sacramento, 'misa') !== false) {
                    $fillColor = [187, 247, 208];
                } elseif (strpos($sacramento, 'bautizo') !== false) {
                    $fillColor = [255, 255, 255];
                } else {
                    $fillColor = [255, 255, 255];
                }
            }
            
            $this->SetFillColor($fillColor[0], $fillColor[1], $fillColor[2]);
            $this->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
            $this->Cell(8, 5, $cont, 1, 0, '', true);
            $this->Cell(25, 5, $fila['EstadoRes'], 1, 0, '', true);
            $this->Cell(34, 5,  iconv('UTF-8', 'Windows-1252//IGNORE', $fila['DescripSac']), 1, 0, '', true);
            $this->Cell(23, 5, $fila['FechaReal'], 1, 0, '', true);
            $this->Cell(18, 5, $fila['HoraReal'], 1, 0, '', true);
            $this->Cell(22, 5,  iconv('UTF-8', 'Windows-1252//IGNORE', $fila['DescripCar']), 1, 0, '', true);
            $this->Cell(60, 5,  iconv('UTF-8', 'Windows-1252//IGNORE', $fila['DatosEm']), 1, true);
        }
        $this->SetTextColor(0, 0, 0);
        $this->Output();
    }
}

$pdf = new CertificadoPDF();
$pdf->generarPDF($reservados);
?>