<?php
require_once '../Controlador/controladorCoordinadorAdmin.php';
require_once '../Controlador/ControladorPersona.php';
require_once '../src/formulario_persona_ampliado.php';

session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: ../logout.php");
  exit();
}
$usuario = $_SESSION['usuario'];
require_once __DIR__ . '/../Controlador/controladorAcceso.php';
ControladorAcceso::exigir('coordinador');

if ($usuario['DescripCar'] != 'Párroco') {
  header("Location: VistaPersonal.php");
  exit();
}

$controller = new ControladorCoordinadorAdmin();
$personaController = new ControladorPersona();
$coordinadores = $controller->obtenerCoordinadores();
$grupos = $controller->obtenerTodosLosGrupos();

$mensaje = '';
$mensaje1 = '';
$personaEncontrada = null;

$zonasSector = $controller->obtenerZonasSector();
$sacramentosRegistrados = $controller->obtenerSacramentosSolicitados();
$estadosCivilesRegistrados = $controller->obtenerEstadosCiviles();

$filtrosBusqueda = [
  'texto' => trim($_POST['buscarNombre'] ?? ''),
  'sacramento' => $_POST['filtroSacramento'] ?? '',
  'zona' => $_POST['filtroZona'] ?? '',
  'estadoCivil' => $_POST['filtroEstado'] ?? '',
];
$mostrarResultados = $_SERVER['REQUEST_METHOD'] === 'POST'
  && (isset($_POST['buscarPersona']) || isset($_POST['seleccionarPersona']));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bloquearCoordinador'])) {
  $ci = $_POST['ciBloquear'];
  if ($controller->bloquearCoordinador($ci)) {
    $mensaje = "Coordinador con CI {$ci} bloqueado correctamente.";
  } else {
    $mensaje1 = "Error al bloquear al coordinador.";
  }
  header("Location: VistaCoordinadorAdmin.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['activarCoordinador'])) {
  $ci = $_POST['ciActivar'];
  if ($controller->activarCoordinador($ci)) {
    $mensaje = "Coordinador con CI {$ci} activado correctamente.";
  } else {
    $mensaje1 = "Error al activar al coordinador.";
  }
  header("Location: VistaCoordinadorAdmin.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['seleccionarPersona'])) {
  $personaEncontrada = $controller->buscarPersona($_POST['ciSeleccionada']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrarPersona'])) {
  if ($personaController->crearPersona($_POST)) {
    $ciRegistrado = $personaController->obtenerCiPersonaRegistrada();
    $personaEncontrada = $controller->buscarPersona($ciRegistrado);
    $mensaje = '<strong>Persona registrada con éxito.</strong> Identificador: ' . htmlspecialchars($ciRegistrado);
  } else {
    $mensaje1 = '<strong>Error:</strong> ' . htmlspecialchars($personaController->obtenerError() ?: 'No se pudo registrar la persona.');
  }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['asignarCoordinador'])) {
  $ci = $_POST['CiPersona'];
  $sacramento = $_POST['Sacramento'];
  $idGrupo = $_POST['IdGrupo'];
  $usuarioCat = $_POST['UsuarioCat'];
  $clave = $_POST['ClaveCat'];

  $existente = $controller->verificarCoordinadorExistente($sacramento);
  if ($existente) {
    $mensaje1 = "<strong>Error:</strong> Ya existe un Coordinador para {$sacramento}.";
  } else {
    if ($controller->registrarCoordinador($ci, $sacramento, $idGrupo, $usuarioCat, $clave)) {
      $persona = $controller->buscarPersona($ci);
      $_SESSION['registroCatequista'] = [
        'Nombre' => $persona['Nombre'] . ' ' . $persona['ApPaterno'],
        'UsuarioCat' => $usuarioCat,
        'ClaveCat' => $clave
      ];
      $mensaje = "<strong>Coordinador asignado con éxito.</strong> Nombre: {$persona['Nombre']} {$persona['ApPaterno']}, Usuario: {$usuarioCat}, Clave: {$clave}";
      $personaEncontrada = null;
      $coordinadores = $controller->obtenerCoordinadores();
    } else {
      $mensaje1 = "Error al asignar al coordinador.";
    }
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
  <link rel="stylesheet" href="../assets/css/persona-registro.css?v=<?= filemtime(__DIR__ . '/../assets/css/persona-registro.css') ?>">
</head>

<body class="sidebar-start-enabled">
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
              <img class="avatar-img rounded border border-white border-3" src="../assets/images/avatar/Ninguno.png" alt="">
            </a>
            <ul class="dropdown-menu dropdown-animation dropdown-menu-end pt-3 small me-md-n3" aria-labelledby="profileDropdown">
              <li class="px-3">
                <div class="d-flex align-items-center position-relative">
                  <div class="avatar me-3">
                    <img class="avatar-img rounded-circle" src="../assets/images/avatar/Ninguno.png" alt="avatar">
                  </div>
                  <div>
                    <a class="h6 stretched-link" href="#"><?php echo $usuario['Nombre'] . " " . $usuario['Paterno'] . " " . $usuario['Materno']; ?></a>
                    <p class="small m-0"><?php echo $usuario['DescripCar']; ?> <i class="bi bi-patch-check-fill text-success small"></i></p>
                  </div>
                </div>
                <a class="dropdown-item btn btn-primary-soft btn-sm my-2 text-center" href="PerfilAdmin.php">Ver mi Perfil</a>
              </li>
              <li><a class="dropdown-item" href="Configuracion.php"><i class="bi bi-gear fa-fw me-2"></i>Configuración</a></li>
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
              <li class="dropdown-divider"></li>
              <li><a class="dropdown-item bg-danger-soft-hover" href="../logout.php"><i class="bi bi-power fa-fw me-2"></i>Salir</a></li>
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
                  <div class="h-50px" style="background-image:url(<?php echo ($usuario['FondoPer'] == 'Ninguno' || empty($usuario['FondoPer'])) ? '../assets/images/fondo/Ninguno.jpg' : "../assets/images/fondo/{$usuario['FondoPer']}?v=" . time(); ?>); background-position: center; background-size: cover; background-repeat: no-repeat;"></div>
                  <div class="card-body pt-0">
                    <div class="text-center">
                      <div class="avatar avatar-lg mt-n5 mb-3">
                        <img class="avatar-img rounded border border-white border-3" src="<?php echo ($usuario['PerfilPer'] == 'Ninguno' || empty($usuario['PerfilPer'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$usuario['PerfilPer']}?v=" . time(); ?>" alt="">
                      </div>
                      <h5 class="mb-0"><?php echo $usuario['Nombre'] . " " . $usuario['Paterno'] . " " . $usuario['Materno']; ?></h5>
                      <small id="cargo"><?php echo $usuario['DescripCar']; ?></small>
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
          <?php if ($mensaje): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              <?php echo $mensaje; ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          <?php endif; ?>
          <?php if ($mensaje1): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <?php echo $mensaje1; ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          <?php endif; ?>

          <div class="card">
            <div class="card-header d-sm-flex align-items-center text-center justify-content-sm-between border-0 pb-0">
              <h1 class="h4 card-title">Designar Coordinadores</h1>
            </div>
            <div class="card-body">
              <p class="mb-3">Seleccione el Sacramento y asigne un Coordinador para cada uno.</p>

              <div class="row g-3 mb-4">
                <div class="col-md-6">
                  <div class="card border border-primary">
                    <div class="card-body text-center">
                      <h5 class="card-title text-primary">Confirmación</h5>
                      <?php
                      $coorConfirmacion = array_filter($coordinadores, function($c) { return $c['Sacramento'] == 'Confirmación'; });
                      if (count($coorConfirmacion) > 0):
                        $coor = array_values($coorConfirmacion)[0];
                      ?>
                        <p class="mb-1"><strong><?php echo $coor['Nombre'] . ' ' . $coor['ApPaterno']; ?></strong></p>
                        <small class="text-muted">CI: <?php echo $coor['CiCat']; ?> | Grupo: <?php echo $coor['NombreGrupo']; ?></small>
                      <?php else: ?>
                        <p class="text-muted mb-2">Sin coordinador asignado</p>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="card border border-success">
                    <div class="card-body text-center">
                      <h5 class="card-title text-success">Primera Comunión</h5>
                      <?php
                      $coorComunion = array_filter($coordinadores, function($c) { return $c['Sacramento'] == 'Primera Comunión'; });
                      if (count($coorComunion) > 0):
                        $coor = array_values($coorComunion)[0];
                      ?>
                        <p class="mb-1"><strong><?php echo $coor['Nombre'] . ' ' . $coor['ApPaterno']; ?></strong></p>
                        <small class="text-muted">CI: <?php echo $coor['CiCat']; ?> | Grupo: <?php echo $coor['NombreGrupo']; ?></small>
                      <?php else: ?>
                        <p class="text-muted mb-2">Sin coordinador asignado</p>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>

              <hr>

              <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Asignar Nuevo Coordinador</h5>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#registrarPersonaModal">
                  <i class="bi bi-plus-circle"></i> Registrar Nuevo
                </button>
              </div>

              <form method="POST" class="row g-3">
                <div class="col-md-8">
                  <label class="form-label">Buscar por nombre o CI</label>
                  <input type="text" class="form-control" name="buscarNombre" placeholder="Nombre, apellido o CI"
                    value="<?php echo htmlspecialchars($filtrosBusqueda['texto']); ?>">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                  <button type="submit" name="buscarPersona" class="btn btn-primary w-100">Buscar</button>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Sacramento</label>
                  <select class="form-select" name="filtroSacramento">
                    <option value="">Todos</option>
                    <?php foreach ($sacramentosRegistrados as $fila): ?>
                      <option value="<?php echo htmlspecialchars($fila['SacramentoSolicita']); ?>"
                        <?php echo $filtrosBusqueda['sacramento'] === $fila['SacramentoSolicita'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($fila['SacramentoSolicita']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Zona / Sector</label>
                  <select class="form-select" name="filtroZona">
                    <option value="">Todas</option>
                    <?php foreach ($zonasSector as $fila): ?>
                      <option value="<?php echo htmlspecialchars($fila['ZonaSector']); ?>"
                        <?php echo $filtrosBusqueda['zona'] === $fila['ZonaSector'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($fila['ZonaSector']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Estado civil</label>
                  <select class="form-select" name="filtroEstado">
                    <option value="">Todos</option>
                    <?php foreach ($estadosCivilesRegistrados as $fila): ?>
                      <option value="<?php echo htmlspecialchars($fila['Estado_per']); ?>"
                        <?php echo $filtrosBusqueda['estadoCivil'] === $fila['Estado_per'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($fila['Estado_per']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </form>

              <?php if ($mostrarResultados): ?>
                <?php
                $personasEncontradas = $controller->buscarPersonasFiltradas(
                  $filtrosBusqueda['texto'],
                  $filtrosBusqueda['sacramento'],
                  $filtrosBusqueda['zona'],
                  $filtrosBusqueda['estadoCivil']
                );
                ?>
                <?php if (count($personasEncontradas) > 0): ?>
                  <div class="table-responsive mt-3">
                    <table class="table table-hover table-sm">
                      <thead class="table-light">
                        <tr>
                          <th>CI</th>
                          <th>Nombre</th>
                          <th>Apellido Paterno</th>
                          <th>Apellido Materno</th>
                          <th>Sacramento</th>
                          <th>Zona / Sector</th>
                          <th>Acción</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($personasEncontradas as $p): ?>
                          <tr>
                            <td><?php echo htmlspecialchars($p['CiPersona']); ?></td>
                            <td><?php echo htmlspecialchars($p['Nombre']); ?></td>
                            <td><?php echo htmlspecialchars($p['ApPaterno']); ?></td>
                            <td><?php echo htmlspecialchars($p['ApMaterno']); ?></td>
                            <td>
                              <?php if (!empty($p['SacramentoSolicita'])): ?>
                                <span class="badge bg-primary"><?php echo htmlspecialchars($p['SacramentoSolicita']); ?></span>
                              <?php else: ?>
                                <span class="text-muted">—</span>
                              <?php endif; ?>
                            </td>
                            <td>
                              <?php if (!empty($p['ZonaSector'])): ?>
                                <?php echo htmlspecialchars($p['ZonaSector']); ?>
                              <?php else: ?>
                                <span class="text-muted">—</span>
                              <?php endif; ?>
                            </td>
                            <td class="text-nowrap">
                              <form method="POST" style="display:inline;">
                                <input type="hidden" name="ciSeleccionada" value="<?php echo htmlspecialchars($p['CiPersona']); ?>">
                                <input type="hidden" name="buscarNombre" value="<?php echo htmlspecialchars($filtrosBusqueda['texto']); ?>">
                                <input type="hidden" name="filtroSacramento" value="<?php echo htmlspecialchars($filtrosBusqueda['sacramento']); ?>">
                                <input type="hidden" name="filtroZona" value="<?php echo htmlspecialchars($filtrosBusqueda['zona']); ?>">
                                <input type="hidden" name="filtroEstado" value="<?php echo htmlspecialchars($filtrosBusqueda['estadoCivil']); ?>">
                                <button type="submit" name="seleccionarPersona" class="btn btn-sm btn-outline-primary">Seleccionar</button>
                              </form>
                              <?php if (!empty($p['DocumentoBautizo'])): ?>
                                <a class="btn btn-sm btn-outline-secondary" target="_blank" title="Ver certificado de bautizo"
                                  href="../src/ver_documento_persona.php?archivo=<?php echo rawurlencode($p['DocumentoBautizo']); ?>">
                                  <i class="bi bi-file-earmark-pdf"></i>
                                </a>
                              <?php endif; ?>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                <?php else: ?>
                  <div class="alert alert-warning mt-3">
                    No se encontraron personas con los filtros seleccionados.
                  </div>
                <?php endif; ?>
              <?php endif; ?>

              <?php if ($personaEncontrada): ?>
                <form method="POST" class="mt-3">
                  <div class="card border-success">
                    <div class="card-body">
                      <h6 class="card-title">Persona Seleccionada</h6>
                      <div class="row g-2">
                        <div class="col-md-3">
                          <label class="form-label">CI</label>
                          <input type="text" class="form-control" name="CiPersona" value="<?php echo htmlspecialchars($personaEncontrada['CiPersona']); ?>" readonly>
                        </div>
                        <div class="col-md-3">
                          <label class="form-label">Nombre</label>
                          <input type="text" class="form-control" value="<?php echo htmlspecialchars($personaEncontrada['Nombre']); ?>" readonly>
                        </div>
                        <div class="col-md-3">
                          <label class="form-label">Apellido Paterno</label>
                          <input type="text" class="form-control" value="<?php echo htmlspecialchars($personaEncontrada['ApPaterno']); ?>" readonly>
                        </div>
                        <div class="col-md-3">
                          <label class="form-label">Apellido Materno</label>
                          <input type="text" class="form-control" value="<?php echo htmlspecialchars($personaEncontrada['ApMaterno']); ?>" readonly>
                        </div>
                      </div>
                      <div class="row g-2 mt-2">
                        <div class="col-md-3">
                          <label class="form-label">Sexo</label>
                          <input type="text" class="form-control" value="<?php echo htmlspecialchars($personaEncontrada['Sexo']); ?>" readonly>
                        </div>
                        <div class="col-md-3">
                          <label class="form-label">Fecha de Nacimiento</label>
                          <input type="text" class="form-control" value="<?php echo htmlspecialchars($personaEncontrada['FechaNac']); ?>" readonly>
                        </div>
                        <div class="col-md-3">
                          <label class="form-label">Estado Civil</label>
                          <input type="text" class="form-control" value="<?php echo htmlspecialchars($personaEncontrada['Estado_per']); ?>" readonly>
                        </div>
                        <div class="col-md-3">
                          <label class="form-label">Sacramento que solicita</label>
                          <input type="text" class="form-control" value="<?php echo htmlspecialchars($personaEncontrada['SacramentoSolicita']); ?>" readonly>
                        </div>
                      </div>
                      <div class="row g-2 mt-2">
                        <div class="col-md-3">
                          <label class="form-label">Zona / Sector</label>
                          <input type="text" class="form-control" value="<?php echo htmlspecialchars($personaEncontrada['ZonaSector']); ?>" readonly>
                        </div>
                        <div class="col-md-4">
                          <label class="form-label">Dirección</label>
                          <input type="text" class="form-control" value="<?php echo htmlspecialchars($personaEncontrada['Direccion']); ?>" readonly>
                        </div>
                        <div class="col-md-2">
                          <label class="form-label">Contacto</label>
                          <input type="text" class="form-control" value="<?php echo htmlspecialchars($personaEncontrada['Contacto']); ?>" readonly>
                        </div>
                        <div class="col-md-3">
                          <label class="form-label">Teléfono alternativo</label>
                          <input type="text" class="form-control" value="<?php echo htmlspecialchars($personaEncontrada['TelefonoAlternativo']); ?>" readonly>
                        </div>
                      </div>
                      <div class="row g-2 mt-2">
                        <div class="col-md-4">
                          <label class="form-label">Correo electrónico</label>
                          <input type="text" class="form-control" value="<?php echo htmlspecialchars($personaEncontrada['Correo']); ?>" readonly>
                        </div>
                        <div class="col-md-4">
                          <label class="form-label">Certificado de Bautizo</label>
                          <div>
                            <?php if (!empty($personaEncontrada['DocumentoBautizo'])): ?>
                              <a class="btn btn-sm btn-outline-success" target="_blank"
                                href="../src/ver_documento_persona.php?archivo=<?php echo rawurlencode($personaEncontrada['DocumentoBautizo']); ?>">
                                <i class="bi bi-file-earmark-pdf"></i> Ver certificado
                              </a>
                            <?php else: ?>
                              <span class="text-muted small">Sin certificado adjunto</span>
                            <?php endif; ?>
                          </div>
                        </div>
                      </div>
                      <div class="row g-2 mt-2">
                        <div class="col-12">
                          <label class="form-label">Observaciones pastorales</label>
                          <textarea class="form-control" rows="2" readonly><?php echo htmlspecialchars($personaEncontrada['Observaciones']); ?></textarea>
                        </div>
                      </div>
                      <div class="row g-2 mt-2">
                        <div class="col-md-4">
                          <label class="form-label">Sacramento</label>
                          <select class="form-select" name="Sacramento" required>
                            <option value="">Seleccionar...</option>
                            <?php
                            $sacramentos = ['Confirmación', 'Primera Comunión'];
                            foreach ($sacramentos as $sac):
                              $yaTiene = array_filter($coordinadores, function($c) use ($sac) { return $c['Sacramento'] == $sac; });
                            ?>
                              <option value="<?php echo $sac; ?>" <?php echo count($yaTiene) > 0 ? 'disabled' : ''; ?>>
                                <?php echo $sac . (count($yaTiene) > 0 ? ' (Ya tiene coordinador)' : ''); ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                        <div class="col-md-4">
                          <label class="form-label">Grupo</label>
                          <select class="form-select" name="IdGrupo" required>
                            <option value="">Seleccionar Sacramento primero...</option>
                          </select>
                        </div>
                        <div class="col-md-4"></div>
                        <div class="col-md-6 mt-2">
                          <label class="form-label">Usuario de acceso</label>
                          <input type="text" class="form-control" name="UsuarioCat" placeholder="Usuario para ingresar al sistema" required>
                        </div>
                        <div class="col-md-6 mt-2">
                          <label class="form-label">Contraseña</label>
                          <input type="text" class="form-control" name="ClaveCat" placeholder="Contraseña para ingresar al sistema" required>
                        </div>
                        <div class="col-md-4 d-flex align-items-end mt-2">
                          <button type="submit" name="asignarCoordinador" class="btn btn-success w-100">Asignar Coordinador</button>
                        </div>
                      </div>
                    </div>
                  </div>
                </form>
              <?php endif; ?>

              <hr>

              <h5 class="mb-3">Coordinadores Actuales</h5>
              <?php if (count($coordinadores) > 0): ?>
                <div class="table-responsive">
                  <table class="table table-hover">
                    <thead>
                      <tr>
                        <th>CI</th>
                        <th>Nombre</th>
                        <th>Sacramento</th>
                        <th>Grupo</th>
                        <th>Estado</th>
                        <th>Credenciales</th>
                        <th>Acciones</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($coordinadores as $coor): ?>
                        <tr>
                          <td><?php echo $coor['CiCat']; ?></td>
                          <td><?php echo $coor['Nombre'] . ' ' . $coor['ApPaterno'] . ' ' . $coor['ApMaterno']; ?></td>
                          <td><span class="badge bg-primary"><?php echo $coor['Sacramento']; ?></span></td>
                          <td><?php echo $coor['NombreGrupo']; ?></td>
                          <td>
                            <?php if ($coor['EstadoCat'] == 'Activo'): ?>
                              <span class="badge bg-success">Activo</span>
                            <?php else: ?>
                              <span class="badge bg-danger">Inactivo</span>
                            <?php endif; ?>
                          </td>
                          <td>
                            <span class="credential-hidden" id="cred-<?php echo $coor['CiCat']; ?>">
                              <span class="text-muted">••••••••</span>
                            </span>
                            <span class="credential-visible d-none" id="cred-show-<?php echo $coor['CiCat']; ?>">
                              <small><strong>U:</strong> <?php echo $coor['UsuarioCat']; ?> <strong>Cl:</strong> <?php echo $coor['ClaveCat']; ?></small>
                            </span>
                            <button type="button" class="btn btn-sm btn-outline-secondary ms-1" onclick="toggleCred('<?php echo $coor['CiCat']; ?>')" title="Ver credenciales">
                              <i class="bi bi-eye" id="eye-<?php echo $coor['CiCat']; ?>"></i>
                            </button>
                          </td>
                          <td>
                            <?php if ($coor['EstadoCat'] == 'Activo'): ?>
                              <form method="POST" class="d-inline">
                                <input type="hidden" name="ciBloquear" value="<?php echo $coor['CiCat']; ?>">
                                <button type="submit" name="bloquearCoordinador" class="btn btn-danger btn-sm" onclick="return confirm('¿Bloquear a este coordinador?')">
                                  <i class="bi bi-lock-fill"></i>
                                </button>
                              </form>
                            <?php else: ?>
                              <form method="POST" class="d-inline">
                                <input type="hidden" name="ciActivar" value="<?php echo $coor['CiCat']; ?>">
                                <button type="submit" name="activarCoordinador" class="btn btn-success btn-sm" onclick="return confirm('¿Activar a este coordinador?')">
                                  <i class="bi bi-unlock-fill"></i>
                                </button>
                              </form>
                            <?php endif; ?>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              <?php else: ?>
                <p class="text-muted">No hay coordinadores asignados actualmente.</p>
              <?php endif; ?>

            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
  <!-- Modal Registrar Persona -->
  <div class="modal fade persona-modal-ampliado" id="registrarPersonaModal" tabindex="-1" aria-labelledby="registrarPersonaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="registrarPersonaModalLabel">Agregar Nueva Persona</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <?php renderFormularioPersonaAmpliado('registrarPersona', 'Registrar persona'); ?>
        </div>
      </div>
    </div>
  </div>
<script src="../assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/functions.js"></script>
  <script src="../assets/js/tema.js"></script>
  <script>
    const gruposPriCom = [
      <?php
      for ($i = 2; $i <= 19; $i++) {
        echo "{id: {$i}, nombre: 'Grupo " . str_pad($i - 2, 2, '0', STR_PAD_LEFT) . "'},";
      }
      ?>
    ];
    const gruposConfirm = [
      <?php
      for ($i = 23; $i <= 40; $i++) {
        echo "{id: {$i}, nombre: 'Grupo " . str_pad($i - 23, 2, '0', STR_PAD_LEFT) . "'},";
      }
      ?>
    ];

    const selectSacramento = document.querySelector('select[name="Sacramento"]');
    const selectGrupo = document.querySelector('select[name="IdGrupo"]');

    function cargarGrupos(lista) {
      selectGrupo.innerHTML = '<option value="">Seleccionar...</option>';
      lista.forEach(g => {
        const opt = document.createElement('option');
        opt.value = g.id;
        opt.textContent = g.nombre;
        selectGrupo.appendChild(opt);
      });
    }

    selectSacramento.addEventListener('change', function() {
      if (this.value === 'Primera Comunión') {
        cargarGrupos(gruposPriCom);
      } else if (this.value === 'Confirmación') {
        cargarGrupos(gruposConfirm);
      } else {
        selectGrupo.innerHTML = '<option value="">Seleccionar...</option>';
      }
    });

    function toggleCred(ci) {
      const hidden = document.getElementById('cred-' + ci);
      const visible = document.getElementById('cred-show-' + ci);
      const eye = document.getElementById('eye-' + ci);
      if (visible.classList.contains('d-none')) {
        hidden.classList.add('d-none');
        visible.classList.remove('d-none');
        eye.classList.replace('bi-eye', 'bi-eye-slash');
      } else {
        visible.classList.add('d-none');
        hidden.classList.remove('d-none');
        eye.classList.replace('bi-eye-slash', 'bi-eye');
      }
    }
  </script>
  <script src="../assets/js/persona-registro.js?v=<?= filemtime(__DIR__ . '/../assets/js/persona-registro.js') ?>"></script>
</body>

</html>
