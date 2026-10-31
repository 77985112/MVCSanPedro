<?php
require_once '../Modelo/modeloLogin.php';
require_once __DIR__ . '/../Modelo/modeloAcceso.php';

class ControladorLogin {
    private $modeloLogin;

    public function __construct() {
        $this->modeloLogin = new ModeloLogin();
    }

    public function login($username, $password) {
        session_start();
        $personalData = $this->modeloLogin->getPersonalData($username, $password);
        if ($personalData) {
            $_SESSION['usuario'] = $personalData;

            // Permisos de módulos para esta persona (se aplican al menú y a
            // la navegación directa por URL).
            $modeloAcceso = new ModeloAcceso();
            $_SESSION['permisos'] = $modeloAcceso->obtener((int) $personalData['CodPer'], $personalData['DescripCar']);

            // Todo el personal activo con credenciales válidas puede ingresar,
            // sin depender de un listado fijo de cargos.
            header("Location: ../VistaPersonal/VistaDashboard.php");
            exit();
        }

        $catequistaData = $this->modeloLogin->getCatequistaData($username, $password);
        if ($catequistaData) {

            $_SESSION['CiPersona'] = $catequistaData;
            if ($catequistaData['RolCat'] == 'Catequista'|| $catequistaData['RolCat'] == 'Titular') {
                header("Location: ../VistaCatequista/VistaCatequista.php");
            } else if ($catequistaData['RolCat'] == 'Coordinador') {
                header("Location: ../VistaCoordinador/VistaCoordinador.php");
            }else {
                header("Location: ../logout.php");
            }
            exit();
        }

        $celebranteData = $this->modeloLogin->getCelebranteData($username, $password);
        if ($celebranteData) {
            $_SESSION['CiPersona'] = $celebranteData;
            if ($celebranteData['Sacramento'] == 'Confirmación' || $celebranteData['Sacramento'] == 'Primera Comunión') {
                header("Location: ../VistaCelebrante/VistaCelebrante.php");
            } else {
                header("Location: ../logout.php");
            }
            exit();
        }
        // Inicio fallido: se vuelve al formulario indicando qué campo corregir
        // (usuario desconocido o contraseña equivocada).
        $motivo = $this->modeloLogin->existeUsuario($username) ? 'contrasena' : 'usuario';
        $destino = $_SERVER['HTTP_REFERER'] ?? '';
        if (!is_string($destino) || $destino === '' || preg_match('/[\r\n]/', $destino)) {
            $destino = '../index.php';
        }
        $separador = strpos($destino, '?') === false ? '?' : '&';
        header('Location: ' . $destino . $separador . 'error_login=' . $motivo);
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $controlador = new ControladorLogin();
    $controlador->login($username, $password);
}
?>
