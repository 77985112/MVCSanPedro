<?php
// Usa procedimientos reales sobre tablas TEMPORARY. No altera reservas ni envía correos reales.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
chdir(__DIR__ . '/../../VistaPersonal');
require_once '../Controlador/controladorReserva.php';

$comprobaciones = 0;
function comprobarCancelacion($condicion, $mensaje)
{
    global $comprobaciones;
    if (!$condicion) throw new RuntimeException($mensaje);
    $comprobaciones++;
}

class CorreoCancelacionSimulado extends ModeloCorreo
{
    public $mensajes = [];
    public $fallar = false;
    public $lanzar = false;
    protected function solicitarEnvio($payload)
    {
        $this->mensajes[] = json_decode($payload, true);
        if ($this->lanzar) throw new RuntimeException('Transporte simulado');
        return ['body' => $this->fallar ? '{}' : '{"messageId":"simulado"}',
            'status' => $this->fallar ? 503 : 201, 'error' => 0];
    }
}

$conexion = (new Conexion())->getConnection();
$lecturaReal = (new Conexion())->getConnection();
$conteoReal = $lecturaReal->query('SELECT COUNT(*) AS n FROM reserva')->fetch_assoc()['n'];
// LIKE no copia las claves foráneas; los datos ficticios quedan en esta conexión.
foreach (['reserva', 'inssacramento', 'solicitante'] as $tabla) {
    $conexion->query('CREATE TEMPORARY TABLE prueba_correo_' . $tabla . ' LIKE ' . $tabla);
    $conexion->query('CREATE TEMPORARY TABLE ' . $tabla . ' LIKE prueba_correo_' . $tabla);
}
$refModelo = new ReflectionClass(Reserva::class);
$modelo = $refModelo->newInstanceWithoutConstructor();
$propiedad = $refModelo->getProperty('conn');
$propiedad->setAccessible(true);
$propiedad->setValue($modelo, $conexion);
$refControlador = new ReflectionClass(ControladorReserva::class);
$controlador = $refControlador->newInstanceWithoutConstructor();
$correo = new CorreoCancelacionSimulado(['api_key' => 'simulada', 'remitente' => 'prueba@example.com', 'nombre' => 'Prueba']);
foreach (['reservaModel' => $modelo, 'correoModel' => $correo] as $nombre => $valor) {
    $propiedad = $refControlador->getProperty($nombre);
    $propiedad->setAccessible(true);
    $propiedad->setValue($controlador, $valor);
}

$datos = [
    'CodPer' => 1, 'FechaReal' => '2099-04-02', 'HoraReal' => '10:30:00',
    'Quien' => 'Prueba aislada', 'Realizacion' => 'Privado', 'CorreoSolicitante' => 'persona@example.com',
    'CiPersonaCelebranteB' => 'prueba-b', 'CiPersonademas' => 'prueba-m',
    'CiPersonaNovio' => 'prueba-novio', 'CiPersonaNovia' => 'prueba-novia',
    'CiPapa' => null, 'CiMama' => null, 'CiPadrino' => null, 'CiMadrina' => null,
    'CiPaNovio' => null, 'CiMaNovio' => null, 'CiPaNovia' => null, 'CiMaNovia' => null,
    'CiTestigoNovio' => 'prueba-test1', 'CiTestigoNovia' => 'prueba-test2',
];
foreach (['Bautizo', 'Matrimonio', 'Misa'] as $tipo) {
    $registro = $datos + ['TipoCel' => $tipo];
    comprobarCancelacion($modelo->{'reservar' . $tipo}($registro), $tipo . ': registrar');
    $fila = $conexion->query('SELECT * FROM reserva ORDER BY CodRes DESC LIMIT 1')->fetch_assoc();
    comprobarCancelacion($fila['CorreoSolicitante'] === 'persona@example.com', $tipo . ': correo asociado a la reserva correcta');
    $cambio = ['CodRes' => $fila['CodRes'], 'EstadoRes' => 'Cancelado',
        'FechaReal' => '2099-05-01', 'HoraReal' => '15:00:00'];
    $antes = count($correo->mensajes);
    comprobarCancelacion($controlador->{'modificarReserva' . $tipo}($cambio), $tipo . ': cancelar');
    $guardada = $conexion->query('SELECT * FROM reserva WHERE CodRes = ' . (int) $fila['CodRes'])->fetch_assoc();
    comprobarCancelacion($guardada['EstadoRes'] === 'Cancelado', $tipo . ': estado persistido');
    comprobarCancelacion($guardada['FechaReal'] === $fila['FechaReal'] && $guardada['HoraReal'] === $fila['HoraReal'], 'Conserva horario cancelado');
    comprobarCancelacion(count($correo->mensajes) === $antes + 1, 'Un solo aviso');
    $mensaje = end($correo->mensajes);
    comprobarCancelacion($mensaje['subject'] === 'Reserva cancelada - ' . $tipo, 'Asunto correcto');
    comprobarCancelacion($mensaje['to'][0]['email'] === 'persona@example.com', 'Destinatario guardado');
    comprobarCancelacion(strpos($mensaje['htmlContent'], '02/04/2099') !== false && strpos($mensaje['htmlContent'], '10:30') !== false, 'Horario original en correo');
    comprobarCancelacion(!$controlador->{'modificarReserva' . $tipo}($cambio), 'Rechaza segunda cancelación');
    comprobarCancelacion(count($correo->mensajes) === $antes + 1, 'No duplica el aviso');
}

// Reservas antiguas, sin correo, error del proveedor y error de transporte.
foreach (['sin_correo', 'correo_agregado', 'error_api', 'error_transporte', 'solo_edicion', 'correo_invalido'] as $caso) {
    $registro = array_replace($datos, ['TipoCel' => 'Misa', 'CorreoSolicitante' => null]);
    $modelo->reservarMisa($registro);
    $id = (int) $conexion->query('SELECT LAST_INSERT_ID() AS id')->fetch_assoc()['id'];
    $cambio = ['CodRes' => $id, 'FechaReal' => '2099-04-03', 'HoraReal' => '11:00:00',
        'EstadoRes' => $caso === 'solo_edicion' ? 'Reservado' : 'Cancelado'];
    if ($caso !== 'sin_correo') $cambio['CorreoSolicitante'] = $caso === 'correo_invalido' ? 'invalido' : '  nuevo@example.com  ';
    $correo->fallar = $caso === 'error_api';
    $correo->lanzar = $caso === 'error_transporte';
    $antes = count($correo->mensajes);
    $resultado = $controlador->modificarReservaMisa($cambio);
    comprobarCancelacion($resultado === ($caso !== 'correo_invalido'), $caso . ': resultado');
    $fila = $conexion->query('SELECT * FROM reserva WHERE CodRes = ' . $id)->fetch_assoc();
    $debeCancelar = !in_array($caso, ['solo_edicion', 'correo_invalido'], true);
    comprobarCancelacion($fila['EstadoRes'] === ($debeCancelar ? 'Cancelado' : 'Reservado'), $caso . ': estado');
    $debeEnviar = !in_array($caso, ['sin_correo', 'solo_edicion', 'correo_invalido'], true);
    comprobarCancelacion(count($correo->mensajes) === $antes + (int) $debeEnviar, $caso . ': avisos');
    if ($debeEnviar || $caso === 'solo_edicion') comprobarCancelacion($fila['CorreoSolicitante'] === 'nuevo@example.com', 'Guarda correo normalizado');
    if (in_array($caso, ['sin_correo', 'error_api', 'error_transporte'], true)) {
        comprobarCancelacion($controlador->obtenerAvisoReserva()['tipo'] === 'warning', 'Advertencia sin revertir cancelación');
    }
}

// Una reserva fallida no deja inscripciones ni correos guardados parcialmente.
$antes = $conexion->query('SELECT COUNT(*) AS n FROM inssacramento')->fetch_assoc()['n'];
$registro = array_replace($datos, ['TipoCel' => 'Misa', 'CiPersonademas' => null, 'FechaReal' => '2099-04-04']);
try { $modelo->reservarMisa($registro); throw new RuntimeException('Debió fallar el registro'); }
catch (mysqli_sql_exception $e) { /* CI obligatorio ausente en el procedimiento. */ }
comprobarCancelacion($conexion->query('SELECT COUNT(*) AS n FROM inssacramento')->fetch_assoc()['n'] === $antes, 'Rollback de inscripción ante error');
comprobarCancelacion($lecturaReal->query('SELECT COUNT(*) AS n FROM reserva')->fetch_assoc()['n'] === $conteoReal, 'No altera reservas reales');
$conexion->close();
$lecturaReal->close();
echo 'OK: ' . $comprobaciones . " comprobaciones; tablas temporales y correo simulado.\n";
