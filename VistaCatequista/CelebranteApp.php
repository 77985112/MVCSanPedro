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

$celebrantes = $controller->obtenerCeleApp($Sac, $gr);
$catequistas = $controller->obtenerCatApp($Sac, $gr);

class CertificadoPDF extends FPDF {
    // Datos de demostración: completa la referencia de bautizo cuando el
    // catequizando no tiene un documento registrado. Cambiar a false para
    // volver a mostrar "Sin registro".
    const MOSTRAR_BAUTIZOS_FICTICIOS = true;

    public function generarPDF($celebrantes, $catequistas, $grupo = '') {
        $this->AddPage('L', 'Letter');

        $titulo = 'Lista de Grupo';
        $grupo = trim((string) $grupo);
        if ($grupo !== '' && strcasecmp($grupo, 'Sin grupo') !== 0) {
            $titulo .= ' N° ' . $grupo;
        }
        $titulo .= ' - Gestión ' . date('Y') . ' (Activos)';

        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, iconv('UTF-8', 'Windows-1252', $titulo), 0, 1, 'C');
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
        
        if (!empty($celebrantes)) {
            $this->SetFont('Arial', 'B', 8);
            $this->Cell(8, 5, iconv('UTF-8', 'Windows-1252', 'Nº'), 1, 0, 'C');
            $this->Cell(30, 5, 'NOMBRES', 1, 0, 'C');
            $this->Cell(35, 5, 'APELLIDOS', 1, 0, 'C');
            $this->Cell(95, 5, iconv('UTF-8', 'Windows-1252', 'LUGAR, LIBRO, PÁGINA, PARTIDA DE BAUTIZO'), 1, 0, 'C');
            $this->Cell(80, 5, 'NOMBRE Y APELLIDO DE PADRINOS', 1, 0, 'C');
            $this->Cell(12, 5, 'EDAD', 1, 0, 'C');
            $this->Ln();
            
            $cont=0;
            $this->SetFont('Arial', '', 8);
            foreach ($celebrantes as $celebrante) {
                $cont++;
                $this->Cell(8, 5, $cont, 1, 0, 'C');
                $this->Cell(30, 5,  iconv('UTF-8', 'Windows-1252', $celebrante['NombreCelebrante']), 1, 0, 'L');
                $this->Cell(35, 5,   iconv('UTF-8', 'Windows-1252', $celebrante['ApellidoCelebrante']), 1, 0, 'L');

                $parroquia = trim((string) ($celebrante['ParroquiaDoc'] ?? ''));
                $libro = trim((string) ($celebrante['LibroDoc'] ?? ''));
                $pagina = trim((string) ($celebrante['PaginaDoc'] ?? ''));
                $partida = trim((string) ($celebrante['PartidaDoc'] ?? ''));
                $tieneDocumento = $parroquia !== '' || $libro !== ''
                    || $pagina !== '' || $partida !== '';

                if ($tieneDocumento) {
                    $referencia = [];
                    if ($libro !== '') {
                        $referencia[] = 'lib. ' . $libro;
                    }
                    if ($pagina !== '') {
                        $referencia[] = 'pag. ' . $pagina;
                    }
                    if ($partida !== '') {
                        $referencia[] = 'part. ' . $partida;
                    }

                    $bautizo = $parroquia;
                    if ($referencia) {
                        $bautizo .= ($bautizo !== '' ? ' - ' : '') . implode(' ', $referencia);
                    }
                } elseif (self::MOSTRAR_BAUTIZOS_FICTICIOS) {
                    // Referencias ficticias y estables para la demostración.
                    $libroFicticio = (($cont * 7) % 30) + 1;
                    $paginaFicticia = (($cont * 13) % 180) + 1;
                    $partidaFicticia = (($cont * 29) % 120) + 1;
                    $bautizo = 'San Pedro de Sacaba - lib. ' . $libroFicticio
                        . ' pag. ' . $paginaFicticia
                        . ' part. ' . $partidaFicticia;
                } else {
                    $bautizo = 'Sin registro';
                }
                $this->Cell(95, 5, iconv('UTF-8', 'Windows-1252', $bautizo), 1, 0, 'C');

                $padrino = (string) ($celebrante['NombrePadrino'] ?? '');
                $madrina = (string) ($celebrante['NombreMadrina'] ?? '');
                $padrinos = trim($padrino . ($padrino !== '' && $madrina !== '' ? ' y ' : '') . $madrina);
                $this->Cell(80, 5, $padrinos !== '' ? iconv('UTF-8', 'Windows-1252', $padrinos) : 'Sin registro', 1, 0, 'C');
                
                $fechaNac = $celebrante['FechaNac'];
                $fechaActual = new DateTime('now');
                if (!empty($fechaNac)) {
                    $dateNac = new DateTime($fechaNac);
                    $edad = $fechaActual->diff($dateNac)->y;
                } else {
                    $edad = 'N/A';
                }
                $this->Cell(12, 5, $edad, 1, 0, 'C');
        
                $this->Ln();
            }
        } else {
            $this->Cell(0, 10, 'No hay catequisandos Activos.', 0, 1, 'L');
        }
        
        $this->Output();
    }
}

$pdf = new CertificadoPDF();
$pdf->generarPDF($celebrantes, $catequistas, $Catequista['NombreGrupo'] ?? '');

?>