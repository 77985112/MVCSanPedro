<?php
require_once '../Modelo/modeloReporteAsistencia.php';
require_once '../fpdf/fpdf.php';
require_once '../FPDI-master/src/autoload.php';

class ReporteAsistenciaControlador {
    private $modelo;

    public function __construct() {
        $this->modelo = new ReporteAsistenciaModelo();
    }

    public function generarReportePDF($sacramento, $anio) {
        // Obtener datos del modelo
        $catequistas = $this->modelo->obtenerCatequistasPorSacramento($sacramento);
        $fechasAsistencia = $this->modelo->obtenerFechasAsistencia($sacramento, $anio);
        $asistencias = $this->modelo->obtenerAsistenciasCatequistas($sacramento, $anio);
        
        // Generar PDF
        $pdf = new FPDF('L', 'mm', 'A4');
        $pdf->AddPage();
        
        // Configuración de fuentes
        $pdf->SetFont('Arial', 'B', 16);
        
        // Título
        $pdf->Cell(0, 10, utf8_decode('Asistencia de Catequistas - ' . $anio), 0, 1, 'C');
        $pdf->Cell(0, 10, utf8_decode('Sacramento: ' . $sacramento), 0, 1, 'C');
        $pdf->Ln(5);
        
        // Cabecera de la tabla
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(70, 7, 'Nombre Completo', 1, 0, 'C');
        $pdf->Cell(30, 7, 'Grupo', 1, 0, 'C');
        
        // Cabeceras de fechas
        foreach ($fechasAsistencia as $fecha) {
            $fechaFormateada = date('d/m', strtotime($fecha['FechaAsisCat']));
            $pdf->Cell(12, 7, $fechaFormateada, 1, 0, 'C');
        }
        $pdf->Cell(20, 7, 'Total Faltas', 1, 1, 'C');
        
        // Datos de la tabla
        $pdf->SetFont('Arial', '', 8);
        
        foreach ($catequistas as $catequista) {
            $pdf->Cell(70, 6, utf8_decode($catequista['Nombre'] . ' ' . $catequista['ApPaterno'] . ' ' . $catequista['ApMaterno']), 1, 0, 'L');
            $pdf->Cell(30, 6, utf8_decode($catequista['NombreGrupo']), 1, 0, 'C');
            
            $totalFaltas = 0;
            
            foreach ($fechasAsistencia as $fecha) {
                $asistencia = '-';
                
                foreach ($asistencias as $asis) {
                    if ($asis['CiCat'] == $catequista['CiCat'] && $asis['FechaAsisCat'] == $fecha['FechaAsisCat']) {
                        $asistencia = ($asis['DetalleAsis'] == 'Presente') ? 'P' : 'F';
                        if ($asistencia == 'F') $totalFaltas++;
                        break;
                    }
                }
                
                $pdf->Cell(12, 6, $asistencia, 1, 0, 'C');
            }
            
            $pdf->Cell(20, 6, $totalFaltas, 1, 1, 'C');
        }
        
        // Pie de página
        $pdf->SetY(-15);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(0, 10, utf8_decode('Página ' . $pdf->PageNo()), 0, 0, 'C');
        
        // Salida del PDF
        $pdf->Output('Asistencia_Catequistas_' . $sacramento . '_' . $anio . '.pdf', 'I');
    }
}
?>