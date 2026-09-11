<?php
// Ejecutar con PHP CLI: consulta fechas en la base local; guardado y correo simulados.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
$directorio = getcwd();
chdir(__DIR__ . '/../../VistaPersonal');
require_once '../Controlador/controladorReserva.php';
chdir($directorio);

$comprobaciones = 0;
function comprobar($condicion, $descripcion)
{
    global $comprobaciones;
    if (!$condicion) {
        throw new RuntimeException('FALLO: ' . $descripcion);
    }
    $comprobaciones++;
}

class ReservasSimuladas
{
    public $llamadas = [];
    public $resultado = true;
    public function existeReservaEnFechaHora($fecha, $hora, $realizacion = '', $codRes = 0)
    {
        return false;
    }
    public function __call($metodo, $argumentos)
    {
        $this->llamadas[] = [$metodo, $argumentos[0]];
        return $this->resultado;
    }
}

class CorreoSimulado extends ModeloCorreo
{
    public $solicitudes = [];
    public $respuesta = ['body' => '{"messageId":"simulado"}', 'status' => 201, 'error' => 0];
    public $fallar = false;
    protected function solicitarEnvio($payload)
    {
        if ($this->fallar) {
            throw new RuntimeException('Fallo simulado de transporte');
        }
        $this->solicitudes[] = json_decode($payload, true);
        return $this->respuesta;
    }
}

function prepararControlador($reserva, $correo)
{
    $reflexion = new ReflectionClass(ControladorReserva::class);
    $controlador = $reflexion->newInstanceWithoutConstructor();
    foreach (['reservaModel' => $reserva, 'correoModel' => $correo] as $nombre => $valor) {
        $propiedad = $reflexion->getProperty($nombre);
        $propiedad->setAccessible(true);
        $propiedad->setValue($controlador, $valor);
    }
    return $controlador;
}

$config = ['api_key' => 'clave-ficticia-sin-acceso', 'remitente' => 'parroquia@example.com', 'nombre' => 'San Pedro'];
$datos = [
    'EnviarConfirmacion' => '1', 'CorreoSolicitante' => '  solicitante@example.com  ',
    'FechaReal' => '2026-09-20', 'HoraReal' => '10:30:00', 'Quien' => 'Dato privado',
    'CiPapa' => 'dato-no-publicable', 'TipoCel' => 'tipo-manipulado', 'Realizacion' => 'Privado',
];

// Validar el tipo de matrimonio antes de guardar o enviar correos.
foreach (['', '   ', str_repeat('a', 31), ['texto']] as $detalle) {
    $reserva = new ReservasSimuladas();
    $correo = new CorreoSimulado($config);
    $controlador = prepararControlador($reserva, $correo);
    comprobar($controlador->reservarMatrimonio(array_replace($datos, [
        'Realizacion' => 'Otro', 'RealizacionOtro' => $detalle
    ])) === false, 'Rechaza descripcion de Otro invalida');
    comprobar(!$reserva->llamadas && !$correo->solicitudes, 'Otro invalido no guarda ni envia');
}
foreach (['En una capilla familiar', str_repeat('a', 30), str_repeat("\u{00e1}", 30)] as $detalle) {
    $reserva = new ReservasSimuladas();
    $correo = new CorreoSimulado($config);
    $controlador = prepararControlador($reserva, $correo);
    comprobar($controlador->reservarMatrimonio(array_replace($datos, [
        'Realizacion' => 'Otro', 'RealizacionOtro' => '  ' . $detalle . '  '
    ])) === true, 'Acepta descripcion de Otro');
    comprobar($reserva->llamadas[0][1]['Realizacion'] === $detalle, 'Envia descripcion completa al modelo');
}
$reserva = new ReservasSimuladas();
$correo = new CorreoSimulado($config);
$controlador = prepararControlador($reserva, $correo);
comprobar($controlador->reservarMatrimonio(array_replace($datos, ['Realizacion' => 'Particular'])) === false, 'Particular eliminado');
comprobar(!$reserva->llamadas && !$correo->solicitudes, 'Particular no guarda ni envia');

// Validar el tipo de bautizo antes de guardar o enviar correos.
foreach (['', '   ', str_repeat('a', 31), ['texto']] as $detalle) {
    $reserva = new ReservasSimuladas();
    $correo = new CorreoSimulado($config);
    $controlador = prepararControlador($reserva, $correo);
    comprobar($controlador->reservarBautizo(array_replace($datos, [
        'Realizacion' => 'Otro', 'RealizacionOtro' => $detalle
    ])) === false, 'Rechaza descripcion de Otro invalida');
    comprobar(!$reserva->llamadas && !$correo->solicitudes, 'Otro invalido no guarda ni envia');
}
foreach (['En una capilla familiar', str_repeat('a', 30), str_repeat("\u{00e1}", 30)] as $detalle) {
    $reserva = new ReservasSimuladas();
    $correo = new CorreoSimulado($config);
    $controlador = prepararControlador($reserva, $correo);
    comprobar($controlador->reservarBautizo(array_replace($datos, [
        'Realizacion' => 'Otro', 'RealizacionOtro' => '  ' . $detalle . '  '
    ])) === true, 'Acepta descripcion de Otro');
    comprobar($reserva->llamadas[0][1]['Realizacion'] === $detalle, 'Envia descripcion completa al modelo');
}
$reserva = new ReservasSimuladas();
$correo = new CorreoSimulado($config);
$controlador = prepararControlador($reserva, $correo);
comprobar($controlador->reservarBautizo(array_replace($datos, ['Realizacion' => 'Particular'])) === false, 'Particular eliminado');
comprobar(!$reserva->llamadas && !$correo->solicitudes, 'Particular no guarda ni envia');

// Validar el tipo de misa antes de guardar o enviar correos.
foreach (['', '   ', str_repeat('a', 31), ['texto']] as $detalle) {
    $reserva = new ReservasSimuladas();
    $correo = new CorreoSimulado($config);
    $controlador = prepararControlador($reserva, $correo);
    comprobar($controlador->reservarMisa(array_replace($datos, [
        'Realizacion' => 'Otro', 'RealizacionOtro' => $detalle
    ])) === false, 'Rechaza descripcion de Otro invalida');
    comprobar(!$reserva->llamadas && !$correo->solicitudes, 'Otro invalido no guarda ni envia');
}
foreach (['En una capilla familiar', str_repeat('a', 30), str_repeat("\u{00e1}", 30)] as $detalle) {
    $reserva = new ReservasSimuladas();
    $correo = new CorreoSimulado($config);
    $controlador = prepararControlador($reserva, $correo);
    comprobar($controlador->reservarMisa(array_replace($datos, [
        'Realizacion' => 'Otro', 'RealizacionOtro' => '  ' . $detalle . '  '
    ])) === true, 'Acepta descripcion de Otro');
    comprobar($reserva->llamadas[0][1]['Realizacion'] === $detalle, 'Envia descripcion completa al modelo');
}
$reserva = new ReservasSimuladas();
$correo = new CorreoSimulado($config);
$controlador = prepararControlador($reserva, $correo);
comprobar($controlador->reservarMisa(array_replace($datos, ['Realizacion' => 'Particular'])) === false, 'Particular eliminado');
comprobar(!$reserva->llamadas && !$correo->solicitudes, 'Particular no guarda ni envia');

foreach (['reservarBautizo' => 'Bautizo', 'reservarMatrimonio' => 'Matrimonio', 'reservarMisa' => 'Misa'] as $metodo => $tipo) {
    $reserva = new ReservasSimuladas();
    $correo = new CorreoSimulado($config);
    $controlador = prepararControlador($reserva, $correo);
    comprobar($controlador->$metodo($datos) === true, $tipo . ': devuelve éxito');
    comprobar(count($reserva->llamadas) === 1 && $reserva->llamadas[0][0] === $metodo, $tipo . ': guarda una vez');
    comprobar(count($correo->solicitudes) === 1, $tipo . ': solicita un correo');
    $payload = $correo->solicitudes[0];
    comprobar($payload['to'][0]['email'] === 'solicitante@example.com', 'Normaliza destinatario');
    comprobar($payload['subject'] === 'Registro de reserva - ' . $tipo, 'Tipo decidido por el controlador');
    comprobar(strpos($payload['htmlContent'], '20/09/2026') !== false && strpos($payload['htmlContent'], '10:30') !== false, 'Datos de fecha y hora');
    comprobar(strpos(json_encode($payload), 'dato-no-publicable') === false, 'No expone cédulas');
    comprobar($controlador->obtenerAvisoReserva()['tipo'] === 'success', 'Aviso de aceptación');
}

foreach (['incorrecto', '', ['correo@example.com']] as $invalido) {
    $reserva = new ReservasSimuladas();
    $correo = new CorreoSimulado($config);
    $controlador = prepararControlador($reserva, $correo);
    comprobar($controlador->reservarBautizo(array_replace($datos, ['CorreoSolicitante' => $invalido])) === false, 'Rechaza correo inválido');
    comprobar(!$reserva->llamadas && !$correo->solicitudes, 'Correo inválido: no guarda ni envía');
}

$reserva = new ReservasSimuladas();
$correo = new CorreoSimulado($config);
$controlador = prepararControlador($reserva, $correo);
$sinCorreo = $datos;
unset($sinCorreo['EnviarConfirmacion'], $sinCorreo['CorreoSolicitante']);
comprobar($controlador->reservarMisa($sinCorreo) === true, 'Compatible con reserva sin campos nuevos');
comprobar(count($reserva->llamadas) === 1 && !$correo->solicitudes, 'Sin selección: no envía');

$reserva = new ReservasSimuladas();
$reserva->resultado = false;
$correo = new CorreoSimulado($config);
$controlador = prepararControlador($reserva, $correo);
comprobar($controlador->reservarMatrimonio($datos) === false, 'Guardado fallido');
comprobar(!$correo->solicitudes, 'Nunca envía si falla el guardado');

foreach ([
    ['body' => '{"message":"rechazo"}', 'status' => 401, 'error' => 0],
    ['body' => '{}', 'status' => 429, 'error' => 0],
    ['body' => '{}', 'status' => 500, 'error' => 0],
    ['body' => '', 'status' => 0, 'error' => 28],
    ['body' => 'no-json', 'status' => 201, 'error' => 0],
    ['body' => '{}', 'status' => 201, 'error' => 0],
] as $respuesta) {
    $reserva = new ReservasSimuladas();
    $correo = new CorreoSimulado($config);
    $correo->respuesta = $respuesta;
    $controlador = prepararControlador($reserva, $correo);
    comprobar($controlador->reservarBautizo($datos) === true, 'Fallo de correo no revierte guardado');
    comprobar(count($reserva->llamadas) === 1 && count($correo->solicitudes) === 1, 'No hay reintentos ni registros duplicados');
    comprobar($controlador->obtenerAvisoReserva()['tipo'] === 'warning', 'No muestra éxito de correo ante fallo');
}

$reserva = new ReservasSimuladas();
$correo = new CorreoSimulado($config);
$correo->fallar = true;
$controlador = prepararControlador($reserva, $correo);
comprobar($controlador->reservarMisa($datos) === true, 'Excepción de correo no afecta guardado');
comprobar($controlador->obtenerAvisoReserva()['tipo'] === 'warning', 'Excepción genera advertencia');

$reserva = new ReservasSimuladas();
$correo = new CorreoSimulado(['api_key' => '', 'remitente' => '', 'nombre' => 'San Pedro']);
$controlador = prepararControlador($reserva, $correo);
comprobar($controlador->reservarMisa($datos) === true, 'Sin configuración conserva reserva');
comprobar(!$correo->solicitudes && $controlador->obtenerAvisoReserva()['tipo'] === 'warning', 'Sin configuración no llama a la API');

$correo = new CorreoSimulado($config);
$correo->enviarConfirmacionReserva('persona@example.com', '<script>alert(1)</script>', '2026-09-20', '10:00');
comprobar(strpos($correo->solicitudes[0]['htmlContent'], '<script>') === false, 'Escapa contenido HTML');

echo 'OK: ' . $comprobaciones . " comprobaciones; sin modificar reservas ni realizar envíos reales.\n";
