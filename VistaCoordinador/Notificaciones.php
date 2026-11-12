<?php
session_start();
if (!isset($_SESSION['CiPersona'])) {
    header("Location: ../logout.php");
    exit();
}

// Solo el coordinador puede crear y administrar notificaciones.
if (($_SESSION['CiPersona']['RolCat'] ?? '') !== 'Coordinador') {
    header("Location: ../logout.php");
    exit();
}

$Catequista = $_SESSION['CiPersona'];


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

    $sql = "SELECT n.idNotificacion, n.TituloNot, n.DetalleNot, n.FechaNotFin, n.CiNotificador, CONCAT(p.Nombre, ' ', p.ApPaterno) AS datoscat 
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

function getCatequistas($conn)
{
    $sql = "SELECT c.CiCat, p.Nombre, p.ApPaterno FROM catequista c INNER JOIN `persona` p ON c.CiCat = p.CiPersona";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

$activeNotifications = getActiveNotifications($conn);
$catNotis = getCatequistas($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $response = ['success' => false, 'message' => ''];

    if ($action === 'create' || $action === 'update') {
        $ciNotificador = $Catequista['CiCat'];
        $sacramentoNot = $Catequista['Sacramento'];
        $tituloNot = $_POST['tituloNot'] ?? '';
        $detalleNot = $_POST['detalleNot'] ?? '';
        $fechaNotifi = $_POST['fechaNotifi'] ?? '';
        $estadoNot = 'Activo';

        $timestampNotifi = strtotime((string) $fechaNotifi);
        $horaNotifi = $timestampNotifi !== false ? (int) date('H', $timestampNotifi) : -1;
        if ($timestampNotifi === false || $horaNotifi < 5 || $horaNotifi > 22) {
            $response['message'] = 'La hora debe estar entre 05:00 y 22:00.';
            echo json_encode($response);
            exit;
        }

        if ($action === 'create') {
            $fechaNot = date('Y-m-d H:i:s');
            $sql = "INSERT INTO notificaciones (CiNotificador, Sacramento, TituloNot, DetalleNot, FechaNot, FechaNotFin, EstadoNot) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssss", $ciNotificador, $sacramentoNot, $tituloNot, $detalleNot, $fechaNot, $fechaNotifi, $estadoNot);
        } else {
            $idNotificacion = (int) ($_POST['idNotificacion'] ?? 0);

            // Solo se puede editar una notificación del mismo sacramento.
            $stmtCheck = $conn->prepare("SELECT Sacramento FROM notificaciones WHERE idNotificacion = ? LIMIT 1");
            $stmtCheck->bind_param("i", $idNotificacion);
            $stmtCheck->execute();
            $noti = $stmtCheck->get_result()->fetch_assoc();
            $stmtCheck->close();

            if (!$noti || $noti['Sacramento'] !== $sacramentoNot) {
                $response['message'] = 'No puedes editar una notificación de otro sacramento.';
                echo json_encode($response);
                exit;
            }

            $sql = "UPDATE notificaciones 
                    SET TituloNot = ?, DetalleNot = ?, FechaNotFin = ?, EstadoNot = ? 
                    WHERE idNotificacion = ? AND Sacramento = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssis", $tituloNot, $detalleNot, $fechaNotifi, $estadoNot, $idNotificacion, $sacramentoNot);
        }

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = $action === 'create' ? 'Notificación creada exitosamente.' : 'Notificación actualizada exitosamente.';
        } else {
            $response['message'] = 'Error al ' . ($action === 'create' ? 'crear' : 'actualizar') . ' la notificación.';
        }
    }

    echo json_encode($response);
    exit;
}

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

                <div class="col-md-2 col-lg-2 col-xxl-2 mt-n4">
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
                                <a class="nav-link active" href="Notificaciones.php"> <i class="bi bi-bell-fill nav-icon"></i> <span class="nav-text">Notificación </span></a>
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
                                <a class="nav-link" href="ActividadVista.php"> <i class="bi bi-calendar-event-fill nav-icon"></i> <span class="nav-text">Actividad</span></a>
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


                <section class="pt-0 col-md-10 col-lg-10 col-xxl-10 pe-lg-5">
                    <div class="container">
                        <div class="card">

                            <div class="row g-2 g-lg-5 align-items-center p-5">
                                <div class="text-center">
                                    <h2>Notificaciones de <?php echo $Catequista['Sacramento']; ?></h2>
                                    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createNotificationModal">Crear Nueva Notificación</button>
                                    <div class="row">
                                        <?php foreach ($activeNotifications as $notification): ?>
                                            <div class="col-md-4 mb-3">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <h5 class="card-title"><?php echo htmlspecialchars($notification['TituloNot']); ?></h5>
                                                        <p class="card-text"><?php echo htmlspecialchars($notification['DetalleNot']); ?></p>
                                                        <p class="card-text"><small class="text-muted">Fecha: <?php echo date('d/m/Y H:i', strtotime($notification['FechaNotFin'])); ?></small></p>
                                                        <p class="card-text"><small class="text-muted">Notificado por: <?php echo htmlspecialchars($notification['datoscat']); ?></small></p>
                                                        <button class="btn btn-sm btn-primary edit-notification" data-notification='<?php echo json_encode($notification); ?>'>Editar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>


                                    <div class="modal fade" id="createNotificationModal" tabindex="-1" aria-labelledby="createNotificationModalLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="createNotificationModalLabel">Crear Nueva Notificación</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form id="createNotificationForm" action="" class="row g-3">
                                                        <input type="hidden" name="action" value="create">
                                                        <input type="hidden" id="ciNotificador" name="ciNotificador" value="<?php echo $Catequista['CiCat']; ?>" required>
                                                        <div class="col-sm-6 col-lg-6 form-group">
                                                            <label for="tituloNot" class="form-label">Título de Notificación</label>
                                                            <input type="text" class="form-control" id="tituloNot" name="tituloNot" required>
                                                        </div>
                                                        <div class="col-sm-6 col-lg-6 form-group">
                                                            <label class="form-label">Fecha de Notificación</label>
                                                            <div class="row g-2">
                                                                <div class="col-12">
                                                                    <input type="date" class="form-control" id="fechaNotifiFecha" required>
                                                                </div>
                                                                <div class="col-6">
                                                                    <select class="form-select" id="fechaNotifiHora" required>
                                                                        <?php for ($h = 0; $h <= 23; $h++): $hh = str_pad((string) $h, 2, '0', STR_PAD_LEFT); ?>
                                                                            <option value="<?php echo $hh; ?>" <?php echo $hh === '05' ? 'selected' : ''; ?> <?php echo ($h < 5 || $h > 22) ? 'disabled class="text-muted"' : ''; ?>><?php echo $hh; ?></option>
                                                                        <?php endfor; ?>
                                                                    </select>
                                                                </div>
                                                                <div class="col-6">
                                                                    <select class="form-select" id="fechaNotifiMinuto" required>
                                                                        <?php for ($m = 0; $m <= 59; $m++): $mm = str_pad((string) $m, 2, '0', STR_PAD_LEFT); ?>
                                                                            <option value="<?php echo $mm; ?>"><?php echo $mm; ?></option>
                                                                        <?php endfor; ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-text">Horario permitido: 05:00 a 22:00.</div>
                                                            <input type="hidden" name="fechaNotifi" id="fechaNotifi">
                                                        </div>
                                                        <div class="col-sm-6 col-lg-12 form-group">
                                                            <label for="detalleNot" class="form-label">Detalle de la Notificación</label>
                                                            <textarea class="form-control" id="detalleNot" name="detalleNot" required></textarea>
                                                        </div>
                                                    </form>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                    <button type="button" class="btn btn-primary" id="submitCreateNotification">Crear Notificación</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal fade" id="editNotificationModal" tabindex="-1" aria-labelledby="editNotificationModalLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editNotificationModalLabel">Editar Notificación</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form id="editNotificationForm" action="" class="row g-3">
                                                        <input type="hidden" name="action" value="update">
                                                        <input type="hidden" name="idNotificacion" id="editIdNotificacion">
                                                        <div class="mb-3">
                                                            <label for="editCiNotificador" class="form-label">Notificador</label>
                                                            <select class="form-select" id="editCiNotificador" name="ciNotificador" required disabled>
                                                                <?php foreach ($catNotis as $catNot): ?>
                                                                    <option value="<?php echo $catNot['CiCat']; ?>">
                                                                        <?php echo $catNot['Nombre'] . ' ' . $catNot['ApPaterno']; ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-sm-6 col-lg-6 form-group">
                                                            <label for="editTituloNot" class="form-label">Título</label>
                                                            <input type="text" class="form-control" id="editTituloNot" name="tituloNot" required>
                                                        </div>
                                                        <div class="col-sm-6 col-lg-6 form-group">
                                                            <label for="editDetalleNot" class="form-label">Detalle</label>
                                                            <textarea class="form-control" id="editDetalleNot" name="detalleNot" required></textarea>
                                                        </div>
                                                        <div class="col-sm-6 col-lg-12 form-group">
                                                            <label class="form-label">Fecha de Notificación</label>
                                                            <div class="row g-2">
                                                                <div class="col-12">
                                                                    <input type="date" class="form-control" id="editFechaNotifiFecha" required>
                                                                </div>
                                                                <div class="col-6">
                                                                    <select class="form-select" id="editFechaNotifiHora" required>
                                                                        <?php for ($h = 0; $h <= 23; $h++): $hh = str_pad((string) $h, 2, '0', STR_PAD_LEFT); ?>
                                                                            <option value="<?php echo $hh; ?>" <?php echo $hh === '05' ? 'selected' : ''; ?> <?php echo ($h < 5 || $h > 22) ? 'disabled class="text-muted"' : ''; ?>><?php echo $hh; ?></option>
                                                                        <?php endfor; ?>
                                                                    </select>
                                                                </div>
                                                                <div class="col-6">
                                                                    <select class="form-select" id="editFechaNotifiMinuto" required>
                                                                        <?php for ($m = 0; $m <= 59; $m++): $mm = str_pad((string) $m, 2, '0', STR_PAD_LEFT); ?>
                                                                            <option value="<?php echo $mm; ?>"><?php echo $mm; ?></option>
                                                                        <?php endfor; ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-text">Horario permitido: 05:00 a 22:00.</div>
                                                            <input type="hidden" name="fechaNotifi" id="editFechaNotifi">
                                                        </div>
                                                    </form>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                    <button type="button" class="btn btn-primary" id="submitEditNotification">Actualizar Notificación</button>
                                                </div>
                                            </div>
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
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1090;">
        <div id="avisoNotificacion" class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="avisoNotificacionTexto"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
        </div>
    </div>
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

    <script>
        function componerFechaNotifi(prefijo) {
            var fecha = document.getElementById(prefijo + 'Fecha');
            var hora = document.getElementById(prefijo + 'Hora');
            var minuto = document.getElementById(prefijo + 'Minuto');
            var oculto = document.getElementById(prefijo);

            if (!fecha || !hora || !minuto || !oculto || !fecha.value || !hora.value || !minuto.value) {
                if (oculto) oculto.value = '';
                return false;
            }

            oculto.value = fecha.value + 'T' + hora.value + ':' + minuto.value;
            return true;
        }

        function mostrarAvisoNotificacion(mensaje) {
            var toastEl = document.getElementById('avisoNotificacion');
            var texto = document.getElementById('avisoNotificacionTexto');
            if (!toastEl || !texto) return;

            texto.textContent = mensaje;
            if (window.bootstrap && bootstrap.Toast) {
                bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 4000 }).show();
            }
        }

        $(document).ready(function() {
            $('#submitCreateNotification').click(function() {
                if (!componerFechaNotifi('fechaNotifi')) {
                    mostrarAvisoNotificacion('Selecciona la fecha de la notificación.');
                    return;
                }
                $.ajax({
                    url: '',
                    method: 'POST',
                    data: $('#createNotificationForm').serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            mostrarAvisoNotificacion(response.message);
                        }
                    },
                    error: function() {
                        mostrarAvisoNotificacion('Error al procesar la solicitud.');
                    }
                });
            });

            $('.edit-notification').click(function() {
                var notification = $(this).data('notification');
                $('#editIdNotificacion').val(notification.idNotificacion);
                $('#editCiNotificador').val(notification.CiNotificador);
                $('#editTituloNot').val(notification.TituloNot);
                $('#editDetalleNot').val(notification.DetalleNot);
                var partesFechaNot = (notification.FechaNotFin || '').split(' ');
                var fechaNot = partesFechaNot[0] || '';
                var horaNot = (partesFechaNot[1] || '05:00').split(':');
                var horaNum = parseInt(horaNot[0], 10);
                if (isNaN(horaNum) || horaNum < 5) horaNum = 5;
                if (horaNum > 22) horaNum = 22;
                $('#editFechaNotifiFecha').val(fechaNot);
                $('#editFechaNotifiHora').val(String(horaNum).padStart(2, '0'));
                $('#editFechaNotifiMinuto').val(horaNot[1] || '00');
                $('#editNotificationModal').modal('show');
            });

            $('#submitEditNotification').click(function() {
                if (!componerFechaNotifi('editFechaNotifi')) {
                    mostrarAvisoNotificacion('Selecciona la fecha de la notificación.');
                    return;
                }
                $.ajax({
                    url: '',
                    method: 'POST',
                    data: $('#editNotificationForm').serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            mostrarAvisoNotificacion(response.message);
                        }
                    },
                    error: function() {
                        mostrarAvisoNotificacion('Error al procesar la solicitud.');
                    }
                });
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const hoy = new Date();
            const yyyy = hoy.getFullYear();
            const mm = String(hoy.getMonth() + 1).padStart(2, '0');
            const dd = String(hoy.getDate()).padStart(2, '0');
            const minFecha = `${yyyy}-${mm}-${dd}`;

            const fechaCrear = document.getElementById('fechaNotifiFecha');
            const fechaEditar = document.getElementById('editFechaNotifiFecha');

            if (fechaCrear) fechaCrear.setAttribute('min', minFecha);
            if (fechaEditar) fechaEditar.setAttribute('min', minFecha);
        });
    </script>
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