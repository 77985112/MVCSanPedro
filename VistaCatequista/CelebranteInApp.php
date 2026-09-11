<?php
session_start();
if (!isset($_SESSION['CiPersona'])) {
    header("Location: ../logout.php");
    exit();
}

$Catequista = $_SESSION['CiPersona'];

require_once('../fpdf/fpdf.php');
require_once('../FPDI-master/src/autoload.php');
require_once '../Controlador/controladorReportesAll.php';
require_once('../Conexion/Conexion.php');

$Sac = $Catequista['Sacramento'];
$gr = $Catequista['IdGrupo'];

$conexion = new Conexion();
$controller = new controladorReporteGeneral($conexion);

$celebrantes = $controller->obtenerCeleAppIn($Sac, $gr);
$catequistas = $controller->obtenerCatApp($Sac, $gr);

class CertificadoPDF extends FPDF {
    public function generarPDF($celebrantes, $catequistas) {
        $this->AddPage('L', 'Letter');

        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, "Lista de Grupo " . iconv('UTF-8', 'Windows-1252', ' Gestión ') . date('Y'). " (Inactivos)", 0, 1, 'C');
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
            $this->SetFont('Arial', '', 10);
            $nombres = implode(', ', array_column($catequistas, 'Datoscat'));
            $contactos = implode(', ', array_column($catequistas, 'Contacto'));
            
            $this->MultiCell(0, 10, "Responsables: ".  iconv('UTF-8', 'Windows-1252', $nombres). " | Contactos: $contactos", 0, 'C');
            $this->Ln(1);
        } else {
            $this->Cell(0, 10, 'No hay catequistas registrados.', 0, 1, 'L');
        }

        $anchoColumnaN = 8;
        $anchoColumnaNombres = 50;
        $anchoColumnaApellidos = 50;
        $anchoTotalTabla = $anchoColumnaN + $anchoColumnaNombres + $anchoColumnaApellidos;
        $anchoPagina = $this->GetPageWidth();
        $posicionX = ($anchoPagina - $anchoTotalTabla) / 2;
        $this->SetX($posicionX);
        
        if (!empty($celebrantes)) {
            $this->SetFont('Arial', 'B', 8);
            $this->Cell(10, 5, iconv('UTF-8', 'Windows-1252', 'Nº'), 1, 0, 'C');
            $this->Cell(50, 5, 'NOMBRES', 1, 0, 'C');
            $this->Cell(50, 5, 'APELLIDOS', 1, 0, 'C');
            $this->Ln();
            
            $cont=0;
            $this->SetFont('Arial', '', 8);
            foreach ($celebrantes as $celebrante) {
                $this->SetX($posicionX);
                $cont++;
                $this->Cell(10, 5, $cont, 1, 0, 'C');
                $this->Cell(50, 5,  iconv('UTF-8', 'Windows-1252', $celebrante['Nombre']), 1, 0, 'L');
                $this->Cell(50, 5,  iconv('UTF-8', 'Windows-1252', $celebrante['ApellidosCel']), 1, 0, 'L');
        
                $this->Ln();
            }
        } else {
            $this->Cell(0, 10, 'No hay catequisandos Inactivos.', 0, 1, 'L');
        }
        
        $this->Output();
    }
}

$pdf = new CertificadoPDF();
$pdf->generarPDF($celebrantes, $catequistas);

?>