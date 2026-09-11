<?php
require('../fpdf/fpdf.php');
require_once '../Conexion/Conexion.php';

class PDF extends FPDF
{
    protected $angle = 0;

    function RotatedText($x, $y, $txt, $angle)
    {
        $this->Rotate($angle, $x, $y);
        $this->Text($x, $y, $txt);
        $this->Rotate(0);
    }

    function Rotate($angle, $x=-1, $y=-1)
    {
        if($x==-1)
            $x=$this->x;
        if($y==-1)
            $y=$this->y;
        if($this->angle!=0)
            $this->_out('Q');
        $this->angle=$angle;
        if($angle!=0)
        {
            $angle*=M_PI/180;
            $c=cos($angle);
            $s=sin($angle);
            $cx=$x*$this->k;
            $cy=($this->h-$y)*$this->k;
            $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
        }
    }

    function _endpage()
    {
        if($this->angle!=0)
        {
            $this->angle=0;
            $this->_out('Q');
        }
        parent::_endpage();
    }
}

$conexion = new Conexion();
$conn = $conexion->getConnection();

$idGrupo = $_POST['idGrupo'];
$nombreGrupo = $_POST['NombreGr'];

$pdf = new PDF('L', 'mm', 'letter');
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Asistencias a Actividades y Reuniones Grupo: ' . $nombreGrupo, 0, 1, 'C');
$pdf->Ln(10);

$name_width = 50;
$date_width = 5;

$sql_actividades = "SELECT DISTINCT FechaAsisConf FROM asistenciacel 
                    INNER JOIN inscripcion ON asistenciacel.IdInscripcion = inscripcion.IdInscripcion
                    WHERE inscripcion.IdGrupo = $idGrupo AND asistenciacel.DetalleAsis = 'Actividad'
                    ORDER BY FechaAsisConf";
$result_actividades = $conexion->query($sql_actividades);

$sql_reuniones = "SELECT DISTINCT r.FechaReu 
                  FROM ReunionCel r
                  INNER JOIN asistenciaapoderado aa ON r.IdReunion = aa.IdReunion
                  INNER JOIN inscripcion i ON aa.IdInscripcion = i.IdInscripcion
                  WHERE i.IdGrupo = $idGrupo
                  ORDER BY r.FechaReu";
$result_reuniones = $conexion->query($sql_reuniones);

function dibujarTabla($pdf, $titulo, $result_fechas, $sql_asistencias, $conexion, $name_width, $date_width, $x_start, $y_start, $idGrupo, $es_reunion = false) {
    $meses_es = ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetXY($x_start, $y_start);
    $pdf->Cell($name_width + $date_width * $result_fechas->num_rows, 10, $titulo, 1, 1, 'C');
    
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetXY($x_start, $pdf->GetY());
    $pdf->Cell($name_width, 15, 'Nombre', 1, 0, 'C');
    
    $fechas = array();
    $x = $x_start + $name_width;
    while ($fecha = $result_fechas->fetch_assoc()) {
        $fecha_obj = new DateTime($fecha['FechaAsisConf'] ?? $fecha['FechaReu']);
        $fecha_str = $fecha_obj->format('d') . '/' . $meses_es[$fecha_obj->format('n') - 1];
        $pdf->Cell($date_width, 15, '', 1, 0, 'C');
        $pdf->RotatedText($x + 3, $pdf->GetY() + 13, $fecha_str, 90);
        $fechas[] = $fecha['FechaAsisConf'] ?? $fecha['FechaReu'];
        $x += $date_width;
    }
    $pdf->Ln(15);
    
    $sql_personas = "SELECT DISTINCT p.CiPersona, p.Nombre, p.ApPaterno, p.ApMaterno, i.IdInscripcion 
                     FROM persona p 
                     INNER JOIN celebrante c ON p.CiPersona = c.CiCel
                     INNER JOIN inscripcion i ON c.CiCel = i.CiCel
                     WHERE i.IdGrupo = $idGrupo";
    $result_personas = $conexion->query($sql_personas);
    
    while ($persona = $result_personas->fetch_assoc()) {
        $pdf->SetX($x_start);
        $nombre_completo = $persona['Nombre'] . ' ' . $persona['ApPaterno'] . ' ' . $persona['ApMaterno'];
        $pdf->Cell($name_width, 7, $nombre_completo, 1, 0, 'L');
        
        foreach ($fechas as $fecha) {
            if ($es_reunion) {
                $sql_asistencia = sprintf($sql_asistencias, $persona['IdInscripcion'], $fecha);
            } else {
                $sql_asistencia = sprintf($sql_asistencias, $persona['IdInscripcion'], $fecha);
            }
            $result_asistencia = $conexion->query($sql_asistencia);
            $asistencia = $result_asistencia->fetch_assoc();
            
            if ($asistencia && $asistencia['TipoAsis'] == 'Presente') {
                $pdf->Cell($date_width, 7, 'P', 1, 0, 'C');
            } else {
                $pdf->SetTextColor(255, 0, 0);
                $pdf->Cell($date_width, 7, 'F', 1, 0, 'C');
                $pdf->SetTextColor(0, 0, 0);
            }
        }
        $pdf->Ln();
    }
}

$page_width = $pdf->GetPageWidth();
$table_width = ($page_width - 20) / 2;

$sql_asistencias_actividades = "SELECT TipoAsis FROM asistenciacel 
                                WHERE IdInscripcion = %d AND FechaAsisConf = '%s' AND DetalleAsis = 'Actividad'";
dibujarTabla($pdf, 'Asistencia a Actividades', $result_actividades, $sql_asistencias_actividades, $conexion, $name_width, $date_width, 10, $pdf->GetY(), $idGrupo);

$sql_asistencias_reuniones = "SELECT aa.DetalleAsis as TipoAsis
                              FROM asistenciaapoderado aa
                              INNER JOIN ReunionCel r ON aa.IdReunion = r.IdReunion
                              WHERE aa.IdInscripcion = %d AND r.FechaReu = '%s'";
dibujarTabla($pdf, 'Asistencia a Reuniones', $result_reuniones, $sql_asistencias_reuniones, $conexion, $name_width, $date_width, $table_width + 30, $pdf->GetY()-32, $idGrupo, true);

$pdf->Output('I', 'asistencias_grupo.pdf');

$conn->close();
?>