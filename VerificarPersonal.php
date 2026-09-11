<?php
session_start();
require_once('Conexion/Conexion.php');

if (!isset($_SESSION['usuario'])) {
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit();
}

$Personal = $_SESSION['usuario'];
$Persona = $Personal['CiPersonal'];

$conexion = new Conexion();
$conn = $conexion->getConnection();

$stmt = $conn->prepare("CALL VerificarEstadoPersonal(?)");
$stmt->bind_param("s", $Persona);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $response = [
        'Estado' => $row['Estado'],
        'DescripCar' => $row['DescripCar'],
        'PerfilPer' => $row['PerfilPer']
    ];

    $_SESSION['usuario']['Estado'] = $row['Estado'];
    $_SESSION['usuario']['DescripCar'] = $row['DescripCar'];

    $_SESSION['usuario']['ClavePer'] = $row['ClavePer'];
    $_SESSION['usuario']['ContactoPer'] = $row['ContactoPer'];
    $_SESSION['usuario']['PerfilPer'] = $row['PerfilPer'];
    $_SESSION['usuario']['FondoPer'] = $row['FondoPer'];
    $_SESSION['usuario']['FrasePer'] = $row['FrasePer'];

    echo json_encode($response);
    
} else {
    $error = ['error' => 'No se encontraron datos'];
    error_log(print_r($error, true));
    echo json_encode($error);
}

$stmt->close();
$conn->close();
exit();
?>