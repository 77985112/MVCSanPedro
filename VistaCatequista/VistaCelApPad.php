<?php
require_once '../Controlador/ControladorPersona.php';
session_start();

if (!isset($_SESSION['CiPersona'])) {
    header("Location: ../logout.php");
    exit();
}

$Catequista = $_SESSION['CiPersona'];
if (isset($_SESSION['ACCIns'])) {
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
$documentos = $controller->verDocumentos($idInscripcion);


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

    if ($controller->registrarDocumento($gestion, $ciCat, $idGrupo, $idInscripcion, $numeroDoc, $detalleDoc, $libroDoc, $paginaDoc, $partidaDoc, $parroquiaDoc)) {
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

<body class="sidebar-start-enabled">
    <header class="navbar-light fixed-top header-static bg-mode">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="VistaCatequista.php">
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
                                    <div class="h-50px" style="background-image:url(<?php echo ($Catequista['FondoCat'] == 'Ninguno' || empty($Catequista['FondoCat'])) ? '../assets/images/fondo/Ninguno.jpg' : "../assets/images/fondo/{$Catequista['FondoCat']}"; ?>); background-position: center; background-size: cover; background-repeat: no-repeat;">
                                    </div>
                                    <div class="card-body pt-0">
                                        <div class="text-center">
                                            <div class="avatar avatar-lg mt-n5 mb-3">
                                                <img class="avatar-img rounded border border-white border-3" src="<?php echo ($Catequista['ImagenCat'] == 'Ninguno' || empty($Catequista['ImagenCat'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$Catequista['ImagenCat']}"; ?>" alt="">
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
                                            <?php if ($Catequista['PoderCat'] == "Habilitado") { ?>
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
                                            <table class="table">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th class="d-none d-md-table-cell">Nombre Completo</th>
                                                        <th>Rol</th>
                                                        <th class="d-none d-md-table-cell">Parentesco</th>
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
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="card card-body bg-mode shadow border-0 ">

                                            <h4>Documentos Registrados</h4>
                                            <table class="table">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>Documento</th>
                                                        <th class="d-none d-md-table-cell">Detalle</th>
                                                        <th class="d-none d-md-table-cell">Parroquia</th>
                                                        <th>Fecha</th>
                                                        <th>-</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($documentos as $documento): ?>
                                                        <tr>
                                                            <td><?php echo $documento['NumeroDoc']; ?></td>
                                                            <td class="d-none d-md-table-cell"><?php echo $documento['DetalleDoc']; ?></td>
                                                            <td class="d-none d-md-table-cell"><?php echo $documento['ParroquiaDoc']; ?></td>
                                                            <td><?php echo $documento['FechaPres']; ?></td>
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
                        <input type="text" class="form-control" name="numeroDoc" placeholder="Codigo Doc" required>
                    </div>

                    <div class="col-sm-6 col-lg-4 form-group">
                        <label for="detalleDoc">Detalle del Documento</label>
                        <select name="detalleDoc" class="form-control">
                            <option value="Confirmación">Confirmación</option>
                            <option value="Matrimonio">Matrimonio</option>
                        </select>
                    </div>

                    <div class="col-sm-6 col-lg-4 form-group">
                        <label for="libroDoc">Libro del Documento</label>
                        <input type="text" class="form-control" name="libroDoc" placeholder="Codigo Libro" required>
                    </div>

                    <div class="col-sm-6 col-lg-4 form-group">
                        <label for="paginaDoc">Página del Documento</label>
                        <input type="text" class="form-control" name="paginaDoc" placeholder="Codigo Pag" required>
                    </div>

                    <div class="col-sm-6 col-lg-4 form-group">
                        <label for="partidaDoc">Partida del Documento</label>
                        <input type="text" class="form-control" name="partidaDoc" placeholder="Codigo Part" required>
                    </div>

                    <div class="col-sm-6 col-lg-4 form-group">
                        <label for="parroquiaDoc">Parroquia del Documento</label>
                        <input type="text" class="form-control" name="parroquiaDoc" placeholder="Nombre de la parroquia" required>
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
                        <a class="familiarnew btn btn-sm btn-dashed rounded mt-0" href="VistaPersona.php"> <i class="bi bi-plus-circle-dotted me-1"></i>Nuevo</a>
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

    <script>
        $(document).ready(function() {
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
                                $('.familiarnew').html(
                                    `<i class="bi bi-plus-circle-dotted me-1""></i>Nuevo`
                                );
                            } else {
                                var form = `${persona.Nombre} ${persona.ApPaterno}`;
                                $('.familiarnew').html(form);
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