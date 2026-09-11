<?php
session_start();
require_once '../Modelo/modeloCelebranteG.php';
require_once '../Controlador/controladorReportesAll.php';
require_once('../Conexion/Conexion.php');

if (!isset($_SESSION['CiPersona'])) {
    header("Location: ../logout.php");
    exit();
}
$Celebrante = $_SESSION['CiPersona'];
$idIns = $Celebrante['IdInscripcion'];

$conexion = new Conexion();
$controller = new controladorReporteGeneral($conexion);
$celebrantes = $controller->obtenerNotas($idIns);

$asistenciasModelo = new Modelo();

$asistenciasCatequesis = $asistenciasModelo->obtenerAsistenciasCatequesis($idIns);
$asistenciasApoderados = $asistenciasModelo->obtenerAsistenciasApoderados($idIns);


if ($Celebrante['Color_Grupo'] === "Ninguno") {
    $colorGrupo = "#000000";
} else {
    $colorGrupo = $Celebrante['Color_Grupo'];
}

$MontoFinal = 0;



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
            .mainC {
                max-width: 100%;
                margin: 0 auto;
            }

            .cardsC {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                list-style: none;
                margin: 0;
                padding: 0;
            }

            .cards_itemC {
                display: flex;
                padding: 1rem;
            }

            .card_imageC {
                height: calc(13 * 1.2rem);
                padding: 1.2rem 1.2rem 0;
                position: relative;
            }

            .card_imageC:before,
            .card_imageC:after {
                content: "";
                position: absolute;
                width: 20px;
                left: 60%;
                top: 0;
                height: 45px;
                background: #e6e6e6b8;
                transform: rotate(45deg);
            }

            .card_imageC:after {
                transform: rotate(-45deg);
                top: auto;
                bottom: -22px;
                left: 40%;
            }

            .card_imageC img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }

            .cards_itemC {
                filter: drop-shadow(0 0 5px rgba(0, 0, 0, 0.25));
            }

            .cardC {
                background-color: white;
                border-radius: 0.25rem;
                display: flex;
                flex-direction: column;
                overflow: hidden;
                padding-left: 30px;
                background: repeating-linear-gradient(#0000 0 calc(1.2rem - 1px),
                        #66afe1 0 1.2rem) right bottom / 100% 100%,
                    linear-gradient(red 0 0) 30px 0/2px 100% #fff;
                background-repeat: no-repeat;
                line-height: 1.2rem;

            }

            .card_contentC {
                padding: 1.2rem;
            }

            h2.card_titleC,
            p {
                margin: 1.2rem 0;
            }

            h2.card_titleC {
                font-size: 1.2em;
            }



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
                background: <?php echo $colorGrupo; ?>;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                border-radius: 30px;
                animation: moving 5s ease-in-out infinite;
            }
        </style>

    </head>

<body class="sidebar-start-enabled">
    <header class="navbar-light bg-mode">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="VistaCelebrante.php">
                    <img class="light-mode-item navbar-brand-item" src="../assets/images/logo.png" alt="logo">
                    <img class="dark-mode-item navbar-brand-item" src="../assets/images/logo-light.png" alt="logo">
                </a>

                <ul class="nav flex-nowrap align-items-center ms-sm-3 list-unstyled">
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
                            <img class="avatar-img border border-white border-3 rounded" src="<?php echo ($Celebrante['PerfilCel'] == 'Ninguno' || empty($Celebrante['PerfilCel'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$Celebrante['PerfilCel']}"; ?>" alt="">
                        </a>
                        <ul class="dropdown-menu dropdown-animation dropdown-menu-end pt-3 small me-md-n3" aria-labelledby="profileDropdown">
                            <li class="px-3">
                                <div class="d-flex align-items-center position-relative">
                                    <div class="avatar me-3">
                                        <img class="avatar-img rounded-circle" src="<?php echo ($Celebrante['PerfilCel'] == 'Ninguno' || empty($Celebrante['PerfilCel'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$Celebrante['PerfilCel']}"; ?>" alt="avatar">
                                    </div>
                                    <div>
                                        <a class="h6 stretched-link" href="PerfilCelebrante.php"><?php echo $Celebrante['Nombre'] . " " . $Celebrante['ApPaterno'] . " " . $Celebrante['ApMaterno']; ?></a>
                                        <p class="small m-0"><?php echo $Celebrante['Sacramento']; ?></p>
                                    </div>
                                </div>
                                <a class="dropdown-item btn btn-primary-soft btn-sm my-2 text-center" href="PerfilCelebrante.php">Ver mi Perfil</a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="Configuracion.php">
                                    <i class="bi bi-gear fa-fw me-2"></i>Configuración y Privacidad
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="VistaReuniones.php">
                                    <i class="fa-fw bi bi-calendar2-check me-2"></i>Fecha de Reuniones
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="Reglamento.php">
                                    <i class="fa-fw bi bi-list-check me-2"></i>Reglamento
                                </a>
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
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sun fa-fw mode-switch" viewBox="0 0 16 16">
                                            <path d="M8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6zm0 1a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z" />
                                            <use href="#"></use>
                                        </svg>
                                    </button>
                                    <button type="button" class="btn btn-modeswitch nav-link text-primary-hover mb-0" data-bs-theme-value="dark" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Oscuro">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-moon-stars fa-fw mode-switch" viewBox="0 0 16 16">
                                            <path d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278zM4.858 1.311A7.269 7.269 0 0 0 1.025 7.71c0 4.02 3.279 7.276 7.319 7.276a7.316 7.316 0 0 0 5.205-2.162c-.337.042-.68.063-1.029.063-4.61 0-8.343-3.714-8.343-8.29 0-1.167.242-2.278.681-3.286z" />
                                            <path d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.734 1.734 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.734 1.734 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.734 1.734 0 0 0 1.097-1.097l.387-1.162zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.156 1.156 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.156 1.156 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732L13.863.1z" />
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
        <section class="pt-5 pb-0 position-relative" style="background-image: url(../assets/images/bg/CelebranteBg.jpg); background-repeat: no-repeat; background-size: cover; background-position: center;">
            <div class="bg-overlay bg-dark opacity-2"></div>
            <div class="container">
                <div class="pt-5">
                    <div class="row position-relative">
                        <div class="card border-0 col-lg-6 py-2">
                            <div class="row g-3">
                                <div class="col-4">
                                    <img class="rounded border border-white border-3" style="width: 180px;" src="<?php echo ($Celebrante['Imagen_Grupo'] == 'Anonimo' || empty($Celebrante['Imagen_Grupo'])) ? '../assets/images/Santo/SanPedro.png' : "../assets/images/Santo/{$Celebrante['Imagen_Grupo']}"; ?>" alt="">
                                </div>
                                <div class="col-8">
                                    <h2><a href="#" class="btn-link stretched-link text-reset fw-bold"><?php echo ($Celebrante['Santo_Grupo'] == 'Anonimo' || empty($Celebrante['Santo_Grupo'])) ? 'San Pedro' : "{$Celebrante['Santo_Grupo']}"; ?></a></h2>
                                    <div class="d-none d-sm-inline-block">
                                        <p class="mb-2"><?php echo $Celebrante['Frase_Santo']; ?></p>
                                    </div>
                                    <h4>Grupo: <strong><?php echo $Celebrante['NombreGrupo']; ?></strong></h4>
                                </div>
                            </div>
                            <div class="loader"></div>
                        </div>

                        <div class="mb-n5 mt-3 mt-lg-5">
                            <div class="col-xl-9 col-lg-11 mx-auto">
                                <div class="d-md-flex gap-3 mt-5">
                                    <a href="VistaMiGrupoCel.php" class="shaque card card-body mb-3 mb-lg-0 p-3 text-center">
                                        <img class="h-40px mb-3" src="../assets/images/icon/badge-outline-filled.svg" alt="">
                                        <h6>Mi Grupo</h6>
                                    </a>

                                    <a href="VistaCelebrante.php" class="shaque card card-body mb-3 mb-lg-0 p-3 text-center">
                                        <img class="h-40px mb-3" src="../assets/images/icon/home-outline-filled.svg" alt="">
                                        <h6>Inicio</h6>
                                    </a>

                                    <a href="VistaEstadoCel.php" class="shaque card card-body mb-3 mb-lg-0 p-3 text-center">
                                        <img class="h-40px mb-3" src="../assets/images/icon/imac-outline-filled.svg" alt="">
                                        <h6>Mi Estado</h6>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <br>

        <section class="pt-5">
            <div class="container">
                <div class="row g-4">
                    <div class="col-12 vstack gap-4">
                        <div class="card p-3">

                            <h1 class="text-center">Mi estado general en catequesis</h1>

                            <div class="mainC col-lg-12">
                                <ul class="cardsC">
                                    <li class="cards_itemC">
                                        <div class="cardC">
                                            <div class="card_imageC"><img
                                                    src="../assets/images/elements/Examen.png"
                                                    alt=""></div>
                                            <div class="card_contentC">
                                                <h2 class="card_titleC text-center"><a href="VistaReporteNotas.php" target="_blank">Examenes</a></h2>
                                                <div class="card_textC">

                                                    <?php $total = 0;
                                                    if (!empty($celebrantes)) {
                                                        foreach ($celebrantes as $celebrante) {
                                                            $total = ($celebrante['NotaExamen1'] + $celebrante['NotaExamen2'] + $celebrante['NotaExamen3'] + $celebrante['NotaExamen4']) / 4; ?>
                                                            <p>Examen 1 | <strong><?= htmlspecialchars($celebrante['NotaExamen1']) ?> Pts.</strong></p>
                                                            <p>Examen 2 | <strong><?= htmlspecialchars($celebrante['NotaExamen2']) ?> Pts.</strong></p>
                                                            <p>Examen 3 | <strong><?= htmlspecialchars($celebrante['NotaExamen3']) ?> Pts.</strong></p>
                                                            <p>Examen 4 | <strong><?= htmlspecialchars($celebrante['NotaExamen4']) ?> Pts.</strong></p>
                                                            <p>Promedio | <strong><?= htmlspecialchars(round($total)) ?> Pts.</strong></p>
                                                            <p>Estado | <strong><?= htmlspecialchars($celebrante['EstadoExamen']) ?></strong></p>
                                                        <?php  }
                                                    } else { ?>
                                                        <p colspan="4"><strong>No se encontraron Notas.</strong></p>
                                                    <?php  } ?>
                                                </div>
                                            </div>
                                        </div>
                                    </li>

                                    <li class="cards_itemC">
                                        <div class="cardC">
                                            <div class="card_imageC"><img
                                                    src="../assets/images/elements/Catequesis.png"
                                                    alt=""></div>
                                            <div class="card_contentC">
                                                <h2 class="card_titleC text-center"><a href="VistaReporteAsistencia.php" target="_blank">Asistencia a catequesis</a></h2>
                                                <div class="card_textC">
                                                    <?php if (!empty($asistenciasCatequesis)) : ?>
                                                        <?php foreach ($asistenciasCatequesis as $asistenciaC) : ?>
                                                            <p><?= htmlspecialchars($asistenciaC['FechaAsisConf']) ?> | <strong><?= htmlspecialchars($asistenciaC['TipoAsis']) ?></strong></p>
                                                        <?php endforeach; ?>
                                                    <?php else : ?>
                                                        <p colspan="4"><strong>No hay asistencias registradas.</strong></p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="cards_itemC">
                                        <div class="cardC">
                                            <div class="card_imageC"><img src="../assets/images/elements/Reuniones.png"
                                                    alt=""></div>
                                            <div class="card_contentC">
                                                <h2 class="card_titleC text-center"><a href="VistaReporteReunion.php" target="_blank">Asistencia a reuniones</a></h2>
                                                <div class="card_textC">
                                                    <?php if (!empty($asistenciasApoderados)) : ?>
                                                        <?php foreach ($asistenciasApoderados as $asistenciaR) : ?>
                                                            <?php $MontoFinal = $MontoFinal + $asistenciaR['MontoAsis'] ?>
                                                            <p><?= htmlspecialchars($asistenciaR['FechaReu']) ?> : <?= htmlspecialchars($asistenciaR['HoraReu']) ?> | <strong><?= htmlspecialchars($asistenciaR['DetalleAsis']) ?></strong> | <?= htmlspecialchars($asistenciaR['MontoAsis']) ?> Bs.</p>
                                                        <?php endforeach; ?>
                                                        <p>Monto Total: <strong><?= htmlspecialchars($MontoFinal) ?></strong> Bs.</p>
                                                    <?php else : ?>
                                                        <p colspan="4"><strong>No hay asistencias registradas.</strong></p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>

                        </div>
                    </div>
                </div>
        </section>

        <script>
            $(document).ready(function() {
                function obtenerEstado() {
                    $.ajax({
                        url: '../VerificarCelebrante.php',
                        method: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            if (data.EstadoCel) {
                                $('#EstadoCel').text(data.EstadoCel);
                                if (data.EstadoCel === "Inactivo") {
                                    window.location.replace("PerfilCelebrante.php");
                                }
                            } else {
                                $('#EstadoCel').text('Error al obtener el estado');
                                console.error(data.error);
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