<?php
session_start();
require_once('Conexion/Conexion.php');

if (!isset($_SESSION['CiPersona'])) {
  echo json_encode(['error' => 'Usuario no autenticado']);
  exit();
}

$Celebrante = $_SESSION['CiPersona'];
$Persona = $Celebrante['CiCel'];

$conexion = new Conexion();
$conn = $conexion->getConnection();

$stmt = $conn->prepare("CALL VerificarEstadoCelebrante(?)");
$stmt->bind_param("s", $Persona);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
  $row = $result->fetch_assoc();
  $response = [
    'EstadoCel' => $row['EstadoCel']
  ];
  $_SESSION['CiPersona']['EstadoCel'] = $row['EstadoCel'];

  $_SESSION['CiPersona']['Color_Grupo'] = $row['Color_Grupo'];
  $_SESSION['CiPersona']['PerfilCel'] = $row['PerfilCel'];
  $_SESSION['CiPersona']['FondoCel'] = $row['FondoCel'];
  $_SESSION['CiPersona']['Imagen_Grupo'] = $row['Imagen_Grupo'];
  $_SESSION['CiPersona']['Santo_Grupo'] = $row['Santo_Grupo'];
  $_SESSION['CiPersona']['Frase_Santo'] = $row['Frase_Santo'];
  $_SESSION['CiPersona']['NombreGrupo'] = $row['NombreGrupo'];
  $_SESSION['CiPersona']['DescripItem'] = $row['DescripItem'];

  error_log(print_r($response, true));
    echo json_encode($response);
} else {
    $error = ['error' => 'No se encontraron datos'];
    error_log(print_r($error, true));
    echo json_encode($error);
}

$stmt->close();
$conn->close();
exit();
