<?php
include_once '../Modelo/modeloConfigCatequista.php';

$conexion = new Conexion();
$db = $conexion->getConnection();

$Usuario = new Usuario($db);

$Usuario->CiCat = $_SESSION['CiPersona']['CiCat'];
$Usuario->CiPersona = $_SESSION['CiPersona']['CiPersona'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['cambiarPerfil']) || isset($_POST['cambiarFondo'])) {
        if ($_FILES['nuevaImagen']['error'] === UPLOAD_ERR_OK) {
            $archivoTmp = $_FILES['nuevaImagen']['tmp_name'];
            $nombreArchivo = $_FILES['nuevaImagen']['name'];
            $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
            $nuevaImagenNombre = $_SESSION['CiPersona']['Nombre'][0] . $_SESSION['CiPersona']['ApPaterno'][0] . $_SESSION['CiPersona']['ApMaterno'][0] . $Usuario->CiCat;
            $nuevaImagenNombre .= isset($_POST['cambiarPerfil']) ? "Perfil" : "Fondo";
            $nuevaImagenNombre .= "." . $extension;
            $rutaCarpeta = isset($_POST['cambiarPerfil']) ? "../assets/images/avatar/" : "../assets/images/fondo/";
            $rutaCompleta = $rutaCarpeta . $nuevaImagenNombre;

            if (isset($_POST['cambiarPerfil']) && !empty($_SESSION['CiPersona']['ImagenCat'])) {
                $rutaImagenAnterior = $rutaCarpeta . $_SESSION['CiPersona']['ImagenCat'];
                if (file_exists($rutaImagenAnterior)) {
                    unlink($rutaImagenAnterior);
                }
            } elseif (isset($_POST['cambiarFondo']) && !empty($_SESSION['CiPersona']['FondoCat'])) {
                $rutaImagenAnterior = $rutaCarpeta . $_SESSION['CiPersona']['FondoCat'];
                if (file_exists($rutaImagenAnterior)) {
                    unlink($rutaImagenAnterior);
                }
            }

            if (move_uploaded_file($archivoTmp, $rutaCompleta)) {
                $column = isset($_POST['cambiarPerfil']) ? "ImagenCat" : "FondoCat";
                $Usuario->updateImage($column, $nuevaImagenNombre);
                $_SESSION['CiPersona'][$column] = $nuevaImagenNombre;
                header("Refresh:1");
                exit();
            } else {
                echo "Error al mover el archivo a la carpeta de destino.";
            }
        } else {
            echo "Error al cargar el archivo: " . $_FILES['nuevaImagen']['error'];
        }
    }

    if (isset($_POST['cambiarFrase'])) {
        $frase = $_POST['frase'];
        $Usuario->updateFrase($frase);
        $_SESSION['CiPersona']['FraseCat'] = $frase;
        header("Refresh:1");
        exit();
    }

    if (isset($_POST['cambiarContacto'])) {
        $contacto = $_POST['Contactodato'];
        $Usuario->updateContacto($contacto);
        $_SESSION['CiPersona']['Contacto'] = $contacto;
        header("Refresh:1");
        exit();
    }

    if (isset($_POST['cambiarEstadoPer'])) {
        $estadoPer = $_POST['Estado_per'];
        $Usuario->updateEstado($estadoPer);
        $_SESSION['CiPersona']['Estado_per'] = $estadoPer;
        header("Refresh:1");
        exit();
    }

    if (isset($_POST['cambiarContracat'])) {
        $CActual = (string) ($_POST['ActualC'] ?? '');
        $CNuevo1 = (string) ($_POST['NuevoC1'] ?? '');
        $CNuevo2 = (string) ($_POST['NuevoC2'] ?? '');

        // En el primer ingreso con una clave de un solo uso no se pide la clave
        // actual: el catequista ya se autenticó con ella.
        $verificarActual = empty($_SESSION['CiPersona']['DebeCambiarClave']);

        if (!$verificarActual || $Usuario->verificarClave($CActual)) {
            if ($CNuevo1 === '') {
                $_SESSION['errorClave'] = 'La nueva contraseña no puede estar vacía.';
                header("Location: Configuracion.php");
                exit();
            }
            if ($CNuevo1 !== $CNuevo2) {
                $_SESSION['errorClave'] = 'Las nuevas contraseñas no coinciden.';
                header("Location: Configuracion.php");
                exit();
            }

            $Usuario->updateClave(password_hash($CNuevo1, PASSWORD_DEFAULT));
            unset($_SESSION['CiPersona']['ClaveCat']);
            $_SESSION['CiPersona']['DebeCambiarClave'] = 0;
            $_SESSION['mensajeClave'] = 'Contraseña cambiada correctamente.';
            header("Location: Configuracion.php");
            exit();
        }

        $_SESSION['errorClave'] = 'La contraseña actual no es válida.';
        header("Location: Configuracion.php");
        exit();
    }

    if (isset($_POST['guardarPrivacidad'])) {
        $Usuario->guardarPrivacidad(
            ($_POST['MostrarContacto'] ?? '0') === '1',
            ($_POST['MostrarFechaNac'] ?? '0') === '1',
            ($_POST['MostrarEstado'] ?? '0') === '1',
            ($_POST['MostrarDireccion'] ?? '0') === '1'
        );
        $_SESSION['mensajePrivacidad'] = 'Preferencias de privacidad guardadas correctamente.';
        header("Location: Configuracion.php#nav-setting-tab-2");
        exit();
    }

    if (isset($_POST['EliminarCount'])) {
        $CiCatCount = $_POST['CiCatCount'];
        if ($CiCatCount == $_SESSION['CiPersona']['CiCat']) {
            $Usuario->updateClave('Inactivo');
            header("Location: ../logout.php");
            exit();
        } else {
            echo "No se encuentra el Catequista.";
        }
    }
}

$Privacidad = $Usuario->obtenerPrivacidad();
?>
