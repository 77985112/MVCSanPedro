<?php
session_start();
require_once '../Controlador/controladorTodosCatSac.php';
$controller = new ContCatequistaSac();

$catequistasCom = $controller->mostrarCatConf();
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
    <link rel="stylesheet" type="text/css" href="../assets/css/logo-cabecera.css?v=<?= filemtime(__DIR__ . '/../assets/css/logo-cabecera.css') ?>">
    <link rel="stylesheet" type="text/css" href="../assets/css/Index.css?v=<?= filemtime(__DIR__ . '/../assets/css/Index.css') ?>">
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

    <header class="navbar-light fixed-top header-static bg-mode cabecera-publica">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="../index.php">
                    <img class="light-mode-item navbar-brand-item" src="../assets/images/logo.png?v=<?= filemtime(__DIR__ . '/../assets/images/logo.png') ?>" alt="logo">
                    <img class="dark-mode-item navbar-brand-item" src="../assets/images/logo-light.png?v=<?= filemtime(__DIR__ . '/../assets/images/logo-light.png') ?>" alt="logo">
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
                            <a class="nav-link" href="srcActividadGeneral.php">Actividades</a>
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
                            <a class="nav-link active dropdown-toggle" href="#" id="NosotrosMenu" data-bs-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">Nosotros</a>
                            <ul class="dropdown-menu" aria-labelledby="NosotrosMenu">
                                <li> <a class="dropdown-item" href="srcNosotrosParroquia.php">Parroquia</a></li>
                                <li> <a class="dropdown-item" href="srcNosotrosComunion.php">Primera Comunión</a></li>
                                <li> <a class="dropdown-item active" href="srcNosotrosConfirmacion.php">Confirmación</a></li>
                            </ul>
                        </li>

                    </ul>
                </div>
                <div class="cabecera-acceso">
                    <button type="button" class="btn cabecera-ingresar" aria-label="Iniciar sesión" data-bs-toggle="modal"
                        data-bs-target="#exampleModal">
                        <i class="bi bi-person" aria-hidden="true"></i><span>Ingresar</span>
                    </button>
                </div>
                <div class="cabecera-contacto">
                    <a type="button" href="srcContacto.php" class="btn cabecera-mensajes" aria-label="Contactos">
                        <i class="bi bi-chat-left-dots" aria-hidden="true"></i><span>Contactos</span>
                    </a>
                </div>
            </div>
        </nav>
    </header>


    <main>


        <section class="py-4 py-sm-5 bg-light d-flex align-items-center justify-content-between">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-12 text-center mb-4">
                        <h2 class="h1">Nuestros Catequistas de 'Confirmación'</h2>
                        <p class="card-title mb-0">Conoce a nuestros Catequistas de Confirmación (Grupo y Cargo)</p>
                    </div>
                </div>
                <br>

                <div class="container">
                    <div class="row">
                        <?php if (!empty($catequistasCom)) : ?>
                            <?php foreach ($catequistasCom as $catequista) : ?>
                                <div class="col-lg-3 col-md-4 col-sm-6 mb-4 catequista-card">
                                    <div class="card shadow-none text-center">
                                        <div class="card-body p-1 pb-0">
                                            <div class="avatar avatar-xl">
                                                <img class="avatar-img rounded-circle" src="<?php echo ($catequista['ImagenCat'] == 'Ninguno' || empty($catequista['ImagenCat'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$catequista['ImagenCat']}?v=" . time(); ?>" alt="">
                                            </div>
                                            <p class="mb-1 mt-3"><strong><?php echo $catequista['NombreCompleto']; ?></strong></p>
                                            <div class="hstack gap-2 gap-xl-3 justify-content-center">
                                                <div>
                                                    <h6 class="mb-0"><?php echo $catequista['RolCat']; ?></h6>
                                                    <small>Cargo</small>
                                                </div>
                                                <div class="vr"></div>
                                                <div>
                                                    <h6 class="mb-0"><?php echo $catequista['NombreGrupo']; ?></h6>
                                                    <small>Grupo</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer p-2 border-0">
                                            <form action="srcCatequistaPer.php" method="POST">
                                                <input type="hidden" name="CiCat" value="<?php echo $catequista['CiCat']; ?>">
                                                <input type="hidden" name="SacAdm" value="<?php echo $catequista['Sacramento']; ?>">
                                                <button type="submit" class="btn btn-sm btn-primary-soft w-100">Ver información rápida</button>
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
        </section>

    </main>



    <div class="modal fade login-modal" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-describedby="login-description" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <button type="button" class="login-close" data-bs-dismiss="modal" aria-label="Cerrar inicio de sesión">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
                <div class="modal-body login-panel">
                    <img class="login-logo" src="../assets/images/logo/logParroSP.png?v=<?= filemtime(__DIR__ . '/../assets/images/logo/logParroSP.png') ?>" alt="Parroquia San Pedro">
                    <div class="login-divider" aria-hidden="true"><span></span></div>
                    <h2 class="modal-title login-title" id="exampleModalLabel">INICIAR SESIÓN</h2>
                    <?php if (isset($_SESSION['usuario']) || isset($_SESSION['CiPersona'])) { ?>
                        <p class="login-description" id="login-description">Ya tienes una sesión activa.</p>
                        <a href="../logout.php" class="login-submit login-session-exit">
                            Cerrar sesión <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                        </a>
                    <?php } else { ?>
                        <p class="login-description" id="login-description">Ingresa tus datos para continuar.</p>
                        <form action="../Controlador/controladorLogin.php" method="POST" class="form-validator" id="login-form">
                            <div class="login-field">
                                <label for="username" class="login-label">Usuario</label>
                                <div class="login-input-wrap">
                                    <i class="bi bi-person login-field-icon" aria-hidden="true"></i>
                                    <input type="text" class="form-control login-input" placeholder="Escribe tu usuario" id="username" name="username" autocomplete="username" autocapitalize="none" spellcheck="false" required>
                                </div>
                            </div>
                            <?php if (($_GET['error_login'] ?? '') === 'usuario'): ?>
                                <p class="login-field-error" role="alert"><i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i> Usuario incorrecto.</p>
                            <?php endif; ?>
                            <div class="login-field">
                                <label for="password" class="login-label">Contraseña</label>
                                <div class="login-input-wrap">
                                    <i class="bi bi-lock login-field-icon" aria-hidden="true"></i>
                                    <input type="password" class="form-control login-input login-password" placeholder="Escribe tu contraseña" id="password" name="password" autocomplete="current-password" required>
                                    <button type="button" class="login-password-toggle" aria-label="Mostrar contraseña" aria-controls="password" aria-pressed="false">
                                        <i class="bi bi-eye-slash" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                            <?php if (($_GET['error_login'] ?? '') === 'contrasena'): ?>
                                <p class="login-field-error" role="alert"><i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i> Contraseña incorrecta.</p>
                            <?php endif; ?>
                            <button type="submit" class="login-submit">
                                Ingresar <i class="bi bi-arrow-right" aria-hidden="true"></i>
                            </button>
                        </form>
                    <?php } ?>
                    <div class="login-footer">
                        <div class="login-divider login-divider-small" aria-hidden="true"><span></span></div>
                        <p>Parroquia San Pedro de Sacaba</p>
                    </div>
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
        var catequistasCarousel = document.getElementById('catequistasCarousel');
        var carousel = new bootstrap.Carousel(catequistasCarousel, {
            interval: 5000,
            wrap: true
        });
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
    <script src="../assets/js/acceso.js?v=<?= filemtime(__DIR__ . '/../assets/js/acceso.js') ?>" defer></script>
<?php if (isset($_GET['error_login'])): ?>
<script>
    (function () {
        var formulario = document.getElementById('login-form');
        var modalEl = formulario ? formulario.closest('.modal') : null;
        if (!modalEl) { return; }
        if (window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
        // El aviso se ve solo en este primer intento fallido: se elimina al
        // cerrar el modal y se limpia el parámetro de la URL.
        modalEl.addEventListener('hidden.bs.modal', function () {
            var avisos = modalEl.querySelectorAll('.login-field-error');
            for (var i = 0; i < avisos.length; i++) { avisos[i].remove(); }
        });
        if (window.history && window.history.replaceState) {
            var url = new URL(window.location.href);
            url.searchParams.delete('error_login');
            window.history.replaceState(null, '', url.pathname + url.search + url.hash);
        }
    })();
</script>
<?php endif; ?>
</body>

</html>