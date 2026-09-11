<?php
require_once '../Controlador/ControladorPersona.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear'])) {
    $controller = new ControladorPersona();
    try {
        $result = $controller->crearPersona($_POST);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Persona registrada correctamente',
                'ciPersona' => $controller->obtenerCiPersonaRegistrada()]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al registrar la persona']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Solicitud inválida']);
}
?>
