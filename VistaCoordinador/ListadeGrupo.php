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
$idGrupo = isset($_GET['Gr']) ? (int) $_GET['Gr'] : 0;
$nombreGrupo = trim((string) ($_GET['N'] ?? ''));

if ($idGrupo <= 0) {
    exit('No se indicó el grupo.');
}

$conexion = new Conexion();
$controller = new controladorReporteGeneral($conexion);
$catequistas = $controller->obtenerCatApp($Catequista['Sacramento'], $idGrupo);

class PDF extends FPDF
{
    protected $catequistas;
    protected $nombreGrupo;
    protected $sacramento;

    public function __construct($catequistas, $nombreGrupo, $sacramento)
    {
        parent::__construct('L', 'mm', 'Letter');
        $this->catequistas = $catequistas;
        $this->nombreGrupo = $nombreGrupo;
        $this->sacramento = $sacramento;
    }

    public function Header()
    {
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 8, 'Listado del Grupo "' . $this->nombreGrupo . '"', 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 6, 'Sacramento: ' . iconv('UTF-8', 'Windows-1252//IGNORE', $this->sacramento), 0, 1, 'C');
        $this->Ln(1);
        $this->SetFont('Arial', '', 11);
        if (!empty($this->catequistas)) {
            $nombresCatequistas = implode(', ', array_column($this->catequistas, 'Datoscat'));
            $this->MultiCell(0, 8, "Catequistas: ".iconv('UTF-8', 'Windows-1252//IGNORE', $nombresCatequistas), 0, 'L');
        } else {
            $this->Cell(0, 8, 'No hay catequistas registrados.', 0, 1, 'L');
        }
        $this->Ln(3);
    }

}

$contG = new GrupoController();
$datosGrupo = $contG->DatosGrupito($idGrupo);
$sacramentoGrupo = $datosGrupo[0]['SacramentoGrupo'] ?? $Catequista['Sacramento'];

$pdf = new PDF($catequistas, $nombreGrupo, $sacramentoGrupo);
$pdf->AddPage();
$pdf->SetFont('Arial', '', 7);

$headers = array(
    iconv('UTF-8', 'Windows-1252//IGNORE', 'N°'),
    'Nombre',
    'Apellidos',
    'Edad',
    'Sexo',
    'Fecha Nac.',
    'Documento',
    'Sacramento',
    'Tutor',
    'Contacto Tutor'
);
$w = array(8, 28, 40, 11, 14, 19, 20, 38, 55, 23);

$pdf->SetFont('Arial', 'B', 7);
for ($i = 0; $i < count($headers); $i++) {
    $pdf->Cell($w[$i], 6, $headers[$i], 1, 0, 'C');
}
$pdf->Ln();
$pdf->SetFont('Arial', '', 7);

$i = 1;

foreach ($datosGrupo as $row) {
    if ($pdf->GetY() > 185) {
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 7);
        for ($i = 0; $i < count($headers); $i++) {
            $pdf->Cell($w[$i], 6, $headers[$i], 1, 0, 'C');
        }
        $pdf->Ln();
        $pdf->SetFont('Arial', '', 7);
    }

    $pdf->Cell($w[0], 6, $i, 'LR', 0, 'C', false);
    $pdf->Cell($w[1], 6, iconv('UTF-8', 'Windows-1252//IGNORE', $row['nombrecel']), 'LR', 0, 'C', false);
    $pdf->Cell($w[2], 6, iconv('UTF-8', 'Windows-1252//IGNORE', $row['ApellidoCel']), 'LR', 0, 'C', false);
    $pdf->Cell($w[3], 6, $row['Edad'] . iconv('UTF-8', 'Windows-1252//IGNORE', ' Años'), 'LR', 0, 'C', false);
    $pdf->Cell($w[4], 6, iconv('UTF-8', 'Windows-1252//IGNORE', $row['Sexo']), 'LR', 0, 'C', false);
    $pdf->Cell($w[5], 6, $row['FechaNac'], 'LR', 0, 'C', false);
    $pdf->Cell($w[6], 6, $row['FechaDoc'], 'LR', 0, 'C', false);
    $pdf->Cell($w[7], 6, iconv('UTF-8', 'Windows-1252//IGNORE', $row['Sacramento']), 'LR', 0, 'C', false);
    $pdf->Cell($w[8], 6, iconv('UTF-8', 'Windows-1252//IGNORE', $row['TutorNombre'] ?? ''), 'LR', 0, 'C', false);
    $pdf->Cell($w[9], 6, $row['TutorContacto'] ?? '', 'LR', 0, 'C', false);
    $pdf->Ln();
    $i++;
}

$pdf->Cell(array_sum($w), 0, '', 'T');
$pdf->Output();
?>
