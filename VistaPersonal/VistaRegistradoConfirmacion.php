<?php
require_once '../Controlador/controladorSolicitud.php';

session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: ../logout.php");
  exit();
}
if (empty($_SESSION['token_detalle_confirmacion'])) {
  $_SESSION['token_detalle_confirmacion'] = bin2hex(random_bytes(32));
}

$usuario = $_SESSION['usuario'];
require_once __DIR__ . '/../Controlador/controladorAcceso.php';
ControladorAcceso::exigir('certificado');
$controller = new ControladorInsSacramento();
$certificadoData = [];
$antecedenteBautizo = [];
$edadConfirmacion = '';
$mensaje = '';
$mensajeEdicion = isset($_GET['actualizado']) ? 'Datos del certificado actualizados correctamente.' : '';
$modoEdicion = false;

if (isset($_GET['CodIns'])) {
  $codIns = $_GET['CodIns'];
  $certificadoData = $controller->VerCertificadoConfirmacion($codIns);
  if ($certificadoData) {
    $antecedenteBautizo = $controller->VerCertificadoExtraC($certificadoData['CiPersona'], 'Celebrante', $codIns) ?: [];
    $nacimiento = DateTimeImmutable::createFromFormat('!Y-m-d', (string) ($certificadoData['FechaNac'] ?? ''));
    $confirmacion = DateTimeImmutable::createFromFormat('!Y-m-d', (string) ($certificadoData['FechaReal'] ?? ''));
    if ($nacimiento && $confirmacion && $nacimiento->format('Y-m-d') === $certificadoData['FechaNac']
        && $confirmacion->format('Y-m-d') === $certificadoData['FechaReal'] && $nacimiento <= $confirmacion) {
      $edadConfirmacion = $nacimiento->diff($confirmacion)->y . ' años';
    }
  }
}
$partidaGuardada = $certificadoData
  && trim((string) ($certificadoData['NroLibro'] ?? '')) !== ''
  && trim((string) ($certificadoData['NroPag'] ?? '')) !== ''
  && trim((string) ($certificadoData['NroPart'] ?? '')) !== '';

$padreMostrar = trim((string) ($certificadoData['NombrePadreManual'] ?? '')) !== ''
  ? $certificadoData['NombrePadreManual'] : ($certificadoData['NombrePadre'] ?? '');
$madreMostrar = trim((string) ($certificadoData['NombreMadreManual'] ?? '')) !== ''
  ? $certificadoData['NombreMadreManual'] : ($certificadoData['NombreMadre'] ?? '');
$padrinoMostrar = trim((string) ($certificadoData['NombrePadrinoManual'] ?? '')) !== ''
  ? $certificadoData['NombrePadrinoManual'] : ($certificadoData['NombrePadrino'] ?? '');
$madrinaMostrar = trim((string) ($certificadoData['NombreMadrinaManual'] ?? '')) !== ''
  ? $certificadoData['NombreMadrinaManual'] : ($certificadoData['NombreMadrina'] ?? '');
$parroquiaBauMostrar = $antecedenteBautizo['ParroquiaBau'] ?? '';
$fechaBauMostrar = $antecedenteBautizo['FechaBau'] ?? '';
$presbiteroMostrar = (string) ($certificadoData['PresbiteroEm'] ?? '');
$celebrantePresMostrar = (string) ($certificadoData['CelebrantePres'] ?? '');
$observacionMostrar = (string) ($certificadoData['Observacion'] ?? '');
$valorFormulario = function ($campo, $actual) {
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_datos_confirmacion'])) {
    return $_POST[$campo] ?? '';
  }
  return $actual;
};

$mensajeEmision = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cambiar_emision'])) {
  try {
    $nroEmision = (string) ($_POST['NroEmision'] ?? '');
    $controller->ActualizarNroEmision((int) ($_POST['CodIns'] ?? ($codIns ?? 0)), $nroEmision);
    $certificadoData['NroEmision'] = $nroEmision;
    $mensajeEmision = 'ok';
  } catch (Throwable $e) {
    $mensajeEmision = $e->getMessage();
  }
}

$mensajeFecha = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['actualizar_fecha_emision'])) {
  try {
    if (!$partidaGuardada) {
      throw new InvalidArgumentException('Guarde primero los datos de la partida.');
    }
    $controller->ActualizarFechaEmision((int) ($codIns ?? 0));
    $certificadoData['FechaEmision'] = date('Y-m-d');
    $mensajeFecha = 'ok';
  } catch (Throwable $e) {
    $mensajeFecha = $e->getMessage();
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_datos_confirmacion'])) {
  try {
    if (!is_string($_POST['token_confirmacion'] ?? null) || !hash_equals($_SESSION['token_detalle_confirmacion'], $_POST['token_confirmacion'])) {
      throw new InvalidArgumentException('Recargue la página antes de guardar.');
    }
    $controller->ActualizarDatosConfirmacion((int) ($codIns ?? 0), $_POST);
    $_SESSION['token_detalle_confirmacion'] = bin2hex(random_bytes(32));
    header('Location: VistaRegistradoConfirmacion.php?CodIns=' . (int) $codIns . '&actualizado=1');
    exit;
  } catch (InvalidArgumentException $e) {
    $mensajeEdicion = $e->getMessage();
    $modoEdicion = true;
  } catch (Throwable $e) {
    error_log('Error al actualizar datos de confirmación: ' . $e->getMessage());
    $mensajeEdicion = 'No se pudieron guardar los datos. Intente nuevamente.';
    $modoEdicion = true;
  }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registrar_certificado'])) {
  if ($controller->RegistrarCertificado($_POST)) {
    $mensaje = "Certificado registrado con éxito.";
  } else {
    $mensaje = "Error al registrar el certificado.";
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

        <div class="col-md-12 col-lg-6 vstack gap-4">
          <div class="card h-100">

            <?php if ($certificadoData): ?>
              <div class="mx-auto bg-mode shadow rounded p-4 mt-2 col-sm-10 col-lg-10">
                <div class="row align-items-end g-4">
                  <div class="col-sm-12 col-lg-12">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                      <h3 class="mb-0">Información detallada del Certificado</h3>
                      <button type="button" id="btnEditarConfirmacion" class="btn btn-sm btn-primary-soft <?php echo $modoEdicion ? 'd-none' : ''; ?>"><i class="bi bi-pencil-square me-1"></i>Editar</button>
                    </div>
                    <?php if ($mensajeEdicion): ?>
                      <div class="alert alert-<?php echo $modoEdicion ? 'danger' : 'success'; ?> py-2 mt-2 mb-2" role="alert">
                        <?php echo htmlspecialchars($mensajeEdicion, ENT_QUOTES, 'UTF-8'); ?>
                      </div>
                    <?php endif; ?>
                    <div id="detalleConfirmacionLectura" class="<?php echo $modoEdicion ? 'd-none' : ''; ?>">
                      <p><strong>Confirmante:</strong> <?php echo $certificadoData['Celebrante']; ?>(<?php echo $certificadoData['CiPersona']; ?>)</p>
                      <p><strong>Fecha de nacimiento:</strong> <?php echo htmlspecialchars((string) ($certificadoData['FechaNac'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                      <p><strong>Edad al recibir la confirmación:</strong> <?php echo htmlspecialchars((string) ($edadConfirmacion), ENT_QUOTES, 'UTF-8'); ?></p>
                      <p><strong>Padre:</strong> <?php echo htmlspecialchars((string) $padreMostrar, ENT_QUOTES, 'UTF-8'); ?></p>
                      <p><strong>Madre:</strong> <?php echo htmlspecialchars((string) $madreMostrar, ENT_QUOTES, 'UTF-8'); ?></p>
                      <p><strong>Parroquia donde se bautizó:</strong> <?php echo htmlspecialchars((string) $parroquiaBauMostrar, ENT_QUOTES, 'UTF-8'); ?></p>
                      <p><strong>Fecha de bautizo:</strong> <?php echo htmlspecialchars((string) $fechaBauMostrar, ENT_QUOTES, 'UTF-8'); ?></p>
                      <p><strong>Padrino:</strong> <?php echo htmlspecialchars((string) $padrinoMostrar, ENT_QUOTES, 'UTF-8'); ?></p>
                      <p><strong>Madrina:</strong> <?php echo htmlspecialchars((string) $madrinaMostrar, ENT_QUOTES, 'UTF-8'); ?></p>
                      <p><strong>Nombre Parroquia:</strong> <?php echo $certificadoData['NomParroquia']; ?></p>
                      <p><strong>Número de Libro:</strong> <?php echo $certificadoData['NroLibro']; ?></p>
                      <p><strong>Número de Página:</strong> <?php echo $certificadoData['NroPag']; ?></p>
                      <p><strong>Número de Partida:</strong> <?php echo $certificadoData['NroPart']; ?></p>
                      <p><strong>Lugar de Nacimiento:</strong> <?php echo $certificadoData['LugarNac']; ?></p>
                      <p><strong>Fecha Real:</strong> <?php echo htmlspecialchars((string) ($certificadoData['FechaReal'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                      <p><strong>El suscrito Pbro. (certifica y firma):</strong> <?php echo htmlspecialchars($presbiteroMostrar, ENT_QUOTES, 'UTF-8'); ?></p>
                      <p><strong>Observaciones:</strong> <?php echo htmlspecialchars($observacionMostrar, ENT_QUOTES, 'UTF-8'); ?></p>
                      <p><strong>Celebrante Presbítero:</strong> <?php echo htmlspecialchars($celebrantePresMostrar, ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>

                    <form method="POST" id="formEditarConfirmacion" class="row g-3 mt-1 <?php echo $modoEdicion ? '' : 'd-none'; ?>">
                      <input type="hidden" name="token_confirmacion" value="<?php echo htmlspecialchars($_SESSION['token_detalle_confirmacion'], ENT_QUOTES, 'UTF-8'); ?>">
                      <div class="col-sm-6">
                        <label class="form-label" for="editNombrePadre">Padre</label>
                        <input type="text" class="form-control" id="editNombrePadre" name="NombrePadreManual" maxlength="100" value="<?php echo htmlspecialchars((string) $valorFormulario('NombrePadreManual', $padreMostrar), ENT_QUOTES, 'UTF-8'); ?>">
                      </div>
                      <div class="col-sm-6">
                        <label class="form-label" for="editNombreMadre">Madre</label>
                        <input type="text" class="form-control" id="editNombreMadre" name="NombreMadreManual" maxlength="100" value="<?php echo htmlspecialchars((string) $valorFormulario('NombreMadreManual', $madreMostrar), ENT_QUOTES, 'UTF-8'); ?>">
                      </div>
                      <div class="col-sm-6">
                        <label class="form-label" for="editNombrePadrino">Padrino</label>
                        <input type="text" class="form-control" id="editNombrePadrino" name="NombrePadrinoManual" maxlength="100" value="<?php echo htmlspecialchars((string) $valorFormulario('NombrePadrinoManual', $padrinoMostrar), ENT_QUOTES, 'UTF-8'); ?>">
                      </div>
                      <div class="col-sm-6">
                        <label class="form-label" for="editNombreMadrina">Madrina</label>
                        <input type="text" class="form-control" id="editNombreMadrina" name="NombreMadrinaManual" maxlength="100" value="<?php echo htmlspecialchars((string) $valorFormulario('NombreMadrinaManual', $madrinaMostrar), ENT_QUOTES, 'UTF-8'); ?>">
                      </div>
                      <div class="col-sm-6">
                        <label class="form-label" for="editParroquiaBau">Parroquia donde se bautizó</label>
                        <input type="text" class="form-control" id="editParroquiaBau" name="ParroquiaBau" maxlength="30" value="<?php echo htmlspecialchars((string) $valorFormulario('ParroquiaBau', $parroquiaBauMostrar), ENT_QUOTES, 'UTF-8'); ?>">
                      </div>
                      <div class="col-sm-6">
                        <label class="form-label" for="editFechaBau">Fecha de bautizo</label>
                        <input type="date" class="form-control" id="editFechaBau" name="FechaBau" value="<?php echo htmlspecialchars((string) $valorFormulario('FechaBau', $fechaBauMostrar), ENT_QUOTES, 'UTF-8'); ?>">
                      </div>
                      <div class="col-sm-6">
                        <label class="form-label" for="editNomParroquia">Nombre Parroquia</label>
                        <input type="text" class="form-control" id="editNomParroquia" name="NomParroquia" maxlength="50" value="<?php echo htmlspecialchars((string) $valorFormulario('NomParroquia', $certificadoData['NomParroquia'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                      </div>
                      <div class="col-sm-2">
                        <label class="form-label" for="editNroLibro">Número de Libro</label>
                        <input type="text" class="form-control" id="editNroLibro" name="NroLibro" maxlength="10" value="<?php echo htmlspecialchars((string) $valorFormulario('NroLibro', $certificadoData['NroLibro'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                      </div>
                      <div class="col-sm-2">
                        <label class="form-label" for="editNroPag">Número de Página</label>
                        <input type="text" class="form-control" id="editNroPag" name="NroPag" maxlength="10" value="<?php echo htmlspecialchars((string) $valorFormulario('NroPag', $certificadoData['NroPag'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                      </div>
                      <div class="col-sm-2">
                        <label class="form-label" for="editNroPart">Número de Partida</label>
                        <input type="text" class="form-control" id="editNroPart" name="NroPart" maxlength="10" value="<?php echo htmlspecialchars((string) $valorFormulario('NroPart', $certificadoData['NroPart'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                      </div>
                      <div class="col-sm-6">
                        <label class="form-label" for="editLugarNac">Lugar de Nacimiento</label>
                        <input type="text" class="form-control" id="editLugarNac" name="LugarNac" maxlength="50" value="<?php echo htmlspecialchars((string) $valorFormulario('LugarNac', $certificadoData['LugarNac'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                      </div>
                      <div class="col-sm-6">
                        <label class="form-label" for="editFechaReal">Fecha Real</label>
                        <input type="date" class="form-control" id="editFechaReal" name="FechaReal" value="<?php echo htmlspecialchars((string) $valorFormulario('FechaReal', $certificadoData['FechaReal'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                      </div>
                      <div class="col-sm-6">
                        <label class="form-label" for="editPresbitero">Pbro. que certifica y firma</label>
                        <input type="text" class="form-control" id="editPresbitero" name="PresbiteroEm" maxlength="50" value="<?php echo htmlspecialchars((string) $valorFormulario('PresbiteroEm', $presbiteroMostrar), ENT_QUOTES, 'UTF-8'); ?>">
                      </div>
                      <div class="col-sm-6">
                        <label class="form-label" for="editCelebrantePres">Celebrante Presbítero</label>
                        <input type="text" class="form-control" id="editCelebrantePres" name="CelebrantePres" maxlength="50" value="<?php echo htmlspecialchars((string) $valorFormulario('CelebrantePres', $celebrantePresMostrar), ENT_QUOTES, 'UTF-8'); ?>">
                      </div>
                      <div class="col-12">
                        <label class="form-label" for="editObservacion">Observaciones</label>
                        <input type="text" class="form-control" id="editObservacion" name="Observacion" maxlength="80" value="<?php echo htmlspecialchars((string) $valorFormulario('Observacion', $observacionMostrar), ENT_QUOTES, 'UTF-8'); ?>">
                      </div>
                      <div class="col-12 d-flex gap-2">
                        <button type="button" id="btnCancelarConfirmacion" class="btn btn-warning-soft">Cancelar</button>
                        <button type="submit" name="actualizar_datos_confirmacion" value="1" class="btn btn-success-soft"><i class="bi bi-check-circle me-1"></i>Actualizar</button>
                      </div>
                    </form>
                    <div class="d-flex align-items-center flex-wrap gap-2">
                      <strong class="me-1">Número de Emisión:</strong>
                      <form method="POST" class="d-flex align-items-center gap-2 mb-0">
                        <input type="hidden" name="CodIns" value="<?php echo htmlspecialchars((string) ($codIns ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="number" class="form-control form-control-sm" style="width: 130px;" name="NroEmision" value="<?php echo htmlspecialchars((string) $certificadoData['NroEmision'], ENT_QUOTES, 'UTF-8'); ?>" min="1" max="9999999999" required>
                        <button type="submit" name="cambiar_emision" value="1" class="btn btn-sm btn-primary-soft">Guardar</button>
                      </form>
                    </div>
                    <?php if ($mensajeEmision !== ''): ?>
                      <p class="<?php echo $mensajeEmision === 'ok' ? 'text-success' : 'text-danger'; ?> mb-1">
                        <?php echo $mensajeEmision === 'ok' ? 'Número de emisión actualizado.' : htmlspecialchars($mensajeEmision, ENT_QUOTES, 'UTF-8'); ?>
                      </p>
                    <?php endif; ?>
                    <div class="d-flex align-items-center flex-wrap gap-2">
                      <strong class="me-1">Fecha de última emisión:</strong>
                      <span><?php echo htmlspecialchars((string) $certificadoData['FechaEmision'], ENT_QUOTES, 'UTF-8'); ?></span>
                      <?php if ($partidaGuardada): ?>
                        <form method="POST" class="mb-0">
                          <input type="hidden" name="CodIns" value="<?php echo htmlspecialchars((string) ($codIns ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                          <button type="submit" name="actualizar_fecha_emision" value="1" class="btn btn-sm <?php echo $mensajeFecha === 'ok' ? 'btn-success' : 'btn-outline-primary'; ?>"><i class="bi <?php echo $mensajeFecha === 'ok' ? 'bi-check-circle' : 'bi-calendar-check'; ?> me-1"></i><?php echo $mensajeFecha === 'ok' ? 'Fecha actualizada' : 'Actualizar a hoy'; ?></button>
                        </form>
                      <?php else: ?>
                        <small class="text-muted">Guarde primero los datos de la partida para cambiar la fecha de emisión.</small>
                      <?php endif; ?>
                    </div>
                    <?php if ($mensajeFecha !== '' && $mensajeFecha !== 'ok'): ?>
                      <p class="text-danger mb-1"><?php echo htmlspecialchars($mensajeFecha, ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>
                    <p><strong>Estado del Certificado:</strong> <?php echo htmlspecialchars((string) ($certificadoData['EstadoCert'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>

                    <div class="col-sm-12 col-lg-12 d-flex align-items-center">
                      <div class="col-sm-4 col-lg-4">
                        <a href="VistaSolicitante.php" class="btn btn-warning-soft w-90"><i class="bi bi-arrow-bar-left"></i> Volver atras</a>
                      </div>
                      <div class="col-sm-4 col-lg-4">
                        <a href="EmitirCertificadoConfirmacion.php?CodIns=<?php echo $codIns; ?>" class="btn btn-success-soft w-90" target="_blank"> Verificar Certificado <i class="bi bi-file-earmark-check"></i></a>
                      </div>
                      <div class="col-sm-4 col-lg-4">
                        <a href="DocConfirmacion.php?CodIns=<?php echo $codIns; ?>" class="btn btn-primary-soft w-90" target="_blank"> Emitir Certificado <i class="bi bi-file-earmark-text"></i></a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <br>
            <?php else: ?>
              <h4 class="p-2">Registrar Nuevo Certificado de Confirmación</h4>
              <form method="POST">
                <input type="hidden" name="CodIns" value="<?php echo $codIns; ?>">
                <div class="row mx-auto p-2 ">
                  <div class="col-sm-6 col-lg-6">
                    <label class="form-label">Nombre de la Parroquia</label>
                    <input type="text" class="form-control" name="NomParroquia" placeholder="Nombre de la Parroquia" required>
                  </div>
                  <div class="col-sm-6 col-lg-6">
                    <label class="form-label">Número de Libro</label>
                    <input type="text" class="form-control" name="NroLibro" placeholder="Número de Libro" required>
                  </div>
                  <div class="col-sm-6 col-lg-6">
                    <label class="form-label">Número de Página</label>
                    <input type="text" class="form-control" name="NroPag" placeholder="Número de Página" required>
                  </div>
                  <div class="col-sm-6 col-lg-6">
                    <label class="form-label">Número de Partida</label>
                    <input type="text" class="form-control" name="NroPart" placeholder="Número de Partida" required>
                  </div>
                  <input type="hidden" name="LugarNac" value="Ninguno">
                  <div class="col-sm-6 col-lg-6">
                    <label class="form-label">Fecha Real</label>
                    <input type="date" class="form-control" name="FechaReal" required>
                  </div>
                  <div class="col-sm-6 col-lg-6">
                    <label class="form-label">Número de Emisión</label>
                    <input type="number" class="form-control" name="NroEmision" placeholder="Número de Emisión" required>
                  </div>
                  <div class="col-sm-6 col-lg-6">
                    <label class="form-label">Fecha de Emisión</label>
                    <input type="date" class="form-control" name="FechaEmision" required>
                  </div>
                  <div class="col-sm-6 col-lg-6">
                    <label class="form-label">Presbítero Emisor</label>
                    <input type="text" class="form-control" name="PresbiteroEm" placeholder="Presbítero Emisor" required>
                  </div>
                  <div class="col-sm-6 col-lg-6">
                    <label class="form-label">Observación</label>
                    <input type="text" class="form-control" name="Observacion" placeholder="Observación" required>
                  </div>
                  <div class="col-sm-6 col-lg-6">
                    <label class="form-label">Celebrante Presbítero</label>
                    <input type='text' class='form-control' name='CelebrantePres' placeholder='Celebrante Presbítero' required>
                  </div>
                  <input type='hidden' name='EstadoCert' value="Activo" required>

                  <div class='col-sm-12 p-2'>
                    <button type='submit' name='registrar_certificado' class='btn btn-primary-soft w-100'>Registrar Certificado <i class='bi bi-upload'></i></button>
                  </div>

                </div>
              </form>
            <?php endif; ?>
          </div>
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
      var btnEditar = document.getElementById('btnEditarConfirmacion');
      var lectura = document.getElementById('detalleConfirmacionLectura');
      var formulario = document.getElementById('formEditarConfirmacion');
      var btnCancelar = document.getElementById('btnCancelarConfirmacion');
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