<?php
require_once '../Modelo/modeloCoordinadorAdmin.php';

class ControladorCoordinadorAdmin {
    private $modelo;

    public function __construct() {
        $this->modelo = new ModeloCoordinadorAdmin();
    }

    public function obtenerCoordinadores() {
        return $this->modelo->obtenerCoordinadores();
    }

    public function buscarPersona($ci) {
        return $this->modelo->buscarPersona($ci);
    }

    public function verificarCoordinadorExistente($sacramento) {
        return $this->modelo->verificarCoordinadorExistente($sacramento);
    }

    public function registrarCoordinador($ci, $sacramento, $idGrupo, $usuario, $clave) {
        return $this->modelo->registrarCoordinador($ci, $sacramento, $idGrupo, $usuario, $clave);
    }

    public function obtenerTodosLosGrupos() {
        return $this->modelo->buscarTodosLosGrupos();
    }

    public function buscarCoordinador($ci) {
        return $this->modelo->buscarCoordinador($ci);
    }

    public function bloquearCoordinador($ci) {
        return $this->modelo->cambiarEstadoCoordinador($ci, 'Inactivo');
    }

    public function activarCoordinador($ci) {
        return $this->modelo->cambiarEstadoCoordinador($ci, 'Activo');
    }

    public function buscarPersonasPorNombre($nombre) {
        return $this->modelo->buscarPersonasPorNombre($nombre);
    }

    public function registrarPersona($ci, $nombre, $apPaterno, $apMaterno, $sexo, $fechaNac, $direccion, $contacto, $estadoCivil) {
        return $this->modelo->registrarPersona($ci, $nombre, $apPaterno, $apMaterno, $sexo, $fechaNac, $direccion, $contacto, $estadoCivil);
    }
}
?>
