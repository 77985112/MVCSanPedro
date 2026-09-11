<?php
require_once '../Controlador/controladorCelAdmin.php';
require_once '../Controlador/ControladorPersona.php';
require_once '../Conexion/Conexion.php';
session_start();

if (!isset($_SESSION['CiPersona'])) {
    header("Location: ../logout.php");
    exit();
}

$Catequista = $_SESSION['CiPersona'];
$saqui = $Catequista['Sacramento'];

require_once '../Controlador/controladorCatCoor.php';
$contCats = new CatsController();
$cateG = $contCats->index($Catequista['CiCat']);
$catsacramento = $contCats->CatequsitasSac($Catequista['Sacramento']);

$controllerCel = new CelebranteControllerAdmin();
$celebrantesSinGrupo = $controllerCel->buscarCeleGR($Catequista['Sacramento']);

$conexion = new Conexion();
$conn = $conexion->getConnection();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "SELECT c.CiCel, p.Nombre, p.Sexo, YEAR(CURDATE()) - YEAR(p.FechaNac) AS Edad
            FROM celebrante c
            JOIN persona p ON c.CiCel = p.CiPersona
            JOIN inscripcion i ON c.CiCel = i.CiCel
            WHERE i.IdGrupo = 1 AND c.Sacramento = '$saqui'";

    $result = $conn->query($sql);
    $celebrantes = [];

    while ($row = $result->fetch_assoc()) {
        $celebrantes[] = $row;
    }
    $agrupados = [];
    foreach ($celebrantes as $celebrante) {
        $edad = $celebrante['Edad'];
        $sexo = $celebrante['Sexo'];

        if (!isset($agrupados[$edad])) {
            $agrupados[$edad] = ['Varon' => [], 'Mujer' => []];
        }
        $agrupados[$edad][$sexo][] = $celebrante;
    }
    foreach ($agrupados as $edad => $celebrantesPorSexo) {
        foreach ($celebrantesPorSexo as $sexo => $celebrantes) {
            foreach ($celebrantes as $celebrante) {
                $nuevoGrupoId = determinarGrupo($edad);
                if (grupoExiste($nuevoGrupoId, $conn)) {
                    if (puedeAsignar($nuevoGrupoId, $conn)) {
                        $sqlUpdate = "UPDATE inscripcion SET IdGrupo = '$nuevoGrupoId' WHERE CiCel = '{$celebrante['CiCel']}'";
                        if ($conn->query($sqlUpdate) === TRUE) {
                            echo "Catequisando {$celebrante['Nombre']} asignado al grupo {$nuevoGrupoId}.<br>";
                        } else {
                            echo "Error al asignar catequisando: " . $conn->error . "<br>";
                        }
                    }
                } else {
                    echo "El grupo {$nuevoGrupoId} no existe.<br>";
                }
            }
        }
    }
}

function determinarGrupo($edad)
{
    switch ($edad) {
        case 10:
            return 2;
        case 11:
            return 5;
        case 12:
            return 8;
        case 13:
            return 11;
        case 14:
            return 13;
        case 15:
            return 17;
        default:
            return null;
    }
}

function puedeAsignar($grupoId, $conn)
{
    $sqlCount = "SELECT COUNT(*) as total FROM inscripcion WHERE IdGrupo = '$grupoId'";
    $resultCount = $conn->query($sqlCount);
    $rowCount = $resultCount->fetch_assoc();

    return ($rowCount['total'] < 26);
}

function grupoExiste($grupoId, $conn)
{
    $sqlCheckGroup = "SELECT COUNT(*) as total FROM grupo WHERE IdGrupo = '$grupoId'";
    $resultCheckGroup = $conn->query($sqlCheckGroup);
    $rowCheckGroup = $resultCheckGroup->fetch_assoc();

    return ($rowCheckGroup['total'] > 0);
}





$mensaje = '';
$mensaje1 = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['modificar'])) {
        $ciCel = $_POST['CiCel'];

        $datosCelebrante = [
            'Sacramento' => $_POST['Sacramento'],
            'EstadoCel' => $_POST['EstadoCel'],
            'PerfilCel' => $_POST['PerfilCel'],
            'FondoCel' => $_POST['FondoCel']
        ];

        $datosInscripcion = [
            'IdGrupo' => $_POST['IdGrupo'],
            'PreValor' => $_POST['PreValor'],
            'CodTipoItem' => $_POST['CodTipoItem']
        ];

        if ($controllerCel->modificarCelebrante($ciCel, $datosCelebrante, $datosInscripcion) == false) {
            $mensaje = "Catequisando modificado con éxito.";
        } else {
            $mensaje1 = "No se pudo modificar al catequisando.";
        }
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
        .button-container {
            position: relative;
        }

        #magicButton {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        #magicButton:hover {
            background-color: #45a049;
        }

        .star {
            position: absolute;
            width: 10px;
            height: 10px;
            background-color: gold;
            clip-path: polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%);
            animation: starAnim 1s ease-out;
        }

        @keyframes starAnim {
            0% {
                transform: scale(0) rotate(0deg);
                opacity: 1;
            }

            100% {
                transform: scale(3.5) rotate(360deg);
                opacity: 0;
            }
        }
    </style>
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
                            <a class="nav-link active" href="VistaCeleAdmin.php"> <i class="bi bi-person-circle nav-icon"></i> <span class="nav-text">Catequisando </span></a>
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


                        <h2 class="align-items-center text-center py-2">Catequisandos de (<?php echo $Catequista['Sacramento']; ?>)</h2>

                        <div class="card-header d-sm-flex align-items-center text-center justify-content-sm-between border-0 pb-0">
                            <h1 class="h4 card-title">Gestionar mis catequisandos</h1>

                            <form method="POST" action="" class="mb-1 d-flex align-items-end">
                                <input type="text" class="form-control me-2" name="CiCel" placeholder="Identificación de Catequisando" required>
                                <input type="hidden" name="SacAdm" value="<?php echo $Catequista['Sacramento']; ?>" required>

                                <button type="submit" class="btn btn-primary-soft me-2" name="Buscar_Celebrante" href="#tab-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Buscar"><i class="bi bi-search"></i></button>
                                <a href="VistaRegistroCelebrante.php" class="btn btn-primary-soft me-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Registrar Nuevos"><i class="bi bi-plus-circle"></i></a>
                            </form>
                        </div>

                        <?php if (!empty($mensaje1)) : ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $mensaje1; ?>
                            </div>
                        <?php endif; ?>



                        <div class="card-body col-lg-12 p-4">
                            <?php if (isset($celebranteAdmin)): ?>
                                <form method="post" action="">
                                    <input type="hidden" name="ACCIns" value="<?= $celebranteAdmin['IdInscripcion'] ?>">
                                    <div class="col-sm-6 col-lg-6 form-group me-2">
                                        <button type="submit" name="ver_apoderado_padrino" class="btn btn-primary-soft">Ver Apoderado y Padrinos</button>
                                    </div>
                                </form>
                                <br>
                                <form method="POST" action="" class="row g-3">
                                    <input type="hidden" name="CiCel" value="<?= $celebranteAdmin['CiCel'] ?>">
                                    <input type="hidden" name="PerfilCel" value="<?= $celebranteAdmin['PerfilCel'] ?>">
                                    <input type="hidden" name="FondoCel" value="<?= $celebranteAdmin['FondoCel'] ?>">
                                    <input type="hidden" name="CodTipoItem" value="<?= $celebranteAdmin['CodTipoItem'] ?>">
                                    <input type="hidden" name="Sacramento" value="<?= $celebranteAdmin['Sacramento'] ?>">

                                    <ul class="list-group col-lg-12 align-items-center">

                                        <li class="list-group-item d-md-flex justify-content-between align-items-center col-lg-8">
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
                                        <input type="text" class="form-control" name="SacramentoCel" value="<?= $celebranteAdmin['DescripItem'] ?>" readonly>
                                    </div>

                                    <div class="col-sm-6 col-lg-2 form-group">
                                        <label class="form-label">Estado de Cuenta</label>
                                        <div>
                                            <input type="radio" id="EstadoCel" name="EstadoCel" value="Activo" <?php echo ($celebranteAdmin['EstadoCel'] == 'Activo') ? 'checked' : ''; ?>>
                                            <label for="Activo">Activo</label>
                                            &nbsp;&nbsp;&nbsp;
                                            <input type="radio" id="EstadoCel" name="EstadoCel" value="Inactivo" <?php echo ($celebranteAdmin['EstadoCel'] == 'Inactivo') ? 'checked' : ''; ?>>
                                            <label for="Inactivo">Inactivo</label>
                                        </div>
                                    </div>

                                    <div class="col-sm-6 col-lg-2 form-group me-2">
                                        <label class="form-label">Costo de Inscripción</label>
                                        <input type="text" class="form-control" value="<?= $celebranteAdmin['Valor'] ?>"></input>
                                    </div>
                                    <div class="col-sm-6 col-lg-2 form-group me-2">
                                        <label class="form-label">Monto Cancelado</label>
                                        <input type="text" class="form-control" name="PreValor" value="<?= $celebranteAdmin['PreValor'] ?>"></input>
                                    </div>

                                    <div class="col-sm-6 col-lg-3 me-2">
                                        <label class="form-label" class="form-control">Grupo Actual</label>
                                        <?php if ($celebranteAdmin['Sacramento'] == 'Primera Comunión'): ?>

                                            <select class="form-control" name="IdGrupo" data-search-enabled="true">
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
                                            <select class="form-control" name="IdGrupo" data-search-enabled="true">
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
                                    <br>
                                    <button type="submit" name="modificar" class="btn btn-success-soft">Actualizar</button>
                                </form>

                                
                            <?php elseif (!isset($celebranteAdmin)): ?>

                                <div class="my-sm-5 py-5 text-center">
                                    <h4>Lista de catequisandos sin grupo</h4>
                                    <p>Si desea asignar un catequisando a un grupo en específico, deberá realizarlo de manera individual</p>

                                    <?php if (!empty($celebrantesSinGrupo)) : ?>
                                        <table class="table table-striped table-hover col-lg-12 align-items-center">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Identificación</th>
                                                    <th>Nombre Completo</th>
                                                    <th>Sacramento</th>
                                                    <th>Edad</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($celebrantesSinGrupo as $ce): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($ce['CiCel']); ?></td>
                                                        <td><?php echo htmlspecialchars($ce['NombreCompleto']); ?></td>
                                                        <td><?php echo htmlspecialchars($ce['DescripItem']); ?></td>
                                                        <td><?php echo htmlspecialchars($ce['Edad']); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                        <br>
                                        <div class="button-container">
                                            <form method="POST">
                                                <button type="submit" id="magicButton"><i class="bi bi-magic"></i> Asignar catequisandos a grupos</button>
                                            </form>
                                        </div>
                                    <?php else : ?>
                                        <i class="display-1 text-body-secondary bi bi-calendar2-event"></i>
                                        <h4 class="mt-2 mb-3 text-body">No hay catequisandos</h4>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
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
                            <?php if (!empty($cateG)): ?>
                                <?php foreach ($cateG as $catequistaCoor): ?>
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
<script>
    const button = document.getElementById('magicButton');
    const container = document.querySelector('.button-container');

    button.addEventListener('click', () => {
        for (let i = 0; i < 20; i++) {
            createStar();
        }
    });

    function createStar() {
        const star = document.createElement('div');
        star.classList.add('star');

        const size = Math.random() * 15 + 5;
        star.style.width = `${size}px`;
        star.style.height = `${size}px`;

        const angle = Math.random() * 360;
        const distance = Math.random() * 100 + 50;
        const x = Math.cos(angle * Math.PI / 180) * distance;
        const y = Math.sin(angle * Math.PI / 180) * distance;

        star.style.left = `calc(50% + ${x}px)`;
        star.style.top = `calc(50% + ${y}px)`;

        container.appendChild(star);

        setTimeout(() => {
            star.remove();
        }, 1000);
    }
</script>
<script src="../assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/functions.js"></script>
<script src="../assets/js/tema.js"></script>
</body>

</html>