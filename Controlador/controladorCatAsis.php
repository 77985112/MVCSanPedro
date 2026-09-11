<?php
require_once '../Modelo/modeloCatAsis.php';

class CatequistaAsisControlador {
    private $model;

    public function __construct() {
        $this->model = new CatequistaModel();
    }

    public function obtenerCatequistas($sacramento) {
        return $this->model->obtenerCatequistas($sacramento);
    }

    public function obtenerAsistencia($ciCat, $idGrupo, $fecha) {
        return $this->model->obtenerAsistencia($ciCat, $idGrupo, $fecha);
    }

    public function registrarAsistencia($catequistas, $fechaAsis) {
        foreach ($catequistas as $catequista) {
            $this->model->registrarAsistencia(
                $catequista['Gestion'],
                $catequista['ciCat'],
                $catequista['idGrupo'],
                $catequista['detalleAsis'],
                $fechaAsis
            );
        }
    }
    
    public function ActualizarAsistencia($catequistas, $fechaAsis) {
        foreach ($catequistas as $catequista) {
            $this->model->ActualizarAsistencia(
                $catequista['Gestion'],
                $catequista['ciCat'],
                $catequista['idGrupo'],
                $catequista['detalleAsis'],
                $fechaAsis
            );
        }
    }
}
?>
