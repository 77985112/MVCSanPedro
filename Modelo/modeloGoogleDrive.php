<?php
require_once __DIR__ . '/../Config/GoogleDriveConfig.php';

use Google\Service\Drive;
use Google\Service\Drive\DriveFile;

class ModeloGoogleDrive {

    private $service;

    public function __construct() {
        $config = GoogleDriveConfig::getInstance();
        $this->service = new Drive($config->getClient());
    }

    public function estaConectado() {
        $config = GoogleDriveConfig::getInstance();
        return $config->isConnected();
    }

    public function obtenerUrlConexion() {
        $config = GoogleDriveConfig::getInstance();
        return $config->getAuthUrl();
    }

    public function desconectar() {
        $config = GoogleDriveConfig::getInstance();
        $config->disconnect();
    }

    public function subirArchivo($filePath, $fileName, $mimeType = 'application/pdf') {
        $fileMetadata = new DriveFile([
            'name' => $fileName,
            'mimeType' => $mimeType
        ]);

        $content = file_get_contents($filePath);
        $file = $this->service->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => $mimeType,
            'uploadType' => 'multipart'
        ]);

        return $file->getId();
    }

    public function subirPDF($pdfContent, $fileName) {
        $fileMetadata = new DriveFile([
            'name' => $fileName,
            'mimeType' => 'application/pdf'
        ]);

        $file = $this->service->files->create($fileMetadata, [
            'data' => $pdfContent,
            'mimeType' => 'application/pdf',
            'uploadType' => 'multipart'
        ]);

        return $file->getId();
    }

    public function crearCarpeta($nombreCarpeta, $parentId = null) {
        $fileMetadata = new DriveFile([
            'name' => $nombreCarpeta,
            'mimeType' => 'application/vnd.google-apps.folder'
        ]);

        if ($parentId) {
            $fileMetadata->setParents([$parentId]);
        }

        $file = $this->service->files->create($fileMetadata, [
            'fields' => 'id'
        ]);

        return $file->id;
    }

    public function buscarCarpeta($nombreCarpeta) {
        $query = "mimeType='application/vnd.google-apps.folder' and name='$nombreCarpeta' and trashed=false";
        $results = $this->service->files->listFiles([
            'q' => $query,
            'spaces' => 'drive',
            'fields' => 'files(id, name)'
        ]);

        $files = $results->getFiles();
        if (count($files) > 0) {
            return $files[0]->getId();
        }
        return null;
    }

    public function obtenerOCrearCarpeta($nombreCarpeta, $parentId = null) {
        $carpetaId = $this->buscarCarpeta($nombreCarpeta);
        if (!$carpetaId) {
            $carpetaId = $this->crearCarpeta($nombreCarpeta, $parentId);
        }
        return $carpetaId;
    }

    public function listarArchivos($carpetaId = null, $limite = 50) {
        $query = "trashed=false";
        if ($carpetaId) {
            $query .= " and '$carpetaId' in parents";
        }

        $results = $this->service->files->listFiles([
            'q' => $query,
            'spaces' => 'drive',
            'fields' => 'files(id, name, mimeType, createdTime, size)',
            'orderBy' => 'createdTime desc',
            'pageSize' => $limite
        ]);

        return $results->getFiles();
    }

    public function eliminarArchivo($fileId) {
        $this->service->files->delete($fileId);
        return true;
    }

    public function descargarArchivo($fileId) {
        $response = $this->service->files->get($fileId, ['alt' => 'media']);
        return $response->getBody()->getContents();
    }
}
