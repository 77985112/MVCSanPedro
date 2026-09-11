<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: ../logout.php");
  exit();
}
$usuario = $_SESSION['usuario'];

$diassemana = array("Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado");
$meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");

require_once '../Controlador/controladorPersonal.php';
$cont = new ControladorPersonal();
$personalExt = $cont->PersonalDemas($usuario['CiPersonal']);

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
        <div class="col-lg-8 vstack gap-4">
          <div class="card">
            <div class="h-200px rounded-top" style="background-image:url(<?php echo ($usuario['FondoPer'] == 'Ninguno' || empty($usuario['FondoPer'])) ? '../assets/images/fondo/Ninguno.jpg' : "../assets/images/fondo/{$usuario['FondoPer']}"; ?>); background-position: center; background-size: cover; background-repeat: no-repeat;"></div>

            <div class="card-body py-0">
              <div class="d-sm-flex align-items-start text-center text-sm-start">
                <div>
                  <div class="avatar avatar-xxl mt-n5 mb-3">
                    <img class="avatar-img rounded-circle border border-white border-3" src="<?php echo ($usuario['PerfilPer'] == 'Ninguno' || empty($usuario['PerfilPer'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$usuario['PerfilPer']}"; ?>" alt="">
                  </div>
                </div>
                <div class="ms-sm-4 mt-sm-3">
                  <h1 class="mb-0 h5"><?php echo $usuario['Nombre'] . " " . $usuario['Paterno'] . " " . $usuario['Materno']; ?> <?php if (in_array($usuario['DescripCar'], ['Párroco', 'Secretario (a) Parroquial', 'Sacerdote'])) : ?><i class="bi bi-patch-check-fill text-success small"></i><?php endif; ?></h1>
                  <p>Personal "<strong>San Pedro de Sacaba</strong>"</p>
                </div>

              </div>
              <ul class="list-inline mb-0 text-center text-sm-start mt-3 mt-sm-0">
                <li class="list-inline-item"><i class="bi bi-briefcase me-1"></i><?php echo $usuario['DescripCar']; ?></li>
                <li class="list-inline-item"><i class="bi bi-geo-alt me-1"></i>Sacaba - Chapare</li>
              </ul>
            </div><br>
          </div>

          <div class="card card-body">

            <?php if ($usuario['Estado'] == "Activo") : ?>

            <?php elseif ($usuario['Estado'] == "Inactivo") : ?>
              <div class="col-lg-8 mx-auto align-items-center text-center justify-content-sm-between">
                <h1 class="display-1 mt-4">Oops!</h1>
                <h2 class="mb-2 h1">Tu cuenta fue suspendida!</h2>
                <p>"Ponte en contacto con el administrador para obtener más información"</p>
                <a class="btn btn-danger-soft btn-sm" href="../logout.php">Cerrar Session</a>
              </div>
            <?php endif; ?>

          </div>
        </div>

        <div class="col-lg-4">
          <div class="row g-4">
            <div class="col-md-6 col-lg-12">
              <div class="card">
                <div class="card-header border-0 pb-0">
                  <h5 class="card-title">Información de Cuenta</h5>
                </div>
                <div class="card-body position-relative pt-0">
                  <p><?php echo $usuario['FrasePer']; ?></p>
                  <ul class="list-unstyled mt-3 mb-0">
                    <?php
                    $fechaAdm = $usuario['FechaIng'];
                    $timestampFecha = strtotime($fechaAdm);
                    $diaSemana = $diassemana[date('w', $timestampFecha)];
                    $dia = date('d', $timestampFecha);
                    $mes = $meses[date('n', $timestampFecha) - 1];
                    $anio = date('Y', $timestampFecha);
                    $fechaFormateada = "$diaSemana, $dia de $mes del $anio";
                    ?>
                    <li class="mb-2"> <i class="bi bi-calendar2-plus me-1"></i> Ingresó: <?php echo $fechaFormateada; ?></li>

                    <li class="mb-2" <?php if ($usuario['Estado'] == "Activo") : ?>style="color:limegreen;" <?php elseif ($usuario['Estado'] == "Inactivo") : ?>style="color:Red;" <?php endif; ?>> <i class="bi bi-broadcast"></i> Estado: <strong> <?php echo $usuario['Estado']; ?> </strong> </li>

                    <li> <i class="bi bi-telephone fa-fw pe-1"></i> Contacto: <strong> <?php echo $usuario['ContactoPer']; ?> </strong> </li>
                  </ul>
                </div>
              </div>
            </div>

            <div class="col-md-6 col-lg-12">
              <div class="card">
                <div class="card-header d-sm-flex justify-content-between align-items-center border-0">
                  <h5 class="card-title">Personal </h5>
                </div>
                <div class="card-body position-relative pt-0">
                  <div class="row g-3">

                    <?php if (!empty($personalExt)): ?>
                      <?php foreach ($personalExt as $personalExtD): ?>
                        <div class="col-6">
                          <div class="card shadow-none text-center h-100">
                            <div class="card-body p-2 pb-0">
                              <input type="hidden" value="<?php echo htmlspecialchars($personalExtD['CiPersonal']); ?>">
                              <div class="avatar avatar-xl">
                                <img class="avatar-img rounded-circle" src="<?php echo ($personalExtD['PerfilPer'] == 'Ninguno' || empty($personalExtD['PerfilPer'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$personalExtD['PerfilPer']}"; ?>" alt="">
                              </div>
                              <h6 class="card-title mb-1 mt-3"> <?php echo $personalExtD['NombreCompleto']; ?></h6>
                              <p class="mb-0 small lh-sm"><?php echo $personalExtD['DescripCar']; ?></p>
                            </div>
                            <div class="card-footer p-2 border-0">
                              <button class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="Ver Perfil"> <i class="bi bi-chat-left-text"></i> </button>
                              <button class="btn btn-sm btn-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="Ver demás"> <i class="bi bi-person-x"></i> </button>
                            </div>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <p>No hay Personal...</p>
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
  <script src="../assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/functions.js"></script>
  <script src="../assets/js/tema.js"></script>
</body>

</html>