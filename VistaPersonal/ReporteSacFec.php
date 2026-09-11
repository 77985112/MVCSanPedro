<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../logout.php");
    exit();
}

require_once('../fpdf/fpdf.php');
require_once '../controlador/ControladorEmitidos.php';
require_once('../Conexion/Conexion.php');

$conexion = new Conexion();
$controller = new CertificadoControlador($conexion);


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $fecha = $_GET['fecha'];
    $descripcion =$_GET['descripcion'];
    if($descripcion=='todos')
    {
        $certificados = $controller->buscarPorFechaCal($fecha, $descripcion);
    }
    else if($descripcion=='mes')
    {
        $certificados = $controller->buscarPorFechaCal($fecha, $descripcion);
    }
}

class CertificadoPDF extends FPDF {
    public function generarPDF($certificados, $tipo, $fecha, $descripcion) {
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
        if ($tipo == 'por_fecha') {
            $this->Cell(0, 10, "Certificados Emitidos en la Fecha: " . htmlspecialchars($fecha), 0, 1, 'C');
        } else {
            $this->Cell(0, 10, "Certificados Emitidos en la Fecha: " . htmlspecialchars($fecha) . " para '" .   iconv('UTF-8', 'Windows-1252//IGNORE', htmlspecialchars($descripcion))."'", 0, 1, 'C');
        }
        $this->Ln(0);

        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 10, 'Se muestra el detalle de los certificados emitidos.', 0, 1, 'C');
        $this->Ln(0);

        $fechaActual = date('d') . ' de ' . $meses[date('n')] . ' de ' . date('Y');
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
        return $this->Output();
    }
}

$pdf = new CertificadoPDF();
$pdf->generarPDF($certificados,'por_fecha_y_sacramento',$fecha,$descripcion);

?>