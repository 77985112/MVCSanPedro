<?php
require_once '../Controlador/controladorCoordinadorAdmin.php';

session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: ../logout.php");
  exit();
}
$usuario = $_SESSION['usuario'];

if ($usuario['DescripCar'] != 'Párroco') {
  header("Location: VistaPersonal.php");
  exit();
}

$controller = new ControladorCoordinadorAdmin();
$coordinadores = $controller->obtenerCoordinadores();
$grupos = $controller->obtenerTodosLosGrupos();

$mensaje = '';
$mensaje1 = '';
$personaEncontrada = null;
$coordinadorEncontrado = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscarPersona'])) {
  $buscarNombre = $_POST['buscarNombre'] ?? '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscarCoordinador'])) {
  $ci = $_POST['ciCoordinador'];
  $coordinadorEncontrado = $controller->buscarCoordinador($ci);
  if (!$coordinadorEncontrado) {
    $mensaje1 = "No se encontró coordinador con CI: {$ci}";
  }
}

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
  $ci = $_POST['ciPersona'];
  $nombre = $_POST['nombre'];
  $apPaterno = $_POST['apPaterno'];
  $apMaterno = $_POST['apMaterno'];
  $sexo = $_POST['sexo'];
  $fechaNac = $_POST['fechaNac'];
  $direccion = $_POST['direccion'];
  $contacto = $_POST['contacto'];
  $estadoCivil = $_POST['estadoCivil'];

  if ($controller->registrarPersona($ci, $nombre, $apPaterno, $apMaterno, $sexo, $fechaNac, $direccion, $contacto, $estadoCivil)) {
    $personaEncontrada = $controller->buscarPersona($ci);
    $mensaje = "<strong>Persona registrada con éxito.</strong> Ahora seleccione el sacramento para asignar.";
  } else {
    $mensaje1 = "<strong>Error:</strong> No se pudo registrar la persona. Verifique que el CI no esté duplicado.";
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['asignarCoordinador'])) {
  $ci = $_POST['CiPersona'];
  $sacramento = $_POST['Sacramento'];
  $idGrupo = $_POST['IdGrupo'];
  $usuario = $_POST['UsuarioCat'];
  $clave = $_POST['ClaveCat'];

  $existente = $controller->verificarCoordinadorExistente($sacramento);
  if ($existente) {
    $mensaje1 = "<strong>Error:</strong> Ya existe un Coordinador para {$sacramento}.";
  } else {
    if ($controller->registrarCoordinador($ci, $sacramento, $idGrupo, $usuario, $clave)) {
      $persona = $controller->buscarPersona($ci);
      $_SESSION['registroCatequista'] = [
        'Nombre' => $persona['Nombre'] . ' ' . $persona['ApPaterno'],
        'UsuarioCat' => $usuario,
        'ClaveCat' => $clave
      ];
      $mensaje = "<strong>Coordinador asignado con éxito.</strong> Nombre: {$persona['Nombre']} {$persona['ApPaterno']}, Usuario: {$usuario}, Clave: {$clave}";
      $personaEncontrada = null;
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
  <link rel="stylesheet" type="text/css" href="../assets/css/style.css">
  <script src="../assets/js/jquery-3.6.4.min.js"></script>
</head>

<body class="sidebar-start-enabled">
  <header class="navbar-light fixed-top header-static bg-mode">
    <nav class="navbar navbar-expand-lg">
      <button class="btn text-secondary py-0 me-3 sidebar-start-toggle"><i class="bi bi-justify-left fs-3 lh-0"></i></button>
      <div class="container">
        <a class="navbar-brand" href="VistaPersonal.php">
          <img class="light-mode-item navbar-brand-item" src="../assets/images/logo.png" alt="logo">
          <img class="dark-mode-item navbar-brand-item" src="../assets/images/logo-light.png" alt="logo">
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
              <li class="dropdown-divider"></li>
              <li><a class="dropdown-item bg-danger-soft-hover" href="../logout.php"><i class="bi bi-power fa-fw me-2"></i>Salir</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </nav>
  </header>

  <main>
    <div class="container">
      <div class="row g-4">
        <div class="col-lg-3">
          <nav class="navbar navbar-expand-lg mx-0">
            <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasSideNavbar">
              <div class="offcanvas-header">
                <a class="navbar-brand" href="#">
                  <img class="light-mode-item navbar-brand-item" src="../assets/images/logo.png" alt="logo">
                  <img class="dark-mode-item navbar-brand-item" src="../assets/images/logo-light.png" alt="logo">
                </a>
                <button type="button" class="btn-close text-reset ms-auto" data-bs-dismiss="offcanvas" aria-label="Close"></button>
              </div>
              <div class="offcanvas-body d-block px-2 px-lg-0">
                <div class="card overflow-hidden">
                  <div class="h-50px" style="background-image:url(<?php echo ($usuario['FondoPer'] == 'Ninguno' || empty($usuario['FondoPer'])) ? '../assets/images/fondo/Ninguno.jpg' : "../assets/images/fondo/{$usuario['FondoPer']}"; ?>); background-position: center; background-size: cover; background-repeat: no-repeat;"></div>
                  <div class="card-body pt-0">
                    <div class="text-center">
                      <div class="avatar avatar-lg mt-n5 mb-3">
                        <img class="avatar-img rounded border border-white border-3" src="<?php echo ($usuario['PerfilPer'] == 'Ninguno' || empty($usuario['PerfilPer'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$usuario['PerfilPer']}"; ?>" alt="">
                      </div>
                      <h5 class="mb-0"><?php echo $usuario['Nombre'] . " " . $usuario['Paterno'] . " " . $usuario['Materno']; ?></h5>
                      <small id="cargo"><?php echo $usuario['DescripCar']; ?></small>
                      <p class="mt-3"><?php echo $usuario['FrasePer']; ?></p>
                    </div>
                    <hr>
                    <ul class="nav nav-link-secondary flex-column fw-bold gap-2">
                      <li class="nav-item">
                        <a class="nav-link" href="VistaPersonal.php"> <img class="me-2 h-20px fa-fw" src="../assets/images/icon/mensaje.png" alt=""><span>Inicio </span></a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link" href="VistaPersona.php"> <img class="me-2 h-20px fa-fw" src="../assets/images/icon/nuevo.png" alt=""><span>Persona </span></a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link" href="VistaReservas.php"> <img class="me-2 h-20px fa-fw" src="../assets/images/icon/instrucciones.png" alt=""><span>Celebración </span></a>
                      </li>
                      <?php if ($usuario['DescripCar'] == 'Párroco') { ?>
                        <li class="nav-item">
                          <a class="nav-link" href="VistaGesPersonal.php"> <img class="me-2 h-20px fa-fw" src="../assets/images/icon/seguro.png" alt=""><span>Personal</span></a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link active" href="VistaCoordinadorAdmin.php"> <span>🔥 ⚪ Coordinador</span></a>
                        </li>
                      <?php } ?>
                      <li class="nav-item">
                        <a class="nav-link" href="VistaSolicitante.php"> <img class="me-2 h-20px fa-fw" src="../assets/images/icon/contrato.png" alt=""><span>Certificado </span></a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link" href="VistaEmitidos.php"> <img class="me-2 h-20px fa-fw" src="../assets/images/icon/Emitido.png" alt=""><span>Certificado Emitido </span></a>
                      </li>
                    </ul>
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
                <div class="col-md-6">
                  <label class="form-label">Buscar por nombre o CI</label>
                  <input type="text" class="form-control" name="buscarNombre" placeholder="Nombre, apellido o CI" required
                    value="<?php echo isset($_POST['buscarNombre']) ? htmlspecialchars($_POST['buscarNombre']) : ''; ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                  <button type="submit" name="buscarPersona" class="btn btn-primary w-100">Buscar</button>
                </div>
              </form>

              <?php if (isset($_POST['buscarNombre']) && !empty($_POST['buscarNombre'])): ?>
                <?php
                $personasEncontradas = $controller->buscarPersonasPorNombre($_POST['buscarNombre']);
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
                          <th>Acción</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($personasEncontradas as $p): ?>
                          <tr>
                            <td><?php echo $p['CiPersona']; ?></td>
                            <td><?php echo $p['Nombre']; ?></td>
                            <td><?php echo $p['ApPaterno']; ?></td>
                            <td><?php echo $p['ApMaterno']; ?></td>
                            <td>
                              <form method="POST" style="display:inline;">
                                <input type="hidden" name="ciSeleccionada" value="<?php echo $p['CiPersona']; ?>">
                                <button type="submit" name="seleccionarPersona" class="btn btn-sm btn-outline-primary">Seleccionar</button>
                              </form>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                <?php else: ?>
                  <div class="alert alert-warning mt-3">No se encontraron personas con "<?php echo htmlspecialchars($_POST['buscarNombre']); ?>"</div>
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
                          <input type="text" class="form-control" name="CiPersona" value="<?php echo $personaEncontrada['CiPersona']; ?>" readonly>
                        </div>
                        <div class="col-md-3">
                          <label class="form-label">Nombre</label>
                          <input type="text" class="form-control" value="<?php echo $personaEncontrada['Nombre']; ?>" readonly>
                        </div>
                        <div class="col-md-3">
                          <label class="form-label">Apellido Paterno</label>
                          <input type="text" class="form-control" value="<?php echo $personaEncontrada['ApPaterno']; ?>" readonly>
                        </div>
                        <div class="col-md-3">
                          <label class="form-label">Apellido Materno</label>
                          <input type="text" class="form-control" value="<?php echo $personaEncontrada['ApMaterno']; ?>" readonly>
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

              <h5 class="mb-3">Buscar Coordinador por CI</h5>
              <form method="POST" class="row g-3 mb-3">
                <div class="col-md-4">
                  <label class="form-label">CI del Coordinador</label>
                  <input type="text" class="form-control" name="ciCoordinador" placeholder="Ingrese CI" required>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                  <button type="submit" name="buscarCoordinador" class="btn btn-warning w-100">Buscar</button>
                </div>
              </form>

              <?php if ($coordinadorEncontrado): ?>
                <div class="card border-warning mb-3">
                  <div class="card-body">
                    <h6 class="card-title">Coordinador Encontrado</h6>
                    <div class="row g-2">
                      <div class="col-md-2"><strong>CI:</strong> <?php echo $coordinadorEncontrado['CiCat']; ?></div>
                      <div class="col-md-3"><strong>Nombre:</strong> <?php echo $coordinadorEncontrado['Nombre'] . ' ' . $coordinadorEncontrado['ApPaterno']; ?></div>
                      <div class="col-md-2"><strong>Sacramento:</strong> <?php echo $coordinadorEncontrado['Sacramento']; ?></div>
                      <div class="col-md-2"><strong>Grupo:</strong> <?php echo $coordinadorEncontrado['NombreGrupo']; ?></div>
                      <div class="col-md-3 text-end">
                        <?php if ($coordinadorEncontrado['EstadoCat'] == 'Activo'): ?>
                          <form method="POST" class="d-inline">
                            <input type="hidden" name="ciBloquear" value="<?php echo $coordinadorEncontrado['CiCat']; ?>">
                            <button type="submit" name="bloquearCoordinador" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro de bloquear a este coordinador?')">
                              <i class="bi bi-lock-fill"></i> Bloquear
                            </button>
                          </form>
                        <?php else: ?>
                          <form method="POST" class="d-inline">
                            <input type="hidden" name="ciActivar" value="<?php echo $coordinadorEncontrado['CiCat']; ?>">
                            <button type="submit" name="activarCoordinador" class="btn btn-success btn-sm" onclick="return confirm('¿Está seguro de activar a este coordinador?')">
                              <i class="bi bi-unlock-fill"></i> Activar
                            </button>
                          </form>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                </div>
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
  <div class="modal fade" id="registrarPersonaModal" tabindex="-1" aria-labelledby="registrarPersonaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form method="POST">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title" id="registrarPersonaModalLabel">Registrar Nueva Persona</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label">CI *</label>
                <input type="text" class="form-control" name="ciPersona" required maxlength="15">
              </div>
              <div class="col-md-4">
                <label class="form-label">Nombre *</label>
                <input type="text" class="form-control" name="nombre" required>
              </div>
              <div class="col-md-4">
                <label class="form-label">Apellido Paterno *</label>
                <input type="text" class="form-control" name="apPaterno" required>
              </div>
              <div class="col-md-4">
                <label class="form-label">Apellido Materno *</label>
                <input type="text" class="form-control" name="apMaterno" required>
              </div>
              <div class="col-md-4">
                <label class="form-label">Sexo *</label>
                <select class="form-select" name="sexo" required>
                  <option value="">Seleccionar...</option>
                  <option value="M">Masculino</option>
                  <option value="F">Femenino</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Fecha de Nacimiento</label>
                <input type="date" class="form-control" name="fechaNac">
              </div>
              <div class="col-md-6">
                <label class="form-label">Dirección</label>
                <input type="text" class="form-control" name="direccion">
              </div>
              <div class="col-md-3">
                <label class="form-label">Contacto</label>
                <input type="text" class="form-control" name="contacto">
              </div>
              <div class="col-md-3">
                <label class="form-label">Estado Civil</label>
                <select class="form-select" name="estadoCivil">
                  <option value="">Seleccionar...</option>
                  <option value="Soltero(a)">Soltero(a)</option>
                  <option value="Casado(a)">Casado(a)</option>
                  <option value="Divorciado(a)">Divorciado(a)</option>
                  <option value="Viudo(a)">Viudo(a)</option>
                  <option value="Unión Libre">Unión Libre</option>
                </select>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" name="registrarPersona" class="btn btn-success">Registrar Persona</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="../assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/functions.js"></script>
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
  </script>
</body>

</html>
