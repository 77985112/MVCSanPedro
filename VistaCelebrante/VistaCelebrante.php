<?php
session_start();
require_once('../Conexion/Conexion.php');

if (!isset($_SESSION['CiPersona'])) {
  header("Location: ../logout.php");
  exit();
}
$Celebrante = $_SESSION['CiPersona'];

require_once '../controlador/ControladorActividad.php';

$controller = new ControladorActividad();
$actividadesParroquia = $controller->obtenerActividades('Parroquia');
$actividadesPrimeraComunion = $controller->obtenerActividades('Primera Comunión');
$actividadesConfirmacion = $controller->obtenerActividades('Confirmación');
$actividadesTodas = $controller->obtenerActividades();

if ($Celebrante['Color_Grupo'] === "Ninguno") {
  $colorGrupo = "#000000";
} else {
  $colorGrupo = $Celebrante['Color_Grupo'];
}



$conexion = new Conexion();
$conn = $conexion->getConnection();

function getActiveNotifications($conn)
{
    $currentDateTime = date('Y-m-d H:i:s');

    $sql = "SELECT n.idNotificacion, n.TituloNot, n.DetalleNot, n.FechaNotFin, CONCAT(p.Nombre, ' ', p.ApPaterno) AS datoscat 
            FROM notificaciones n 
            INNER JOIN catequista c ON n.CiNotificador = c.CiCat 
            INNER JOIN persona p ON c.CiCat = p.CiPersona 
            WHERE n.EstadoNot = 'Activo' AND n.FechaNotFin > ?
            ORDER BY n.FechaNotFin ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $currentDateTime);
    $stmt->execute();
    $result = $stmt->get_result();

    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }

    return $notifications;
}

$activeNotifications = getActiveNotifications($conn);
?>

<!DOCTYPE html>
<html lang="es">

<head>

  <head>
    <title>San Pedro de Sacaba</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="shortcut icon" href="../assets/images/Icono.png">
    <link rel="stylesheet" type="text/css" href="../assets/vendor/font-awesome/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="../assets/vendor/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" type="text/css" href="../assets/css/style.css">
    <script src="../assets/js/jquery-3.6.4.min.js"></script>

    <style>

      .loader {
        display: block;
        --height-of-loader: 10px;
        width: 100%;
        height: var(--height-of-loader);
        border-radius: 10px;
        background-color: rgba(0, 0, 0, 0.2);
        position: relative;
      }

      .loader::before {
        content: "";
        position: absolute;
        background: <?php echo $colorGrupo; ?>;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border-radius: 30px;
        animation: moving 5s ease-in-out infinite;
      }
    </style>

  </head>

<body class="sidebar-start-enabled">
  <header class="navbar-light bg-mode">
    <nav class="navbar navbar-expand-lg">
      <div class="container">
        <a class="navbar-brand" href="VistaCelebrante.php">
          <img class="light-mode-item navbar-brand-item" src="../assets/images/logo.png" alt="logo">
          <img class="dark-mode-item navbar-brand-item" src="../assets/images/logo-light.png" alt="logo">
        </a>

        <ul class="nav flex-nowrap align-items-center ms-sm-3 list-unstyled">
        <li class="nav-item dropdown ms-2">
                        <a class="nav-link bg-light icon-md btn btn-light p-0" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                            <span class="badge-notif animation-blink"></span>
                            <i class="bi bi-bell-fill fs-6"> </i>
                        </a>
                        <div class="dropdown-menu dropdown-animation dropdown-menu-end dropdown-menu-size-md p-0 shadow-lg border-0" aria-labelledby="notifDropdown">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="m-0">Notificaciones <span class="badge bg-danger ms-2"><?php echo count($activeNotifications); ?> activas</span></h6>
                                </div>
                                <div class="card-body p-0">
                                    <ul class="list-group list-group-flush">
                                        <?php foreach ($activeNotifications as $notification): ?>
                                            <li class="list-group-item">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h6 class="mb-0"><?php echo htmlspecialchars($notification['TituloNot']); ?></h6>
                                                    <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($notification['FechaNotFin'])); ?></small>
                                                </div>
                                                <p class="mb-0 small"><?php echo htmlspecialchars($notification['DetalleNot']); ?></p>
                                                <small class="text-muted">Notificado por: <?php echo htmlspecialchars($notification['datoscat']); ?></small>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <?php if (empty($activeNotifications)): ?>
                                    <div class="card-body">
                                        <p class="text-center mb-0">No hay notificaciones activas.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </li>
          <li class="nav-item ms-2 dropdown">
            <a class="nav-link btn icon-md p-0" href="#" id="profileDropdown" role="button" data-bs-auto-close="outside" data-bs-display="static" data-bs-toggle="dropdown" aria-expanded="false">
              <img class="avatar-img border border-white border-3 rounded" src="<?php echo ($Celebrante['PerfilCel'] == 'Ninguno' || empty($Celebrante['PerfilCel'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$Celebrante['PerfilCel']}"; ?>" alt="">
            </a>
            <ul class="dropdown-menu dropdown-animation dropdown-menu-end pt-3 small me-md-n3" aria-labelledby="profileDropdown">
              <li class="px-3">
                <div class="d-flex align-items-center position-relative">
                  <div class="avatar me-3">
                    <img class="avatar-img rounded-circle" src="<?php echo ($Celebrante['PerfilCel'] == 'Ninguno' || empty($Celebrante['PerfilCel'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$Celebrante['PerfilCel']}"; ?>" alt="avatar">
                  </div>
                  <div>
                    <a class="h6 stretched-link" href="PerfilCelebrante.php"><?php echo $Celebrante['Nombre'] . " " . $Celebrante['ApPaterno'] . " " . $Celebrante['ApMaterno']; ?></a>
                    <p class="small m-0"><?php echo $Celebrante['Sacramento']; ?></p>
                  </div>
                </div>
                <a class="dropdown-item btn btn-primary-soft btn-sm my-2 text-center" href="PerfilCelebrante.php">Ver mi Perfil</a>
              </li>
              <li>
                <a class="dropdown-item" href="Configuracion.php">
                  <i class="bi bi-gear fa-fw me-2"></i>Configuración y Privacidad
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="VistaReuniones.php">
                  <i class="fa-fw bi bi-calendar2-check me-2"></i>Fecha de Reuniones
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="Reglamento.php">
                  <i class="fa-fw bi bi-list-check me-2"></i>Reglamento
                </a>
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
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sun fa-fw mode-switch" viewBox="0 0 16 16">
                      <path d="M8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6zm0 1a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z" />
                      <use href="#"></use>
                    </svg>
                  </button>
                  <button type="button" class="btn btn-modeswitch nav-link text-primary-hover mb-0" data-bs-theme-value="dark" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Oscuro">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-moon-stars fa-fw mode-switch" viewBox="0 0 16 16">
                      <path d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278zM4.858 1.311A7.269 7.269 0 0 0 1.025 7.71c0 4.02 3.279 7.276 7.319 7.276a7.316 7.316 0 0 0 5.205-2.162c-.337.042-.68.063-1.029.063-4.61 0-8.343-3.714-8.343-8.29 0-1.167.242-2.278.681-3.286z" />
                      <path d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.734 1.734 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.734 1.734 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.734 1.734 0 0 0 1.097-1.097l.387-1.162zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.156 1.156 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.156 1.156 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732L13.863.1z" />
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
    <section class="pt-5 pb-0 position-relative" style="background-image: url(../assets/images/bg/CelebranteBg.jpg); background-repeat: no-repeat; background-size: cover; background-position: center;">
      <div class="bg-overlay bg-dark opacity-2"></div>
      <div class="container">
        <div class="pt-5">
          <div class="row position-relative">
            <div class="card border-0 col-lg-6 py-2">
              <div class="row g-3">
                <div class="col-4">
                  <img class="rounded border border-white border-3" style="width: 180px;" src="<?php echo ($Celebrante['Imagen_Grupo'] == 'Anonimo' || empty($Celebrante['Imagen_Grupo'])) ? '../assets/images/Santo/SanPedro.png' : "../assets/images/Santo/{$Celebrante['Imagen_Grupo']}"; ?>" alt="">
                </div>
                <div class="col-8">
                  <h2><a href="#" class="btn-link stretched-link text-reset fw-bold"><?php echo ($Celebrante['Santo_Grupo'] == 'Anonimo' || empty($Celebrante['Santo_Grupo'])) ? 'San Pedro' : "{$Celebrante['Santo_Grupo']}"; ?></a></h2>
                  <div class="d-none d-sm-inline-block">
                    <p class="mb-2"><?php echo $Celebrante['Frase_Santo']; ?></p>
                  </div>
                  <h4>Grupo: <strong><?php echo $Celebrante['NombreGrupo']; ?></strong></h4>
                </div>
              </div>
              <div class="loader"></div>
            </div>

            <div class="mb-n5 mt-3 mt-lg-5">
              <div class="col-xl-9 col-lg-11 mx-auto">
                <div class="d-md-flex gap-3 mt-5">
                  <a href="VistaMiGrupoCel.php" class="shaque card card-body mb-3 mb-lg-0 p-3 text-center">
                    <img class="h-40px mb-3" src="../assets/images/icon/badge-outline-filled.svg" alt="">
                    <h6>Mi Grupo</h6>
                  </a>

                  <a href="VistaCelebrante.php" class="shaque card card-body mb-3 mb-lg-0 p-3 text-center">
                    <img class="h-40px mb-3" src="../assets/images/icon/home-outline-filled.svg" alt="">
                    <h6>Inicio</h6>
                  </a>

                  <a href="VistaEstadoCel.php" class="shaque card card-body mb-3 mb-lg-0 p-3 text-center">
                    <img class="h-40px mb-3" src="../assets/images/icon/imac-outline-filled.svg" alt="">
                    <h6>Mi Estado</h6>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <br>

    <section class="pt-5">
      <div class="container">
        <div class="row g-4">

          <div class="col-12 vstack gap-4">
            <div class="card">
              <div class="card-header d-sm-flex align-items-center text-center justify-content-sm-between border-0 pb-0">
                <h2 class="h4 card-title">Proximas Actividades</h2>
              </div>
              <div class="card-body">
                <ul class="nav nav-tabs nav-bottom-line justify-content-center justify-content-md-start">
                  <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-1"> Todos</a></li>
                  <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-2"> Parroquia </a></li>
                  <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-3"> Primera Comunión </a></li>
                  <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-4"> Confirmación </a></li>
                </ul>
                <div class="tab-content mb-0 pb-0">

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
                                <img class="img-fluid rounded-top fixed-height" src="../assets/images/events/<?php echo $actividad['ImagenActividad'] !== 'Ninguno' ? $actividad['ImagenActividad'] : 'Generico.png'; ?>" alt="" style="width: 100%; height:150px;">
                                <div class="badge bg-<?php echo $actividad['EstActividad'] == 'Activo' ? 'success' : 'danger'; ?> text-white mt-2 me-2 position-absolute top-0 end-0"><?php echo $actividad['EstActividad']; ?></div>
                              </div>
                              <div class="card-body position-relative pt-0">
                                <input type="hidden" value="<?php echo $actividad['idActividad']; ?>">
                                <h6 class="mt-3"><a href="DetalleActividad.php?Actividad=<?php echo $actividad['idActividad']; ?>"><?php echo $actividad['TituloActividad']; ?></a></h6>
                                <p class="mb-0 small"><i class="bi bi-calendar-check pe-1"></i><?php echo $actividad['FechaActividad']; ?> a <?php echo $actividad['HoraAct']; ?></p>
                                <p class="small"><i class="bi bi-geo-alt pe-1"></i><?php echo $actividad['LugarAct']; ?></p>
                              </div>
                            </div>
                          </div>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </div>
                  </div>

                  <div class="tab-pane fade" id="tab-2">
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
                                <img class="img-fluid rounded-top fixed-height" src="../assets/images/events/<?php echo $actividad['ImagenActividad'] !== 'Ninguno' ? $actividad['ImagenActividad'] : 'Generico.png'; ?>" alt="" style="width: 100%; height:150px;">
                                <div class="badge bg-<?php echo $actividad['EstActividad'] == 'Activo' ? 'success' : 'danger'; ?> text-white mt-2 me-2 position-absolute top-0 end-0"><?php echo $actividad['EstActividad']; ?></div>
                              </div>
                              <div class="card-body position-relative pt-0">
                                <input type="hidden" value="<?php echo $actividad['idActividad']; ?>">
                                <h6 class="mt-3"><a href="DetalleActividad.php?Actividad=<?php echo $actividad['idActividad']; ?>"><?php echo $actividad['TituloActividad']; ?></a></h6>
                                <p class="mb-0 small"><i class="bi bi-calendar-check pe-1"></i><?php echo $actividad['FechaActividad']; ?> a <?php echo $actividad['HoraAct']; ?></p>
                                <p class="small"><i class="bi bi-geo-alt pe-1"></i><?php echo $actividad['LugarAct']; ?></p>
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
                                <img class="img-fluid rounded-top fixed-height" src="../assets/images/events/<?php echo $actividad['ImagenActividad'] !== 'Ninguno' ? $actividad['ImagenActividad'] : 'Generico.png'; ?>" alt="" style="width: 100%; height:150px;">
                                <div class="badge bg-<?php echo $actividad['EstActividad'] == 'Activo' ? 'success' : 'danger'; ?> text-white mt-2 me-2 position-absolute top-0 end-0"><?php echo $actividad['EstActividad']; ?></div>
                              </div>
                              <div class="card-body position-relative pt-0">
                                <input type="hidden" value="<?php echo $actividad['idActividad']; ?>">
                                <h6 class="mt-3"><a href="DetalleActividad.php?Actividad=<?php echo $actividad['idActividad']; ?>"><?php echo $actividad['TituloActividad']; ?></a></h6>
                                <p class="mb-0 small"><i class="bi bi-calendar-check pe-1"></i><?php echo $actividad['FechaActividad']; ?> a <?php echo $actividad['HoraAct']; ?></p>
                                <p class="small"><i class="bi bi-geo-alt pe-1"></i><?php echo $actividad['LugarAct']; ?></p>
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
                                <img class="img-fluid rounded-top fixed-height" src="../assets/images/events/<?php echo $actividad['ImagenActividad'] !== 'Ninguno' ? $actividad['ImagenActividad'] : 'Generico.png'; ?>" alt="" style="width: 100%; height:150px;">
                                <div class="badge bg-<?php echo $actividad['EstActividad'] == 'Activo' ? 'success' : 'danger'; ?> text-white mt-2 me-2 position-absolute top-0 end-0"><?php echo $actividad['EstActividad']; ?></div>
                              </div>
                              <div class="card-body position-relative pt-0">
                                <input type="hidden" value="<?php echo $actividad['idActividad']; ?>">
                                <h6 class="mt-3"><a href="DetalleActividad.php?Actividad=<?php echo $actividad['idActividad']; ?>"><?php echo $actividad['TituloActividad']; ?></a></h6>
                                <p class="mb-0 small"><i class="bi bi-calendar-check pe-1"></i><?php echo $actividad['FechaActividad']; ?> a <?php echo $actividad['HoraAct']; ?></p>
                                <p class="small"><i class="bi bi-geo-alt pe-1"></i><?php echo $actividad['LugarAct']; ?></p>
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
    </section>
  </main>

  <script>
    $(document).ready(function() {
      function obtenerEstado() {
        $.ajax({
          url: '../VerificarCelebrante.php',
          method: 'GET',
          dataType: 'json',
          success: function(data) {
            if (data.EstadoCel) {
              $('#EstadoCel').text(data.EstadoCel);
              if (data.EstadoCel === "Inactivo") {
                window.location.replace("PerfilCelebrante.php");
              }
            } else {
              $('#EstadoCel').text('Error al obtener el estado');
              console.error(data.error);
            }
          },
          error: function(jqXHR, textStatus, errorThrown) {
            console.error("Error en la solicitud AJAX: " + textStatus, errorThrown);
          }
        });
      }

      setInterval(obtenerEstado, 5000);

      obtenerEstado();
    });
  </script>
  <script src="../assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/functions.js"></script>
  <script src="../assets/js/tema.js"></script>
</body>

</html>