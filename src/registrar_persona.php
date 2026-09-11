<?php
include '../Conexion/Conexion.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conexion = (new Conexion())->getConnection();

    $ciPersona = $_POST['ciPersona'];
    $nombre = $_POST['nombre'];
    $apPaterno = $_POST['apPaterno'];
    $apMaterno = $_POST['apMaterno'];
    $sexo = $_POST['sexo'];
    $fechaNac = $_POST['fechaNac'];
    $direccion = $_POST['direccion'];
    $contacto = $_POST['contacto'];
    $estadoPer = $_POST['estadoPer'];

    $stmt = $conexion->prepare("CALL RegistrarPersona(?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $ciPersona, $nombre, $apPaterno, $apMaterno, $sexo, $fechaNac, $direccion, $contacto, $estadoPer);

    if ($stmt->execute()) {
        $response['success'] = true;
    } else {
        $response['message'] = "Error: " . $stmt->error;
    }

    $stmt->close();
    $conexion->close();
}

echo json_encode($response);
?>
