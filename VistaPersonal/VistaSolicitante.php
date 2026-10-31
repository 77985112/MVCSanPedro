<?php
require_once '../Controlador/controladorSolicitud.php';
require_once '../Controlador/ControladorPersona.php';

session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: ../logout.php");
  exit();
}
$usuario = $_SESSION['usuario'];
require_once __DIR__ . '/../Controlador/controladorAcceso.php';
ControladorAcceso::exigir('certificado');
if (empty($_SESSION['token_bautizo'])) {
  $_SESSION['token_bautizo'] = bin2hex(random_bytes(32));
}
if (empty($_SESSION['token_confirmacion'])) {
  $_SESSION['token_confirmacion'] = bin2hex(random_bytes(32));
}
if (empty($_SESSION['token_matrimonio'])) {
  $_SESSION['token_matrimonio'] = bin2hex(random_bytes(32));
}
$registroBautizo = $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_bautizo']);
$registroConfirmacion = $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_confirmacion']);
$registroMatrimonio = $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_matrimonio']);
$valorBautizo = function ($campo, $predeterminado = '') use ($registroBautizo) {
  $valor = $registroBautizo ? ($_POST[$campo] ?? '') : $predeterminado;
  return htmlspecialchars(is_scalar($valor) ? (string) $valor : '', ENT_QUOTES, 'UTF-8');
};
$valorConfirmacion = function ($campo, $predeterminado = '') use ($registroConfirmacion) {
  $valor = $registroConfirmacion ? ($_POST[$campo] ?? '') : $predeterminado;
  return htmlspecialchars(is_scalar($valor) ? (string) $valor : '', ENT_QUOTES, 'UTF-8');
};
$valorMatrimonio = function ($campo, $predeterminado = '') use ($registroMatrimonio) {
  $valor = $registroMatrimonio ? ($_POST[$campo] ?? '') : $predeterminado;
  return htmlspecialchars(is_scalar($valor) ? (string) $valor : '', ENT_QUOTES, 'UTF-8');
};
$presbiteroBautizo = ($usuario['DescripCar'] ?? '') === 'Párroco'
  ? trim(($usuario['Nombre'] ?? '') . ' ' . ($usuario['Paterno'] ?? '') . ' ' . ($usuario['Materno'] ?? '')) : '';
$presbiteroActual = $presbiteroBautizo;

$controllerPersona = new ControladorPersona();
$controllerSacramento = new ControladorInsSacramento();

$mensaje = '';
$certificadoBautizo = [];
$certificadoConfirmacion = [];
$certificadoMatrimonio = [];

$persona = null;

if (isset($_GET['ciPersona'])) {
  $ciPersona = $_GET['ciPersona'];
  if (!is_string($ciPersona) || trim($ciPersona) === '') {
    echo json_encode(['error' => 'Ingrese una identificación válida']);
    exit;
  }
  $persona = $controllerPersona->buscarPersona($ciPersona);

  if ($persona) {
    if (($_GET['datosConfirmacion'] ?? '') === '1') {
      $persona['BautizoConfirmante'] = $controllerSacramento->BuscarBautizoConfirmante($persona['CiPersona']);
    }
    echo json_encode($persona);
  } else {
    echo json_encode(['error' => 'Persona no encontrada']);
  }
  exit;
}

// AJAX persona creation is handled by ajaxCrearPersona.php



if (isset($_GET['bautizo'])) {
  $certificadoBautizo = $controllerSacramento->VerInsSacramentoBautizo($_GET['ciPersonaBus'] ?? '');
}
if (isset($_GET['confirmacion'])) {
  $certificadoConfirmacion = $controllerSacramento->VerInsSacramentoConfirmacion($_GET['ciPersonaBus'] ?? '');
}
if (isset($_GET['matrimonio'])) {
  $certificadoMatrimonio = $controllerSacramento->VerInsSacramentoMatrimonio($_GET['ciPersonaBus'] ?? '');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registrar_bautizo'])) {
  try {
    if (!is_string($_POST['token_bautizo'] ?? null) || !hash_equals($_SESSION['token_bautizo'], $_POST['token_bautizo'])) {
      throw new InvalidArgumentException('La sesión del formulario cambió. Recargue la página e intente nuevamente.');
    }
    $datosBautizo = $_POST;
    $datosBautizo['CodPer'] = $usuario['CodPer'];
    $codInsBautizo = $controllerSacramento->RegistrarBautizoCompleto($datosBautizo);
    $_SESSION['token_bautizo'] = bin2hex(random_bytes(32));
    header('Location: VistaRegistradoBautizo.php?CodIns=' . $codInsBautizo);
    exit;
  } catch (InvalidArgumentException $e) {
    $mensaje = $e->getMessage();
  } catch (Throwable $e) {
    error_log('Error al registrar certificado de bautizo: ' . $e->getMessage());
    $mensaje = 'No se pudo guardar el certificado. Revise los datos e intente nuevamente.';
  }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registrar_confirmacion'])) {
  try {
    if (!is_string($_POST['token_confirmacion'] ?? null) || !hash_equals($_SESSION['token_confirmacion'], $_POST['token_confirmacion'])) {
      throw new InvalidArgumentException('La sesión del formulario cambió. Recargue la página e intente nuevamente.');
    }
    $datosConfirmacion = $_POST;
    $datosConfirmacion['CodPer'] = $usuario['CodPer'];
    $codInsConfirmacion = $controllerSacramento->RegistrarConfirmacionCompleto($datosConfirmacion);
    $_SESSION['token_confirmacion'] = bin2hex(random_bytes(32));
    header('Location: VistaRegistradoConfirmacion.php?CodIns=' . $codInsConfirmacion);
    exit;
  } catch (InvalidArgumentException $e) {
    $mensaje = $e->getMessage();
  } catch (Throwable $e) {
    error_log('Error al registrar certificado de confirmación: ' . $e->getMessage());
    $mensaje = 'No se pudo guardar el certificado de confirmación. Revise los datos e intente nuevamente.';
  }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registrar_matrimonio'])) {
  try {
    if (!is_string($_POST['token_matrimonio'] ?? null) || !hash_equals($_SESSION['token_matrimonio'], $_POST['token_matrimonio'])) {
      throw new InvalidArgumentException('La sesión del formulario cambió. Recargue la página e intente nuevamente.');
    }
    $datosMatrimonio = $_POST;
    $datosMatrimonio['CodPer'] = $usuario['CodPer'];
    $codInsMatrimonio = $controllerSacramento->RegistrarMatrimonioCompleto($datosMatrimonio);
    $_SESSION['token_matrimonio'] = bin2hex(random_bytes(32));
    header('Location: VistaRegistradoMatrimonio.php?CodIns=' . $codInsMatrimonio);
    exit;
  } catch (InvalidArgumentException $e) {
    $mensaje = $e->getMessage();
  } catch (Throwable $e) {
    error_log('Error al registrar certificado de matrimonio: ' . $e->getMessage());
    $mensaje = 'No se pudo guardar el certificado de matrimonio. Revise los datos e intente nuevamente.';
  }
}
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

        <?php if ($mensaje): ?>
          <p role="alert"><?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>


        <div class="col-md-12 col-lg-6 vstack gap-4">
          <div class="card h-100">
            <div class="card-header d-sm-flex align-items-center text-center justify-content-sm-between border-0 pb-0">
              <h1 class="h4 card-title">Información de Certificados</h1>

              <form method="GET" class="mb-3 d-flex align-items-end">
                <div class="form-group mb-0 me-2">
                  <input type="text" class="form-control" name="ciPersonaBus" placeholder="CI, nombres, apellidos o partida" value="<?php echo htmlspecialchars($_GET['ciPersonaBus'] ?? $_GET['ciPersonaSeleccionada'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary-soft me-2" name="bautizo" href="#tab-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Bautizo">
                  <i class="bi bi-droplet"></i>
                </button>
                <button type="submit" class="btn btn-primary-soft me-2" name="confirmacion" href="#tab-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Confirmación">
                  <i class="bi bi-fire"></i>
                </button>
                <button type="submit" class="btn btn-primary-soft me-2" name="matrimonio" href="#tab-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Matrimonio">
                  <i class="bi bi-clipboard-heart"></i>
                </button>
              </form>

            </div>



            <div class="card-body">
              <ul class="nav nav-tabs nav-bottom-line mb-3">
                <li class="nav-item"> <a class="nav-link <?php echo (!$registroBautizo && !$registroConfirmacion && !$registroMatrimonio) ? 'active' : ''; ?>" data-bs-toggle="tab" href="#tab-1"> Información </a> </li>
                <li class="nav-item"> <a class="nav-link <?php echo $registroBautizo ? 'active' : ''; ?>" data-bs-toggle="tab" href="#tab-2"> Registrar Bautizo </a> </li>
                <li class="nav-item"> <a class="nav-link <?php echo $registroConfirmacion ? 'active' : ''; ?>" data-bs-toggle="tab" href="#tab-3"> Registrar Confirmación </a> </li>
                <li class="nav-item"> <a class="nav-link <?php echo $registroMatrimonio ? 'active' : ''; ?>" data-bs-toggle="tab" href="#tab-4"> Registrar Matrimonio </a> </li>
              </ul>
              <div class="tab-content">
                <div class="tab-pane <?php echo (!$registroBautizo && !$registroConfirmacion && !$registroMatrimonio) ? 'show active' : ''; ?>" id="tab-1">
                  <?php if (count($certificadoBautizo) > 0): ?>
                    <h3>Datos de Bautizo Encontradas</h3>
                    <table class="table table-hover">
                      <thead class="table-dark">
                        <tr>
                          <th scope="col">Sacramento</th>
                          <th scope="col">Nombre Bautizado</th>
                          <th scope="col">Fecha de registro</th>
                          <th scope="col">Doc</th>
                          <th scope="col">Ver</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($certificadoBautizo as $bautizo): ?>
                          <tr>
                            <td><?php echo $bautizo['DescripSac']; ?></td>
                            <td><?php echo $bautizo['NombreBautizado']; ?></td>
                            <td><?php echo $bautizo['FechaIns']; ?></td>
                            <td><a class="btn btn-primary-soft" href="registrar_documento.php?CodIns=<?php echo $bautizo['CodIns']; ?>&amp;buscar=<?php echo rawurlencode($_GET['ciPersonaBus'] ?? ''); ?>&amp;sac=bautizo"><i class="bi bi-file-earmark-text"></i></a></td>
                            <td><a class="btn btn-primary-soft" href="VistaRegistradoBautizo.php?CodIns=<?php echo $bautizo['CodIns']; ?>"><i class="bi bi-eye"></i></a></td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  <?php elseif (isset($_GET['bautizo'])): ?>
                    <div class="my-sm-5 py-sm-5 text-center">
                      <i class="display-1 text-body-secondary bi bi-calendar2-event"></i>
                      <h4 class="mt-2 mb-3 text-body">Certificado no encontrado</h4>
                    </div>
                  <?php endif; ?>

                  <?php if (count($certificadoConfirmacion) > 0): ?>
                    <h3>Datos de Confirmación Encontrados</h3>
                    <table class="table table-hover">
                      <thead class="table-dark">
                        <tr>
                          <th scope="col">Sacramento</th>
                          <th scope="col">Nombre Confirmante</th>
                          <th scope="col">Fecha de registro</th>
                          <th scope="col">Doc</th>
                          <th scope="col">Ver</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($certificadoConfirmacion as $confirmacion): ?>
                          <tr>
                            <td><?php echo $confirmacion['DescripSac']; ?></td>
                            <td><?php echo $confirmacion['Confirmante']; ?></td>
                            <td><?php echo $confirmacion['FechaIns']; ?></td>
                            <td><a class="btn btn-primary-soft" href="registrar_documento.php?CodIns=<?php echo $confirmacion['CodIns']; ?>&amp;buscar=<?php echo rawurlencode($_GET['ciPersonaBus'] ?? ''); ?>&amp;sac=confirmacion"><i class="bi bi-file-earmark-text"></i></a></td>
                            <td><a class="btn btn-primary-soft" href="VistaRegistradoConfirmacion.php?CodIns=<?php echo $confirmacion['CodIns']; ?>"><i class="bi bi-eye"></i></a></td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  <?php elseif (isset($_GET['confirmacion'])): ?>
                    <div class="my-sm-5 py-sm-5 text-center">
                      <i class="display-1 text-body-secondary bi bi-calendar2-event"></i>
                      <h4 class="mt-2 mb-3 text-body">Certificado no encontrado</h4>
                    </div>
                  <?php endif; ?>

                  <?php if (count($certificadoMatrimonio) > 0): ?>
                    <h3>Datos de Matrimonio Encontrados</h3>
                    <table class="table table-hover">
                      <thead class="table-dark">
                        <tr>
                          <th scope="col">Sacramento</th>
                          <th scope="col">Novio</th>
                          <th scope="col">Novia</th>
                          <th scope="col">Fecha</th>
                          <th scope="col">Doc</th>
                          <th scope="col">Ver</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($certificadoMatrimonio as $matrimonio): ?>
                          <tr>
                            <td><?php echo $matrimonio['DescripSac']; ?></td>
                            <td><?php echo $matrimonio['NombreNovio']; ?></td>
                            <td><?php echo $matrimonio['NombreNovia']; ?></td>
                            <td><?php echo $matrimonio['FechaIns']; ?></td>
                            <td><a class="btn btn-primary-soft" href="registrar_documento.php?CodIns=<?php echo $matrimonio['CodIns']; ?>&amp;buscar=<?php echo rawurlencode($_GET['ciPersonaBus'] ?? ''); ?>&amp;sac=matrimonio"><i class="bi bi-file-earmark-text"></i></a></td>
                            <td><a class="btn btn-primary-soft" href="VistaRegistradoMatrimonio.php?CodIns=<?php echo $matrimonio['CodIns']; ?>"><i class="bi bi-eye"></i></a></td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  <?php elseif (isset($_GET['matrimonio'])): ?>
                    <div class="my-sm-5 py-sm-5 text-center">
                      <i class="display-1 text-body-secondary bi bi-calendar2-event"></i>
                      <h4 class="mt-2 mb-3 text-body">Certificado no encontrado</h4>
                    </div>
                  <?php endif; ?>
                </div>

                <div class="tab-pane <?php echo $registroBautizo ? 'show active' : ''; ?>" id="tab-2">

                  <form method="POST" class="row g-3" id="formCertificadoBautizo" autocomplete="off" data-rellenar="<?php echo $registroBautizo ? '1' : '0'; ?>">
                    <input type="hidden" name="CodPer" value="<?php echo (int) $usuario['CodPer']; ?>">
                    <input type="hidden" name="token_bautizo" value="<?php echo htmlspecialchars($_SESSION['token_bautizo'], ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="col-12">
                      <p class="text-muted mb-1">Complete los datos de la partida para llenar el certificado PDF. Los campos con * son obligatorios.</p>
                      <h5>Datos del bautizado y su familia</h5>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                      <label class="form-label" for="bautizoCiPersonaCelebranteB">Bautizado/a (CI o partida de nacimiento) *</label>
                      <input type="text" class="form-control" id="bautizoCiPersonaCelebranteB" name="CiPersonaCelebranteB" value="<?php echo $valorBautizo('CiPersonaCelebranteB'); ?>" required maxlength="15" autocomplete="off" data-persona data-persona-bautizo data-modal-persona="MostrarModalBautizo">
                      <a href="#" class="celebranteB btn btn-sm btn-dashed rounded mt-0" data-registrar-persona><i class="bi bi-plus-circle-dotted me-1"></i>registrar nuevo</a>
                      <div class="small text-muted" data-nombre-persona aria-live="polite"></div>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                      <label class="form-label" for="bautizoCiPapa">Identificación del padre (opcional)</label>
                      <input type="text" class="form-control" id="bautizoCiPapa" name="CiPapa" value="<?php echo $valorBautizo('CiPapa'); ?>" maxlength="15" autocomplete="off" data-persona data-persona-bautizo data-modal-persona="MostrarModalDemas">
                      <a href="#" class="papacelebrante btn btn-sm btn-dashed rounded mt-0" data-registrar-persona><i class="bi bi-plus-circle-dotted me-1"></i>registrar nuevo</a>
                      <div class="small text-muted" data-nombre-persona aria-live="polite"></div>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                      <label class="form-label" for="bautizoCiMama">Identificación de la madre (opcional)</label>
                      <input type="text" class="form-control" id="bautizoCiMama" name="CiMama" value="<?php echo $valorBautizo('CiMama'); ?>" maxlength="15" autocomplete="off" data-persona data-persona-bautizo data-modal-persona="MostrarModalDemas">
                      <a href="#" class="mamacelebrante btn btn-sm btn-dashed rounded mt-0" data-registrar-persona><i class="bi bi-plus-circle-dotted me-1"></i>registrar nuevo</a>
                      <div class="small text-muted" data-nombre-persona aria-live="polite"></div>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                      <label class="form-label" for="bautizoCiPadrino">Identificación del padrino (opcional)</label>
                      <input type="text" class="form-control" id="bautizoCiPadrino" name="CiPadrino" value="<?php echo $valorBautizo('CiPadrino'); ?>" maxlength="15" autocomplete="off" data-persona data-persona-bautizo data-modal-persona="MostrarModalDemas">
                      <a href="#" class="padrinocelebrante btn btn-sm btn-dashed rounded mt-0" data-registrar-persona><i class="bi bi-plus-circle-dotted me-1"></i>registrar nuevo</a>
                      <div class="small text-muted" data-nombre-persona aria-live="polite"></div>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                      <label class="form-label" for="bautizoCiMadrina">Identificación de la madrina (opcional)</label>
                      <input type="text" class="form-control" id="bautizoCiMadrina" name="CiMadrina" value="<?php echo $valorBautizo('CiMadrina'); ?>" maxlength="15" autocomplete="off" data-persona data-persona-bautizo data-modal-persona="MostrarModalDemas">
                      <a href="#" class="madrinacelebrante btn btn-sm btn-dashed rounded mt-0" data-registrar-persona><i class="bi bi-plus-circle-dotted me-1"></i>registrar nuevo</a>
                      <div class="small text-muted" data-nombre-persona aria-live="polite"></div>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                      <label class="form-label" for="bautizoFechaNac">Fecha de nacimiento *</label>
                      <input type="date" class="form-control" id="bautizoFechaNac" name="FechaNac" value="<?php echo $valorBautizo('FechaNac'); ?>" required>
                    </div>
                    <div class="col-12"><small class="text-muted">El nombre se obtiene del CI. La fecha de nacimiento se carga automáticamente; cualquier corrección se guardará en la persona.</small></div>
                    <div class="col-sm-6 col-lg-8">
                      <label class="form-label" for="bautizoLugarNac">Lugar de nacimiento *</label>
                      <input type="text" class="form-control" id="bautizoLugarNac" name="LugarNac" value="<?php echo $valorBautizo('LugarNac', ''); ?>" required maxlength="50">
                    </div>
                    <div class="col-12 mt-4"><h5>Parroquia y partida de bautizo</h5></div>
                    <div class="col-12">
                      <label class="form-label" for="bautizoNomParroquia">Nombre de la parroquia *</label>
                      <input type="text" class="form-control" id="bautizoNomParroquia" name="NomParroquia" value="<?php echo $valorBautizo('NomParroquia', 'San Pedro de Sacaba'); ?>" required maxlength="50">
                    </div>
                    <div class="col-sm-6 col-lg-4">
                      <label class="form-label" for="bautizoNroLibro">Libro de bautismos *</label>
                      <input type="text" class="form-control" id="bautizoNroLibro" name="NroLibro" value="<?php echo $valorBautizo('NroLibro'); ?>" required maxlength="5">
                    </div>
                    <div class="col-sm-6 col-lg-4">
                      <label class="form-label" for="bautizoNroPag">Página de bautismos *</label>
                      <input type="text" class="form-control" id="bautizoNroPag" name="NroPag" value="<?php echo $valorBautizo('NroPag'); ?>" required maxlength="5">
                    </div>
                    <div class="col-sm-6 col-lg-4">
                      <label class="form-label" for="bautizoNroPart">Número de partida de bautismo *</label>
                      <input type="text" class="form-control" id="bautizoNroPart" name="NroPart" value="<?php echo $valorBautizo('NroPart'); ?>" required maxlength="5">
                    </div>
                    <div class="col-sm-6 col-lg-6">
                      <label class="form-label" for="bautizoFechaReal">Fecha del bautizo *</label>
                      <input type="date" class="form-control" id="bautizoFechaReal" name="FechaReal" max="<?php echo date('Y-m-d'); ?>" value="<?php echo $valorBautizo('FechaReal'); ?>" required>
                    </div>
                    <div class="col-sm-6 col-lg-6">
                      <label class="form-label" for="bautizoCelebrantePres">Presbítero que realizó el bautizo *</label>
                      <input type="text" class="form-control" id="bautizoCelebrantePres" name="CelebrantePres" value="<?php echo $valorBautizo('CelebrantePres'); ?>" required maxlength="50">
                    </div>
                    <div class="col-12 mt-4"><h5>Registro civil de nacimiento</h5><p class="text-muted small mb-0">Copie estos datos del certificado de nacimiento. Son distintos del libro y la partida de bautismo.</p></div>
                    <div class="col-sm-6 col-lg-4">
                      <label class="form-label" for="bautizoOficialiaCivil">Oficialía de Registro Civil *</label>
                      <input type="text" class="form-control" id="bautizoOficialiaCivil" name="OficialiaCivil" value="<?php echo $valorBautizo('OficialiaCivil'); ?>" required maxlength="30">
                    </div>
                    <div class="col-sm-6 col-lg-4">
                      <label class="form-label" for="bautizoLibroCivil">Libro del registro civil *</label>
                      <input type="text" class="form-control" id="bautizoLibroCivil" name="LibroCivil" value="<?php echo $valorBautizo('LibroCivil'); ?>" required maxlength="10">
                    </div>
                    <div class="col-sm-6 col-lg-4">
                      <label class="form-label" for="bautizoPartidaCivil">Partida del registro civil *</label>
                      <input type="text" class="form-control" id="bautizoPartidaCivil" name="PartidaCivil" value="<?php echo $valorBautizo('PartidaCivil'); ?>" required maxlength="10">
                    </div>
                    <div class="col-sm-6 col-lg-6">
                      <label class="form-label" for="bautizoFolioCivil">Folio o página del registro civil (opcional)</label>
                      <input type="text" class="form-control" id="bautizoFolioCivil" name="FolioCivil" value="<?php echo $valorBautizo('FolioCivil', ''); ?>" maxlength="10">
                    </div>
                    <div class="col-12 mt-4"><h5>Emisión y firma del certificado</h5></div>
                    <div class="col-sm-6 col-lg-4">
                      <label class="form-label" for="bautizoNroEmision">Número de emisión *</label>
                      <input type="number" class="form-control" id="bautizoNroEmision" name="NroEmision" value="<?php echo $valorBautizo('NroEmision'); ?>" required min="1" max="2147483647" step="1">
                    </div>
                    <div class="col-sm-6 col-lg-4">
                      <label class="form-label" for="bautizoFechaEmision">Fecha de emisión *</label>
                      <input type="date" class="form-control" id="bautizoFechaEmision" name="FechaEmision" max="<?php echo date('Y-m-d'); ?>" value="<?php echo $valorBautizo('FechaEmision', date('Y-m-d')); ?>" required>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                      <label class="form-label" for="bautizoPresbiteroEm">Presbítero que certifica y firma *</label>
                      <input type="text" class="form-control" id="bautizoPresbiteroEm" name="PresbiteroEm" value="<?php echo $valorBautizo('PresbiteroEm', $presbiteroBautizo); ?>" required maxlength="50">
                    </div>
                    <div class="col-12">
                      <label class="form-label" for="bautizoCertFirmado">Certificado (firmado y rubricado)</label>
                      <input type="text" class="form-control" id="bautizoCertFirmado" name="CertFirmado" value="<?php echo $valorBautizo('CertFirmado'); ?>" maxlength="60" placeholder="Pbro. que firma la línea «Certifico (Firmado y Rubricado)»">
                      <small class="text-muted">Se imprime en la línea «Certifico(Firmado y Rubricado)» del PDF, que puede ser otro presbítero. Si lo deja vacío se usa el que certifica; la firma de abajo es siempre el presbítero que certifica.</small>
                    </div>
                    <div class="col-12">
                      <label class="form-label" for="bautizoObservacion">Nota marginal / observación (opcional)</label>
                      <textarea class="form-control" id="bautizoObservacion" name="Observacion" maxlength="80" rows="2"><?php echo $valorBautizo('Observacion'); ?></textarea>
                      <small class="text-muted">Si no hay anotaciones, puede escribir «Sin anotaciones marginales».</small>
                    </div>
                    <button type="submit" class="btn btn-success-soft me-2" name="registrar_bautizo" value="1">Guardar Certificado de Bautizo</button>
                  </form>
                </div>

                <div class="tab-pane <?php echo $registroConfirmacion ? 'show active' : ''; ?>" id="tab-3">
                  <form method="POST" class="row g-3" id="formCertificadoConfirmacion" autocomplete="off" data-rellenar="<?php echo $registroConfirmacion ? '1' : '0'; ?>">
                    <input type="hidden" name="CodPer" value="<?php echo (int) $usuario['CodPer']; ?>">
                    <input type="hidden" name="token_confirmacion" value="<?php echo htmlspecialchars($_SESSION['token_confirmacion'], ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="LugarNac" value="Ninguno">

                    <div class="col-12">
                      <p class="text-muted mb-1">Complete los datos de la partida para llenar el certificado PDF. Los campos con * son obligatorios.</p>
                      <h5>Datos del confirmante, padres y padrinos</h5>
                    </div>

                    <div class="col-sm-6 col-lg-4">
                      <label class="form-label" for="confCiCelebrante">Confirmante (CI) *</label>
                      <input type="text" class="form-control" id="confCiCelebrante" name="CiPersonaCelebranteC" value="<?php echo $valorConfirmacion('CiPersonaCelebranteC'); ?>" placeholder="CI Confirmante" required minlength="4" maxlength="15" autocomplete="off" data-persona data-modal-persona="MostrarModalConfirmante">
                      <a href="#" class="celebranteC btn btn-sm btn-dashed rounded mt-0" data-registrar-persona><i class="bi bi-plus-circle-dotted me-1"></i>registrar nuevo</a>
                      <div class="small text-muted" data-nombre-persona aria-live="polite"></div>
                    </div>

                    <div class="col-sm-6 col-lg-4">
                      <label class="form-label" for="confCiPadrino">Padrino (CI, opcional)</label>
                      <input type="text" class="form-control" id="confCiPadrino" name="CiPadrino" value="<?php echo $valorConfirmacion('CiPadrino'); ?>" placeholder="CI Padrino" minlength="4" maxlength="15" autocomplete="off" data-persona data-modal-persona="MostrarModalDemas">
                      <a href="#" class="padrinocelebrante btn btn-sm btn-dashed rounded mt-0" data-registrar-persona><i class="bi bi-plus-circle-dotted me-1"></i>registrar nuevo</a>
                      <div class="small text-muted" data-nombre-persona aria-live="polite"></div>
                    </div>

                    <div class="col-sm-6 col-lg-4">
                      <label class="form-label" for="confCiMadrina">Madrina (CI, opcional)</label>
                      <input type="text" class="form-control" id="confCiMadrina" name="CiMadrina" value="<?php echo $valorConfirmacion('CiMadrina'); ?>" placeholder="CI Madrina" minlength="4" maxlength="15" autocomplete="off" data-persona data-modal-persona="MostrarModalDemas">
                      <a href="#" class="madrinacelebrante btn btn-sm btn-dashed rounded mt-0" data-registrar-persona><i class="bi bi-plus-circle-dotted me-1"></i>registrar nuevo</a>
                      <div class="small text-muted" data-nombre-persona aria-live="polite"></div>
                    </div>

                    <div class="col-sm-6">
                      <label class="form-label" for="confFechaNac">Fecha de nacimiento registrada</label>
                      <input type="date" class="form-control" id="confFechaNac" name="FechaNac" readonly aria-describedby="confNacimientoAviso">
                      <small class="text-muted" id="confNacimientoAviso" aria-live="polite">Se completa automáticamente con la fecha guardada en el registro del confirmante.</small>
                    </div>
                    <div class="col-sm-6">
                      <label class="form-label" for="confEdad">Edad al recibir la confirmación</label>
                      <input type="text" class="form-control" id="confEdad" readonly placeholder="Se calcula con las dos fechas" aria-live="polite">
                      <small class="text-muted">Se calcula automáticamente con el nacimiento y la fecha de confirmación.</small>
                    </div>
                    <div class="col-sm-6">
                      <label class="form-label" for="confCiPapa">Padre (CI, opcional)</label>
                      <input type="text" class="form-control" id="confCiPapa" name="CiPapa" value="<?php echo $valorConfirmacion('CiPapa'); ?>" placeholder="CI del padre" minlength="4" maxlength="15" autocomplete="off" data-persona data-modal-persona="MostrarModalDemas">
                      <a href="#" class="btn btn-sm btn-dashed rounded mt-0" data-registrar-persona><i class="bi bi-plus-circle-dotted me-1"></i>registrar nuevo</a>
                      <div class="small text-muted" data-nombre-persona aria-live="polite"></div>
                    </div>
                    <div class="col-sm-6">
                      <label class="form-label" for="confCiMama">Madre (CI, opcional)</label>
                      <input type="text" class="form-control" id="confCiMama" name="CiMama" value="<?php echo $valorConfirmacion('CiMama'); ?>" placeholder="CI de la madre" minlength="4" maxlength="15" autocomplete="off" data-persona data-modal-persona="MostrarModalDemas">
                      <a href="#" class="btn btn-sm btn-dashed rounded mt-0" data-registrar-persona><i class="bi bi-plus-circle-dotted me-1"></i>registrar nuevo</a>
                      <div class="small text-muted" data-nombre-persona aria-live="polite"></div>
                    </div>
                    <div class="col-12 mt-4">
                      <h5>Datos del bautismo</h5>
                      <p class="text-muted small mb-0" id="confBautizoAviso" aria-live="polite">Al ingresar el CI se buscará su bautismo en San Pedro de Sacaba. Si no está registrado, complete los datos manualmente.</p>
                    </div>
                    <div class="col-sm-6">
                      <label class="form-label" for="confParroquiaBau">Parroquia donde se bautizó *</label>
                      <input type="text" class="form-control" id="confParroquiaBau" name="ParroquiaBau" value="<?php echo $valorConfirmacion('ParroquiaBau'); ?>" maxlength="30" required>
                    </div>
                    <div class="col-sm-6">
                      <label class="form-label" for="confFechaBau">Fecha de bautizo *</label>
                      <input type="date" class="form-control" id="confFechaBau" name="FechaBau" value="<?php echo $valorConfirmacion('FechaBau'); ?>" required>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                      <label class="form-label" for="confLibroBau">Libro de bautismos (opcional)</label>
                      <input type="text" class="form-control" id="confLibroBau" name="LibroBau" value="<?php echo $valorConfirmacion('LibroBau'); ?>" maxlength="10">
                    </div>
                    <div class="col-sm-6 col-lg-4">
                      <label class="form-label" for="confPaginaBau">Página del bautismo (opcional)</label>
                      <input type="text" class="form-control" id="confPaginaBau" name="PaginaBau" value="<?php echo $valorConfirmacion('PaginaBau'); ?>" maxlength="10">
                    </div>
                    <div class="col-sm-6 col-lg-4">
                      <label class="form-label" for="confPartidaBau">Partida del bautismo (opcional)</label>
                      <input type="text" class="form-control" id="confPartidaBau" name="PartidaBau" value="<?php echo $valorConfirmacion('PartidaBau'); ?>" maxlength="10">
                    </div>

                    <div class="col-12 mt-4">
                      <h5>Partida de confirmación</h5>

                    </div>

                    <div class="col-12">
                      <label class="form-label" for="confNomParroquia">Nombre de la parroquia *</label>
                      <input type="text" class="form-control" id="confNomParroquia" name="NomParroquia" value="<?php echo $valorConfirmacion('NomParroquia', 'San Pedro de Sacaba'); ?>" required maxlength="50">
                    </div>
                    <div class="col-sm-6 col-lg-4">
                      <label class="form-label" for="confNroLibro">Libro de confirmaciones *</label>
                      <input type="text" class="form-control" id="confNroLibro" name="NroLibro" value="<?php echo $valorConfirmacion('NroLibro'); ?>" placeholder="Libro" required maxlength="5">
                    </div>
                    <div class="col-sm-6 col-lg-4">
                      <label class="form-label" for="confNroPag">Página *</label>
                      <input type="text" class="form-control" id="confNroPag" name="NroPag" value="<?php echo $valorConfirmacion('NroPag'); ?>" placeholder="Página" required maxlength="5">
                    </div>
                    <div class="col-sm-6 col-lg-4">
                      <label class="form-label" for="confNroPart">Número de partida *</label>
                      <input type="text" class="form-control" id="confNroPart" name="NroPart" value="<?php echo $valorConfirmacion('NroPart'); ?>" placeholder="Partida" required maxlength="5">
                    </div>
                    <div class="col-sm-6 col-lg-6">
                      <label class="form-label" for="confFechaReal">Fecha de la confirmación *</label>
                      <input type="date" class="form-control" id="confFechaReal" name="FechaReal" max="<?php echo date('Y-m-d'); ?>" value="<?php echo $valorConfirmacion('FechaReal'); ?>" required>
                    </div>
                    <div class="col-sm-6 col-lg-6">
                      <label class="form-label" for="confCelebrantePres">Nombre del Obispo Confirmante *</label>
                      <input type="text" class="form-control" id="confCelebrantePres" name="CelebrantePres" value="<?php echo $valorConfirmacion('CelebrantePres'); ?>" placeholder="Obispo Confirmante" required maxlength="50">
                    </div>

                    <div class="col-12 mt-4"><h5>Emisión y firma del certificado</h5></div>
                    <div class="col-sm-6 col-lg-4">
                      <label class="form-label" for="confNroEmision">Número de emisión *</label>
                      <input type="number" class="form-control" id="confNroEmision" name="NroEmision" value="<?php echo $valorConfirmacion('NroEmision'); ?>" required min="1" max="2147483647" step="1">
                    </div>
                    <div class="col-sm-6 col-lg-4">
                      <label class="form-label" for="confFechaEmision">Fecha de emisión *</label>
                      <input type="date" class="form-control" id="confFechaEmision" name="FechaEmision" max="<?php echo date('Y-m-d'); ?>" value="<?php echo $valorConfirmacion('FechaEmision', date('Y-m-d')); ?>" required>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                      <label class="form-label" for="confPresbiteroEm">El suscrito Pbro. (certifica y firma) *</label>
                      <input type="text" class="form-control" id="confPresbiteroEm" name="PresbiteroEm" value="<?php echo $valorConfirmacion('PresbiteroEm', $presbiteroActual); ?>" required maxlength="50">
                      <small class="text-muted">Este mismo nombre aparecerá en el encabezado y en la firma del certificado.</small>
                    </div>
                    <div class="col-12">
                      <label class="form-label" for="confObservacion">Observaciones (opcional)</label>
                      <textarea class="form-control" id="confObservacion" name="Observacion" maxlength="80" rows="2"><?php echo $valorConfirmacion('Observacion'); ?></textarea>
                      <small class="text-muted">Si no hay observaciones, puede escribir «Ninguna».</small>
                    </div>

                    <button type="submit" class="btn btn-success-soft me-2" name="registrar_confirmacion" value="1">Registrar Certificado de Confirmación</button>
                  </form>

                </div>

                <div class="tab-pane <?php echo $registroMatrimonio ? 'show active' : ''; ?>" id="tab-4">

                  <form method="POST" class="row g-3" id="formCertificadoMatrimonio" autocomplete="off" data-rellenar="<?php echo $registroMatrimonio ? '1' : '0'; ?>">
                    <input type="hidden" name="CodPer" value="<?php echo (int) $usuario['CodPer']; ?>">
                    <input type="hidden" name="token_matrimonio" value="<?php echo htmlspecialchars($_SESSION['token_matrimonio'], ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="LugarNac" value="Ninguno">

                    <div class="col-12">
                      <p class="text-muted mb-1">Complete los datos de la partida para llenar el certificado PDF. Los campos con * son obligatorios.</p>
                      <h5>Novios, familia y testigos</h5>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                      <label class="form-label" for="matCiNovio">CI del Novio *</label>
                      <input type="text" class="form-control" id="matCiNovio" name="CiPersonaNovio" value="<?php echo $valorMatrimonio('CiPersonaNovio'); ?>" placeholder="CI Novio" required minlength="4" maxlength="15" autocomplete="off" data-persona data-modal-persona="MostrarModalDemas">
                      <a href="#" class="novio btn btn-sm btn-dashed rounded mt-0" data-registrar-persona><i class="bi bi-plus-circle-dotted me-1"></i>registrar nuevo</a>
                      <div class="small text-muted" data-nombre-persona aria-live="polite"></div>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                      <label class="form-label" for="matCiNovia">CI de la Novia *</label>
                      <input type="text" class="form-control" id="matCiNovia" name="CiPersonaNovia" value="<?php echo $valorMatrimonio('CiPersonaNovia'); ?>" placeholder="CI Novia" required minlength="4" maxlength="15" autocomplete="off" data-persona data-modal-persona="MostrarModalDemas">
                      <a href="#" class="novia btn btn-sm btn-dashed rounded mt-0" data-registrar-persona><i class="bi bi-plus-circle-dotted me-1"></i>registrar nuevo</a>
                      <div class="small text-muted" data-nombre-persona aria-live="polite"></div>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                      <label class="form-label" for="matCiPaNovio">CI del Papá del Novio</label>
                      <input type="text" class="form-control" id="matCiPaNovio" name="CiPaNovio" value="<?php echo $valorMatrimonio('CiPaNovio'); ?>" placeholder="CI Papá Novio" minlength="4" maxlength="15" autocomplete="off" data-persona data-modal-persona="MostrarModalDemas">
                      <a href="#" class="papanovio btn btn-sm btn-dashed rounded mt-0" data-registrar-persona><i class="bi bi-plus-circle-dotted me-1"></i>registrar nuevo</a>
                      <div class="small text-muted" data-nombre-persona aria-live="polite"></div>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                      <label class="form-label" for="matCiMaNovio">CI de la Mamá del Novio</label>
                      <input type="text" class="form-control" id="matCiMaNovio" name="CiMaNovio" value="<?php echo $valorMatrimonio('CiMaNovio'); ?>" placeholder="CI Mamá Novio" maxlength="15" autocomplete="off" data-persona data-modal-persona="MostrarModalDemas">
                      <a href="#" class="mamanovio btn btn-sm btn-dashed rounded mt-0" data-registrar-persona><i class="bi bi-plus-circle-dotted me-1"></i>registrar nuevo</a>
                      <div class="small text-muted" data-nombre-persona aria-live="polite"></div>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                      <label class="form-label" for="matCiPaNovia">CI del Papá de la Novia</label>
                      <input type="text" class="form-control" id="matCiPaNovia" name="CiPaNovia" value="<?php echo $valorMatrimonio('CiPaNovia'); ?>" placeholder="CI Papá Novia" maxlength="15" autocomplete="off" data-persona data-modal-persona="MostrarModalDemas">
                      <a href="#" class="papanovia btn btn-sm btn-dashed rounded mt-0" data-registrar-persona><i class="bi bi-plus-circle-dotted me-1"></i>registrar nuevo</a>
                      <div class="small text-muted" data-nombre-persona aria-live="polite"></div>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                      <label class="form-label" for="matCiMaNovia">CI de la Mamá de la Novia</label>
                      <input type="text" class="form-control" id="matCiMaNovia" name="CiMaNovia" value="<?php echo $valorMatrimonio('CiMaNovia'); ?>" placeholder="CI Mamá Novia" maxlength="15" autocomplete="off" data-persona data-modal-persona="MostrarModalDemas">
                      <a href="#" class="mamanovia btn btn-sm btn-dashed rounded mt-0" data-registrar-persona><i class="bi bi-plus-circle-dotted me-1"></i>registrar nuevo</a>
                      <div class="small text-muted" data-nombre-persona aria-live="polite"></div>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                      <label class="form-label" for="matCiPadrino">CI del Testigo presencial 1</label>
                      <input type="text" class="form-control" id="matCiPadrino" name="CiPadrino" value="<?php echo $valorMatrimonio('CiPadrino'); ?>" placeholder="CI Testigo presencial" maxlength="15" autocomplete="off" data-persona data-modal-persona="MostrarModalDemas">
                      <a href="#" class="padrinocelebrante btn btn-sm btn-dashed rounded mt-0" data-registrar-persona><i class="bi bi-plus-circle-dotted me-1"></i>registrar nuevo</a>
                      <div class="small text-muted" data-nombre-persona aria-live="polite"></div>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                      <label class="form-label" for="matCiMadrina">CI del Testigo presencial 2</label>
                      <input type="text" class="form-control" id="matCiMadrina" name="CiMadrina" value="<?php echo $valorMatrimonio('CiMadrina'); ?>" placeholder="CI Testigo presencial" maxlength="15" autocomplete="off" data-persona data-modal-persona="MostrarModalDemas">
                      <a href="#" class="madrinacelebrante btn btn-sm btn-dashed rounded mt-0" data-registrar-persona><i class="bi bi-plus-circle-dotted me-1"></i>registrar nuevo</a>
                      <div class="small text-muted" data-nombre-persona aria-live="polite"></div>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                      <label class="form-label" for="matCiTestigoNovio">CI del Testigo de información 1 *</label>
                      <input type="text" class="form-control" id="matCiTestigoNovio" name="CiTestigoNovio" value="<?php echo $valorMatrimonio('CiTestigoNovio'); ?>" placeholder="CI Testigo" required minlength="4" maxlength="15" autocomplete="off" data-persona data-modal-persona="MostrarModalDemas">
                      <a href="#" class="testigonovio btn btn-sm btn-dashed rounded mt-0" data-registrar-persona><i class="bi bi-plus-circle-dotted me-1"></i>registrar nuevo</a>
                      <div class="small text-muted" data-nombre-persona aria-live="polite"></div>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                      <label class="form-label" for="matCiTestigoNovia">CI del Testigo de información 2 *</label>
                      <input type="text" class="form-control" id="matCiTestigoNovia" name="CiTestigoNovia" value="<?php echo $valorMatrimonio('CiTestigoNovia'); ?>" placeholder="CI Testigo" required minlength="4" maxlength="15" autocomplete="off" data-persona data-modal-persona="MostrarModalDemas">
                      <a href="#" class="testigonovia btn btn-sm btn-dashed rounded mt-0" data-registrar-persona><i class="bi bi-plus-circle-dotted me-1"></i>registrar nuevo</a>
                      <div class="small text-muted" data-nombre-persona aria-live="polite"></div>
                    </div>

                    <div class="col-12 mt-4">
                      <h5>Partida de matrimonio</h5>
                      <p class="text-muted small mb-0">Para emitir el PDF deberá registrar además los documentos de bautizo del novio y de la novia (botón «Doc» de la lista) con su parroquia y fecha de bautizo.</p>
                    </div>

                    <div class="col-sm-6 col-lg-4">
                      <label class="form-label" for="matNroLibro">Libro de matrimonios *</label>
                      <input type="text" class="form-control" id="matNroLibro" name="NroLibro" value="<?php echo $valorMatrimonio('NroLibro'); ?>" placeholder="Libro" required maxlength="5">
                    </div>
                    <div class="col-sm-6 col-lg-4">
                      <label class="form-label" for="matNroPag">Folio *</label>
                      <input type="text" class="form-control" id="matNroPag" name="NroPag" value="<?php echo $valorMatrimonio('NroPag'); ?>" placeholder="Folio" required maxlength="5">
                    </div>
                    <div class="col-sm-6 col-lg-4">
                      <label class="form-label" for="matNroPart">Número de partida *</label>
                      <input type="text" class="form-control" id="matNroPart" name="NroPart" value="<?php echo $valorMatrimonio('NroPart'); ?>" placeholder="Partida" required maxlength="5">
                    </div>
                    <div class="col-sm-6 col-lg-6">
                      <label class="form-label" for="matFechaReal">Fecha del matrimonio *</label>
                      <input type="date" class="form-control" id="matFechaReal" name="FechaReal" max="<?php echo date('Y-m-d'); ?>" value="<?php echo $valorMatrimonio('FechaReal'); ?>" required>
                    </div>
                    <div class="col-sm-6 col-lg-6">
                      <label class="form-label" for="matCelebrantePres">Sacerdote que celebra *</label>
                      <input type="text" class="form-control" id="matCelebrantePres" name="CelebrantePres" value="<?php echo $valorMatrimonio('CelebrantePres'); ?>" placeholder="Sacerdote que celebra" required maxlength="50">
                    </div>

                    <div class="col-12">
                      <label class="form-label" for="matImpedimento">Impedimento o dispensa</label>
                      <input type="text" class="form-control" id="matImpedimento" name="ImpedimentoMat" value="<?php echo $valorMatrimonio('ImpedimentoMat'); ?>" maxlength="80">
                      <small class="text-muted">Transcriba lo que dice la partida; escriba «Ninguno» solo si corresponde.</small>
                    </div>
                    <div class="col-sm-6">
                      <label class="form-label" for="matFiliacionNovio">Filiación del novio (Hijo…)</label>
                      <input type="text" class="form-control" id="matFiliacionNovio" name="FiliacionNovio" value="<?php echo $valorMatrimonio('FiliacionNovio'); ?>" maxlength="30">
                    </div>
                    <div class="col-sm-6">
                      <label class="form-label" for="matFiliacionNovia">Filiación de la novia (Hija…)</label>
                      <input type="text" class="form-control" id="matFiliacionNovia" name="FiliacionNovia" value="<?php echo $valorMatrimonio('FiliacionNovia'); ?>" maxlength="30">
                    </div>
                    <div class="col-sm-6">
                      <label class="form-label" for="matEstadoCivilNovio">Estado civil del novio antes del matrimonio *</label>
                      <select class="form-select" id="matEstadoCivilNovio" name="EstadoCivilNovio" required>
                        <option value="">Seleccione según la partida</option>
                        <option value="Soltero" <?php echo $valorMatrimonio('EstadoCivilNovio') === 'Soltero' ? 'selected' : ''; ?>>Soltero</option>
                        <option value="Viudo" <?php echo $valorMatrimonio('EstadoCivilNovio') === 'Viudo' ? 'selected' : ''; ?>>Viudo</option>
                      </select>
                    </div>
                    <div class="col-sm-6">
                      <label class="form-label" for="matEstadoCivilNovia">Estado civil de la novia antes del matrimonio *</label>
                      <select class="form-select" id="matEstadoCivilNovia" name="EstadoCivilNovia" required>
                        <option value="">Seleccione según la partida</option>
                        <option value="Soltera" <?php echo $valorMatrimonio('EstadoCivilNovia') === 'Soltera' ? 'selected' : ''; ?>>Soltera</option>
                        <option value="Viuda" <?php echo $valorMatrimonio('EstadoCivilNovia') === 'Viuda' ? 'selected' : ''; ?>>Viuda</option>
                      </select>
                    </div>
                    <div class="col-12"><small class="text-muted">El PDF tachará la opción que no corresponda. Indique el estado que tenía cada cónyuge al casarse.</small></div>
                    <div class="col-12"><small class="text-muted">Copie la filiación de la partida. Los padres y demás datos opcionales no consignados se marcarán con una línea, conservando los puntos de la plantilla. Las edades se calculan con el nacimiento guardado en Persona y la fecha del matrimonio.</small></div>

                    <div class="col-12 mt-4"><h5>Emisión y firma del certificado</h5></div>
                    <div class="col-sm-6">
                      <label class="form-label" for="matFechaEmision">Fecha de emisión *</label>
                      <input type="date" class="form-control" id="matFechaEmision" name="FechaEmision" max="<?php echo date('Y-m-d'); ?>" value="<?php echo $valorMatrimonio('FechaEmision', date('Y-m-d')); ?>" required>
                    </div>
                    <div class="col-sm-6">
                      <label class="form-label" for="matPresbiteroEm">Presbítero que certifica y firma *</label>
                      <input type="text" class="form-control" id="matPresbiteroEm" name="PresbiteroEm" value="<?php echo $valorMatrimonio('PresbiteroEm', $presbiteroActual); ?>" required maxlength="50">
                    </div>
                    <div class="col-12">
                      <label class="form-label" for="matCertFirmado">Párroco que firma la partida (Fdo)</label>
                      <input type="text" class="form-control" id="matCertFirmado" name="CertFirmado" value="<?php echo $valorMatrimonio('CertFirmado'); ?>" maxlength="60" placeholder="Párroco que figura en la partida original">
                      <small class="text-muted">Se imprime en la línea «De que doy de yo el Párroco (Fdo)». Puede ser otro presbítero; si lo deja vacío se usa el que certifica.</small>
                    </div>
                    <div class="col-12">
                      <label class="form-label" for="matObservacion">Observaciones (opcional)</label>
                      <textarea class="form-control" id="matObservacion" name="Observacion" maxlength="80" rows="2"><?php echo $valorMatrimonio('Observacion'); ?></textarea>
                      <small class="text-muted">Transcriba las observaciones de la partida. Si no se consigna ese dato, se marcará con una línea.</small>
                    </div>

                    <button type="submit" class="btn btn-success-soft me-2" name="registrar_matrimonio" value="1">Registrar Certificado de Matrimonio</button>
                  </form>

                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
  </main>

  <div class="modal fade modal-xl" id="MostrarModalDemas" tabindex="-1" aria-labelledby="MostrarModalDemas" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addModalLabel">Agregar Nueva Persona</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form method="POST" class="row g-3" id="formModalDemas">
            <input type="hidden" name="redirigir" value="bautizo">
            <div class="form-group col-sm-1 col-lg-2">
              <label for="CiPersona">CiPersona:</label>
              <input type="text" class="form-control" name="CiPersona" placeholder="CI Persona" required minlength="4" maxlength="12">
            </div>
            <div class="form-group col-sm-2 col-lg-3">
              <label for="Nombre">Nombre:</label>
              <input type="text" class="form-control" name="Nombre" placeholder="Nombre Completo"
                required>
            </div>
            <div class="form-group col-sm-2 col-lg-2">
              <label for="ApPaterno">Apellido Paterno:</label>
              <input type="text" class="form-control" name="ApPaterno" placeholder="Apellido Paterno"
                required>
            </div>
            <div class="form-group col-sm-2 col-lg-2">
              <label for="ApMaterno">Apellido Materno:</label>
              <input type="text" class="form-control" name="ApMaterno" placeholder="Apellido Materno">
            </div>
            <div class="form-group col-sm-2 col-lg-2">
              <label class="form-label">Sexo:</label>
              <div>
                <input type="radio" name="Sexo" value="Varon" checked>
                <label for="varon">Varon</label>
                &nbsp;&nbsp;&nbsp;
                <input type="radio" name="Sexo" value="Mujer">
                <label for="mujer">Mujer</label>
              </div>
            </div>
            <br>
            <div class="form-group col-lg-2">
              <label for="FechaNac">Fecha de Nacimiento:</label>
              <input type="date" class="form-control" name="FechaNac" id="FechaNacDemas" required>
            </div>
            <div class="form-group col-lg-4">
              <label for="Direccion">Dirección:</label>
              <input type="text" class="form-control" name="Direccion" placeholder="Dirección" autocomplete="new-password">
            </div>
            <div class="form-group col-lg-3">
              <label for="Contacto">Contacto:</label>
              <input type="text" class="form-control" name="Contacto" placeholder="Número de contacto" autocomplete="new-password">
            </div>
            <div class="form-group col-lg-4">
              <label for="Correo">Correo electrónico:</label>
              <input type="email" class="form-control" name="Correo" placeholder="Correo electrónico (opcional)" autocomplete="new-password">
            </div>
            <div class="form-group col-lg-3">
              <label for="Estado_per">Estado Civil:</label>
              <select name="Estado_per" class="form-control" required>
                <option value="Soltero">Soltero</option>
                <option value="Soltera">Soltera</option>
                <option value="Casado">Casado</option>
                <option value="Casada">Casada</option>
                <option value="Viudo">Viudo</option>
                <option value="Viuda">Viuda</option>
              </select>
            </div><br>
            <button type="submit" name="crear" class="btn btn-success-soft">Registrar</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <div class="modal fade modal-xl" id="MostrarModalBautizo" tabindex="-1" aria-labelledby="MostrarModalBautizo" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addModalLabel">Agregar Nueva Persona</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form method="POST" class="row g-3" id="formModalBautizo">
            <div class="form-group col-sm-1 col-lg-2">
              <label for="CiPersona">CI:</label>
              <input type="text" class="form-control" name="CiPersona" placeholder="N° CI" minlength="4" maxlength="15">
            </div>
            <div class="form-group col-sm-2 col-lg-3">
              <label for="Nombre">Nombre:</label>
              <input type="text" class="form-control" name="Nombre" placeholder="Nombre Completo"
                required>
            </div>
            <div class="form-group col-sm-2 col-lg-2">
              <label for="ApPaterno">Apellido Paterno:</label>
              <input type="text" class="form-control" name="ApPaterno" placeholder="Apellido Paterno"
                required>
            </div>
            <div class="form-group col-sm-2 col-lg-2">
              <label for="ApMaterno">Apellido Materno:</label>
              <input type="text" class="form-control" name="ApMaterno" placeholder="Apellido Materno">
            </div>
            <div class="form-group col-sm-2 col-lg-2">
              <label class="form-label">Sexo:</label>
              <div>
                <input type="radio" name="Sexo" value="Varon" checked>
                <label for="varon">Varon</label>
                &nbsp;&nbsp;&nbsp;
                <input type="radio" name="Sexo" value="Mujer">
                <label for="mujer">Mujer</label>
              </div>
            </div>
            <br>
            <div class="form-group col-lg-12">
              <label class="form-label text-muted">Si no tiene CI, complete los datos del Certificado de Nacimiento:</label>
            </div>
            <div class="form-group col-sm-2 col-lg-2">
              <label for="Oficialia">Oficialía:</label>
              <input type="text" class="form-control" name="Oficialia" placeholder="N° Oficialía">
            </div>
            <div class="form-group col-sm-2 col-lg-2">
              <label for="Libro">Libro:</label>
              <input type="text" class="form-control" name="Libro" placeholder="N° Libro">
            </div>
            <div class="form-group col-sm-2 col-lg-2">
              <label for="Partida">Partida:</label>
              <input type="text" class="form-control" name="Partida" placeholder="N° Partida">
            </div>
            <div class="form-group col-sm-2 col-lg-2">
              <label for="Folio">Folio:</label>
              <input type="text" class="form-control" name="Folio" placeholder="N° Folio">
            </div>
            <br>
            <div class="form-group col-lg-2">
              <label for="FechaNac">Fecha de Nacimiento:</label>
              <input type="date" class="form-control" name="FechaNac" id="FechaNacB" max="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="form-group col-lg-4">
              <label for="Direccion">Dirección:</label>
              <input type="text" class="form-control" name="Direccion" placeholder="Dirección" autocomplete="new-password">
            </div>
            <div class="form-group col-lg-3">
              <label for="Contacto">Contacto:</label>
              <input type="text" class="form-control" name="Contacto" placeholder="Número de contacto" autocomplete="new-password">
            </div>
            <div class="form-group col-lg-4">
              <label for="Correo">Correo electrónico:</label>
              <input type="email" class="form-control" name="Correo" placeholder="Correo electrónico (opcional)" autocomplete="new-password">
            </div><br>
            <button type="submit" name="crear" class="btn btn-success-soft">Registrar</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <div class="modal fade modal-xl" id="MostrarModalConfirmante" tabindex="-1" aria-labelledby="MostrarModalConfirmante" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addModalLabel">Agregar Nueva Persona</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form method="POST" class="row g-3" id="formModalConfirmante">
            <div class="form-group col-sm-1 col-lg-2">
              <label for="CiPersona">CiPersona:</label>
              <input type="text" class="form-control" name="CiPersona" placeholder="CI Persona" required minlength="4" maxlength="12">
            </div>
            <div class="form-group col-sm-2 col-lg-3">
              <label for="Nombre">Nombre:</label>
              <input type="text" class="form-control" name="Nombre" placeholder="Nombre Completo"
                required>
            </div>
            <div class="form-group col-sm-2 col-lg-2">
              <label for="ApPaterno">Apellido Paterno:</label>
              <input type="text" class="form-control" name="ApPaterno" placeholder="Apellido Paterno"
                required>
            </div>
            <div class="form-group col-sm-2 col-lg-2">
              <label for="ApMaterno">Apellido Materno:</label>
              <input type="text" class="form-control" name="ApMaterno" placeholder="Apellido Materno">
            </div>
            <div class="form-group col-sm-2 col-lg-2">
              <label class="form-label">Sexo:</label>
              <div>
                <input type="radio" name="Sexo" value="Varon" checked>
                <label for="varon">Varon</label>
                &nbsp;&nbsp;&nbsp;
                <input type="radio" name="Sexo" value="Mujer">
                <label for="mujer">Mujer</label>
              </div>
            </div>
            <br>
            <div class="form-group col-lg-2">
              <label for="FechaNac">Fecha de Nacimiento:</label>
              <input type="date" class="form-control" name="FechaNac" id="FechaNacC" max="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="form-group col-lg-4">
              <label for="Direccion">Dirección:</label>
              <input type="text" class="form-control" name="Direccion" placeholder="Dirección" autocomplete="new-password">
            </div>
            <div class="form-group col-lg-3">
              <label for="Contacto">Contacto:</label>
              <input type="text" class="form-control" name="Contacto" placeholder="Número de contacto" autocomplete="new-password">
            </div>
            <div class="form-group col-lg-4">
              <label for="Correo">Correo electrónico:</label>
              <input type="email" class="form-control" name="Correo" placeholder="Correo electrónico (opcional)" autocomplete="new-password">
            </div><br>
            <button type="submit" name="crear" class="btn btn-success-soft">Registrar</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <script>

    document.getElementById('toggleFormBtn')?.addEventListener('click', function() {
      const formReserva = document.getElementById('formReservaBautizo');

      if (formReserva.style.display === 'none' || formReserva.style.display === '') {
        formReserva.style.display = 'block';
      } else {
        formReserva.style.display = 'none';
      }
    });

    document.getElementById('toggleFormBtn2')?.addEventListener('click', function() {
      const formReserva = document.getElementById('formReservaMatrimonio');

      if (formReserva.style.display === 'none' || formReserva.style.display === '') {
        formReserva.style.display = 'block';
      } else {
        formReserva.style.display = 'none';
      }
    });
    document.getElementById('toggleFormBtn3')?.addEventListener('click', function() {
      const formReserva = document.getElementById('formReservaMisa');

      if (formReserva.style.display === 'none' || formReserva.style.display === '') {
        formReserva.style.display = 'block';
      } else {
        formReserva.style.display = 'none';
      }
    });
  </script>

  <script>
    $(document).ready(function() {
      var urlParams = new URLSearchParams(window.location.search);
      var tab = urlParams.get('tab');
      if (tab) {
        var tabId = '';
        if (tab === 'bautizo') tabId = '#tab-2';
        else if (tab === 'confirmacion') tabId = '#tab-3';
        else if (tab === 'matrimonio') tabId = '#tab-4';
        
        if (tabId) {
          $('a[href="' + tabId + '"]').tab('show');
        }
      }

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
    function submitPersonaModal(formId, modalId) {
      var form = document.getElementById(formId);
      if (!form) return;
      
      form.addEventListener('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(form);
        formData.append('crear', '1');
        
        fetch('../Controlador/ajaxCrearPersona.php', {
          method: 'POST',
          body: formData
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
          if (data.success) {
            document.dispatchEvent(new CustomEvent('persona-certificado-registrada', { detail: { ciPersona: data.ciPersona, modalId: modalId } }));
            alert(data.message);
            form.reset();
            var modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
            if (modal) modal.hide();
          } else {
            alert('Error: ' + data.message);
          }
        })
        .catch(function(error) {
          alert('Error de conexión: ' + error);
        });
      });
    }
    
    document.addEventListener('DOMContentLoaded', function() {
      submitPersonaModal('formModalDemas', 'MostrarModalDemas');
      submitPersonaModal('formModalBautizo', 'MostrarModalBautizo');
      submitPersonaModal('formModalConfirmante', 'MostrarModalConfirmante');
    });
  </script>
  <script>
    const todayD = new Date();
    const maxDateD = new Date(todayD.getFullYear() - 18, todayD.getMonth(), todayD.getDate());
    const yearD = maxDateD.getFullYear();
    const monthD = String(maxDateD.getMonth() + 1).padStart(2, '0');
    const dayD = String(maxDateD.getDate()).padStart(2, '0');
    document.getElementById('FechaNacDemas').setAttribute('max', `${yearD}-${monthD}-${dayD}`);

    const todayB = new Date();
    const maxDateB = new Date(todayB.getFullYear(), todayB.getMonth(), todayB.getDate());
    const yearB = maxDateB.getFullYear();
    const monthB = String(maxDateB.getMonth() + 1).padStart(2, '0');
    const dayB = String(maxDateB.getDate()).padStart(2, '0');
    document.getElementById('FechaNacB').setAttribute('max', `${yearB}-${monthB}-${dayB}`);

    const todayC = new Date();
    const maxDateC = new Date(todayC.getFullYear() - 13, todayC.getMonth(), todayC.getDate());
    const yearC = maxDateC.getFullYear();
    const monthC = String(maxDateC.getMonth() + 1).padStart(2, '0');
    const dayC = String(maxDateC.getDate()).padStart(2, '0');
    document.getElementById('FechaNacC').setAttribute('max', `${yearC}-${monthC}-${dayC}`);
  </script>
  <script src="../assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/certificado-persona.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/certificado-persona.js'); ?>"></script>
  <script src="../assets/js/functions.js"></script>
  <script src="../assets/js/tema.js"></script>
</body>

</html>
