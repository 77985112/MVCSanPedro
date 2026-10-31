<?php
require_once __DIR__ . '/../Conexion/Conexion.php';

class ReporteActividadModelo
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Conexion())->getConnection();
    }

    /**
     * Obtiene todas las actividades de un año incluyendo las marcadas como Eliminado.
     * Incluye el nombre de quien publicó (personal o catequista).
     */
    public function obtenerActividadesPorAno($anio)
    {
        $stmt = $this->conn->prepare("
            SELECT
                a.idActividad,
                a.TituloActividad,
                a.FechaActividad,
                a.HoraAct,
                a.LugarAct,
                a.TipoActividad,
                a.EstActividad,
                a.Fecha_Public,
                a.DetalleActividad,
                COALESCE(
                    CONCAT(p.Nombre, ' ', p.Paterno, ' ', IFNULL(p.Materno, '')),
                    CONCAT(pe.Nombre, ' ', pe.ApPaterno, ' ', IFNULL(pe.ApMaterno, '')),
                    'Sistema'
                ) AS PublicadoPor
            FROM actividades a
            LEFT JOIN `personal` p ON a.CodPer = p.CodPer
            LEFT JOIN catequista c ON a.CiCat = c.CiCat
            LEFT JOIN `persona` pe ON c.CiCat = pe.CiPersona
            WHERE YEAR(a.FechaActividad) = ?
            ORDER BY a.FechaActividad ASC, a.HoraAct ASC
        ");
        $stmt->bind_param("i", $anio);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $actividades = $resultado->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $actividades;
    }

    /**
     * Devuelve un array con los años disponibles para reportar,
     * desde el año más antiguo hasta el año actual + 1.
     */
    public function obtenerAnosDisponibles()
    {
        $stmt = $this->conn->prepare("
            SELECT COALESCE(MIN(YEAR(FechaActividad)), YEAR(CURDATE())) AS minimo,
                   COALESCE(MAX(YEAR(FechaActividad)), YEAR(CURDATE())) AS maximo
            FROM actividades
        ");
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $minimo = (int) ($fila['minimo'] ?? date('Y'));
        $maximo = max((int) ($fila['maximo'] ?? date('Y')), date('Y') + 1);

        $anos = [];
        for ($anio = $minimo; $anio <= $maximo; $anio++) {
            $anos[] = $anio;
        }
        return $anos;
    }
}
