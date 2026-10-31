<?php
require_once '../Modelo/modeloPersonal.php';

class ControladorPersonal {
    private $modelo;

    public function __construct() {
        $this->modelo = new Personal();
    }

    public function registrarNuevoPersonal($data) {
        $data['UsuarioPer'] = trim((string) ($data['UsuarioPer'] ?? ''));
        $data['ClavePer'] = trim((string) ($data['ClavePer'] ?? ''));
        if ($data['UsuarioPer'] === '' || $data['ClavePer'] === '') {
            return false;
        }
        $data = $this->resolverCargoOtro($data, 'CodCar');
        if ($data === false) {
            return false;
        }
        return $this->modelo->registrarNuevoPersonal($data);
    }
    public function buscarPersonalPorCi($ci) {
        return $this->modelo->buscarPersonalPorCi($ci);
    }

    public function buscarPersonalPorTexto($texto) {
        return $this->modelo->buscarPersonalPorTexto($texto);
    }

    public function contarPersonal() {
        return $this->modelo->contarPersonal();
    }

    public function listarPersonal($inicio, $limite) {
        return $this->modelo->listarPersonal($inicio, $limite);
    }

    public function modificarPersonalPorCi($data) {
        $data = $this->resolverCargoOtro($data, 'CodCar_2');
        if ($data === false) {
            return false;
        }
        return $this->modelo->modificarPersonalPorCi($data);
    }

    // Si el cargo elegido es "Otro", registra (o reutiliza) el nombre escrito por el usuario.
    private function resolverCargoOtro($data, $campo) {
        if (isset($data[$campo]) && $data[$campo] === 'otro') {
            $nombre = trim($data['CargoOtro'] ?? '');
            if ($nombre === '') {
                return false;
            }
            $codigo = $this->modelo->obtenerOCrearCargo($nombre);
            if (!$codigo) {
                return false;
            }
            $data[$campo] = $codigo;
        }
        return $data;
    }

    public function PersonalDemas($ciPersonal) {
        return $this->modelo->PersonalDemas($ciPersonal);
    }
}
?>