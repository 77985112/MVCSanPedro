<?php
require_once __DIR__ . '/../Conexion/Conexion.php';

class ModeloDashboard
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Conexion())->getConnection();
    }

    private function contar($sql)
    {
        $resultado = $this->conn->query($sql);
        if (!$resultado) {
            return 0;
        }

        $fila = $resultado->fetch_assoc();
        $resultado->free();
        return (int) ($fila['total'] ?? 0);
    }

    public function contarPersonalActivo()
    {
        return $this->contar("SELECT COUNT(*) AS total FROM `personal` WHERE Estado = 'Activo'");
    }

    public function contarPersonalTotal()
    {
        return $this->contar("SELECT COUNT(*) AS total FROM `personal`");
    }

    public function contarPersonas()
    {
        return $this->contar("SELECT COUNT(*) AS total FROM `persona`");
    }

    public function contarReservas()
    {
        return $this->contar("SELECT COUNT(*) AS total FROM `reserva`");
    }

    public function contarCertificados()
    {
        return $this->contar("SELECT COUNT(*) AS total FROM certificado");
    }

    public function contarActividades()
    {
        return $this->contar("SELECT COUNT(*) AS total FROM actividades WHERE EstActividad <> 'Eliminado'");
    }

    public function obtenerProximasReservas($limite = 4)
    {
        $limite = max(1, min(20, (int) $limite));
        $stmt = $this->conn->prepare(
            "SELECT CodRes, FechaReal, HoraReal, TipoCel, Realizacion, Quien, EstadoRes
             FROM `reserva`
             WHERE FechaReal >= CURDATE() AND EstadoRes = 'Reservado'
             ORDER BY FechaReal ASC, HoraReal ASC
             LIMIT ?"
        );
        $stmt->bind_param('i', $limite);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $reservas = $resultado->fetch_all(MYSQLI_ASSOC);
        $resultado->free();
        $stmt->close();
        return $reservas;
    }

    public function obtenerActividadesRecientes($limite = 3)
    {
        $limite = max(1, min(20, (int) $limite));
        $stmt = $this->conn->prepare(
            "SELECT idActividad, TituloActividad, FechaActividad, HoraAct, LugarAct,
                    ImagenActividad, EstActividad
             FROM actividades
             WHERE EstActividad <> 'Eliminado'
             ORDER BY FechaActividad DESC, HoraAct DESC
             LIMIT ?"
        );
        $stmt->bind_param('i', $limite);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $actividades = $resultado->fetch_all(MYSQLI_ASSOC);
        $resultado->free();
        $stmt->close();
        return $actividades;
    }

    public function obtenerEstadisticasSacramentos($anioInicio = 2026)
    {
        $anioInicio = max(2000, (int) $anioInicio);
        $sql = "SELECT YEAR(c.FechaEmision) AS anio, ts.DescripSac AS sacramento, COUNT(*) AS total
                FROM certificado c
                INNER JOIN inssacramento ins ON ins.CodIns = c.CodIns
                INNER JOIN tiposacramento ts ON ts.CodSac = ins.CodSac
                WHERE c.EstadoCert = 'Activo'
                  AND YEAR(c.FechaEmision) >= ?
                  AND ts.DescripSac IN ('Bautizo', 'Matrimonio', 'Confirmación')
                GROUP BY YEAR(c.FechaEmision), ts.DescripSac
                ORDER BY anio ASC, ts.DescripSac ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $anioInicio);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $filas = $resultado->fetch_all(MYSQLI_ASSOC);
        $resultado->free();
        $stmt->close();
        return $filas;
    }

    public function obtenerEstadisticasMisas($anioInicio = 2026)
    {
        $anioInicio = max(2000, (int) $anioInicio);
        $sql = "SELECT YEAR(r.FechaReal) AS anio, COUNT(*) AS total
                FROM `reserva` r
                WHERE r.TipoCel = 'Misa'
                  AND r.EstadoRes = 'Reservado'
                  AND YEAR(r.FechaReal) >= ?
                GROUP BY YEAR(r.FechaReal)
                ORDER BY anio ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $anioInicio);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $filas = $resultado->fetch_all(MYSQLI_ASSOC);
        $resultado->free();
        $stmt->close();
        return $filas;
    }

    public function contarCatequistasActivos()
    {
        return $this->contar("SELECT COUNT(*) AS total FROM catequista WHERE EstadoCat = 'Activo'");
    }

    public function contarGrupos()
    {
        return $this->contar('SELECT COUNT(*) AS total FROM `grupo`');
    }

    public function contarInscripciones()
    {
        return $this->contar('SELECT COUNT(*) AS total FROM inscripcion');
    }
}
