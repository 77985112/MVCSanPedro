<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../Controlador/ControladorPersona.php';

$respuesta = ['success' => false, 'message' => 'Solicitud no válida.'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador = new ControladorPersona();
    if ($controlador->crearPersona($_POST)) {
        $respuesta = [
            'success' => true,
            'ciPersona' => $controlador->obtenerCiPersonaRegistrada(),
            'message' => 'Persona registrada correctamente.'
        ];
    } else {
        http_response_code(422);
        $respuesta['message'] = $controlador->obtenerError() ?: 'No se pudo registrar la persona.';
    }
}
echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
?>