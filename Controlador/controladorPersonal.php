<?php
require_once '../Modelo/modeloPersonal.php';

class ControladorPersonal {
    private $modelo;

    public function __construct() {
        $this->modelo = new Personal();
    }

    public function registrarNuevoPersonal($data) {
        return $this->modelo->registrarNuevoPersonal($data);
    }
    public function buscarPersonalPorCi($ci) {
        return $this->modelo->buscarPersonalPorCi($ci);
    }

    public function modificarPersonalPorCi($data) {
        return $this->modelo->modificarPersonalPorCi($data);
    }

    public function PersonalDemas($ciPersonal) {
        return $this->modelo->PersonalDemas($ciPersonal);
    }
}
?>