<?php
require_once '../Controlador/ControladorPersona.php';
require_once '../src/formulario_persona_ampliado.php';

session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: ../logout.php");
  exit();
}
$usuario = $_SESSION['usuario'];
require_once __DIR__ . '/../Controlador/controladorAcceso.php';
ControladorAcceso::exigir('persona');

$controller = new ControladorPersona();
$mensajePersona = '';
$errorPersona = '';

if (isset($_GET['seguimientoPersona'])) {
  header('Content-Type: application/json; charset=utf-8');
  $identificacion = is_string($_GET['seguimientoPersona']) ? trim($_GET['seguimientoPersona']) : '';
  if ($identificacion === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Identificación no válida.'], JSON_UNESCAPED_UNICODE);
    exit;
  }
  $seguimientoPersona = $controller->buscarPersona($identificacion);
  if (!$seguimientoPersona) {
    http_response_code(404);
    echo json_encode(['error' => 'Persona no encontrada.'], JSON_UNESCAPED_UNICODE);
    exit;
  }
  echo json_encode([
    'CiPersona' => $seguimientoPersona['CiPersona'],
    'NombreCompleto' => trim(implode(' ', array_filter([
      $seguimientoPersona['Nombre'], $seguimientoPersona['ApPaterno'], $seguimientoPersona['ApMaterno']
    ]))),
    'SacramentoSolicita' => $seguimientoPersona['SacramentoSolicita'] ?? '',
    'ProcesosPastorales' => $seguimientoPersona['ProcesosPastorales'] ?? []
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['crear'])) {
  if ($controller->crearPersona($_POST)) {
    $mensajePersona = '<strong>Persona registrada correctamente.</strong> CI: ' . htmlspecialchars($controller->obtenerCiPersonaRegistrada() ?? ($_POST['CiPersona'] ?? ''));
  } else {
    $errorPersona = $controller->obtenerError() ?: 'No se pudo registrar la persona. Verifique los datos e intente nuevamente.';
  }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['actualizar'])) {
  $actualizada = $controller->actualizarPersona($_POST);
  $destino = 'VistaPersona.php?datPer=' . rawurlencode($_POST['CiPersona'] ?? '');
  if (!empty($_POST['buscarActual'])) $destino .= '&buscarPersona=' . rawurlencode($_POST['buscarActual']);
  $destino .= $actualizada ? '&actualizada=1' : '&actualizada=0';
  header('Location: ' . $destino);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cambiar_ci'])) {
  $ciAnterior = trim((string) ($_POST['CiPersona'] ?? ''));
  if ($controller->actualizarIdentificacion($_POST)) {
    $destino = 'VistaPersona.php?datPer=' . rawurlencode(trim((string) ($_POST['NuevaCi'] ?? ''))) . '&ciActualizada=1';
    if (!empty($_POST['buscarActual'])) $destino .= '&buscarPersona=' . rawurlencode($_POST['buscarActual']);
    header('Location: ' . $destino);
    exit;
  }
  $errorPersona = $controller->obtenerError() ?: 'No se pudo actualizar el CI de la persona.';
  if ($ciAnterior !== '') {
    $_GET['datPer'] = $ciAnterior;
    if (!empty($_POST['buscarActual'])) $_GET['buscarPersona'] = $_POST['buscarActual'];
  }
}
$persona = null;
if (isset($_GET['datPer'])) {
  $persona = $controller->buscarPersona($_GET['datPer']);
}
$terminoBusqueda = isset($_GET['buscarPersona']) && is_string($_GET['buscarPersona'])
  ? preg_replace('/\s+/u', ' ', trim($_GET['buscarPersona'])) : '';
$resultadosBusqueda = $terminoBusqueda !== '' ? $controller->buscarPersonas($terminoBusqueda, 20) : [];
if (isset($_GET['actualizada'])) {
  if ($_GET['actualizada'] === '1') $mensajePersona = '<strong>Datos de la persona actualizados correctamente.</strong>';
  else $errorPersona = 'No se pudieron actualizar los datos de la persona.';
}
if (isset($_GET['ciActualizada']) && $_GET['ciActualizada'] === '1') {
  $mensajePersona = '<strong>CI actualizado correctamente.</strong> Nuevo CI: ' . htmlspecialchars((string) ($_GET['datPer'] ?? ''), ENT_QUOTES, 'UTF-8') . '.';
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

        <div class="col-md-8 col-lg-9 vstack gap-4">
          <?php if ($mensajePersona): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              <?php echo $mensajePersona; ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          <?php endif; ?>
          <?php if ($errorPersona): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <strong>Error:</strong> <?php echo htmlspecialchars($errorPersona); ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          <?php endif; ?>
          <div class="card h-100">
            <div class="card-header d-lg-flex align-items-start text-center justify-content-lg-between border-0 pb-0 gap-3">
              <h1 class="h4 card-title">Datos de Persona</h1>
              <form method="GET" class="persona-buscador mb-3">
                <div class="d-flex align-items-center gap-2">
                  <div class="form-group mb-0 flex-grow-1">
                    <input type="search" class="form-control" name="buscarPersona" value="<?php echo htmlspecialchars($terminoBusqueda, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Buscar por CI, nombre o apellido" aria-label="Buscar persona" required>
                  </div>
                  <button type="submit" class="btn btn-primary-soft" data-bs-toggle="tooltip" data-bs-placement="top" title="Buscar"><i class="bi bi-search"></i></button>
                  <button type="button" class="btn btn-primary-soft" data-bs-toggle="modal" data-bs-target="#MostrarModal" title="Registrar nueva persona"><i class="bi bi-plus-circle"></i></button>
                </div>
                <small class="text-muted d-block text-start mt-1">Puede buscar por CI, nombre, apellido paterno o materno.</small>
              </form>
            </div>

            <div class="card-body">
              <?php if ($terminoBusqueda !== ''): ?>
                <section class="persona-resultados mb-4" aria-labelledby="tituloResultadosPersona">
                  <div class="persona-resultados-cabecera">
                    <h2 class="h6 mb-0" id="tituloResultadosPersona">Personas encontradas</h2>
                    <span class="badge bg-primary-soft text-primary"><?php echo count($resultadosBusqueda); ?> <?php echo count($resultadosBusqueda) === 1 ? 'resultado' : 'resultados'; ?></span>
                  </div>
                  <?php if ($resultadosBusqueda): ?>
                    <div class="table-responsive">
                      <table class="table align-middle mb-0 persona-resultados-tabla">
                        <thead><tr><th>Nombre completo</th><th>Año de nacimiento</th><th>CI / Documento</th><th class="text-center">Acciones</th></tr></thead>
                        <tbody>
                          <?php foreach ($resultadosBusqueda as $resultado):
                            $nombreCompleto = trim(implode(' ', array_filter([$resultado['Nombre'], $resultado['ApPaterno'], $resultado['ApMaterno']])));
                            $documentoMostrar = trim((string) ($resultado['NumeroDocumento'] ?? '')) !== '' ? $resultado['NumeroDocumento'] : $resultado['CiPersona'];
                            $anioNacimiento = !empty($resultado['FechaNac']) ? substr($resultado['FechaNac'], 0, 4) : '—';
                            $esPersonaSeleccionada = $persona && (string) $persona['CiPersona'] === (string) $resultado['CiPersona'];
                          ?>
                            <tr class="<?php echo $esPersonaSeleccionada ? 'persona-fila-activa' : ''; ?>"<?php echo $esPersonaSeleccionada ? ' aria-current="true"' : ''; ?>>
                              <td class="fw-semibold"><?php echo htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8'); ?></td>
                              <td><?php echo htmlspecialchars($anioNacimiento, ENT_QUOTES, 'UTF-8'); ?></td>
                              <td><?php echo htmlspecialchars($documentoMostrar, ENT_QUOTES, 'UTF-8'); ?></td>
                              <td>
                                <div class="persona-acciones justify-content-center">
                                  <a class="btn persona-accion persona-accion-ver" href="VistaPersona.php?datPer=<?php echo rawurlencode($resultado['CiPersona']); ?>&amp;buscarPersona=<?php echo rawurlencode($terminoBusqueda); ?>" data-bs-toggle="tooltip" title="Ver y actualizar" aria-label="Ver y actualizar"><i class="bi bi-eye-fill"></i></a>
                                  <button type="button" class="btn persona-accion persona-accion-seguimiento" data-seguimiento-ci="<?php echo htmlspecialchars($resultado['CiPersona'], ENT_QUOTES, 'UTF-8'); ?>" data-bs-toggle="tooltip" title="Seguimiento pastoral" aria-label="Seguimiento pastoral"><i class="bi bi-clipboard-pulse"></i></button>
                                  <a class="btn persona-accion persona-accion-reserva" href="VistaReservas.php?reservante=<?php echo rawurlencode($resultado['CiPersona']); ?>" data-bs-toggle="tooltip" title="Crear reserva" aria-label="Crear reserva"><i class="bi bi-calendar-plus"></i></a>
                                  <a class="btn persona-accion persona-accion-certificado" href="VistaSolicitante.php?ciPersonaSeleccionada=<?php echo rawurlencode($resultado['CiPersona']); ?>" data-bs-toggle="tooltip" title="Emitir certificado" aria-label="Emitir certificado"><i class="bi bi-file-earmark-text"></i></a>
                                </div>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                    <div class="persona-resultados-pie">Mostrando <?php echo count($resultadosBusqueda); ?> <?php echo count($resultadosBusqueda) === 1 ? 'persona' : 'personas'; ?></div>
                  <?php else: ?>
                    <div class="p-4 text-center text-muted">No se encontraron personas.</div>
                  <?php endif; ?>
                </section>
              <?php endif; ?>
              <?php if ($persona): ?>
                <?php
                  $procesos = $persona['ProcesosPastorales'] ?? ['estadoActual' => 'Registrado', 'reservas' => [], 'catequesis' => [], 'sacramentos' => []];
                  $estadoActual = $procesos['estadoActual'] ?? 'Registrado';
                  $clasesEstado = ['Registrado' => 'secondary', 'Reservado' => 'info', 'Inscrito' => 'primary', 'Realizado' => 'warning', 'Certificado emitido' => 'success'];
                  $claseEstado = $clasesEstado[$estadoActual] ?? 'secondary';
                  $sacramentoSolicitado = $persona['SacramentoSolicita'] ?? '';
                  $tabReserva = ['Bautizo' => 'bautizo', 'Reserva de Bautizo' => 'bautizo', 'Matrimonio' => 'matrimonio', 'Reserva de Matrimonio' => 'matrimonio', 'Reserva de misa' => 'misa'][$sacramentoSolicitado] ?? '';
                ?>
                <div class="card border mb-4"><div class="card-body">
                  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div><h6 class="mb-1">Seguimiento pastoral</h6>
                      <span class="badge bg-<?php echo $claseEstado; ?>"><?php echo htmlspecialchars($estadoActual); ?></span>
                      <?php if ($sacramentoSolicitado !== ''): ?><span class="badge bg-primary-soft text-primary ms-1">Solicita: <?php echo htmlspecialchars($sacramentoSolicitado); ?></span><?php endif; ?>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                      <?php if ($tabReserva !== ''): ?><a class="btn btn-sm btn-outline-primary" href="VistaReservas.php?tab=<?php echo rawurlencode($tabReserva); ?>&amp;reservante=<?php echo rawurlencode($persona['CiPersona']); ?>"><i class="bi bi-calendar-plus me-1"></i>Ir a reserva</a><?php endif; ?>
                    </div>
                  </div>
                  <div class="row g-3 mt-1 small">
                    <div class="col-md-4"><strong>Reservas</strong>
                      <?php if (empty($procesos['reservas'])): ?><div class="text-muted">Sin reservas</div>
                      <?php else: foreach (array_slice($procesos['reservas'], 0, 3) as $reserva): ?><div><?php echo htmlspecialchars($reserva['TipoCel']); ?>: <?php echo htmlspecialchars($reserva['EstadoRes'] ?: 'Registrado'); ?> <span class="text-muted">(<?php echo htmlspecialchars($reserva['FechaReal']); ?>)</span></div><?php endforeach; endif; ?>
                    </div>
                    <div class="col-md-4"><strong>Catequesis</strong>
                      <?php if (empty($procesos['catequesis'])): ?><div class="text-muted">Sin inscripción</div>
                      <?php else: foreach (array_slice($procesos['catequesis'], 0, 3) as $catequesis): ?><div><?php echo htmlspecialchars($catequesis['Sacramento']); ?>: <?php echo $catequesis['IdInscripcion'] ? 'Inscrito' : htmlspecialchars($catequesis['EstadoCel']); ?></div><?php endforeach; endif; ?>
                    </div>
                    <div class="col-md-4"><strong>Sacramentos y certificados</strong>
                      <?php if (empty($procesos['sacramentos'])): ?><div class="text-muted">Sin sacramentos registrados</div>
                      <?php else: foreach (array_slice($procesos['sacramentos'], 0, 3) as $sacramento): ?><div><?php echo htmlspecialchars($sacramento['DescripSac']); ?>: <?php echo htmlspecialchars($sacramento['EstadoProceso']); ?></div><?php endforeach; endif; ?>
                      <?php if ($sacramentoSolicitado === 'Primera Comunión'): ?><div class="text-muted">Primera Comunión no genera certificado.</div><?php endif; ?>
                    </div>
                  </div>
                </div></div>
                <?php $identificacionProvisional = $controller->identificacionProvisional($persona); ?>
                <?php if ($identificacionProvisional['puede']): ?>
                  <?php
                    $documentoActual = trim((string) ($persona['NumeroDocumento'] ?? '')) !== '' ? $persona['NumeroDocumento'] : $persona['CiPersona'];
                  ?>
                  <div class="card border border-warning-subtle bg-warning-subtle mb-4">
                    <div class="card-body">
                      <h6 class="mb-1"><i class="bi bi-person-vcard me-2"></i>Actualizar el CI registrado</h6>
                      <?php if ($identificacionProvisional['motivo'] === 'partida'): ?>
                        <p class="small mb-3">Esta persona fue registrada con la partida de su certificado de nacimiento (<strong><?php echo htmlspecialchars($identificacionProvisional['partida'], ENT_QUOTES, 'UTF-8'); ?></strong>) porque todavía no contaba con CI. Cuando ya cuente con su carnet, registre aquí el número definitivo.</p>
                      <?php else: ?>
                        <p class="small mb-3">Esta persona fue registrada sin documento de identidad. Cuando ya cuente con su CI, registre aquí el número definitivo.</p>
                      <?php endif; ?>
                      <form method="POST" action="" class="row g-3 align-items-end" data-cambiar-ci>
                        <input type="hidden" name="cambiar_ci" value="1">
                        <input type="hidden" name="CiPersona" value="<?php echo htmlspecialchars($persona['CiPersona'], ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="buscarActual" value="<?php echo htmlspecialchars($terminoBusqueda, ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="col-sm-6 col-lg-4 form-group">
                          <label class="form-label">Documento actual:</label>
                          <input type="text" class="form-control" value="<?php echo htmlspecialchars((string) $documentoActual, ENT_QUOTES, 'UTF-8'); ?>" readonly data-ci-actual>
                        </div>
                        <div class="col-sm-6 col-lg-4 form-group">
                          <label class="form-label" for="NuevaCi">Nuevo CI:</label>
                          <input type="text" class="form-control" id="NuevaCi" name="NuevaCi" placeholder="Número de carnet" required minlength="4" maxlength="15" pattern="[A-Za-z0-9.\-]{4,15}" autocomplete="off">
                        </div>
                        <div class="col-sm-6 col-lg-4">
                          <button type="submit" class="btn btn-success-soft">Guardar CI definitivo</button>
                        </div>
                        <div class="col-12 d-none" data-ci-confirmacion>
                          <div class="alert alert-warning mb-0" role="alert">
                            <span data-ci-mensaje></span>
                            <button type="button" class="btn btn-sm btn-warning ms-2" data-ci-aceptar>Sí, cambiar</button>
                            <button type="button" class="btn btn-sm btn-light ms-1" data-ci-cancelar>Cancelar</button>
                          </div>
                        </div>
                      </form>
                      <p class="small text-muted mt-3 mb-0">Al guardar el CI se actualizan también sus reservas, solicitudes y certificados; el certificado de nacimiento se conserva.</p>
                    </div>
                  </div>
                <?php endif; ?>
                <form method="POST" action="" class="row g-3">
                  <input type="hidden" name="CiPersona" value="<?php echo $persona['CiPersona']; ?>">
                  <input type="hidden" name="buscarActual" value="<?php echo htmlspecialchars($terminoBusqueda, ENT_QUOTES, 'UTF-8'); ?>">

                  <div class="col-sm-6 col-lg-4 form-group">
                    <label class="form-label">Nombres</label>
                    <input type="text" class="form-control" id="Nombre" name="Nombre" value="<?php echo $persona['Nombre']; ?>">
                  </div>
                  <div class="col-sm-6 col-lg-4 form-group">
                    <label class="form-label">Apellido Paterno</label>
                    <input type="text" class="form-control" id="ApPaterno" name="ApPaterno" value="<?php echo $persona['ApPaterno']; ?>">
                  </div>
                  <div class="col-sm-6 col-lg-4 form-group">
                    <label class="form-label">Apellido Materno</label>
                    <input type="text" class="form-control" id="ApMaterno" name="ApMaterno" value="<?php echo $persona['ApMaterno']; ?>">
                  </div>

                  <div class="col-sm-6 col-lg-3 form-group">
                    <label class="form-label">Sexo:</label>
                    <?php $genero = $persona['Sexo']; ?>
                    <div>
                      <input type="radio" id="Sexo" name="Sexo" value="Varon" <?php echo ($genero == 'Varon') ? 'checked' : ''; ?>>
                      <label for="varon">Varon</label>
                      &nbsp;&nbsp;&nbsp;
                      <input type="radio" id="Sexo" name="Sexo" value="Mujer" <?php echo ($genero == 'Mujer') ? 'checked' : ''; ?>>
                      <label for="mujer">Mujer</label>
                    </div>
                  </div>

                  <div class="col-sm-6 col-lg-3 form-group">
                    <label class="form-label">Fecha de Nacimiento:</label>
                    <input type="date" class="form-control" id="FechaNac" name="FechaNac" value="<?php echo $persona['FechaNac']; ?>">
                  </div>
                  <div class="col-sm-6 col-lg-6 form-group">
                    <label class="form-label">Dirección:</label>
                    <input type="text" class="form-control" id="Direccion" name="Direccion" value="<?php echo $persona['Direccion']; ?>" autocomplete="new-password">
                  </div>
                  <div class="col-sm-6 col-lg-4 form-group">
                    <label class="form-label">Contacto:</label>
                    <input type="text" class="form-control" id="Contacto" name="Contacto" value="<?php echo $persona['Contacto']; ?>" autocomplete="new-password">
                  </div>
                  <div class="col-sm-6 col-lg-4 form-group">
                    <label class="form-label">Estado Civil:</label>
                    <input type="text" class="form-control" name="Estado_per" value="<?php echo $persona['Estado_per']; ?>" readonly>
                  </div>

                  <div class="col-sm-6 col-lg-3 me-2">
                    <label class="form-label" class="form-control">Nuevo Estado Civil:</label>
                    <select class="form-control" name="Estado_per2" data-search-enabled="true">
                      <option value="Soltero" <?php if ($persona['Estado_per'] == "Soltero") echo 'selected'; ?>>Soltero</option>
                      <option value="Soltera" <?php if ($persona['Estado_per'] == "Soltera") echo 'selected'; ?>>Soltera</option>
                      <option value="Casado" <?php if ($persona['Estado_per'] == "Casado") echo 'selected'; ?>>Casado</option>
                      <option value="Casada" <?php if ($persona['Estado_per'] == "Casada") echo 'selected'; ?>>Casada</option>
                      <option value="Viudo" <?php if ($persona['Estado_per'] == "Viudo") echo 'selected'; ?>>Viudo</option>
                      <option value="Viuda" <?php if ($persona['Estado_per'] == "Viuda") echo 'selected'; ?>>Viuda</option>
                    </select>
                  </div>
                                    <div class="col-sm-6 col-lg-4 form-group">
                    <label class="form-label">Correo electrónico:</label>
                    <input type="email" class="form-control" id="Correo" name="Correo" value="<?php echo $persona['Correo'] ?? ''; ?>" placeholder="Correo electrónico (opcional)" autocomplete="new-password">
                  </div>

                  <br>
                  <button type="submit" name="actualizar" class="btn btn-success-soft">Actualizar</button>
                </form>
              <?php endif; ?>
              <?php if (!$persona && isset($_GET['datPer'])): ?>
                <div class="my-sm-5 py-sm-5 text-center">
                  <i class="display-1 text-body-secondary bi bi-calendar2-event"></i>
                  <h4 class="mt-2 mb-3 text-body">Persona no encontrada</h4>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

      </div>
    </div>
  </main>
  <div class="modal fade persona-modal-ampliado" id="MostrarModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addModalLabel">Agregar Nueva Persona</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <?php renderFormularioPersonaAmpliado('crear', 'Registrar persona'); ?>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="ModalSeguimientoPastoral" tabindex="-1" aria-labelledby="tituloSeguimientoPastoral" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <div><h5 class="modal-title" id="tituloSeguimientoPastoral">Seguimiento pastoral</h5><div class="small text-muted" data-seguimiento-persona></div></div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="text-center py-4" data-seguimiento-cargando><span class="spinner-border spinner-border-sm text-primary me-2"></span>Consultando información...</div>
          <div class="d-none" data-seguimiento-contenido>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3"><strong>Estado actual:</strong><span class="badge bg-secondary" data-seguimiento-estado>Registrado</span><span class="badge bg-primary-soft text-primary d-none" data-seguimiento-solicitud></span></div>
            <div class="row g-3">
              <div class="col-md-4"><div class="seguimiento-bloque"><h6><i class="bi bi-calendar-check me-2"></i>Reservas</h6><div data-seguimiento-reservas></div></div></div>
              <div class="col-md-4"><div class="seguimiento-bloque"><h6><i class="bi bi-people me-2"></i>Catequesis</h6><div data-seguimiento-catequesis></div></div></div>
              <div class="col-md-4"><div class="seguimiento-bloque"><h6><i class="bi bi-file-earmark-check me-2"></i>Sacramentos y certificados</h6><div data-seguimiento-sacramentos></div></div></div>
            </div>
          </div>
          <div class="alert alert-danger d-none mb-0" data-seguimiento-error></div>
        </div>
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
        const todayB = new Date();
        const maxDateB = new Date(todayB.getFullYear(), todayB.getMonth(), todayB.getDate());
        const yearB = maxDateB.getFullYear();
        const monthB = String(maxDateB.getMonth() + 1).padStart(2, '0');
        const dayB = String(maxDateB.getDate()).padStart(2, '0');
        const fechaNacB = document.getElementById('FechaNacB');
        if (fechaNacB) fechaNacB.setAttribute('max', `${yearB}-${monthB}-${dayB}`);
    </script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      document.querySelectorAll('form[data-cambiar-ci]').forEach(function (form) {
        var campo = form.querySelector('input[name="NuevaCi"]');
        var caja = form.querySelector('[data-ci-confirmacion]');
        var texto = form.querySelector('[data-ci-mensaje]');
        var actual = form.querySelector('[data-ci-actual]');
        var aceptar = form.querySelector('[data-ci-aceptar]');
        var cancelar = form.querySelector('[data-ci-cancelar]');
        if (!campo || !caja || !actual || !aceptar || !cancelar) return;
        var confirmado = false;

        form.addEventListener('submit', function (evento) {
          if (confirmado) return;
          evento.preventDefault();
          if (!form.reportValidity()) return;
          texto.textContent = '¿Confirmar el cambio de CI de ' + actual.value + ' a ' + campo.value.trim() + '?';
          caja.classList.remove('d-none');
          campo.readOnly = true;
          aceptar.focus();
        });

        aceptar.addEventListener('click', function () {
          confirmado = true;
          form.submit();
        });

        cancelar.addEventListener('click', function () {
          caja.classList.add('d-none');
          campo.readOnly = false;
          campo.focus();
        });
      });
    });
  </script>

  <script src="../assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/functions.js"></script>
  <script src="../assets/js/tema.js"></script>
  <script src="../assets/js/persona-registro.js?v=<?= filemtime(__DIR__ . '/../assets/js/persona-registro.js') ?>"></script>
  <script src="../assets/js/persona-busqueda.js?v=<?= filemtime(__DIR__ . '/../assets/js/persona-busqueda.js') ?>"></script>
</body>

</html>
