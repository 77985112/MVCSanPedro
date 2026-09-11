<?php
session_start();
require_once '../controlador/ControladorActividad.php';

$controller = new ControladorActividad();
$actividadesParroquia = $controller->obtenerActividades('Parroquia');
$actividadesPrimeraComunion = $controller->obtenerActividades('Primera Comunión');
$actividadesConfirmacion = $controller->obtenerActividades('Confirmación');
$actividadesTodas = $controller->obtenerActividades();

$diassemana = array("Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado");
$meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");

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
    <link rel="stylesheet" type="text/css" href="../assets/vendor/tiny-slider/dist/tiny-slider.css">
    <link rel="stylesheet" type="text/css" href="../assets/vendor/plyr/plyr.css">
    <link rel="stylesheet" type="text/css" href="../assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="../assets/css/Index.css">
    <style>
        .bg-mode {
            background-color: #ffffff;
            color: #000000;
        }

        .navbar-light .navbar-brand {
            color: #000000;
        }

        .catequista-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .catequista-card:hover {
            transform: scale(1.05);
            z-index: 1;
            box-shadow: 5px 5px 10px rgba(0, 0, 0, 0.5);
        }
    </style>

</head>

<body data-bs-theme="light">

    <header class="navbar-light fixed-top header-static bg-mode">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="../index.php">
                    <img class="light-mode-item navbar-brand-item" src="../assets/images/logo.png" alt="logo">
                    <img class="dark-mode-item navbar-brand-item" src="../assets/images/logo-light.png" alt="logo">
                </a>
                <button class="navbar-toggler ms-auto icon-md btn btn-light p-0" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false"
                    aria-label="Toggle navigation">
                    <span class="navbar-toggler-animation">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>

                <div class="collapse navbar-collapse" id="navbarCollapse">
                    <ul class="navbar-nav navbar-nav-scroll me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="../index.php">Inicio</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="srcActividadGeneral.php">Actividades</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="SacramentoMenu" data-bs-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">Sacramentos</a>
                            <ul class="dropdown-menu" aria-labelledby="SacramentoMenu">
                                <li> <a class="dropdown-item" href="srcSacramentoBautizo.php">Bautizo</a></li>
                                <li> <a class="dropdown-item" href="srcSacramentoComunion.php">Primera Comunión</a></li>
                                <li> <a class="dropdown-item" href="srcSacramentoConfirmacion.php">Confirmación</a></li>
                                <li> <a class="dropdown-item" href="srcSacramentoMatrimonio.php">Matrimonio</a></li>
                                <li> <a class="dropdown-item" href="srcSacramentos.php">Todos los sacramentos</a></li>
                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="NosotrosMenu" data-bs-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">Conócenos</a>
                            <ul class="dropdown-menu" aria-labelledby="NosotrosMenu">
                                <li> <a class="dropdown-item" href="srcNosotrosParroquia.php">Parroquia</a></li>
                                <li> <a class="dropdown-item" href="srcNosotrosComunion.php">Primera Comunión</a></li>
                                <li> <a class="dropdown-item" href="srcNosotrosConfirmacion.php">Confirmación</a></li>
                            </ul>
                        </li>

                    </ul>
                </div>
                <div class="ms-3 ms-lg-auto me-2" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Ingresar">
                    <button type="button" class="btn btn-primary-soft" data-bs-toggle="modal"
                        data-bs-target="#exampleModal">
                        <i class="bi bi-person-fill"></i>
                    </button>
                </div>
                <div class="ms-3 ms-lg-auto" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Contactos">
                    <a type="button" href="srcContacto.php" class="btn btn-primary-soft">
                        <i class="bi bi-chat-left-dots"></i>
                    </a>
                </div>
            </div>
        </nav>
    </header>


    <main>
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-8 vstack gap-4">


                    <div class="card h-100">
                        <div class="card-header d-sm-flex align-items-center text-center justify-content-sm-between border-0 pb-0">
                            <h1 class="h2 card-title">Proximas actividades a realizarce </h1>
                        </div>
                        <div class="card-body">
                            <ul class="nav nav-tabs nav-bottom-line justify-content-center justify-content-md-start">
                                <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-1"> Todos</a></li>
                                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-2"> Parroquia </a></li>
                                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-3"> Primera Comunión </a></li>
                                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-4"> Confirmación </a></li>
                            </ul>
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

                                                <?php
                                                $fechaAdm = $actividad['FechaActividad'];
                                                $timestampFecha = strtotime($fechaAdm);
                                                $diaSemana = $diassemana[date('w', $timestampFecha)];
                                                $dia = date('d', $timestampFecha);
                                                $mes = $meses[date('n', $timestampFecha) - 1];
                                                $anio = date('Y', $timestampFecha);
                                                $fechaFormateada = "$diaSemana, $dia de $mes del $anio";
                                                ?>
                                                <div class="col-sm-6 col-xl-6">
                                                    <div class="card h-100 catequista-card">
                                                        <div class="position-relative">
                                                            <img
                                                                class="img-fluid rounded-top fixed-height"
                                                                src="../assets/images/events/<?php echo $actividad['ImagenActividad'] !== 'Ninguno' ? $actividad['ImagenActividad'] : 'Generico.png'; ?>"
                                                                alt=""
                                                                style="width: 100%; height:300px;"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#modalImagen"
                                                                onclick="abrirModal('<?php echo '../assets/images/events/' . ($actividad['ImagenActividad'] !== 'Ninguno' ? $actividad['ImagenActividad'] : 'Generico.png'); ?>')">
                                                            <div class="badge bg-<?php echo $actividad['EstActividad'] == 'Activo' ? 'success' : 'danger'; ?> text-white mt-2 me-2 position-absolute top-0 end-0"><?php echo $actividad['EstActividad']; ?></div>
                                                        </div>
                                                        <div class="card-body position-relative pt-0">
                                                            <input type="hidden" value="<?php echo $actividad['idActividad']; ?>">
                                                            <h6 class="mt-3"><?php echo $actividad['TituloActividad']; ?></h6>
                                                            <p class="mb-0 small"><i class="bi bi-calendar-check pe-1"></i><?php echo $fechaFormateada; ?> a <strong><?php echo $actividad['HoraAct']; ?></strong></p>
                                                            <p class="small"><i class="bi bi-geo-alt pe-1"></i><?php echo $actividad['LugarAct']; ?></p>
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
                                                <?php
                                                $fechaParr = $actividad['FechaActividad'];
                                                $timestampFecha = strtotime($fechaParr);
                                                $diaSemana = $diassemana[date('w', $timestampFecha)];
                                                $dia = date('d', $timestampFecha);
                                                $mes = $meses[date('n', $timestampFecha) - 1];
                                                $anio = date('Y', $timestampFecha);
                                                $fechaForm = "$diaSemana, $dia de $mes del $anio";
                                                ?>
                                                <div class="col-sm-6 col-xl-6">
                                                    <div class="card h-100 catequista-card">
                                                        <div class="position-relative">
                                                            <img
                                                                class="img-fluid rounded-top fixed-height"
                                                                src="../assets/images/events/<?php echo $actividad['ImagenActividad'] !== 'Ninguno' ? $actividad['ImagenActividad'] : 'Generico.png'; ?>"
                                                                alt=""
                                                                style="width: 100%; height:300px;"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#modalImagen"
                                                                onclick="abrirModal('<?php echo '../assets/images/events/' . ($actividad['ImagenActividad'] !== 'Ninguno' ? $actividad['ImagenActividad'] : 'Generico.png'); ?>')">
                                                            <div class="badge bg-<?php echo $actividad['EstActividad'] == 'Activo' ? 'success' : 'danger'; ?> text-white mt-2 me-2 position-absolute top-0 end-0"><?php echo $actividad['EstActividad']; ?></div>
                                                        </div>
                                                        <div class="card-body position-relative pt-0">
                                                            <input type="hidden" value="<?php echo $actividad['idActividad']; ?>">
                                                            <h6 class="mt-3"><?php echo $actividad['TituloActividad']; ?></h6>
                                                            <p class="mb-0 small"><i class="bi bi-calendar-check pe-1"></i><?php echo $fechaFormateada; ?> a <strong><?php echo $actividad['HoraAct']; ?></strong></p>
                                                            <p class="small"><i class="bi bi-geo-alt pe-1"></i><?php echo $actividad['LugarAct']; ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Primera Comunión -->
                                <div class="tab-pane fade" id="tab-3">
                                    <div class="row g-4">
                                        <?php if (empty($actividadesPrimeraComunion)): ?>
                                            <div class="my-sm-5 py-sm-5 text-center">
                                                <i class="display-1 text-body-secondary bi bi-calendar2-event"></i>
                                                <h4 class="mt-2 mb-3 text-body">No hay Actividades</h4>
                                            </div>
                                        <?php else: ?>
                                            <?php foreach ($actividadesPrimeraComunion as $actividad): ?>
                                                <?php
                                                $fechaCom = $actividad['FechaActividad'];
                                                $timestampFecha = strtotime($fechaCom);
                                                $diaSemana = $diassemana[date('w', $timestampFecha)];
                                                $dia = date('d', $timestampFecha);
                                                $mes = $meses[date('n', $timestampFecha) - 1];
                                                $anio = date('Y', $timestampFecha);
                                                $fechaCom = "$diaSemana, $dia de $mes del $anio";
                                                ?>
                                                <div class="col-sm-6 col-xl-6">
                                                    <div class="card h-100 catequista-card">
                                                        <div class="position-relative">
                                                            <img
                                                                class="img-fluid rounded-top fixed-height"
                                                                src="../assets/images/events/<?php echo $actividad['ImagenActividad'] !== 'Ninguno' ? $actividad['ImagenActividad'] : 'Generico.png'; ?>"
                                                                alt=""
                                                                style="width: 100%; height:300px;"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#modalImagen"
                                                                onclick="abrirModal('<?php echo '../assets/images/events/' . ($actividad['ImagenActividad'] !== 'Ninguno' ? $actividad['ImagenActividad'] : 'Generico.png'); ?>')">
                                                            <div class="badge bg-<?php echo $actividad['EstActividad'] == 'Activo' ? 'success' : 'danger'; ?> text-white mt-2 me-2 position-absolute top-0 end-0"><?php echo $actividad['EstActividad']; ?></div>
                                                        </div>
                                                        <div class="card-body position-relative pt-0">
                                                            <input type="hidden" value="<?php echo $actividad['idActividad']; ?>">
                                                            <h6 class="mt-3"><?php echo $actividad['TituloActividad']; ?></h6>
                                                            <p class="mb-0 small"><i class="bi bi-calendar-check pe-1"></i><?php echo $fechaFormateada; ?> a <strong><?php echo $actividad['HoraAct']; ?></strong></p>
                                                            <p class="small"><i class="bi bi-geo-alt pe-1"></i><?php echo $actividad['LugarAct']; ?></p>
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
                                                <?php
                                                $fechaConf = $actividad['FechaActividad'];
                                                $timestampFecha = strtotime($fechaConf);
                                                $diaSemana = $diassemana[date('w', $timestampFecha)];
                                                $dia = date('d', $timestampFecha);
                                                $mes = $meses[date('n', $timestampFecha) - 1];
                                                $anio = date('Y', $timestampFecha);
                                                $fechaConf = "$diaSemana, $dia de $mes del $anio";
                                                ?>
                                                <div class="col-sm-6 col-xl-6">
                                                    <div class="card h-100 catequista-card">
                                                        <div class="position-relative">
                                                            <img
                                                                class="img-fluid rounded-top fixed-height"
                                                                src="../assets/images/events/<?php echo $actividad['ImagenActividad'] !== 'Ninguno' ? $actividad['ImagenActividad'] : 'Generico.png'; ?>"
                                                                alt=""
                                                                style="width: 100%; height:300px;"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#modalImagen"
                                                                onclick="abrirModal('<?php echo '../assets/images/events/' . ($actividad['ImagenActividad'] !== 'Ninguno' ? $actividad['ImagenActividad'] : 'Generico.png'); ?>')">
                                                            <div class="badge bg-<?php echo $actividad['EstActividad'] == 'Activo' ? 'success' : 'danger'; ?> text-white mt-2 me-2 position-absolute top-0 end-0"><?php echo $actividad['EstActividad']; ?></div>
                                                        </div>
                                                        <div class="card-body position-relative pt-0">
                                                            <input type="hidden" value="<?php echo $actividad['idActividad']; ?>">
                                                            <h6 class="mt-3"><?php echo $actividad['TituloActividad']; ?></h6>
                                                            <p class="mb-0 small"><i class="bi bi-calendar-check pe-1"></i><?php echo $fechaFormateada; ?> a <strong><?php echo $actividad['HoraAct']; ?></strong></p>
                                                            <p class="small"><i class="bi bi-geo-alt pe-1"></i><?php echo $actividad['LugarAct']; ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        <br>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Ingrese sus datos</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <?php
                    if (isset($_SESSION['usuario']) || isset($_SESSION['CiPersona'])) {
                        echo '<h2 class="mb-48 text-center">Oops..</h2>';
                        echo '<h6 class="mb-48 text-center">Ya tienes una sesion activa</h6><br>';
                        echo '<a href="logout.php" class="btn btn-lg btn-danger-soft"> Cerrar Session</a>';
                    } else {
                    ?>
                        <div class="modal-body">
                            <form action="../Controlador/controladorLogin.php" method="POST" class="form-validator"
                                id="login-form">
                                <div class="mb-3 position-relative input-group-lg">
                                    <input type="text" class="form-control" placeholder="Usuario" id="username"
                                        name="username">
                                </div>
                                <div class="mb-3">
                                    <div class="input-group input-group-lg">
                                        <input class="form-control fakepassword" type="password" id="password"
                                            name="password" placeholder="Contraseña">
                                        <span class="input-group-text p-0">
                                            <i class="fakepasswordicon fa-solid fa-eye-slash cursor-pointer p-2 w-40px"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-lg btn-primary-soft">Ingresar</button>
                                </div>
                            </form>
                        </div>
                    <?php }
                    ?>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalImagen" tabindex="-1" aria-labelledby="modalImagenLabel" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <div class="modal-body">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        <img id="imagenModal" src="" alt="" class="img-fluid" style="width: 100%; height: auto;">
                    </div>
                </div>
            </div>
        </div>
    </main>



    <footer class="pt-1 bg-mode">
        <hr class="mb-0 mt-1">
        <div class="bg- light py-3">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <p class="text-center mb-0">©2026 <a class="text-body" target="_blank" href="https://www.facebook.com/ParroquiaSanPedrodeSacaba/">Parroquia San Pedro </a>de Sacaba.</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <script>
        function abrirModal(imagenSrc) {
            document.getElementById('imagenModal').src = imagenSrc;
        }
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var loginForm = document.getElementById("login-form");
            var forgotPasswordForm = document.getElementById("forgot-password-form");
            var forgotPasswordLink = document.getElementById("forgot-password-link");
            var backToLoginLink = document.getElementById("back-to-login-link");
            var forgotPasswordBlock = document.getElementById("forgot-password-block");

            forgotPasswordLink.addEventListener("click", function(e) {
                e.preventDefault();
                loginForm.style.display = "none";
                Menu.style.display = "None"
                Menu1.style.display = "None"
                forgotPasswordBlock.style.display = "block";
            });

            backToLoginLink.addEventListener("click", function(e) {
                e.preventDefault();
                loginForm.style.display = "block";
                Menu.style.display = "block"
                Menu1.style.display = "block"
                forgotPasswordBlock.style.display = "none";
            });
        });


        const showVideoButton = document.getElementById('showVideoButton');
        const videoContainer = document.getElementById('videoContainer');
        const closeVideoButton = document.getElementById('closeVideoButton');
        const videoPlayer = document.getElementById('videoPlayer');

        showVideoButton.addEventListener('click', function() {
            videoContainer.style.display = 'block';
            showVideoButton.style.display = 'none';
            videoPlayer.play();
        });

        closeVideoButton.addEventListener('click', function() {
            videoContainer.style.display = 'none';
            showVideoButton.style.display = 'block';
            videoPlayer.pause();
        });


        document.addEventListener('DOMContentLoaded', function() {
            document.documentElement.setAttribute('data-bs-theme', 'light');

            localStorage.setItem('theme', 'light');

            const modeSwitchButtons = document.querySelectorAll('.btn-modeswitch');
            modeSwitchButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const theme = this.getAttribute('data-bs-theme-value');
                    document.documentElement.setAttribute('data-bs-theme', theme);
                    localStorage.setItem('theme', theme);
                });
            });
        });
    </script>
    <script src="../assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/vendor/plyr/plyr.js"></script>
    <script src="../assets/vendor/tiny-slider/dist/tiny-slider.js"></script>
    <script src="../assets/js/functions.js"></script>
    <script src="../assets/js/tema.js"></script>
</body>

</html>