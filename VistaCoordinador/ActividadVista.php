<?php
require_once '../Controlador/controladorActividad.php';

session_start();
if (!isset($_SESSION['CiPersona'])) {
    header("Location: ../logout.php");
    exit();
}
$Catequista = $_SESSION['CiPersona'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && empty($_FILES)
    && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    http_response_code(413);
    exit('El archivo supera el límite de carga. Use una imagen de hasta 5 MB o un video de hasta 100 MB.');
}


$controlador = new ControladorActividad();
$fechaMinimaActividad = ControladorActividad::fechaMinimaActividad();

// Coordinador de sacramento (Primera Comunión / Confirmación): puede editar y eliminar
// las actividades de SU sacramento mientras estén activas (la eliminación no requiere estado).
$sesionCoordinador = $_SESSION['CiPersona'] ?? null;
$esCoordinador = is_array($sesionCoordinador) && ($sesionCoordinador['RolCat'] ?? '') === 'Coordinador';
$sacramentoCoordinador = $esCoordinador ? (string) ($sesionCoordinador['Sacramento'] ?? '') : '';
if (empty($_SESSION['csrf_actividad_eliminar'])) {
    $_SESSION['csrf_actividad_eliminar'] = bin2hex(random_bytes(32));
}
$actividadEliminada = !empty($_SESSION['actividad_eliminada']);
unset($_SESSION['actividad_eliminada']);
$errorEliminarActividad = isset($_SESSION['actividad_eliminada_error'])
    ? (string) $_SESSION['actividad_eliminada_error'] : '';
unset($_SESSION['actividad_eliminada_error']);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['Nueva_Actividad'])) {
    try {
        $controlador->registrarActividad($_POST);
        header("Location: ActividadVista.php");
        exit;
    } catch (InvalidArgumentException $e) {
        http_response_code(400);
        echo htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    } catch (Throwable $e) {
        error_log('Registrar archivo de actividad: ' . $e->getMessage());
        http_response_code(500);
        echo 'No se pudo publicar la actividad. Intente nuevamente.';
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'eliminar_actividad') {
    if (!$esCoordinador) {
        http_response_code(403);
        exit('Solo los coordinadores pueden eliminar actividades.');
    }
    $errorEliminacion = '';
    if (!is_string($_POST['csrf_actividad_eliminar'] ?? null)
        || !hash_equals($_SESSION['csrf_actividad_eliminar'], $_POST['csrf_actividad_eliminar'])) {
        $errorEliminacion = 'La sesión del formulario venció. Recargue la página e intente de nuevo.';
    } else {
        $idEliminar = filter_var($_POST['idActividad'] ?? null, FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]);
        $actividadEliminar = $idEliminar ? $controlador->obtenerActividadPorId($idEliminar) : null;
        if (!$actividadEliminar) {
            $errorEliminacion = 'No se encontró la actividad.';
        } elseif ($actividadEliminar['TipoActividad'] !== $sacramentoCoordinador) {
            $errorEliminacion = 'Solo puede eliminar actividades de ' . $sacramentoCoordinador . '.';
        } else {
            try {
                $controlador->eliminarActividad($idEliminar);
                $_SESSION['actividad_eliminada'] = true;
            } catch (InvalidArgumentException $e) {
                $errorEliminacion = $e->getMessage();
            } catch (Throwable $e) {
                error_log('Eliminar actividad (coordinador): ' . $e->getMessage());
                $errorEliminacion = 'No se pudo eliminar la actividad. Intente nuevamente.';
            }
        }
    }
    if ($errorEliminacion !== '') {
        $_SESSION['actividad_eliminada_error'] = $errorEliminacion;
    }
    header('Location: ActividadVista.php', true, 303);
    exit();
}

$actividadesParroquia = $controlador->obtenerActividades('Parroquia');
$actividadesPrimeraComunion = $controlador->obtenerActividades('Primera Comunión');
$actividadesConfirmacion = $controlador->obtenerActividades('Confirmación');
$actividadesTodas = $controlador->obtenerActividades();

require_once '../Controlador/controladorCatCoor.php';
$contCats = new CatsController();
$catequistas = $contCats->index($Catequista['CiCat']);
$catsacramento = $contCats->CatequsitasSac($Catequista['Sacramento']);


require_once '../Conexion/Conexion.php';

$conexion = new Conexion();
$conn = $conexion->getConnection();

function getActiveNotifications($conn)
{
    $currentDateTime = date('Y-m-d H:i:s');

    $sql = "SELECT n.idNotificacion, n.TituloNot, n.DetalleNot, n.FechaNotFin, CONCAT(p.Nombre, ' ', p.ApPaterno) AS datoscat 
            FROM notificaciones n 
            INNER JOIN catequista c ON n.CiNotificador = c.CiCat 
            INNER JOIN `persona` p ON c.CiCat = p.CiPersona 
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
    <link rel="stylesheet" type="text/css" href="../assets/css/logo-cabecera.css?v=<?= filemtime(__DIR__ . '/../assets/css/logo-cabecera.css') ?>">
    <script src="../assets/js/jquery-3.6.4.min.js"></script>
</head>

<body class="sidebar-start-enabled">
    <script>
        if (window.innerWidth < 992) {
            document.body.classList.remove('sidebar-start-enabled');
        }
    </script>

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


            <section class="pt-0 ">
                <div class="container">
                    <div class="card">

                        <div class="card-header d-sm-flex align-items-center text-center justify-content-sm-between border-0 pb-0 py-5">
                            <h1 class="h4 card-title">Actividades Registradas</h1>
                            <div class="mb-3 d-flex align-items-end">
                                <button type="button" class="btn btn-primary-soft me-2" data-bs-toggle="modal" data-bs-target="#feedActionActividad"><i class="bi bi-plus-circle"></i></button>
                            </div>
                        </div>

                        <div class="card-body">
                            <ul class="nav nav-tabs nav-bottom-line justify-content-center justify-content-md-start">
                                <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-1"> Todos</a></li>
                                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-2"> Parroquia </a></li>
                                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-3"> Primera Comunión </a></li>
                                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-4"> Confirmación </a></li>
                            </ul>
                            <?php if ($actividadEliminada): ?>
                                <div class="alert alert-success" role="alert"><i class="bi bi-check-circle me-1"></i>Actividad eliminada correctamente.</div>
                            <?php endif; ?>
                            <?php if ($errorEliminarActividad !== ''): ?>
                                <div class="alert alert-danger" role="alert"><i class="bi bi-exclamation-triangle me-1"></i><?php echo htmlspecialchars($errorEliminarActividad, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></div>
                            <?php endif; ?>
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
                    <?php echo ControladorActividad::mediaActividad($actividad, '150px'); ?>
                                                            <div class="badge bg-<?php echo $actividad['EstActividad'] == 'Activo' ? 'success' : 'danger'; ?> text-white mt-2 me-2 position-absolute top-0 end-0"><?php echo $actividad['EstActividad']; ?></div>
                                                        </div>
                                                        <div class="card-body position-relative pt-0">
                                                            <input type="hidden" value="<?php echo $actividad['idActividad']; ?>">
                                                            <h6 class="mt-3"><a href="DetalleActividad.php?idActividad=<?php echo $actividad['idActividad']; ?>"><?php echo htmlspecialchars($actividad['TituloActividad'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a></h6>
                                                            <p class="mb-0 small"><i class="bi bi-calendar-check pe-1"></i><?php echo $actividad['FechaActividad']; ?> a <?php echo $actividad['HoraAct']; ?></p>
                                                            <p class="small"><i class="bi bi-geo-alt pe-1"></i><?php echo htmlspecialchars($actividad['LugarAct'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
                                                            <?php if ($esCoordinador && $actividad['TipoActividad'] === $sacramentoCoordinador && ControladorActividad::esActividadEditable($actividad)): ?>
                                                                <a class="btn btn-primary-soft btn-sm w-100 mt-3" href="DetalleActividad.php?idActividad=<?php echo (int) $actividad['idActividad']; ?>&amp;editar=1#updateForm">
                                                                    <i class="bi bi-pencil-square me-1"></i>Editar
                                                                </a>
                                                            <?php endif; ?>
                                                            <?php if ($esCoordinador && $actividad['TipoActividad'] === $sacramentoCoordinador): ?>
                                                                <button type="button" class="btn btn-outline-danger btn-sm w-100 mt-3" data-bs-toggle="modal" data-bs-target="#modalEliminarActividad" data-id="<?php echo $actividad['idActividad']; ?>" data-titulo="<?php echo htmlspecialchars($actividad['TituloActividad'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
                                                                    <i class="bi bi-trash3 me-1"></i>Eliminar
                                                                </button>
                                                            <?php endif; ?>
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
                    <?php echo ControladorActividad::mediaActividad($actividad, '150px'); ?>
                                                            <div class="badge bg-<?php echo $actividad['EstActividad'] == 'Activo' ? 'success' : 'danger'; ?> text-white mt-2 me-2 position-absolute top-0 end-0"><?php echo $actividad['EstActividad']; ?></div>
                                                        </div>
                                                        <div class="card-body position-relative pt-0">
                                                            <input type="hidden" value="<?php echo $actividad['idActividad']; ?>">
                                                            <h6 class="mt-3"><a href="DetalleActividad.php?idActividad=<?php echo $actividad['idActividad']; ?>"><?php echo htmlspecialchars($actividad['TituloActividad'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a></h6>
                                                            <p class="mb-0 small"><i class="bi bi-calendar-check pe-1"></i><?php echo $actividad['FechaActividad']; ?> a <?php echo $actividad['HoraAct']; ?></p>
                                                            <p class="small"><i class="bi bi-geo-alt pe-1"></i><?php echo htmlspecialchars($actividad['LugarAct'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
                                                            <?php if ($esCoordinador && $actividad['TipoActividad'] === $sacramentoCoordinador && ControladorActividad::esActividadEditable($actividad)): ?>
                                                                <a class="btn btn-primary-soft btn-sm w-100 mt-3" href="DetalleActividad.php?idActividad=<?php echo (int) $actividad['idActividad']; ?>&amp;editar=1#updateForm">
                                                                    <i class="bi bi-pencil-square me-1"></i>Editar
                                                                </a>
                                                            <?php endif; ?>
                                                            <?php if ($esCoordinador && $actividad['TipoActividad'] === $sacramentoCoordinador): ?>
                                                                <button type="button" class="btn btn-outline-danger btn-sm w-100 mt-3" data-bs-toggle="modal" data-bs-target="#modalEliminarActividad" data-id="<?php echo $actividad['idActividad']; ?>" data-titulo="<?php echo htmlspecialchars($actividad['TituloActividad'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
                                                                    <i class="bi bi-trash3 me-1"></i>Eliminar
                                                                </button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>

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
                    <?php echo ControladorActividad::mediaActividad($actividad, '150px'); ?>
                                                            <div class="badge bg-<?php echo $actividad['EstActividad'] == 'Activo' ? 'success' : 'danger'; ?> text-white mt-2 me-2 position-absolute top-0 end-0"><?php echo $actividad['EstActividad']; ?></div>
                                                        </div>
                                                        <div class="card-body position-relative pt-0">
                                                            <input type="hidden" value="<?php echo $actividad['idActividad']; ?>">
                                                            <h6 class="mt-3"><a href="DetalleActividad.php?idActividad=<?php echo $actividad['idActividad']; ?>"><?php echo htmlspecialchars($actividad['TituloActividad'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a></h6>
                                                            <p class="mb-0 small"><i class="bi bi-calendar-check pe-1"></i><?php echo $actividad['FechaActividad']; ?> a <?php echo $actividad['HoraAct']; ?></p>
                                                            <p class="small"><i class="bi bi-geo-alt pe-1"></i><?php echo htmlspecialchars($actividad['LugarAct'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
                                                            <?php if ($esCoordinador && $actividad['TipoActividad'] === $sacramentoCoordinador && ControladorActividad::esActividadEditable($actividad)): ?>
                                                                <a class="btn btn-primary-soft btn-sm w-100 mt-3" href="DetalleActividad.php?idActividad=<?php echo (int) $actividad['idActividad']; ?>&amp;editar=1#updateForm">
                                                                    <i class="bi bi-pencil-square me-1"></i>Editar
                                                                </a>
                                                            <?php endif; ?>
                                                            <?php if ($esCoordinador && $actividad['TipoActividad'] === $sacramentoCoordinador): ?>
                                                                <button type="button" class="btn btn-outline-danger btn-sm w-100 mt-3" data-bs-toggle="modal" data-bs-target="#modalEliminarActividad" data-id="<?php echo $actividad['idActividad']; ?>" data-titulo="<?php echo htmlspecialchars($actividad['TituloActividad'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
                                                                    <i class="bi bi-trash3 me-1"></i>Eliminar
                                                                </button>
                                                            <?php endif; ?>
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
                    <?php echo ControladorActividad::mediaActividad($actividad, '150px'); ?>
                                                            <div class="badge bg-<?php echo $actividad['EstActividad'] == 'Activo' ? 'success' : 'danger'; ?> text-white mt-2 me-2 position-absolute top-0 end-0"><?php echo $actividad['EstActividad']; ?></div>
                                                        </div>
                                                        <div class="card-body position-relative pt-0">
                                                            <input type="hidden" value="<?php echo $actividad['idActividad']; ?>">
                                                            <h6 class="mt-3"><a href="DetalleActividad.php?idActividad=<?php echo $actividad['idActividad']; ?>"><?php echo htmlspecialchars($actividad['TituloActividad'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a></h6>
                                                            <p class="mb-0 small"><i class="bi bi-calendar-check pe-1"></i><?php echo $actividad['FechaActividad']; ?> a <?php echo $actividad['HoraAct']; ?></p>
                                                            <p class="small"><i class="bi bi-geo-alt pe-1"></i><?php echo htmlspecialchars($actividad['LugarAct'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
                                                            <?php if ($esCoordinador && $actividad['TipoActividad'] === $sacramentoCoordinador && ControladorActividad::esActividadEditable($actividad)): ?>
                                                                <a class="btn btn-primary-soft btn-sm w-100 mt-3" href="DetalleActividad.php?idActividad=<?php echo (int) $actividad['idActividad']; ?>&amp;editar=1#updateForm">
                                                                    <i class="bi bi-pencil-square me-1"></i>Editar
                                                                </a>
                                                            <?php endif; ?>
                                                            <?php if ($esCoordinador && $actividad['TipoActividad'] === $sacramentoCoordinador): ?>
                                                                <button type="button" class="btn btn-outline-danger btn-sm w-100 mt-3" data-bs-toggle="modal" data-bs-target="#modalEliminarActividad" data-id="<?php echo $actividad['idActividad']; ?>" data-titulo="<?php echo htmlspecialchars($actividad['TituloActividad'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
                                                                    <i class="bi bi-trash3 me-1"></i>Eliminar
                                                                </button>
                                                            <?php endif; ?>
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


<div class="modal fade" id="feedActionActividad" tabindex="-1" aria-labelledby="feedActionActividad" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="feedActionPhotoLabel">Registrar Nueva Actividad</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" class="row g-3 p-3" enctype="multipart/form-data">
                <div class="d-flex mb-3">
                    <div class="avatar avatar-xs me-2">
                        <img class="avatar-img rounded-circle" src="<?php echo ($Catequista['ImagenCat'] == 'Ninguno' || empty($Catequista['ImagenCat'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$Catequista['ImagenCat']}?v=" . time(); ?>" alt="">
                    </div>
                    <div class="w-100">
                        <input type="text" class="form-control" name="TituloActividad" placeholder="Título de la Actividad" required>
                    </div>
                </div>
                <div class="w-100">
                    <textarea class="form-control pe-4 fs-3 lh-1 border-0" name="DetalleActividad" rows="4" maxlength="<?php echo ControladorActividad::LIMITE_DESCRIPCION; ?>" aria-describedby="ayudaDescripcionActividad" placeholder="Detalle de la Actividad..." required></textarea>
                    <div id="ayudaDescripcionActividad" class="form-text">Hasta 5.000 caracteres.</div>
                </div>
                <hr>

                <div class="col-sm-6 col-lg-4 form-group">
                    <label for="FechaActividad">Fecha:</label>
                    <input type="date" class="form-control" name="FechaActividad" id="FechaActividad" min="<?php echo $fechaMinimaActividad; ?>" required>
                    <div class="form-text">Solo puede registrar fechas desde hoy en adelante; al pasar la fecha la actividad quedará concluida automáticamente.</div>
                </div>
                <div class="col-sm-6 col-lg-4 form-group">
                    <label for="HoraAct">Hora:</label>
                    <input type="time" class="form-control" name="HoraAct" id="HoraAct" required>
                </div>

                <input type="hidden" name="ImagenActividad" id="ImagenActividad">

                <input type="hidden" name="TipoActividad" id="tipoActividadFormulario" value="Parroquia">

                <div class="col-sm-6 col-lg-8 form-group">
                    <label for="LugarAct">Lugar de Actividad:</label>
                    <input type="text" class="form-control" name="LugarAct" maxlength="<?php echo ControladorActividad::LIMITE_LUGAR; ?>" aria-describedby="ayudaLugarActividad" required>
                    <div id="ayudaLugarActividad" class="form-text">Hasta 255 caracteres; puede incluir la dirección y referencias.</div>
                </div>

                <input type="hidden" name="CiCat" value="<?php echo $Catequista['CiCat'] ?>" required>
                <br>
                <div>
                    <label class="form-label">Agregue una imagen o un video de la actividad</label>
                    <div class="card shadow-none p-3">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label small mb-1" for="NuevaImagenActividad">Imágenes (JPG/PNG, hasta 5 MB c/u, máximo 7)</label>
                                <input type="file" class="form-control py-2" id="NuevaImagenActividad" name="NuevaImagenActividad[]" multiple accept=".jpg,.jpeg,.png,image/jpeg,image/png" data-actividad-imagen="previewImagen" data-actividad-grupo="actividadCoordinador" data-actividad-requerido="true" data-actividad-maximas="<?php echo ControladorActividad::MAXIMO_IMAGENES; ?>">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small mb-1" for="NuevaVideoActividad">Video (MP4/WebM, hasta 100 MB)</label>
                                <input type="file" class="form-control py-2" id="NuevaVideoActividad" name="NuevaVideoActividad" accept=".mp4,.webm,video/mp4,video/webm" data-actividad-video="previewVideo" data-actividad-grupo="actividadCoordinador" data-actividad-requerido="true">
                                <video id="previewVideo" class="w-100 rounded mt-3 d-none actividad-video" controls playsinline preload="metadata" style="max-height:300px;"></video>
                            </div>
                        </div>
                        <div id="previewImagen" class="d-flex flex-wrap gap-2 mt-3"></div>
                        <div class="form-text mt-2">Puede adjuntar hasta 7 imágenes (JPG/PNG, hasta 5 MB c/u) y/o un video; al menos uno es obligatorio. La primera imagen será la principal.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger-soft me-2" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="Nueva_Actividad" class="btn btn-success-soft">Publicar</button>
                </div>
            </form>


        </div>
    </div>
</div>

<div class="modal fade" id="modalEliminarActividad" tabindex="-1" aria-labelledby="tituloEliminarActividad" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" id="formEliminarActividad">
                <input type="hidden" name="accion" value="eliminar_actividad">
                <input type="hidden" name="idActividad" id="eliminarActividadId" value="">
                <input type="hidden" name="csrf_actividad_eliminar" value="<?php echo htmlspecialchars($_SESSION['csrf_actividad_eliminar'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="tituloEliminarActividad">Eliminar actividad</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">¿Seguro que desea eliminar la actividad <strong id="eliminarActividadTitulo"></strong>?</p>
                    <p class="small mb-0 text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Se eliminará también su imagen o video, y la actividad dejará de mostrarse en la página web. Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger-soft" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-trash3 me-1"></i>Eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function () {
        // El tipo de actividad se toma de la pestaña desde la que se abre el formulario
        // (Todos/Parroquia -> Parroquia, Primera Comunión -> Primera Comunión, Confirmación -> Confirmación).
        var tiposPorPestana = { 'tab-1': 'Parroquia', 'tab-2': 'Parroquia', 'tab-3': 'Primera Comunión', 'tab-4': 'Confirmación' };
        var modalNueva = document.getElementById('feedActionActividad');
        if (modalNueva) {
            modalNueva.addEventListener('show.bs.modal', function () {
                var activa = document.querySelector('.nav-tabs .nav-link.active');
                var pestana = activa ? (activa.getAttribute('href') || '').replace('#', '') : '';
                var campo = document.getElementById('tipoActividadFormulario');
                if (campo) campo.value = tiposPorPestana[pestana] || 'Parroquia';
            });
        }
        // Modal de eliminar: carga el id y el título de la tarjeta elegida.
        var modalEliminar = document.getElementById('modalEliminarActividad');
        if (modalEliminar && window.bootstrap) {
            modalEliminar.addEventListener('show.bs.modal', function (event) {
                var boton = event.relatedTarget;
                if (!boton) return;
                document.getElementById('eliminarActividadId').value = boton.getAttribute('data-id') || '';
                document.getElementById('eliminarActividadTitulo').textContent = boton.getAttribute('data-titulo') || '';
            });
        }
    })();
</script>
<script>
    const todays = new Date();
    const horaInput = document.getElementById('HoraAct');

    horaInput.addEventListener('change', function() {
        const [hours, minutes] = this.value.split(':').map(Number);
        this.value = `${String(hours).padStart(2, '0')}:30`;
        if (minutes >= 30) {
            this.value = `${String(hours).padStart(2, '0')}:00`;
        }
    });
</script>

<script>
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
</body>

</html>