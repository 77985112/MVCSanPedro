<?php
require_once '../Modelo/modeloCoordinadorAdmin.php';

class ControladorCoordinadorAdmin {
    private $modelo;

    public function __construct() {
        $this->modelo = new ModeloCoordinadorAdmin();
    }

    public function obtenerCoordinadores($gestion = null) {
        return $this->modelo->obtenerCoordinadores($gestion);
    }

    public function buscarPersona($ci) {
        return $this->modelo->buscarPersona($ci);
    }

    public function verificarCoordinadorExistente($sacramento, $excluirCi = '', $gestion = null) {
        return $this->modelo->verificarCoordinadorExistente($sacramento, $excluirCi, $gestion);
    }

    public function verificarUsuarioDisponible($usuario, $excluirCi = '') {
        return $this->modelo->verificarUsuarioDisponible($usuario, $excluirCi);
    }

    public function actualizarCargo($ci, $sacramento, $idGrupo, $usuario, $clave) {
        return $this->modelo->actualizarCargo($ci, $sacramento, $idGrupo, $usuario, $clave);
    }

    public function cerrarAsignacion($ci, $gestion) {
        return $this->modelo->cerrarAsignacion($ci, $gestion);
    }

    public function registrarCoordinador($ci, $sacramento, $idGrupo, $usuario, $clave, $gestion = null) {
        return $this->modelo->registrarCoordinador($ci, $sacramento, $idGrupo, $usuario, $clave, $gestion);
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

    public function actualizarCoordinador($ci, $usuario, $clave = '') {
        $hash = $clave !== '' ? password_hash($clave, PASSWORD_DEFAULT) : '';
        return $this->modelo->actualizarCoordinador($ci, $usuario, $hash);
    }

    public function restablecerClaveCoordinador($ci) {
        $registro = $this->modelo->buscarCoordinador($ci);
        if (!$registro) {
            return false;
        }
        $nueva = 'Tmp' . strtoupper(bin2hex(random_bytes(4)));
        if (!$this->modelo->actualizarClaveCoordinador($ci, password_hash($nueva, PASSWORD_DEFAULT), 1)) {
            return false;
        }
        return ['clave' => $nueva, 'registro' => $registro];
    }

    public function marcarCambioClaveCoordinador($ci) {
        return $this->modelo->marcarCambioClave($ci);
    }

    public function buscarPersonasPorNombre($nombre) {
        return $this->modelo->buscarPersonasPorNombre($nombre);
    }

    public function buscarPersonasFiltradas($texto = '', $sacramento = '', $zona = '', $estadoCivil = '') {
        return $this->modelo->buscarPersonasFiltradas($texto, $sacramento, $zona, $estadoCivil);
    }

    public function obtenerZonasSector() {
        return $this->modelo->obtenerZonasSector();
    }

    public function obtenerSacramentosSolicitados() {
        return $this->modelo->obtenerSacramentosSolicitados();
    }

    public function obtenerEstadosCiviles() {
        return $this->modelo->obtenerEstadosCiviles();
    }

    public function registrarPersona($ci, $nombre, $apPaterno, $apMaterno, $sexo, $fechaNac, $direccion, $zonaSector, $contacto, $telefonoAlternativo, $correo, $estadoCivil, $sacramentoSolicita, $observaciones) {
        return $this->modelo->registrarPersona($ci, $nombre, $apPaterno, $apMaterno, $sexo, $fechaNac, $direccion, $zonaSector, $contacto, $telefonoAlternativo, $correo, $estadoCivil, $sacramentoSolicita, $observaciones);
    }
}
?>
