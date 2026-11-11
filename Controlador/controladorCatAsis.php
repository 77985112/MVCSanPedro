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
        if (!is_array($catequistas)) {
            return;
        }

        foreach ($catequistas as $catequista) {
            if (!is_array($catequista)
                || !isset($catequista['Gestion'], $catequista['ciCat'], $catequista['idGrupo'])) {
                continue;
            }

            $detalleAsis = $catequista['detalleAsis'] ?? 'Falta';
            if (!in_array($detalleAsis, ['Presente', 'Falta', 'Permiso'], true)) {
                $detalleAsis = 'Falta';
            }

            $this->model->registrarAsistencia(
                $catequista['Gestion'],
                $catequista['ciCat'],
                $catequista['idGrupo'],
                $detalleAsis,
                $fechaAsis
            );
        }
    }
    
    public function ActualizarAsistencia($catequistas, $fechaAsis) {
        if (!is_array($catequistas)) {
            return;
        }

        foreach ($catequistas as $catequista) {
            if (!is_array($catequista)
                || !isset($catequista['Gestion'], $catequista['ciCat'], $catequista['idGrupo'])) {
                continue;
            }

            $detalleAsis = $catequista['detalleAsis'] ?? 'Falta';
            if (!in_array($detalleAsis, ['Presente', 'Falta', 'Permiso'], true)) {
                $detalleAsis = 'Falta';
            }

            $this->model->ActualizarAsistencia(
                $catequista['Gestion'],
                $catequista['ciCat'],
                $catequista['idGrupo'],
                $detalleAsis,
                $fechaAsis
            );
        }
    }

    public function eliminarAsistencia($sacramento, $fechaAsis) {
        return $this->model->eliminarAsistenciaPorFecha($sacramento, $fechaAsis);
    }
}
?>
