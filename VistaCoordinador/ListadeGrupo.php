<?php
require('../fpdf/fpdf.php');
require_once '../Controlador/controladorGrupos.php';
require_once '../Controlador/controladorReportesAll.php';
require_once('../Conexion/Conexion.php');

session_start();
if (!isset($_SESSION['CiPersona'])) {
    header("Location: ../logout.php");
    exit();
}

$Catequista = $_SESSION['CiPersona'];
$idGrupo = $_GET['Gr'];
$nombreGrupo = $_GET['N'];

$conexion = new Conexion();
$controller = new controladorReporteGeneral($conexion);
$catequistas = $controller->obtenerCatApp($Catequista['Sacramento'], $idGrupo);

class PDF extends FPDF
{
    protected $catequistas;
    protected $nombreGrupo;

    public function __construct($catequistas, $nombreGrupo)
    {
        parent::__construct('L', 'mm', 'Letter');
        $this->catequistas = $catequistas;
        $this->nombreGrupo = $nombreGrupo;
    }

    public function Header()
    {
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 10, 'Listado del Grupo "' . $this->nombreGrupo . '"', 0, 1, 'C');
        $this->Ln(2);
        $this->SetFont('Arial', '', 12);
        if (!empty($this->catequistas)) {
            $nombresCatequistas = implode(', ', array_column($this->catequistas, 'Datoscat'));
            $this->MultiCell(0, 10, "Catequistas: ".iconv('UTF-8', 'Windows-1252//IGNORE', $nombresCatequistas), 0, 'L');
        } else {
            $this->Cell(0, 10, 'No hay catequistas registrados.', 0, 1, 'L');
        }
        $this->Ln(5);
    }

}

$pdf = new PDF($catequistas, $nombreGrupo);
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

$contG = new GrupoController();
$datosGrupo = $contG->DatosGrupito($idGrupo);

$headers = array(iconv('UTF-8', 'Windows-1252//IGNORE', 'N°'), 'Nombre', 'Apellidos', 'Edad', 'Fecha Nac.', 'Fecha Doc.', 'Contacto Tutor', 'Sexo');
$w = array(10, 40, 50, 15, 25, 25, 50, 20);

for ($i = 0; $i < count($headers); $i++) {
    $pdf->Cell($w[$i], 7, $headers[$i], 1, 0, 'C');
}
$pdf->Ln();

$i = 1;

foreach ($datosGrupo as $row) {
    if ($pdf->GetY() > 180) {
        $pdf->AddPage();
        for ($i = 0; $i < count($headers); $i++) {
            $pdf->Cell($w[$i], 7, $headers[$i], 1, 0, 'C');
        }
        $pdf->Ln();
    }

    $pdf->Cell($w[0], 6, $i, 'LR', 0, 'C', false);
    $pdf->Cell($w[1], 6, iconv('UTF-8', 'Windows-1252//IGNORE', $row['nombrecel']), 'LR', 0, 'C', false);
    $pdf->Cell($w[2], 6, iconv('UTF-8', 'Windows-1252//IGNORE', $row['ApellidoCel']), 'LR', 0, 'C', false);
    $pdf->Cell($w[3], 6, $row['Edad'] . iconv('UTF-8', 'Windows-1252//IGNORE', ' Años'), 'LR', 0, 'C', false);
    $pdf->Cell($w[4], 6, $row['FechaNac'], 'LR', 0, 'C', false);
    $pdf->Cell($w[5], 6, $row['FechaDoc'], 'LR', 0, 'C', false);
    $pdf->Cell($w[6], 6, $row['ContacTutor'], 'LR', 0, 'C', false);
    $pdf->Cell($w[7], 6, iconv('UTF-8', 'Windows-1252//IGNORE',$row['Sexo']), 'LR', 0, 'C', false);
    $pdf->Ln();
    $i++;
}

$pdf->Cell(array_sum($w), 0, '', 'T');
$pdf->Output();
?>
