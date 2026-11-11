<?php
require_once '../Modelo/modeloPermisosCatequista.php';

class ControladorPermisosCatequista
{
    private $modelo;

    public function __construct()
    {
        $this->modelo = new ModeloPermisosCatequista();
    }

    public function obtenerCompaneros($idGrupo, $gestion, $ciTitular)
    {
        return $this->modelo->obtenerCompaneros($idGrupo, $gestion, $ciTitular);
    }

    public function guardar($ciCat, $asistencia, $puntaje, $registrar, $idGrupo, $gestion, $ciTitular)
    {
        if ($ciCat === '' || $ciTitular === ''
            || !$this->modelo->verificarCompanero($ciCat, $idGrupo, $gestion, $ciTitular)) {
            return false;
        }
        return $this->modelo->guardarPermisos($ciCat, $asistencia, $puntaje, $registrar, $ciTitular);
    }
}
?>
