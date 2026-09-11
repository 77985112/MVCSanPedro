<?php
/**
 * Bootstrap para PHPUnit
 * Sistema Parroquia San Pedro de Sacaba
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuración de la base de datos de pruebas
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'bdsanpedro');

// URL base para pruebas funcionales
define('BASE_URL', 'http://localhost/MVCSanPedro');

/**
 * Conexion a la base de datos para pruebas
 */
function getTestConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Error de conexion: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}

/**
 * Limpiar datos de prueba
 */
function cleanTestData($conn) {
    // No eliminar datos reales, solo verificar integridad
    $tables = ['mensajepublico', 'certificado', 'detallecert', 'solicitante', 'inssacramento'];
    // Solo para pruebas con datos mock
}

echo "=== Bootstrap de pruebas cargado ===\n";
echo "Base de datos: " . DB_NAME . "\n";
echo "URL base: " . BASE_URL . "\n\n";
