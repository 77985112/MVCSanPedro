<?php
require_once '../Controlador/controladorSacramentoFecha.php';

if (isset($_POST['idSacramento'])) {
    $idSacramento = $_POST['idSacramento'];

    $controller = new ControladorSacramento();

    $datosSacramento = $controller->obtenerSacramentoPorId($idSacramento);

    if ($datosSacramento) {
        echo json_encode([
            'estadito' => $datosSacramento['estadito'],
            'Fecha_Ini' => $datosSacramento['Fecha_Ini'],
            'Fecha_Fin' => $datosSacramento['Fecha_Fin'],
            'Hora_Ini' => date('H:i', strtotime($datosSacramento['Fecha_Ini'])),
            'Hora_Fin' => date('H:i', strtotime($datosSacramento['Fecha_Fin']))
        ]);
    } else {
        echo json_encode(['error' => 'No se encontraron datos']);
    }
}
?>
