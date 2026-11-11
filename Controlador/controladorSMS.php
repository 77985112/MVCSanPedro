<?php
require_once '../Modelo/modeloSMS.php';

class ControladorMensaje {

    public function registrarMensaje() {
        if (isset($_POST['submit'])) {
            $nombre = $_POST['nombre'];
            $numCorr = $_POST['numcorr'];
            $concepto = $_POST['concepto'];
            $detalle = $_POST['detalle'];

            $mensaje = new Mensaje();
            $resultado = $mensaje->insertarMensaje($nombre, $numCorr, $concepto, $detalle);

            return $resultado ? "Mensaje registrado exitosamente" : "Error al registrar el mensaje";
        }
        return null;
    }

    public function listarMensajes($estado = null) {
        $mensaje = new Mensaje();
        return $mensaje->obtenerMensajes($estado);
    }

    public function contarMensajes($estado = null) {
        $mensaje = new Mensaje();
        return $mensaje->contarMensajes($estado);
    }

    public function eliminarMensaje($id) {
        $mensaje = new Mensaje();
        return $mensaje->eliminarMensaje($id);
    }

    public function leerMensaje($id) {
        $mensaje = new Mensaje();
        $mensajeData = $mensaje->obtenerMensajePorId($id);
        $mensaje->marcarComoLeido($id);
        return $mensajeData;
    }
}
?>