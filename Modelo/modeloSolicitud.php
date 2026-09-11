<?php
require_once '../Conexion/Conexion.php';

class ModeloInsSacramento
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Conexion())->getConnection();
    }

    public function validarClaveParroco($clave)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM Personal WHERE ClavePer = ?");
        $stmt->bind_param("s", $clave);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        return $count > 0;
    }


    public function RegistrarCertificado($dataCer)
    {
        $stmt = $this->conn->prepare("CALL RegistrarCertificado(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "issssssssssssss",
            $dataCer['CodIns'],
            $dataCer['NomParroquia'],
            $dataCer['LugParroquia'],
            $dataCer['NroLibro'],
            $dataCer['NroPag'],
            $dataCer['NroPart'],
            $dataCer['LugarNac'],
            $dataCer['FechaReal'],
            $dataCer['HoraReal'],
            $dataCer['NroEmision'],
            $dataCer['FechaEmision'],
            $dataCer['PresbiteroEm'],
            $dataCer['Observacion'],
            $dataCer['CelebrantePres'],
            $dataCer['EstadoCert']
        );
        return $stmt->execute();
    }

    /** ----------------------------------------------------------------------------------- */

    public function registrarBautizo($dataBau)
    {
        $stmt = $this->conn->prepare("CALL RegistrarInsSacramentoBautizo(?,?,?,?,?,?)");
        $stmt->bind_param(
            "sissss",
            $dataBau['CiPersonaCelebranteB'],
            $dataBau['CodPer'],
            $dataBau['CiPapa'],
            $dataBau['CiMama'],
            $dataBau['CiPadrino'],
            $dataBau['CiMadrina']
        );

        return $stmt->execute();
    }

    public function VerInsSacramentoBautizo($ciPersona)
    {
        $stmt = $this->conn->prepare("CALL VerInsSacramentoBautizo(?)");
        $stmt->bind_param("s", $ciPersona);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function VerCertificadoBautizo($codIns)
    {
        $stmt = $this->conn->prepare("CALL VerCertificadoBautizo(?)");
        $stmt->bind_param("i", $codIns);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result->fetch_assoc();
    }

    public function VerCertificadoExtra($CiNacimiento)
    {
        $stmt = $this->conn->prepare("CALL VerCertificadoExtN(?)");
        $stmt->bind_param("s", $CiNacimiento);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result->fetch_assoc();
    }

    /** ----------------------------------------------------------------------------------- */

    public function registrarMatrimonio($dataMat)
    {
        $stmt = $this->conn->prepare("CALL RegistrarInsSacramentoMatrimonio(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "ssissssssss",
            $dataMat['CiPersonaNovio'],
            $dataMat['CiPersonaNovia'],
            $dataMat['CodPer'],
            $dataMat['CiPadrino'],
            $dataMat['CiMadrina'],
            $dataMat['CiTestigoNovio'],
            $dataMat['CiTestigoNovia'],
            $dataMat['CiPaNovio'],
            $dataMat['CiMaNovio'],
            $dataMat['CiPaNovia'],
            $dataMat['CiMaNovia']
        );

        return $stmt->execute();
    }

    public function VerInsSacramentoMatrimonio($ciPersonaNovio)
    {
        $stmt = $this->conn->prepare("CALL VerInsSacramentoMatrimonio(?)");
        $stmt->bind_param("s", $ciPersonaNovio);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function VerCertificadoMatrimonio($codIns)
    {
        $stmt = $this->conn->prepare("CALL VerCertificadoMatrimonio(?)");
        $stmt->bind_param("i", $codIns);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result->fetch_assoc();
    }

    public function VerCertificadoExtraB($CiNovio,$Novio)
    {
        $stmt = $this->conn->prepare("CALL VerCertificadoExtB(?,?)");
        $stmt->bind_param("ss", $CiNovio,$Novio);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result->fetch_assoc();
    }

    public function VerCertificadoExtraN($CiNovia,$Novia)
    {
        $stmt = $this->conn->prepare("CALL VerCertificadoExtN(?)");
        $stmt->bind_param("s", $CiNovia);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result->fetch_assoc();
    }


    /** ----------------------------------------------------------------------------------- */

    public function registrarConfirmacion($dataConf)
    {
        $stmt = $this->conn->prepare("CALL RegistrarInsSacramentoConfirmacion(?,?,?,?)");
        $stmt->bind_param(
            "siss",
            $dataConf['CiPersonaCelebranteC'],
            $dataConf['CodPer'],
            $dataConf['CiPadrino'],
            $dataConf['CiMadrina'],
        );

        return $stmt->execute();
    }

    public function VerInsSacramentoConfirmacion($ciPersona)
    {
        $stmt = $this->conn->prepare("CALL VerInsSacramentoConfirmacion(?)");
        $stmt->bind_param("s", $ciPersona);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function VerCertificadoConfirmacion($codIns)
    {
        $stmt = $this->conn->prepare("CALL VerCertificadoConfirmacion(?)");
        $stmt->bind_param("i", $codIns);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result->fetch_assoc();
    }

    public function VerCertificadoExtraC($CiCelebrante,$Cel)
    {
        $stmt = $this->conn->prepare("CALL VerCertificadoExtB(?,?)");
        $stmt->bind_param("ss", $CiCelebrante,$Cel);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result->fetch_assoc();
    }
}
