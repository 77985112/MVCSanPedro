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

    public function ActualizarNroEmision($codIns, $nroEmision)
    {
        return $this->model->ActualizarNroEmision($codIns, $nroEmision);
    }

    public function ActualizarFechaEmision($codIns, $fecha = null)
    {
        return $this->model->ActualizarFechaEmision($codIns, $fecha);
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

    public function RegistrarBautizoCompleto($datos)
    {
        return $this->model->registrarBautizoCompleto($datos);
    }

    public function VerInsSacramentoBautizo($busqueda = null)
    {
        return $this->model->VerInsSacramentoBautizo($busqueda);
    }

    public function VerCertificadoBautizo($codIns)
    {
        return $this->model->VerCertificadoBautizo($codIns);
    }

    public function ActualizarDatosBautizo($codIns, $datos)
    {
        return $this->model->ActualizarDatosBautizo($codIns, $datos);
    }

    public function VerCertificadoExtra($CiNacimiento, $codIns = null)
    {
        return $this->model->VerCertificadoExtra($CiNacimiento, $codIns);
    }

    /** ----------------------------------------------------------------------------------- */

    public function RegistrarMatrimonio($dataMat)
    {
        return $this->model->registrarMatrimonio($dataMat);
    }

    public function RegistrarMatrimonioCompleto($datos)
    {
        return $this->model->registrarMatrimonioCompleto($datos);
    }

    public function VerInsSacramentoMatrimonio($busqueda)
    {
        return $this->model->VerInsSacramentoMatrimonio($busqueda);
    }

    public function RegistrarCertificadoMatrimonio($datos)
    {
        return $this->model->RegistrarCertificadoMatrimonio($datos);
    }

    public function ActualizarDatosMatrimonio($codIns, $datos)
    {
        return $this->model->ActualizarDatosMatrimonio($codIns, $datos);
    }

    public function VerCertificadoMatrimonio($codIns)
    {
        return $this->model->VerCertificadoMatrimonio($codIns);
    }

    public function VerCertificadoExtraB($CiNovio, $Novio, $codIns = null)
    {
        return $this->model->VerCertificadoExtraB($CiNovio, $Novio, $codIns);
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

    public function RegistrarConfirmacionCompleto($datos)
    {
        return $this->model->registrarConfirmacionCompleto($datos);
    }

    public function VerInsSacramentoConfirmacion($busqueda = null)
    {
        return $this->model->VerInsSacramentoConfirmacion($busqueda);
    }

    public function VerCertificadoConfirmacion($codIns)
    {
        return $this->model->VerCertificadoConfirmacion($codIns);
    }

    public function ActualizarDatosConfirmacion($codIns, $datos)
    {
        return $this->model->ActualizarDatosConfirmacion($codIns, $datos);
    }

    public function BuscarBautizoConfirmante($ciPersona)
    {
        return $this->model->buscarBautizoConfirmante($ciPersona);
    }

    public function VerCertificadoExtraC($CiCelebrante, $Cel, $codIns = null)
    {
        return $this->model->VerCertificadoExtraC($CiCelebrante, $Cel, $codIns);
    }
}
