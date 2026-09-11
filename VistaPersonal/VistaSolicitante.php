<?php
require_once '../Controlador/controladorSolicitud.php';
require_once '../Controlador/ControladorPersona.php';

session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: ../logout.php");
  exit();
}
$usuario = $_SESSION['usuario'];

$controllerPersona = new ControladorPersona();
$controllerSacramento = new ControladorInsSacramento();

$mensaje = '';
$certificadoBautizo = [];
$certificadoConfirmacion = [];
$certificadoMatrimonio = [];

$persona = null;

if (isset($_GET['ciPersona'])) {
  $ciPersona = $_GET['ciPersona'];
  $persona = $controllerPersona->buscarPersona($ciPersona);

  if ($persona) {
    echo json_encode($persona);
  } else {
    echo json_encode(['error' => 'Persona no encontrada']);
  }
  exit;
}

// AJAX persona creation is handled by ajaxCrearPersona.php



if (isset($_GET['bautizo'])) {
  $certificadoBautizo = $controllerSacramento->VerInsSacramentoBautizo($_GET['ciPersonaBus'], null);
}
if (isset($_GET['confirmacion'])) {
  $certificadoConfirmacion = $controllerSacramento->VerInsSacramentoConfirmacion($_GET['ciPersonaBus'], null);
}
if (isset($_GET['matrimonio'])) {
  $certificadoMatrimonio = $controllerSacramento->VerInsSacramentoMatrimonio($_GET['ciPersonaBus']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registrar_bautizo'])) {
  if ($controllerSacramento->RegistrarBautizo($_POST)) {
    $mensaje = "Bautizo registrado con éxito.";
  } else {
    $mensaje = "Error al registrar el bautizo.";
  }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registrar_confirmacion'])) {
  if ($controllerSacramento->RegistrarConfirmacion($_POST)) {
    $mensaje = "Confirmación registrada con éxito.";
  } else {
    $mensaje = "Error al registrar la confirmación.";
  }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registrar_matrimonio'])) {
  if ($controllerSacramento->RegistrarMatrimonio($_POST)) {
    $mensaje = "Matrimonio registrado con éxito.";
  } else {
    $mensaje = "Error al registrar el matrimonio.";
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


<body>
  <header class="navbar-light fixed-top header-static bg-mode">
    <nav class="navbar navbar-expand-lg">
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
              <img class="avatar-img rounded border border-white border-3" src="<?php echo ($usuario['PerfilPer'] == 'Ninguno' || empty($usuario['PerfilPer'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$usuario['PerfilPer']}"; ?>" alt="">
            </a>
            <ul class="dropdown-menu dropdown-animation dropdown-menu-end pt-3 small me-md-n3" aria-labelledby="profileDropdown">
              <li class="px-3">
                <div class="d-flex align-items-center position-relative">
                  <div class="avatar me-3">
                    <img class="avatar-img rounded-circle" src="<?php echo ($usuario['PerfilPer'] == 'Ninguno' || empty($usuario['PerfilPer'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$usuario['PerfilPer']}"; ?>" alt="avatar">
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
    <div class="container">
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
                  <img class="light-mode-item navbar-brand-item" src="../assets/images/logo.png" alt="logo">
                  <img class="dark-mode-item navbar-brand-item" src="../assets/images/logo-light.png" alt="logo">
                </a>
                <button type="button" class="btn-close text-reset ms-auto" data-bs-dismiss="offcanvas" aria-label="Close"></button>
              </div>

              <div class="offcanvas-body d-block px-2 px-lg-0">
                <div class="card overflow-hidden">
                  <div class="h-50px" style="background-image:url(<?php echo ($usuario['FondoPer'] == 'Ninguno' || empty($usuario['FondoPer'])) ? '../assets/images/fondo/Ninguno.jpg' : "../assets/images/fondo/{$usuario['FondoPer']}"; ?>); background-position: center; background-size: cover; background-repeat: no-repeat;">
                  </div>
                  <div class="card-body pt-0">
                    <div class="text-center">
                      <div class="avatar avatar-lg mt-n5 mb-3">
                        <img class="avatar-img rounded border border-white border-3" src="<?php echo ($usuario['PerfilPer'] == 'Ninguno' || empty($usuario['PerfilPer'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$usuario['PerfilPer']}"; ?>" alt="">
                      </div>
                      <h5 class="mb-0"> <a href="PerfilAdmin.php"><?php echo $usuario['Nombre'] . " " . $usuario['Paterno'] . " " . $usuario['Materno']; ?></a> </h5>
                      <small><?php echo $usuario['DescripCar']; ?></small>
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
                          <a class="nav-link" href="VistaCoordinadorAdmin.php"> <span>🔥 ⚪ Coordinador</span></a>
                        </li>
                      <?php } ?>
                      <li class="nav-item">
                        <a class="nav-link active" href="VistaSolicitante.php"> <img class="me-2 h-20px fa-fw" src="../assets/images/icon/contrato.png" alt=""><span>Certificado </span></a>
                      </li>
                      <?php if ($usuario['DescripCar'] != "Personal") { ?>
                        <li class="nav-item">
                          <a class="nav-link" href="VistaEmitidos.php"> <img class="me-2 h-20px fa-fw" src="../assets/images/icon/Emitido.png" alt=""><span>Certificado Emitido </span></a>
                        </li>
                      <?php } ?>

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

        <?php if ($mensaje): ?>
          <p><?php echo $mensaje; ?></p>
        <?php endif; ?>


        <div class="col-md-12 col-lg-6 vstack gap-4">
          <div class="card h-100">
            <div class="card-header d-sm-flex align-items-center text-center justify-content-sm-between border-0 pb-0">
              <h1 class="h4 card-title">Información de Certificados</h1>

              <form method="GET" class="mb-3 d-flex align-items-end">
                <div class="form-group mb-0 me-2">
                  <input type="text" class="form-control" name="ciPersonaBus" placeholder="Identificación" value="<?php echo isset($_GET['ciPersona']) ? $_GET['ciPersona'] : ''; ?>" required>
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
                <li class="nav-item"> <a class="nav-link active" data-bs-toggle="tab" href="#tab-1"> Información </a> </li>
                <li class="nav-item"> <a class="nav-link" data-bs-toggle="tab" href="#tab-2"> Registrar Bautizo </a> </li>
                <li class="nav-item"> <a class="nav-link" data-bs-toggle="tab" href="#tab-3"> Registrar Confirmación </a> </li>
                <li class="nav-item"> <a class="nav-link" data-bs-toggle="tab" href="#tab-4"> Registrar Matrimonio </a> </li>
              </ul>
              <div class="tab-content">
                <div class="tab-pane show active" id="tab-1">
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
                            <td><a class="btn btn-primary-soft" href="registrar_documento.php?CodIns=<?php echo $bautizo['CodIns']; ?>"><i class="bi bi-file-earmark-text"></i></a></td>
                            <td><a class="btn btn-primary-soft" href="VistaRegistradoBautizo.php?CodIns=<?php echo $bautizo['CodIns']; ?>"><i class="bi bi-eye"></i></a></td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  <?php elseif (isset($_POST['buscar_Solicitud_bautizo'])): ?>
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
                            <td><a class="btn btn-primary-soft" href="registrar_documento.php?CodIns=<?php echo $confirmacion['CodIns']; ?>"><i class="bi bi-file-earmark-text"></i></a></td>
                            <td><a class="btn btn-primary-soft" href="VistaRegistradoConfirmacion.php?CodIns=<?php echo $confirmacion['CodIns']; ?>"><i class="bi bi-eye"></i></a></td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  <?php elseif (isset($_POST['buscar_Solicitud_confirmacion'])): ?>
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
                            <td><a class="btn btn-primary-soft" href="registrar_documento.php?CodIns=<?php echo $matrimonio['CodIns']; ?>"><i class="bi bi-file-earmark-text"></i></a></td>
                            <td><a class="btn btn-primary-soft" href="VistaRegistradoMatrimonio.php?CodIns=<?php echo $matrimonio['CodIns']; ?>"><i class="bi bi-eye"></i></a></td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  <?php elseif (isset($_POST['buscar_Solicitud_matrimonio'])): ?>
                    <div class="my-sm-5 py-sm-5 text-center">
                      <i class="display-1 text-body-secondary bi bi-calendar2-event"></i>
                      <h4 class="mt-2 mb-3 text-body">Certificado no encontrado</h4>
                    </div>
                  <?php endif; ?>
                </div>

                <div class="tab-pane" id="tab-2">

                  <form method="POST" class="row g-3">
                    <input type="hidden" name="CodPer" value="<?php echo $usuario['CodPer']; ?>" required>
                    <div class="col-sm-6 col-lg-4">
                      <label class="form-label">Celebrante</label>
                      <input type="text" class="form-control" name="CiPersonaCelebranteB" placeholder="CI o N° Partida de Nacimiento" required minlength="3" maxlength="15">
                      <a class="celebranteB btn btn-sm btn-dashed rounded mt-0"><i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalBautizo"></i>registrar nuevo</a>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                      <label class="form-label">Identificación del Padre</label>
                      <input type="text" class="form-control" name="CiPapa" placeholder="CI Papá (opcional)" required minlength="4" maxlength="12">
                      <a class="papacelebrante btn btn-sm btn-dashed rounded mt-0"><i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo</a>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                      <label class="form-label">Identificación de la Madre</label>
                      <input type="text" class="form-control" name="CiMama" placeholder="CI Mamá (opcional)" required minlength="4" maxlength="12">
                      <a class="mamacelebrante btn btn-sm btn-dashed rounded mt-0"><i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo</a>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                      <label class="form-label">Identificación del Padrino</label>
                      <input type="text" class="form-control" name="CiPadrino" placeholder="CI Padrino (opcional)" minlength="4" maxlength="12">
                      <a class="padrinocelebrante btn btn-sm btn-dashed rounded mt-0"><i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo</a>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                      <label class="form-label">Identificación de la Madrina</label>
                      <input type="text" class="form-control" name="CiMadrina" placeholder="CI Madrina (opcional)" minlength="4" maxlength="12">
                      <a class="madrinacelebrante btn btn-sm btn-dashed rounded mt-0"><i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo</a>
                    </div>
                    <button type="submit" class="btn btn-success-soft me-2" name="registrar_bautizo"> Registrar Certificado de Bautizo</button>
                  </form>
                </div>

                <div class="tab-pane" id="tab-3">
                  <form method="POST" class="row g-3">
                    <input type="hidden" name="CodPer" value="<?php echo $usuario['CodPer']; ?>" required>

                    <div class="col-sm-6 col-lg-4">
                      <label class="form-label">Confirmante</label>
                      <input type="text" class="form-control" name="CiPersonaCelebranteC" placeholder="CI Confirmante" required minlength="4" maxlength="12">
                      <a class="celebranteC btn btn-sm btn-dashed rounded mt-0"><i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalConfirmante"></i>registrar nuevo</a>
                    </div>

                    <div class="col-sm-6 col-lg-4">
                      <label class="form-label">Identificación del Padrino</label>
                      <input type="text" class="form-control" name="CiPadrino" placeholder="CI Padrino (opcional)" minlength="4" maxlength="12">
                      <a class="padrinocelebrante btn btn-sm btn-dashed rounded mt-0"><i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo</a>
                    </div>

                    <div class="col-sm-6 col-lg-4">
                      <label class="form-label">Identificación de la Madrina</label>
                      <input type="text" class="form-control" name="CiMadrina" placeholder="CI Madrina (opcional)" minlength="4" maxlength="12">
                      <a class="madrinacelebrante btn btn-sm btn-dashed rounded mt-0"><i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo</a>
                    </div>

                    <button type="submit" class="btn btn-success-soft me-2" name="registrar_confirmacion">Registrar Certificado de Confirmación</button>
                  </form>

                </div>

                <div class="tab-pane" id="tab-4">

                  <form method="POST" class="row g-3">
                    <br>
                    <input type="hidden" name="CodPer" placeholder="Código Personal" value="<?php echo $usuario['CodPer']; ?>" required>

                    <div class="col-sm-6 col-lg-3">
                      <label class="form-label">Ci del Novio</label>
                      <input type="text" class="form-control col-lg-4" name="CiPersonaNovio" placeholder="CI Novio" required minlength="4" maxlength="12">
                      <a class="novio btn btn-sm btn-dashed rounded mt-0"><i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo</a>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                      <label class="form-label">Ci de la Novia</label>
                      <input type="text" class="form-control col-lg-4" name="CiPersonaNovia" placeholder="CI Novia" required minlength="4" maxlength="12">
                      <a class="novia btn btn-sm btn-dashed rounded mt-0"><i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo</a>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                      <label class="form-label">Ci del Papá del Novio</label>
                      <input type="text" class="form-control col-lg-4" name="CiPaNovio" placeholder="CI Papá Novio" minlength="4" maxlength="12">
                      <a class="papanovio btn btn-sm btn-dashed rounded mt-0"><i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo</a>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                      <label class="form-label">Ci de la Mamá del Novio</label>
                      <input type="text" class="form-control col-lg-4" name="CiMaNovio" placeholder="CI Mamá Novio" maxlength="12">
                      <a class="mamanovio btn btn-sm btn-dashed rounded mt-0"><i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo</a>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                      <label class="form-label">Ci del Papá de la Novia</label>
                      <input type="text" class="form-control col-lg-4" name="CiPaNovia" placeholder="CI Papá Novia" maxlength="12">
                      <a class="papanovia btn btn-sm btn-dashed rounded mt-0"><i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo</a>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                      <label class="form-label">Ci de la Mamá de la Novia</label>
                      <input type="text" class="form-control col-lg-4" name="CiMaNovia" placeholder="CI Mamá Novia" maxlength="12">
                      <a class="mamanovia btn btn-sm btn-dashed rounded mt-0"><i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo</a>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                      <label class="form-label">Ci del padrino</label>
                      <input type="text" class="form-control" name="CiPadrino" placeholder="CI Padrino" maxlength="12">
                      <a class="padrinocelebrante btn btn-sm btn-dashed rounded mt-0"><i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo</a>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                      <label class="form-label">Ci de la Madrina</label>
                      <input type="text" class="form-control" name="CiMadrina" placeholder="CI Madrina" maxlength="12">
                      <a class="madrinacelebrante btn btn-sm btn-dashed rounded mt-0"><i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo</a>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                      <label class="form-label">Testigo del Novio</label>
                      <input type="text" class="form-control" name="CiTestigoNovio" placeholder="CI Testigo" required minlength="4" maxlength="12">
                      <a class="testigonovio btn btn-sm btn-dashed rounded mt-0"><i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo</a>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                      <label class="form-label">Testigo de la Novia</label>
                      <input type="text" class="form-control" name="CiTestigoNovia" placeholder="CI Testigo" required minlength="4" maxlength="12">
                      <a class="testigonovia btn btn-sm btn-dashed rounded mt-0"><i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo</a>
                    </div>

                    <button type="submit" class="btn btn-success-soft me-2" name="registrar_matrimonio">Registrar Matrimonio</button>
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
              <input type="text" class="form-control" name="Direccion" placeholder="Dirección">
            </div>
            <div class="form-group col-lg-3">
              <label for="Contacto">Contacto:</label>
              <input type="text" class="form-control" name="Contacto" placeholder="Número de contacto">
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
              <input type="text" class="form-control" name="Direccion" placeholder="Dirección">
            </div>
            <div class="form-group col-lg-3">
              <label for="Contacto">Contacto:</label>
              <input type="text" class="form-control" name="Contacto" placeholder="Número de contacto">
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
              <input type="text" class="form-control" name="Direccion" placeholder="Dirección">
            </div>
            <div class="form-group col-lg-3">
              <label for="Contacto">Contacto:</label>
              <input type="text" class="form-control" name="Contacto" placeholder="Número de contacto">
            </div><br>
            <button type="submit" name="crear" class="btn btn-success-soft">Registrar</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <script>
    $(document).ready(function() {
      $('input[name="CiPersonaCelebranteB"]').on('input', function() {
        var ciPersona = $(this).val();
        if (ciPersona.length >= 3 || ciPersona.length <= 0) {
          $.ajax({
            url: '',
            type: 'GET',
            data: {
              ciPersona: ciPersona
            },
            success: function(response) {
              var persona = JSON.parse(response);
              if (persona.error) {
                $('.celebranteB').html(
                  `<i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalBautizo"></i>registrar nuevo`
                );
              } else {
                var EstadoP = `${persona.Estado_per}`;
                var formP = `${persona.Nombre} ${persona.ApPaterno} ${persona.ApMaterno || ''}`;

                if (EstadoP === "Soltero" || EstadoP === "Soltera") {
                  $('.celebranteB').html(formP);
                } else if (EstadoP === "Casado") {
                  $('.celebranteB').html('La persona no se registrará.');
                } else if (EstadoP === "Casada") {
                  $('.celebranteB').html('La persona no se registrará.');
                } else {
                  $('.celebranteB').html(
                    `<i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalBautizo"></i>verificar o registrar`
                  );
                }
              }
            }
          });

        }
      });

      $('input[name="CiPersonaCelebranteC"]').on('input', function() {
        var ciPersona = $(this).val();
        if (ciPersona.length >= 6 || ciPersona.length <= 0) {
          $.ajax({
            url: '',
            type: 'GET',
            data: {
              ciPersona: ciPersona
            },
            success: function(response) {
              var persona = JSON.parse(response);
              if (persona.error) {
                $('.celebranteC').html(
                  `<i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalConfirmante"></i>registrar nuevo`
                );
              } else {
                var EstadoPC = `${persona.Estado_per}`;
                var formPC = `${persona.Nombre} ${persona.ApPaterno} ${persona.ApMaterno || ''}`;

                if (EstadoPC === "Soltero" || EstadoPC === "Soltera") {
                  $('.celebranteC').html(formPC);
                } else if (EstadoPC === "Casado") {
                  $('.celebranteC').html('La persona no se registrará.');
                } else if (EstadoPC === "Casada") {
                  $('.celebranteC').html('La persona no se registrará.');
                } else {
                  $('.celebranteC').html(
                    `<i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalConfirmante"></i>verificar o registrar`
                  );
                }
              }
            }
          });

        }
      });
      $('input[name="CiPapa"]').on('input', function() {
        var ciPersona = $(this).val();
        if (ciPersona.length >= 6 || ciPersona.length <= 0) {
          $.ajax({
            url: '',
            type: 'GET',
            data: {
              ciPersona: ciPersona
            },
            success: function(response) {
              var persona = JSON.parse(response);
              if (persona.error) {
                $('.papacelebrante').html(
                  `<i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo`
                );
              } else {
                var form = `${persona.Nombre} ${persona.ApPaterno} ${persona.ApMaterno || ''}`;
                $('.papacelebrante').html(form);
              }
            }
          });
        }
      });
      $('input[name="CiMama"]').on('input', function() {
        var ciPersona = $(this).val();
        if (ciPersona.length >= 6 || ciPersona.length <= 0) {
          $.ajax({
            url: '',
            type: 'GET',
            data: {
              ciPersona: ciPersona
            },
            success: function(response) {
              var persona = JSON.parse(response);
              if (persona.error) {
                $('.mamacelebrante').html(
                  `<i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo`
                );
              } else {
                var form = `${persona.Nombre} ${persona.ApPaterno} ${persona.ApMaterno || ''}`;
                $('.mamacelebrante').html(form);
              }
            }
          });
        }
      });
      $('input[name="CiPadrino"]').on('input', function() {
        var ciPersona = $(this).val();
        if (ciPersona.length >= 6 || ciPersona.length <= 0) {
          $.ajax({
            url: '',
            type: 'GET',
            data: {
              ciPersona: ciPersona
            },
            success: function(response) {
              var persona = JSON.parse(response);
              if (persona.error) {
                $('.padrinocelebrante').html(
                  `<i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo`
                );
              } else {
                var form = `${persona.Nombre} ${persona.ApPaterno} ${persona.ApMaterno || ''}`;
                $('.padrinocelebrante').html(form);
              }
            }
          });
        }
      });
      $('input[name="CiMadrina"]').on('input', function() {
        var ciPersona = $(this).val();
        if (ciPersona.length >= 6 || ciPersona.length <= 0) {
          $.ajax({
            url: '',
            type: 'GET',
            data: {
              ciPersona: ciPersona
            },
            success: function(response) {
              var persona = JSON.parse(response);
              if (persona.error) {
                $('.madrinacelebrante').html(
                  `<i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo`
                );
              } else {
                var form = `${persona.Nombre} ${persona.ApPaterno} ${persona.ApMaterno || ''}`;
                $('.madrinacelebrante').html(form);
              }
            }
          });
        }
      });
      $('input[name="CiPaNovio"]').on('input', function() {
        var ciPersona = $(this).val();
        if (ciPersona.length >= 6 || ciPersona.length <= 0) {
          $.ajax({
            url: '',
            type: 'GET',
            data: {
              ciPersona: ciPersona
            },
            success: function(response) {
              var persona = JSON.parse(response);
              if (persona.error) {
                $('.papanovio').html(
                  `<i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo`
                );
              } else {
                var form = `${persona.Nombre} ${persona.ApPaterno} ${persona.ApMaterno || ''}`;
                $('.papanovio').html(form);
              }
            }
          });
        }
      });
      $('input[name="CiMaNovio"]').on('input', function() {
        var ciPersona = $(this).val();
        if (ciPersona.length >= 6 || ciPersona.length <= 0) {
          $.ajax({
            url: '',
            type: 'GET',
            data: {
              ciPersona: ciPersona
            },
            success: function(response) {
              var persona = JSON.parse(response);
              if (persona.error) {
                $('.mamanovio').html(
                  `<i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo`
                );
              } else {
                var form = `${persona.Nombre} ${persona.ApPaterno} ${persona.ApMaterno || ''}`;
                $('.mamanovio').html(form);
              }
            }
          });
        }
      });
      $('input[name="CiPaNovia"]').on('input', function() {
        var ciPersona = $(this).val();
        if (ciPersona.length >= 6 || ciPersona.length <= 0) {
          $.ajax({
            url: '',
            type: 'GET',
            data: {
              ciPersona: ciPersona
            },
            success: function(response) {
              var persona = JSON.parse(response);
              if (persona.error) {
                $('.papanovia').html(
                  `<i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo`
                );
              } else {
                var form = `${persona.Nombre} ${persona.ApPaterno} ${persona.ApMaterno || ''}`;
                $('.papanovia').html(form);
              }
            }
          });
        }
      });
      $('input[name="CiMaNovia"]').on('input', function() {
        var ciPersona = $(this).val();
        if (ciPersona.length >= 6 || ciPersona.length <= 0) {
          $.ajax({
            url: '',
            type: 'GET',
            data: {
              ciPersona: ciPersona
            },
            success: function(response) {
              var persona = JSON.parse(response);
              if (persona.error) {
                $('.mamanovia').html(
                  `<i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo`
                );
              } else {
                var form = `${persona.Nombre} ${persona.ApPaterno} ${persona.ApMaterno || ''}`;
                $('.mamanovia').html(form);
              }
            }
          });
        }
      });
      $('input[name="CiTestigoNovio"]').on('input', function() {
        var ciPersona = $(this).val();
        if (ciPersona.length >= 6 || ciPersona.length <= 0) {
          $.ajax({
            url: '',
            type: 'GET',
            data: {
              ciPersona: ciPersona
            },
            success: function(response) {
              var persona = JSON.parse(response);
              if (persona.error) {
                $('.testigonovio').html(
                  `<i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo`
                );
              } else {
                var form = `${persona.Nombre} ${persona.ApPaterno} ${persona.ApMaterno || ''}`;
                $('.testigonovio').html(form);
              }
            }
          });
        }
      });
      $('input[name="CiTestigoNovia"]').on('input', function() {
        var ciPersona = $(this).val();
        if (ciPersona.length >= 6 || ciPersona.length <= 0) {
          $.ajax({
            url: '',
            type: 'GET',
            data: {
              ciPersona: ciPersona
            },
            success: function(response) {
              var persona = JSON.parse(response);
              if (persona.error) {
                $('.testigonovia').html(
                  `<i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo`
                );
              } else {
                var form = `${persona.Nombre} ${persona.ApPaterno} ${persona.ApMaterno || ''}`;
                $('.testigonovia').html(form);
              }
            }
          });
        }
      });



      $('input[name="CiPersonaNovio"]').on('input', function() {
        var ciPersona = $(this).val();
        if (ciPersona.length >= 6 || ciPersona.length <= 0) {
          $.ajax({
            url: '',
            type: 'GET',
            data: {
              ciPersona: ciPersona
            },
            success: function(response) {
              var persona = JSON.parse(response);
              if (persona.error) {
                $('.novio').html(
                  `<i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo`
                );
              } else {
                var GeneroNovio = `${persona.Sexo}`;
                var EstadoPNovio = `${persona.Estado_per}`;
                var formNovio = `${persona.Nombre} ${persona.ApPaterno} ${persona.ApMaterno || ''}`;

                if (GeneroNovio === "Varon" && EstadoPNovio === "Soltero") {
                  $('.novio').html(formNovio);
                } else if (GeneroNovio === "Mujer") {
                  $('.novio').html('La persona es mujer.');
                } else if (EstadoPNovio === "Casado") {
                  $('.novio').html('La persona ya está casado.');
                } else {
                  $('.novio').html(
                    `<i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>verificar o registrar`
                  );
                }
              }
            }
          });
        }
      });
      $('input[name="CiPersonaNovia"]').on('input', function() {
        var ciPersona = $(this).val();
        if (ciPersona.length >= 6 || ciPersona.length <= 0) {
          $.ajax({
            url: '',
            type: 'GET',
            data: {
              ciPersona: ciPersona
            },
            success: function(response) {
              var persona = JSON.parse(response);
              if (persona.error) {
                $('.novia').html(
                  `<i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo`
                );
              } else {
                var GeneroNovia = `${persona.Sexo}`;
                var EstadoPNovia = `${persona.Estado_per}`;
                var formNovia = `${persona.Nombre} ${persona.ApPaterno} ${persona.ApMaterno || ''}`;

                if (GeneroNovia === "Mujer" && EstadoPNovia === "Soltera") {
                  $('.novia').html(formNovia);
                } else if (GeneroNovia === "Varon") {
                  $('.novia').html('La persona es varon.');
                } else if (EstadoPNovia === "Casada") {
                  $('.novia').html('La persona ya está casada.');
                } else {
                  $('.novia').html(
                    `<i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>verificar o registrar`
                  );
                }
              }
            }
          });
        }
      });
    });

    document.getElementById('toggleFormBtn').addEventListener('click', function() {
      const formReserva = document.getElementById('formReservaBautizo');

      if (formReserva.style.display === 'none' || formReserva.style.display === '') {
        formReserva.style.display = 'block';
      } else {
        formReserva.style.display = 'none';
      }
    });

    document.getElementById('toggleFormBtn2').addEventListener('click', function() {
      const formReserva = document.getElementById('formReservaMatrimonio');

      if (formReserva.style.display === 'none' || formReserva.style.display === '') {
        formReserva.style.display = 'block';
      } else {
        formReserva.style.display = 'none';
      }
    });
    document.getElementById('toggleFormBtn3').addEventListener('click', function() {
      const formReserva = document.getElementById('formReservaMisa');

      if (formReserva.style.display === 'none' || formReserva.style.display === '') {
        formReserva.style.display = 'block';
      } else {
        formReserva.style.display = 'none';
      }
    });

    const inputTime = document.getElementById('HoraReal');

    inputTime.addEventListener('input', function() {
      const timeValue = inputTime.value;
      let [hours, minutes] = timeValue.split(':').map(Number);

      if (minutes >= 0 && minutes < 30) {
        minutes = 0;
      } else {
        minutes = 30;
      }

      const formattedTime = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
      inputTime.value = formattedTime;
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
  <script src="../assets/js/functions.js"></script>
  <script src="../assets/js/tema.js"></script>
</body>

</html>