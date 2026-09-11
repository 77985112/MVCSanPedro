<?php
session_start();
if (!isset($_SESSION['CiPersona'])) {
    header("Location: ../logout.php");
    exit();
}
$Catequista = $_SESSION['CiPersona'];

require_once('../fpdf/fpdf.php');
require_once '../Controlador/controladorReportesAll.php';
require_once('../Conexion/Conexion.php');
$codIns = $Catequista['Sacramento'];

$conexion = new Conexion();
$controller = new controladorReporteGeneral($conexion);
$catequistas = $controller->obtenerCatesActivos($codIns);

class CertificadoPDF extends FPDF {
    public function generarPDF($catequistas) {
        if (!empty($catequistas)) {
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

            $this->AddPage('P','Letter');
            $this->SetFont('Arial', 'B', 12);
            $this->Cell(0, 10, 'Reporte de Catequistas "Activos" de la '. iconv('UTF-8', 'Windows-1252//IGNORE', 'gestión ') . date('Y'), 0, 1, 'C');
            $this->Ln(2);

            $dia = date('d');
            $mes = $meses[date('n')];
            $año = date('Y');

            $fechaActual = "$dia de $mes de $año";
            $this->SetFont('Arial', '', 10);
            $this->Cell(0, 10, $fechaActual, 0, 1, 'R');
            $this->Ln(5);

            $this->SetFont('Arial', 'B', 10);
            $this->Cell(8, 5, '#', 1);
            $this->Cell(30, 5, 'Cargo', 1);
            $this->Cell(80, 5, 'Nombre Completo', 1);
            $this->Cell(30, 5, 'Contacto', 1);
            $this->Cell(25, 5, 'Grupo', 1);
            $this->Ln();

            $this->SetFont('Arial', '', 10);
            $cont=0;
            foreach ($catequistas as $fila) {
                $cont++;
                $this->Cell(8, 5, $cont, 1);
                $this->Cell(30, 5, iconv('UTF-8', 'Windows-1252//IGNORE', $fila['RolCat']), 1);
                $this->Cell(80, 5, iconv('UTF-8', 'Windows-1252//IGNORE', $fila['DatosCatequistaCoor']), 1);
                $this->Cell(30, 5, iconv('UTF-8', 'Windows-1252//IGNORE', $fila['ContactoCatequista']), 1);
                $this->Cell(25, 5, iconv('UTF-8', 'Windows-1252//IGNORE', $fila['NombreGrupo']), 1);
                $this->Ln();
            }
            
            $this->Output();
        }
        else
        {
            $this->AddPage();
            $this->SetFont('Arial', 'B', 12);
            $this->Cell(0, 10, 'Reporte de Catequistas "Activos" de la '.iconv('UTF-8', 'Windows-1252//IGNORE', 'gestión ') . date('Y'), 0, 1, 'C');
            $this->Ln(5);
            $this->SetFont('Arial', 'B', 10);
            $this->Cell(50, 10, 'No hay Catequistas Activos...', 0);
            $this->Ln();
            $this->Output();
        }
    }
}

$pdf = new CertificadoPDF();
$pdf->generarPDF($catequistas);
?>