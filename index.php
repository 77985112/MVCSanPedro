<?php
session_start();
require_once 'Controlador/controladorCatequistas.php';
$controller = new CatequistaController();

$CatequistasT = $controller->mostrarCatequistasActivos();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <title>San Pedro de Sacaba</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="shortcut icon" href="assets/images/Icono.png">
    <link rel="stylesheet" type="text/css" href="assets/vendor/font-awesome/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="assets/vendor/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" type="text/css" href="assets/vendor/tiny-slider/dist/tiny-slider.css">
    <link rel="stylesheet" type="text/css" href="assets/vendor/plyr/plyr.css">
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="assets/css/Index.css">
    <link rel="stylesheet" type="text/css" href="assets/css/loader.css">
    <style>
        .bg-mode {
            background-color: #ffffff;
            color: #000000;
        }

        .navbar-light .navbar-brand {
            color: #000000;
        }
    </style>

</head>

<body data-bs-theme="light">
    <div id="loader">
        <div class="book">
            <div class="inner">
                <div class="left"></div>
                <div class="middle"></div>
                <div class="right"></div>
            </div>
            <ul>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
            </ul>
            <h1>San Pedro</h1>
        </div>
    </div>
    <header class="navbar-light fixed-top header-static bg-mode">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="index.php">
                    <img class="light-mode-item navbar-brand-item" src="assets/images/logo.png" alt="logo">
                    <img class="dark-mode-item navbar-brand-item" src="assets/images/logo-light.png" alt="logo">
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
                            <a class="nav-link active" href="index.php">Inicio</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="src/srcActividadGeneral.php">Actividades</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="SacramentoMenu" data-bs-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">Sacramentos</a>
                            <ul class="dropdown-menu" aria-labelledby="SacramentoMenu">
                                <li> <a class="dropdown-item" href="src/srcSacramentoBautizo.php">Bautizo</a></li>
                                <li> <a class="dropdown-item" href="src/srcSacramentoComunion.php">Primera Comunión</a></li>
                                <li> <a class="dropdown-item" href="src/srcSacramentoConfirmacion.php">Confirmación</a></li>
                                <li> <a class="dropdown-item" href="src/srcSacramentoMatrimonio.php">Matrimonio</a></li>
                                <li> <a class="dropdown-item" href="src/srcSacramentos.php">Todos los sacramentos</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="NosotrosMenu" data-bs-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">Nosotros</a>
                            <ul class="dropdown-menu" aria-labelledby="NosotrosMenu">
                                <li> <a class="dropdown-item" href="src/srcNosotrosParroquia.php">Parroquia</a></li>
                                <li> <a class="dropdown-item" href="src/srcNosotrosComunion.php">Primera Comunión</a></li>
                                <li> <a class="dropdown-item" href="src/srcNosotrosConfirmacion.php">Confirmación</a></li>
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
                    <a type="button" href="src/srcContacto.php" class="btn btn-primary-soft">
                        <i class="bi bi-chat-left-dots"></i>
                    </a>
                </div>
            </div>
        </nav>
    </header>


    <main>
        <section class="hero-banner-1">

            <div class="container">
                <div class="content">
                    <div class="text_block wow fadeInUp" data-wow-delay="800ms">
                        <div class="row">
                            <div class="col-xl-6 col-lg-8">

                                <div class="row g-4 align-items-center position-relative z-index-1">
                                    <div class="col-md-6">
                                        <div class="me-4">
                                            <button id="showVideoButton"
                                                class="btn btn-primary icon-md rounded-circle playerbtn"><i
                                                    class="bi bi-play-fill"></i></button>
                                            <div id="videoContainer" class="player-wrapper rounded-3"
                                                style="display:none;">
                                                <button id="closeVideoButton"
                                                    class="btn btn-danger icon-md rounded-circle mb-3"> <i
                                                        class="bi bi-x-lg"></i></button>
                                                <video id="videoPlayer" class="player-html" controls>
                                                    <source src="assets/images/videos/Templo_San Pedro.mp4"
                                                        type="video/mp4">
                                                </video>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>


                <div class="banner_feature_card">
                    <div class="card_block">
                        <h6 class="fm-sec"> - Salmos 25:4-21</h6>
                        <p>Señor, muéstrame tus caminos, enséñame tus sendas.</p>
                        <img src="assets/media/shapes/target-2.png" alt="" class="target_icon">
                    </div>
                </div>
                <img src="assets/media/banners/hero-banner-1/main-img.png" alt="" class="main-img">
            </div>
        </section>

        <section class="py-4 py-sm-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-12 text-center mb-4">
                        <h2 class="h1">Nuestras principales actividades</h2>
                        <p>Conoce nuestras principales actividades de nuestra parroquia.</p>
                    </div>
                </div>
                <div class="row g-4 g-lg-5">
                    <div class="col-md-3 text-center">
                        <img class="h-200px mb-4" src="assets/images/elements/Bautizo.png" alt="">
                        <h4><a href="src/srcSacramentoBautizo.php">Bautizo</a></h4>
                        <p class="mb-0">Etapa donde una persona inicia en el camino de la fe.</p>
                    </div>
                    <div class="col-md-3 text-center">
                        <img class="h-200px mb-4" src="assets/images/elements/PrimeraComunion.png" alt="">
                        <h4><a href="src/srcSacramentoComunion.php">Primera Comunión</a></h4>
                        <p class="mb-0">Tiempo en el cual se prepara para el pimer encuentro con Cristo.</p>
                    </div>
                    <div class="col-md-3 text-center">
                        <img class="h-200px mb-4" src="assets/images/elements/Confirmacion.png" alt="">
                        <h4><a href="src/srcSacramentoConfirmacion.php">Confirmación</a></h4>
                        <p class="mb-0">Preparación y fortalecimiento espiriual para confirmar la Fé.</p>
                    </div>
                    <div class="col-md-3 text-center">
                        <img class="h-200px mb-4" src="assets/images/elements/Matrimonio.png" alt="">
                        <h4><a href="src/srcSacramentoMatrimonio.php">Matrimonio</a></h4>
                        <p class="mb-0">Ceremonia donde dos personas se unen por la voluntad de Dios.</p>
                    </div>
                </div>
            </div>
        </section>


        <section class="py-4 py-sm-5 bg-light">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-12 text-center mb-4">
                        <h2 class="h1">Horarios de Atención y Misas</h2>
                        <p>Consulta los horarios para reservar ceremonias y los horarios de misas en nuestra parroquia.</p>
                    </div>
                </div>
                <div class="row g-4 g-lg-5">
                    <div class="col-md-6">
                        <div class="card card-body bg-white shadow-sm border-0">
                            <h4 class="mb-3">Horarios de Atención</h4>
                            <p><strong>Reservas para Bautizo y Matrimonio:</strong></p>
                            <ul class="list-unstyled">
                                <li><i class="bi bi-clock-fill me-2"></i> Lunes a Viernes: 9:00 AM - 1:00 PM / 3:00 PM - 6:00 PM</li>
                                <li><i class="bi bi-clock-fill me-2"></i> Sábado: 9:00 AM - 12:00 PM</li>
                                <li><i class="bi bi-x-circle-fill me-2"></i> Domingo: Cerrado</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-body bg-white shadow-sm border-0">
                            <h4 class="mb-3">Horarios de Misas</h4>
                            <ul class="list-unstyled">
                                <li><i class="bi bi-calendar3 me-2"></i> Lunes a Viernes: 7:00 AM / 12:00 PM / 7:00 PM</li>
                                <li><i class="bi bi-calendar3 me-2"></i> Sábado: 7:00 AM / 5:00 PM (Misa de Vigilia)</li>
                                <li><i class="bi bi-calendar3 me-2"></i> Domingo: 8:00 AM / 10:00 AM / 12:00 PM / 6:00 PM</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="py-4 py-sm-5 bg-light d-flex align-items-center justify-content-between">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-12 text-center mb-4">
                        <h2 class="h1">Nuestros Catequistas</h2>
                    </div>
                </div>


                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center border-0 pb-0">
                        <h3 class="card-title mb-0">Conoce a nuestros Catequistas</h3>
                    </div>

                    <div class="card-body">
                        <div class="tiny-slider arrow-hover">
                            <div class="tiny-slider-inner ms-n4" data-arrow="true" data-dots="false" data-items-xl="4" data-items-lg="3" data-items-md="2" data-items-sm="2" data-items-xs="1" data-gutter="12" data-edge="30">
                                <?php if (!empty($CatequistasT)) : ?>
                                    <?php foreach ($CatequistasT  as $catequista) : ?>
                                        <div>
                                            <div class="card shadow-none text-center">
                                                <div class="card-body p-1 pb-0">
                                                    <div class="avatar avatar-xl">
                                                        <img class="avatar-img rounded-circle" src="<?php echo ($catequista['ImagenCat'] == 'Ninguno' || empty($catequista['ImagenCat'])) ? 'assets/images/avatar/Ninguno.png' : "assets/images/avatar/{$catequista['ImagenCat']}"; ?>" alt="">
                                                    </div>
                                                    <h6 class="card-title mb-1 mt-3"> <?php echo $catequista['NombreCompleto']; ?></h6>

                                                    <p class="mb-0 small lh-sm"><?php echo $catequista['Sacramento']; ?></p>
                                                </div>
                                                <div class="card-footer p-2 border-0">
                                                    <form action="src/srcCatequistaPer.php" method="POST">
                                                        <input type="hidden" name="CiCat" value="<?php echo $catequista['CiCat']; ?>">
                                                        <input type="hidden" name="SacAdm" value="<?php echo $catequista['Sacramento']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-primary-soft w-100">Ver informacion rápida</button>
                                                    </form>
                                                </div>

                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <p>No se encontraron catequistas activos.</p>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>



        <section class="py-2 py-sm-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-12 text-center mb-4">
                        <h2 class="h2">Información General</h2>
                    </div>
                </div>

                <div class="row g-4 g-lg-5 align-items-center">
                    <div class="col-lg-4">
                        <h2 class="h1">Nuestras inscripciones</h2>
                        <p class="mb-4">¿Tienes todo lo necesario para inscribirte?</p>
                        <a class="btn btn-dark" href="src/srcActividadGeneral.php">Mira nuestras actividades</a>
                    </div>
                    <div class="col-lg-8">
                        <div class="card card-body bg-mode shadow-none border-0 p-4 p-sm-5 pb-sm-0 overflow-hidden">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <img class="h-50px" src="assets/media/icons/3dtag.png" alt="">
                                    <h4 class="mt-4">¿Conoces los sacramentos?</h4>
                                    <p class="mb-0">Los sacramentos son signos sensibles (palabras y acciones), accesibles a nuestra humanidad, a través de los cuales Cristo actúa y nos comunica su gracia.</p>
                                </div>
                                <div class="col-md-6 text-end">
                                    <div class="iphone-x-half mb-n1 mt-0">
                                        <img src="assets/images/mockup/Introdux.png" />

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="col-md-3 col-lg-3">
                        <div class="card card-body bg-mode shadow-none border-0 p-2 p-lg-2">
                            <div>
                                <img class="h-50px" src="assets/media/resources/BautizoInf.png" alt="">
                            </div>
                            <h6 class="mt-4"><a href="src/srcSacramentoBautizo.php">Requisitos para Bautizo</a></h6>
                            <hr>
                            <p class="mb-0">1.- Tener menos de 10 años</p>
                            <p class="mb-0">2.- Certificado de Nacimiento</p>
                            <p class="mb-0">3.- Padrino (os)</p>
                            <br>
                            <br>
                        </div>
                    </div>

                    <div class="col-md-3 col-lg-3">
                        <div class="card card-body bg-mode shadow-none border-0 p-2 p-lg-2">
                            <div>
                                <img class="h-50px" src="assets/media/resources/ComunionInf.png" alt="">
                            </div>
                            <h6 class="mt-4"><a href="src/srcSacramentoComunion.php">Requisitos para Comunión</a></h6>
                            <hr>
                            <p class="mb-0">1.- Tener 10 años cumplidos</p>
                            <p class="mb-0 text-center">- Fotocopias -</p>
                            <p class="mb-0">2.- Certificado de Nacimiento</p>
                            <p class="mb-0">3.- Certificado de Bautizo</p>
                            <br>
                        </div>
                    </div>

                    <div class="col-md-3 col-lg-3">
                        <div class="card card-body bg-mode shadow-none border-0 p-2 p-lg-2">
                            <div>
                                <img class="h-50px" src="assets/media/resources/ConfirmacionInf.png" alt="">
                            </div>
                            <h6 class="mt-4"><a href="src/srcSacramentoConfirmacion.php">Requisitos para Confirmación</a></h6>
                            <hr>
                            <p class="mb-0">1.- Tener 14 años cumplidos</p>
                            <p class="mb-0 text-center"> - Fotocopias -</p>
                            <p class="mb-0">2.- Certificado de Nacimiento</p>
                            <p class="mb-0">3.- Certificado de Bautizo</p>
                            <br>
                        </div>
                    </div>
                    <div class="col-md-3 col-lg-3">
                        <div class="card card-body bg-mode shadow-none border-0 p-2 p-lg-2">
                            <div>
                                <img class="h-50px" src="assets/media/resources/MatrimonioInf.png" alt="">
                            </div>
                            <h6 class="mt-4"><a href="src/srcSacramentoMatrimonio.php">Requisitos para Matrimonio</a></h6>
                            <hr>
                            <p class="mb-0">1.- Carnet de Identidad</p>
                            <p class="mb-0">2.- Certificado de Nacimiento</p>
                            <p class="mb-0">3.- Certificado de Bautizo</p>
                            <p class="mb-0">4.- Certificado de Confirmación</p>
                            <br>
                        </div>
                    </div>
                    <div class="col-lg-12 mx-auto text-center">
                        <p class="lead mb-4">Conoce todos los detalles de fechas y requisitos necesarios en nuestra pestaña de <strong>'Sacramentos'</strong>...</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-2 py-sm-5">
            <div class="container">
                <div class="card card-body bg-light shadow-none border-0 p-2 p-sm-2 text-center">
                    <div class="col-lg-12 mx-auto">
                        <h2 class="h1">Parroquia "San Pedro de Sacaba"</h2>
                        <p class="lead mb-4">Nuestra Parroquia, fué creada para todos los feligreses, fieles y laicos de nuestra jurisdicción y Vicaría Foránea de Sacaba.</p>
                        <ul class="nav nav-divider justify-content-center mt-4">
                            <li class="nav-item"> Dios, sana los de corazón quebrantado y les venda sus heridas. </li>
                            <li class="nav-item"> No hay nada mejor que estar en la fe.</li>
                            <li class="nav-item"> Hemos de ir a Misa. </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>


        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Ingrese sus datos</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <?php
                    if (isset($_SESSION['usuario']) || isset($_SESSION['CiPersona'])) {
                        echo '<div class="modal-body">';
                        echo '<h2 class="mb-48 text-center">Oops..</h2>';
                        echo '<h6 class="mb-48 text-center">Ya tienes una sesion activa</h6><br>';
                        echo '<a href="logout.php" class="btn btn-lg btn-danger-soft"> Cerrar Session</a>';
                        echo '</div>';
                    } else {
                    ?>
                        <div class="modal-body">
                            <form action="Controlador/controladorLogin.php" method="POST" class="form-validator"
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
        var catequistasCarousel = document.getElementById('catequistasCarousel');
        if (catequistasCarousel) {
            var carousel = new bootstrap.Carousel(catequistasCarousel, {
                interval: 5000,
                wrap: true
            });
        }
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var loginForm = document.getElementById("login-form");
            var forgotPasswordForm = document.getElementById("forgot-password-form");
            var forgotPasswordLink = document.getElementById("forgot-password-link");
            var backToLoginLink = document.getElementById("back-to-login-link");
            var forgotPasswordBlock = document.getElementById("forgot-password-block");
            var Menu = document.getElementById("Menu");
            var Menu1 = document.getElementById("Menu1");

            if (forgotPasswordLink) {
                forgotPasswordLink.addEventListener("click", function(e) {
                    e.preventDefault();
                    if (loginForm) loginForm.style.display = "none";
                    if (Menu) Menu.style.display = "None";
                    if (Menu1) Menu1.style.display = "None";
                    if (forgotPasswordBlock) forgotPasswordBlock.style.display = "block";
                });
            }

            if (backToLoginLink) {
                backToLoginLink.addEventListener("click", function(e) {
                    e.preventDefault();
                    if (loginForm) loginForm.style.display = "block";
                    if (Menu) Menu.style.display = "block";
                    if (Menu1) Menu1.style.display = "block";
                    if (forgotPasswordBlock) forgotPasswordBlock.style.display = "none";
                });
            }
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
    <script src="assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/plyr/plyr.js"></script>
    <script src="assets/vendor/tiny-slider/dist/tiny-slider.js"></script>
    <script src="assets/js/functions.js"></script>
    <script src="assets/js/tema.js"></script>
    <script src="assets/js/loader.js"></script>
</body>

</html>