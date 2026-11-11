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
$grupos = $controller->obtenerTodosLosGrupos();

$mensaje = '';
$mensaje1 = '';

// Gestión (año) seleccionada
$anioActual = (int) date('Y');
$gestionSeleccionada = (int) ($_POST['Gestion'] ?? $_GET['gestion'] ?? $anioActual);
if ($gestionSeleccionada < $anioActual - 3 || $gestionSeleccionada > $anioActual + 3) {
    $gestionSeleccionada = $anioActual;
}
$gestiones = range($anioActual - 1, $anioActual + 2);

// Buscador de personas para el formulario (AJAX)
if (isset($_GET['buscarPersona'])) {
    header('Content-Type: application/json; charset=utf-8');
    $texto = trim((string) $_GET['buscarPersona']);
    $lista = $texto === '' ? [] : $controller->buscarPersonasPorNombre($texto);
    $salida = [];
    foreach ($lista as $p) {
        $salida[] = [
            'ci' => $p['CiPersona'],
            'nombre' => trim($p['Nombre'] . ' ' . $p['ApPaterno'] . ' ' . $p['ApMaterno']),
            'doc' => trim((string) ($p['NumeroDocumento'] ?? '')),
        ];
    }
    echo json_encode(['ok' => true, 'personas' => $salida]);
    exit;
}

// Registro de persona nueva (modal existente)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrarPersona'])) {
    if ($personaController->crearPersona($_POST)) {
        $ciRegistrado = $personaController->obtenerCiPersonaRegistrada();
        $mensaje = '<strong>Persona registrada con éxito.</strong> Identificador: ' . htmlspecialchars($ciRegistrado);
    } else {
        $mensaje1 = '<strong>Error:</strong> ' . htmlspecialchars($personaController->obtenerError() ?: 'No se pudo registrar la persona.');
    }
}

// Acciones del módulo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $accion = (string) $_POST['accion'];

    if ($accion === 'cambiar_estado') {
        $ci = trim((string) ($_POST['ci'] ?? ''));
        $nuevo = ($_POST['estado'] ?? '') === 'Activo' ? 'Activo' : 'Inactivo';
        $ok = $nuevo === 'Activo' ? $controller->activarCoordinador($ci) : $controller->bloquearCoordinador($ci);
        if ($ok) {
            $mensaje = $nuevo === 'Activo' ? 'Coordinador activado.' : 'Coordinador inactivado.';
        } else {
            $mensaje1 = 'No se pudo cambiar el estado del coordinador.';
        }
    }

    if ($accion === 'asignar_coordinador') {
        $ci = trim((string) ($_POST['CiPersona'] ?? ''));
        $sacramento = trim((string) ($_POST['Sacramento'] ?? ''));
        $usuarioCat = trim((string) ($_POST['UsuarioCat'] ?? ''));
        $clave = trim((string) ($_POST['ClaveCat'] ?? ''));

        $errores = [];
        if ($ci === '') {
            $errores[] = 'Escribe el CI del nuevo coordinador.';
        } elseif (!$controller->buscarPersona($ci)) {
            $errores[] = 'La persona no está registrada. Regístrala primero en el módulo Persona.';
        }
        if (!in_array($sacramento, ['Primera Comunión', 'Confirmación'], true)) {
            $errores[] = 'Selecciona el sacramento.';
        }
        if ($usuarioCat === '') {
            $errores[] = 'Escribe el usuario de acceso.';
        } elseif ($controller->verificarUsuarioDisponible($usuarioCat)) {
            $errores[] = 'Ese usuario ya lo usa otro catequista.';
        }
        if (mb_strlen($clave) < 6) {
            $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
        }

        if ($errores) {
            $mensaje1 = implode(' ', $errores);
        } else {
            $actual = $controller->verificarCoordinadorExistente($sacramento, '', $gestionSeleccionada);
            $idGrupo = $actual ? (int) $actual['IdGrupo'] : 0;

            // Si no hay coordinador previo, se usa el primer grupo de ese sacramento.
            if ($idGrupo <= 0) {
                foreach ($grupos as $g) {
                    if (trim((string) $g['SacramentoGrupo']) === $sacramento) {
                        $idGrupo = (int) $g['IdGrupo'];
                        break;
                    }
                }
            }

            if ($idGrupo <= 0) {
                $mensaje1 = 'No hay grupos registrados para ' . htmlspecialchars($sacramento) . '.';
            } else {
                if ($actual && $actual['CiCat'] === $ci) {
                    // Misma persona: solo se actualizan sus credenciales.
                    $ok = $controller->actualizarCargo($ci, $sacramento, $idGrupo, $usuarioCat, $clave);
                    $mensaje = 'Se actualizaron los datos del coordinador.';
                } else {
                    if ($actual) {
                        // Se cierra la gestión del anterior y se le quita el acceso.
                        $controller->cerrarAsignacion($actual['CiCat'], $gestionSeleccionada);
                        $controller->bloquearCoordinador($actual['CiCat']);
                    }
                    $ok = $controller->registrarCoordinador($ci, $sacramento, $idGrupo, $usuarioCat, $clave, $gestionSeleccionada);
                    if ($ok) {
                        $persona = $controller->buscarPersona($ci);
                        $mensaje = 'Coordinador asignado: <strong>' . htmlspecialchars(trim($persona['Nombre'] . ' ' . $persona['ApPaterno'])) . '</strong> para ' . htmlspecialchars($sacramento) . ' · Gestión ' . $gestionSeleccionada . '.';
                        if ($actual) {
                            $mensaje .= ' El coordinador anterior finalizó su gestión y quedó inactivo.';
                        }
                    }
                }

                if (!$ok) {
                    $mensaje1 = 'No se pudo asignar el coordinador.';
                }
            }
        }
    }
}

$coordinadores = $controller->obtenerCoordinadores($gestionSeleccionada);
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
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2 border-0 pb-0">
              <div>
                <h1 class="h4 card-title mb-1">Coordinadores</h1>
                <p class="text-muted small mb-0">Asigna el coordinador de cada sacramento cuando termina su gestión.</p>
              </div>
              <form method="GET" class="d-flex align-items-center gap-2">
                <label class="form-label mb-0 small text-muted" for="gestionSel">Gestión</label>
                <select class="form-select form-select-sm" id="gestionSel" name="gestion" onchange="this.form.submit()" style="width:auto;">
                  <?php foreach ($gestiones as $g): ?>
                    <option value="<?php echo $g; ?>" <?php echo $g === $gestionSeleccionada ? 'selected' : ''; ?>><?php echo $g; ?></option>
                  <?php endforeach; ?>
                </select>
              </form>
            </div>
            <div class="card-body">

              <div class="row g-3 mb-4">
                <?php
                $resumenCoordinadores = [
                  ['sacramento' => 'Confirmación', 'clase' => 'border-danger', 'texto' => 'text-danger', 'estilo' => ''],
                  ['sacramento' => 'Primera Comunión', 'clase' => '', 'texto' => '', 'estilo' => 'style="border-color:#e0a800;"'],
                ];
                foreach ($resumenCoordinadores as $bloque):
                  $activo = null;
                  $cerrado = null;
                  foreach ($coordinadores as $c) {
                    if (trim((string) $c['Sacramento']) !== $bloque['sacramento']) { continue; }
                    if ((int) $c['Vigente'] === 1) { $activo = $c; break; }
                    if ($cerrado === null) { $cerrado = $c; }
                  }
                  $mostrar = $activo ?: $cerrado;
                ?>
                  <div class="col-md-6">
                    <div class="card border <?php echo $bloque['clase']; ?>" <?php echo $bloque['estilo']; ?>>
                      <div class="card-body text-center">
                        <h5 class="card-title <?php echo $bloque['texto']; ?>" <?php echo $bloque['sacramento'] === 'Primera Comunión' ? 'style="color:#a67c00;"' : ''; ?>><?php echo $bloque['sacramento']; ?></h5>
                        <?php if ($mostrar): ?>
                          <p class="mb-1"><strong><?php echo htmlspecialchars($mostrar['Nombre'] . ' ' . $mostrar['ApPaterno']); ?></strong></p>
                          <small class="text-muted">CI: <?php echo htmlspecialchars($mostrar['CiCat']); ?> | Grupo: <?php echo htmlspecialchars((string) $mostrar['NombreGrupo']); ?></small>
                          <div class="mt-2">
                            <?php if ((int) $mostrar['Vigente'] === 1): ?>
                              <span class="badge bg-success">Vigente en <?php echo $gestionSeleccionada; ?></span>
                            <?php else: ?>
                              <span class="badge bg-secondary">Finalizó el <?php echo !empty($mostrar['FechaFinCat']) ? date('d/m/Y', strtotime($mostrar['FechaFinCat'])) : '-'; ?></span>
                            <?php endif; ?>
                          </div>
                        <?php else: ?>
                          <p class="text-muted mb-2">Sin coordinador asignado</p>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>

              <hr>

              <h6 class="mb-3"><i class="bi bi-person-plus me-1"></i>Asignar nuevo coordinador</h6>
              <form method="POST" class="row g-3">
                <input type="hidden" name="accion" value="asignar_coordinador">
                <input type="hidden" name="Gestion" value="<?php echo $gestionSeleccionada; ?>">
                <div class="col-md-3">
                  <label class="form-label" for="sacramentoCoor">Sacramento</label>
                  <select class="form-select" id="sacramentoCoor" name="Sacramento" required>
                    <option value="">Seleccionar…</option>
                    <option value="Primera Comunión">Primera Comunión</option>
                    <option value="Confirmación">Confirmación</option>
                  </select>
                </div>
                <div class="col-md-3">
                  <label class="form-label" for="buscarPersonaCoor">Buscar persona</label>
                  <div class="position-relative">
                    <input type="text" class="form-control" id="buscarPersonaCoor" placeholder="Nombre, apellido o carnet" autocomplete="off">
                    <div id="resultadosPersona" class="list-group position-absolute w-100 shadow-sm" style="z-index:1050;max-height:240px;overflow:auto;display:none;background:#fff;border-radius:.5rem;"></div>
                  </div>
                  <div class="form-text" id="ayudaPersona">Escribe al menos 2 letras y elige de la lista.</div>
                  <input type="hidden" name="CiPersona" id="ciPersonaCoor">
                </div>
                <div class="col-md-2">
                  <label class="form-label" for="usuarioCoor">Usuario</label>
                  <input type="text" class="form-control" id="usuarioCoor" name="UsuarioCat" maxlength="100" required>
                </div>
                <div class="col-md-2">
                  <label class="form-label" for="claveCoor">Contraseña</label>
                  <input type="text" class="form-control" id="claveCoor" name="ClaveCat" minlength="6" required>
                </div>
                <div class="col-md-2">
                  <label class="form-label d-none d-md-block">&nbsp;</label>
                  <button type="submit" class="btn btn-primary w-100">Asignar</button>
                </div>
                <div class="col-12">
                  <div class="alert alert-warning py-2 mb-0 small">
                    Al asignar, el coordinador activo de ese sacramento pasa automáticamente a <strong>Inactivo</strong>.
                  </div>
                </div>
              </form>

              <hr>

              <h6 class="mb-3">Coordinadores · Gestión <?php echo $gestionSeleccionada; ?></h6>
              <div class="table-responsive">
                <table class="table align-middle mb-0">
                  <thead>
                    <tr>
                      <th>Nombre</th>
                      <th>CI</th>
                      <th>Sacramento</th>
                      <th>Usuario</th>
                      <th>Vigencia</th>
                      <th>Acceso</th>
                      <th class="text-end">Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (!$coordinadores): ?>
                      <tr><td colspan="7" class="text-center text-muted py-4">No hay coordinadores registrados en esta gestión.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($coordinadores as $coor): ?>
                      <?php
                      $esConf = trim((string) $coor['Sacramento']) === 'Confirmación';
                      $esPc = trim((string) $coor['Sacramento']) === 'Primera Comunión';
                      $esActivo = trim((string) $coor['EstadoCat']) === 'Activo';
                      ?>
                      <tr>
                        <td><strong><?php echo htmlspecialchars($coor['Nombre'] . ' ' . $coor['ApPaterno'] . ' ' . $coor['ApMaterno']); ?></strong></td>
                        <td><?php echo htmlspecialchars($coor['CiCat']); ?></td>
                        <td>
                          <?php if ($esConf): ?>
                            <span class="badge bg-danger">Confirmación</span>
                          <?php elseif ($esPc): ?>
                            <span class="badge" style="background:#e0a800;color:#3d2b00;">Primera Comunión</span>
                          <?php else: ?>
                            <span class="badge bg-primary"><?php echo htmlspecialchars($coor['Sacramento']); ?></span>
                          <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($coor['UsuarioCat']); ?></td>
                        <td>
                          <?php if ((int) $coor['Vigente'] === 1): ?>
                            <span class="badge bg-success">Vigente</span>
                          <?php else: ?>
                            <span class="badge bg-secondary">Finalizó <?php echo !empty($coor['FechaFinCat']) ? date('d/m/Y', strtotime($coor['FechaFinCat'])) : ''; ?></span>
                          <?php endif; ?>
                        </td>
                        <td>
                          <span class="badge <?php echo $esActivo ? 'bg-success' : 'bg-danger'; ?>">
                            <?php echo $esActivo ? 'Activo' : 'Inactivo'; ?>
                          </span>
                        </td>
                        <td class="text-end">
                          <button type="button" class="btn btn-sm btn-outline-secondary" onclick="verClave('<?php echo htmlspecialchars($coor['CiCat']); ?>')" title="Ver contraseña">
                            <i class="bi bi-eye" id="ojo-<?php echo htmlspecialchars($coor['CiCat']); ?>"></i>
                          </button>
                          <span id="clave-<?php echo htmlspecialchars($coor['CiCat']); ?>" class="small text-muted d-none ms-1"><?php echo htmlspecialchars($coor['ClaveCat']); ?></span>

                          <form method="POST" class="d-inline">
                            <input type="hidden" name="accion" value="cambiar_estado">
                            <input type="hidden" name="Gestion" value="<?php echo $gestionSeleccionada; ?>">
                            <input type="hidden" name="ci" value="<?php echo htmlspecialchars($coor['CiCat']); ?>">
                            <input type="hidden" name="estado" value="<?php echo $esActivo ? 'Inactivo' : 'Activo'; ?>">
                            <button type="submit" class="btn btn-sm <?php echo $esActivo ? 'btn-outline-danger' : 'btn-outline-success'; ?>">
                              <?php echo $esActivo ? 'Inactivar' : 'Activar'; ?>
                            </button>
                          </form>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>

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
    // Buscador de personas: nombre, apellido o carnet
    const buscadorPersona = document.getElementById('buscarPersonaCoor');
    const resultadosPersona = document.getElementById('resultadosPersona');
    const ayudaPersona = document.getElementById('ayudaPersona');
    const ciPersonaCoor = document.getElementById('ciPersonaCoor');
    let temporizadorBusqueda;

    function pintarPersonas(lista) {
      resultadosPersona.innerHTML = '';
      if (!lista.length) {
        resultadosPersona.style.display = 'none';
        ayudaPersona.className = 'form-text text-danger';
        ayudaPersona.textContent = 'No se encontraron personas con ese dato.';
        return;
      }
      lista.forEach(p => {
        const boton = document.createElement('button');
        boton.type = 'button';
        boton.className = 'list-group-item list-group-item-action py-2';
        boton.innerHTML = '<strong>' + p.nombre + '</strong><br><small class="text-muted">CI: ' + p.ci + (p.doc ? ' · Doc: ' + p.doc : '') + '</small>';
        boton.addEventListener('click', function () {
          ciPersonaCoor.value = p.ci;
          buscadorPersona.value = p.nombre;
          ayudaPersona.className = 'form-text text-success';
          ayudaPersona.textContent = 'Seleccionado: ' + p.nombre + ' (CI ' + p.ci + ')';
          resultadosPersona.style.display = 'none';
        });
        resultadosPersona.appendChild(boton);
      });
      resultadosPersona.style.display = 'block';
    }

    if (buscadorPersona) {
      buscadorPersona.addEventListener('input', function () {
        const texto = this.value.trim();
        ciPersonaCoor.value = '';
        ayudaPersona.className = 'form-text';
        ayudaPersona.textContent = 'Escribe al menos 2 letras y elige de la lista.';
        clearTimeout(temporizadorBusqueda);
        if (texto.length < 2) {
          resultadosPersona.style.display = 'none';
          return;
        }
        temporizadorBusqueda = setTimeout(function () {
          fetch('VistaCoordinadorAdmin.php?buscarPersona=' + encodeURIComponent(texto), { credentials: 'same-origin' })
            .then(r => r.json())
            .then(d => pintarPersonas(d.personas || []))
            .catch(() => {});
        }, 250);
      });

      document.addEventListener('click', function (evento) {
        if (evento.target !== buscadorPersona && !resultadosPersona.contains(evento.target)) {
          resultadosPersona.style.display = 'none';
        }
      });
    }

    function verClave(ci) {
      const span = document.getElementById('clave-' + ci);
      const ojo = document.getElementById('ojo-' + ci);
      if (!span) return;
      const oculto = span.classList.toggle('d-none');
      if (ojo) ojo.className = oculto ? 'bi bi-eye' : 'bi bi-eye-slash';
    }


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
