<?php
require_once '../Modelo/modeloPersona.php';

class ControladorPersona {
    private $modelo;
    private $ciPersonaRegistrada = null;

    public function __construct() {
        $this->modelo = new Persona();
    }

    public function crearPersona($data) {
        $this->ciPersonaRegistrada = null;
        $ciPersona = trim($data['CiPersona'] ?? '');
        
        $oficialia = trim($data['Oficialia'] ?? '');
        $libro = trim($data['Libro'] ?? '');
        $partida = trim($data['Partida'] ?? '');
        $folio = trim($data['Folio'] ?? '');
        
        if (empty($ciPersona)) {
            if (!empty($partida)) {
                $ciPersona = $partida;
            } else {
                $ciPersona = 'SINCI-' . date('ymd') . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
            }
        }
        
        $certificado = '';
        if (!empty($oficialia) || !empty($libro) || !empty($partida) || !empty($folio)) {
            $certificado = "Ofic.{$oficialia}-Lib.{$libro}-Part.{$partida}-Fol.{$folio}";
        }
        
        $estado = $data['Estado_per'] ?? '';
        
        $resultado = $this->modelo->crear($ciPersona, $data['Nombre'], $data['ApPaterno'], $data['ApMaterno'], $data['Sexo'], $data['FechaNac'], $data['Direccion'], $data['Contacto'], $estado, $certificado);
        if ($resultado) $this->ciPersonaRegistrada = $ciPersona;
        return $resultado;
    }

    public function obtenerCiPersonaRegistrada() {
        return $this->ciPersonaRegistrada;
    }

    public function buscarPersona($ciPersona) {
        return $this->modelo->buscar($ciPersona);
    }

    public function actualizarPersona($data) {
        return $this->modelo->actualizar($data['CiPersona'], $data['Nombre'], $data['ApPaterno'], $data['ApMaterno'], $data['Sexo'], $data['FechaNac'], $data['Direccion'], $data['Contacto'], $data['Estado_per2']);
    }
}
?>
