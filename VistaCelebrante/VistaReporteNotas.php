<?php
session_start();
if (!isset($_SESSION['CiPersona'])) {
    header("Location: ../logout.php");
    exit();
}
$Celebrante = $_SESSION['CiPersona'];

require_once('../fpdf/fpdf.php');
require_once('../FPDI-master/src/autoload.php');
require_once '../Controlador/controladorReportesAll.php';
require_once('../Conexion/Conexion.php');

$Sac = $Celebrante['Sacramento'];
$gr = $Celebrante['IdGrupo'];
$ins = $Celebrante['IdInscripcion'];

$conexion = new Conexion();
$controller = new controladorReporteGeneral($conexion);

$celebrantes = $controller->obtenerNotas($ins);
$catequistas = $controller->obtenerCatApp($Sac, $gr);

class CertificadoPDF extends FPDF {
    public function generarPDF($celebrantes, $catequistas) {
        $this->AddPage('L', 'Letter');

        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, "Mi estado en Notas " . date('Y'), 0, 1, 'C');
        $this->Ln(0);
        
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        $dia = date('d');
        $mes = $meses[date('n')];
        $año = date('Y');
        $fechaActual = "$dia de $mes de $año";

        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 10, "Fecha: $fechaActual", 0, 1, 'R');
        $this->Ln(1);
        
        if (!empty($catequistas)) {
            $this->SetFont('Arial', '', 12);
            $nombres = implode(', ', array_column($catequistas, 'Datoscat'));
            
            $this->MultiCell(0, 10, "Responsables: ". iconv('UTF-8', 'Windows-1252//IGNORE', $nombres), 0, 'L');
        } else {
            $this->Cell(0, 10, 'No hay catequistas registrados.', 0, 1, 'L');
        }
        
        if (!empty($celebrantes)) {
            $this->SetFont('Arial', '', 12);
            
            foreach ($celebrantes as $celebrante) {
                $sacra = $celebrante['Sacramento'];
                $this->MultiCell(0, 10, "Catequisando: " . iconv('UTF-8', 'Windows-1252//IGNORE', $celebrante['DatoCelebrante']) . " | " . iconv('UTF-8', 'Windows-1252', $sacra), 0, 'L');
            }
            $this->Ln(2);
        
            $this->SetFont('Arial', 'B', 14);
            $this->Cell(25, 10, 'GR', 1, 0, 'C');
            $this->Cell(35, 10, 'EXAMEN 1', 1, 0, 'C');
            $this->Cell(35, 10, 'EXAMEN 2', 1, 0, 'C');
            $this->Cell(35, 10, 'EXAMEN 3', 1, 0, 'C');
            $this->Cell(35, 10, 'EXAMEN 4', 1, 0, 'C');
            $this->Cell(30, 10, 'PROMEDIO', 1, 0, 'C');
            $this->Cell(45, 10, 'ESTADO', 1, 0, 'C');
            $this->Ln();
        
            $this->SetFont('Arial', '', 14);
            foreach ($celebrantes as $celebrante) {
                $total = ($celebrante['NotaExamen1'] + $celebrante['NotaExamen2'] + $celebrante['NotaExamen3'] + $celebrante['NotaExamen4']) / 4;
        
                $this->Cell(25, 10, $celebrante['NombreGrupo'], 1, 0, 'C');
                $this->Cell(35, 10, $celebrante['NotaExamen1'], 1, 0, 'C');
                $this->Cell(35, 10, $celebrante['NotaExamen2'], 1, 0, 'C');
                $this->Cell(35, 10, $celebrante['NotaExamen3'], 1, 0, 'C');
                $this->Cell(35, 10, $celebrante['NotaExamen4'], 1, 0, 'C');
                $this->Cell(30, 10, round($total, 2), 1, 0, 'C');
                $this->Cell(45, 10, $celebrante['EstadoExamen'], 1, 0, 'C');
                
                $this->Ln();
            }
        } else {
            $this->Cell(0, 10, 'No se encontraron Notas.', 0, 1, 'L');
        }
        
        $this->Ln(1);
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 10, iconv('UTF-8', 'Windows-1252', 'Este registro (impreso) es válido con el sello de coordinación...'), 0, 1, 'L');
        $this->Output();
    }
}

$pdf = new CertificadoPDF();
$pdf->generarPDF($celebrantes, $catequistas);

?>