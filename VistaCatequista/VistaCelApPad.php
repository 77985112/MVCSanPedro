<?php
require_once '../Controlador/ControladorPersona.php';
session_start();

if (!isset($_SESSION['CiPersona'])) {
    header("Location: ../logout.php");
    exit();
}

$Catequista = $_SESSION['CiPersona'];
if (isset($_GET['accins']) && ctype_digit((string) $_GET['accins'])) {
    $idInscripcion = (int) $_GET['accins'];
    $_SESSION['ACCIns'] = $idInscripcion;
} elseif (isset($_SESSION['ACCIns'])) {
    $idInscripcion = $_SESSION['ACCIns'];
} else {
    header("Location: VistaDocCel.php");
    exit();
}


require_once '../Controlador/controladorCatCoor.php';
$contCats = new CatsController();
$catequistas = $contCats->index($Catequista['CiCat']);
$catsacramento = $contCats->CatequsitasSac($Catequista['Sacramento']);

require_once '../Controlador/controladorCelAdmin.php';
$controller = new ControladorCelAdm();
$familiares = $controller->verFamiliares($idInscripcion);
$padrinosManuales = $controller->verPadrinosManuales($idInscripcion);
$documentos = $controller->verDocumentos($idInscripcion);
$datosInscripcion = $controller->verDatosInscripcion($idInscripcion);
$ciCelInscripcion = $datosInscripcion['CiCel'] ?? '';
$sacramentoInscripcion = $datosInscripcion['Sacramento'] ?? '';
$fechaNacimientoInscripcion = $datosInscripcion['FechaNac'] ?? '';
$bautizoLocal = $ciCelInscripcion !== '' ? $controller->verBautizoLocal($ciCelInscripcion) : null;

$detalleDocDefault = $bautizoLocal ? 'Bautizo' : 'Certificado de Nacimiento';
$numeroDocDefault = $bautizoLocal['NroEmision'] ?? '';
if ($numeroDocDefault === '') {
    $numeroDocDefault = $bautizoLocal['NroPart'] ?? '';
}
$libroDocDefault = $bautizoLocal['NroLibro'] ?? '';
$paginaDocDefault = $bautizoLocal['NroPag'] ?? '';
$partidaDocDefault = $bautizoLocal['NroPart'] ?? '';
$parroquiaDocDefault = $bautizoLocal['NomParroquia'] ?? '';
$fechaDocDefault = $bautizoLocal['FechaReal'] ?? '';
if ($fechaDocDefault === '') {
    foreach ($documentos as $documento) {
        if ($documento['DetalleDoc'] === 'Bautizo' && !empty($documento['FechaDoc'])) {
            $fechaDocDefault = $documento['FechaDoc'];
            break;
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['Registrar_Doc'])) {
    $gestion = $_POST['gestion'];
    $ciCat = $_POST['ciCat'];
    $idGrupo = $_POST['idGrupo'];
    $idInscripcion = $_POST['idInscripcion'];
    $numeroDoc = $_POST['numeroDoc'];
    $detalleDoc = $_POST['detalleDoc'];
    $libroDoc = $_POST['libroDoc'];
    $paginaDoc = $_POST['paginaDoc'];
    $partidaDoc = $_POST['partidaDoc'];
    $parroquiaDoc = $_POST['parroquiaDoc'];
    $fechaDoc = $_POST['fechaDoc'] ?? '';

    if ($controller->registrarDocumento($gestion, $ciCat, $idGrupo, $idInscripcion, $numeroDoc, $detalleDoc, $libroDoc, $paginaDoc, $partidaDoc, $parroquiaDoc, $fechaDoc)) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['Eliminar_Doc'])) {
    $gestion = $_POST['gestion'];
    $ciCat = $_POST['ciCat'];
    $idGrupo = $_POST['idGrupo'];
    $idInscripcion = $_POST['idInscripcion'];

    if ($controller->eliminarDocumento($gestion, $ciCat, $idGrupo, $idInscripcion)) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['Actualizar_Docs_Presentados'])) {
    $presentados = $_POST['documento_presentado'] ?? [];
    if (!is_array($presentados)) {
        $presentados = [];
    }
    $fechaBautizo = $_POST['fechaBautizo'] ?? '';

    $controller->actualizarDocumentosPresentados(
        $Catequista['Gestion'],
        $Catequista['CiCat'],
        $Catequista['IdGrupo'],
        $idInscripcion,
        $presentados,
        $fechaBautizo
    );

    header("Location: VistaCelApPad.php?accins=" . $idInscripcion);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['Registrar_Fam'])) {

    $ciFamiliar = $_POST['ciFamiliar'];
    $idInscripcion = $_POST['idInscripcion'];
    $rolFam = $_POST['rolFam'];
    $parentesco = $_POST['parentesco'];

    if ($controller->registrarFamiliar($ciFamiliar, $idInscripcion, $rolFam, $parentesco)) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['Eliminar_Fam'])) {

    $ciFamiliar = $_POST['ciFamiliar'];
    $idInscripcion = $_POST['idInscripcion'];

    if ($controller->eliminarFamiliar($ciFamiliar, $idInscripcion)) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['Registrar_Padrino_Manual'])) {

    $idInscripcion = (int) ($_POST['idInscripcion'] ?? 0);
    $rolFam = $_POST['rolFamManual'] ?? 'Padrino';
    $nombre = trim($_POST['nombrePadrino'] ?? '');
    $apellido = trim($_POST['apellidoPadrino'] ?? '');
    $estadoCivil = trim($_POST['estadoCivilPadrino'] ?? '');

    if (!in_array($rolFam, ['Padrino', 'Madrina'], true)) {
        $rolFam = 'Padrino';
    }
    if (!in_array($estadoCivil, ['Soltero/a', 'Casado/a por la Iglesia', 'Viudo/a'], true)) {
        $estadoCivil = 'Soltero/a';
    }

    if ($idInscripcion > 0 && $nombre !== '' && $apellido !== '') {
        $controller->registrarPadrinoManual($idInscripcion, $rolFam, $nombre, $apellido, $estadoCivil);
    }

    header("Location: VistaCelApPad.php?accins=" . $idInscripcion);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['Eliminar_Padrino_Manual'])) {

    $idPadrino = (int) ($_POST['idPadrino'] ?? 0);
    $idInscripcion = (int) ($_POST['idInscripcion'] ?? 0);

    if ($idPadrino > 0 && $idInscripcion > 0) {
        $controller->eliminarPadrinoManual($idPadrino, $idInscripcion);
    }

    header("Location: VistaCelApPad.php?accins=" . $idInscripcion);
    exit();
}



if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['ciPersona'])) {
    $ciPersona = $_GET['ciPersona'];
    $controller = new ControladorPersona();
    $persona = $controller->buscarPersona($ciPersona);

    if ($persona) {
        echo json_encode($persona);
    } else {
        echo json_encode(['error' => 'Persona no encontrada']);
    }
    exit();
}


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

<body class="sidebar-start-enabled">
    <header class="navbar-light fixed-top header-static bg-mode">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="VistaCatequista.php">
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
                                    <img class="light-mode-item navbar-brand-item" src="../assets/images/logo.png?v=<?= filemtime(__DIR__ . '/../assets/images/logo.png') ?>" alt="logo">
                                    <img class="dark-mode-item navbar-brand-item" src="../assets/images/logo-light.png?v=<?= filemtime(__DIR__ . '/../assets/images/logo-light.png') ?>" alt="logo">
                                </a>
                                <button type="button" class="btn-close text-reset ms-auto" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                            </div>

                            <div class="offcanvas-body d-block px-2 px-lg-0">
                                <div class="card overflow-hidden">
                                    <div class="h-50px" style="background-image:url(<?php echo ($Catequista['FondoCat'] == 'Ninguno' || empty($Catequista['FondoCat'])) ? '../assets/images/fondo/Ninguno.jpg' : "../assets/images/fondo/{$Catequista['FondoCat']}?v=" . time(); ?>); background-position: center; background-size: cover; background-repeat: no-repeat;">
                                    </div>
                                    <div class="card-body pt-0">
                                        <div class="text-center">
                                            <div class="avatar avatar-lg mt-n5 mb-3">
                                                <img class="avatar-img rounded border border-white border-3" src="<?php echo ($Catequista['ImagenCat'] == 'Ninguno' || empty($Catequista['ImagenCat'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$Catequista['ImagenCat']}?v=" . time(); ?>" alt="">
                                            </div>
                                            <h5 class="mb-0"> <a href="PerfilCatequista.php"><?php echo $Catequista['Nombre'] . " " . $Catequista['ApPaterno'] . " " . $Catequista['ApMaterno']; ?></a> </h5>

                                            <small><?php echo $Catequista['RolCat']; ?></small>
                                            <p class="mt-3"><?php echo $Catequista['FraseCat']; ?></p>
                                            <hr>
                                            <div class="hstack gap-2 gap-xl-3 justify-content-center">
                                                <div>
                                                    <h6 class="mb-0"><?php echo $Catequista['NombreGrupo']; ?></h6>
                                                    <small>Grupo</small>
                                                </div>
                                                <div class="vr"></div>
                                                <div>
                                                    <h6 class="mb-0">
                                                        <?php
                                                        $ahora = new DateTime();
                                                        $fechaInicio = new DateTime($Catequista['FechaRegCat']);
                                                        $diferencia = $ahora->diff($fechaInicio);
                                                        echo $diferencia->format("%y");
                                                        ?> años
                                                    </h6>
                                                    <small>Trayectoria</small>
                                                </div>
                                                <div class="vr"></div>
                                                <div>
                                                    <h6 class="mb-0">0</h6>
                                                    <small><i class="bi bi-star-fill text-danger pe-1"></i></small>
                                                </div>
                                            </div>


                                        </div>

                                        <hr>

                                        <ul class="nav nav-link-secondary flex-column fw-bold gap-2">
                                            <li class="nav-item">
                                                <a class="nav-link" href="VistaCatequista.php"> <img class="me-2 h-20px fa-fw" src="../assets/images/icon/mensaje.png" alt=""><span>Inicio </span></a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" href="VistaPersona.php"> <img class="me-2 h-20px fa-fw" src="../assets/images/icon/nuevo.png" alt=""><span>Persona </span></a>
                                            </li>
                                            <?php if ($Catequista['RolCat'] == "Titular") { ?>
                                                <li class="nav-item">
                                                    <a class="nav-link" href="VistaReuniones.php"> <img class="me-2 h-20px fa-fw" src="../assets/images/icon/instrucciones.png" alt=""><span>Reuniones / Actividades</span></a>
                                                </li>
                                            <?php } ?>
                                            <li class="nav-item">
                                                <a class="nav-link" href="GrupoEsp.php?id=<?php echo $Catequista['IdGrupo']; ?>"> <img class="me-2 h-20px fa-fw" src="../assets/images/icon/contrato.png" alt=""><span>Grupo </span></a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" href="VistaCelebranteAsis.php"> <img class="me-2 h-20px fa-fw" src="../assets/images/icon/ListaCelebrante.png" alt=""><span>Asistencia a Catquesis </span></a>
                                            </li>
                                            <?php if ($Catequista['PoderCat'] == "Habilitado" || !empty($Catequista['PuedeRegistrarCatequizandos'])) { ?>
                                                <li class="nav-item">
                                                    <a class="nav-link" href="VistaDocCel.php"> <img class="me-2 h-20px fa-fw" src="../assets/images/icon/Documentos.png" alt=""><span>Registros</span></a>
                                                </li>
                                            <?php } ?>
                                            <?php if ($Catequista['RolCat'] == "Titular") { ?>
                                                <li class="nav-item">
                                                    <a class="nav-link" href="VistaCelExamen.php"> <img class="me-2 h-20px fa-fw" src="../assets/images/icon/Examen.png" alt=""><span>Pruebas</span></a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" href="VistaReporteGrupo.php"> <img class="me-2 h-20px fa-fw" src="../assets/images/icon/reporte.png" alt=""><span>Reporte General</span></a>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                    <div class="card-footer text-center py-2">
                                        <a class="btn btn-link btn-sm" href="PerfilCatequista.php">Ver mi Perfil</a>
                                    </div>
                                </div>
                                <p class="small text-center mt-1">©2026 <a class="text-reset" target="_blank" href="https://sacaba.gob.bo/index.php/inicio/cultura/arte-y-arquitectura"> San Pedro de Sacaba </a></p>
                            </div>
                        </div>
                    </nav>
                </div>

                <div class="col-md-8 col-lg-6 vstack gap-4">

                    <div class="card h-100">
                        <section class="py-2">
                            <div class="container">
                                <div class="card-header d-sm-flex align-items-center text-center justify-content-sm-between border-0 pb-0">
                                    <h1 class="h2 card-title">Resgistros realizados</h1>
                                    <div class="mb-3 d-flex align-items-end">
                                        <button type="button" class="btn btn-primary-soft me-2" data-bs-toggle="modal" data-bs-target="#feedActionFamiliar"><i class="bi bi-person-add"></i></button>
                                        <button type="button" class="btn btn-primary-soft me-2" data-bs-toggle="modal" data-bs-target="#feedActionPadrinoManual" title="Registrar padrino o madrina solo con nombre y apellido"><i class="bi bi-person-vcard"></i></button>
                                        <button type="button" class="btn btn-primary-soft me-2" data-bs-toggle="modal" data-bs-target="#feedActionDocumento"><i class="bi bi-file-earmark-text"></i></button>
                                    </div>
                                </div>
                            </div>
                        </section>
                        <section class="py-2 py-sm-2">
                            <div class="container">
                                <div class="row g-2 g-lg-5 align-items-center">

                                    <div class="col-md-12">
                                        <div class="card card-body bg-mode shadow border-0  p-lg-3">

                                            <h4>Apoderado / Padrinos</h4>
                                            <?php
                                            $tienePadrinos = !empty($padrinosManuales);
                                            foreach ($familiares as $familiarPadrino) {
                                                if (in_array($familiarPadrino['RolFam'] ?? '', ['Padrino', 'Madrina'], true)) {
                                                    $tienePadrinos = true;
                                                    break;
                                                }
                                            }
                                            ?>
                                            <?php if ($sacramentoInscripcion === 'Confirmación' && !$tienePadrinos): ?>
                                                <div class="alert alert-warning py-2 mb-2">En Confirmación debe registrar al menos un padrino o madrina.</div>
                                            <?php endif; ?>
                                            <table class="table">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th class="d-none d-md-table-cell">Nombre Completo</th>
                                                        <th>Rol</th>
                                                        <th class="d-none d-md-table-cell">Parentesco / Estado civil</th>
                                                        <th>Contacto</th>
                                                        <th>-</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($familiares as $familiar): ?>
                                                        <tr>
                                                            <td class="d-none d-md-table-cell"><?php echo $familiar['NombreCompleto']; ?></td>
                                                            <td><?php echo $familiar['RolFam']; ?></td>
                                                            <td class="d-none d-md-table-cell"><?php echo $familiar['Parentesco']; ?></td>
                                                            <td><?php echo $familiar['Contacto']; ?></td>
                                                            <td>
                                                                <form action="VistaCelApPad.php" method="POST">
                                                                    <input type="hidden" name="ciFamiliar" value="<?php echo $familiar['CiFamiliar']; ?>">
                                                                    <input type="hidden" name="idInscripcion" value="<?php echo $idInscripcion; ?>">
                                                                    <button type="submit" name="Eliminar_Fam" class="btn btn-danger-soft"><i class="bi bi-trash"></i></button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                    <?php foreach ($padrinosManuales as $padrinoManual): ?>
                                                        <tr>
                                                            <td class="d-none d-md-table-cell">
                                                                <?php echo htmlspecialchars($padrinoManual['Nombre'] . ' ' . $padrinoManual['Apellido']); ?>
                                                                <span class="badge bg-secondary">Manual</span>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($padrinoManual['RolFam']); ?></td>
                                                            <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($padrinoManual['EstadoCivil'] ?: '—'); ?></td>
                                                            <td>-</td>
                                                            <td>
                                                                <form action="VistaCelApPad.php" method="POST">
                                                                    <input type="hidden" name="idPadrino" value="<?php echo (int) $padrinoManual['IdPadrino']; ?>">
                                                                    <input type="hidden" name="idInscripcion" value="<?php echo (int) $idInscripcion; ?>">
                                                                    <button type="submit" name="Eliminar_Padrino_Manual" class="btn btn-danger-soft"><i class="bi bi-trash"></i></button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                    <?php if (empty($familiares) && empty($padrinosManuales)): ?>
                                                        <tr>
                                                            <td colspan="5" class="text-center text-muted">No se han registrado apoderados ni padrinos.</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="card card-body bg-mode shadow border-0 ">

                                            <h4>Documentos Registrados</h4>
                                            <?php
                                            $documentosPresentados = array_column($documentos, 'DetalleDoc');
                                            $documentosRequeridos = ['Certificado de Nacimiento', 'Bautizo'];
                                            if ($sacramentoInscripcion === 'Confirmación') {
                                                $documentosRequeridos[] = 'Recuerdo de Primera Comunión';
                                                $documentosRequeridos[] = 'Certificado de Confirmación (Padrino soltero)';
                                                $documentosRequeridos[] = 'Certificado de Matrimonio Religioso (Casado)';
                                                $documentosRequeridos[] = 'Certificado de Defunción o CI de Viudo (Padrino)';
                                            }
                                            ?>
                                            <form method="POST" action="VistaCelApPad.php?accins=<?php echo $idInscripcion; ?>" class="mb-3">
                                                <p class="text-muted mb-2">Marca los documentos que presentó:</p>
                                                <?php foreach ($documentosRequeridos as $indice => $requerido): ?>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="documento_presentado[]" value="<?php echo htmlspecialchars($requerido, ENT_QUOTES, 'UTF-8'); ?>" id="doc_presentado_<?php echo $indice; ?>" <?php echo in_array($requerido, $documentosPresentados, true) ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="doc_presentado_<?php echo $indice; ?>"><?php echo htmlspecialchars($requerido, ENT_QUOTES, 'UTF-8'); ?></label>
                                                    </div>
                                                <?php endforeach; ?>
                                                <div class="form-group mt-2">
                                                    <label for="fechaBautizoChecklist" class="form-label small">Fecha de bautizo (según el certificado)</label>
                                                    <input type="date" class="form-control form-control-sm" name="fechaBautizo" id="fechaBautizoChecklist" value="<?php echo htmlspecialchars($fechaDocDefault, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <small class="text-muted d-block">No es la fecha de nacimiento.</small>
                                                </div>
                                                <button type="submit" name="Actualizar_Docs_Presentados" class="btn btn-sm btn-primary-soft mt-2">Guardar documentos presentados</button>
                                            </form>
                                            <hr>
                                            <table class="table">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>Documento</th>
                                                        <th class="d-none d-md-table-cell">Detalle</th>
                                                        <th class="d-none d-md-table-cell">Parroquia</th>
                                                        <th>Fecha de nacimiento</th>
                                                        <th>Fecha del documento</th>
                                                        <th>Fecha de entrega</th>
                                                        <th>-</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($documentos as $indiceDocumento => $documento): ?>
                                                        <tr>
                                                            <td><?php echo $documento['NumeroDoc']; ?></td>
                                                            <td class="d-none d-md-table-cell"><?php echo $documento['DetalleDoc']; ?></td>
                                                            <td class="d-none d-md-table-cell"><?php echo $documento['ParroquiaDoc']; ?></td>
                                                            <td>
                                                                <?php echo $indiceDocumento === 0 && !empty($fechaNacimientoInscripcion) ? date('d/m/Y', strtotime($fechaNacimientoInscripcion)) : ''; ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                echo $documento['DetalleDoc'] !== 'Certificado de Nacimiento' && !empty($documento['FechaDoc']) ? date('d/m/Y', strtotime($documento['FechaDoc'])) : '';
                                                                ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                echo !empty($documento['FechaPres']) ? date('d/m/Y', strtotime($documento['FechaPres'])) : '-';
                                                                ?>
                                                            </td>
                                                            <td>
                                                                <form action="VistaCelApPad.php" method="POST">
                                                                    <input type="hidden" name="gestion" value="<?php echo $documento['Gestion']; ?>">
                                                                    <input type="hidden" name="ciCat" value="<?php echo $documento['CiCat']; ?>">
                                                                    <input type="hidden" name="idGrupo" value="<?php echo $documento['IdGrupo']; ?>">
                                                                    <input type="hidden" name="idInscripcion" value="<?php echo $idInscripcion; ?>">
                                                                    <button type="submit" name="Eliminar_Doc" class="btn btn-danger-soft"><i class="bi bi-trash"></i></button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                            <p class="text-muted small mb-0">La fecha de entrega es cuando se presentó el documento, no la fecha de nacimiento.</p>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>


                        <div class="align-items-center p-3 col-lg-12">
                            <a href="VistaDocCel.php" class="btn btn-primary-soft w-100">Volver</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="feedActionDocumento" tabindex="-1" aria-labelledby="feedActionDocumento" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Documento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="VistaCelApPad.php" method="POST" class="row g-3 p-3" enctype="multipart/form-data">

                    <input type="hidden" name="gestion" value="<?php echo $Catequista['Gestion']; ?>">
                    <input type="hidden" name="ciCat" value="<?php echo $Catequista['CiCat']; ?>">
                    <input type="hidden" name="idGrupo" value="<?php echo $Catequista['IdGrupo']; ?>">
                    <div class="col-sm-6 col-lg-4 form-group">
                        <label for="numeroDoc">Número del Documento</label>
                        <input type="text" class="form-control" name="numeroDoc" placeholder="Codigo Doc" value="<?php echo htmlspecialchars($numeroDocDefault, ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>

                    <div class="col-sm-6 col-lg-4 form-group">
                        <label for="detalleDoc">Detalle del Documento</label>
                        <select name="detalleDoc" class="form-control" id="detalleDoc">
                            <option value="Certificado de Nacimiento" <?php if ($detalleDocDefault === 'Certificado de Nacimiento') echo 'selected'; ?>>Certificado de Nacimiento</option>
                            <option value="Bautizo" <?php if ($detalleDocDefault === 'Bautizo') echo 'selected'; ?>>Bautizo</option>
                            <?php if ($sacramentoInscripcion === 'Confirmación'): ?>
                                <option value="Recuerdo de Primera Comunión" <?php if ($detalleDocDefault === 'Recuerdo de Primera Comunión') echo 'selected'; ?>>Recuerdo de Primera Comunión</option>
                            <?php endif; ?>
                        </select>
                        <?php if ($bautizoLocal): ?>
                            <small class="text-success d-block">Bautizado en <?php echo htmlspecialchars($bautizoLocal['NomParroquia'], ENT_QUOTES, 'UTF-8'); ?><?php if (!empty($bautizoLocal['FechaReal'])): ?> el <?php echo date('d/m/Y', strtotime($bautizoLocal['FechaReal'])); ?><?php endif; ?>. Datos cargados del registro parroquial.</small>
                        <?php elseif ($sacramentoInscripcion === 'Confirmación'): ?>
                            <small class="text-muted">Fotocopia del Certificado de Nacimiento, Bautizo y Recuerdo de Primera Comunión.</small>
                        <?php else: ?>
                            <small class="text-muted">Fotocopia del Certificado de Nacimiento y del Bautizo. Si aún no está bautizado, registre solo el Certificado de Nacimiento.</small>
                        <?php endif; ?>
                    </div>

                    <div class="col-sm-6 col-lg-4 form-group">
                        <label for="libroDoc">Libro del Documento</label>
                        <input type="text" class="form-control" name="libroDoc" placeholder="Codigo Libro" value="<?php echo htmlspecialchars($libroDocDefault, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div class="col-sm-6 col-lg-4 form-group">
                        <label for="paginaDoc">Página del Documento</label>
                        <input type="text" class="form-control" name="paginaDoc" placeholder="Codigo Pag" value="<?php echo htmlspecialchars($paginaDocDefault, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div class="col-sm-6 col-lg-4 form-group">
                        <label for="partidaDoc">Partida del Documento</label>
                        <input type="text" class="form-control" name="partidaDoc" placeholder="Codigo Part" value="<?php echo htmlspecialchars($partidaDocDefault, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div class="col-sm-6 col-lg-4 form-group">
                        <label for="parroquiaDoc">Lugar de bautizo</label>
                        <input type="text" class="form-control" name="parroquiaDoc" placeholder="Parroquia o lugar" value="<?php echo htmlspecialchars($parroquiaDocDefault, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div class="col-sm-6 col-lg-4 form-group">
                        <label for="fechaDoc">Fecha de bautizo (según el certificado)</label>
                        <input type="date" class="form-control" name="fechaDoc" id="fechaDoc" value="<?php echo htmlspecialchars($fechaDocDefault, ENT_QUOTES, 'UTF-8'); ?>">
                        <small class="text-muted d-block">No es la fecha de nacimiento.</small>
                    </div>

                    <input type="hidden" name="idInscripcion" value="<?php echo $idInscripcion; ?>">
                    <button type="submit" class="btn btn-success-soft me-2" name="Registrar_Doc">Registrar</button>
                </form>
            </div>
        </div>
    </div>



    <div class="modal fade" id="feedActionFamiliar" tabindex="-1" aria-labelledby="feedActionFamiliar" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="feedActionPhotoLabel">Registrar Nueva Familiar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="VistaCelApPad.php" method="POST" class="row g-3 p-3" enctype="multipart/form-data">
                    <div class="col-sm-6 col-lg-4 form-group">
                        <label for="ciFamiliar">CI del Familiar</label>
                        <input type="text" class="form-control" id="CFam" name="ciFamiliar" placeholder="Identificación" required>
                        <button type="button" class="btn btn-sm btn-dashed rounded mt-0" onclick="abrirModalPersonaFamiliar()"> <i class="bi bi-plus-circle-dotted me-1"></i>Nuevo</button>
                        <span class="familiarnew text-muted ms-2"></span>
                    </div>

                    <div class="col-sm-6 col-lg-4 form-group">
                        <label for="rolFam">Función</label>
                        <select name="rolFam" class="form-control">
                            <option value="Apoderado">Apoderado</option>
                            <option value="Padrino">Padrino</option>
                            <option value="Madrina">Madrina</option>
                        </select>
                    </div>

                    <div class="col-sm-6 col-lg-4 form-group">
                        <label for="parentesco">Parentesco</label>
                        <select name="parentesco" class="form-control">
                            <option value="Papá">Papá</option>
                            <option value="Mamá">Mamá</option>
                            <option value="Hermano">Hermano</option>
                            <option value="Hermana">Hermana</option>
                            <option value="Tio">Tio</option>
                            <option value="Tia">Tia</option>
                            <option value="Abuelito">Abuelito</option>
                            <option value="Abuelita">Abuelita</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <input type="hidden" name="idInscripcion" value="<?php echo $idInscripcion; ?>">
                    <button type="submit" class="btn btn-success-soft me-2" name="Registrar_Fam">Registrar</button>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="feedActionPadrinoManual" tabindex="-1" aria-labelledby="feedActionPadrinoManualLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="feedActionPadrinoManualLabel">Registrar Padrino o Madrina (solo nombre)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="VistaCelApPad.php" method="POST" class="row g-3 p-3">
                    <div class="col-sm-6 form-group">
                        <label for="rolFamManual">Función</label>
                        <select name="rolFamManual" id="rolFamManual" class="form-control" required>
                            <option value="Padrino">Padrino</option>
                            <option value="Madrina">Madrina</option>
                        </select>
                    </div>

                    <div class="col-sm-6 form-group">
                        <label for="estadoCivilPadrino">Estado civil</label>
                        <select name="estadoCivilPadrino" id="estadoCivilPadrino" class="form-control">
                            <option value="Soltero/a">Soltero/a</option>
                            <option value="Casado/a por la Iglesia">Casado/a por la Iglesia</option>
                            <option value="Viudo/a">Viudo/a</option>
                        </select>
                    </div>

                    <div class="col-sm-6 form-group">
                        <label for="nombrePadrino">Nombre</label>
                        <input type="text" class="form-control" id="nombrePadrino" name="nombrePadrino" maxlength="60" required>
                    </div>

                    <div class="col-sm-6 form-group">
                        <label for="apellidoPadrino">Apellido</label>
                        <input type="text" class="form-control" id="apellidoPadrino" name="apellidoPadrino" maxlength="60" required>
                    </div>

                    <div class="col-12">
                        <p class="text-muted small mb-0">Se guarda el nombre tal como se escriba, sin necesidad de registrar a la persona ni su CI.</p>
                    </div>

                    <input type="hidden" name="idInscripcion" value="<?php echo $idInscripcion; ?>">
                    <button type="submit" class="btn btn-success-soft me-2" name="Registrar_Padrino_Manual">Registrar</button>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalPersonaFamiliar" tabindex="-1" aria-labelledby="modalPersonaFamiliarLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form id="form-persona-familiar">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalPersonaFamiliarLabel">Registrar Persona</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body row g-3">
                        <div class="form-group col-sm-4 col-lg-2">
                            <label for="familiaCi">CI Persona</label>
                            <input type="text" class="form-control" id="familiaCi" placeholder="Identificación" required>
                        </div>
                        <div class="form-group col-sm-4 col-lg-3">
                            <label for="familiaNombre">Nombre</label>
                            <input type="text" class="form-control" id="familiaNombre" placeholder="Nombres" required>
                        </div>
                        <div class="form-group col-sm-4 col-lg-2">
                            <label for="familiaApPaterno">Apellido Paterno</label>
                            <input type="text" class="form-control" id="familiaApPaterno" placeholder="Apellido Paterno" required>
                        </div>
                        <div class="form-group col-sm-4 col-lg-2">
                            <label for="familiaApMaterno">Apellido Materno</label>
                            <input type="text" class="form-control" id="familiaApMaterno" placeholder="Apellido Materno" required>
                        </div>
                        <div class="form-group col-sm-4 col-lg-2">
                            <label class="form-label">Sexo:</label>
                            <div>
                                <input type="radio" id="familiaVaron" name="familiaSexo" value="Varon" checked>
                                <label for="familiaVaron">Varon</label>
                                &nbsp;&nbsp;&nbsp;
                                <input type="radio" id="familiaMujer" name="familiaSexo" value="Mujer">
                                <label for="familiaMujer">Mujer</label>
                            </div>
                        </div>
                        <div class="form-group col-sm-4 col-lg-3">
                            <label for="familiaFechaNac">Fecha de Nacimiento</label>
                            <input type="date" class="form-control" id="familiaFechaNac" required>
                        </div>
                        <div class="form-group col-sm-4 col-lg-3">
                            <label for="familiaDireccion">Dirección</label>
                            <input type="text" class="form-control" id="familiaDireccion" placeholder="Ingrese su Dirección" required>
                        </div>
                        <div class="form-group col-sm-4 col-lg-3">
                            <label for="familiaContacto">Contacto</label>
                            <input type="text" class="form-control" id="familiaContacto" placeholder="Número de Contacto" required>
                        </div>
                        <div class="form-group col-sm-4 col-lg-3">
                            <label for="familiaEstadoPer">Estado</label>
                            <select id="familiaEstadoPer" class="form-control" required>
                                <option value="Soltero">Soltero</option>
                                <option value="Soltera">Soltera</option>
                                <option value="Casado">Casado</option>
                                <option value="Casada">Casada</option>
                                <option value="Viudo">Viudo</option>
                                <option value="Viuda">Viuda</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" onclick="registrarPersonaFamiliar()">Registrar Persona</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function mostrarAlertaFamiliar(mensaje, tipo) {
            const alerta = document.createElement('div');
            alerta.className = `alert alert-${tipo} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
            alerta.style.zIndex = '99999';
            alerta.style.minWidth = '400px';
            alerta.innerHTML = `${mensaje}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
            document.body.appendChild(alerta);
            setTimeout(() => { alerta.remove(); }, 4000);
        }

        function abrirModalPersonaFamiliar() {
            const form = document.getElementById('form-persona-familiar');
            if (form) form.reset();
            $('#feedActionFamiliar').one('hidden.bs.modal', function() {
                $('#modalPersonaFamiliar').modal('show');
            });
            $('#feedActionFamiliar').modal('hide');
        }

        function registrarPersonaFamiliar() {
            const dataPersona = {
                ciPersona: $('#familiaCi').val(),
                nombre: $('#familiaNombre').val(),
                apPaterno: $('#familiaApPaterno').val(),
                apMaterno: $('#familiaApMaterno').val(),
                sexo: $('input[name="familiaSexo"]:checked').val(),
                fechaNac: $('#familiaFechaNac').val(),
                direccion: $('#familiaDireccion').val(),
                contacto: $('#familiaContacto').val(),
                estadoPer: $('#familiaEstadoPer').val()
            };

            if (!dataPersona.ciPersona || !dataPersona.nombre || !dataPersona.apPaterno || !dataPersona.fechaNac || !dataPersona.direccion || !dataPersona.contacto) {
                mostrarAlertaFamiliar('Completa los datos obligatorios de la persona.', 'danger');
                return;
            }

            $.post('../src/registrar_persona.php', dataPersona, function(response) {
                if (response.success) {
                    const ciPersona = response.ciPersona || dataPersona.ciPersona;
                    const nombreCompleto = [dataPersona.nombre, dataPersona.apPaterno, dataPersona.apMaterno].filter(Boolean).join(' ');
                    $('#CFam').val(ciPersona);
                    $('.familiarnew').text(nombreCompleto);
                    $('#modalPersonaFamiliar').modal('hide');
                    mostrarAlertaFamiliar('Persona registrada correctamente.', 'success');
                } else {
                    mostrarAlertaFamiliar('Error al registrar: ' + response.message, 'danger');
                }
            }, 'json').fail(function(xhr) {
                const mensaje = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'No se pudo registrar la persona.';
                mostrarAlertaFamiliar('Error al registrar: ' + mensaje, 'danger');
            });
        }

        $(document).ready(function() {
            const modalPersonaFamiliar = document.getElementById('modalPersonaFamiliar');
            if (modalPersonaFamiliar) {
                modalPersonaFamiliar.addEventListener('hidden.bs.modal', function() {
                    const familiar = document.getElementById('feedActionFamiliar');
                    if (familiar && !familiar.classList.contains('show')) {
                        $('#feedActionFamiliar').modal('show');
                    }
                });
            }

            $('input[id="CFam"]').on('input', function() {
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
                                $('.familiarnew').text('');
                            } else {
                                $('.familiarnew').text(`${persona.Nombre} ${persona.ApPaterno}`);
                            }
                        }
                    });
                }
            });
        });


        $(document).ready(function() {
            function obtenerEstado() {
                $.ajax({
                    url: '../VerificarCatequista.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.EstadoCat && response.RolCat) {
                            $('#estado').text(response.EstadoCat);
                            $('#cargo').text(response.RolCat);
                            if (response.RolCat === "Coordinador") {
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