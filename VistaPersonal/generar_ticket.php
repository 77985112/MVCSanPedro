<?php
require('../fpdf/fpdf.php');
session_start();

if (!isset($_SESSION['usuario']) || !isset($_SESSION['registroPersonal'])) {
    header("Location: ../logout.php");
    exit();
}

$personal = $_SESSION['registroPersonal'];
$pdf = new FPDF('P', 'mm', array(100, 100));
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 5, 'San Pedro de Sacaba', 0, 1, 'C');
$pdf->Ln();
$pdf->Image('../assets/images/Santo/SanPedro.png', 12, 15, 20, 20);
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(35, 15);
$pdf->Cell(0, 5, 'Nombre: ' . $personal['Nombre'], 0, 1);
$pdf->SetX(35);
$pdf->Cell(0, 5, 'Usuario: ' . $personal['UsuarioPer'], 0, 1);
$pdf->SetX(35);
$pdf->Cell(0, 5, 'Clave: ' . $personal['ClavePer'], 0, 1);
$pdf->Output();
?>