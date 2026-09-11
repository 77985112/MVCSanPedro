<?php
session_start();
require_once __DIR__ . '/../Config/GoogleDriveConfig.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: ../logout.php");
    exit();
}

if (!isset($_GET['code'])) {
    header("Location: ../VistaPersonal/VistaPersonal.php");
    exit();
}

try {
    $config = GoogleDriveConfig::getInstance();
    $client = $config->getClient();

    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    if (isset($token['error'])) {
        throw new Exception("Error al obtener token: " . $token['error']);
    }

    $config->saveToken($token);

    header("Location: ../VistaPersonal/VistaPersonal.php?drive=conectado");
    exit();

} catch (Exception $e) {
    header("Location: ../VistaPersonal/VistaPersonal.php?drive=error&msg=" . urlencode($e->getMessage()));
    exit();
}
