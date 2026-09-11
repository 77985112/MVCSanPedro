<?php
require_once '../Modelo/modeloLogin.php';

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
            
            if (in_array($personalData['DescripCar'], ['Párroco', 'Secretario (a) Parroquial', 'Sacerdote', 'Personal'])) {
                header("Location: ../VistaPersonal/VistaPersonal.php");
            } else {
                header("Location: ../logout.php");
            }
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
        header("Location: ../logout.php");
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