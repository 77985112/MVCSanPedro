<?php
session_start();
require_once('Conexion/Conexion.php');

if (!isset($_SESSION['CiPersona'])) {
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit();
}

$Catequista = $_SESSION['CiPersona'];
$Persona = $Catequista['CiCat'];

$conexion = new Conexion();
$conn = $conexion->getConnection();

$stmt = $conn->prepare("CALL VerificarEstadoCatequista(?)");
$stmt->bind_param("s", $Persona);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $response = [
        'EstadoCat' => $row['EstadoCat'],
        'RolCat' => $row['RolCat']
    ];

    $_SESSION['CiPersona']['EstadoCat'] = $row['EstadoCat'];
    $_SESSION['CiPersona']['RolCat'] = $row['RolCat'];

    $_SESSION['CiPersona']['ImagenCat'] = $row['ImagenCat'];
    $_SESSION['CiPersona']['FondoCat'] = $row['FondoCat'];
    $_SESSION['CiPersona']['PoderCat'] = $row['PoderCat'];
    $_SESSION['CiPersona']['FraseCat'] = $row['FraseCat'];
    $_SESSION['CiPersona']['NombreGrupo'] = $row['NombreGrupo'];
    $_SESSION['CiPersona']['Color_Grupo'] = $row['Color_Grupo'];
    $_SESSION['CiPersona']['Santo_Grupo'] = $row['Santo_Grupo'];
    $_SESSION['CiPersona']['Imagen_Grupo'] = $row['Imagen_Grupo'];
    $_SESSION['CiPersona']['Frase_Santo'] = $row['Frase_Santo'];

    echo json_encode($response);
} else {
    $error = ['error' => 'No se encontraron datos'];
    echo json_encode($error);
}

$stmt->close();

// Permisos que el titular del grupo haya asignado a este catequista.
$stmtPerm = $conn->prepare("SELECT PuedeAsistenciaApoderados, PuedePuntajeExamenes, PuedeRegistrarCatequizandos
                            FROM permisos_catequista WHERE CiCat = ? LIMIT 1");
if ($stmtPerm) {
    $stmtPerm->bind_param("s", $Persona);
    $stmtPerm->execute();
    $perm = $stmtPerm->get_result()->fetch_assoc();
    $stmtPerm->close();
    $_SESSION['CiPersona']['PuedeAsistenciaApoderados'] = (int) ($perm['PuedeAsistenciaApoderados'] ?? 0);
    $_SESSION['CiPersona']['PuedePuntajeExamenes'] = (int) ($perm['PuedePuntajeExamenes'] ?? 0);
    $_SESSION['CiPersona']['PuedeRegistrarCatequizandos'] = (int) ($perm['PuedeRegistrarCatequizandos'] ?? 0);
}

$conn->close();
exit();
