<?php
require_once '../Controlador/controladorSolicitud.php';

session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: ../logout.php");
  exit();
}

$usuario = $_SESSION['usuario'];
require_once __DIR__ . '/../Controlador/controladorAcceso.php';
ControladorAcceso::exigir('certificado');
$controller = new ControladorInsSacramento();
$certificadoData = [];
$codIns = (int) ($_GET['CodIns'] ?? 0);

if ($codIns > 0) {
  $certificadoData = $controller->VerCertificadoMatrimonio($codIns);
}
$partidaGuardada = $certificadoData
  && trim((string) ($certificadoData['NroLibro'] ?? '')) !== ''
  && trim((string) ($certificadoData['NroPag'] ?? '')) !== ''
  && trim((string) ($certificadoData['NroPart'] ?? '')) !== ''
  && trim((string) ($certificadoData['EstadoCivilNovio'] ?? '')) !== ''
  && trim((string) ($certificadoData['EstadoCivilNovia'] ?? '')) !== '';

if (empty($_SESSION['token_detalle_matrimonio'])) {
  $_SESSION['token_detalle_matrimonio'] = bin2hex(random_bytes(32));
}
$mensaje = '';
$mensajeEdicion = isset($_GET['actualizado']) ? 'Datos del certificado actualizados correctamente.' : '';
$modoEdicion = false;
$fechaActualizada = isset($_GET['fecha_actualizada']);
if (isset($_GET['guardado'])) {
  $mensaje = 'Datos del certificado guardados.';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    if (!is_string($_POST['token_matrimonio'] ?? null) || !hash_equals($_SESSION['token_detalle_matrimonio'], $_POST['token_matrimonio'])) {
      throw new InvalidArgumentException('Recargue la página antes de guardar.');
    }
    $codIns = (int) ($_GET['CodIns'] ?? 0);
    $destino = 'VistaRegistradoMatrimonio.php?CodIns=' . $codIns;
    if (isset($_POST['guardar_datos_matrimonio'])) {
      $controller->ActualizarDatosMatrimonio($codIns, $_POST);
      $destino .= '&actualizado=1';
    } elseif (isset($_POST['actualizar_fecha_emision'])) {
      if (!$partidaGuardada) {
        throw new InvalidArgumentException('Guarde primero los datos de la partida.');
      }
      $controller->ActualizarFechaEmision($codIns);
      $destino .= '&fecha_actualizada=1';
    } elseif (isset($_POST['registrar_certificado'])) {
      $datos = $_POST;
      $datos['CodIns'] = $codIns;
      $datos['CodPer'] = $usuario['CodPer'];
      $controller->RegistrarCertificadoMatrimonio($datos);
      $destino .= '&guardado=1';
    } else {
      throw new InvalidArgumentException('Acción no válida.');
    }
    $_SESSION['token_detalle_matrimonio'] = bin2hex(random_bytes(32));
    header('Location: ' . $destino);
    exit;
  } catch (InvalidArgumentException $e) {
    $mensaje = $e->getMessage();
    $modoEdicion = isset($_POST['guardar_datos_matrimonio']);
  } catch (Throwable $e) {
    error_log('Error al guardar datos de matrimonio: ' . $e->getMessage());
    $mensaje = 'No se pudieron guardar los datos. Intente nuevamente.';
    $modoEdicion = isset($_POST['guardar_datos_matrimonio']);
  }
}
$valorMatrimonio = function ($campo, $predeterminado = '') use ($certificadoData) {
  $usarPost = $_SERVER['REQUEST_METHOD'] === 'POST'
    && (isset($_POST['guardar_datos_matrimonio']) || isset($_POST['registrar_certificado']));
  $valor = $usarPost ? ($_POST[$campo] ?? '') : ($certificadoData[$campo] ?? $predeterminado);
  return htmlspecialchars(is_scalar($valor) ? (string) $valor : '', ENT_QUOTES, 'UTF-8');
};
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

        <div class="col-md-12 col-lg-6 vstack gap-4">
          <div class="card h-100">
            <h4 class="p-2">Detalles del Certificado de Matrimonio</h4>
            <?php if ($mensaje !== ''): ?>
              <div class="alert alert-<?php echo $modoEdicion ? 'danger' : 'info'; ?> mx-3" role="status"><?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

              <?php if ($certificadoData): ?>
                <div class="mx-auto bg-mode shadow rounded p-4 mt-2 col-sm-10 col-lg-10">
                  <div class="row align-items-end g-4">
                    <div class="col-sm-12 col-lg-12">
                      <div class="d-flex justify-content-between align-items-center gap-2">
                        <h3 class="mb-0">Información detallada del Certificado</h3>
                        <button type="button" id="btnEditarMatrimonio" class="btn btn-sm btn-primary-soft <?php echo $modoEdicion ? 'd-none' : ''; ?>"><i class="bi bi-pencil-square me-1"></i>Editar</button>
                      </div>
                      <?php if ($mensajeEdicion !== ''): ?>
                        <div class="alert alert-<?php echo $modoEdicion ? 'danger' : 'success'; ?> py-2 mt-2 mb-2" role="alert">
                          <?php echo htmlspecialchars($mensajeEdicion, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                      <?php endif; ?>

                      <div id="detalleMatrimonioLectura" class="<?php echo $modoEdicion ? 'd-none' : ''; ?>">
                        <p><strong>Sacramento:</strong> <?php echo htmlspecialchars((string) ($certificadoData['DescripSac'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><strong>Novio:</strong> <?php echo htmlspecialchars((string) ($certificadoData['NombreNovio'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>(<?php echo htmlspecialchars((string) ($certificadoData['CiNovio'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>)</p>
                        <p><strong>Novia:</strong> <?php echo htmlspecialchars((string) ($certificadoData['NombreNovia'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>(<?php echo htmlspecialchars((string) ($certificadoData['CiNovia'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>)</p>
                        <p><strong>Padre del Novio:</strong> <?php echo htmlspecialchars((string) ($certificadoData['NombrePadreNovio'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><strong>Madre del Novio:</strong> <?php echo htmlspecialchars((string) ($certificadoData['NombreMadreNovio'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><strong>Padre de la Novia:</strong> <?php echo htmlspecialchars((string) ($certificadoData['NombrePadreNovia'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><strong>Madre de la Novia:</strong> <?php echo htmlspecialchars((string) ($certificadoData['NombreMadreNovia'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><strong>Testigo presencial 1:</strong> <?php echo htmlspecialchars((string) ($certificadoData['NombrePadrino'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><strong>Testigo presencial 2:</strong> <?php echo htmlspecialchars((string) ($certificadoData['NombreMadrina'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><strong>Testigo de información (Novio):</strong> <?php echo htmlspecialchars((string) ($certificadoData['NombreTestigoNovio'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><strong>Testigo de información (Novia):</strong> <?php echo htmlspecialchars((string) ($certificadoData['NombreTestigoNovia'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><strong>Nombre Parroquia:</strong> <?php echo htmlspecialchars((string) ($certificadoData['NomParroquia'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><strong>Número de Libro:</strong> <?php echo htmlspecialchars((string) ($certificadoData['NroLibro'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><strong>Folio:</strong> <?php echo htmlspecialchars((string) ($certificadoData['NroPag'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><strong>Número de Partida:</strong> <?php echo htmlspecialchars((string) ($certificadoData['NroPart'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><strong>Fecha Realización:</strong> <?php echo htmlspecialchars((string) ($certificadoData['FechaReal'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><strong>Impedimento o dispensa:</strong> <?php echo htmlspecialchars((string) ($certificadoData['ImpedimentoMat'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><strong>Filiación del novio:</strong> <?php echo htmlspecialchars((string) ($certificadoData['FiliacionNovio'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><strong>Filiación de la novia:</strong> <?php echo htmlspecialchars((string) ($certificadoData['FiliacionNovia'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><strong>Estado civil del novio antes del matrimonio:</strong> <?php echo htmlspecialchars((string) ($certificadoData['EstadoCivilNovio'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><strong>Estado civil de la novia antes del matrimonio:</strong> <?php echo htmlspecialchars((string) ($certificadoData['EstadoCivilNovia'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                        <div class="d-flex align-items-center flex-wrap gap-2">
                          <strong class="me-1">Fecha de última emisión:</strong>
                          <span><?php echo htmlspecialchars((string) $certificadoData['FechaEmision'], ENT_QUOTES, 'UTF-8'); ?></span>
                          <?php if ($partidaGuardada): ?>
                            <form method="POST" class="mb-0">
                              <input type="hidden" name="token_matrimonio" value="<?php echo htmlspecialchars($_SESSION['token_detalle_matrimonio'], ENT_QUOTES, 'UTF-8'); ?>">
                              <button type="submit" name="actualizar_fecha_emision" value="1" class="btn btn-sm <?php echo $fechaActualizada ? 'btn-success' : 'btn-outline-primary'; ?>"><i class="bi <?php echo $fechaActualizada ? 'bi-check-circle' : 'bi-calendar-check'; ?> me-1"></i><?php echo $fechaActualizada ? 'Fecha actualizada' : 'Actualizar a hoy'; ?></button>
                            </form>
                          <?php else: ?>
                            <small class="text-muted">Guarde primero los datos de la partida para cambiar la fecha de emisión.</small>
                          <?php endif; ?>
                        </div>
                        <p><strong>Presbítero Emisor:</strong> <?php echo htmlspecialchars((string) ($certificadoData['PresbiteroEm'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><strong>Párroco de la partida (Fdo):</strong> <?php echo htmlspecialchars(trim((string) ($certificadoData['CertFirmado'] ?? '')) !== '' ? $certificadoData['CertFirmado'] : ($certificadoData['PresbiteroEm'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><strong>Observaciones:</strong> <?php echo htmlspecialchars((string) ($certificadoData['Observacion'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><strong>Celebrante Presbítero:</strong> <?php echo htmlspecialchars((string) ($certificadoData['CelebrantePres'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                        <p><strong>Estado del Certificado:</strong> <?php echo htmlspecialchars((string) ($certificadoData['EstadoCert'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                      </div>

                      <form method="POST" id="formEditarMatrimonio" class="row g-3 mt-1 <?php echo $modoEdicion ? '' : 'd-none'; ?>">
                        <input type="hidden" name="token_matrimonio" value="<?php echo htmlspecialchars($_SESSION['token_detalle_matrimonio'], ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="col-sm-6">
                          <label class="form-label" for="editPadreNovio">Padre del Novio</label>
                          <input type="text" class="form-control" id="editPadreNovio" name="NombrePadreNovioManual" maxlength="100" value="<?php echo $valorMatrimonio('NombrePadreNovioManual', $certificadoData['NombrePadreNovio'] ?? ''); ?>">
                        </div>
                        <div class="col-sm-6">
                          <label class="form-label" for="editMadreNovio">Madre del Novio</label>
                          <input type="text" class="form-control" id="editMadreNovio" name="NombreMadreNovioManual" maxlength="100" value="<?php echo $valorMatrimonio('NombreMadreNovioManual', $certificadoData['NombreMadreNovio'] ?? ''); ?>">
                        </div>
                        <div class="col-sm-6">
                          <label class="form-label" for="editPadreNovia">Padre de la Novia</label>
                          <input type="text" class="form-control" id="editPadreNovia" name="NombrePadreNoviaManual" maxlength="100" value="<?php echo $valorMatrimonio('NombrePadreNoviaManual', $certificadoData['NombrePadreNovia'] ?? ''); ?>">
                        </div>
                        <div class="col-sm-6">
                          <label class="form-label" for="editMadreNovia">Madre de la Novia</label>
                          <input type="text" class="form-control" id="editMadreNovia" name="NombreMadreNoviaManual" maxlength="100" value="<?php echo $valorMatrimonio('NombreMadreNoviaManual', $certificadoData['NombreMadreNovia'] ?? ''); ?>">
                        </div>
                        <div class="col-sm-6">
                          <label class="form-label" for="editTestigoPresencial1">Testigo presencial 1</label>
                          <input type="text" class="form-control" id="editTestigoPresencial1" name="NombreTestigoPresencial1Manual" maxlength="100" value="<?php echo $valorMatrimonio('NombreTestigoPresencial1Manual', $certificadoData['NombrePadrino'] ?? ''); ?>">
                        </div>
                        <div class="col-sm-6">
                          <label class="form-label" for="editTestigoPresencial2">Testigo presencial 2</label>
                          <input type="text" class="form-control" id="editTestigoPresencial2" name="NombreTestigoPresencial2Manual" maxlength="100" value="<?php echo $valorMatrimonio('NombreTestigoPresencial2Manual', $certificadoData['NombreMadrina'] ?? ''); ?>">
                        </div>
                        <div class="col-sm-6">
                          <label class="form-label" for="editTestigoInfoNovio">Testigo de información (Novio)</label>
                          <input type="text" class="form-control" id="editTestigoInfoNovio" name="NombreTestigoInfoNovioManual" maxlength="100" value="<?php echo $valorMatrimonio('NombreTestigoInfoNovioManual', $certificadoData['NombreTestigoNovio'] ?? ''); ?>">
                        </div>
                        <div class="col-sm-6">
                          <label class="form-label" for="editTestigoInfoNovia">Testigo de información (Novia)</label>
                          <input type="text" class="form-control" id="editTestigoInfoNovia" name="NombreTestigoInfoNoviaManual" maxlength="100" value="<?php echo $valorMatrimonio('NombreTestigoInfoNoviaManual', $certificadoData['NombreTestigoNovia'] ?? ''); ?>">
                        </div>
                        <div class="col-12">
                          <label class="form-label" for="editNomParroquia">Nombre Parroquia</label>
                          <input type="text" class="form-control" id="editNomParroquia" name="NomParroquia" maxlength="50" value="<?php echo $valorMatrimonio('NomParroquia'); ?>">
                        </div>
                        <div class="col-sm-4">
                          <label class="form-label" for="editNroLibro">Número de Libro</label>
                          <input type="text" class="form-control" id="editNroLibro" name="NroLibro" maxlength="10" value="<?php echo $valorMatrimonio('NroLibro'); ?>">
                        </div>
                        <div class="col-sm-4">
                          <label class="form-label" for="editNroPag">Folio</label>
                          <input type="text" class="form-control" id="editNroPag" name="NroPag" maxlength="10" value="<?php echo $valorMatrimonio('NroPag'); ?>">
                        </div>
                        <div class="col-sm-4">
                          <label class="form-label" for="editNroPart">Número de Partida</label>
                          <input type="text" class="form-control" id="editNroPart" name="NroPart" maxlength="10" value="<?php echo $valorMatrimonio('NroPart'); ?>">
                        </div>
                        <div class="col-sm-6">
                          <label class="form-label" for="editFechaReal">Fecha Realización</label>
                          <input type="date" class="form-control" id="editFechaReal" name="FechaReal" value="<?php echo $valorMatrimonio('FechaReal'); ?>">
                        </div>
                        <div class="col-12">
                          <label class="form-label" for="editImpedimento">Impedimento o dispensa</label>
                          <input type="text" class="form-control" id="editImpedimento" name="ImpedimentoMat" maxlength="80" value="<?php echo $valorMatrimonio('ImpedimentoMat'); ?>">
                        </div>
                        <div class="col-sm-6">
                          <label class="form-label" for="editFiliacionNovio">Filiación del novio (Hijo…)</label>
                          <input type="text" class="form-control" id="editFiliacionNovio" name="FiliacionNovio" maxlength="30" value="<?php echo $valorMatrimonio('FiliacionNovio'); ?>">
                        </div>
                        <div class="col-sm-6">
                          <label class="form-label" for="editFiliacionNovia">Filiación de la novia (Hija…)</label>
                          <input type="text" class="form-control" id="editFiliacionNovia" name="FiliacionNovia" maxlength="30" value="<?php echo $valorMatrimonio('FiliacionNovia'); ?>">
                        </div>
                        <div class="col-sm-6">
                          <label class="form-label" for="editEstadoCivilNovio">Estado civil del novio antes del matrimonio *</label>
                          <select class="form-select" id="editEstadoCivilNovio" name="EstadoCivilNovio" required>
                            <option value="">Seleccione según la partida</option>
                            <option value="Soltero" <?php echo $valorMatrimonio('EstadoCivilNovio') === 'Soltero' ? 'selected' : ''; ?>>Soltero</option>
                            <option value="Viudo" <?php echo $valorMatrimonio('EstadoCivilNovio') === 'Viudo' ? 'selected' : ''; ?>>Viudo</option>
                          </select>
                        </div>
                        <div class="col-sm-6">
                          <label class="form-label" for="editEstadoCivilNovia">Estado civil de la novia antes del matrimonio *</label>
                          <select class="form-select" id="editEstadoCivilNovia" name="EstadoCivilNovia" required>
                            <option value="">Seleccione según la partida</option>
                            <option value="Soltera" <?php echo $valorMatrimonio('EstadoCivilNovia') === 'Soltera' ? 'selected' : ''; ?>>Soltera</option>
                            <option value="Viuda" <?php echo $valorMatrimonio('EstadoCivilNovia') === 'Viuda' ? 'selected' : ''; ?>>Viuda</option>
                          </select>
                        </div>
                        <div class="col-sm-6">
                          <label class="form-label" for="editPresbitero">Pbro. que certifica y firma</label>
                          <input type="text" class="form-control" id="editPresbitero" name="PresbiteroEm" maxlength="50" value="<?php echo $valorMatrimonio('PresbiteroEm'); ?>">
                        </div>
                        <div class="col-sm-6">
                          <label class="form-label" for="editCertFirmado">Párroco de la partida (Fdo)</label>
                          <input type="text" class="form-control" id="editCertFirmado" name="CertFirmado" maxlength="60" value="<?php echo $valorMatrimonio('CertFirmado'); ?>">
                        </div>
                        <div class="col-sm-6">
                          <label class="form-label" for="editCelebrantePres">Celebrante Presbítero</label>
                          <input type="text" class="form-control" id="editCelebrantePres" name="CelebrantePres" maxlength="50" value="<?php echo $valorMatrimonio('CelebrantePres'); ?>">
                        </div>
                        <div class="col-sm-6">
                          <label class="form-label" for="editObservacion">Observaciones</label>
                          <input type="text" class="form-control" id="editObservacion" name="Observacion" maxlength="80" value="<?php echo $valorMatrimonio('Observacion'); ?>">
                        </div>
                        <div class="col-12"><small class="text-muted">El PDF tachará la opción de estado civil que no corresponda. Los nombres y datos que deje vacíos se marcarán con una línea.</small></div>
                        <div class="col-12 d-flex gap-2">
                          <button type="button" id="btnCancelarMatrimonio" class="btn btn-warning-soft">Cancelar</button>
                          <button type="submit" name="guardar_datos_matrimonio" value="1" class="btn btn-success-soft"><i class="bi bi-check-circle me-1"></i>Actualizar</button>
                        </div>
                      </form>

                      <div class="col-sm-12 col-lg-12 d-flex align-items-center">
                        <div class="col-sm-4 col-lg-4">
                          <a href="VistaSolicitante.php" class="btn btn-warning-soft w-90"><i class="bi bi-arrow-bar-left"></i> Volver atras</a>
                        </div>
                        <div class="col-sm-4 col-lg-4">
                          <a href="EmitirCertificadoMatrimonio.php?CodIns=<?php echo $codIns; ?>" class="btn btn-success-soft w-90" target="_blank"> Verificar Certificado <i class="bi bi-file-earmark-check"></i></a>
                        </div>
                        <div class="col-sm-4 col-lg-4">
                          <a href="DocMatrimonio.php?CodIns=<?php echo $codIns; ?>" class="btn btn-primary-soft w-90" target="_blank"> Emitir Certificado <i class="bi bi-file-earmark-text"></i></a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <br>
              <?php else: ?>

                <h4 class="p-2">Registrar Nuevo Certificado de Matrimonio</h4>
                <form method="POST" class="row g-3 p-3">
                  <input type="hidden" name="token_matrimonio" value="<?php echo htmlspecialchars($_SESSION['token_detalle_matrimonio'], ENT_QUOTES, 'UTF-8'); ?>">
                  <div class="col-sm-6">
                    <label class="form-label" for="nuevoNroLibro">Libro de matrimonios *</label>
                    <input class="form-control" id="nuevoNroLibro" type="text" name="NroLibro" value="<?php echo $valorMatrimonio('NroLibro'); ?>" maxlength="5" required>
                  </div>
                  <div class="col-sm-6">
                    <label class="form-label" for="nuevoNroPag">Folio *</label>
                    <input class="form-control" id="nuevoNroPag" type="text" name="NroPag" value="<?php echo $valorMatrimonio('NroPag'); ?>" maxlength="5" required>
                  </div>
                  <div class="col-sm-6">
                    <label class="form-label" for="nuevoNroPart">Número de partida *</label>
                    <input class="form-control" id="nuevoNroPart" type="text" name="NroPart" value="<?php echo $valorMatrimonio('NroPart'); ?>" maxlength="5" required>
                  </div>
                  <div class="col-sm-6">
                    <label class="form-label" for="nuevoFechaReal">Fecha del matrimonio *</label>
                    <input class="form-control" id="nuevoFechaReal" type="date" name="FechaReal" value="<?php echo $valorMatrimonio('FechaReal'); ?>" required>
                  </div>
                  <div class="col-sm-6">
                    <label class="form-label" for="nuevoCelebrantePres">Sacerdote que celebra *</label>
                    <input class="form-control" id="nuevoCelebrantePres" type="text" name="CelebrantePres" value="<?php echo $valorMatrimonio('CelebrantePres'); ?>" maxlength="50" required>
                  </div>
                  <div class="col-sm-6">
                    <label class="form-label" for="nuevoImpedimentoMat">Impedimento o dispensa</label>
                    <input class="form-control" id="nuevoImpedimentoMat" type="text" name="ImpedimentoMat" value="<?php echo $valorMatrimonio('ImpedimentoMat'); ?>" maxlength="80">
                  </div>
                  <div class="col-sm-6">
                    <label class="form-label" for="nuevoFiliacionNovio">Filiación del novio (Hijo…)</label>
                    <input class="form-control" id="nuevoFiliacionNovio" type="text" name="FiliacionNovio" value="<?php echo $valorMatrimonio('FiliacionNovio'); ?>" maxlength="30">
                  </div>
                  <div class="col-sm-6">
                    <label class="form-label" for="nuevoFiliacionNovia">Filiación de la novia (Hija…)</label>
                    <input class="form-control" id="nuevoFiliacionNovia" type="text" name="FiliacionNovia" value="<?php echo $valorMatrimonio('FiliacionNovia'); ?>" maxlength="30">
                  </div>
                  <div class="col-sm-6">
                    <label class="form-label" for="nuevoEstadoCivilNovio">Estado civil del novio antes del matrimonio *</label>
                    <select class="form-select" id="nuevoEstadoCivilNovio" name="EstadoCivilNovio" required>
                      <option value="">Seleccione según la partida</option>
                      <option value="Soltero" <?php echo $valorMatrimonio('EstadoCivilNovio') === 'Soltero' ? 'selected' : ''; ?>>Soltero</option>
                      <option value="Viudo" <?php echo $valorMatrimonio('EstadoCivilNovio') === 'Viudo' ? 'selected' : ''; ?>>Viudo</option>
                    </select>
                  </div>
                  <div class="col-sm-6">
                    <label class="form-label" for="nuevoEstadoCivilNovia">Estado civil de la novia antes del matrimonio *</label>
                    <select class="form-select" id="nuevoEstadoCivilNovia" name="EstadoCivilNovia" required>
                      <option value="">Seleccione según la partida</option>
                      <option value="Soltera" <?php echo $valorMatrimonio('EstadoCivilNovia') === 'Soltera' ? 'selected' : ''; ?>>Soltera</option>
                      <option value="Viuda" <?php echo $valorMatrimonio('EstadoCivilNovia') === 'Viuda' ? 'selected' : ''; ?>>Viuda</option>
                    </select>
                  </div>
                  <div class="col-12"><small class="text-muted">El PDF tachará la opción que no corresponda. Indique el estado que tenía cada cónyuge al casarse.</small></div>
                  <div class="col-sm-6">
                    <label class="form-label" for="nuevoFechaEmision">Fecha de emisión *</label>
                    <input class="form-control" id="nuevoFechaEmision" type="date" name="FechaEmision" value="<?php echo $valorMatrimonio('FechaEmision'); ?>" required>
                  </div>
                  <div class="col-sm-6">
                    <label class="form-label" for="nuevoPresbiteroEm">Presbítero que certifica y firma *</label>
                    <input class="form-control" id="nuevoPresbiteroEm" type="text" name="PresbiteroEm" value="<?php echo $valorMatrimonio('PresbiteroEm'); ?>" maxlength="50" required>
                  </div>
                  <div class="col-sm-6">
                    <label class="form-label" for="nuevoCertFirmado">Párroco que firma la partida (Fdo)</label>
                    <input class="form-control" id="nuevoCertFirmado" type="text" name="CertFirmado" value="<?php echo $valorMatrimonio('CertFirmado'); ?>" maxlength="60">
                  </div>
                  <div class="col-sm-6">
                    <label class="form-label" for="nuevoObservacion">Observaciones</label>
                    <input class="form-control" id="nuevoObservacion" type="text" name="Observacion" value="<?php echo $valorMatrimonio('Observacion'); ?>" maxlength="80">
                  </div>
                  <div class="col-12"><small class="text-muted">Transcriba la partida original. Los padres y demás datos opcionales no consignados se marcarán con una línea.</small></div>
                  <div class="col-12"><button type="submit" name="registrar_certificado" value="1" class="btn btn-primary-soft w-100">Registrar Certificado</button></div>
                </form>
          </div>
        <?php endif; ?>
        </div>
      </div>
    </div>
  </main>

  <script>
    $(document).ready(function() {
      function obtenerEstado() {
        $.ajax({
          url: '../VerificarPersonal.php',
          method: 'GET',
          dataType: 'json',
          success: function(response) {
            if (response.Estado && response.DescripCar) {
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
  <script>
    (function () {
      var btnEditar = document.getElementById('btnEditarMatrimonio');
      var lectura = document.getElementById('detalleMatrimonioLectura');
      var formulario = document.getElementById('formEditarMatrimonio');
      var btnCancelar = document.getElementById('btnCancelarMatrimonio');
      if (!btnEditar || !lectura || !formulario) return;
      btnEditar.addEventListener('click', function () {
        lectura.classList.add('d-none');
        formulario.classList.remove('d-none');
        btnEditar.classList.add('d-none');
      });
      if (btnCancelar) {
        btnCancelar.addEventListener('click', function () {
          formulario.classList.add('d-none');
          lectura.classList.remove('d-none');
          btnEditar.classList.remove('d-none');
        });
      }
    }());
  </script>
  <script src="../assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/functions.js"></script>
  <script src="../assets/js/tema.js"></script>
</body>

</html>
