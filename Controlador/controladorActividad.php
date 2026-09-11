<?php
require_once '../Modelo/modeloActividad.php';

class ControladorActividad
{
    private $actividadModel;

    public function __construct()
    {
        $this->actividadModel = new Actividad();
    }

    public function obtenerActividades($tipo = null)
    {
        return $this->actividadModel->obtenerActividades($tipo);
    }

    public function registrarActividad($data)
    {
        $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/MVCSanPedro/assets/images/events/";

        if (isset($_FILES['NuevaImagenActividad']) && $_FILES['NuevaImagenActividad']['error'] === UPLOAD_ERR_OK) {
            $archivoTmp = $_FILES['NuevaImagenActividad']['tmp_name'];
            $nombreArchivo = $_FILES['NuevaImagenActividad']['name'];
            $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));

            if (!in_array($extension, ['jpg', 'jpeg', 'png'])) {
                throw new Exception("Solo se permiten archivos JPG, JPEG y PNG.");
            }

            $nuevoNombreImagen = "ACT_" . time() . "." . $extension;
            $rutaCompleta = $target_dir . $nuevoNombreImagen;

            if (move_uploaded_file($archivoTmp, $rutaCompleta)) {
                $data['ImagenActividad'] = $nuevoNombreImagen;
            } else {
                throw new Exception("Error al mover el archivo a la carpeta de destino.");
            }
        } else {
            throw new Exception("Error al cargar la imagen.");
        }
        return $this->actividadModel->registrarActividad($data);
    }


    public function actualizarActividad($data)
    {
        $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/MVCSanPedro/assets/images/events/";
        $imagenAnterior = $data['ImagenAnterior'];

        if (isset($_FILES['NuevaImagenActividad']) && $_FILES['NuevaImagenActividad']['error'] == UPLOAD_ERR_OK) {
            $archivoTmp = $_FILES['NuevaImagenActividad']['tmp_name'];
            $nombreArchivo = $_FILES['NuevaImagenActividad']['name'];
            $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));

            if (!in_array($extension, ['jpg', 'jpeg', 'png'])) {
                throw new Exception("Solo se permiten archivos JPG, JPEG y PNG.");
            }

            $nuevoNombreImagen = "ACT_" . time() . "." . $extension;
            $rutaCompleta = $target_dir . $nuevoNombreImagen;

            if (move_uploaded_file($archivoTmp, $rutaCompleta)) {
                if (!empty($imagenAnterior) && file_exists($target_dir . $imagenAnterior) && $imagenAnterior !== $nuevoNombreImagen) {
                    unlink($target_dir . $imagenAnterior);
                }
                $data['ImagenActividad'] = $nuevoNombreImagen;
            } else {
                throw new Exception("Error al mover el archivo a la carpeta de destino.");
            }
        } else {
            $data['ImagenActividad'] = $imagenAnterior;
        }

        return $this->actividadModel->actualizarActividad($data);
    }
}
