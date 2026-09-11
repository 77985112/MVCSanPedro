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
        if ($sacramento === 'Primera Comunión') {
            $inicio = 2;
            $fin = 19;
        } elseif ($sacramento === 'Confirmación') {
            $inicio = 23;
            $fin = 40;
        } else {
            echo "Sacramento no válido.";
            exit;
        }

        return $this->grupoModel->getDatosPorSacramento($inicio, $fin);
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

        $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/MVCSanPedro/assets/images/Santo/";

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
