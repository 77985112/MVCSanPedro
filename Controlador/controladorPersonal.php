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
        if ($data['ClavePer'] === '') {
            $data['ClavePer'] = $this->generarClaveTemporal();
        }
        if ($data['UsuarioPer'] === '' || $data['ClavePer'] === '') {
            return false;
        }
        $data = $this->resolverCargoOtro($data, 'CodCar');
        if ($data === false) {
            return false;
        }
        // La contraseña de un solo uso se guarda cifrada.
        $data['ClavePer'] = password_hash($data['ClavePer'], PASSWORD_DEFAULT);
        return $this->modelo->registrarNuevoPersonal($data);
    }

    /** Genera una contraseña de un solo uso legible para imprimir en el ticket. */
    public function generarClaveTemporal() {
        return 'Tmp' . strtoupper(bin2hex(random_bytes(4)));
    }

    /** Genera y guarda una nueva clave de un solo uso para un personal existente. */
    public function restablecerClave($ciPersonal) {
        $registro = $this->modelo->buscarPersonalPorCi($ciPersonal);
        if (!$registro) {
            return false;
        }
        $nueva = $this->generarClaveTemporal();
        if (!$this->modelo->restablecerClave($ciPersonal, password_hash($nueva, PASSWORD_DEFAULT))) {
            return false;
        }
        return ['clave' => $nueva, 'registro' => $registro];
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