<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: ../logout.php");
  exit();
}
$usuario = $_SESSION['usuario'];

// Solo el Párroco puede acceder
if (trim($usuario['DescripCar'] ?? '') !== 'Párroco') {
  header("Location: VistaPersonal.php");
  exit();
}

require_once __DIR__ . '/../Controlador/controladorReporteActividad.php';
require_once __DIR__ . '/../Modelo/modeloReporteActividad.php';

$modelo = new ReporteActividadModelo();
$anos = $modelo->obtenerAnosDisponibles();
$anioActual = (int) date('Y');
$anioSeleccionado = isset($_GET['anio']) && is_numeric($_GET['anio']) ? (int) $_GET['anio'] : $anioActual;
$anioSeleccionado = in_array($anioSeleccionado, $anos, true) ? $anioSeleccionado : $anioActual;

$tipo = isset($_GET['tipo']) && in_array($_GET['tipo'], ['mensual', 'anual'], true) ? $_GET['tipo'] : '';

if ($tipo !== '') {
    $controlador = new ReporteActividadControlador();
    $controlador->generarPDF($anioSeleccionado, $tipo);
    exit();
}

$actividades = $modelo->obtenerActividadesPorAno($anioSeleccionado);
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <title>Reporte de Actividades - San Pedro de Sacaba</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="shortcut icon" href="../assets/images/Icono.png">
  <link rel="stylesheet" type="text/css" href="../assets/vendor/font-awesome/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../assets/vendor/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" type="text/css" href="../assets/css/style.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/style.css'); ?>">
  <link rel="stylesheet" type="text/css" href="../assets/css/logo-cabecera.css?v=<?= filemtime(__DIR__ . '/../assets/css/logo-cabecera.css') ?>">
  <script src="../assets/js/jquery-3.6.4.min.js"></script>
</head>

<body>
  <header class="navbar-light fixed-top header-static bg-mode">
    <nav class="navbar navbar-expand-lg">
      <div class="container">
        <a class="navbar-brand" href="VistaPersonal.php">
          <img class="light-mode-item navbar-brand-item" src="../assets/images/logo.png?v=<?= filemtime(__DIR__ . '/../assets/images/logo.png') ?>" alt="logo">
          <img class="dark-mode-item navbar-brand-item" src="../assets/images/logo-light.png?v=<?= filemtime(__DIR__ . '/../assets/images/logo-light.png') ?>" alt="logo">
        </a>
        <a href="VistaPersonal.php" class="btn btn-primary-soft ms-auto">Volver a Inicio</a>
      </div>
    </nav>
  </header>

  <main>
    <div class="container" style="padding-top: 100px;">
      <div class="row g-4">
        <div class="col-lg-8 mx-auto">
          <div class="card">
            <div class="card-header border-0 pb-0">
              <h5 class="card-title">Reporte de Actividades Parroquiales</h5>
            </div>
            <div class="card-body">
              <p class="text-muted">Seleccione el año y el tipo de reporte que desea generar en PDF.</p>

              <form method="get" action="VistaReporteActividades.php" class="row g-3 align-items-end">
                <div class="col-md-4">
                  <label for="anio" class="form-label">Año</label>
                  <select id="anio" name="anio" class="form-select">
                    <?php foreach ($anos as $anio): ?>
                      <option value="<?php echo $anio; ?>" <?php echo $anio === $anioSeleccionado ? 'selected' : ''; ?>><?php echo $anio; ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-4">
                  <a id="linkReporteMensual" class="btn btn-primary-soft w-100" href="VistaReporteActividades.php?anio=<?php echo $anioSeleccionado; ?>&tipo=mensual" target="_blank" rel="noopener noreferrer">
                    <i class="bi bi-calendar-month me-1"></i> Reporte Mensual
                  </a>
                </div>
                <div class="col-md-4">
                  <a id="linkReporteAnual" class="btn btn-primary w-100" href="VistaReporteActividades.php?anio=<?php echo $anioSeleccionado; ?>&tipo=anual" target="_blank" rel="noopener noreferrer">
                    <i class="bi bi-calendar3 me-1"></i> Reporte Anual
                  </a>
                </div>
              </form>

              <hr class="my-4">

              <h6 class="mb-3">Vista previa del período <?php echo $anioSeleccionado; ?> (<?php echo count($actividades); ?> actividades)</h6>
              <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                  <thead class="table-light">
                    <tr>
                      <th>#</th>
                      <th>Título</th>
                      <th>Fecha</th>
                      <th>Tipo</th>
                      <th>Estado</th>
                      <th>Publicado por</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($actividades)): ?>
                      <tr><td colspan="6" class="text-center">No hay actividades registradas.</td></tr>
                    <?php else: ?>
                      <?php foreach ($actividades as $index => $a): ?>
                        <tr class="<?php echo strcasecmp($a['EstActividad'], 'Eliminado') === 0 ? 'table-danger' : ''; ?>">
                          <td><?php echo $index + 1; ?></td>
                          <td><?php echo htmlspecialchars($a['TituloActividad']); ?></td>
                          <td><?php echo date('d/m/Y', strtotime($a['FechaActividad'])); ?></td>
                          <td><?php echo htmlspecialchars($a['TipoActividad']); ?></td>
                          <td><?php echo htmlspecialchars($a['EstActividad']); ?></td>
                          <td><?php echo htmlspecialchars($a['PublicadoPor']); ?></td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script src="../assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/functions.js"></script>
  <script src="../assets/js/tema.js"></script>
  <script>
    (function () {
      var selectAnio = document.getElementById('anio');
      var linkMensual = document.getElementById('linkReporteMensual');
      var linkAnual = document.getElementById('linkReporteAnual');
      if (!selectAnio || !linkMensual || !linkAnual) return;
      function actualizarEnlaces() {
        var anio = selectAnio.value;
        linkMensual.href = 'VistaReporteActividades.php?anio=' + encodeURIComponent(anio) + '&tipo=mensual';
        linkAnual.href = 'VistaReporteActividades.php?anio=' + encodeURIComponent(anio) + '&tipo=anual';
      }
      selectAnio.addEventListener('change', actualizarEnlaces);
    })();
  </script>
</body>

</html>
