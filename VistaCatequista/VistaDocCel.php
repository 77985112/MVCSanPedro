<?php
session_start();
if (!isset($_SESSION['CiPersona'])) {
    header("Location: ../logout.php");
    exit();
}
$Catequista = $_SESSION['CiPersona'];

require_once '../Controlador/ControladorPersona.php';
require_once '../Controlador/controladorCelAdmin.php';

$controllerCel = new CelebranteControllerAdmin();
$DetalleIns = $controllerCel->verificarIns($Catequista['Sacramento']);

$inscripcionesActivas = false;
foreach ($DetalleIns as $inscripcion) {
    if ($inscripcion['EstadoIns'] === 'Online' && $inscripcion['ActividadSac'] === 'Inscripción') {
        $inscripcionesActivas = true;
        break;
    }
}

$mensaje = '';
$mensaje1 = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['modificar'])) {
        $ciCel = $_POST['CiCel'];

        $datosCelebrante = [
            'SacramentoCel' => $_POST['SacramentoCel'],
            'EstadoCel' => $_POST['EstadoCel'],
            'PerfilCel' => $_POST['PerfilCel'],
            'FondoCel' => $_POST['FondoCel']
        ];

        $datosInscripcion = [
            'IdGrupo' => $_POST['IdGrupo'],
            'PreValor' => $_POST['PreValor'],
            'CodTipoItem' => $_POST['CodTipoItem']
        ];

        $controllerCel->modificarCelebrante($ciCel, $datosCelebrante, $datosInscripcion);
        $mensaje =  "Catequisando modificado con éxito.";
    }

    if (isset($_POST['Buscar_Celebrante'])) {
        $ciCel = $_POST['CiCel'];
        $SacramentoCel = $Catequista['Sacramento'];
        $celebranteAdmin = $controllerCel->buscarCelebrante($ciCel, $SacramentoCel);
    }
}

$controller = new ControladorPersona();
if (isset($_GET['ciPersona'])) {
    $ciPersona = $_GET['ciPersona'];
    $persona = $controller->buscarPersona($ciPersona);

    if ($persona) {
        echo json_encode($persona);
    } else {
        echo json_encode(['error' => 'Persona no encontrada']);
    }
    exit;
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ver_apoderado_padrino'])) {
    $_SESSION['ACCIns'] = $_POST['ACCIns'];
    header("Location: VistaCelApPad.php");
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
                                                    <a class="nav-link active" href="VistaDocCel.php"> <img class="me-2 h-20px fa-fw" src="../assets/images/icon/Documentos.png" alt=""><span>Registros</span></a>
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

                        <div class="card-header d-sm-flex align-items-center text-center justify-content-sm-between border-0 pb-0">
                            <h1 class="h4 card-title">Gestionar mis catequisandos</h1>

                            <form method="POST" action="" class="mb-1 d-flex align-items-end">
                                <input type="text" class="form-control me-2" name="CiCel" placeholder="Identificación de Catequisando" required>
                                <input type="hidden" name="SacAdm" value="<?php echo $Catequista['Sacramento']; ?>" required>

                                <button type="submit" class="btn btn-primary-soft me-2" name="Buscar_Celebrante" href="#tab-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Buscar"><i class="bi bi-search"></i></button>
                                <?php if ($Catequista['PoderCat'] == "Habilitado") { ?>
                                    <a href="VistaRegistroCelebrante.php" class="btn btn-primary-soft me-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Registrar Nuevos"><i class="bi bi-plus-circle"></i></a>
                                <?php } ?>
                            </form>
                        </div>



                        <div class="card-body col-lg-12 p-4">
                            <?php if (isset($celebranteAdmin)): ?>
                                <form method="post" action="">
                                    <input type="hidden" name="ACCIns" value="<?= $celebranteAdmin['IdInscripcion'] ?>">
                                    <div class="col-sm-6 col-lg-6 form-group me-2">
                                        <button type="submit" name="ver_apoderado_padrino" class="btn btn-primary-soft">Ver Apoderado y Padrinos</button>
                                    </div>
                                </form>
                                <br>
                                <div class="row g-3">
                                    <input type="hidden" name="CiCel" value="<?= $celebranteAdmin['CiCel'] ?>">
                                    <input type="hidden" name="PerfilCel" value="<?= $celebranteAdmin['PerfilCel'] ?>">
                                    <input type="hidden" name="FondoCel" value="<?= $celebranteAdmin['FondoCel'] ?>">
                                    <input type="hidden" name="CodTipoItem" value="<?= $celebranteAdmin['CodTipoItem'] ?>">
                                    <input type="hidden" name="SacramentoCel" value="<?= $celebranteAdmin['Sacramento'] ?>">

                                    <ul class="list-group col-lg-12 align-items-center">

                                        <li class="list-group-item d-md-flex justify-content-between align-items-center col-lg-12">
                                            <div class="me-md-3">
                                                <h6 class="mb-0">Imagen de Perfil</h6>
                                                <img id="imagenPreviewPerfil" class="avatar-img rounded-circle border border-white border-3" src="<?php echo ($celebranteAdmin['PerfilCel'] == 'Ninguno' || empty($celebranteAdmin['PerfilCel'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$celebranteAdmin['PerfilCel']}"; ?>" alt="" style="width: 200px; height: 200px;">
                                            </div>
                                            <div class="me-md-3">
                                                <h6 class="mb-0">Imagen de Fondo</h6>
                                                <img id="imagenPreviewFondo" class="h-200px rounded" src="<?php echo ($celebranteAdmin['FondoCel'] == 'Ninguno' || empty($celebranteAdmin['FondoCel'])) ? '../assets/images/fondo/Ninguno.jpg' : "../assets/images/fondo/{$celebranteAdmin['FondoCel']}"; ?>" alt="" style="background-size: cover; background-repeat: no-repeat; width: 450px; height: 200px;">
                                            </div>
                                        </li>
                                    </ul>

                                    <hr>
                                    <div class="col-sm-6 col-lg-3 form-group">
                                        <label class="form-label">Nombres Completo</label>
                                        <input type="text" class="form-control" id="Nombre" name="Nombre" value="<?= $celebranteAdmin['NombreCompleto'] ?>" readonly>
                                    </div>

                                    <div class="col-sm-6 col-lg-5 form-group">
                                        <label class="form-label">Sacramento que realiza el catequisando</label>
                                        <input type="text" class="form-control" name="Sacramento" value="<?= $celebranteAdmin['DescripItem'] ?>" readonly>
                                    </div>

                                    <div class="col-sm-6 col-lg-3 form-group">
                                        <label class="form-label">Estado de Cuenta</label>
                                        <div>
                                            <input type="radio" id="EstadoCel" name="EstadoCel" value="Activo" <?php echo ($celebranteAdmin['EstadoCel'] == 'Activo') ? 'checked' : ''; ?>>
                                            <label for="Activo">Activo</label>
                                            &nbsp;&nbsp;&nbsp;
                                            <input type="radio" id="EstadoCel" name="EstadoCel" value="Inactivo" <?php echo ($celebranteAdmin['EstadoCel'] == 'Inactivo') ? 'checked' : ''; ?>>
                                            <label for="Inactivo">Inactivo</label>
                                        </div>
                                    </div>

                                    <div class="col-sm-6 col-lg-3 form-group me-2">
                                        <label class="form-label">Costo de Inscripción</label>
                                        <input type="text" class="form-control" value="<?= $celebranteAdmin['Valor'] ?>" readonly></input>
                                    </div>
                                    <div class="col-sm-6 col-lg-2 form-group me-2">
                                        <label class="form-label">Monto Cancelado</label>
                                        <input type="number" class="form-control" name="PreValor" value="<?= $celebranteAdmin['PreValor'] ?>"></input>
                                    </div>

                                    <div class="col-sm-6 col-lg-3 me-2">
                                        <label class="form-label" class="form-control">Grupo Actual</label>
                                        <input type="hidden" class="form-control" name="IdGrupo" value="<?= $celebranteAdmin['IdGrupo'] ?>"></input>
                                        <?php if ($celebranteAdmin['Sacramento'] == 'Primera Comunión'): ?>
                                            <select class="form-control" data-search-enabled="true">
                                                <option value="1" <?php if ($celebranteAdmin['IdGrupo'] == "1") echo 'selected'; ?>>Sin Grupo</option>
                                                <option value="2" <?php if ($celebranteAdmin['IdGrupo'] == "2") echo 'selected'; ?>>Grupo 01</option>
                                                <option value="3" <?php if ($celebranteAdmin['IdGrupo'] == "3") echo 'selected'; ?>>Grupo 02</option>
                                                <option value="4" <?php if ($celebranteAdmin['IdGrupo'] == "4") echo 'selected'; ?>>Grupo 03</option>
                                                <option value="5" <?php if ($celebranteAdmin['IdGrupo'] == "5") echo 'selected'; ?>>Grupo 04</option>
                                                <option value="6" <?php if ($celebranteAdmin['IdGrupo'] == "6") echo 'selected'; ?>>Grupo 05</option>
                                                <option value="7" <?php if ($celebranteAdmin['IdGrupo'] == "7") echo 'selected'; ?>>Grupo 06</option>
                                                <option value="8" <?php if ($celebranteAdmin['IdGrupo'] == "8") echo 'selected'; ?>>Grupo 07</option>
                                                <option value="9" <?php if ($celebranteAdmin['IdGrupo'] == "9") echo 'selected'; ?>>Grupo 08</option>
                                                <option value="10" <?php if ($celebranteAdmin['IdGrupo'] == "10") echo 'selected'; ?>>Grupo 09</option>
                                                <option value="11" <?php if ($celebranteAdmin['IdGrupo'] == "11") echo 'selected'; ?>>Grupo 10</option>
                                                <option value="12" <?php if ($celebranteAdmin['IdGrupo'] == "12") echo 'selected'; ?>>Grupo 11</option>
                                                <option value="13" <?php if ($celebranteAdmin['IdGrupo'] == "13") echo 'selected'; ?>>Grupo 12</option>
                                                <option value="14" <?php if ($celebranteAdmin['IdGrupo'] == "14") echo 'selected'; ?>>Grupo 13</option>
                                                <option value="15" <?php if ($celebranteAdmin['IdGrupo'] == "15") echo 'selected'; ?>>Grupo 14</option>
                                                <option value="16" <?php if ($celebranteAdmin['IdGrupo'] == "16") echo 'selected'; ?>>Grupo 15</option>
                                                <option value="17" <?php if ($celebranteAdmin['IdGrupo'] == "17") echo 'selected'; ?>>Grupo 16</option>
                                                <option value="18" <?php if ($celebranteAdmin['IdGrupo'] == "18") echo 'selected'; ?>>Grupo 17</option>
                                                <option value="19" <?php if ($celebranteAdmin['IdGrupo'] == "19") echo 'selected'; ?>>Grupo 18</option>
                                                <option value="20" <?php if ($celebranteAdmin['IdGrupo'] == "20") echo 'selected'; ?>>Grupo 19</option>
                                                <option value="21" <?php if ($celebranteAdmin['IdGrupo'] == "21") echo 'selected'; ?>>Grupo 20</option>
                                                <option value="43" <?php if ($celebranteAdmin['IdGrupo'] == "43") echo 'selected'; ?>>Pendiente</option>
                                            </select>

                                        <?php elseif ($celebranteAdmin['Sacramento'] == 'Confirmación'): ?>
                                            <select class="form-control" data-search-enabled="true">
                                                <option value="22" <?php if ($celebranteAdmin['IdGrupo'] == "22") echo 'selected'; ?>>Sin Grupo</option>
                                                <option value="23" <?php if ($celebranteAdmin['IdGrupo'] == "23") echo 'selected'; ?>>Grupo 01</option>
                                                <option value="24" <?php if ($celebranteAdmin['IdGrupo'] == "24") echo 'selected'; ?>>Grupo 02</option>
                                                <option value="25" <?php if ($celebranteAdmin['IdGrupo'] == "25") echo 'selected'; ?>>Grupo 03</option>
                                                <option value="26" <?php if ($celebranteAdmin['IdGrupo'] == "26") echo 'selected'; ?>>Grupo 04</option>
                                                <option value="27" <?php if ($celebranteAdmin['IdGrupo'] == "27") echo 'selected'; ?>>Grupo 05</option>
                                                <option value="28" <?php if ($celebranteAdmin['IdGrupo'] == "28") echo 'selected'; ?>>Grupo 06</option>
                                                <option value="29" <?php if ($celebranteAdmin['IdGrupo'] == "29") echo 'selected'; ?>>Grupo 07</option>
                                                <option value="30" <?php if ($celebranteAdmin['IdGrupo'] == "30") echo 'selected'; ?>>Grupo 08</option>
                                                <option value="31" <?php if ($celebranteAdmin['IdGrupo'] == "31") echo 'selected'; ?>>Grupo 09</option>
                                                <option value="32" <?php if ($celebranteAdmin['IdGrupo'] == "32") echo 'selected'; ?>>Grupo 10</option>
                                                <option value="33" <?php if ($celebranteAdmin['IdGrupo'] == "33") echo 'selected'; ?>>Grupo 11</option>
                                                <option value="34" <?php if ($celebranteAdmin['IdGrupo'] == "34") echo 'selected'; ?>>Grupo 12</option>
                                                <option value="35" <?php if ($celebranteAdmin['IdGrupo'] == "35") echo 'selected'; ?>>Grupo 13</option>
                                                <option value="36" <?php if ($celebranteAdmin['IdGrupo'] == "36") echo 'selected'; ?>>Grupo 14</option>
                                                <option value="37" <?php if ($celebranteAdmin['IdGrupo'] == "37") echo 'selected'; ?>>Grupo 15</option>
                                                <option value="38" <?php if ($celebranteAdmin['IdGrupo'] == "38") echo 'selected'; ?>>Grupo 16</option>
                                                <option value="39" <?php if ($celebranteAdmin['IdGrupo'] == "39") echo 'selected'; ?>>Grupo 17</option>
                                                <option value="40" <?php if ($celebranteAdmin['IdGrupo'] == "40") echo 'selected'; ?>>Grupo 18</option>
                                                <option value="41" <?php if ($celebranteAdmin['IdGrupo'] == "41") echo 'selected'; ?>>Grupo 19</option>
                                                <option value="42" <?php if ($celebranteAdmin['IdGrupo'] == "42") echo 'selected'; ?>>Grupo 20</option>
                                                <option value="43" <?php if ($celebranteAdmin['IdGrupo'] == "43") echo 'selected'; ?>>Pendiente</option>
                                            </select>

                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php elseif (!isset($celebranteAdmin)): ?>
                                <div class="my-sm-5 py-sm-5 text-center">
                                    <i class="display-1 text-body-secondary bi bi-calendar2-event"></i>
                                    <h4 class="mt-2 mb-3 text-body">Catequisando no encontrado</h4>
                                </div>
                            <?php endif; ?>
                        </div>


                    </div>
                </div>
            </div>

        </div>
    </main>


    <div class="modal fade modal-lg" id="MostrarModal" tabindex="-1" aria-labelledby="MostrarModal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addModalLabel">Registrar Nuevo Catequisando</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if ($inscripcionesActivas): ?>
                        <form method="POST" class="row g-1">
                            <div class="form-group col-sm-1 col-lg-4">
                                <label class="form-label">CI del Catequisando</label>
                                <input type="text" class="form-control" id="CiCelNuevo" name="CiCelNuevo" placeholder="CI Persona">
                                <a class="celebrantenew btn btn-sm btn-dashed rounded mt-0" href="VistaPersona.php"> <i class="bi bi-plus-circle-dotted me-1"></i>registrar a la Persona</a>
                            </div>

                            <input type="hidden" name="Sacramento" value="<?php echo $catequista['Sacramento']; ?>">
                            <input type="hidden" class="form-control" name="IdGrupo" value="Pendiente">

                            <div class="form-group col-lg-5">
                                <label for="CodTipoItem" class="form-label">Sacramento</label>
                                <?php if ($Catequista['Sacramento'] == 'Primera Comunión'): ?>

                                    <select class="form-control" name="CodTipoItem" data-search-enabled="true">
                                        <option value="1">Primera Comunión</option>
                                        <option value="2">Primera Comunión - Bautizo</option>
                                    </select>

                                <?php elseif ($Catequista['Sacramento'] == 'Confirmación'): ?>
                                    <select class="form-control" name="CodTipoItem" data-search-enabled="true">
                                        <option value="3">Confirmación</option>
                                        <option value="4">Confirmación - Primera Comunión</option>
                                        <option value="5">Confirmación - Primera Comunión - Bautizo</option>
                                    </select>

                                <?php endif; ?>
                            </div>
                            <div class="form-group col-lg-3">
                                <label for="PreValor" class="form-label">Monto Pendiente</label>
                                <input type="text" class="form-control" name="PreValor" required><br>
                            </div>
                            <br>
                            <button type="submit" name="registrarCel" class="btn btn-success-soft">Registrar Catequisando</button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-warning" role="alert">
                            Las inscripciones no están activas en este momento.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $('input[name="CiCelNuevo"]').on('input', function() {
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
                                $('.celebrantenew').html(
                                    `<i class="bi bi-plus-circle-dotted me-1""></i>registrar a la Persona`
                                );
                            } else {
                                var form = `${persona.Nombre} ${persona.ApPaterno}`;
                                $('.celebrantenew').html(form);
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