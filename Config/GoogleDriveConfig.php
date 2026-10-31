<?php

// El respaldo en Drive es opcional: si las librerías de Composer no se
// subieron al servidor, el resto del sistema sigue funcionando.
$autoloadDrive = __DIR__ . '/../vendor/autoload.php';
if (is_file($autoloadDrive)) {
    require_once $autoloadDrive;
}

use Google\Client;

class GoogleDriveConfig {

    private static $instance = null;
    private $client;
    private $connected = false;
    private $tokenError = null;
    private $invalidando = false;

    const TOKEN_PATH = __DIR__ . '/../Config/google_token.json';
    const INVALID_PATH = __DIR__ . '/../Config/google_token.invalid';
    const CREDENTIALS_PATH = __DIR__ . '/../Config/credentials.json';
    const SCOPES = ['https://www.googleapis.com/auth/drive.file'];

    private function __construct() {
        if (!class_exists('Google\Client')) {
            $this->tokenError = 'El respaldo en Google Drive no está disponible en este servidor.';
            return;
        }

        $this->client = new Client();
        $this->client->setApplicationName('San Pedro - Respaldo de Certificados');
        $this->client->setScopes(self::SCOPES);
        $this->client->setAuthConfig(self::CREDENTIALS_PATH);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');

        // En el hosting la URL pública debe coincidir con la URI autorizada en
        // Google Cloud. Se fija con la variable de entorno DRIVE_REDIRECT_URI;
        // sin esa variable se conservan las rutas locales de esta instalación.
        $redirectLocal = getenv('GOOGLE_DRIVE_REDIRECT') ?: '';
        if (trim((string) $redirectLocal) !== '') {
            $redirect = trim($redirectLocal);
        } elseif (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' && !empty($_SERVER['HTTP_HOST'])) {
            $redirect = 'https://' . $_SERVER['HTTP_HOST'] . '/Controlador/googleDriveCallback.php';
        } else {
            $redirect = 'http://localhost/MVCSanPedro/Controlador/googleDriveCallback.php';
        }
        $this->client->setRedirectUri($redirect);

        $this->cargarToken();
    }

    /**
     * Carga el token guardado y, si está vencido, intenta renovarlo.
     * Si Google responde invalid_grant (token revocado o vencido) se elimina
     * el token para que la vista ofrezca volver a conectar la cuenta.
     */
    private function cargarToken() {
        if (!file_exists(self::TOKEN_PATH)) {
            // Si ya se invalidó antes, se conserva el motivo hasta que la
            // cuenta se vuelva a conectar.
            if (file_exists(self::INVALID_PATH)) {
                $this->tokenError = trim((string) @file_get_contents(self::INVALID_PATH));
            }
            return;
        }

        $contenido = @file_get_contents(self::TOKEN_PATH);
        $accessToken = $contenido ? json_decode($contenido, true) : null;

        if (!is_array($accessToken) || empty($accessToken['access_token'])) {
            $this->invalidarToken('No se pudo leer la conexión guardada de Google Drive.');
            return;
        }

        $this->client->setAccessToken($accessToken);

        if (!$this->client->isAccessTokenExpired()) {
            $this->connected = true;
            return;
        }

        $refreshToken = $this->client->getRefreshToken();
        if (!$refreshToken) {
            $this->invalidarToken('La conexión con Google Drive venció. Vuelve a conectar tu cuenta.');
            return;
        }

        try {
            $nuevoToken = $this->client->fetchAccessTokenWithRefreshToken($refreshToken);

            if (is_array($nuevoToken) && !empty($nuevoToken['error'])) {
                throw new RuntimeException($nuevoToken['error_description'] ?? $nuevoToken['error']);
            }

            $tokenActual = $this->client->getAccessToken();
            if (is_array($nuevoToken) && !empty($nuevoToken['access_token'])) {
                $tokenActual = $nuevoToken;
            }

            if (!$tokenActual || !empty($tokenActual['error'])) {
                throw new RuntimeException('No se pudo renovar la conexión con Google Drive.');
            }

            // Google no devuelve el refresh_token al renovar; se conserva el
            // original para poder renovar la conexión en la próxima hora.
            if (empty($tokenActual['refresh_token']) && !empty($refreshToken)) {
                $tokenActual['refresh_token'] = $refreshToken;
            }

            $this->client->setAccessToken($tokenActual);
            $this->saveToken($tokenActual);
            $this->connected = !$this->client->isAccessTokenExpired();
        } catch (Throwable $e) {
            $this->invalidarToken('La conexión con Google Drive venció. Vuelve a conectar tu cuenta.');
        }
    }

    /** Elimina el token inválido para evitar errores repetidos. */
    private function invalidarToken($mensaje) {
        if (!$this->invalidando) {
            $this->invalidando = true;
            @unlink(self::TOKEN_PATH);
            @file_put_contents(self::INVALID_PATH, $mensaje);
            $this->invalidando = false;
        }
        $this->tokenError = $mensaje;
        $this->connected = false;
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getClient() {
        return $this->client;
    }

    public function isConnected() {
        return $this->connected;
    }

    /** Mensaje para mostrar cuando la conexión guardada ya no sirve. */
    public function getTokenError() {
        return $this->tokenError;
    }

    public function getAuthUrl() {
        return $this->client ? $this->client->createAuthUrl() : '';
    }

    public function saveToken($tokenData) {
        file_put_contents(self::TOKEN_PATH, json_encode($tokenData));
        if (file_exists(self::INVALID_PATH)) {
            @unlink(self::INVALID_PATH);
        }
    }

    public function disconnect() {
        if (file_exists(self::TOKEN_PATH)) {
            unlink(self::TOKEN_PATH);
        }
        if (file_exists(self::INVALID_PATH)) {
            @unlink(self::INVALID_PATH);
        }
        $this->connected = false;
        $this->tokenError = null;
    }
}
