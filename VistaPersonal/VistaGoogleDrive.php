<?php
// Ruta conservada por compatibilidad. Google Drive ahora se administra dentro
// de la pantalla Certificados Emitidos.
$parametros = $_GET;
$destino = 'VistaEmitidos.php';

if (!empty($parametros)) {
    $destino .= '?' . http_build_query($parametros);
}

header('Location: ' . $destino . '#respaldo-drive');
exit();
