<?php
require_once '../Conexion/Conexion.php';

class ModeloSacramento
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Conexion())->getConnection();
    }

    public function getSacramentoComunion()
    {
        $stmt = $this->conn->prepare("SELECT * FROM fechassacramento WHERE Sacramento = 'Primera Comunión'");

        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getSacramentoConfirmacion()
    {
        $stmt = $this->conn->prepare("SELECT * FROM fechassacramento WHERE Sacramento = 'Confirmación'");

        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function updateSacramento($idSacramento, $fechaIni, $fechaFin, $ciCatCat, $estadito)
    {
        $stmt = $this->conn->prepare("UPDATE fechassacramento SET Fecha_Ini = ?, Fecha_Fin = ?, CiCatCat = ?, EstadoIns = ? WHERE idSacramento = ?");
        $stmt->bind_param('ssssi', $fechaIni, $fechaFin, $ciCatCat, $estadito, $idSacramento);
        return $stmt->execute();
    }

    public function getDatosSacramentoPorId($idSacramento)
    {
        $stmt = $this->conn->prepare("SELECT EstadoIns as estadito, Fecha_Ini, Fecha_Fin, TIME(Fecha_Ini) as Hora_Ini, TIME(Fecha_Fin) as Hora_Fin FROM fechassacramento WHERE idSacramento = ?");
        $stmt->bind_param('i', $idSacramento);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getAllSacramentos()
    {
        $stmt = $this->conn->prepare("SELECT * FROM fechassacramento");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>