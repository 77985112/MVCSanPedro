<?php
session_start();
if (!isset($_SESSION['CiPersona'])) {
    header("Location: ../logout.php");
    exit();
}
$Catequista = $_SESSION['CiPersona'];
include_once '../Controlador/controladorConfigCatequista.php';

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
        .disabled-link {
            pointer-events: none;
            opacity: 0.5;
            cursor: not-allowed;
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
        <div class="container">
            <div class="row">
                <div class="col-lg-3">
                    <div class="d-flex align-items-center mb-4 d-lg-none">
                        <button class="border-0 bg-transparent" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar">
                            <span class="btn btn-primary"><i class="fa-solid fa-sliders-h"></i></span>
                            <span class="h6 mb-0 fw-bold d-lg-none ms-2">Configuración</span>
                        </button>
                    </div>
                    <nav class="navbar navbar-light navbar-expand-lg mx-0">
                        <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasNavbar">
                            <div class="offcanvas-header">
                                <button type="button" class="btn-close text-reset ms-auto" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                            </div>

                            <div class="offcanvas-body p-0">
                                <div class="card w-100">
                                    <div class="card-body">

                                        <ul class="nav nav-tabs nav-pills nav-pills-soft flex-column fw-bold gap-2 border-0">
                                            <li class="nav-item" data-bs-dismiss="offcanvas">
                                                <a class="nav-link d-flex mb-0 active" href="#nav-setting-tab-1" data-bs-toggle="tab"> <img class="me-2 h-20px fa-fw" src="../assets/images/icon/person-outline-filled.svg" alt=""><span> Mi cuenta</span></a>
                                            </li>
                                            <li class="nav-item" data-bs-dismiss="offcanvas">
                                                <a class="nav-link d-flex mb-0" href="#nav-setting-tab-2" data-bs-toggle="tab"> <img class="me-2 h-20px fa-fw" src="../assets/images/icon/shield-outline-filled.svg" alt=""><span> Seguridad </span></a>
                                            </li>
                                            <?php if ($Catequista['RolCat'] == "Coordinador"): ?>
                                                <li class="nav-item" data-bs-dismiss="offcanvas">
                                                    <a class="nav-link d-flex mb-0" href="#nav-setting-tab-3" data-bs-toggle="tab"> <img class="me-2 h-20px fa-fw" src="../assets/images/icon/handshake-outline-filled.svg" alt=""><span> Compañeros</span></a>
                                                </li>
                                            <?php endif; ?>
                                            <li class="nav-item" data-bs-dismiss="offcanvas">
                                                <a class="nav-link d-flex mb-0" href="#nav-setting-tab-4" data-bs-toggle="tab"> <img class="me-2 h-20px fa-fw" src="../assets/images/icon/trash-var-outline-filled.svg" alt=""><span> Eliminar cuenta</span></a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="card-footer text-center py-2">
                                        <a class="btn btn-link text-secondary btn-sm" href="PerfilCatequista.php">Volver al perfil</a>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </nav>
                </div>

                <div class="col-lg-6 vstack gap-4">
                    <div class="tab-content py-0 mb-0">

                        <div class="tab-pane show active fade" id="nav-setting-tab-1">
                            <div class="card mb-4">
                                <div class="card-header border-0 pb-0">
                                    <h5 class="card-title"> Mi Información</h5>
                                </div>
                                <div class="card-body">

                                    <div class="card-header border-0 pb-0">
                                        <form id="imagenForm" method="post" action="" enctype="multipart/form-data">
                                            <input type="file" id="nuevaImagen" name="nuevaImagen" accept="image/*" class="btn btn-secondary-soft me-2">
                                            <button type="button" id="cambiarPerfil" class="btn btn-primary-soft me-2"><i class="bi bi-person-circle"></i></button>
                                            <button type="button" id="cambiarFondo" class="btn btn-success-soft me-2"><i class="bi bi-aspect-ratio"></i></button>
                                        </form>
                                    </div>
                                    <div class="card-body" id="vistaPreviaContainer">
                                        <ul class="list-group">
                                            <li class="list-group-item d-md-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6>Vista Previa:</h6>
                                                    <img id="preview" alt="Selecciona una imagen" style="width: 100%; height:200px;" />
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-group">

                                            <li class="list-group-item d-md-flex justify-content-between align-items-center">
                                                <div class="me-md-3">
                                                    <h6 class="mb-0">Imagen de Perfil</h6>
                                                    <img id="imagenPreviewPerfil" class="avatar-img rounded-circle border border-white border-3" src="<?php echo ($Catequista['ImagenCat'] == 'Ninguno' || empty($Catequista['ImagenCat'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$Catequista['ImagenCat']}"; ?>" alt="" style="width: 200px; height: 200px;">
                                                    <p class="small mb-0" id="imagenTextoPerfil"><?php echo ($Catequista['ImagenCat'] == 'Ninguno' || empty($Catequista['ImagenCat'])) ? 'Fondo Genérico' : 'Perfil Actual'; ?></p>
                                                </div>
                                                <div class="me-md-3">
                                                    <h6 class="mb-0">Imagen de Fondo</h6>
                                                    <img id="imagenPreviewFondo" class="h-200px rounded" src="<?php echo ($Catequista['FondoCat'] == 'Ninguno' || empty($Catequista['FondoCat'])) ? '../assets/images/fondo/Ninguno.jpg' : "../assets/images/fondo/{$Catequista['FondoCat']}"; ?>" alt="" style="background-size: cover; background-repeat: no-repeat; width: 450px; height: 200px;">
                                                    <p class="small mb-0" id="imagenTextoFondo"><?php echo ($Catequista['FondoCat'] == 'Ninguno' || empty($Catequista['FondoCat'])) ? 'Fondo Genérico' : 'Fondo Actual'; ?></p>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="rounded border px-3 py-2 mb-3">
                                        <form action="" method="post">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <h6 class="me-5">sobre mí </h6>
                                                <input type="text" class="form-control col-lg-1" id="frase" name="frase" style="font-size: 12pt; height: 30px; width:50%;" placeholder="Nueva Frase" required>
                                                <div class="dropdown ms-auto py-2">
                                                    <a class="nav nav-link text-secondary mb-0" href="#" id="aboutAction" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="bi bi-three-dots"></i>
                                                    </a>
                                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="aboutAction">
                                                        <li><button type="submit" name="cambiarFrase" class="dropdown-item"> <i class="bi bi-floppy fa-fw pe-2"></i>Guardar</button></li>
                                                        <li><a class="dropdown-item" href="#"> <i class="bi bi-arrow-clockwise fa-fw pe-2"></i>Cancelar</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <textarea type="text" name="FraseCat" class="form-control"><?php echo $Catequista['FraseCat']; ?></textarea>
                                        </form>
                                    </div>


                                    <div class="row g-4">
                                        <div class="col-sm-6">
                                            <div class="d-flex align-items-center rounded border px-3 py-2">
                                                <p class="mb-0">
                                                    <i class="bi bi-person fa-fw me-2"></i> Nombre: <strong> <?php echo $Catequista['Nombre'] . " " . $Catequista['ApPaterno'] . " " . $Catequista['ApMaterno']; ?></strong>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="d-flex align-items-center rounded border px-3 py-2">
                                                <p class="mb-0">
                                                    <i class="bi bi-briefcase fa-fw me-2"></i> Rol: <strong> <?php echo $Catequista['RolCat']; ?></strong>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <form action="" method="post">
                                                <div class="d-flex align-items-center rounded border px-3 py-2">

                                                    <p class="mb-0">
                                                        <i class="bi bi-telephone fa-fw me-2"></i> Contacto:
                                                    </p> <input type="text" id="Contactodato" name="Contactodato" class="form-control" style="font-size: 12pt; height: 20px; width:150px; " value="<?php echo $Catequista['Contacto']; ?>"></input>
                                                    <div class="dropdown ms-auto">
                                                        <a class="nav nav-link text-secondary mb-0" href="#" id="aboutAction2" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="bi bi-three-dots"></i>
                                                        </a>
                                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="aboutAction2">
                                                            <li><button type="submit" name="cambiarContacto" class="dropdown-item"> <i class="bi bi-floppy fa-fw pe-2"></i>Guardar</a></li>
                                                            <li><a class="dropdown-item" href="#"> <i class="bi bi-arrow-clockwise fa-fw pe-2"></i>Cancelar</a></li>
                                                        </ul>
                                                    </div>

                                                </div>
                                            </form>
                                        </div>
                                        <div class="col-sm-6">
                                            <form action="" method="post">
                                                <div class="d-flex align-items-center rounded border px-3 py-2">
                                                    <p class="mb-0"> <i class="bi bi-heart fa-fw me-2"></i> Estado: <strong> <?php echo $Catequista['Estado_per']; ?></strong></p>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="d-flex align-items-center rounded border px-3 py-2">
                                                <p class="mb-0">
                                                    <i class="bi bi-cake fa-fw me-2"></i> Fecha de Nacimiento: <strong><?php echo $Catequista['FechaNac']; ?></strong>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="d-flex align-items-center rounded border px-3 py-2">
                                                <p class="mb-0">
                                                    <i class="bi bi-geo-alt fa-fw me-2"></i> Dirección:
                                                </p><input type="text" name="Direccion" class="form-control" value="<?php echo $Catequista['Direccion']; ?>" style="font-size: 10pt; height: 20px; width:220px; "> </input>
                                                <div class="ms-auto">
                                                    <a class="nav nav-link text-secondary mb-0" href="#" id="aboutAction5" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="bi bi-three-dots"></i>
                                                    </a>
                                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="aboutAction5">
                                                        <li><a class="dropdown-item" href="#"> <i class="bi bi-floppy fa-fw pe-2"></i>Guardar</a></li>
                                                        <li><a class="dropdown-item" href="#"> <i class="bi bi-arrow-clockwise fa-fw pe-2"></i>Cancelar</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="d-flex align-items-center rounded border px-3 py-2">
                                                <p class="mb-0">
                                                    <i class="bi bi-pen fa-fw me-2"></i> Inscribe: <strong> <?php echo $Catequista['PoderCat']; ?> </strong>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="d-flex align-items-center rounded border px-3 py-2">
                                                <p class="mb-0">
                                                    <i class="bi bi-calendar3 fa-fw me-2"></i> Se unio en: <strong> <?php echo $Catequista['FechaIniCat']; ?></strong>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card mb-4">
                                <div class="card-header border-0 pb-0">
                                    <h5 class="card-title">Cambia tu contraseña</h5>
                                    <p class="mb-0">Al cambiar tu contraseña tines mas segura tu cuenta.</p>
                                </div>
                                <div class="card-body">
                                    <form method="post">
                                        <div class="row g-3">
                                            <div class="col-4">
                                                <label class="form-label">Contraseña actual</label>
                                                <input type="text" class="form-control" name="ActualC" id="ActualC" placeholder="Contraseña Actual">
                                            </div>
                                            <div class="col-4">
                                                <label class="form-label">Nueva contraseña</label>
                                                <div class="input-group">
                                                    <input class="form-control fakepassword" type="password" name="NuevoC1" id="NuevoC1" placeholder="Nueva Contraseña">
                                                    <span class="input-group-text p-0">
                                                        <i class="fakepasswordicon fa-solid fa-eye-slash cursor-pointer p-2 w-40px" onclick="myFunction()"></i>
                                                    </span>
                                                </div>
                                                <div id="pswmeter" class="mt-2"></div>
                                                <div id="pswmeter-message" class="rounded mt-1"></div>
                                            </div>

                                            <div class="col-4">
                                                <label class="form-label">Confirma tu contraseña</label>
                                                <div class="input-group">
                                                    <input class="form-control fakepassword" type="password" name="NuevoC2" id="NuevoC2" placeholder="Confirmar Contraseña">
                                                    <span class="input-group-text p-0">
                                                        <i class="fakepasswordicon fa-solid fa-eye-slash cursor-pointer p-2 w-40px" onclick="myFunction2()"></i>
                                                    </span>
                                                </div>
                                                <div id="pswmeter" class="mt-2"></div>
                                                <div id="pswmeter-message" class="rounded mt-1"></div>
                                            </div>
                                            <div class="col-12 text-end">

                                                <button type="submit" name="cambiarContracat" class="btn btn-primary mb-0">Guardar Contraseña</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>



                        <div class="tab-pane fade" id="nav-setting-tab-2">
                            <div class="card">
                                <div class="card-header border-0 pb-0">
                                    <h5 class="card-title">Mi seguridad</h5>
                                    <p class="mb-0">Puedes modificar la visualización de tus datos personales a Catequistas y Confirmantes que visitan tu perfil.</p>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group">
                                        <li class="list-group-item d-md-flex justify-content-between align-items-start">
                                            <div class="me-md-3">
                                                <h6 class="mb-0">Contacto</h6>
                                                <p class="small mb-0">Los demás podrían requerir tu número de contacto para informarte o realizarte alguna consulta.</p>
                                            </div>
                                            <button class="visibility-toggle btn btn-success-soft btn-sm mt-1 mt-md-0" data-visible="true"> <i class="bi bi-eye"></i> Visible</button>
                                        </li>
                                        <li class="list-group-item d-md-flex justify-content-between align-items-start">
                                            <div class="me-md-3">
                                                <h6 class="mb-0"> Fecha de nacimiento</h6>
                                                <p class="small mb-0">El día de tu nacimiento insentiva a festejar tu cumpleaño con los amigos.</p>
                                            </div>
                                            <button class="visibility-toggle btn btn-success-soft btn-sm mt-1 mt-md-0" data-visible="true"> <i class="bi bi-eye"></i> Visible</button>
                                        </li>
                                        <li class="list-group-item d-md-flex justify-content-between align-items-start">
                                            <div class="me-md-3">
                                                <h6 class="mb-0"> Estado</h6>
                                                <p class="small mb-0">Tu situacion emocional es el motivo de alegrias y tristezas.</p>
                                            </div>
                                            <button class="visibility-toggle btn btn-success-soft btn-sm mt-1 mt-md-0" data-visible="true"> <i class="bi bi-eye"></i> Visible</button>
                                        </li>
                                        <li class="list-group-item d-md-flex justify-content-between align-items-start">
                                            <div class="me-md-3">
                                                <h6 class="mb-0"> Dirección</h6>
                                                <p class="small mb-0">Tu dirección nos ayuda a ver tus dificultades y facilidades en participar de la Confirmación.</p>
                                            </div>
                                            <button class="visibility-toggle btn btn-success-soft btn-sm mt-1 mt-md-0" data-visible="true"> <i class="bi bi-eye"></i> Visible</button>
                                        </li>
                                    </ul>
                                </div>
                                <div class="card-footer pt-0 text-end border-0">
                                    <button type="submit" class="btn btn-sm btn-primary mb-0">Guardar Cambios</button>
                                </div>
                            </div>
                        </div>
                        <!-- seguridad fin -->

                        <!-- Poderes Compañeros -->
                        <div class="tab-pane fade" id="nav-setting-tab-3">
                            <div class="card mb-4">
                                <div class="card-header border-0 pb-0">
                                    <h4 class="card-title">Dale poderes a tus compañeros</h4>
                                    <h6 class="mb-0">“No te preguntes qué pueden hacer tus compañeros por ti. Pregúntate que puedes hacer tú por ellos”</h6>
                                    <br>
                                </div>
                            </div>
                            <div class="card mb-4">
                                <div class="card-header border-0 pb-0">
                                    <div class="hstack gap-2 mb-3">
                                        <div class="avatar">
                                            <img class="avatar-img rounded-circle" src="" alt="">
                                        </div>
                                        <div class="overflow-hidden">
                                            <h4 class="card-title">hola</h4>
                                            <apan class="mb-0" class="mb-0 small" style="color: red;">nada</apan>
                                        </div>
                                        <a class="btn btn-danger-soft rounded-circle icon-md ms-auto" href="#"><i class="fa-solid fa-eye"> </i></a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <div class="me-2">
                                                <h6 class="mb-0"><i class="bi bi-person-lines-fill"> </i> Poder registrar asistencia en aula y actividades a Confirmantes</h6>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" role="switch" id="msgSwitchCheckChecked">
                                            </div>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <div class="me-2">
                                                <h6 class="mb-0"><i class="bi bi-card-checklist"> </i> Poder registrar asistencia a apoderados en reuniones</h6>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" role="switch" id="msgSwitchCheckChecked2">
                                            </div>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <div class="me-2">
                                                <h6 class="mb-0"><i class="bi bi-vector-pen"> </i> Poder registrar puntaje en examenes de confirmantes</h6>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" role="switch" id="msgSwitchCheckChecked3">
                                            </div>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <div class="me-2">
                                                <h6 class="mb-0"><i class="bi bi-filetype-doc"> </i> Poder registrar documentación de confirmantes</h6>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" role="switch" id="msgSwitchCheckChecked4">
                                            </div>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <div class="me-2">
                                                <h6 class="mb-0"><i class="bi bi-file-text"> </i> Poder preparar e instruir a confirmantes con material educativo</h6>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" role="switch" id="msgSwitchCheckChecked4">
                                            </div>
                                        </li>
                                    </ul>
                                    <hr>
                                </div>
                                <div class="card-footer pt-0 text-end border-0">
                                    <button type="submit" class="btn btn-sm btn-success mb-0">Asignar Poderes</button>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="nav-setting-tab-4">
                            <form action="" method="post">
                                <input type="hidden" value="<?php echo $Catequista['CiCat'] ?>" name="CiCatCount">
                                <div class="card">
                                    <div class="card-header border-0 pb-0">
                                        <h5 class="card-title">Eliminar Cuenta</h5>
                                        <p class="mb-0">No siempre se puede contar con una persona como tú.<br>Tu presencia nos hará mucha falta, de todas maneras te deseamos éxitos en tu camino...</p>
                                    </div>
                                    <div class="card-body">
                                        <h6>Antes de irte...</h6>
                                        <ul>
                                            <li>Puedes ver tu información <a href="PerfilCatequista.php">aquí</a> </li>
                                            <li>Si eliminas tu cuenta perderás el acceso a esta.</li>
                                        </ul>
                                        <div class="form-check form-check-md my-4">
                                            <input class="form-check-input" type="checkbox" value="" id="deleteaccountCheck">
                                            <label class="form-check-label" for="deleteaccountCheck">Sí, estoy de acuerdo en eliminar mi cuenta.</label>
                                        </div>
                                        <a href="PerfilCatequista.php" class="btn btn-success-soft btn-sm mb-2 mb-sm-0">Cancelar Eliminación</a>
                                        <button id="deleteAccountButton" class="btn btn-danger-soft btn-sm mb-0 disabled-link" name="EliminarCount">Eliminar mi cuenta</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>


    <div class="modal fade" id="modalLoginActivity" tabindex="-1" aria-labelledby="modalLabelLoginActivity" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <!-- Modal header -->
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabelLoginActivity">Where You're Logged in </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="list-group list-group-flush">
                        <!-- location list item -->
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 pb-3">
                            <div class="me-2">
                                <h6 class="mb-0">London, UK</h6>
                                <ul class="nav nav-divider small">
                                    <li class="nav-item">Active now </li>
                                    <li class="nav-item">This Apple iMac </li>
                                </ul>
                            </div>
                            <button class="btn btn-sm btn-primary-soft"> Logout </button>
                        </li>
                        <!-- location list item -->
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                            <div class="me-2">
                                <h6 class="mb-0">California, USA</h6>
                                <ul class="nav nav-divider small">
                                    <li class="nav-item">Active now </li>
                                    <li class="nav-item">This Apple iMac </li>
                                </ul>
                            </div>
                            <button class="btn btn-sm btn-primary-soft"> Logout </button>
                        </li>
                        <!-- location list item -->
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                            <div class="me-2">
                                <h6 class="mb-0">New york, USA</h6>
                                <ul class="nav nav-divider small">
                                    <li class="nav-item">Active now </li>
                                    <li class="nav-item">This Windows </li>
                                </ul>
                            </div>
                            <button class="btn btn-sm btn-primary-soft"> Logout </button>
                        </li>
                        <!-- location list item -->
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 pt-3">
                            <div class="me-2">
                                <h6 class="mb-0">Mumbai, India</h6>
                                <ul class="nav nav-divider small">
                                    <li class="nav-item">Active now </li>
                                    <li class="nav-item">This Windows </li>
                                </ul>
                            </div>
                            <button class="btn btn-sm btn-primary-soft"> Logout </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <script>
        const checkbox = document.getElementById('deleteaccountCheck');
        const deleteButton = document.getElementById('deleteAccountButton');
        checkbox.addEventListener('change', function() {
            if (checkbox.checked) {
                deleteButton.classList.remove('disabled-link');
            } else {
                deleteButton.classList.add('disabled-link');
            }
        });



        $(document).ready(function() {
            $('#nuevaImagen').change(function() {
                mostrarVistaPrevia();
            });

            $('#cambiarPerfil').click(function() {
                enviarFormulario('cambiarPerfil');
            });

            $('#cambiarFondo').click(function() {
                enviarFormulario('cambiarFondo');
            });

            function mostrarVistaPrevia() {
                var input = document.getElementById('nuevaImagen');
                var preview = document.getElementById('preview');
                var container = document.getElementById('vistaPreviaContainer');

                if (input.files && input.files[0]) {
                    var reader = new FileReader();

                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        container.style.display = 'block';
                    }

                    reader.readAsDataURL(input.files[0]);
                } else {
                    preview.src = '';
                    container.style.display = 'none';
                }
            }

            function enviarFormulario(botonPresionado) {
                var formData = new FormData($('#imagenForm')[0]);
                formData.append(botonPresionado, '');

                $.ajax({
                    type: 'POST',
                    url: '',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        location.reload();
                    },
                    error: function(xhr, status, error) {
                        console.error("Error al enviar el formulario:", error);
                    }
                });
            }
        });
    </script>

    <script>
        function myFunction() {
            var x = document.getElementById("NuevoC1");
            if (x.type === "password") {
                x.type = "text";
            } else {
                x.type = "password";
            }
        }

        function myFunction2() {
            var x2 = document.getElementById("NuevoC2");
            if (x2.type === "password") {
                x2.type = "text";
            } else {
                x2.type = "password";
            }
        }
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
</body>

</html>