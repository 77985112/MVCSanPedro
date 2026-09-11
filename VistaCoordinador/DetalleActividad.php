<?php
require_once '../controlador/ControladorActividad.php';
require_once '../Conexion/Conexion.php';

session_start();
if (!isset($_SESSION['CiPersona'])) {
    header("Location: ../logout.php");
    exit();
}
$Catequista = $_SESSION['CiPersona'];

if (!isset($_GET['idActividad'])) {
    header("Location: ActividadVista.php");
    exit();
}

$Actividad = $_GET['idActividad'];
$controlador = new ControladorActividad();

$conn = new Conexion();
$conexion = $conn->getConnection();

$sql = "SELECT * FROM actividades WHERE idActividad = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $Actividad);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "No se encontró la actividad.";
    exit();
}
$actividad = $result->fetch_assoc();

$diassemana = array("Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado");
$meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");

$fecha = new DateTime($actividad['FechaActividad']);
$fechaFormateada = $diassemana[$fecha->format('w')] . ", " . $fecha->format('d') . " de " . $meses[$fecha->format('n') - 1] . " de " . $fecha->format('Y');

$dia = $diassemana[$fecha->format('w')];
$mes = $fecha->format('d') . " de " . $meses[$fecha->format('n') - 1];


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['actualizar_Actividad'])) {
    $controlador->actualizarActividad($_POST);
    header("Location: ActividadVista.php");
    exit;
}


require_once '../Controlador/controladorCatCoor.php';
$contCats = new CatsController();
$catequistas = $contCats->index($Catequista['CiCat']);
$catsacramento = $contCats->CatequsitasSac($Catequista['Sacramento']);





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
    <title>San Pedro de Sacaba</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="shortcut icon" href="../assets/images/Icono.png">
    <link rel="stylesheet" type="text/css" href="../assets/vendor/font-awesome/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="../assets/vendor/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" type="text/css" href="../assets/css/style.css">
    <script src="../assets/js/jquery-3.6.4.min.js"></script>
</head>

<header class="navbar-light fixed-top header-static bg-mode">
    <nav class="navbar navbar-expand-lg">
        <button class="btn text-secondary py-0 me-3 sidebar-start-toggle"><i class="bi bi-justify-left fs-3 lh-0"></i></button>
        <div class="container">
            <a class="navbar-brand" href="VistaCoordinador.php">
                <img class="light-mode-item navbar-brand-item" src="../assets/images/logo.png" alt="logo">
                <img class="dark-mode-item navbar-brand-item" src="../assets/images/logo-light.png" alt="logo">
            </a>

            <ul class="nav flex-nowrap align-items-center ms-sm-3 list-unstyled">
                <li class="nav-item ms-2">
                    <a class="nav-link bg-light icon-md btn btn-light p-0" href="Configuracion.php" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Configurar">
                        <i class="bi bi-gear-fill fs-6"> </i>
                    </a>
                </li>

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
                        <img class="avatar-img rounded border border-white border-3" src="<?php echo ($Catequista['ImagenCat'] == 'Ninguno' || empty($Catequista['ImagenCat'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$Catequista['ImagenCat']}"; ?>" alt="">
                    </a>
                    <ul class="dropdown-menu dropdown-animation dropdown-menu-end pt-3 small me-md-n3" aria-labelledby="profileDropdown">
                        <li class="px-3">
                            <div class="d-flex align-items-center position-relative">
                                <div class="avatar me-3">
                                    <img class="avatar-img rounded-circle" src="<?php echo ($Catequista['ImagenCat'] == 'Ninguno' || empty($Catequista['ImagenCat'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$Catequista['ImagenCat']}"; ?>" alt="avatar">
                                </div>
                                <div>
                                    <a class="h6 stretched-link" href="#"><?php echo $Catequista['Nombre'] . " " . $Catequista['ApPaterno'] . " " . $Catequista['ApMaterno']; ?></a>
                                    <p class="small m-0"><?php echo $Catequista['RolCat']; ?> <i class="bi bi-patch-check-fill text-success small"></i></p>
                                </div>
                            </div>
                            <a class="dropdown-item btn btn-primary-soft btn-sm my-2 text-center" href="PerfilCatequista.php">Ver mi Perfil</a>
                        </li>
                        <hr>
                        <li>
                            <a class="dropdown-item" href="Configuracion.php"><i class="bi bi-gear fa-fw me-2"></i>Configuración y Privacidad</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="ReunionesCat.php"><i class="fa-fw bi bi-calendar2-check me-2"></i>Fecha de Reuniones</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="Documentos.php">
                                <i class="fa-fw bi bi-card-text me-2"></i>Documentación
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="Reglamento.php">
                                <i class="fa-fw bi bi-list-check me-2"></i>Reglamento
                            </a>
                        </li>
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
    <div class="container-fluid">
        <div class="row justify-content-between g-0">

            <div class="col-md-2 col-lg-3 col-xxl-4 mt-n4">
                <div class="nav-sidenav p-4 bg-mode h-100 custom-scrollbar">
                    <ul class="nav nav-link-secondary flex-column fw-bold gap-2">
                        <h6 class="nav-text badge bg-primary text-white mt-2 me-2 border border-white border-3 rounded"><i class="bi bi-caret-down-fill"></i>&nbsp;&nbsp; General</h6>
                        <li class="nav-item">
                            <a class="nav-link" href="VistaCoordinador.php"> <i class="bi bi-house nav-icon"></i> <span class="nav-text">Inicio </span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="VistaAsisCat.php"> <i class="bi bi-people nav-icon"></i> <span class="nav-text">Asistencia </span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="Notificaciones.php"> <i class="bi bi-bell-fill nav-icon"></i> <span class="nav-text">Notificación </span></a>
                        </li>
                        <?php if ($Catequista['RolCat'] == "Coordinador") { ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="Inscripciones.php"> <i class="bi bi-pencil-square nav-icon"></i> <span class="nav-text">Fecha de Sacramento </span></a>
                                </li>
                            <?php } ?>
                        <li class="nav-item">
                            <a class="nav-link" href="CatequistaAdm.php"> <i class="bi bi-file-person nav-icon"></i> <span class="nav-text">Catequista </span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="VistaCeleAdmin.php"> <i class="bi bi-person-circle nav-icon"></i> <span class="nav-text">Catequisando </span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="ActividadVista.php"> <i class="bi bi-calendar-event-fill nav-icon"></i> <span class="nav-text">Actividad</span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="VistaPersona.php"> <i class="<bi bi-person-vcard nav-icon"></i> <span class="nav-text">Persona </span></a>
                        </li>
                        <h6 class="nav-text badge bg-primary text-white mt-2 me-2 border border-white border-3 rounded"><i class="bi bi-caret-down-fill"></i>&nbsp;&nbsp; Personal</h6>
                        <li class="nav-item">
                            <a class="nav-link" href="Configuracion.php"> <i class="bi bi-gear-wide-connected nav-icon"></i> <span class="nav-text">Configuración </span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="VistaTodosCat.php"> <i class="bi bi-calendar-check nav-icon"></i> <span class="nav-text">Listado Catequistas</span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="VistaTodosCat.php"> <i class="bi bi-file-earmark-text nav-icon"></i> <span class="nav-text">Reporte </span></a>
                        </li>
                        <h6 class="nav-text badge bg-primary text-white mt-2 me-2 border border-white border-3 rounded"><i class="bi bi-caret-down-fill"></i>&nbsp;&nbsp; Publico</h6>
                        <li class="nav-item">
                            <a class="nav-link" href="VistaMensajeCoor.php"> <i class="bi bi-file-earmark-text nav-icon"></i> <span class="nav-text">Mensaje Público</span></a>
                        </li>
                    </ul>
                </div>
            </div>


            <section class="pt-0">
                <div class="container">
                    <div class="card">
                        <div class="col-10 mt-4">
                            <div class="d-sm-flex justify-content-between align-items-center">
                                <h5 class="mb-md-2 p-2">Detalle de Actividad </h5>
                            </div>
                        </div>
                        <div class="col-md-8 col-lg-12 vstack gap-4 align-items-center">
                            <div class="card card-body card-overlay-bottom border-0 col-lg-9" style="background-image:url(../assets/images/events/<?php echo $actividad['ImagenActividad'] !== 'Ninguno' ? $actividad['ImagenActividad'] : 'Generico.png'; ?>" class="img-fluid rounded activity-image" alt="Imagen de <?php echo $actividad['TituloActividad']; ?>); background-position: center; background-size: cover; background-repeat: no-repeat;" data-bs-toggle="modal"
                                data-bs-target="#imageModal">
                                <div class="row g-3 justify-content-between">
                                    <div class="col-lg-9">
                                        <div class="bg-mode text-center rounded overflow-hidden p-1 d-inline-block">
                                            <div class="bg-primary p-2 text-white rounded-top small lh-1"> <?php echo $dia; ?></div>
                                            <h5 class="mb-0 py-2 lh-1"><?php echo $mes; ?></h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-3 justify-content-between align-items-center mt-5 pt-5 position-relative z-index-9">
                                    <div class="text-lg-end">
                                        <a class="btn btn-light-soft" href="ActividadVista.php"> Volver </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card card-body col-lg-9">
                                <div class="row g-4">

                                    <div class="col-lg-9">
                                        <h1 class="h3 mb-1"><?php echo $actividad['TituloActividad']; ?></h1>
                                    </div>
                                    <div class="col-9">
                                        <p class="mb-0"><?php echo $actividad['DetalleActividad']; ?></p>
                                    </div>
                                    <hr class="mt-4">
                                    <div class="col-sm-6 col-lg-4">
                                        <h5>Hora de Actividad</h5>
                                        <p class="small mb-0"> <?php echo date("g:i A", strtotime($actividad['HoraAct'])); ?></p>
                                    </div>
                                    <div class="col-sm-6 col-lg-4">
                                        <h5>Fecha de Actividad</h5>
                                        <h7 class=" mb-0"> <?php echo $fechaFormateada; ?> </h7>
                                    </div>
                                    <div class="col-sm-6 col-lg-4">
                                        <h5>Organiza</h5>
                                        <p class="small mb-0"><?php echo $actividad['TipoActividad']; ?></p>
                                    </div>
                                    <div class="col-sm-3 col-lg-4">
                                        <h5>Estado</h5>
                                        <p><span class="badge bg-<?php echo $actividad['EstActividad'] == 'Activo' ? 'success' : 'danger'; ?>"><?php echo ucfirst($actividad['EstActividad']); ?></span></p>
                                    </div>
                                    <div class="col-lg-3">
                                        <h5>¿Donde?</h5>
                                        <p> <?php echo $actividad['LugarAct']; ?></p>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="text-lg-end">
                                            <a class="btn btn-primary-soft" id="toggleFormButton" data-bs-toggle="tooltip" data-bs-placement="top" title="Modificar"><i class="bi bi-pencil-square"></i></a>
                                        </div>
                                    </div>

                                    <div id="updateForm" style="display: none;">
                                        <hr>
                                        <div class="mt-4">
                                            <div class="align-items-center">
                                                <h5 class="mb-md-2"> Actualizar Actividad </h5>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <form method="POST" class="row g-3 p-3" enctype="multipart/form-data">
                                                <input type="hidden" name="idActividad" value="<?php echo $actividad['idActividad']; ?>">
                                                <input type="hidden" name="ImagenAnterior" value="<?php echo $actividad['ImagenActividad']; ?>">

                                                <div class="col-sm-6 col-lg-4 form-group">
                                                    <label for="TituloActividad">Nombre de la Actividad</label>
                                                    <input type="text" class="form-control" name="TituloActividad" value="<?php echo $actividad['TituloActividad']; ?>" required>
                                                </div>
                                                <div class="col-sm-6 col-lg-4 form-group">
                                                    <label for="FechaActividad">Fecha</label>
                                                    <input type="date" class="form-control" name="FechaActividad" value="<?php echo $actividad['FechaActividad']; ?>" required>
                                                </div>
                                                <div class="col-sm-6 col-lg-4 form-group">
                                                    <label for="HoraAct">Hora</label>
                                                    <input type="time" class="form-control" name="HoraAct" value="<?php echo $actividad['HoraAct']; ?>" required>
                                                </div>

                                                <div class="col-sm-6 col-lg-4 form-group">
                                                    <label class="form-label">Detalle de la Actividad</label>
                                                    <input type="text" class="form-control" name="DetalleActividad" value="<?php echo $actividad['DetalleActividad']; ?>" required>
                                                </div>

                                                <div class="col-sm-6 col-lg-6 form-group">
                                                    <label class="form-label">Seleccionar Nueva Imagen (opcional)</label>
                                                    <input type="file" class="form-control" id="NuevaImagenActividad" name="NuevaImagenActividad" accept=".jpg, .png">
                                                </div>

                                                <div class="col-12">
                                                    <input type="hidden" class="form-control" name="ImagenActividad" value="<?php echo $actividad['ImagenActividad']; ?>" required>
                                                    <img id="previewImagen" src="../assets/images/events/<?php echo $actividad['ImagenActividad']; ?>" alt="Imagen de la actividad" style="width: 100%; margin-top: 10px;">
                                                </div>

                                                <div class="col-sm-6 col-lg-4 form-group">
                                                    <label class="form-label">Lugar de la Actividad</label>
                                                    <input type="text" class="form-control" name="LugarAct" value="<?php echo $actividad['LugarAct']; ?>" required>
                                                </div>

                                                <input type="hidden" name="CiCat" value="<?php echo $actividad['CiCat']; ?>">

                                                <div class="col-sm-6 col-lg-4 form-group">
                                                    <label class="form-label">Tipo de Actividad</label>
                                                    <input type="text" class="form-control" name="TipoActividad" value="<?php echo $actividad['TipoActividad']; ?>" required>
                                                </div>

                                                <div class="col-sm-6 col-lg-4 form-group">
                                                    <label class="form-label">Estado:</label>
                                                    <?php $Act = $actividad['EstActividad']; ?>
                                                    <div>
                                                        <input type="radio" name="EstActividad" value="Activo" <?php echo ($Act == 'Activo') ? 'checked' : ''; ?>>
                                                        <label for="Activo">Activo</label>
                                                        &nbsp;&nbsp;&nbsp;
                                                        <input type="radio" name="EstActividad" value="Inactivo" <?php echo ($Act == 'Concluido') ? 'checked' : ''; ?>>
                                                        <label for="Inactivo">Inactivo</label>
                                                    </div>
                                                </div>

                                                <br>
                                                <button type="submit" class="btn btn-success-soft" name="actualizar_Actividad">Actualizar</button>
                                            </form>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <br>
                        </div>
                    </div>
            </section>
            <div class="col-md-2 col-lg-3 col-xxl-4">
                <div class="sidebar-end p-4 bg-mode custom-scrollbar h-100">

                    <div class="sidebar-end-alignment d-flex justify-content-center flex-column">
                        <div class="d-flex gap-2 align-items-center">
                            <a class="btn p-0 text-secondary sidebar-end-toggle d-none d-lg-flex">
                                <i class="bi bi-justify-left fs-3"></i>
                            </a>
                            <h5 class="contact-title mb-0">Coordinación</h5>
                        </div>

                        <ul class="list-unstyled">
                            <?php if (!empty($catequistas)): ?>
                                <?php foreach ($catequistas as $catequistaCoor): ?>
                                    <hr>
                                    <li class="mt-2 hstack gap-3 align-items-center position-relative">
                                        <input type="hidden" value="<?php echo htmlspecialchars($catequistaCoor['CiCat']); ?>">
                                        <div class="avatar avatar-xs">
                                            <img class="avatar-img rounded-circle" src="<?php echo ($catequistaCoor['ImagenCat'] == 'Ninguno' || empty($catequistaCoor['ImagenCat'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$catequistaCoor['ImagenCat']}"; ?>" alt="">
                                        </div>
                                        <div class="overflow-hidden contact-name">
                                            <p class="h6 mb-0"><?php echo htmlspecialchars($catequistaCoor['NombreCompleto']); ?></p>
                                        </div>
                                        <div class="contact-status ms-auto fs-3"></div>
                                    </li>
                                    <hr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>No hay catequistas disponibles.</p>
                            <?php endif; ?>
                        </ul>

                        <h5 class="mt-3 contact-title">Catequistas</h5>

                        <ul class="list-unstyled pb-5">

                            <li class="mt-3 hstack gap-3 align-items-center position-relative">
                                <div class="flex-shrink-0 avatar">
                                    <ul class="avatar-group avatar-group-four">


                                        <?php if (!empty($catsacramento)): ?>
                                            <?php foreach ($catsacramento as $catSac): ?>
                                                <li class="avatar avatar-xxs">
                                                    <img class="avatar-img rounded-circle" src="<?php echo ($catSac['ImagenCat'] == 'Ninguno' || empty($catSac['ImagenCat'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$catSac['ImagenCat']}"; ?>" alt="avatar">
                                                </li>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <li class="avatar avatar-xxs">
                                                <img class="avatar-img rounded-circle" src="" alt="avatar">
                                            </li>
                                            <li class="avatar avatar-xxs">
                                                <img class="avatar-img rounded-circle" src="" alt="avatar">
                                            </li>
                                            <li class="avatar avatar-xxs">
                                                <img class="avatar-img rounded-circle" src="" alt="avatar">
                                            </li>
                                            <li class="avatar avatar-xxs">
                                                <img class="avatar-img rounded-circle" src="" alt="avatar">
                                            </li>
                                        <?php endif; ?>



                                    </ul>
                                </div>
                                <div class="overflow-hidden contact-name">
                                    <a class="h6 mb-0 stretched-link text-truncate d-inline-block" href="VistaTodosCat.php">Ver todos</a>
                                </div>
                            </li>

                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel"><?php echo $actividad['TituloActividad']; ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img src="../assets/images/events/<?php echo $actividad['ImagenActividad'] !== 'Ninguno' ? $actividad['ImagenActividad'] : 'Generico.png'; ?>" class="img-fluid" alt="Imagen completa de <?php echo $actividad['TituloActividad']; ?>">
            </div>
        </div>
    </div>
</div>
<script>
    document.getElementById('toggleFormButton').addEventListener('click', function() {
        var form = document.getElementById('updateForm');
        if (form.style.display === 'none' || form.style.display === '') {
            form.style.display = 'block';
        } else {
            form.style.display = 'none';
        }
    });

    document.getElementById('NuevaImagenActividad').addEventListener('change', function(event) {
        var input = event.target;
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewImagen').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);

            var fileName = input.files[0].name;
            var fileExt = fileName.split('.').pop();
            var newFileName = 'ACT' + Math.random().toString(36).substr(2, 5).toUpperCase() + '.' + fileExt;

            document.querySelector('input[name="ImagenActividad"]').value = newFileName;
        }
    });





    $(document).ready(function() {
        function obtenerEstado() {
            $.ajax({
                url: '../VerificarCoordinador.php',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.EstadoCat && response.RolCat) {
                        $('#estado').text(response.EstadoCat);
                        $('#cargo').text(response.RolCat);
                        if (response.RolCat === "Catequista" || response.RolCat === "Titular") {
                            window.location.replace("../logout.php");
                        }
                        if (response.EstadoCat === "Inactivo") {
                            window.location.replace("PerfilCatequista.php");
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

        setInterval(obtenerEstado, 5000);

        obtenerEstado();
    });
</script>
<script src="../assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/functions.js"></script>
<script src="../assets/js/tema.js"></script>
</body>

</html>