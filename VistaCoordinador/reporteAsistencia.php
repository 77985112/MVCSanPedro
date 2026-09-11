<?php
session_start();
if (!isset($_SESSION['CiPersona'])) {
    header("Location: ../logout.php");
    exit();
}
$Catequista = $_SESSION['CiPersona'];

require_once '../Controlador/controladorReporteAsistencia.php';

// Obtener el año de la fecha seleccionada
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
$anio = date('Y', strtotime($fecha));
$sacramento = isset($_GET['sacramento']) ? $_GET['sacramento'] : $Catequista['Sacramento'];

// Si se solicita generar el PDF
if (isset($_GET['generar_pdf'])) {
    $controlador = new ReporteAsistenciaControlador();
    $controlador->generarReportePDF($sacramento, $anio);
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Asistencia</title>
    <!-- Incluir tus estilos CSS aquí -->
</head>
<body class="sidebar-start-enabled">
    <section class="pt-0">
        <div class="container">
            <div class="card">
                <div class="py-3">
                    <div class="row position-relative">
                        <div class="col-lg-10 mx-auto">
                            <div class="text-center">
                                <h1>Reporte de Asistencia de Catequistas</h1>
                                <p>Selecciona una fecha para generar el reporte de asistencia</p>
                            </div>
                            <div class="mx-auto bg-mode shadow rounded p-2 mt-5 col-lg-6">
                                <form method="GET" class="row align-items-end g-4">
                                    <input type="hidden" name="sacramento" id="sacramento" value="<?php echo htmlspecialchars($sacramento); ?>" required>
                                    <div class="col-sm-6 col-lg-6">
                                        <label for="fecha" class="form-label">Fecha:</label>
                                        <input type="date" name="fecha" id="fecha" class="form-control" value="<?php echo htmlspecialchars($fecha); ?>" required>
                                    </div>
                                    <div class="col-sm-6 col-lg-3">
                                        <button type="submit" class="btn btn-primary-soft w-100" data-bs-toggle="tooltip" data-bs-placement="top" title="Buscar"><i class="bi bi-search"></i></button>
                                    </div>
                                    <div class="col-sm-6 col-lg-3">
                                        <button type="submit" name="generar_pdf" class="btn btn-danger-soft w-100" data-bs-toggle="tooltip" data-bs-placement="top" title="Generar PDF"><i class="bi bi-file-earmark-pdf"></i></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>