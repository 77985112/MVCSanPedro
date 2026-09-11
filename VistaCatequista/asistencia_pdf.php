<?php
require('../fpdf/fpdf.php');
require_once '../Conexion/Conexion.php';


$idGrupo = $_POST['idGrupo'];
$NombreGr = $_POST['NombreGr'];

$conexion = new Conexion();
$db = $conexion->getConnection();

$sql_celebrantes = "
        SELECT p.CiPersona, CONCAT(p.Nombre, ' ', p.ApPaterno, ' ', p.ApMaterno) AS NombreCompleto
        FROM persona p
        INNER JOIN celebrante c ON p.CiPersona = c.CiCel
        INNER JOIN inscripcion i ON c.CiCel = i.CiCel
        WHERE i.IdGrupo = $idGrupo
        ORDER BY p.ApPaterno, p.ApMaterno, p.Nombre";
$result_celebrantes = $db->query($sql_celebrantes);

$celebrantesAll = [];
while ($row = $result_celebrantes->fetch_assoc()) {
    $celebrantesAll[$row['CiPersona']] = $row['NombreCompleto'];
}

$sql_fechas = "
        SELECT DISTINCT a.FechaAsisConf
        FROM asistenciacel a
        INNER JOIN inscripcion i ON a.IdInscripcion = i.IdInscripcion
        WHERE i.IdGrupo = $idGrupo AND a.DetalleAsis = 'Catequesis'
        ORDER BY a.FechaAsisConf";
$result_fechas = $db->query($sql_fechas);

$fechas = [];
while ($row = $result_fechas->fetch_assoc()) {
    $fechas[] = $row['FechaAsisConf'];
}

$asistenciasAll = [];
foreach ($celebrantesAll as $ci => $nombre) {
    $asistenciasAll[$ci] = [];
    foreach ($fechas as $fecha) {
        $sql_asistencia = "
                SELECT a.TipoAsis
                FROM asistenciacel a
                INNER JOIN inscripcion i ON a.IdInscripcion = i.IdInscripcion
                WHERE i.CiCel = '$ci' AND a.FechaAsisConf = '$fecha' AND DetalleAsis = 'Catequesis'";
        $result_asistencia = $db->query($sql_asistencia);

        if ($result_asistencia->num_rows > 0) {
            $tipoAsis = $result_asistencia->fetch_assoc()['TipoAsis'];
            $asistenciasAll[$ci][$fecha] = ($tipoAsis === 'Presente') ? 'P' : 'F';
        } else {
            $asistenciasAll[$ci][$fecha] = 'F';
        }
    }
}

$dias_es = ["Dom", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb"];
$meses_es = ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];
    class PDF extends FPDF
    {
        protected $angle = 0;
        function RotatedText($x, $y, $txt, $angle)
        {
            $this->SetXY($x, $y);
            $this->Rotate($angle, $x, $y);
            $this->Cell(40, 10, utf8_decode($txt), 0, 0, 'C');
            $this->Rotate(0);
        }

        function Rotate($angle, $x = -1, $y = -1)
        {
            if ($x == -1) {
                $x = $this->x;
            }
            if ($y == -1) {
                $y = $this->y;
            }
            if ($this->angle != 0) {
                $this->_out('Q');
            }
            $this->angle = $angle;
            if ($angle != 0) {
                $angle *= M_PI / 180;
                $c = cos($angle);
                $s = sin($angle);
                $cx = $x * $this->k;
                $cy = ($this->h - $y) * $this->k;
                $this->_out(sprintf('q %.3F %.3F %.3F %.3F %.3F %.3F cm 1 0 0 1 %.3F %.3F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
            }
        }
    }

    $pdf = new PDF('L', 'mm', 'Letter');
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 17);
    $pdf->Cell(0, 10, utf8_decode('Asistencia del Grupo ' . $NombreGr . ' en Catequisis'), 0, 1, 'C');

    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 10);

    $pdf->Cell(70, 35, utf8_decode('Nº / Nombres'), 1, 0, 'C');

    $x = $pdf->GetX();
    $y = $pdf->GetY();
    foreach ($fechas as $fecha) {
        $fecha_obj = new DateTime($fecha);
        $dia = $dias_es[$fecha_obj->format('w')];
        $mes = $meses_es[$fecha_obj->format('n') - 1];
        $dia_numero = $fecha_obj->format('d');
        $pdf->SetXY($x, $y);
        $pdf->Cell(5, 35, '', 1, 0);
        $pdf->RotatedText($x - 2, $y + 35, "$dia $dia_numero $mes", 90);
        $x += 5;
    }
    $pdf->Ln(0);

    $pdf->SetFont('Arial', '', 10);
    $counter = 1;

    foreach ($celebrantesAll as $ci => $nombre) {
        $pdf->Cell(70, 5, utf8_decode("$counter. $nombre"), 1, 0, 'L');
        foreach ($fechas as $fecha) {
            $tipoAsis = $asistenciasAll[$ci][$fecha];
            $pdf->Cell(5, 5, $tipoAsis, 1, 0, 'C');
        }
        $pdf->Ln();
        $counter++;
    }

$pdf->Output('I', 'asistencia.pdf');
