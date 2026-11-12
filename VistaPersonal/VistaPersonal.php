<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: ../logout.php");
  exit();
}
$usuario = $_SESSION['usuario'];
$accesoDenegado = !empty($_SESSION['acceso_denegado']);
unset($_SESSION['acceso_denegado']);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && empty($_FILES)
    && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    http_response_code(413);
    exit('El archivo supera el límite de carga. Use una imagen de hasta 2 MB o un video de hasta 10 MB.');
}


require_once __DIR__ . '/../Controlador/controladorActividad.php';

$controller = new ControladorActividad();
$puedeRegistrarParroquia = $controller->puedePublicarParroquia($usuario);
$puedeGestionarParroquia = $controller->puedeGestionarActividadParroquia($usuario);
$esAutorActividad = static function ($actividad) use ($usuario) {
    return (int) ($actividad['CodPer'] ?? 0) === (int) ($usuario['CodPer'] ?? 0);
};
$puedeEditarActividad = static function ($actividad) use ($controller, $esAutorActividad, $puedeGestionarParroquia) {
    // Las actividades de Primera Comunión y Confirmación las edita su
    // coordinador; en este panel solo se editan las de Parroquia.
    return ($actividad['TipoActividad'] ?? '') === 'Parroquia'
        && ($esAutorActividad($actividad) || $puedeGestionarParroquia)
        && ControladorActividad::esActividadEditable($actividad);
};
$puedeEliminarActividad = static function ($actividad) use ($esAutorActividad, $puedeGestionarParroquia) {
    return $esAutorActividad($actividad) || $puedeGestionarParroquia;
};
$errorActividad = '';
$actividadPublicada = !empty($_SESSION['actividad_parroquia_publicada']);
unset($_SESSION['actividad_parroquia_publicada']);
$valoresActividad = array_fill_keys(
    ['TituloActividad', 'DetalleActividad', 'FechaActividad', 'HoraAct', 'LugarAct', 'VideoUrl'], ''
);
if (empty($_SESSION['csrf_actividad_parroquia'])) {
  $_SESSION['csrf_actividad_parroquia'] = bin2hex(random_bytes(32));
}
if (empty($_SESSION['csrf_actividad_eliminar'])) {
  $_SESSION['csrf_actividad_eliminar'] = bin2hex(random_bytes(32));
}
$actividadActualizada = !empty($_SESSION['actividad_parroquia_actualizada']);
unset($_SESSION['actividad_parroquia_actualizada']);
$actividadEliminada = !empty($_SESSION['actividad_eliminada']);
unset($_SESSION['actividad_eliminada']);
$errorEliminarActividad = isset($_SESSION['actividad_eliminada_error'])
  ? (string) $_SESSION['actividad_eliminada_error'] : '';
unset($_SESSION['actividad_eliminada_error']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'eliminar_actividad') {
  $idActividadEliminar = (int) ($_POST['idActividad'] ?? 0);
  if (!$controller->puedeEliminarActividadParroquia($idActividadEliminar, $usuario)) {
    http_response_code(403);
    exit('No tienes permiso para eliminar esta actividad.');
  }
  $errorEliminacion = '';
  if (!is_string($_POST['csrf_actividad_eliminar'] ?? null)
      || !hash_equals($_SESSION['csrf_actividad_eliminar'], $_POST['csrf_actividad_eliminar'])) {
    $errorEliminacion = 'La sesión del formulario venció. Recargue la página e intente de nuevo.';
  } else {
    try {
      $controller->eliminarActividad((int) ($_POST['idActividad'] ?? 0), $usuario);
      $_SESSION['actividad_eliminada'] = true;
    } catch (InvalidArgumentException $e) {
      $errorEliminacion = $e->getMessage();
    } catch (Throwable $e) {
      error_log('Eliminar actividad: ' . $e->getMessage());
      $errorEliminacion = 'No se pudo eliminar la actividad. Intente nuevamente.';
    }
  }
  if ($errorEliminacion !== '') {
    $_SESSION['actividad_eliminada_error'] = $errorEliminacion;
  }
  header('Location: VistaPersonal.php', true, 303);
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!$puedeRegistrarParroquia) {
    http_response_code(403);
    exit('No tienes permiso para publicar actividades de Parroquia.');
  }
  foreach ($valoresActividad as $campo => $valor) {
    $valoresActividad[$campo] = is_string($_POST[$campo] ?? null) ? trim($_POST[$campo]) : '';
  }
  if (!is_string($_POST['csrf_actividad_parroquia'] ?? null)
      || !hash_equals($_SESSION['csrf_actividad_parroquia'], $_POST['csrf_actividad_parroquia'])) {
    $errorActividad = 'La sesión del formulario venció. Revise los datos y vuelva a seleccionar la imagen o el video.';
  } else {
    try {
      $controller->registrarActividadParroquia($_POST, $usuario);
      $_SESSION['actividad_parroquia_publicada'] = true;
      $_SESSION['csrf_actividad_parroquia'] = bin2hex(random_bytes(32));
      header('Location: VistaPersonal.php#tab-2', true, 303);
      exit();
    } catch (InvalidArgumentException | DomainException $e) {
      $errorActividad = $e->getMessage();
    } catch (Throwable $e) {
      error_log('Registrar actividad de Parroquia: ' . $e->getMessage());
      $errorActividad = 'No se pudo publicar la actividad. Intente nuevamente.';
    }
  }
}
$valorActividad = static function ($campo) use ($valoresActividad) {
  return htmlspecialchars($valoresActividad[$campo], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};
$fechaMinimaActividad = ControladorActividad::fechaMinimaActividad();

$actividadesParroquia = $controller->obtenerActividades('Parroquia');
$actividadesPrimeraComunion = $controller->obtenerActividades('Primera Comunión');
$actividadesConfirmacion = $controller->obtenerActividades('Confirmación');
$actividadesTodas = $controller->obtenerActividades();

?>

<!DOCTYPE html>
<html lang="es">

<head>
  <title>San Pedro de Sacaba</title>
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

        <ul class="nav flex-nowrap align-items-center ms-sm-3 list-unstyled">
          <li class="nav-item ms-2">
            <a class="nav-link bg-light icon-md btn btn-light p-0" href="Configuracion.php" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Configurar">
              <i class="bi bi-gear-fill fs-6"> </i>
            </a>
          </li>

          <li class="nav-item ms-2 dropdown">
            <a class="nav-link btn icon-md p-0" href="#" id="profileDropdown" role="button" data-bs-auto-close="outside" data-bs-display="static" data-bs-toggle="dropdown" aria-expanded="false">
              <img class="avatar-img rounded border border-white border-3" src="<?php echo ($usuario['PerfilPer'] == 'Ninguno' || empty($usuario['PerfilPer'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$usuario['PerfilPer']}?v=" . time(); ?>" alt="">
            </a>
            <ul class="dropdown-menu dropdown-animation dropdown-menu-end pt-3 small me-md-n3" aria-labelledby="profileDropdown">
              <li class="px-3">
                <div class="d-flex align-items-center position-relative">
                  <div class="avatar me-3">
                    <img class="avatar-img rounded-circle" src="<?php echo ($usuario['PerfilPer'] == 'Ninguno' || empty($usuario['PerfilPer'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$usuario['PerfilPer']}?v=" . time(); ?>" alt="avatar">
                  </div>
                  <div>
                    <a class="h6 stretched-link" href="#"><?php echo $usuario['Nombre'] . " " . $usuario['Paterno'] . " " . $usuario['Materno']; ?></a>
                    <p class="small m-0"><?php echo $usuario['DescripCar']; ?> <i class="bi bi-patch-check-fill text-success small"></i></p>
                  </div>
                </div>
                <a class="dropdown-item btn btn-primary-soft btn-sm my-2 text-center" href="PerfilAdmin.php">Ver mi Perfil</a>
              </li>
              <li>
                <a class="dropdown-item" href="Configuracion.php"><i class="bi bi-gear fa-fw me-2"></i>Configuración</a>
              </li>
              <li>
                <a class="dropdown-item" href="Reglamento.php" target="_blank"> <i class="fa-fw bi bi-card-text me-2"></i>Reglamento</a>
              </li>
              <li class="dropdown-divider"></li>
              <li><a class="dropdown-item bg-danger-soft-hover" href="../logout.php"><i class="bi bi-power fa-fw me-2"></i>Salir</a></li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li>
                <div class="modeswitch-item theme-icon-active d-flex justify-content-center gap-3 align-items-center p-2 pb-0">
                  <span>Modo:</span>
                  <button type="button" class="btn btn-modeswitch nav-link text-primary-hover mb-0" data-bs-theme-value="light" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Claro">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sun fa-fw mode-switch" viewbox="0 0 16 16">
                      <path d="M8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6zm0 1a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z">
                      </path>
                      <use href="#"></use>
                    </svg>
                  </button>
                  <button type="button" class="btn btn-modeswitch nav-link text-primary-hover mb-0" data-bs-theme-value="dark" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Oscuro">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-moon-stars fa-fw mode-switch" viewbox="0 0 16 16">
                      <path d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278zM4.858 1.311A7.269 7.269 0 0 0 1.025 7.71c0 4.02 3.279 7.276 7.319 7.276a7.316 7.316 0 0 0 5.205-2.162c-.337.042-.68.063-1.029.063-4.61 0-8.343-3.714-8.343-8.29 0-1.167.242-2.278.681-3.286z">
                      </path>
                      <path d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.734 1.734 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.734 1.734 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.734 1.734 0 0 0 1.097-1.097l.387-1.162zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.156 1.156 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.156 1.156 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732L13.863.1z">
                      </path>
                      <use href="#"></use>
                    </svg>
                  </button>
                </div>
              </li>
            </ul>
          </li>
        </ul>
      </div>
    </nav>
  </header>


  <main>
    <div class="container personal-dashboard-container">
      <?php if ($accesoDenegado): ?>
        <div class="alert alert-warning d-flex align-items-center" role="alert">
          <i class="bi bi-shield-lock me-2"></i>
          No tienes acceso a ese módulo. El Párroco puede habilitarlo desde «Personal».
        </div>
      <?php endif; ?>
      <div class="row g-4">
        <div class="col-lg-3">
          <div class="d-flex align-items-center d-lg-none">
            <button class="border-0 bg-transparent" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSideNavbar" aria-controls="offcanvasSideNavbar">
              <span class="btn btn-primary"><i class="fa-solid fa-sliders-h"></i></span>
              <span class="h6 mb-0 fw-bold d-lg-none ms-2">Otras Opciones</span>
            </button>
          </div>

          <nav class="navbar navbar-expand-lg mx-0">
            <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasSideNavbar">
              <div class="offcanvas-header">
                <a class="navbar-brand" href="#">
                  <img class="light-mode-item navbar-brand-item" src="../assets/images/logo.png?v=<?= filemtime(__DIR__ . '/../assets/images/logo.png') ?>" alt="logo">
                  <img class="dark-mode-item navbar-brand-item" src="../assets/images/logo-light.png?v=<?= filemtime(__DIR__ . '/../assets/images/logo-light.png') ?>" alt="logo">
                </a>
                <button type="button" class="btn-close text-reset ms-auto" data-bs-dismiss="offcanvas" aria-label="Close"></button>
              </div>

              <div class="offcanvas-body d-block px-2 px-lg-0">
                <div class="card overflow-hidden">
                  <div class="h-50px" style="background-image:url(<?php echo ($usuario['FondoPer'] == 'Ninguno' || empty($usuario['FondoPer'])) ? '../assets/images/fondo/Ninguno.jpg' : "../assets/images/fondo/{$usuario['FondoPer']}?v=" . time(); ?>); background-position: center; background-size: cover; background-repeat: no-repeat;">
                  </div>
                  <div class="card-body pt-0">
                    <div class="text-center">
                      <div class="avatar avatar-lg mt-n5 mb-3">
                        <img class="avatar-img rounded border border-white border-3" src="<?php echo ($usuario['PerfilPer'] == 'Ninguno' || empty($usuario['PerfilPer'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$usuario['PerfilPer']}?v=" . time(); ?>" alt="">
                      </div>
                      <h5 class="mb-0"> <a href="PerfilAdmin.php"><?php echo $usuario['Nombre'] . " " . $usuario['Paterno'] . " " . $usuario['Materno']; ?></a> </h5>
                      <small><?php echo $usuario['DescripCar']; ?></small>
                      <p class="mt-3"><?php echo $usuario['FrasePer']; ?></p>
                    </div>

                    <hr>

                    <?php require_once __DIR__ . '/../Controlador/controladorAcceso.php'; ControladorAcceso::renderMenu(); ?>
                  </div>
                  <div class="card-footer text-center py-2">
                    <a class="btn btn-link btn-sm" href="PerfilAdmin.php">Ver mi Perfil</a>
                  </div>
                </div>
                <p class="small text-center mt-1">©2026 <a class="text-reset" target="_blank" href="https://sacaba.gob.bo/index.php/inicio/cultura/arte-y-arquitectura"> San Pedro de Sacaba </a></p>
              </div>
            </div>
          </nav>
        </div>

        <div class="col-md-8 col-lg-6 vstack gap-4">

          <div class="card h-100">
            <div class="card-header d-sm-flex align-items-center text-center justify-content-sm-between border-0 pb-0">
              <h1 class="h4 card-title">Proximas Actividades</h1>
              <?php if (trim($usuario['DescripCar'] ?? '') === 'Párroco'): ?>
                <div class="d-flex gap-2 mt-2 mt-sm-0">
                  <a class="btn btn-outline-primary btn-sm" href="VistaReporteActividades.php?tipo=mensual" target="_blank" rel="noopener noreferrer">
                    <i class="bi bi-calendar-month me-1"></i>Reporte Mensual
                  </a>
                  <a class="btn btn-outline-primary btn-sm" href="VistaReporteActividades.php?tipo=anual" target="_blank" rel="noopener noreferrer">
                    <i class="bi bi-calendar3 me-1"></i>Reporte Anual
                  </a>
                </div>
              <?php endif; ?>
            </div>
            <div class="card-body">
              <ul class="nav nav-tabs nav-bottom-line justify-content-center justify-content-md-start">
                <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-1"> Todos</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-2"> Parroquia </a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-3"> Primera Comunión </a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-4"> Confirmación </a></li>
              </ul>
              <?php if ($actividadActualizada): ?>
                <div class="alert alert-success" role="status"><i class="bi bi-check-circle me-1"></i>Los cambios de la actividad se guardaron correctamente.</div>
              <?php endif; ?>
              <?php if ($actividadEliminada): ?>
                <div class="alert alert-success" role="status"><i class="bi bi-check-circle me-1"></i>La actividad se eliminó correctamente.</div>
              <?php endif; ?>
              <?php if ($errorEliminarActividad !== ''): ?>
                <div class="alert alert-danger" role="alert"><i class="bi bi-exclamation-triangle me-1"></i><?php echo htmlspecialchars($errorEliminarActividad, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></div>
              <?php endif; ?>
              <div class="tab-content mb-0 pb-0 actividades-lista-compacta">

                <div class="tab-pane fade show active" id="tab-1">
                  <div class="row g-4">
                    <?php if (empty($actividadesTodas)): ?>
                      <div class="my-sm-5 py-sm-5 text-center">
                        <i class="display-1 text-body-secondary bi bi-calendar2-event"></i>
                        <h4 class="mt-2 mb-3 text-body">No hay Actividades</h4>
                      </div>
                    <?php else: ?>
                      <?php foreach ($actividadesTodas as $actividad): ?>
                        <div class="col-sm-6 col-xl-4">
                          <div class="card h-100">
                            <div class="position-relative">
                              <?php echo ControladorActividad::mediaActividad($actividad, '150px'); ?>
                              <div class="badge bg-<?php echo $actividad['EstActividad'] == 'Activo' ? 'success' : 'danger'; ?> text-white mt-2 me-2 position-absolute top-0 end-0"><?php echo $actividad['EstActividad']; ?></div>
                            </div>
                            <div class="card-body position-relative pt-0">
                              <input type="hidden" value="<?php echo $actividad['idActividad']; ?>">
                              <h6 class="mt-3"><a href="DetalleActividad.php?Actividad=<?php echo $actividad['idActividad']; ?>"><?php echo htmlspecialchars($actividad['TituloActividad'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a></h6>
                              <p class="mb-0 small"><i class="bi bi-calendar-check pe-1"></i><?php echo $actividad['FechaActividad']; ?> a <?php echo $actividad['HoraAct']; ?></p>
                              <p class="small"><i class="bi bi-geo-alt pe-1"></i><?php echo htmlspecialchars($actividad['LugarAct'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
                              <?php if ($puedeEliminarActividad($actividad)): ?>
                                <div class="d-flex gap-2 mt-2">
                                  <?php if ($puedeEditarActividad($actividad)): ?>
                                    <a class="btn btn-primary-soft btn-sm flex-fill" href="DetalleActividad.php?Actividad=<?php echo (int) $actividad['idActividad']; ?>&amp;editar=1#editarActividad">
                                      <i class="bi bi-pencil-square me-1"></i>Editar
                                    </a>
                                  <?php endif; ?>
                                  <button type="button" class="btn btn-outline-danger btn-sm flex-fill" data-bs-toggle="modal" data-bs-target="#modalEliminarActividad" data-id="<?php echo $actividad['idActividad']; ?>" data-titulo="<?php echo htmlspecialchars($actividad['TituloActividad'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
                                    <i class="bi bi-trash3 me-1"></i>Eliminar
                                  </button>
                                </div>
                              <?php endif; ?>
                            </div>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </div>
                </div>

                <div class="tab-pane fade" id="tab-2">
                  <?php if ($puedeRegistrarParroquia): ?>
                    <div class="mb-4">
                      <button type="button" class="btn btn-primary-soft" data-bs-toggle="modal" data-bs-target="#nuevaActividadParroquia">
                        <i class="bi bi-plus-circle me-2"></i>Nueva actividad de Parroquia
                      </button>
                    </div>
                  <?php endif; ?>
                  <?php if ($actividadPublicada): ?>
                    <div class="alert alert-success" role="status">La actividad se publicó en Parroquia.</div>
                  <?php endif; ?>
                  <div class="row g-4">
                    <?php if (empty($actividadesParroquia)): ?>
                      <div class="my-sm-5 py-sm-5 text-center">
                        <i class="display-1 text-body-secondary bi bi-calendar2-event"></i>
                        <h4 class="mt-2 mb-3 text-body">No hay Actividades</h4>
                      </div>
                    <?php else: ?>
                      <?php foreach ($actividadesParroquia as $actividad): ?>
                        <div class="col-sm-6 col-xl-4">
                          <div class="card h-100">
                            <div class="position-relative">
                              <?php echo ControladorActividad::mediaActividad($actividad, '150px'); ?>
                              <div class="badge bg-<?php echo $actividad['EstActividad'] == 'Activo' ? 'success' : 'danger'; ?> text-white mt-2 me-2 position-absolute top-0 end-0"><?php echo $actividad['EstActividad']; ?></div>
                            </div>
                            <div class="card-body position-relative pt-0">
                              <input type="hidden" value="<?php echo $actividad['idActividad']; ?>">
                              <h6 class="mt-3"><a href="DetalleActividad.php?Actividad=<?php echo $actividad['idActividad']; ?>"><?php echo htmlspecialchars($actividad['TituloActividad'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a></h6>
                              <p class="mb-0 small"><i class="bi bi-calendar-check pe-1"></i><?php echo $actividad['FechaActividad']; ?> a <?php echo $actividad['HoraAct']; ?></p>
                              <p class="small"><i class="bi bi-geo-alt pe-1"></i><?php echo htmlspecialchars($actividad['LugarAct'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
                              <?php if ($puedeEliminarActividad($actividad)): ?>
                                <div class="d-flex gap-2 mt-2">
                                  <?php if ($puedeEditarActividad($actividad)): ?>
                                    <a class="btn btn-primary-soft btn-sm flex-fill" href="DetalleActividad.php?Actividad=<?php echo (int) $actividad['idActividad']; ?>&amp;editar=1#editarActividad">
                                      <i class="bi bi-pencil-square me-1"></i>Editar
                                    </a>
                                  <?php endif; ?>
                                  <button type="button" class="btn btn-outline-danger btn-sm flex-fill" data-bs-toggle="modal" data-bs-target="#modalEliminarActividad" data-id="<?php echo $actividad['idActividad']; ?>" data-titulo="<?php echo htmlspecialchars($actividad['TituloActividad'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
                                    <i class="bi bi-trash3 me-1"></i>Eliminar
                                  </button>
                                </div>
                              <?php endif; ?>
                            </div>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </div>
                </div>

                <!-- Primera Comunión -->
                <div class="tab-pane fade" id="tab-3">
                  <div class="row g-4">
                    <?php if (empty($actividadesPrimeraComunion)): ?>
                      <div class="my-sm-5 py-sm-5 text-center">
                        <i class="display-1 text-body-secondary bi bi-calendar2-event"></i>
                        <h4 class="mt-2 mb-3 text-body">No hay Actividades</h4>
                      </div>
                    <?php else: ?>
                      <?php foreach ($actividadesPrimeraComunion as $actividad): ?>
                        <div class="col-sm-6 col-xl-4">
                          <div class="card h-100">
                            <div class="position-relative">
                              <?php echo ControladorActividad::mediaActividad($actividad, '150px'); ?>
                              <div class="badge bg-<?php echo $actividad['EstActividad'] == 'Activo' ? 'success' : 'danger'; ?> text-white mt-2 me-2 position-absolute top-0 end-0"><?php echo $actividad['EstActividad']; ?></div>
                            </div>
                            <div class="card-body position-relative pt-0">
                              <input type="hidden" value="<?php echo $actividad['idActividad']; ?>">
                              <h6 class="mt-3"><a href="DetalleActividad.php?Actividad=<?php echo $actividad['idActividad']; ?>"><?php echo htmlspecialchars($actividad['TituloActividad'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a></h6>
                              <p class="mb-0 small"><i class="bi bi-calendar-check pe-1"></i><?php echo $actividad['FechaActividad']; ?> a <?php echo $actividad['HoraAct']; ?></p>
                              <p class="small"><i class="bi bi-geo-alt pe-1"></i><?php echo htmlspecialchars($actividad['LugarAct'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
                              <?php if ($puedeEliminarActividad($actividad)): ?>
                                <div class="d-flex gap-2 mt-2">
                                  <?php if ($puedeEditarActividad($actividad)): ?>
                                    <a class="btn btn-primary-soft btn-sm flex-fill" href="DetalleActividad.php?Actividad=<?php echo (int) $actividad['idActividad']; ?>&amp;editar=1#editarActividad">
                                      <i class="bi bi-pencil-square me-1"></i>Editar
                                    </a>
                                  <?php endif; ?>
                                  <button type="button" class="btn btn-outline-danger btn-sm flex-fill" data-bs-toggle="modal" data-bs-target="#modalEliminarActividad" data-id="<?php echo $actividad['idActividad']; ?>" data-titulo="<?php echo htmlspecialchars($actividad['TituloActividad'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
                                    <i class="bi bi-trash3 me-1"></i>Eliminar
                                  </button>
                                </div>
                              <?php endif; ?>
                            </div>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </div>
                </div>

                <div class="tab-pane fade" id="tab-4">
                  <div class="row g-4">
                    <?php if (empty($actividadesConfirmacion)): ?>
                      <div class="my-sm-5 py-sm-5 text-center">
                        <i class="display-1 text-body-secondary bi bi-calendar2-event"></i>
                        <h4 class="mt-2 mb-3 text-body">No hay Actividades</h4>
                      </div>
                    <?php else: ?>
                      <?php foreach ($actividadesConfirmacion as $actividad): ?>
                        <div class="col-sm-6 col-xl-4">
                          <div class="card h-100">
                            <div class="position-relative">
                              <?php echo ControladorActividad::mediaActividad($actividad, '150px'); ?>
                              <div class="badge bg-<?php echo $actividad['EstActividad'] == 'Activo' ? 'success' : 'danger'; ?> text-white mt-2 me-2 position-absolute top-0 end-0"><?php echo $actividad['EstActividad']; ?></div>
                            </div>
                            <div class="card-body position-relative pt-0">
                              <input type="hidden" value="<?php echo $actividad['idActividad']; ?>">
                              <h6 class="mt-3"><a href="DetalleActividad.php?Actividad=<?php echo $actividad['idActividad']; ?>"><?php echo htmlspecialchars($actividad['TituloActividad'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a></h6>
                              <p class="mb-0 small"><i class="bi bi-calendar-check pe-1"></i><?php echo $actividad['FechaActividad']; ?> a <?php echo $actividad['HoraAct']; ?></p>
                              <p class="small"><i class="bi bi-geo-alt pe-1"></i><?php echo htmlspecialchars($actividad['LugarAct'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
                              <?php if ($puedeEliminarActividad($actividad)): ?>
                                <div class="d-flex gap-2 mt-2">
                                  <?php if ($puedeEditarActividad($actividad)): ?>
                                    <a class="btn btn-primary-soft btn-sm flex-fill" href="DetalleActividad.php?Actividad=<?php echo (int) $actividad['idActividad']; ?>&amp;editar=1#editarActividad">
                                      <i class="bi bi-pencil-square me-1"></i>Editar
                                    </a>
                                  <?php endif; ?>
                                  <button type="button" class="btn btn-outline-danger btn-sm flex-fill" data-bs-toggle="modal" data-bs-target="#modalEliminarActividad" data-id="<?php echo $actividad['idActividad']; ?>" data-titulo="<?php echo htmlspecialchars($actividad['TituloActividad'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
                                    <i class="bi bi-trash3 me-1"></i>Eliminar
                                  </button>
                                </div>
                              <?php endif; ?>
                            </div>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    <?php endif; ?>

                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </main>

  <?php if ($puedeRegistrarParroquia): ?>
    <div class="modal fade" id="nuevaActividadParroquia" tabindex="-1" aria-labelledby="tituloActividadParroquia" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="tituloActividadParroquia">Nueva actividad de Parroquia</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <?php if ($errorActividad !== ''): ?>
              <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($errorActividad, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <form id="formActividadParroquia" action="VistaPersonal.php#tab-2" method="POST" class="row g-3" enctype="multipart/form-data">
              <input type="hidden" name="csrf_actividad_parroquia" value="<?php echo htmlspecialchars($_SESSION['csrf_actividad_parroquia'], ENT_QUOTES, 'UTF-8'); ?>">
              <div class="col-12">
                <label class="form-label" for="parroquiaTituloActividad">Título de la actividad *</label>
                <input type="text" class="form-control" id="parroquiaTituloActividad" name="TituloActividad" maxlength="150" value="<?php echo $valorActividad('TituloActividad'); ?>" required>
              </div>
              <div class="col-12">
                <label class="form-label" for="parroquiaDetalleActividad">Descripción *</label>
                <textarea class="form-control" id="parroquiaDetalleActividad" name="DetalleActividad" rows="4" maxlength="<?php echo ControladorActividad::LIMITE_DESCRIPCION; ?>" placeholder="Describe brevemente la actividad..." required><?php echo $valorActividad('DetalleActividad'); ?></textarea>
              </div>
              <div class="col-sm-6">
                <label class="form-label" for="parroquiaFechaActividad">Fecha *</label>
                <input type="date" class="form-control" id="parroquiaFechaActividad" name="FechaActividad" min="<?php echo $fechaMinimaActividad; ?>" value="<?php echo $valorActividad('FechaActividad'); ?>" required>
              </div>
              <div class="col-sm-6">
                <label class="form-label" for="parroquiaHoraAct">Hora *</label>
                <input type="time" class="form-control" id="parroquiaHoraAct" name="HoraAct" value="<?php echo $valorActividad('HoraAct'); ?>" required>
              </div>
              <div class="col-12">
                <label class="form-label" for="parroquiaLugarAct">Lugar *</label>
                <input type="text" class="form-control" id="parroquiaLugarAct" name="LugarAct" maxlength="<?php echo ControladorActividad::LIMITE_LUGAR; ?>" placeholder="Ej: Plaza principal de Sacaba" value="<?php echo $valorActividad('LugarAct'); ?>" required>
              </div>
              <div class="col-12">
                <label class="form-label" for="parroquiaVideoUrl">Enlace de video (YouTube, TikTok o Facebook)</label>
                <input type="url" class="form-control" id="parroquiaVideoUrl" name="VideoUrl" maxlength="255" placeholder="https://www.youtube.com/watch?v=..." value="<?php echo $valorActividad('VideoUrl'); ?>">
                <div class="form-text">Opcional. También puede subir un video MP4/WebM.</div>
                <div id="previewVideoUrlParroquia" class="mt-2"></div>
              </div>
              <div class="col-12">
                <label class="form-label">Imagen, video o enlace *</label>
                <div class="row g-3 align-items-end">
                  <div class="col-sm-6">
                    <label class="form-label small text-muted mb-1" for="parroquiaNuevaImagen">Imágenes (cualquier formato; se convierten a JPG, máx. 5)</label>
                    <input type="file" class="form-control" id="parroquiaNuevaImagen" name="NuevaImagenActividad[]" multiple accept="image/*" data-actividad-imagen="previewActividadParroquia" data-actividad-grupo="actividadParroquia" data-actividad-requerido="true" data-actividad-maximas="<?php echo ControladorActividad::MAXIMO_IMAGENES; ?>">
                  </div>
                  <div class="col-sm-6">
                    <label class="form-label small text-muted mb-1" for="parroquiaNuevaVideo">Video</label>
                    <input type="file" class="form-control" id="parroquiaNuevaVideo" name="NuevaVideoActividad" accept=".mp4,.webm,video/mp4,video/webm" data-actividad-video="previewVideoParroquia" data-actividad-grupo="actividadParroquia" data-actividad-requerido="true">
                    <video id="previewVideoParroquia" class="w-100 rounded mt-3 d-none actividad-video" controls playsinline preload="metadata" style="max-height:300px;"></video>
                  </div>
                </div>
                <div id="previewActividadParroquia" class="d-flex flex-wrap gap-2 mt-3"></div>
                <div class="form-text mt-2">Puede adjuntar imágenes, un video o pegar un enlace de YouTube, TikTok o Facebook.</div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-danger-soft" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success-soft" form="formActividadParroquia">Publicar actividad</button>
          </div>
        </div>
      </div>
    </div>
    <script>
      // Vista previa del video por enlace de la actividad de Parroquia.
      (function () {
        var campo = document.getElementById('parroquiaVideoUrl');
        var caja = document.getElementById('previewVideoUrlParroquia');
        if (!campo || !caja) return;

        function embed(url) {
          var u;
          try { u = new URL(url); } catch (e) { return null; }
          var host = u.hostname.toLowerCase().replace(/^www\./, '');
          var ruta = u.pathname || '';
          var m;

          if (['youtube.com', 'm.youtube.com', 'youtube-nocookie.com'].indexOf(host) >= 0) {
            var id = u.searchParams.get('v') || '';
            if (!id && (m = ruta.match(/^\/(?:shorts|embed|live|v)\/([A-Za-z0-9_-]{6,20})/))) id = m[1];
            return /^[A-Za-z0-9_-]{6,20}$/.test(id) ? 'https://www.youtube.com/embed/' + id : null;
          }
          if (host === 'youtu.be') {
            m = ruta.match(/^\/([A-Za-z0-9_-]{6,20})/);
            return m ? 'https://www.youtube.com/embed/' + m[1] : null;
          }
          if (host === 'tiktok.com' || host === 'm.tiktok.com') {
            m = ruta.match(/\/video\/(\d{6,25})/);
            return m ? 'https://www.tiktok.com/embed/v2/' + m[1] : null;
          }
          if (['facebook.com', 'm.facebook.com', 'web.facebook.com', 'fb.watch'].indexOf(host) >= 0) {
            return 'https://www.facebook.com/plugins/video.php?href=' + encodeURIComponent(url) + '&show_text=false';
          }
          return null;
        }

        function actualizar() {
          var valor = campo.value.trim();
          caja.replaceChildren();
          if (!valor) return;

          var src = embed(valor);
          if (!src) {
            var error = document.createElement('div');
            error.className = 'form-text text-danger';
            error.textContent = 'Use un enlace de YouTube, TikTok o Facebook.';
            caja.appendChild(error);
            return;
          }

          var marco = document.createElement('iframe');
          marco.src = src;
          marco.className = 'w-100 rounded';
          marco.style.cssText = 'height:340px;border:0;background:#101722;';
          marco.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share');
          marco.setAttribute('allowfullscreen', '');
          marco.setAttribute('loading', 'lazy');
          marco.setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');
          marco.title = 'Vista previa del video';
          caja.appendChild(marco);
        }

        campo.addEventListener('input', actualizar);
        campo.addEventListener('change', actualizar);
        actualizar();
      })();
    </script>
  <?php endif; ?>

  <div class="modal fade" id="modalEliminarActividad" tabindex="-1" aria-labelledby="tituloEliminarActividad" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="post" id="formEliminarActividad">
          <input type="hidden" name="accion" value="eliminar_actividad">
          <input type="hidden" name="idActividad" id="eliminarActividadId" value="">
          <input type="hidden" name="csrf_actividad_eliminar" value="<?php echo htmlspecialchars($_SESSION['csrf_actividad_eliminar'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
          <div class="modal-header">
            <h5 class="modal-title" id="tituloEliminarActividad">Eliminar actividad</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <p class="mb-2">¿Seguro que desea eliminar la actividad <strong id="eliminarActividadTitulo"></strong>?</p>
            <p class="small mb-0 text-danger"><i class="bi bi-exclamation-triangle me-1"></i>La actividad dejará de mostrarse en la página web, pero quedará registrada para fines de reporte. Esta acción no se puede deshacer.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-danger-soft" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-danger"><i class="bi bi-trash3 me-1"></i>Eliminar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    $(document).ready(function() {
      function obtenerEstado() {
        $.ajax({
          url: '../VerificarPersonal.php',
          method: 'GET',
          dataType: 'json',
          success: function(response) {
            if (response.Estado && response.DescripCar && response.PerfilPer) {
              $('#estado').text(response.Estado);
              $('#cargo').text(response.DescripCar);

              if (response.Estado === "Inactivo") {
                window.location.replace("PerfilAdmin.php");
              }
            } else {
              console.error(response.error || 'Error en la respuesta del servidor');
            }
          },
          error: function(jqXHR, textStatus, errorThrown) {
            console.error("Error en la solicitud AJAX: " + textStatus, errorThrown);
          }
        });
      }
      obtenerEstado();

      setInterval(obtenerEstado, 5000);
    });
  </script>
  <script src="../assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/functions.js"></script>
  <script src="../assets/js/tema.js"></script>
  <script>
    if (window.location.hash === '#tab-2' || <?php echo ($actividadPublicada || $errorActividad !== '') ? 'true' : 'false'; ?>) {
      bootstrap.Tab.getOrCreateInstance(document.querySelector('a[href="#tab-2"]')).show();
    }
    <?php if ($puedeRegistrarParroquia): ?>
      document.getElementById('formActividadParroquia').addEventListener('submit', function () {
        document.querySelector('button[form="formActividadParroquia"]').disabled = true;
      });
      <?php if ($errorActividad !== ''): ?>
        bootstrap.Modal.getOrCreateInstance(document.getElementById('nuevaActividadParroquia')).show();
      <?php endif; ?>
    <?php endif; ?>
  </script>
  <script src="../assets/js/actividad-multimedia.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/actividad-multimedia.js'); ?>"></script>
    <script src="../assets/js/actividad-compresion.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/actividad-compresion.js'); ?>"></script>
  <script>
    (function () {
      var modal = document.getElementById('modalEliminarActividad');
      if (!modal || !window.bootstrap) return;
      modal.addEventListener('show.bs.modal', function (event) {
        var boton = event.relatedTarget;
        if (!boton) return;
        document.getElementById('eliminarActividadId').value = boton.getAttribute('data-id') || '';
        document.getElementById('eliminarActividadTitulo').textContent = boton.getAttribute('data-titulo') || '';
      });
    })();
  </script>
</body>

</html>