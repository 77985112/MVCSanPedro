<?php
// Usa lecturas de la base local y una tabla TEMPORARY para probar los procedimientos.
// No modifica reservas reales ni envia correos. No cargar tests/bootstrap.php.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
chdir(__DIR__ . '/../../VistaPersonal');
require_once '../Modelo/modeloReserva.php';
require_once '../Modelo/modeloReportesAll.php';
require_once '../fpdf/fpdf.php';

$comprobaciones = 0;
function verificarReserva($condicion, $mensaje)
{
    global $comprobaciones;
    if (!$condicion) throw new RuntimeException($mensaje);
    $comprobaciones++;
}

$c = (new Conexion())->getConnection();
$registro = $c->query('SELECT * FROM reserva LIMIT 1')->fetch_assoc();
if (!$registro) throw new RuntimeException('Se requiere una reserva existente para copiar su estructura de datos.');
unset($registro['HorarioOcupado']); // La columna generada se calcula al insertar.
$hoy = $c->query('SELECT CURDATE() AS hoy')->fetch_assoc()['hoy'];
$ayer = (new DateTimeImmutable($hoy))->modify('-1 day')->format('Y-m-d');
$manana = (new DateTimeImmutable($hoy))->modify('+1 day')->format('Y-m-d');
// La tabla temporal oculta la tabla real solo en esta conexion.
$c->query('CREATE TEMPORARY TABLE prueba_estructura_reserva LIKE reserva');
$c->query('CREATE TEMPORARY TABLE Reserva LIKE prueba_estructura_reserva');
$columnas = '`' . implode('`,`', array_keys($registro)) . '`';
$valores = array_values($registro);
$insertar = $c->prepare('INSERT INTO Reserva (' . $columnas . ') VALUES (' . implode(',', array_fill(0, count($valores), '?')) . ')');
$insertar->bind_param(str_repeat('s', count($valores)), ...$valores);
$insertar->execute();
$id = (int) $registro['CodRes'];
foreach (['ModificarReservaBautizo', 'ModificarReservaMatrimonio', 'ModificarReservaMisa'] as $procedimiento) {
    foreach ([
        [$manana, 'Completado', 'Reservado'],
        [$hoy, 'Completado', 'Reservado'],
        [$ayer, 'Completado', 'Completado'],
        [$manana, 'Cancelado', 'Cancelado'],
        [$ayer, 'Cancelado', 'Cancelado'],
        [$manana, 'Reservado', 'Reservado'],
    ] as [$fecha, $estado, $esperado]) {
        $hora = '10:00:00';
        $stmt = $c->prepare('CALL ' . $procedimiento . '(?, ?, ?, ?)');
        $stmt->bind_param('isss', $id, $fecha, $hora, $estado);
        $stmt->execute();
        $stmt->close();
        while ($c->more_results()) $c->next_result();
        $guardado = $c->query('SELECT EstadoRes FROM Reserva')->fetch_assoc()['EstadoRes'];
        verificarReserva($guardado === $esperado, $procedimiento . ': estado incorrecto');
    }
}
$c->close(); // Elimina automaticamente la tabla temporal.

// Verificar la base real, el calendario y los tres tipos de reporte.
$conexionReal = (new Conexion())->getConnection();
$futuras = $conexionReal->query("SELECT COUNT(*) AS n FROM reserva WHERE EstadoRes = 'Completado' AND FechaReal >= CURDATE()")->fetch_assoc()['n'];
verificarReserva((int) $futuras === 0, 'Quedan reservas futuras completadas');
$reservaModel = new Reserva();
$calendario = $reservaModel->getReservasByFecha('2026-10-27');
verificarReserva(count($calendario) > 0, 'Calendario sin datos de prueba');
foreach ($calendario as $fila) {
    if ($fila['tipocel'] === 'Misa') verificarReserva($fila['DescripSac'] === 'Misa', 'Nombre incorrecto en calendario');
}
$reportes = [
    'mensual' => (new modeloReporteGeneral(new Conexion()))->obtenerReservasMes(2026, 10),
    'diario' => (new modeloReporteGeneral(new Conexion()))->obtenerReservasDia(2026, 10, 27),
    'anual' => (new modeloReporteGeneral(new Conexion()))->obtenerReservas(),
];
foreach ($reportes as $tipo => $filas) {
    verificarReserva(count($filas) > 0, 'Reporte vacio: ' . $tipo);
    foreach ($filas as $fila) {
        if ($fila['TipoCel'] === 'Misa') verificarReserva($fila['DescripSac'] === 'Misa', 'Nombre incorrecto en reporte');
        if ($fila['FechaReal'] >= $hoy) verificarReserva($fila['EstadoRes'] !== 'Completado', 'Estado futuro incorrecto en reporte');
    }
}

// Generar PDF en memoria con las clases reales, sin iniciar sesiones web.
foreach (['VistaReporteEsp.php' => 'PDFMensualPrueba', 'VistaReporteReservas.php' => 'PDFAnualPrueba'] as $archivo => $clase) {
    $fuente = file_get_contents($archivo);
    $inicio = strpos($fuente, 'class CertificadoPDF extends FPDF');
    $fin = strpos($fuente, '$pdf = new');
    $codigoClase = substr($fuente, $inicio, $fin - $inicio);
    eval(str_replace('class CertificadoPDF ', 'class ' . $clase . ' ', $codigoClase));
    $pdf = new $clase();
    $pdf->SetCompression(false);
    ob_start();
    if ($archivo === 'VistaReporteEsp.php') $pdf->generarPDF($reportes['mensual'], 'mensual');
    else $pdf->generarPDF($reportes['anual']);
    $contenido = ob_get_clean();
    verificarReserva(strpos($contenido, '%PDF-') === 0, 'No se genero PDF');
    verificarReserva(strpos($contenido, '(Sacramento/Misa)') !== false, 'Falta encabezado');
    verificarReserva(strpos($contenido, '(Misa)') !== false, 'Falta nombre Misa');
    verificarReserva(strpos($contenido, '(Reservado)') !== false, 'Falta estado Reservado');
    $medida = new FPDF();
    $medida->SetFont('Arial', 'B', 10);
    verificarReserva($medida->GetStringWidth('Sacramento/Misa') < 32, 'Encabezado no cabe en columna');
}
echo 'OK: ' . $comprobaciones . " comprobaciones, incluidos procedimientos en tabla temporal y PDF en memoria.\n";
