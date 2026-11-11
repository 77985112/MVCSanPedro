<?php
require_once '../Controlador/controladorActividad.php';
require_once '../Conexion/Conexion.php';

session_start();
if (!isset($_SESSION['CiPersona'])) {
    header("Location: ../logout.php");
    exit();
}
$Catequista = $_SESSION['CiPersona'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && empty($_FILES)
    && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    http_response_code(413);
    exit('El archivo supera el límite de carga. Use una imagen de hasta 2 MB o un video de hasta 10 MB.');
}


if (!isset($_GET['idActividad'])) {
    header("Location: ActividadVista.php");
    exit();
}

$Actividad = $_GET['idActividad'];
$controlador = new ControladorActividad();

$conn = new Conexion();
$conexion = $conn->getConnection();

$sql = "SELECT * FROM actividades WHERE idActividad = ? AND EstActividad <> 'Eliminado'";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $Actividad);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "No se encontró la actividad.";
    exit();
}
$actividad = $result->fetch_assoc();
$actividad['ImagenesExtrasFilas'] = $controlador->imagenesExtras((int) $actividad['idActividad']);
$actividad['ImagenesExtras'] = array_column($actividad['ImagenesExtrasFilas'], 'ImagenActividad');
// Solo el coordinador del sacramento de la actividad puede editarla mientras esté activa.
$puedeEditarCoordinador = (($Catequista['RolCat'] ?? '') === 'Coordinador')
    && $actividad['TipoActividad'] === (string) ($Catequista['Sacramento'] ?? '')
    && ControladorActividad::esActividadEditable($actividad);
list($archivoImagen, $archivoVideo) = ControladorActividad::archivosActividad($actividad);$esVideoActividad = $archivoVideo !== null;$hayImagen = $archivoImagen !== null;$archivoActividad = rawurlencode($archivoImagen ?? 'Generico.png');

$diassemana = array("Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado");
$meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");

$fecha = new DateTime($actividad['FechaActividad']);
$fechaFormateada = $diassemana[$fecha->format('w')] . ", " . $fecha->format('d') . " de " . $meses[$fecha->format('n') - 1] . " de " . $fecha->format('Y');

$dia = $diassemana[$fecha->format('w')];
$mes = $fecha->format('d') . " de " . $meses[$fecha->format('n') - 1];


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['actualizar_Actividad'])) {
    if (($Catequista['RolCat'] ?? '') !== 'Coordinador') {
        http_response_code(403);
        exit('Solo los coordinadores pueden editar actividades.');
    }
    if ($actividad['TipoActividad'] !== (string) ($Catequista['Sacramento'] ?? '')) {
        http_response_code(403);
        exit('Solo puede editar actividades de ' . htmlspecialchars((string) ($Catequista['Sacramento'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '.');
    }
    if (!ControladorActividad::esActividadEditable($actividad)) {
        http_response_code(403);
        exit('La actividad ya no está activa; solo puede editarse si sigue activa y no está concluida.');
    }
    try {
        $_POST['idActividad'] = $actividad['idActividad'];
        $controlador->actualizarActividad($_POST);
        header("Location: ActividadVista.php");
        exit;
    } catch (InvalidArgumentException $e) {
        http_response_code(400);
        exit(htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
    } catch (Throwable $e) {
        error_log('Actualizar archivo de actividad: ' . $e->getMessage());
        http_response_code(500);
        exit('No se pudo actualizar la actividad. El archivo anterior se conserva.');
    }
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
            INNER JOIN `persona` p ON c.CiCat = p.CiPersona 
            WHERE n.EstadoNot = 'Activo' AND n.FechaNotFin > ? AND n.Sacramento = ?
            ORDER BY n.FechaNotFin ASC";

    $stmt = $conn->prepare($sql);
    $sacramentoActual = $_SESSION['CiPersona']['Sacramento'] ?? '';
    $stmt->bind_param("ss", $currentDateTime, $sacramentoActual);
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
    <link rel="stylesheet" type="text/css" href="../assets/css/logo-cabecera.css?v=<?= filemtime(__DIR__ . '/../assets/css/logo-cabecera.css') ?>">
    <script src="../assets/js/jquery-3.6.4.min.js"></script>
</head>

<header class="navbar-light fixed-top header-static bg-mode">
    <nav class="navbar navbar-expand-lg">
        <button class="btn text-secondary py-0 me-3 sidebar-start-toggle d-none d-lg-flex align-items-center"><i class="bi bi-justify-left fs-3 lh-0"></i></button>
        <div class="container">
            <a class="navbar-brand" href="VistaCoordinador.php">
                <img class="light-mode-item navbar-brand-item" src="../assets/images/logo.png?v=<?= filemtime(__DIR__ . '/../assets/images/logo.png') ?>" alt="logo">
                <img class="dark-mode-item navbar-brand-item" src="../assets/images/logo-light.png?v=<?= filemtime(__DIR__ . '/../assets/images/logo-light.png') ?>" alt="logo">
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
                        <img class="avatar-img rounded border border-white border-3" src="<?php echo ($Catequista['ImagenCat'] == 'Ninguno' || empty($Catequista['ImagenCat'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$Catequista['ImagenCat']}?v=" . time(); ?>" alt="">
                    </a>
                    <ul class="dropdown-menu dropdown-animation dropdown-menu-end pt-3 small me-md-n3" aria-labelledby="profileDropdown">
                        <li class="px-3">
                            <div class="d-flex align-items-center position-relative">
                                <div class="avatar me-3">
                                    <img class="avatar-img rounded-circle" src="<?php echo ($Catequista['ImagenCat'] == 'Ninguno' || empty($Catequista['ImagenCat'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$Catequista['ImagenCat']}?v=" . time(); ?>" alt="avatar">
                                </div>
                                <div>
                                    <a class="h6 stretched-link" href="#"><?php echo $Catequista['Nombre'] . " " . $Catequista['ApPaterno'] . " " . $Catequista['ApMaterno']; ?></a>
                                    <p class="small m-0"><?php echo $Catequista['RolCat'] === 'Coordinador' ? 'Coordinadores' : $Catequista['RolCat']; ?> <i class="bi bi-patch-check-fill text-success small"></i></p>
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
        <div class="d-flex align-items-center d-lg-none mb-2">
            <button class="border-0 bg-transparent p-0 d-flex align-items-center sidebar-start-toggle-mobile" type="button" aria-label="Otras opciones">
                <span class="btn btn-primary"><i class="fa-solid fa-sliders-h"></i></span>
                <span class="h6 mb-0 fw-bold ms-2 text-nowrap">Otras Opciones</span>
            </button>
        </div>
        <div class="row justify-content-between g-0">

            <div class="col-md-2 col-lg-3 col-xxl-4 mt-n4">
                <div class="nav-sidenav p-4 bg-mode h-100 custom-scrollbar">
                    <div class="d-flex justify-content-end d-lg-none mb-2">
                        <button class="btn btn-light btn-sm sidebar-start-close" type="button" aria-label="Cerrar menú"><i class="bi bi-x-lg"></i></button>
                    </div>
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
                        <li class="nav-item">
                            <a class="nav-link" href="VistaTodosCat.php"> <i class="bi bi-calendar-check nav-icon"></i> <span class="nav-text">Reporte Activos Inactivos</span></a>
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
                            <div class="card card-body <?php echo $hayImagen || !$esVideoActividad ? 'card-overlay-bottom' : 'bg-dark'; ?> border-0 col-lg-9" <?php if ($hayImagen || !$esVideoActividad): ?>style="background-image:url('../assets/images/events/<?php echo $archivoActividad; ?>'); background-position:center; background-size:cover; background-repeat:no-repeat;" data-bs-toggle="modal" data-bs-target="#imageModal"<?php endif; ?>>
                        <?php if ($esVideoActividad): ?>
                          <video class="w-100 rounded actividad-video" src="../assets/images/events/<?php echo rawurlencode($archivoVideo); ?>" controls playsinline preload="none" style="max-height:65vh; background:#000;">Su navegador no puede reproducir este video. <a href="../assets/images/events/<?php echo rawurlencode($archivoVideo); ?>">Abrir video</a></video>
                        <?php endif; ?>
                                <div class="row g-3 justify-content-between">
                                    <div class="col-lg-9">
                                        <div class="bg-mode text-center rounded overflow-hidden p-1 d-inline-block">
                                            <div class="bg-primary p-2 text-white rounded-top small lh-1"> <?php echo $dia; ?></div>
                                            <h5 class="mb-0 py-2 lh-1"><?php echo $mes; ?></h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-3 justify-content-between align-items-center <?php echo $esVideoActividad ? 'mt-3' : 'mt-5 pt-5'; ?> position-relative z-index-9">
                                    <div class="text-lg-end">
                                        <a class="btn btn-light-soft" href="ActividadVista.php"> Volver </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card card-body col-lg-9">
                                <div class="row g-4">

                                    <div class="col-lg-9">
                                        <h1 class="h3 mb-1"><?php echo htmlspecialchars($actividad['TituloActividad'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></h1>
                                    </div>
                                    <div class="col-9">
                                        <p class="mb-0"><?php echo htmlspecialchars($actividad['DetalleActividad'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
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
                                        <p> <?php echo htmlspecialchars($actividad['LugarAct'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
                                    </div>
                                    <?php if ($puedeEditarCoordinador): ?>
                                    <div class="col-lg-3">
                                        <div class="text-lg-end">
                                            <a class="btn btn-primary-soft" id="toggleFormButton" data-bs-toggle="tooltip" data-bs-placement="top" title="Modificar"><i class="bi bi-pencil-square"></i></a>
                                        </div>
                                    </div>
                                    <?php endif; ?>

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
                                                    <input type="text" class="form-control" name="TituloActividad" value="<?php echo htmlspecialchars($actividad['TituloActividad'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" required>
                                                </div>
                                                <div class="col-sm-6 col-lg-4 form-group">
                                                    <label for="FechaActividad">Fecha</label>
                                                    <input type="date" class="form-control" name="FechaActividad" min="<?php echo ControladorActividad::fechaMinimaActividad(); ?>" value="<?php echo $actividad['FechaActividad']; ?>" required>
                                                    <div class="form-text">Solo fechas desde hoy en adelante; al pasar la fecha la actividad queda concluida automáticamente.</div>
                                                </div>
                                                <div class="col-sm-6 col-lg-4 form-group">
                                                    <label for="HoraAct">Hora</label>
                                                    <input type="time" class="form-control" name="HoraAct" value="<?php echo $actividad['HoraAct']; ?>" required>
                                                </div>

                                                <div class="col-12 form-group">
                                                    <label class="form-label">Detalle de la Actividad</label>
                                                    <textarea class="form-control" name="DetalleActividad" rows="5" maxlength="<?php echo ControladorActividad::LIMITE_DESCRIPCION; ?>" aria-describedby="ayudaDescripcionActividad" required><?php echo htmlspecialchars($actividad['DetalleActividad'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></textarea>
                                                    <div id="ayudaDescripcionActividad" class="form-text">Hasta 5.000 caracteres.</div>
                                                </div>

                                                <div class="col-sm-6 col-lg-6 form-group">
                                                    <?php $imagenesActuales = ControladorActividad::imagenesActividad($actividad); $filasExtras = $actividad['ImagenesExtrasFilas'] ?? []; ?>
                                                    <label class="form-label">Imágenes actuales / nuevas imágenes (hasta 5 en total)</label>
                                                    <?php if ($imagenesActuales): ?>
                                                    <div id="galeriaActualImagen" class="d-flex flex-wrap align-items-start gap-2 mb-2">
                                                        <div class="text-center">
                                                            <img src="../assets/images/events/<?php echo rawurlencode($imagenesActuales[0]); ?>" class="rounded border" style="width:86px; height:66px; object-fit:cover;" alt="Imagen principal">
                                                            <div class="form-text" style="font-size:.65rem;">Principal</div>
                                                        </div>
                                                        <?php foreach ($filasExtras as $filaExtra): ?>
                                                        <div class="text-center">
                                                            <img src="../assets/images/events/<?php echo rawurlencode($filaExtra['ImagenActividad']); ?>" class="rounded border" style="width:86px; height:66px; object-fit:cover;" alt="Imagen adicional">
                                                            <div class="form-check m-0" style="font-size:.65rem;">
                                                                <input class="form-check-input" type="checkbox" name="QuitarImagenExtra[]" value="<?php echo (int) $filaExtra['idActImg']; ?>" id="quitarExtra<?php echo (int) $filaExtra['idActImg']; ?>">
                                                                <label class="form-check-label" for="quitarExtra<?php echo (int) $filaExtra['idActImg']; ?>">Quitar</label>
                                                            </div>
                                                        </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    <?php endif; ?>
                                                    <input type="file" class="form-control" id="NuevaImagenActividad" name="NuevaImagenActividad[]" multiple accept=".jpg,.jpeg,.png,image/jpeg,image/png" data-actividad-imagen="previewImagen" data-actividad-grupo="edicionActividad" data-actividad-maximas="<?php echo max(0, ControladorActividad::MAXIMO_IMAGENES - count($imagenesActuales)); ?>" data-actividad-galeria="galeriaActualImagen">
                                                    <?php if ($hayImagen): ?>
                                                    <div class="form-check mt-2">
                                                        <input class="form-check-input" type="checkbox" id="QuitarImagen" name="QuitarImagen" value="1" data-quitar-actividad="NuevaImagenActividad">
                                                        <label class="form-check-label small" for="QuitarImagen">Quitar todas las imágenes actuales</label>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="col-sm-6 col-lg-6 form-group">
                                                    <label class="form-label">Video actual / nuevo video (opcional)</label>
                                                    <input type="file" class="form-control" id="NuevaVideoActividad" name="NuevaVideoActividad" accept=".mp4,.webm,video/mp4,video/webm" data-actividad-video="previewVideo" data-actividad-grupo="edicionActividad">
                                                    <?php if ($esVideoActividad): ?>
                                                    <div class="form-check mt-2">
                                                        <input class="form-check-input" type="checkbox" id="QuitarVideo" name="QuitarVideo" value="1" data-quitar-actividad="NuevaVideoActividad">
                                                        <label class="form-check-label small" for="QuitarVideo">Quitar el video actual</label>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="col-12">
                                                    <div class="form-text">Sin seleccionar otro archivo, se conservan los actuales. Puede agregar hasta 5 imágenes en total (JPG/PNG, hasta 2 MB c/u); videos MP4/WebM hasta 10 MB.</div>
                                                    <div id="previewImagen" class="d-flex flex-wrap gap-2 mt-3"></div>
                                                    <video id="previewVideo" <?php if ($esVideoActividad): ?>src="../assets/images/events/<?php echo rawurlencode($archivoVideo); ?>"<?php endif; ?> class="w-100 rounded mt-3 actividad-video <?php echo $esVideoActividad ? '' : 'd-none'; ?>" controls playsinline preload="none" style="max-height:300px;"></video>
                                                </div>

                                                <div class="col-12 form-group">
                                                    <label class="form-label" for="editarVideoUrl">Enlace de video (YouTube, TikTok o Facebook)</label>
                                                    <input type="url" class="form-control" id="editarVideoUrl" name="VideoUrl" maxlength="255" placeholder="https://www.youtube.com/watch?v=..." value="<?php echo htmlspecialchars($actividad['VideoUrl'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
                                                    <div class="form-text">Opcional. También puede subir un video MP4/WebM.</div>
                                                    <div id="previewVideoUrl" class="mt-2"></div>
                                                </div>

                                                <div class="col-sm-6 col-lg-4 form-group">
                                                    <label class="form-label">Lugar de la Actividad</label>
                                                    <input type="text" class="form-control" name="LugarAct" maxlength="<?php echo ControladorActividad::LIMITE_LUGAR; ?>" aria-describedby="ayudaLugarActividad" value="<?php echo htmlspecialchars($actividad['LugarAct'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" required>
                                                    <div id="ayudaLugarActividad" class="form-text">Hasta 255 caracteres; puede incluir la dirección y referencias.</div>
                                                </div>

                                                <input type="hidden" name="CiCat" value="<?php echo $actividad['CiCat']; ?>">

                                                <div class="col-sm-6 col-lg-4 form-group">
                                                    <label class="form-label">Tipo de Actividad</label>
                                                    <input type="hidden" name="TipoActividad" value="<?php echo htmlspecialchars($actividad['TipoActividad'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
                                                    <div class="form-control bg-light"><?php echo htmlspecialchars($actividad['TipoActividad'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></div>
                                                    <div class="form-text">Se conserva el tipo con el que se publicó.</div>
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
                                            <img class="avatar-img rounded-circle" src="<?php echo ($catequistaCoor['ImagenCat'] == 'Ninguno' || empty($catequistaCoor['ImagenCat'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$catequistaCoor['ImagenCat']}?v=" . time(); ?>" alt="">
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
                                                    <img class="avatar-img rounded-circle" src="<?php echo ($catSac['ImagenCat'] == 'Ninguno' || empty($catSac['ImagenCat'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$catSac['ImagenCat']}?v=" . time(); ?>" alt="avatar">
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
                <h5 class="modal-title" id="imageModalLabel"><?php echo htmlspecialchars($actividad['TituloActividad'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if ($hayImagen || !$esVideoActividad): ?>
                <img id="imagenGrandeDetalle" src="../assets/images/events/<?php echo $archivoActividad; ?>" class="img-fluid" alt="Imagen completa de <?php echo htmlspecialchars($actividad['TituloActividad'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
                <?php $imagenesGaleria = ControladorActividad::imagenesActividad($actividad); ?>
                <?php if (count($imagenesGaleria) > 1): ?>
                <div class="d-flex flex-wrap justify-content-center gap-2 mt-3">
                    <?php foreach ($imagenesGaleria as $indiceGaleria => $nombreGaleria): ?>
                    <img src="../assets/images/events/<?php echo rawurlencode($nombreGaleria); ?>" class="rounded border" style="width:84px; height:64px; object-fit:cover; cursor:pointer;" data-imagen-modal="../assets/images/events/<?php echo rawurlencode($nombreGaleria); ?>" onclick="var galeriaGrande = document.getElementById('imagenGrandeDetalle'); galeriaGrande.src = this.dataset.imagenModal; galeriaGrande.alt = this.alt;" alt="Imagen <?php echo $indiceGaleria + 1; ?> de la actividad">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <?php endif; ?>
                <?php if ($esVideoActividad): ?>
                <video class="w-100 rounded actividad-video<?php echo $hayImagen ? ' mt-3' : ''; ?>" src="../assets/images/events/<?php echo rawurlencode($archivoVideo); ?>" controls playsinline preload="none" style="max-height:65vh; background:#000;">Su navegador no puede reproducir este video. <a href="../assets/images/events/<?php echo rawurlencode($archivoVideo); ?>">Abrir video</a></video>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script>
    (function () {
        var botonToggle = document.getElementById('toggleFormButton');
        if (botonToggle) {
            botonToggle.addEventListener('click', function () {
                var form = document.getElementById('updateForm');
                if (form.style.display === 'none' || form.style.display === '') {
                    form.style.display = 'block';
                } else {
                    form.style.display = 'none';
                }
            });
        }
        <?php if ($puedeEditarCoordinador): ?>
        // Enlace "Editar" de la tarjeta (?editar=1): abre el formulario directamente.
        if (new URLSearchParams(window.location.search).get('editar') === '1') {
            var formEdicion = document.getElementById('updateForm');
            if (formEdicion) {
                formEdicion.style.display = 'block';
                formEdicion.scrollIntoView({ block: 'start' });
            }
        }
        <?php endif; ?>
    })();

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
  <script src="../assets/js/actividad-multimedia.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/actividad-multimedia.js'); ?>"></script>
    <script src="../assets/js/actividad-compresion.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/actividad-compresion.js'); ?>"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var abrir = document.querySelector('.sidebar-start-toggle-mobile');
        var cerrar = document.querySelector('.sidebar-start-close');
        if (abrir) {
            abrir.addEventListener('click', function() {
                document.body.classList.add('sidebar-start-enabled');
            });
        }
        if (cerrar) {
            cerrar.addEventListener('click', function() {
                document.body.classList.remove('sidebar-start-enabled');
            });
        }
    });
</script>
<script>
    // Vista previa del video por enlace de la actividad.
    (function () {
        var campo = document.getElementById('editarVideoUrl');
        var caja = document.getElementById('previewVideoUrl');
        if (!campo || !caja) return;

        function embed(url) {
            var u;
            try { u = new URL(url); } catch (e) { return null; }
            var host = u.hostname.toLowerCase().replace(/^www\./, '');
            var ruta = u.pathname || '';
            var m;

            if (['youtube.com', 'm.youtube.com', 'youtube-nocookie.com'].indexOf(host) >= 0) {
                var id = u.searchParams.get('v') || '';
                if (!id && (m = ruta.match(/^\/(?:shorts|embed|live|v)\/([A-Za-z0-9_-]{6,20})/))) id = m[1];
                return /^[A-Za-z0-9_-]{6,20}$/.test(id) ? 'https://www.youtube.com/embed/' + id : null;
            }
            if (host === 'youtu.be') {
                m = ruta.match(/^\/([A-Za-z0-9_-]{6,20})/);
                return m ? 'https://www.youtube.com/embed/' + m[1] : null;
            }
            if (host === 'tiktok.com' || host === 'm.tiktok.com') {
                m = ruta.match(/\/video\/(\d{6,25})/);
                return m ? 'https://www.tiktok.com/embed/v2/' + m[1] : null;
            }
            if (['facebook.com', 'm.facebook.com', 'web.facebook.com', 'fb.watch'].indexOf(host) >= 0) {
                return 'https://www.facebook.com/plugins/video.php?href=' + encodeURIComponent(url) + '&show_text=false';
            }
            return null;
        }

        function actualizar() {
            var valor = campo.value.trim();
            caja.replaceChildren();
            if (!valor) return;

            var src = embed(valor);
            if (!src) {
                var error = document.createElement('div');
                error.className = 'form-text text-danger';
                error.textContent = 'Use un enlace de YouTube, TikTok o Facebook.';
                caja.appendChild(error);
                return;
            }

            var marco = document.createElement('iframe');
            marco.src = src;
            marco.className = 'w-100 rounded';
            marco.style.cssText = 'height:340px;border:0;background:#101722;';
            marco.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share');
            marco.setAttribute('allowfullscreen', '');
            marco.setAttribute('loading', 'lazy');
            marco.setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');
            marco.title = 'Vista previa del video';
            caja.appendChild(marco);
        }

        campo.addEventListener('input', actualizar);
        campo.addEventListener('change', actualizar);
        actualizar();
    })();
</script>
</body>

</html>