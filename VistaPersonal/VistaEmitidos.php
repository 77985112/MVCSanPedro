<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../logout.php");
    exit();
}
$usuario = $_SESSION['usuario'];
require_once __DIR__ . '/../Controlador/controladorAcceso.php';
ControladorAcceso::exigir('emitidos');

require_once '../Controlador/ControladorCertificadoCal.php';
require_once '../Controlador/controladorGoogleDrive.php';

$controller = new CertificadoController();
$reservasMatrimonio = [];
$reservasBautizo = [];
$reservasMisa = [];

if (empty($_SESSION['csrf_drive'])) {
    $_SESSION['csrf_drive'] = bin2hex(random_bytes(32));
}

$drive = new ControladorGoogleDrive();
$estaConectado = $drive->estaConectado();
$archivosDrive = [];
$mensajeDrive = '';
$tipoMensajeDrive = '';

if (isset($_GET['drive'])) {
    if ($_GET['drive'] === 'conectado') {
        $mensajeDrive = 'Google Drive se conectó correctamente.';
        $tipoMensajeDrive = 'success';
        $estaConectado = true;
    } elseif ($_GET['drive'] === 'error') {
        $mensajeDrive = 'No se pudo conectar Google Drive: ' . ($_GET['msg'] ?? 'error desconocido');
        $tipoMensajeDrive = 'danger';
    }
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['accion_drive'])) {
    $tokenDrive = $_POST['csrf_drive'] ?? '';
    if (!is_string($tokenDrive) || !hash_equals($_SESSION['csrf_drive'], $tokenDrive)) {
        $mensajeDrive = 'La sesión del formulario venció. Actualiza la página e inténtalo nuevamente.';
        $tipoMensajeDrive = 'danger';
    } elseif ($_POST['accion_drive'] === 'desconectar') {
        $drive->desconectar();
        $estaConectado = false;
        $mensajeDrive = 'El respaldo automático en Google Drive quedó desactivado.';
        $tipoMensajeDrive = 'warning';
    } elseif ($_POST['accion_drive'] === 'eliminar' && $estaConectado) {
        try {
            $fileId = trim((string) ($_POST['fileId'] ?? ''));
            if ($fileId === '') {
                throw new RuntimeException('No se identificó el archivo.');
            }
            $drive->eliminarArchivo($fileId);
            $mensajeDrive = 'El archivo se eliminó de Google Drive.';
            $tipoMensajeDrive = 'success';
        } catch (Throwable $e) {
            $mensajeDrive = 'No se pudo eliminar el archivo de Google Drive.';
            $tipoMensajeDrive = 'danger';
        }
    }
}

if (!$estaConectado && $mensajeDrive === '') {
    $errorDrive = $drive->obtenerErrorConexion();
    if ($errorDrive) {
        $mensajeDrive = $errorDrive;
        $tipoMensajeDrive = 'warning';
    }
}

if ($estaConectado) {
    try {
        // Los certificados se guardan en una carpeta por sacramento. Se listan
        // solo los PDF para que el botón eliminar actúe sobre un certificado y
        // no sobre la carpeta que los contiene.
        $archivosDrive = [];
        foreach (['Confirmacion', 'Bautizo', 'Matrimonio'] as $carpetaDrive) {
            foreach ($drive->listarArchivos($carpetaDrive, 20) as $archivoDrive) {
                if (strtolower((string) $archivoDrive->getMimeType()) !== 'application/pdf') {
                    continue;
                }
                $archivosDrive[] = $archivoDrive;
            }
        }
        usort($archivosDrive, function ($a, $b) {
            return strcmp((string) $b->getCreatedTime(), (string) $a->getCreatedTime());
        });
        $archivosDrive = array_slice($archivosDrive, 0, 20);
    } catch (Throwable $e) {
        $estaConectado = false;
        $mensajeDrive = 'No se pudo consultar Google Drive. Vuelve a conectar la cuenta.';
        $tipoMensajeDrive = 'danger';
    }
}

$ultimoRespaldoDrive = !empty($archivosDrive)
    ? date('d/m/Y H:i', strtotime($archivosDrive[0]->getCreatedTime()))
    : '';
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
    <link rel="stylesheet" type="text/css" href="../assets/css/style.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/style.css'); ?>">
    <link rel="stylesheet" type="text/css" href="../assets/css/logo-cabecera.css?v=<?= filemtime(__DIR__ . '/../assets/css/logo-cabecera.css') ?>">
    <script src="../assets/js/jquery-3.6.4.min.js"></script>
    <style>
        .btn-separado {
            margin-bottom: 10px;
        }

        .containerRes {
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: flex-start;
            gap: 20px;
            width: 100%;
            margin-top: 0px;
            flex-wrap: wrap;
        }

        #reminders {
            width: 300px;
            height: 400px;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        #calendar {
            flex-grow: 1;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            max-height: 900px;
        }

        #current_day {
            background: rgba(255, 255, 255, 0.4);
            padding: 0.2rem 0.5rem;
            text-transform: dark;
            height: 50px;
            font-size: 1.6rem;
            line-height: 2.2rem;
            transition: 0.5s;
        }

        #week-container {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            grid-gap: 10px;
            margin-top: 20px;
        }

        .day.reserved {
            background-color: #b9ffb3;
            /* Color verde claro */
            color: dark;
        }

        .day {
            background-color: #fafafa;
            border-radius: 10px;
            padding: 5px;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.3s ease;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            font-size: 1.2rem;
        }

        .day:hover {
            background-color: #87CEFA;
        }

        .selected {
            background-color: #87CEFA;
            color: dark;
            font-weight: bold;
        }


        .day-name {
            font-weight: bold;
            text-align: center;
            margin-top: 5px;
            font-size: 0.8rem;
        }

        @media screen and (max-width: 768px) {
            .containerRes {

                padding: 0px;
            }

            #calendar {
                max-width: 100%;
                max-height: 100%;
                margin-bottom: 20px;
            }

            #reminders {
                width: 100%;
                max-width: 100%;
                height: auto;
            }

            #week-container {
                grid-template-columns: repeat(7, 1fr);
            }

            #monthDisplay {
                font-weight: bold;
                font-size: 0.8rem;
            }

            .day {
                padding: 5px;
                font-size: 1rem;
                height: 40px;
                width: 30px;
                text-align: center;
                cursor: pointer;
                transition: background-color 0.3s ease;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
            }

            .day-name {
                font-size: 0.7rem;
            }
        }

        @media screen and (max-width: 480px) {
            #week-container {
                grid-template-columns: repeat(7, 1fr);
            }

            #monthDisplay {
                font-weight: bold;
                font-size: 0.8rem;
            }

            .day {
                padding: 5px;
                font-size: 0.8rem;
                height: 40px;
                width: 30px;
                text-align: center;
                cursor: pointer;
                transition: background-color 0.3s ease;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
            }

            .day-name {
                font-size: 0.6rem;
            }
        }

        #monthDisplay {
            font-weight: bold;
        }

        .drive-respaldo {
            border: 1px solid var(--bs-border-color);
            border-radius: 12px;
            background: var(--bs-body-bg);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
            margin-top: 1rem;
            overflow: hidden;
        }

        .drive-respaldo-principal {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.25rem;
        }

        .drive-respaldo-icono {
            width: 56px;
            height: 56px;
            flex: 0 0 56px;
            display: grid;
            place-items: center;
            border-radius: 12px;
            background: #eef4ff;
            color: #0d6efd;
            font-size: 1.7rem;
        }

        .drive-respaldo-info {
            min-width: 220px;
            flex: 1 1 320px;
        }

        .drive-estado {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            border-radius: 999px;
            padding: .25rem .65rem;
            font-size: .78rem;
            font-weight: 700;
        }

        .drive-estado.conectado {
            color: #146c43;
            background: #d1e7dd;
        }

        .drive-estado.desconectado {
            color: #6c757d;
            background: #e9ecef;
        }

        .drive-estado.local {
            color: #084298;
            background: #cfe2ff;
        }

        .drive-respaldo-acciones {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: .75rem;
            flex: 1 1 420px;
        }

        .drive-switch {
            display: flex;
            align-items: center;
            gap: .55rem;
            margin: 0;
            white-space: nowrap;
        }

        .drive-switch .form-check-input {
            width: 2.7rem;
            height: 1.35rem;
            margin: 0;
            cursor: pointer;
        }

        .drive-detalles {
            border-top: 1px solid var(--bs-border-color);
            padding: 1rem 1.25rem 1.25rem;
        }

        @media (max-width: 991.98px) {
            .drive-respaldo-principal,
            .drive-respaldo-acciones {
                flex-wrap: wrap;
            }

            .drive-respaldo-acciones {
                justify-content: flex-start;
            }
        }
    </style>
    <script>
        function generarPDFPorFecha() {
            const fecha = document.getElementById('fecha').value;
            if (!fecha) {
                alert('Seleccione una fecha en el calendario.');
                return;
            }
            const url = `ReporteSacFec.php?fecha=${fecha}&descripcion=todos`;
            window.open(url, '_blank');
        }

        function generarPDFPorFechaMes() {
            const fecha = document.getElementById('fecha').value;
            if (!fecha) {
                alert('Seleccione una fecha en el calendario.');
                return;
            }
            const url = `ReporteSacFec.php?fecha=${fecha}&descripcion=mes`;
            window.open(url, '_blank');
        }
    </script>
</head>

<body>
    <header class="navbar-light fixed-top header-static bg-mode">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="VistaPersonal.php">
                    <img class="light-mode-item navbar-brand-item" src="../assets/images/logo.png?v=<?= filemtime(__DIR__ . '/../assets/images/logo.png') ?>" alt="logo">
                    <img class="dark-mode-item navbar-brand-item" src="../assets/images/logo-light.png?v=<?= filemtime(__DIR__ . '/../assets/images/logo-light.png') ?>" alt="logo">
                </a>

                <ul class="nav flex-nowrap align-items-center ms-sm-3 list-unstyled">
                    <li class="nav-item ms-2">
                        <a class="nav-link bg-light icon-md btn btn-light p-0" href="Configuracion.php" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Configurar">
                            <i class="bi bi-gear-fill fs-6"> </i>
                        </a>
                    </li>

                    <li class="nav-item ms-2 dropdown">
                        <a class="nav-link btn icon-md p-0" href="#" id="profileDropdown" role="button" data-bs-auto-close="outside" data-bs-display="static" data-bs-toggle="dropdown" aria-expanded="false">
                            <img class="avatar-img rounded border border-white border-3" src="<?php echo ($usuario['PerfilPer'] == 'Ninguno' || empty($usuario['PerfilPer'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$usuario['PerfilPer']}?v=" . time(); ?>" alt="">
                        </a>
                        <ul class="dropdown-menu dropdown-animation dropdown-menu-end pt-3 small me-md-n3" aria-labelledby="profileDropdown">
                            <li class="px-3">
                                <div class="d-flex align-items-center position-relative">
                                    <div class="avatar me-3">
                                        <img class="avatar-img rounded-circle" src="<?php echo ($usuario['PerfilPer'] == 'Ninguno' || empty($usuario['PerfilPer'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$usuario['PerfilPer']}?v=" . time(); ?>" alt="avatar">
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
        <div class="container personal-dashboard-container">
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
                                    <div class="h-50px" style="background-image:url(<?php echo ($usuario['FondoPer'] == 'Ninguno' || empty($usuario['FondoPer'])) ? '../assets/images/fondo/Ninguno.jpg' : "../assets/images/fondo/{$usuario['FondoPer']}?v=" . time(); ?>); background-position: center; background-size: cover; background-repeat: no-repeat;">
                                    </div>
                                    <div class="card-body pt-0">
                                        <div class="text-center">
                                            <div class="avatar avatar-lg mt-n5 mb-3">
                                                <img class="avatar-img rounded border border-white border-3" src="<?php echo ($usuario['PerfilPer'] == 'Ninguno' || empty($usuario['PerfilPer'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$usuario['PerfilPer']}?v=" . time(); ?>" alt="">
                                            </div>
                                            <h5 class="mb-0"> <a href="PerfilAdmin.php"><?php echo $usuario['Nombre'] . " " . $usuario['Paterno'] . " " . $usuario['Materno']; ?></a> </h5>
                                            <small><?php echo $usuario['DescripCar']; ?></small>
                                            <p class="mt-3"><?php echo $usuario['FrasePer']; ?></p>
                                        </div>

                                        <hr>

                                        <?php require_once __DIR__ . '/../Controlador/controladorAcceso.php'; ControladorAcceso::renderMenu(); ?>
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

                <div class="col-md-8 col-lg-6 vstack gap-4">
                    <div class="card h-100">

                        <div class="container">
                            <div class="row position-relative">
                                <div class="col-lg-12 mx-auto">
                                    <div class="text-center">
                                        <h1 class="py-1">Certificados Emitidos</h1>
                                    </div>

                                    <div class="containerRes">
                                        <div id="calendar">
                                            <div class="col-md-12 col-lg-12 d-sm-flex align-items-center text-center justify-content-sm-between ">
                                                <button class="btn btn-primary-soft" id="prevMonth">Anterior</button>
                                                <div id="current_day">
                                                    <p id="monthDisplay" class="w-100">Mes y Año</p>
                                                </div>
                                                <button class="btn btn-primary-soft" id="nextMonth">Siguiente</button>
                                            </div>

                                            <div id="week-container"></div>
                                        </div>

                                        <div id="reminders">
                                            <h5>Certificados Emitidos</h5>
                                            <div id="reminderList">Seleccione una fecha.</div>

                                            <div class="col-sm-12 col-lg-12 d-flex py-3">
                                                <input type="hidden" id="fecha" required>
                                                <div class="col-sm-5 col-lg-5">
                                                    <button onclick="generarPDFPorFechaMes()" class="btn btn-primary-soft" data-bs-toggle="tooltip" data-bs-placement="top" title="Mensual"><i class="bi bi-calendar2-minus"></i></button>
                                                    <button onclick="generarPDFPorFecha()" class="btn btn-primary-soft" data-bs-toggle="tooltip" data-bs-placement="top" title="Diario"><i class="bi bi-sunrise-fill"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <section class="drive-respaldo" id="respaldo-drive" aria-labelledby="tituloRespaldoDrive">
                                        <div class="drive-respaldo-principal">
                                            <div class="drive-respaldo-icono" aria-hidden="true">
                                                <i class="bi bi-cloud-arrow-up-fill"></i>
                                            </div>

                                            <div class="drive-respaldo-info">
                                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                                    <h5 class="mb-0" id="tituloRespaldoDrive">Respaldo en Google Drive</h5>
                                                    <?php if ($estaConectado): ?>
                                                        <span class="drive-estado conectado"><i class="bi bi-check-circle-fill"></i> Conectado</span>
                                                    <?php else: ?>
                                                        <span class="drive-estado desconectado"><i class="bi bi-dash-circle-fill"></i> Desconectado</span>
                                                    <?php endif; ?>
                                                    <span class="drive-estado local"><i class="bi bi-hdd-fill"></i> Copia local activa</span>
                                                </div>
                                                <div class="small text-body-secondary">
                                                    <?php if ($estaConectado && $ultimoRespaldoDrive): ?>
                                                        Último respaldo: <?php echo htmlspecialchars($ultimoRespaldoDrive); ?>
                                                    <?php elseif ($estaConectado): ?>
                                                        Cuenta conectada, todavía sin certificados respaldados.
                                                    <?php else: ?>
                                                        Activa Drive para respaldar los certificados al generarlos.
                                                    <?php endif; ?>
                                                </div>
                                                <div class="small text-body-secondary mt-1">Copia local: <strong>respaldos/certificados</strong>. Drive conserva una segunda copia en la nube.</div>
                                            </div>

                                            <div class="drive-respaldo-acciones">
                                                <?php if ($estaConectado): ?>
                                                    <form method="POST" class="drive-switch" id="formDesconectarDrive">
                                                        <input type="hidden" name="csrf_drive" value="<?php echo htmlspecialchars($_SESSION['csrf_drive']); ?>">
                                                        <input type="hidden" name="accion_drive" value="desconectar">
                                                        <label class="form-check-label" for="driveAutomatico">Respaldo automático</label>
                                                        <input class="form-check-input" type="checkbox" role="switch" id="driveAutomatico" checked>
                                                    </form>
                                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#driveDetalles" aria-expanded="false" aria-controls="driveDetalles">
                                                        <i class="bi bi-gear me-1"></i>Configurar Drive
                                                    </button>
                                                    <a href="VistaEmitidos.php#respaldo-drive" class="btn btn-primary">
                                                        <i class="bi bi-arrow-clockwise me-1"></i>Actualizar lista
                                                    </a>
                                                <?php else: ?>
                                                    <?php $urlDrive = $drive->obtenerUrlConexion(); ?>
                                                    <?php if ($urlDrive !== ''): ?>
                                                        <div class="drive-switch">
                                                            <label class="form-check-label" for="driveAutomatico">Respaldo automático</label>
                                                            <input class="form-check-input js-conectar-drive" type="checkbox" role="switch" id="driveAutomatico"
                                                                data-url="<?php echo htmlspecialchars($urlDrive, ENT_QUOTES, 'UTF-8'); ?>">
                                                        </div>
                                                        <a href="<?php echo htmlspecialchars($urlDrive, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary">
                                                            <i class="bi bi-google me-1"></i>Conectar Drive
                                                        </a>
                                                    <?php else: ?>
                                                        <div class="drive-switch">
                                                            <label class="form-check-label">Respaldo automático</label>
                                                            <span class="badge bg-secondary">No disponible</span>
                                                        </div>
                                                        <button type="button" class="btn btn-secondary" disabled title="El respaldo en Google Drive no está configurado en este servidor.">
                                                            <i class="bi bi-google me-1"></i>Conectar Drive
                                                        </button>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <?php if ($estaConectado): ?>
                                            <div class="alert alert-warning mt-3 mb-0 d-none" id="confirmarDesconectarDrive" role="alert">
                                                <div class="d-flex flex-wrap align-items-center gap-2">
                                                    <span class="me-auto"><i class="bi bi-exclamation-triangle me-1"></i>¿Desactivar el respaldo automático y desconectar Google Drive?</span>
                                                    <button type="submit" form="formDesconectarDrive" class="btn btn-sm btn-warning">Aceptar</button>
                                                    <button type="button" class="btn btn-sm btn-light border" data-cancelar-desconectar>Cancelar</button>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($mensajeDrive): ?>
                                            <div class="px-3 pb-3">
                                                <div class="alert alert-<?php echo htmlspecialchars($tipoMensajeDrive); ?> mb-0" role="alert">
                                                    <?php echo htmlspecialchars($mensajeDrive); ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($estaConectado): ?>
                                            <div class="collapse" id="driveDetalles">
                                                <div class="drive-detalles">
                                                    <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                                                        <div>
                                                            <h6 class="mb-1"><i class="bi bi-folder2-open me-1"></i>Archivos respaldados</h6>
                                                            <span class="small text-body-secondary">Últimos <?php echo count($archivosDrive); ?> archivos encontrados.</span>
                                                        </div>
                                                    </div>

                                                    <?php if (!empty($archivosDrive)): ?>
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-hover align-middle mb-0">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Archivo</th>
                                                                        <th>Fecha</th>
                                                                        <th class="text-end">Acción</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php foreach ($archivosDrive as $archivoDrive): ?>
                                                                        <tr>
                                                                            <td><i class="bi bi-file-earmark-pdf text-danger me-1"></i><?php echo htmlspecialchars($archivoDrive->getName()); ?></td>
                                                                            <td><?php echo date('d/m/Y H:i', strtotime($archivoDrive->getCreatedTime())); ?></td>
                                                                            <td class="text-end">
                                                                                <form method="POST" class="d-inline-flex align-items-center gap-1 js-eliminar-drive">
                                                                                    <input type="hidden" name="csrf_drive" value="<?php echo htmlspecialchars($_SESSION['csrf_drive']); ?>">
                                                                                    <input type="hidden" name="accion_drive" value="eliminar">
                                                                                    <input type="hidden" name="fileId" value="<?php echo htmlspecialchars($archivoDrive->getId()); ?>">
                                                                                    <button type="button" class="btn btn-sm btn-outline-danger js-pedir-eliminar" title="Eliminar de Drive">
                                                                                        <i class="bi bi-trash"></i>
                                                                                    </button>
                                                                                    <span class="js-confirmar-eliminar d-none align-items-center gap-1">
                                                                                        <span class="small text-danger fw-semibold">¿Eliminar?</span>
                                                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                                                            <i class="bi bi-check-lg"></i> Sí
                                                                                        </button>
                                                                                        <button type="button" class="btn btn-sm btn-secondary-soft js-cancelar-eliminar">
                                                                                            <i class="bi bi-x-lg"></i> No
                                                                                        </button>
                                                                                    </span>
                                                                                </form>
                                                                            </td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="text-center text-body-secondary py-3">
                                                            <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                                                            No hay certificados respaldados todavía.
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </section>

                                    <div class="col-sm-12 col-lg-12 d-flex py-3">
                                        <a href="VistaReporteEmitidos.php" class="btn btn-primary-soft w-100" target="_blank">
                                            <i class="bi bi-file-text"></i> Generar Reporte Emitidos Anual
                                        </a>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        $(document).ready(function() {
            function obtenerEstado() {
                $.ajax({
                    url: '../VerificarPersonal.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.Estado && response.DescripCar && response.PerfilPer) {
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
        const months = [
            "Enero", "Febrero", "Marzo", "Abril", "Mayo",
            "Junio", "Julio", "Agosto", "Septiembre",
            "Octubre", "Noviembre", "Diciembre"
        ];

        const dayNames = ["Lun", "Mar", "Mié", "Jue", "Vie", "Sáb", "Dom"];

        let currentMonthIndex = new Date().getMonth();
        let currentYear = new Date().getFullYear();

        function displayMonth() {
            const monthDisplay = document.getElementById('monthDisplay');
            monthDisplay.innerText = `${months[currentMonthIndex]} ${currentYear}`;

            displayDays();
        }

        function displayDays() {
            const weekContainer = document.getElementById('week-container');
            weekContainer.innerHTML = '';

            const firstDayOfMonth = new Date(currentYear, currentMonthIndex, 1).getDay();
            const daysInMonth = new Date(currentYear, currentMonthIndex + 1, 0).getDate();
            const hoy = new Date();
            const diaSeleccionado = currentMonthIndex === hoy.getMonth() && currentYear === hoy.getFullYear()
                ? hoy.getDate() : 1;

            const adjustedFirstDay = (firstDayOfMonth + 6) % 7;
            for (let i = 0; i < adjustedFirstDay; i++) {
                const emptyDiv = document.createElement('div');
                weekContainer.appendChild(emptyDiv);
            }

            for (let day = 1; day <= daysInMonth; day++) {
                const dayDiv = document.createElement('div');
                dayDiv.classList.add('day');
                dayDiv.innerText = day;
                const dayNameDiv = document.createElement('div');
                dayNameDiv.classList.add('day-name');
                const dayOfWeekIndex = (adjustedFirstDay + day - 1) % 7;
                dayNameDiv.innerText = dayNames[dayOfWeekIndex];

                dayDiv.appendChild(dayNameDiv);
                dayDiv.addEventListener('click', () => {
                    toggleSelection(dayDiv);
                    showReminders(day);
                });

                weekContainer.appendChild(dayDiv);

                if (day === diaSeleccionado) {
                    toggleSelection(dayDiv);
                }
            }

            showReminders(diaSeleccionado);
            highlightReservedDays(currentMonthIndex + 1, currentYear);
        }

        function toggleSelection(dayDiv) {
            const previouslySelected = document.querySelector('.selected');
            if (previouslySelected) {
                previouslySelected.classList.remove('selected');
            }
            dayDiv.classList.add('selected');
        }

        function showReminders(day) {
            const remindersDiv = document.getElementById('reminderList');
            const fechaSeleccionada = `${currentYear}-${(currentMonthIndex + 1).toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;

            document.getElementById("fecha").value = fechaSeleccionada;

            remindersDiv.innerHTML = `<h6>Fecha: ${day} ${months[currentMonthIndex]}</h6>`;

            fetch(`../Controlador/ControladorCertificadoCal.php?fecha=${fechaSeleccionada}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        data.forEach(reserva => {


                            let realizacionIcon;
                            switch (reserva.DescripSac) {
                                case 'Bautizo':
                                    realizacionIcon = '<i class="bi bi-droplet-half"></i>';
                                    break;
                                case 'Confirmación':
                                    realizacionIcon = '<i class="bi bi-fire"></i>';
                                    break;
                                case 'Matrimonio':
                                    realizacionIcon = '<i class="bi bi-clipboard-heart"></i>';
                                    break;
                                default:
                                    realizacionIcon = '';
                            }

                            let estadoColor;
                            switch (reserva.EstadoCert) {
                                case 'Activo':
                                    estadoColor = 'blue';
                                    break;
                                case 'Inactivo':
                                    estadoColor = 'red';
                                    break;
                                default:
                                    estadoColor = '';
                            }

                            remindersDiv.innerHTML += `
                <div style="color: ${estadoColor};"> 
                    ${realizacionIcon} | ${reserva.DescripSac} <i class="bi bi-file-earmark-ruled-fill"></i> ${reserva.DescripCar}
                </div>`;
                        });
                    } else {
                        remindersDiv.innerHTML += `<div>Sin certificados emitidos.</div>`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    remindersDiv.innerHTML = 'Error al cargar los datos.';
                });
        }

        function changeMonth(direction) {
            if (direction === 'next') {
                currentMonthIndex++;
                if (currentMonthIndex > 11) {
                    currentMonthIndex = 0;
                    currentYear++;
                }
            } else if (direction === 'prev') {
                currentMonthIndex--;
                if (currentMonthIndex < 0) {
                    currentMonthIndex = 11;
                    currentYear--;
                }
            }
            displayMonth();
        }

        function highlightReservedDays(mes, year) {
            fetch(`../Controlador/ControladorCertificadoCal.php?mes=${mes}&year=${year}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(reserva => {
                        const dia = reserva.dia;
                        const dayElements = document.querySelectorAll('.day');

                        dayElements.forEach((dayElement, index) => {
                            if (index === dia - 1) {
                                dayElement.classList.add('reserved');
                            }
                        });
                    });
                })
                .catch(error => {
                    console.error('Error al cargar los días reservados:', error);
                });
        }

        displayMonth();
        document.getElementById('prevMonth').addEventListener('click', () => changeMonth('prev'));
        document.getElementById('nextMonth').addEventListener('click', () => changeMonth('next'));
    </script>

    <script>
        (function () {
            var interruptor = document.querySelector('.js-conectar-drive');
            if (!interruptor) return;

            interruptor.addEventListener('change', function () {
                if (interruptor.checked && interruptor.dataset.url) {
                    window.location.href = interruptor.dataset.url;
                }
            });
        })();
    </script>

    <script>
        (function () {
            document.querySelectorAll('.js-eliminar-drive').forEach(function (formulario) {
                var boton = formulario.querySelector('.js-pedir-eliminar');
                var grupo = formulario.querySelector('.js-confirmar-eliminar');
                var cancelar = formulario.querySelector('.js-cancelar-eliminar');
                if (!boton || !grupo) return;

                boton.addEventListener('click', function (evento) {
                    evento.preventDefault();
                    document.querySelectorAll('.js-confirmar-eliminar').forEach(function (otro) {
                        if (otro !== grupo) {
                            otro.classList.add('d-none');
                            otro.classList.remove('d-inline-flex');
                        }
                    });
                    grupo.classList.remove('d-none');
                    grupo.classList.add('d-inline-flex');
                });

                if (cancelar) {
                    cancelar.addEventListener('click', function () {
                        grupo.classList.add('d-none');
                        grupo.classList.remove('d-inline-flex');
                    });
                }
            });
        })();
    </script>

    <script>
        (function () {
            var interruptor = document.getElementById('driveAutomatico');
            var panel = document.getElementById('confirmarDesconectarDrive');
            if (!interruptor || !panel) {
                return;
            }

            interruptor.addEventListener('change', function () {
                if (interruptor.checked) {
                    return;
                }
                interruptor.checked = true;
                panel.classList.remove('d-none');
                var confirmar = panel.querySelector('button[type="submit"]');
                if (confirmar) {
                    confirmar.focus();
                }
            });

            var cancelar = panel.querySelector('[data-cancelar-desconectar]');
            if (cancelar) {
                cancelar.addEventListener('click', function () {
                    panel.classList.add('d-none');
                    interruptor.focus();
                });
            }
        })();
    </script>

    <script src="../assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/functions.js"></script>
    <script src="../assets/js/tema.js"></script>
</body>

</html>
