<?php
include '../Conexion/Conexion.php';

$response = ['success' => true, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conexion = (new Conexion())->getConnection();
    $ciFamiliar = $_POST['ciFamiliar'];
    $rolFam = $_POST['rolFam'];
    $parentesco = $_POST['parentesco'];
    $celebrantes = $_POST['celebrantes'];

    foreach ($celebrantes as $idInscripcion) {
        $stmt = $conexion->prepare("CALL registrarFamiliar(?, ?, ?, ?)");
        $stmt->bind_param("siss", $ciFamiliar, $idInscripcion, $rolFam, $parentesco);

        if (!$stmt->execute()) {
            $response['success'] = false;
            $response['message'] = "Error al registrar apoderado para el catequisando con ID ${idInscripcion}: " . $stmt->error;
            break;
        }
        $stmt->close();
    }

    $conexion->close();
}

echo json_encode($response);
?>
