<?php

/**
 * Impide que un catequizando con contraseña de un solo uso navegue a otros
 * módulos antes de crear su contraseña definitiva.
 */
class ControladorAccesoCelebrante {
    public static function exigirCambioClave() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['CiPersona']['DebeCambiarClave'])) {
            return;
        }

        $archivoActual = basename($_SERVER['SCRIPT_NAME'] ?? '');
        if ($archivoActual === 'Configuracion.php') {
            return;
        }

        header('Location: Configuracion.php?cambio_obligatorio=1');
        exit();
    }
}
?>
