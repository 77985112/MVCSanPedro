<?php

/**
 * Impide que un catequista o coordinador con contraseña de un solo uso
 * navegue a otros módulos antes de crear su contraseña definitiva.
 */
class ControladorAccesoCatequista {
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

        $rol = $_SESSION['CiPersona']['RolCat'] ?? '';
        $destino = $rol === 'Coordinador'
            ? '../VistaCoordinador/Configuracion.php?cambio_obligatorio=1'
            : '../VistaCatequista/Configuracion.php?cambio_obligatorio=1';

        header('Location: ' . $destino);
        exit();
    }
}
?>
