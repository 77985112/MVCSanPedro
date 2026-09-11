<?php
require_once '../Modelo/modeloCatCoor.php';

class CatsController
{
    private $model;

    public function __construct()
    {
        $this->model = new CatequistaModelAsis();
    }

    public function index($ciCat)
    {
        $catequistas = $this->model->obtenerCatequistas($ciCat);
        return $catequistas;
    }

    public function CatequsitasSac($SacramentoCat)
    {
        $catsacramento = $this->model->CatequsitasSac($SacramentoCat);
        return $catsacramento;
    }

    public function CatequsitasSacCoor($SacramentoCat)
    {
        $catsacramento = $this->model->CatequsitasSacCoor($SacramentoCat);
        return $catsacramento;
    }
}
?>
