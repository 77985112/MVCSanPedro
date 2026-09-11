<?php
require_once '../Modelo/modeloReportesAll.php';

class controladorReporteGeneral {
    private $modelo;

    public function __construct($conexion) {
        $this->modelo = new modeloReporteGeneral($conexion);
    }

    public function mostrarReporteEmitidos() {
        return $this->modelo->obtenerCertificadosEmitidos();
    }

    public function obtenerReservas() {
        return $this->modelo->obtenerReservas();
    }
    public function obtenerReservasMes($anio, $mes) {
        return $this->modelo->obtenerReservasMes($anio, $mes);
    }
    public function obtenerReservasDia($anio, $mes, $dia) {
        return $this->modelo->obtenerReservasDia($anio, $mes, $dia);
    }

    public function obtenerCatesActivos($data) {
        return $this->modelo->obtenerCatesActivos($data);
    }

    public function obtenerCatesInactivos($data) {
        return $this->modelo->obtenerCatesInactivos($data);
    }

    public function obtenerCeleApp($data1, $data2) {
        return $this->modelo->obtenerCeleApp($data1, $data2);
    }

    public function obtenerCeleAppIn($data1, $data2) {
        return $this->modelo->obtenerCeleAppIn($data1, $data2);
    }

    public function obtenerCatApp($data3, $data4) {
        return $this->modelo->obtenerCatApp($data3, $data4);
    }

    public function obtenerNotas($data0) {
        return $this->modelo->obtenerNotas($data0);
    }

    public function obtenerAsistencias($data1) {
        return $this->modelo->obtenerAsistencias($data1);
    }
    public function obtenerReuniones($data3) {
        return $this->modelo->obtenerReuniones($data3);
    }

}
?>
