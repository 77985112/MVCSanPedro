<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../logout.php");
    exit();
}

require_once('../fpdf/fpdf.php');
require_once '../Controlador/controladorReportesAll.php';
require_once('../Conexion/Conexion.php');

$conexion = new Conexion();
$controller = new controladorReporteGeneral($conexion);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $tipoReporte = $_GET['tipo'];

    if ($tipoReporte === 'mensual') {
        $anio = $_GET['anio'];
        $mes = $_GET['mes'];
        $reservas = $controller->obtenerReservasMes($anio, $mes);
    } elseif ($tipoReporte === 'diario') {
        $anio = $_GET['anio'];
        $mes = $_GET['mes'];
        $dia = $_GET['dia'];
        $reservas = $controller->obtenerReservasDia($anio, $mes, $dia);
    }
    
    usort($reservas, function($a, $b) {
        return strtotime($a['FechaReal']) - strtotime($b['FechaReal']);
    });
}

class CertificadoPDF extends FPDF
{
    public function generarPDF($reservas, $tipoReporte)
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
        $titulo = ($tipoReporte === 'mensual') ? 'Reporte Mensual' : 'Reporte Diario';
        $this->Cell(0, 10, "$titulo de " . date('Y'), 0, 1, 'C');
        $this->Ln(0);

        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 10, 'Se muestra el detalle de las reservas realizadas.', 0, 1, 'C');
        $this->Ln(0);

        $fechaActual = date('d') . ' de ' . $meses[date('n')] . ' de ' . date('Y');
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
        foreach ($reservas as $index => $fila) {
            if (isset(
                $fila['EstadoRes'],
                $fila['DescripSac'],
                $fila['FechaReal'],
                $fila['HoraReal'],
                $fila['DescripCar'],
                $fila['DatosEm']
            )) {
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
                $this->Cell(8, 5, ($index + 1), 1, 0, '', true);
                $this->Cell(25, 5, iconv('UTF-8', 'Windows-1252//IGNORE', $fila['EstadoRes']), 1, 0, '', true);
                $this->Cell(34,5,iconv('UTF-8','Windows-1252//IGNORE',isset($fila['DescripSac']) ? $fila['DescripSac'] : ''),1,0,'',true);
                $this->Cell(23, 5, $fila['FechaReal'], 1, 0, '', true);
                $this->Cell(18, 5, $fila['HoraReal'], 1, 0, '', true);
                $this->Cell(22, 5, iconv('UTF-8', 'Windows-1252//IGNORE', $fila['DescripCar']), 1, 0, '', true);
                $this->Cell(60, 5, iconv('UTF-8', 'Windows-1252//IGNORE', $fila['DatosEm']), 1, true);
            } else {
                error_log("Datos faltantes en fila: " . json_encode($fila));
            }
        }
        $this->SetTextColor(0, 0, 0);

        $this->Output('I', 'reporte.pdf');
    }
}

$pdf = new CertificadoPDF();
$pdf->generarPDF($reservas, $tipoReporte);
?>