<?php
require_once __DIR__ . '/../Modelo/modeloDashboard.php';

class ControladorDashboard
{
    private $modelo;

    public function __construct()
    {
        $this->modelo = new ModeloDashboard();
    }

    private function intentar(callable $consulta, $valorPorDefecto)
    {
        try {
            return $consulta();
        } catch (Throwable $e) {
            error_log('Dashboard: ' . $e->getMessage());
            return $valorPorDefecto;
        }
    }

    public function obtenerResumen()
    {
        $personalActivo = $this->intentar(function () {
            return $this->modelo->contarPersonalActivo();
        }, 0);
        $personalTotal = $this->intentar(function () {
            return $this->modelo->contarPersonalTotal();
        }, 0);
        $personas = $this->intentar(function () {
            return $this->modelo->contarPersonas();
        }, 0);
        $reservas = $this->intentar(function () {
            return $this->modelo->contarReservas();
        }, 0);
        $certificados = $this->intentar(function () {
            return $this->modelo->contarCertificados();
        }, 0);
        $actividades = $this->intentar(function () {
            return $this->modelo->contarActividades();
        }, 0);
        $proximasReservas = $this->intentar(function () {
            return $this->modelo->obtenerProximasReservas(4);
        }, []);
        $actividadesRecientes = $this->intentar(function () {
            return $this->modelo->obtenerActividadesRecientes(3);
        }, []);
        $estadisticasSacramentos = $this->intentar(function () {
            return $this->modelo->obtenerEstadisticasSacramentos(2026);
        }, []);
        $estadisticasMisas = $this->intentar(function () {
            return $this->modelo->obtenerEstadisticasMisas(2026);
        }, []);
        $catequistasActivos = $this->intentar(function () {
            return $this->modelo->contarCatequistasActivos();
        }, 0);
        $grupos = $this->intentar(function () {
            return $this->modelo->contarGrupos();
        }, 0);
        $inscripciones = $this->intentar(function () {
            return $this->modelo->contarInscripciones();
        }, 0);

        return [
            'personalActivo' => (int) $personalActivo,
            'personalTotal' => (int) $personalTotal,
            'personas' => (int) $personas,
            'reservas' => (int) $reservas,
            'certificados' => (int) $certificados,
            'actividades' => (int) $actividades,
            'proximasReservas' => is_array($proximasReservas) ? $proximasReservas : [],
            'actividadesRecientes' => is_array($actividadesRecientes) ? $actividadesRecientes : [],
            'estadisticasSacramentos' => is_array($estadisticasSacramentos) ? $estadisticasSacramentos : [],
            'estadisticasMisas' => is_array($estadisticasMisas) ? $estadisticasMisas : [],
            'catequesis' => [
                'activos' => (int) $catequistasActivos,
                'grupos' => (int) $grupos,
                'inscripciones' => (int) $inscripciones,
            ],
            'totalModulos' => (int) $personalActivo + (int) $reservas + (int) $certificados,
        ];
    }
}
