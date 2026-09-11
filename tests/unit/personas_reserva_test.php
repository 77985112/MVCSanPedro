<?php
// CLI; la tabla temporal oculta persona solo en esta conexión.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
chdir(__DIR__ . '/../../VistaPersonal');
require_once '../Controlador/ControladorPersona.php';
$c = (new Conexion())->getConnection();
$c->query('CREATE TEMPORARY TABLE prueba_persona_reserva LIKE persona');
$c->query('CREATE TEMPORARY TABLE persona LIKE prueba_persona_reserva');
$ref = new ReflectionClass(Persona::class);
$modelo = $ref->newInstanceWithoutConstructor();
$prop = $ref->getProperty('conn');
$prop->setAccessible(true);
$prop->setValue($modelo, $c);
$ref = new ReflectionClass(ControladorPersona::class);
$controlador = $ref->newInstanceWithoutConstructor();
$prop = $ref->getProperty('modelo');
$prop->setAccessible(true);
$prop->setValue($controlador, $modelo);
$total = 0;
function comprobarPersonaReserva($ok, $mensaje) {
    global $total;
    if (!$ok) throw new RuntimeException($mensaje);
    $total++;
}
$datos = ['CiPersona' => '  0012345  ', 'Nombre' => 'Persona de prueba',
    'ApPaterno' => 'Apellido', 'ApMaterno' => '', 'Sexo' => 'Mujer',
    'FechaNac' => '2000-01-01', 'Direccion' => '', 'Contacto' => '', 'Estado_per' => 'Soltera'];
comprobarPersonaReserva($controlador->crearPersona($datos), 'Guarda persona');
comprobarPersonaReserva($controlador->obtenerCiPersonaRegistrada() === '0012345', 'Devuelve CI normalizado conservando ceros iniciales');
comprobarPersonaReserva($controlador->buscarPersona('0012345')['Nombre'] === 'Persona de prueba', 'Encuentra el nombre después del alta');
$partida = array_replace($datos, ['CiPersona' => '', 'Partida' => 'PN000123', 'Estado_per' => '']);
comprobarPersonaReserva($controlador->crearPersona($partida), 'Registra bautizando con partida');
comprobarPersonaReserva($controlador->obtenerCiPersonaRegistrada() === 'PN000123', 'Devuelve la partida usada como identificador');
comprobarPersonaReserva($controlador->buscarPersona('PN000123')['CiPersona'] === 'PN000123', 'Filtra el bautizando recién registrado');
$certificado = array_replace($datos, ['CiPersona' => 'registro-antes', 'Partida' => '777777']);
comprobarPersonaReserva($controlador->crearPersona($certificado), 'Prepara coincidencia parcial anterior');
$exacta = array_replace($datos, ['CiPersona' => '777777', 'Nombre' => 'Coincidencia exacta']);
comprobarPersonaReserva($controlador->crearPersona($exacta), 'Registra CI exacto');
comprobarPersonaReserva($controlador->buscarPersona('777777')['Nombre'] === 'Coincidencia exacta', 'Prioriza CI exacto frente a certificado parcial');
comprobarPersonaReserva($controlador->crearPersona(array_replace($exacta, ['Nombre' => 'No reemplazar'])), 'Permite seleccionar CI ya existente');
comprobarPersonaReserva($controlador->obtenerCiPersonaRegistrada() === '777777', 'Devuelve el CI existente');
comprobarPersonaReserva($controlador->buscarPersona('777777')['Nombre'] === 'Coincidencia exacta', 'Mantiene datos de la persona existente');
comprobarPersonaReserva($controlador->buscarPersona("' OR 1=1 --") === null, 'La búsqueda trata el CI como texto literal');
$prop->setValue($controlador, new class { public function crear(...$datos) { return false; } });
comprobarPersonaReserva(!$controlador->crearPersona($datos), 'Propaga fallo del guardado');
comprobarPersonaReserva($controlador->obtenerCiPersonaRegistrada() === null, 'No devuelve CI anterior cuando falla');
$c->close();
echo "OK: $total comprobaciones; personas temporales, sin registros reales.\n";
