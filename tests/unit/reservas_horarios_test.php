<?php
// Procedimientos reales y tablas TEMPORARY; sin reservas ni correos reales.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
chdir(__DIR__ . '/../../VistaPersonal');
require_once '../Controlador/controladorReserva.php';
$comprobaciones = 0;
function comprobarHorario($condicion, $mensaje)
{
    global $comprobaciones;
    if (!$condicion) throw new RuntimeException($mensaje);
    $comprobaciones++;
}
class CorreoHorarioSimulado extends ModeloCorreo
{
    public $envios = 0;
    protected function solicitarEnvio($payload)
    {
        $this->envios++;
        return ['body' => '{"messageId":"simulado"}', 'status' => 201, 'error' => 0];
    }
}
$c = (new Conexion())->getConnection();
$real = (new Conexion())->getConnection();
$conteoReal = $real->query('SELECT COUNT(*) AS n FROM reserva')->fetch_assoc()['n'];
foreach (['reserva', 'inssacramento', 'solicitante'] as $tabla) {
    $c->query('CREATE TEMPORARY TABLE prueba_horario_' . $tabla . ' LIKE ' . $tabla);
    $c->query('CREATE TEMPORARY TABLE ' . $tabla . ' LIKE prueba_horario_' . $tabla);
}
$ref = new ReflectionClass(Reserva::class);
$modelo = $ref->newInstanceWithoutConstructor();
$propiedad = $ref->getProperty('conn');
$propiedad->setAccessible(true);
$propiedad->setValue($modelo, $c);
$ref = new ReflectionClass(ControladorReserva::class);
$controlador = $ref->newInstanceWithoutConstructor();
$correo = new CorreoHorarioSimulado(['api_key' => 'simulada', 'remitente' => 'prueba@example.com', 'nombre' => 'Prueba']);
foreach (['reservaModel' => $modelo, 'correoModel' => $correo] as $nombre => $valor) {
    $propiedad = $ref->getProperty($nombre);
    $propiedad->setAccessible(true);
    $propiedad->setValue($controlador, $valor);
}
function datosHorario($tipo, $fecha, $hora)
{
    static $numero = 0;
    $ci = 'horario' . ++$numero;
    return ['TipoCel' => $tipo, 'FechaReal' => $fecha, 'HoraReal' => $hora,
        'CodPer' => 1, 'Quien' => 'Prueba aislada', 'Realizacion' => 'Privado',
        'CorreoSolicitante' => 'prueba@example.com', 'EnviarConfirmacion' => '1',
        'CiPersonaCelebranteB' => $ci, 'CiPersonademas' => $ci,
        'CiPersonaNovio' => $ci . 'n', 'CiPersonaNovia' => $ci . 'a',
        'CiPapa' => null, 'CiMama' => null, 'CiPadrino' => null, 'CiMadrina' => null,
        'CiPaNovio' => null, 'CiMaNovio' => null, 'CiPaNovia' => null, 'CiMaNovia' => null,
        'CiTestigoNovio' => $ci . 't1', 'CiTestigoNovia' => $ci . 't2'];
}
function contarHorario($c)
{
    return $c->query('SELECT (SELECT COUNT(*) FROM reserva) AS reservas,
        (SELECT COUNT(*) FROM inssacramento) AS inscripciones,
        (SELECT COUNT(*) FROM solicitante) AS solicitantes')->fetch_assoc();
}
$dia = 0;
foreach (['Bautizo', 'Matrimonio', 'Misa'] as $ocupante) {
    foreach (['Bautizo', 'Matrimonio', 'Misa'] as $nuevo) {
        $fecha = '2099-05-' . sprintf('%02d', ++$dia);
        $primera = datosHorario($ocupante, $fecha, '10:00');
        comprobarHorario($controlador->{'reservar' . $ocupante}($primera), 'Crea primer horario');
        $idPrimera = (int) $c->query('SELECT LAST_INSERT_ID() AS id')->fetch_assoc()['id'];
        $antes = contarHorario($c);
        $envios = $correo->envios;
        $duplicada = datosHorario($nuevo, $fecha, '10:00:00');
        comprobarHorario(!$controlador->{'reservar' . $nuevo}($duplicada), "$ocupante impide $nuevo en el mismo horario");
        comprobarHorario(contarHorario($c) === $antes, 'Conflicto no deja inscripciones ni solicitantes');
        comprobarHorario($correo->envios === $envios, 'Conflicto no envía confirmación');
        comprobarHorario(preg_match('/ocupadas|Ya existe una reserva/', $controlador->obtenerAvisoReserva()['mensaje']) === 1, 'Aviso claro de horario ocupado');
        $comunitaria = array_replace($duplicada, ['Realizacion' => 'Comunitario']);
        comprobarHorario(!$controlador->{'reservar' . $nuevo}($comunitaria), 'Comunitario también respeta el horario ocupado');
        try {
            $modelo->{'reservar' . $nuevo}($duplicada);
            throw new RuntimeException('El modelo debe rechazar el horario duplicado');
        } catch (DomainException $e) {
            comprobarHorario(strpos($e->getMessage(), 'ocupadas') !== false, 'El modelo también impide coincidencias');
        }
        comprobarHorario(contarHorario($c) === $antes, 'Rollback de todos los registros ante conflicto de la base');

        $otraHora = datosHorario($nuevo, $fecha, '11:00');
        comprobarHorario($controlador->{'reservar' . $nuevo}($otraHora), 'Permite otra hora del mismo día');
        $idSegunda = (int) $c->query('SELECT LAST_INSERT_ID() AS id')->fetch_assoc()['id'];
        $cambio = ['CodRes' => $idSegunda, 'EstadoRes' => 'Reservado', 'FechaReal' => $fecha, 'HoraReal' => '10:00'];
        $envios = $correo->envios;
        comprobarHorario(!$controlador->{'modificarReserva' . $nuevo}($cambio), 'Impide reprogramar al horario ocupado');
        comprobarHorario($c->query('SELECT HoraReal FROM reserva WHERE CodRes = ' . $idSegunda)->fetch_assoc()['HoraReal'] === '11:00:00', 'Conserva horario anterior tras rechazo');
        comprobarHorario($correo->envios === $envios, 'Reprogramación rechazada no envía correo');
        $cambio['HoraReal'] = '11:00';
        comprobarHorario($controlador->{'modificarReserva' . $nuevo}($cambio), 'Permite editar sin cambiar el horario propio');

        $cancelar = ['CodRes' => $idPrimera, 'EstadoRes' => 'Cancelado', 'FechaReal' => $fecha, 'HoraReal' => '10:00'];
        comprobarHorario($controlador->{'modificarReserva' . $ocupante}($cancelar), 'Permite cancelar el horario ocupado');
        // Una reserva cancelada no se usa para probar nuevamente el mismo matrimonio.
        $reemplazo = datosHorario('Misa', $fecha, '10:00');
        comprobarHorario($controlador->reservarMisa($reemplazo), 'Cancelación libera horario para otra reserva');
        $idReemplazo = (int) $c->query('SELECT LAST_INSERT_ID() AS id')->fetch_assoc()['id'];
        $cancelar['CodRes'] = $idReemplazo;
        comprobarHorario($controlador->modificarReservaMisa($cancelar), 'Admite varias canceladas en el mismo horario');
        comprobarHorario($controlador->reservarMisa(datosHorario('Misa', $fecha, '10:00')), 'Permite reutilizar horario tras varias cancelaciones');
    }
}
comprobarHorario($controlador->reservarMisa(datosHorario('Misa', '2099-06-01', '10:00')), 'Permite misma hora en otra fecha');
// Los nueve cruces comunitarios pueden compartir horario, incluso al reprogramar.
$dia = 1;
foreach (['Bautizo', 'Matrimonio', 'Misa'] as $ocupante) {
    foreach (['Bautizo', 'Matrimonio', 'Misa'] as $nuevo) {
        $fecha = '2099-06-' . sprintf('%02d', ++$dia);
        $primera = array_replace(datosHorario($ocupante, $fecha, '10:00'), ['Realizacion' => 'Comunitario']);
        comprobarHorario($controlador->{'reservar' . $ocupante}($primera), 'Registra primera comunitaria');
        $idPrimera = (int) $c->query('SELECT LAST_INSERT_ID() AS id')->fetch_assoc()['id'];
        $segunda = array_replace(datosHorario($nuevo, $fecha, '10:00:00'), ['Realizacion' => 'Comunitario']);
        $envios = $correo->envios;
        comprobarHorario($controlador->{'reservar' . $nuevo}($segunda), "$ocupante y $nuevo comunitarios comparten horario");
        $idSegunda = (int) $c->query('SELECT LAST_INSERT_ID() AS id')->fetch_assoc()['id'];
        comprobarHorario($correo->envios === $envios + 1, 'Confirma por correo la comunitaria aceptada');
        comprobarHorario($c->query('SELECT HorarioOcupado FROM reserva WHERE CodRes = ' . $idSegunda)->fetch_assoc()['HorarioOcupado'] === null, 'Índice permite compartir comunitarias');
        $antes = contarHorario($c);
        $envios = $correo->envios;
        foreach (['Privado', 'Otro'] as $realizacion) {
            $solicitud = array_replace(datosHorario($nuevo, $fecha, '10:00'), ['Realizacion' => $realizacion, 'RealizacionOtro' => 'En una capilla']);
            comprobarHorario(!$controlador->{'reservar' . $nuevo}($solicitud), 'Comunitarias impiden una reserva exclusiva');
        }
        comprobarHorario(contarHorario($c) === $antes && $correo->envios === $envios, 'Rechazo no guarda datos ni envía correo');

        $cambio = ['CodRes' => $idSegunda, 'EstadoRes' => 'Reservado', 'FechaReal' => $fecha, 'HoraReal' => '11:00'];
        comprobarHorario($controlador->{'modificarReserva' . $nuevo}($cambio), 'Mueve comunitaria a horario libre');
        $cambio['HoraReal'] = '10:00';
        comprobarHorario($controlador->{'modificarReserva' . $nuevo}($cambio), 'Reprograma comunitaria al horario compartido');
        comprobarHorario($controlador->{'modificarReserva' . $nuevo}($cambio), 'Edita comunitaria sin cambiar horario');

        $privada = datosHorario($nuevo, $fecha, '12:00');
        comprobarHorario($controlador->{'reservar' . $nuevo}($privada), 'Registra privada en horario libre');
        $idPrivada = (int) $c->query('SELECT LAST_INSERT_ID() AS id')->fetch_assoc()['id'];
        $cambio['HoraReal'] = '12:00';
        comprobarHorario(!$controlador->{'modificarReserva' . $nuevo}($cambio), 'Comunitaria no se mueve al horario privado');
        $cambio['CodRes'] = $idPrivada;
        $cambio['HoraReal'] = '10:00';
        $cambio['Realizacion'] = 'Comunitario';
        comprobarHorario(!$controlador->{'modificarReserva' . $nuevo}($cambio), 'No convierte una privada en comunitaria mediante POST de modificación');

        $cancelar = ['CodRes' => $idPrimera, 'EstadoRes' => 'Cancelado', 'FechaReal' => $fecha, 'HoraReal' => '10:00'];
        comprobarHorario($controlador->{'modificarReserva' . $ocupante}($cancelar), 'Cancela primera comunitaria');
        comprobarHorario(!$controlador->reservarMisa(datosHorario('Misa', $fecha, '10:00')), 'Otra comunitaria vigente mantiene ocupado el horario');
        $cancelar['CodRes'] = $idSegunda;
        comprobarHorario($controlador->{'modificarReserva' . $nuevo}($cancelar), 'Cancela última comunitaria');
        comprobarHorario($controlador->reservarMisa(datosHorario('Misa', $fecha, '10:00')), 'Última cancelación libera el horario para una privada');
    }
}

// El detalle de Otro mantiene horario exclusivo en ambos sentidos.
$otra = array_replace(datosHorario('Misa', '2099-07-01', '10:00'), ['Realizacion' => 'Otro', 'RealizacionOtro' => 'En una capilla']);
comprobarHorario($controlador->reservarMisa($otra), 'Registra Otro en horario libre');
foreach (['Privado', 'Comunitario', 'Otro'] as $realizacion) {
    $solicitud = array_replace(datosHorario('Misa', '2099-07-01', '10:00'), ['Realizacion' => $realizacion, 'RealizacionOtro' => 'Acción de gracias']);
    comprobarHorario(!$controlador->reservarMisa($solicitud), 'Otro no comparte su horario');
}

// Una segunda conexión ocupa el bloqueo sin escribir en las tablas reales.
comprobarHorario($real->query("SELECT IS_USED_LOCK(CONCAT('reservas:', SHA1(DATABASE()))) AS ocupado")->fetch_assoc()['ocupado'] === null, 'Libera bloqueo tras altas y modificaciones');
$bloqueado = $real->query("SELECT GET_LOCK(CONCAT('reservas:', SHA1(DATABASE())), 0) AS adquirido")->fetch_assoc()['adquirido'];
comprobarHorario((int) $bloqueado === 1, 'Segunda conexión adquiere bloqueo');
try {
    $antes = contarHorario($c);
    $envios = $correo->envios;
    comprobarHorario(!$controlador->reservarMisa(datosHorario('Misa', '2099-07-02', '10:00')), 'Alta simultánea espera y avisa si no obtiene turno');
    comprobarHorario(strpos($controlador->obtenerAvisoReserva()['mensaje'], 'procesando otra reserva') !== false, 'Aviso de concurrencia comprensible');
    comprobarHorario(contarHorario($c) === $antes && $correo->envios === $envios, 'Bloqueo no deja registros ni envía correo');
} finally {
    $real->query("SELECT RELEASE_LOCK(CONCAT('reservas:', SHA1(DATABASE())))");
}
comprobarHorario($controlador->reservarMisa(datosHorario('Misa', '2099-07-02', '10:00')), 'Permite guardar al liberarse el bloqueo');
comprobarHorario($real->query('SELECT COUNT(*) AS n FROM reserva')->fetch_assoc()['n'] === $conteoReal, 'No modifica reservas reales');
$c->close();
$real->close();
echo 'OK: ' . $comprobaciones . " comprobaciones; tablas temporales y correo simulado.\n";
