<?php
session_start();
if (!isset($_SESSION['usuario']) && !isset($_SESSION['CiPersona'])) {
    http_response_code(403);
    exit('Acceso no autorizado.');
}

$archivo = basename((string) ($_GET['archivo'] ?? ''));
if ($archivo === '' || !preg_match('/^[A-Z0-9_]+\.(jpg|png|pdf)$/i', $archivo)) {
    http_response_code(400);
    exit('Documento no válido.');
}
$ruta = __DIR__ . '/../assets/documentos_persona/' . $archivo;
if (!is_file($ruta)) {
    http_response_code(404);
    exit('Documento no encontrado.');
}
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($ruta);
$permitidos = ['image/jpeg', 'image/png', 'application/pdf'];
if (!in_array($mime, $permitidos, true)) {
    http_response_code(415);
    exit('Tipo de documento no permitido.');
}
header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($ruta));
header('Content-Disposition: inline; filename="' . $archivo . '"');
header('X-Content-Type-Options: nosniff');
readfile($ruta);
?>