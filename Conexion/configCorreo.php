<?php
// Configuración base de Apache y valores privados opcionales en correo.local.php.
$configCorreo = [
    'api_key' => trim(getenv('BREVO_API_KEY') ?: ''),
    'remitente' => trim(getenv('BREVO_SENDER_EMAIL') ?: ''),
    'nombre' => trim(getenv('BREVO_SENDER_NAME') ?: 'Parroquia San Pedro de Sacaba'),
];

$archivoCorreoLocal = __DIR__ . '/correo.local.php';
if (is_file($archivoCorreoLocal)) {
    if (!defined('SAN_PEDRO_CONFIG_CORREO')) {
        define('SAN_PEDRO_CONFIG_CORREO', true);
    }
    $configCorreoLocal = require $archivoCorreoLocal;
    if (is_array($configCorreoLocal)) {
        foreach (['api_key', 'remitente', 'nombre'] as $campoCorreo) {
            if (isset($configCorreoLocal[$campoCorreo]) && is_string($configCorreoLocal[$campoCorreo]) &&
                trim($configCorreoLocal[$campoCorreo]) !== '') {
                $configCorreo[$campoCorreo] = trim($configCorreoLocal[$campoCorreo]);
            }
        }
    }
}
return $configCorreo;
