<?php
require_once '../Modelo/modeloSolicitud.php';

class ControladorInsSacramento
{
    private $model;

    public function __construct()
    {
        $this->model = new ModeloInsSacramento();
    }

    public function RegistrarCertificado($data)
    {
        return $this->model->RegistrarCertificado($data);
    }

    public function validarClaveParroco($dato)
    {
        return $this->model->validarClaveParroco($dato);
    }

    /** ----------------------------------------------------------------------------------- */

    public function RegistrarBautizo($dataBau)
    {
        return $this->model->registrarBautizo($dataBau);
    }

    public function VerInsSacramentoBautizo($ciPersona = null)
    {
        return $this->model->VerInsSacramentoBautizo($ciPersona);
    }

    public function VerCertificadoBautizo($codIns)
    {
        return $this->model->VerCertificadoBautizo($codIns);
    }

    public function VerCertificadoExtra($CiNacimiento)
    {
        return $this->model->VerCertificadoExtra($CiNacimiento);
    }

    /** ----------------------------------------------------------------------------------- */

    public function RegistrarMatrimonio($dataMat)
    {
        return $this->model->registrarMatrimonio($dataMat);
    }

    public function VerInsSacramentoMatrimonio($ciPersonaNovio)
    {
        return $this->model->VerInsSacramentoMatrimonio($ciPersonaNovio);
    }

    public function VerCertificadoMatrimonio($codIns)
    {
        return $this->model->VerCertificadoMatrimonio($codIns);
    }

    public function VerCertificadoExtraB($CiNovio,$Novio)
    {
        return $this->model->VerCertificadoExtraB($CiNovio,$Novio);
    }

    public function VerCertificadoExtraN($CiNovia,$Novia)
    {
        return $this->model->VerCertificadoExtraN($CiNovia,$Novia);
    }
    /** ----------------------------------------------------------------------------------- */

    public function RegistrarConfirmacion($dataConf)
    {
        return $this->model->registrarConfirmacion($dataConf);
    }

    public function VerInsSacramentoConfirmacion($ciPersona = null)
    {
        return $this->model->VerInsSacramentoConfirmacion($ciPersona);
    }

    public function VerCertificadoConfirmacion($codIns)
    {
        return $this->model->VerCertificadoConfirmacion($codIns);
    }

    public function VerCertificadoExtraC($CiCelebrante,$Cel)
    {
        return $this->model->VerCertificadoExtraC($CiCelebrante,$Cel);
    }
}
