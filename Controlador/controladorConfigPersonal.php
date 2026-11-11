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

            // Validar extensión permitida
            $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($extension, $extensionesPermitidas, true)) {
                echo "Formato de imagen no permitido. Use JPG, JPEG, PNG, GIF o WEBP.";
                return false;
            }

            // Obtener las iniciales del nombre completo
            $nombreCompleto = $_SESSION['usuario']['Nombre'] . ' ' . $_SESSION['usuario']['Paterno'] . ' ' . $_SESSION['usuario']['Materno'];
            $iniciales = '';
            foreach (explode(' ', $nombreCompleto) as $parte) {
                if (!empty($parte)) {
                    $iniciales .= strtoupper($parte[0]);
                }
            }

            $campo = ($tipoImagen === 'PerfilPer') ? 'PerfilPer' : 'FondoPer';

            // Generar un nombre único corto para evitar caché y quepa en VARCHAR(25)
            $tipoCorto = ($tipoImagen === 'PerfilPer') ? 'P' : 'F';
            $timestamp = base_convert((string) time(), 10, 36);
            $nuevaImagenNombre = "{$ciPersonal}_{$tipoCorto}_{$timestamp}.{$extension}";

            // Definir la ruta de la carpeta según el tipo de imagen
            $rutaCarpeta = ($tipoImagen === 'PerfilPer') ? "../assets/images/avatar/" : "../assets/images/fondo/";
            $rutaCompleta = $rutaCarpeta . $nuevaImagenNombre;

            // Borrar la imagen/fondo anterior si existe y no es la genérica
            $imagenAnterior = $_SESSION['usuario'][$campo] ?? 'Ninguno';
            if ($imagenAnterior !== 'Ninguno' && !empty($imagenAnterior)) {
                $rutaAnterior = $rutaCarpeta . basename($imagenAnterior);
                if (file_exists($rutaAnterior) && is_file($rutaAnterior)) {
                    @unlink($rutaAnterior);
                }
            }

            // Mover el archivo a la carpeta de destino
            if (move_uploaded_file($archivoTmp, $rutaCompleta)) {
                // Actualizar la base de datos
                if ($this->usuarioModel->actualizarImagen($ciPersonal, $campo, $nuevaImagenNombre)) {
                    $_SESSION['usuario'][$campo] = $nuevaImagenNombre;
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

    public function cambiarContrasena($ciPersonal, $actualC, $nuevoC1, $nuevoC2, $verificarActual = true)
    {
        // En el primer ingreso con una clave de un solo uso no se pide la clave
        // actual: el usuario ya se autenticó con ella. En los cambios normales
        // sí se exige para evitar que alguien cambie la clave sin conocerla.
        if ($verificarActual) {
            $usuario = $this->usuarioModel->obtenerUsuarioPorCI($ciPersonal);
            $claveGuardada = (string) ($usuario['ClavePer'] ?? '');
            $info = password_get_info($claveGuardada);
            $esHash = (int) ($info['algo'] ?? 0) !== 0;
            $actualC = (string) $actualC;

            $claveCorrecta = $esHash
                ? (password_verify($actualC, $claveGuardada)
                    || (!empty($_SESSION['usuario']['DebeCambiarClave'])
                        && password_verify(trim($actualC), $claveGuardada)))
                : hash_equals($claveGuardada, $actualC);

            if (!$claveCorrecta) {
                echo "La contraseña actual no es válida.";
                return false;
            }
        }

        if ($nuevoC1 === '') {
            echo "La nueva contraseña no puede estar vacía.";
            return false;
        }

        if ($nuevoC1 !== $nuevoC2) {
            echo "Las nuevas contraseñas no coinciden.";
            return false;
        }

        $nuevoHash = password_hash($nuevoC1, PASSWORD_DEFAULT);
        if ($this->usuarioModel->actualizarContrasena($ciPersonal, $nuevoHash)) {
            unset($_SESSION['usuario']['ClavePer']);
            $_SESSION['usuario']['DebeCambiarClave'] = 0;
            return true;
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
