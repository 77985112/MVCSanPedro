<?php
require_once '../Controlador/ControladorPersona.php';
session_start();
if (!isset($_SESSION['CiPersona'])) {
    header("Location: ../logout.php");
    exit();
}
$Catequista = $_SESSION['CiPersona'];

require_once '../Controlador/controladorCatCoor.php';
$contCats = new CatsController();
$catsCoor = $contCats->index($Catequista['CiCat']);
$catsacramentoCoor  = $contCats->CatequsitasSac($Catequista['Sacramento']);

require_once '../Controlador/controladorCatAdmin.php';
$controller = new CatequistaController();
$CatSinG = $controller->buscarCatSinGr($Catequista['Sacramento']);


$mensaje = '';
$mensaje1 = '';
$catadm = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Buscar_Catequista'])) {
    $CiCat = $_POST['CiCatequista'];
    $SacAdm = $_POST['SacAdm'];

    $catadm = $controller->buscarCatequista($CiCat, $SacAdm);

    if (!$catadm) {
        $mensaje1 = "No se encontró catequista con los datos proporcionados.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modificar'])) {
    $CiCat = $_POST['CiCat'];
    $datosCatequista = [
        'Sacramento' => $_POST['Sacramento'],
        'EstadoCat' => $_POST['EstadoCat'],
        'ImagenCat' => $_POST['ImagenCat'],
        'FondoCat' => $_POST['FondoCat'],
        'PoderCat' => $_POST['PoderCat'],
        'FraseCat' => $_POST['FraseCat']
    ];
    $datosAsignacion = [
        'IdGrupo' => $_POST['IdGrupo'],
        'FechaIniCat' => $_POST['FechaIniCat'],
        'FechaFinCat' => $_POST['FechaFinCat'],
        'RolCat' => $_POST['RolCat']
    ];
    $controller->modificarCatequista($CiCat, $datosCatequista, $datosAsignacion);
    echo "Catequista modificado correctamente.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar'])) {
    $CiCatNuevo = $_POST['CiCatNuevo'];

    if ($controller->buscarCatequista($CiCatNuevo, $_POST['Sacramento'])) {
        $mensaje1 = "<strong>Error:</strong> El catequista con CI {$CiCatNuevo} ya existe.";
    } else {
        $datos = [
            'CiCat' => $CiCatNuevo,
            'Sacramento' => $_POST['Sacramento'],
            'UsuarioCat' => $CiCatNuevo,
            'ClaveCat' => $CiCatNuevo,
            'EstadoCat' => 'Activo',
            'ImagenCat' => 'Ninguno',
            'FondoCat' => 'Ninguno',
            'PoderCat' => 'Inabilitado',
            'FraseCat' => 'La catequesis es como la lluvia que empapa el suelo y hace crecer las semillas.',
            'Gestion' => date('Y-m-d'),
            'IdGrupo' => $_POST['IdGrupo'],
            'FechaAsigCat' => date('Y-m-d'),
            'FechaIniCat' => $_POST['FechaIniCat'],
            'FechaFinCat' => null,
            'RolCat' => 'Catequista'
        ];

        if ($controller->registrarCatequista($datos) == false) {
            $_SESSION['registroCatequista'] = [
                'Nombre' => $CiCatNuevo,
                'UsuarioCat' => $CiCatNuevo,
                'ClaveCat' => $CiCatNuevo
            ];
            $mensaje = "<strong>Catequista registrado con éxito.</strong> Nombre: {$CiCatNuevo}, Usuario: {$CiCatNuevo}, Clave: {$CiCatNuevo}";
        } else {
            $mensaje1 = "No se pudo registrar al catequista.";
        }
    }
}

$controllerPersona = new ControladorPersona();
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
            top: 0;
            background: var(--loader-color);
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 30px;
            animation: moving 5s ease-in-out infinite;
        }
    </style>
</head>

<body class="sidebar-start-enabled">
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
                                <a class="nav-link active" href="CatequistaAdm.php"> <i class="bi bi-file-person nav-icon"></i> <span class="nav-text">Catequista </span></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="VistaCeleAdmin.php"> <i class="bi bi-person-circle nav-icon"></i> <span class="nav-text">Catequisando </span></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="ActividadVista.php"> <i class="bi bi-calendar-event-fill nav-icon"></i> <span class="nav-text">Actividad</span></a>
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


                <section class="pt-0 ">
                    <div class="container">
                        <div class="card">
                            <h2 class="align-items-center text-center py-2">Catequistas (<?php echo $Catequista['Sacramento']; ?>)</h2>

                            <div class="card-header d-sm-flex align-items-center text-center justify-content-sm-between border-0 pb-0">
                                <h1 class="h4 card-title">Gestionar mis catequistas</h1>

                                <form method="POST" action="" class="mb-1 d-flex align-items-end">
                                    <input type="text" class="form-control me-2" name="CiCatequista" placeholder="Identificación de Catequista" required>
                                    <input type="hidden" name="SacAdm" value="<?php echo $Catequista['Sacramento']; ?>" required>

                                    <button type="submit" class="btn btn-primary-soft me-2" name="Buscar_Catequista" href="#tab-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Buscar"><i class="bi bi-search"></i></button>
                                    <button type="button" class="btn btn-primary-soft me-2" data-bs-toggle="modal" data-bs-target="#MostrarModal"><i class="bi bi-plus-circle"></i></button>
                                </form>
                            </div>

                            <?php if ($mensaje): ?>
                                <div class="alert alert-success" role="alert">
                                    <?php echo $mensaje; ?>
                                    <a href="tiketCat.php" target="_blank" class="btn btn-xs btn-success mt-2 mt-lg-0 ms-lg-4">Generar ticket</a>
                                </div>
                            <?php endif; ?>
                            <?php if ($mensaje1) : ?>
                                <div class="alert alert-danger" role="alert">
                                    <?php echo $mensaje1; ?>
                                </div>
                            <?php endif; ?>


                            <div class="card-body col-lg-12 p-4">
                                <hr>
                                <?php if (isset($catadm)): ?>
                                    <form method="POST" action="" class="row g-3">
                                        <input type="hidden" name="CiCat" value="<?= $catadm['CiCat'] ?>">
                                        <input type="hidden" name="ImagenCat" value="<?= $catadm['ImagenCat'] ?>">
                                        <input type="hidden" name="FondoCat" value="<?= $catadm['FondoCat'] ?>">

                                        <ul class="list-group col-lg-12 align-items-center">

                                            <li class="list-group-item d-md-flex justify-content-between align-items-center col-lg-8">
                                                <div class="me-md-3">
                                                    <h6 class="mb-0">Imagen de Perfil</h6>
                                                    <img id="imagenPreviewPerfil" class="avatar-img rounded-circle border border-white border-3" src="<?php echo ($catadm['ImagenCat'] == 'Ninguno' || empty($catadm['ImagenCat'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$catadm['ImagenCat']}"; ?>" alt="" style="width: 200px; height: 200px;">
                                                </div>
                                                <div class="me-md-3">
                                                    <h6 class="mb-0">Imagen de Fondo</h6>
                                                    <img id="imagenPreviewFondo" class="h-200px rounded" src="<?php echo ($catadm['FondoCat'] == 'Ninguno' || empty($catadm['FondoCat'])) ? '../assets/images/fondo/Ninguno.jpg' : "../assets/images/fondo/{$catadm['FondoCat']}"; ?>" alt="" style="background-size: cover; background-repeat: no-repeat; width: 450px; height: 200px;">
                                                </div>
                                            </li>
                                        </ul>

                                        <hr>
                                        <div class="col-sm-6 col-lg-3 form-group">
                                            <label class="form-label">Nombres Completo</label>
                                            <input type="text" class="form-control" id="Nombre" name="Nombre" value="<?= $catadm['NombreCompleto'] ?>" readonly>
                                        </div>

                                        <div class="col-sm-6 col-lg-3 form-group">
                                            <label class="form-label">Sacramento</label>
                                            <input type="text" class="form-control" name="Sacramento" value="<?= $catadm['Sacramento'] ?>" readonly>
                                        </div>

                                        <div class="col-sm-6 col-lg-3 form-group">
                                            <label class="form-label">Estado de Cuenta</label>
                                            <div>
                                                <input type="radio" id="EstadoCat" name="EstadoCat" value="Activo" <?php echo ($catadm['EstadoCat'] == 'Activo') ? 'checked' : ''; ?>>
                                                <label for="Activo">Activo</label>
                                                &nbsp;&nbsp;&nbsp;
                                                <input type="radio" id="EstadoCat" name="EstadoCat" value="Inactivo" <?php echo ($catadm['EstadoCat'] == 'Inactivo') ? 'checked' : ''; ?>>
                                                <label for="Inactivo">Inactivo</label>
                                            </div>
                                        </div>

                                        <div class="col-sm-6 col-lg-3 form-group">
                                            <label class="form-label">Realiza Inscripciones</label>
                                            <div>
                                                <input type="radio" id="PoderCat" name="PoderCat" value="Habilitado" <?php echo ($catadm['PoderCat'] == 'Habilitado') ? 'checked' : ''; ?>>
                                                <label for="Habilitado">Habilitado</label>
                                                &nbsp;&nbsp;&nbsp;
                                                <input type="radio" id="PoderCat" name="PoderCat" value="Inabilitado" <?php echo ($catadm['PoderCat'] == 'Inabilitado') ? 'checked' : ''; ?>>
                                                <label for="Inabilitado">Inabilitado</label>
                                            </div>
                                        </div>

                                        <div class="col-sm-6 col-lg-4 form-group me-2">
                                            <label class="form-label">Frase Registrada</label>
                                            <textarea class="form-control" id="FraseCat" name="FraseCat"><?= $catadm['FraseCat'] ?></textarea>
                                        </div>
                                        <div class="col-sm-6 col-lg-2 me-2">
                                            <label class="form-label" class="form-control">Grupo Actual</label>
                                            <?php if ($catadm['Sacramento'] == 'Primera Comunión'): ?>

                                                <select class="form-control" name="IdGrupo" data-search-enabled="true">
                                                    <option value="1" <?php if ($catadm['IdGrupo'] == "1") echo 'selected'; ?>>Sin Grupo</option>
                                                    <option value="2" <?php if ($catadm['IdGrupo'] == "2") echo 'selected'; ?>>Grupo 01</option>
                                                    <option value="3" <?php if ($catadm['IdGrupo'] == "3") echo 'selected'; ?>>Grupo 02</option>
                                                    <option value="4" <?php if ($catadm['IdGrupo'] == "4") echo 'selected'; ?>>Grupo 03</option>
                                                    <option value="5" <?php if ($catadm['IdGrupo'] == "5") echo 'selected'; ?>>Grupo 04</option>
                                                    <option value="6" <?php if ($catadm['IdGrupo'] == "6") echo 'selected'; ?>>Grupo 05</option>
                                                    <option value="7" <?php if ($catadm['IdGrupo'] == "7") echo 'selected'; ?>>Grupo 06</option>
                                                    <option value="8" <?php if ($catadm['IdGrupo'] == "8") echo 'selected'; ?>>Grupo 07</option>
                                                    <option value="9" <?php if ($catadm['IdGrupo'] == "9") echo 'selected'; ?>>Grupo 08</option>
                                                    <option value="10" <?php if ($catadm['IdGrupo'] == "10") echo 'selected'; ?>>Grupo 09</option>
                                                    <option value="11" <?php if ($catadm['IdGrupo'] == "11") echo 'selected'; ?>>Grupo 10</option>
                                                    <option value="12" <?php if ($catadm['IdGrupo'] == "12") echo 'selected'; ?>>Grupo 11</option>
                                                    <option value="13" <?php if ($catadm['IdGrupo'] == "13") echo 'selected'; ?>>Grupo 12</option>
                                                    <option value="14" <?php if ($catadm['IdGrupo'] == "14") echo 'selected'; ?>>Grupo 13</option>
                                                    <option value="15" <?php if ($catadm['IdGrupo'] == "15") echo 'selected'; ?>>Grupo 14</option>
                                                    <option value="16" <?php if ($catadm['IdGrupo'] == "16") echo 'selected'; ?>>Grupo 15</option>
                                                    <option value="17" <?php if ($catadm['IdGrupo'] == "17") echo 'selected'; ?>>Grupo 16</option>
                                                    <option value="18" <?php if ($catadm['IdGrupo'] == "18") echo 'selected'; ?>>Grupo 17</option>
                                                    <option value="19" <?php if ($catadm['IdGrupo'] == "19") echo 'selected'; ?>>Grupo 18</option>
                                                    <option value="20" <?php if ($catadm['IdGrupo'] == "20") echo 'selected'; ?>>Grupo 19</option>
                                                    <option value="21" <?php if ($catadm['IdGrupo'] == "21") echo 'selected'; ?>>Grupo 20</option>
                                                    <option value="43" <?php if ($catadm['IdGrupo'] == "43") echo 'selected'; ?>>Pendiente</option>
                                                </select>

                                            <?php elseif ($catadm['Sacramento'] == 'Confirmación'): ?>
                                                <select class="form-control" name="IdGrupo" data-search-enabled="true">
                                                    <option value="22" <?php if ($catadm['IdGrupo'] == "22") echo 'selected'; ?>>Sin Grupo</option>
                                                    <option value="23" <?php if ($catadm['IdGrupo'] == "23") echo 'selected'; ?>>Grupo 01</option>
                                                    <option value="24" <?php if ($catadm['IdGrupo'] == "24") echo 'selected'; ?>>Grupo 02</option>
                                                    <option value="25" <?php if ($catadm['IdGrupo'] == "25") echo 'selected'; ?>>Grupo 03</option>
                                                    <option value="26" <?php if ($catadm['IdGrupo'] == "26") echo 'selected'; ?>>Grupo 04</option>
                                                    <option value="27" <?php if ($catadm['IdGrupo'] == "27") echo 'selected'; ?>>Grupo 05</option>
                                                    <option value="28" <?php if ($catadm['IdGrupo'] == "28") echo 'selected'; ?>>Grupo 06</option>
                                                    <option value="29" <?php if ($catadm['IdGrupo'] == "29") echo 'selected'; ?>>Grupo 07</option>
                                                    <option value="30" <?php if ($catadm['IdGrupo'] == "30") echo 'selected'; ?>>Grupo 08</option>
                                                    <option value="31" <?php if ($catadm['IdGrupo'] == "31") echo 'selected'; ?>>Grupo 09</option>
                                                    <option value="32" <?php if ($catadm['IdGrupo'] == "32") echo 'selected'; ?>>Grupo 10</option>
                                                    <option value="33" <?php if ($catadm['IdGrupo'] == "33") echo 'selected'; ?>>Grupo 11</option>
                                                    <option value="34" <?php if ($catadm['IdGrupo'] == "34") echo 'selected'; ?>>Grupo 12</option>
                                                    <option value="35" <?php if ($catadm['IdGrupo'] == "35") echo 'selected'; ?>>Grupo 13</option>
                                                    <option value="36" <?php if ($catadm['IdGrupo'] == "36") echo 'selected'; ?>>Grupo 14</option>
                                                    <option value="37" <?php if ($catadm['IdGrupo'] == "37") echo 'selected'; ?>>Grupo 15</option>
                                                    <option value="38" <?php if ($catadm['IdGrupo'] == "38") echo 'selected'; ?>>Grupo 16</option>
                                                    <option value="39" <?php if ($catadm['IdGrupo'] == "39") echo 'selected'; ?>>Grupo 17</option>
                                                    <option value="40" <?php if ($catadm['IdGrupo'] == "40") echo 'selected'; ?>>Grupo 18</option>
                                                    <option value="41" <?php if ($catadm['IdGrupo'] == "41") echo 'selected'; ?>>Grupo 19</option>
                                                    <option value="42" <?php if ($catadm['IdGrupo'] == "42") echo 'selected'; ?>>Grupo 20</option>
                                                    <option value="43" <?php if ($catadm['IdGrupo'] == "43") echo 'selected'; ?>>Pendiente</option>
                                                </select>
                                            <?php endif; ?>
                                        </div>
                                        <input type="hidden" name="FechaIniCat" value="<?= $catadm['FechaIniCat'] ?>">

                                        <div class="col-sm-6 col-lg-2 form-group">
                                            <label class="form-label">Fecha de Finalización</label>
                                            <input type="date" class="form-control" id="FechaFinCat" name="FechaFinCat" value="<?= $catadm['FechaFinCat'] ?>">
                                        </div>


                                        <div class="col-sm-6 col-lg-3 me-2">
                                            <label class="form-label" class="form-control">Rol Asignado</label>
                                            <select class="form-control" name="RolCat" data-search-enabled="true">
                                                <option value="Catequista" <?php if ($catadm['RolCat'] == "Catequista") echo 'selected'; ?>>Catequista</option>
                                                <option value="Titular" <?php if ($catadm['RolCat'] == "Titular") echo 'selected'; ?>>Titular</option>
                                                <option value="Coordinador" <?php if ($catadm['RolCat'] == "Coordinador") echo 'selected'; ?>>Coordinador</option>
                                            </select>
                                        </div>

                                        <hr>

                                        <br>
                                        <button type="submit" name="modificar" class="btn btn-success-soft">Actualizar</button>
                                    </form>
                                <?php endif; ?>

                                <?php if (!isset($catadm)): ?>

                                    <div class="my-sm-5 py-5 text-center">
                                        <h4>Lista de catequistas sin grupo de "<?php echo $Catequista['Sacramento']; ?>"</h4>
                                        <p>Para asignar un catequista a un grupo en especifico, deberá realizarlo de manera individual</p>
                                        <?php if (empty($CatSinG)) : ?>
                                            <table class="table table-striped table-hover col-lg-12 align-items-center">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>Identificación</th>
                                                        <th>Nombre Completo</th>
                                                        <th>Rol Asignado</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($CatSinG as $ca): ?>
                                                        <?php if ($ca['RolCat'] != 'Coordinador'): ?>
                                                            <tr>
                                                                <td><?php echo htmlspecialchars($ca['CiCat']); ?></td>
                                                                <td><?php echo htmlspecialchars($ca['NombreCompleto']); ?></td>
                                                                <td><?php echo htmlspecialchars($ca['RolCat']); ?></td>
                                                            </tr>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        <?php else : ?>
                                            <i class="display-1 text-body-secondary bi bi-calendar2-event"></i>
                                            <h4 class="mt-2 mb-3 text-body">No hay catequistas</h4>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
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
                                <?php if (!empty($catsCoor)): ?>
                                    <?php foreach ($catsCoor as $catequistaCoor): ?>
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


                                            <?php if (!empty($catsacramentoCoor)): ?>
                                                <?php foreach ($catsacramentoCoor as $catSac): ?>
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

    <div class="modal fade modal-md" id="MostrarModal" tabindex="-1" aria-labelledby="MostrarModal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addModalLabel">Registrar Nuevo Catequista</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" class="row g-1">

                        <div class="form-group col-sm-1 col-lg-5">
                            <label class="form-label">CI del Catequista</label>
                            <input type="text" class="form-control" id="CiCatNuevo" name="CiCatNuevo" placeholder="CI Persona">
                            <a class="catequista btn btn-sm btn-dashed rounded mt-0" href="VistaPersona.php"> <i class="bi bi-plus-circle-dotted me-1"></i>registrar a la Persona</a>
                        </div>

                        <input type="hidden" name="Sacramento" value="<?php echo $Catequista['Sacramento']; ?>">

                        <div class="col-sm-6 col-lg-3">
                            <label class="form-label">Asignar Grupo</label>
                            <?php if ($Catequista['Sacramento'] == 'Primera Comunión'): ?>

                                <select class="form-control" name="IdGrupo" data-search-enabled="true">
                                    <option value="1">Sin Grupo</option>
                                    <option value="2">Grupo 01</option>
                                    <option value="3">Grupo 02</option>
                                    <option value="4">Grupo 03</option>
                                    <option value="5">Grupo 04</option>
                                    <option value="6">Grupo 05</option>
                                    <option value="7">Grupo 06</option>
                                    <option value="8">Grupo 07</option>
                                    <option value="9">Grupo 08</option>
                                    <option value="10">Grupo 09</option>
                                    <option value="11">Grupo 10</option>
                                    <option value="12">Grupo 11</option>
                                    <option value="13">Grupo 12</option>
                                    <option value="14">Grupo 13</option>
                                    <option value="15">Grupo 14</option>
                                    <option value="16">Grupo 15</option>
                                    <option value="17">Grupo 16</option>
                                    <option value="18">Grupo 17</option>
                                    <option value="19">Grupo 18</option>
                                    <option value="20">Grupo 19</option>
                                    <option value="21">Grupo 20</option>
                                </select>

                            <?php elseif ($catequista['Sacramento'] == 'Confirmación'): ?>
                                <select class="form-control" name="IdGrupo" data-search-enabled="true">
                                    <option value="22">Sin Grupo</option>
                                    <option value="23">Grupo 01</option>
                                    <option value="24">Grupo 02</option>
                                    <option value="25">Grupo 03</option>
                                    <option value="26">Grupo 04</option>
                                    <option value="27">Grupo 05</option>
                                    <option value="28">Grupo 06</option>
                                    <option value="29">Grupo 07</option>
                                    <option value="30">Grupo 08</option>
                                    <option value="31">Grupo 09</option>
                                    <option value="32">Grupo 10</option>
                                    <option value="33">Grupo 11</option>
                                    <option value="34">Grupo 12</option>
                                    <option value="35">Grupo 13</option>
                                    <option value="36">Grupo 14</option>
                                    <option value="37">Grupo 15</option>
                                    <option value="38">Grupo 16</option>
                                    <option value="39">Grupo 17</option>
                                    <option value="40">Grupo 18</option>
                                    <option value="41">Grupo 19</option>
                                    <option value="42">Grupo 20</option>
                                </select>

                            <?php endif; ?>
                        </div>
                        <div class="form-group col-lg-4">
                            <label for="FechaNac" class="form-label">Fecha de Inicio:</label>
                            <input type="date" class="form-control" name="FechaIniCat" required><br>
                        </div>
                        <br>
                        <button type="submit" name="registrar" class="btn btn-success-soft">Registrar Catequista</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script>
        $(document).ready(function() {
            $('input[name="CiCatNuevo"]').on('input', function() {
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
                                $('.catequista').html(
                                    `<i class="bi bi-plus-circle-dotted me-1""></i>registrar a la Persona`
                                );
                            } else {
                                var form = `${persona.Nombre} ${persona.ApPaterno}`;
                                $('.catequista').html(form);
                            }
                        }
                    });
                }
            });
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