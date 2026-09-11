<?php
require_once '../Modelo/modeloConfigPersonal.php';

class UsuarioController
{
    private $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new UsuarioModel();
    }

    public function cambiarImagen($ciPersonal, $tipoImagen, $file) {
        if ($file['error'] === UPLOAD_ERR_OK) {
            $archivoTmp = $file['tmp_name'];
            $nombreArchivo = $file['name'];
            $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
    
            // Obtener las iniciales del nombre completo
            $nombreCompleto = $_SESSION['usuario']['Nombre'] . ' ' . $_SESSION['usuario']['Paterno'] . ' ' . $_SESSION['usuario']['Materno'];
            $iniciales = '';
            foreach (explode(' ', $nombreCompleto) as $parte) {
                if (!empty($parte)) {
                    $iniciales .= strtoupper($parte[0]);
                }
            }
    
            // Generar el nuevo nombre de archivo
            $nuevaImagenNombre = "{$ciPersonal}{$iniciales}{$tipoImagen}.{$extension}";
    
            // Definir la ruta de la carpeta según el tipo de imagen
            $rutaCarpeta = ($tipoImagen === 'PerfilPer') ? "../assets/images/avatar/" : "../assets/images/fondo/";
            $rutaCompleta = $rutaCarpeta . $nuevaImagenNombre;
    
            // Eliminar imagen anterior si existe
            if ($tipoImagen === 'PerfilPer' && !empty($_SESSION['usuario']['PerfilPer'])) {
                $rutaImagenAnterior = $rutaCarpeta . $_SESSION['usuario']['PerfilPer'];
                if (file_exists($rutaImagenAnterior)) {
                    unlink($rutaImagenAnterior);
                }
            } elseif ($tipoImagen === 'FondoPer' && !empty($_SESSION['usuario']['FondoPer'])) {
                $rutaImagenAnterior = $rutaCarpeta . $_SESSION['usuario']['FondoPer'];
                if (file_exists($rutaImagenAnterior)) {
                    unlink($rutaImagenAnterior);
                }
            }
    
            // Mover el archivo a la carpeta de destino
            if (move_uploaded_file($archivoTmp, $rutaCompleta)) {
                // Actualizar la base de datos
                if ($this->usuarioModel->actualizarImagen($ciPersonal, ($tipoImagen === 'PerfilPer' ? 'PerfilPer' : 'FondoPer'), $nuevaImagenNombre)) {
                    $_SESSION['usuario'][($tipoImagen === 'PerfilPer' ? 'PerfilPer' : 'FondoPer')] = $nuevaImagenNombre;
                    return true;
                } else {
                    echo "Error al actualizar la base de datos.";
                }
            } else {
                echo "Error al mover el archivo a la carpeta de destino.";
            }
        } else {
            echo "Error al cargar el archivo: " . $file['error'];
        }
        return false;
    }

    public function cambiarFrase($ciPersonal, $frase)
    {
        if ($this->usuarioModel->actualizarFrase($ciPersonal, $frase)) {
            $_SESSION['usuario']['FrasePer'] = $frase;
            return true;
        }
        return false;
    }

    public function cambiarContacto($ciPersonal, $contacto)
    {
        if ($this->usuarioModel->actualizarContacto($ciPersonal, $contacto)) {
            $_SESSION['usuario']['ContactoPer'] = $contacto;
            return true;
        }
        return false;
    }

    public function cambiarContrasena($ciPersonal, $actualC, $nuevoC1, $nuevoC2, $claveActual)
    {
        if ($actualC === $claveActual) {
            if ($nuevoC1 === $nuevoC2) {
                return $this->usuarioModel->actualizarContrasena($ciPersonal, password_hash($nuevoC1, PASSWORD_DEFAULT));
            } else {
                echo "Las nuevas contraseñas no coinciden.";
            }
        } else {
            echo "La contraseña actual no es válida.";
        }
        return false;
    }

    public function eliminarCuenta($ciPersonal)
    {
        if ($this->usuarioModel->eliminarCuenta($ciPersonal)) {
            session_destroy();
            header("Location: ../logout.php");
            exit();
        }
    }

    private function generarNombreArchivo($ciPersonal, $tipoImagen, $extension)
    {
        return "{$ciPersonal}_{$tipoImagen}." . uniqid() . ".{$extension}";
    }
}
