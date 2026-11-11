<?php
require('../fpdf/fpdf.php');
require_once '../Conexion/Conexion.php';

class PDF extends FPDF
{
}

$conexion = new Conexion();
$conn = $conexion->getConnection();

$idGrupo = isset($_POST['idGrupo']) ? (int) $_POST['idGrupo'] : 0;
$nombreGrupo = isset($_POST['NombreGr']) ? (string) $_POST['NombreGr'] : '';

if ($idGrupo <= 0) {
    exit('No se indicó el grupo.');
}

$texto = function ($valor) {
    return iconv('UTF-8', 'Windows-1252//IGNORE', (string) $valor);
};

function fechaCorta($fecha)
{
    $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    $dt = new DateTime($fecha);
    return $dt->format('d') . '/' . $meses[(int) $dt->format('n') - 1];
}

// Fechas de reuniones con asistencia registrada para el grupo.
$fechasReuniones = [];
$consulta = $conn->query("SELECT DISTINCT r.FechaReu
                          FROM `reunioncel` r
                          INNER JOIN asistenciaapoderado aa ON r.IdReunion = aa.IdReunion
                          INNER JOIN inscripcion i ON aa.IdInscripcion = i.IdInscripcion
                          WHERE i.IdGrupo = $idGrupo
                          ORDER BY r.FechaReu");
if ($consulta) {
    while ($fila = $consulta->fetch_assoc()) {
        $fechasReuniones[] = $fila['FechaReu'];
    }
}

// Fechas de actividades con asistencia registrada para el grupo.
$fechasActividades = [];
$consulta = $conn->query("SELECT DISTINCT a.FechaAsisConf
                          FROM asistenciacel a
                          INNER JOIN inscripcion i ON a.IdInscripcion = i.IdInscripcion
                          WHERE i.IdGrupo = $idGrupo AND a.DetalleAsis = 'Actividad'
                          ORDER BY a.FechaAsisConf");
if ($consulta) {
    while ($fila = $consulta->fetch_assoc()) {
        $fechasActividades[] = $fila['FechaAsisConf'];
    }
}

// Personas del grupo.
$personas = [];
$consulta = $conn->query("SELECT DISTINCT p.CiPersona, p.Nombre, p.ApPaterno, p.ApMaterno, i.IdInscripcion
                          FROM `persona` p
                          INNER JOIN `celebrante` c ON p.CiPersona = c.CiCel
                          INNER JOIN inscripcion i ON c.CiCel = i.CiCel
                          WHERE i.IdGrupo = $idGrupo
                          ORDER BY p.ApPaterno, p.ApMaterno, p.Nombre");
if ($consulta) {
    while ($fila = $consulta->fetch_assoc()) {
        $personas[] = $fila;
    }
}

$columnas = [];
foreach ($fechasReuniones as $fecha) {
    $columnas[] = ['tipo' => 'reunion', 'fecha' => $fecha];
}
foreach ($fechasActividades as $fecha) {
    $columnas[] = ['tipo' => 'actividad', 'fecha' => $fecha];
}

$pdf = new PDF('L', 'mm', 'letter');
$pdf->SetAutoPageBreak(false);
$pdf->SetMargins(10, 10, 10);

$margen = 10;
$nombreWidth = 60;
$totalWidth = 26;
$cantidadFechas = count($columnas);
$anchoDisponible = $pdf->GetPageWidth() - (2 * $margen) - $nombreWidth - $totalWidth;
$fechaWidth = $cantidadFechas > 0 ? $anchoDisponible / $cantidadFechas : 0;
$fechaWidth = min(30, max(9, $fechaWidth));

function imprimirCabecera($pdf, $texto, $nombreGrupo, $columnas, $nombreWidth, $fechaWidth, $totalWidth, $margen)
{
    $pdf->SetFont('Arial', 'B', 15);
    $pdf->SetXY($margen, 10);
    $pdf->Cell(0, 8, $texto('Control de Pagos y Asistencias - Grupo: ' . $nombreGrupo), 0, 1, 'C');

    $pdf->SetFont('Arial', '', 8);
    $pdf->SetX($margen);
    $pdf->Cell(0, 5, $texto('P = presente · F = falta · Monto pagado por reunión'), 0, 1, 'C');
    $pdf->Ln(3);

    $pdf->SetFont('Arial', 'B', 7);
    $pdf->SetX($margen);
    $pdf->Cell($nombreWidth, 12, 'Nombre', 1, 0, 'C');
    foreach ($columnas as $columna) {
        $prefijo = $columna['tipo'] === 'reunion' ? 'Reu ' : 'Act ';
        $pdf->Cell($fechaWidth, 12, $texto($prefijo . fechaCorta($columna['fecha'])), 1, 0, 'C');
    }
    $pdf->Cell($totalWidth, 12, 'Total pagado', 1, 1, 'C');
}

$pdf->AddPage();
imprimirCabecera($pdf, $texto, $nombreGrupo, $columnas, $nombreWidth, $fechaWidth, $totalWidth, $margen);

$pdf->SetFont('Arial', '', 7);
$yLimite = $pdf->GetPageHeight() - 15;

foreach ($personas as $persona) {
    if ($pdf->GetY() + 6 > $yLimite) {
        $pdf->AddPage();
        imprimirCabecera($pdf, $texto, $nombreGrupo, $columnas, $nombreWidth, $fechaWidth, $totalWidth, $margen);
        $pdf->SetFont('Arial', '', 7);
    }

    $pdf->SetX($margen);
    $nombreCompleto = $persona['Nombre'] . ' ' . $persona['ApPaterno'] . ' ' . $persona['ApMaterno'];
    $pdf->Cell($nombreWidth, 6, $texto($nombreCompleto), 1, 0, 'L');

    $idInscripcion = (int) $persona['IdInscripcion'];
    $totalPagado = 0;

    foreach ($columnas as $columna) {
        $fecha = $columna['fecha'];

        if ($columna['tipo'] === 'reunion') {
            $consulta = $conn->query("SELECT aa.MontoAsis, aa.DetalleAsis
                                      FROM asistenciaapoderado aa
                                      INNER JOIN `reunioncel` r ON aa.IdReunion = r.IdReunion
                                      WHERE aa.IdInscripcion = $idInscripcion AND r.FechaReu = '$fecha'
                                      LIMIT 1");
            $fila = $consulta ? $consulta->fetch_assoc() : null;
            $monto = $fila ? (int) $fila['MontoAsis'] : 0;
            $totalPagado += $monto;

            if (!$fila) {
                $pdf->Cell($fechaWidth, 6, '-', 1, 0, 'C');
            } else {
                $estado = stripos($fila['DetalleAsis'], 'Presente') !== false ? 'P' : 'F';
                if ($estado === 'F') {
                    $pdf->SetTextColor(200, 0, 0);
                }
                $pdf->Cell($fechaWidth, 6, $estado . ' / ' . number_format($monto, 0), 1, 0, 'C');
                $pdf->SetTextColor(0, 0, 0);
            }
        } else {
            $consulta = $conn->query("SELECT TipoAsis
                                      FROM asistenciacel
                                      WHERE IdInscripcion = $idInscripcion AND FechaAsisConf = '$fecha' AND DetalleAsis = 'Actividad'
                                      LIMIT 1");
            $fila = $consulta ? $consulta->fetch_assoc() : null;

            if ($fila && stripos($fila['TipoAsis'], 'Presente') !== false) {
                $pdf->Cell($fechaWidth, 6, 'P', 1, 0, 'C');
            } elseif ($fila) {
                $pdf->SetTextColor(200, 0, 0);
                $pdf->Cell($fechaWidth, 6, 'F', 1, 0, 'C');
                $pdf->SetTextColor(0, 0, 0);
            } else {
                $pdf->Cell($fechaWidth, 6, '-', 1, 0, 'C');
            }
        }
    }

    $pdf->SetFont('Arial', 'B', 7);
    $pdf->Cell($totalWidth, 6, number_format($totalPagado, 0), 1, 1, 'C');
    $pdf->SetFont('Arial', '', 7);
}

$pdf->Output('I', 'asistencias_grupo.pdf');

$conn->close();
