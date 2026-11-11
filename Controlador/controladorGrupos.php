<?php
require_once '../Modelo/modeloGrupos.php';

class GrupoController
{
    private $grupoModel;

    public function __construct()
    {
        $this->grupoModel = new GrupoModel();
    }


    public function DatosGrupito($idGrIn)
    {
        return $this->grupoModel->DatosGrupito($idGrIn);
    }

    public function mostrarDatosPorSacramento($sacramento)
    {
        if ($sacramento !== 'Primera Comunión' && $sacramento !== 'Confirmación') {
            echo "Sacramento no válido.";
            exit;
        }

        return $this->grupoModel->getDatosPorSacramento($sacramento);
    }

    /** Agrega un grupo nuevo al final del sacramento. */
    public function crearGrupo($sacramento)
    {
        if ($sacramento !== 'Primera Comunión' && $sacramento !== 'Confirmación') {
            return ['ok' => false, 'mensaje' => 'Sacramento no válido.'];
        }

        $nombre = $this->grupoModel->siguienteNombreGrupo($sacramento);
        if ($this->grupoModel->crearGrupo($nombre, $sacramento)) {
            return ['ok' => true, 'mensaje' => 'Se creó el Grupo ' . $nombre . ' de ' . $sacramento . '.'];
        }

        return ['ok' => false, 'mensaje' => 'No se pudo crear el grupo.'];
    }

    /** Quita un grupo vacio del sacramento indicado. */
    public function eliminarGrupo($idGrupo, $sacramento)
    {
        if ($sacramento !== 'Primera Comunión' && $sacramento !== 'Confirmación') {
            return ['ok' => false, 'mensaje' => 'Sacramento no válido.'];
        }

        $grupo = $this->grupoModel->obtenerGrupoPorId($idGrupo);
        if (!$grupo || trim((string) $grupo['SacramentoGrupo']) !== $sacramento) {
            return ['ok' => false, 'mensaje' => 'Ese grupo no pertenece a tu sacramento.'];
        }

        if ((int) $grupo['NombreGrupo'] <= 17) {
            return [
                'ok' => false,
                'mensaje' => 'Los grupos 01 a 17 son fijos y no se pueden quitar. Solo se pueden quitar los grupos nuevos (18 en adelante).'
            ];
        }

        $datos = $this->grupoModel->contarDatosGrupo($idGrupo);
        if ((int) $datos['inscripciones'] > 0 || (int) $datos['asignaciones'] > 0) {
            return [
                'ok' => false,
                'mensaje' => 'No se puede quitar el Grupo ' . $grupo['NombreGrupo']
                    . ': todavía tiene catequizandos o catequistas asignados.'
            ];
        }

        if ($this->grupoModel->contarGruposPorSacramento($sacramento) <= 1) {
            return ['ok' => false, 'mensaje' => 'Debe quedar al menos un grupo.'];
        }

        if ($this->grupoModel->eliminarGrupo($idGrupo)) {
            return ['ok' => true, 'mensaje' => 'Se quitó el Grupo ' . $grupo['NombreGrupo'] . '.'];
        }

        return ['ok' => false, 'mensaje' => 'No se pudo quitar el grupo.'];
    }

    public function mostrarGrupoCoor($idGrupo)
    {
        $grupo = $this->grupoModel->obtenerGrupoPorId($idGrupo);
        $catequistas = $this->grupoModel->obtenerCatequistas($idGrupo);
        $celebrantes = $this->grupoModel->obtenerCelebrantes($idGrupo);

        return [
            'grupo' => $grupo,
            'catequistas' => $catequistas,
            'celebrantes' => $celebrantes
        ];
    }

    public function mostrarGrupoCat($idGrupo)
    {
        $grupo = $this->grupoModel->obtenerGrupoPorId($idGrupo);
        $celebrantes = $this->grupoModel->obtenerCelebrantes($idGrupo);

        return [
            'grupo' => $grupo,
            'celebrantes' => $celebrantes
        ];
    }

    public function actualizarGrupo($idGrupo, $nombre, $color, $santo, $imagen, $frase)
    {

        $target_dir = __DIR__ . '/../assets/images/Santo/';

        $grupo = $this->grupoModel->obtenerGrupoPorId($idGrupo);
        $imagenAnterior = $grupo['Imagen_Grupo'];

        $imagen = $imagenAnterior;

        if (isset($_FILES['Imagen_Grupo']) && $_FILES['Imagen_Grupo']['error'] === UPLOAD_ERR_OK) {
            $archivoTmp = $_FILES['Imagen_Grupo']['tmp_name'];

            $nombreArchivo = $_FILES['Imagen_Grupo']['name'];
            $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));

            $nuevoNombreImagen = "SANTO_" . time() . "." . $extension;
            $rutaCompleta = $target_dir . $nuevoNombreImagen;
            if (move_uploaded_file($archivoTmp, $rutaCompleta)) {
                $imagen = $nuevoNombreImagen;
                if (!empty($imagenAnterior) && $imagenAnterior !== 'SanPedro.png') {
                    $rutaImagenAnterior = $target_dir . $imagenAnterior;
                    if (file_exists($rutaImagenAnterior)) {
                        unlink($rutaImagenAnterior);
                    }
                }
            } else {
                throw new Exception("Error al mover el archivo a la carpeta de destino.");
            }
        }
        return $this->grupoModel->actualizarGrupo($idGrupo, $nombre, $color, $santo, $imagen, $frase);
    }
}
