<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Google\Client;

class GoogleDriveConfig {

    private static $instance = null;
    private $client;

    const TOKEN_PATH = __DIR__ . '/../Config/google_token.json';
    const CREDENTIALS_PATH = __DIR__ . '/../Config/credentials.json';
    const SCOPES = ['https://www.googleapis.com/auth/drive.file'];

    private function __construct() {
        $this->client = new Client();
        $this->client->setApplicationName('San Pedro - Respaldo de Certificados');
        $this->client->setScopes(self::SCOPES);
        $this->client->setAuthConfig(self::CREDENTIALS_PATH);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
        $this->client->setRedirectUri('http://localhost/MVCSanPedro/Controlador/googleDriveCallback.php');

        if (file_exists(self::TOKEN_PATH)) {
            $accessToken = json_decode(file_get_contents(self::TOKEN_PATH), true);
            $this->client->setAccessToken($accessToken);

            if ($this->client->isAccessTokenExpired()) {
                if ($this->client->getRefreshToken()) {
                    $this->client->fetchAccessTokenWithRefreshToken();
                    file_put_contents(self::TOKEN_PATH, json_encode($this->client->getAccessToken()));
                }
            }
        }
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
        return file_exists(self::TOKEN_PATH) && $this->client->getAccessToken();
    }

    public function getAuthUrl() {
        return $this->client->createAuthUrl();
    }

    public function saveToken($tokenData) {
        file_put_contents(self::TOKEN_PATH, json_encode($tokenData));
    }

    public function disconnect() {
        if (file_exists(self::TOKEN_PATH)) {
            unlink(self::TOKEN_PATH);
        }
    }
}
