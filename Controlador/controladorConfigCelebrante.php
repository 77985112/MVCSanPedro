<?php
require_once '../Conexion/Conexion.php';
require_once '../Modelo/modeloConfigCelebrante.php';

$conexion = new Conexion();
$db = $conexion->getConnection();
$celebrante = new Celebrante($db);

$celebrante->CiCel = $_SESSION['CiPersona']['CiCel'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['cambiarPerfil']) || isset($_POST['cambiarFondo'])) {
        if ($_FILES['nuevaImagen']['error'] == UPLOAD_ERR_OK) {
            $archivoTmp = $_FILES['nuevaImagen']['tmp_name'];
            $nombreArchivo = $_FILES['nuevaImagen']['name'];
            $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
            $nuevaImagenNombre = $celebrante->Nombre[0] . $celebrante->ApPaterno[0] . $celebrante->ApMaterno[0] . $celebrante->CiCel;
            $nuevaImagenNombre .= (isset($_POST['cambiarPerfil'])) ? "Perfil" : "Fondo";
            $nuevaImagenNombre .= "." . $extension;
            $rutaCarpeta = (isset($_POST['cambiarPerfil'])) ? "../assets/images/avatar/" : "../assets/images/fondo/";
            $rutaCompleta = $rutaCarpeta . $nuevaImagenNombre;

            if (isset($_POST['cambiarPerfil']) && !empty($celebrante->PerfilCel)) {
                $rutaImagenAnterior = $rutaCarpeta . $celebrante->PerfilCel;
                if (file_exists($rutaImagenAnterior)) {
                    unlink($rutaImagenAnterior);
                }
            } elseif (isset($_POST['cambiarFondo']) && !empty($celebrante->FondoCel)) {
                $rutaImagenAnterior = $rutaCarpeta . $celebrante->FondoCel;
                if (file_exists($rutaImagenAnterior)) {
                    unlink($rutaImagenAnterior);
                }
            }

            if (move_uploaded_file($archivoTmp, $rutaCompleta)) {
                $column = isset($_POST['cambiarPerfil']) ? "PerfilCel" : "FondoCel";
                if ($celebrante->updateImage($column, $nuevaImagenNombre)) {
                    header("Refresh:1");
                } else {
                    echo "Error al actualizar la imagen en la base de datos.";
                }
            } else {
                echo "Error al mover el archivo a la carpeta de destino.";
            }
        } else {
            echo "Error al cargar el archivo: " . $_FILES['nuevaImagen']['error'];
        }
    }

    if (isset($_POST['cambiarContacto'])) {
        $contacto = $_POST['Contactodato'];
        if ($celebrante->updateContact($contacto)) {
            $_SESSION['CiPersona']['Contacto'] = $contacto;
            header("Refresh:1");
        } else {
            echo "Error al actualizar el contacto.";
        }
    }


    if (isset($_POST['cambiarContracat'])) {
        $CActual = $_POST['ActualC'];
        $CNuevo1 = $_POST['NuevoC1'];
        $CNuevo2 = $_POST['NuevoC2'];

        if ($CActual == $celebrante->ClaveCel) {
            if ($CNuevo1 == $CNuevo2) {
                if ($celebrante->updatePassword($CNuevo2)) {
                    header("Location: ../logout.php");
                    exit;
                } else {
                    echo "Error al actualizar la contraseña.";
                }
            } else {
                echo "Las nuevas contraseñas no coinciden.";
            }
        } else {
            echo "La contraseña actual no es válida.";
        }
    }

    if (isset($_POST['EliminarCount'])) {
        $CiCelCount = $_POST['CiCatCount'];
        if ($CiCelCount == $celebrante->CiCel) {
            if ($celebrante->deactivateAccount()) {
                header("Location: ../logout.php");
                exit;
            } else {
                echo "Error al eliminar la cuenta.";
            }
        } else {
            echo "No se encuentra el catequisando.";
        }
    }
}
?>
