<?php
session_start();


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

        .bautizo-contenido,
        .bautizo-contenido h1,
        .bautizo-contenido h2,
        .bautizo-contenido h3,
        .bautizo-contenido h4,
        .bautizo-contenido h5,
        .bautizo-contenido h6,
        .bautizo-contenido p,
        .bautizo-contenido li,
        .bautizo-contenido strong,
        .bautizo-contenido span {
            color: #000000;
        }

        .bautizo-contenido {
            padding: 2.5rem;
            background: #f5f7fa;
            border-radius: 18px;
        }

        .bautizo-presentacion {
            display: grid;
            grid-template-columns: minmax(240px, 340px) 1fr;
            gap: 2.5rem;
            align-items: center;
            padding: 1.5rem;
            margin-bottom: 2.5rem;
            background: #ffffff;
            border: 1px solid #e2e7ee;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(15, 45, 82, 0.08);
        }

        .bautizo-imagen {
            width: 100%;
            max-width: 320px;
            aspect-ratio: 1 / 1;
            object-fit: cover;
            border: 5px solid #ffffff;
            border-radius: 50%;
            box-shadow: 0 8px 22px rgba(15, 45, 82, 0.18);
            justify-self: center;
        }

        .bautizo-presentacion h2 {
            margin-bottom: 1rem;
            font-size: clamp(1.75rem, 3vw, 2.35rem);
            font-weight: 700;
        }

        .bautizo-presentacion p {
            margin: 0;
            line-height: 1.75;
        }

        .bautizo-encabezado {
            margin-bottom: 1.75rem;
            text-align: center;
        }

        .bautizo-encabezado h2 {
            margin-bottom: .4rem;
            font-size: clamp(1.8rem, 3vw, 2.4rem);
            font-weight: 750;
        }

        .bautizo-requisitos {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1.25rem;
        }

        .bautizo-tarjeta {
            padding: 1.5rem;
            background: #ffffff;
            border: 1px solid #dfe5ec;
            border-top: 4px solid #12345b;
            border-radius: 14px;
            box-shadow: 0 6px 18px rgba(15, 45, 82, 0.07);
        }

        .bautizo-tarjeta h3 {
            display: flex;
            gap: .7rem;
            align-items: center;
            margin-bottom: 1rem;
            font-size: 1.08rem;
            font-weight: 700;
        }

        .bautizo-tarjeta h3 i {
            display: inline-grid;
            width: 2.25rem;
            height: 2.25rem;
            flex: 0 0 2.25rem;
            place-items: center;
            color: #ffffff;
            background: #12345b;
            border-radius: 50%;
        }

        .bautizo-lista {
            padding: 0;
            margin: 0;
            list-style: none;
        }

        .bautizo-lista li {
            position: relative;
            padding-left: 1.4rem;
            margin-bottom: .75rem;
            line-height: 1.55;
        }

        .bautizo-lista li:last-child {
            margin-bottom: 0;
        }

        .bautizo-lista li::before {
            position: absolute;
            top: .1rem;
            left: 0;
            color: #bd8700;
            content: "\25B8";
            font-weight: 700;
        }

        .bautizo-aviso {
            padding: .9rem 1rem;
            margin: 1rem 0 0;
            line-height: 1.5;
            background: #fff7df;
            border-left: 4px solid #d9a20b;
            border-radius: 8px;
        }

        .bautizo-horarios {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .8rem;
        }

        .bautizo-horario {
            padding: 1rem;
            text-align: center;
            background: #f2f6fb;
            border: 1px solid #d8e2ee;
            border-radius: 10px;
        }

        .bautizo-horario strong,
        .bautizo-horario span {
            display: block;
        }

        .bautizo-horario strong {
            margin-bottom: .25rem;
            font-size: 1.05rem;
        }

        .bautizo-notas {
            grid-column: 1 / -1;
            border-top-color: #d9a20b;
        }

        @media (max-width: 991.98px) {
            .bautizo-contenido {
                padding: 1.5rem;
            }

            .bautizo-presentacion {
                grid-template-columns: 1fr;
                text-align: center;
            }
        }

        @media (max-width: 767.98px) {
            .bautizo-contenido {
                padding: 1rem;
            }

            .bautizo-presentacion,
            .bautizo-requisitos {
                grid-template-columns: 1fr;
            }

            .bautizo-presentacion {
                gap: 1.5rem;
                padding: 1rem;
            }

            .bautizo-notas {
                grid-column: auto;
            }
        }

        @media (max-width: 420px) {
            .bautizo-horarios {
                grid-template-columns: 1fr;
            }
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
                            <a class="nav-link active dropdown-toggle" href="#" id="SacramentoMenu"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Sacramentos</a>
                            <ul class="dropdown-menu" aria-labelledby="SacramentoMenu">
                                <li> <a class="dropdown-item active" href="srcSacramentoBautizo.php">Bautizo</a></li>
                                <li> <a class="dropdown-item" href="srcSacramentoComunion.php">Primera Comunión</a></li>
                                <li> <a class="dropdown-item" href="srcSacramentoConfirmacion.php">Confirmación</a></li>
                                <li> <a class="dropdown-item" href="srcSacramentoMatrimonio.php">Matrimonio</a></li>
                                <li> <a class="dropdown-item" href="srcSacramentos.php">Todos los sacramentos</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="NosotrosMenu" data-bs-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">Nosotros</a>
                            <ul class="dropdown-menu" aria-labelledby="NosotrosMenu">
                                <li> <a class="dropdown-item" href="srcNosotrosParroquia.php">Parroquia</a></li>
                                <li> <a class="dropdown-item" href="srcNosotrosComunion.php">Primera Comunión</a></li>
                                <li> <a class="dropdown-item" href="srcNosotrosConfirmacion.php">Confirmación</a></li>
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
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-8 vstack gap-4">

                    <div class="card h-100">

                        <div class="card-body">

                            <section class="bautizo-contenido">
                                <div class="bautizo-presentacion">
                                    <img class="bautizo-imagen" src="../assets/images/post/Sacramentos/bautismo.jpg" alt="Celebración del sacramento del Bautismo">
                                    <div>
                                        <h2>¿Qué es el Bautismo?</h2>
                                        <p>Cuando los niños nacen, inmediatamente los papás les ponen un nombre, pues saben que es muy importante llamarlos de alguna manera para poder registrarlos y para que sean <strong>“alguien en especial”</strong>, con nombre y apellido, ciudadanos del país en que nacieron, con derechos y obligaciones. Sin embargo, no sucede lo mismo con el Bautismo; a veces no le damos la importancia que tiene o estamos confundidos porque escuchamos diferentes opiniones y nos olvidamos de que la Biblia nos dice: <strong>“El que crea y se bautice se salvará”.</strong> (Mc. 16, 16)</p>
                                    </div>
                                </div>

                                <div class="bautizo-encabezado">
                                    <h2>Requisitos para Bautizo</h2>
                                    <p>Todo lo que necesitas para realizar un Bautizo.</p>
                                </div>

                                <div class="bautizo-requisitos">
                                    <article class="bautizo-tarjeta">
                                        <h3><i class="bi bi-person-heart" aria-hidden="true"></i> Del niño o niña</h3>
                                        <ul class="bautizo-lista">
                                            <li>Certificado de Nacimiento.</li>
                                        </ul>
                                    </article>

                                    <article class="bautizo-tarjeta">
                                        <h3><i class="bi bi-people" aria-hidden="true"></i> De los padrinos (Deben ser católicos)</h3>
                                        <ul class="bautizo-lista">
                                            <li>Si son casados por lo religioso, presentar certificado de Matrimonio Religioso Católico y Carnet de Identidad (original y fotocopia).</li>
                                            <li>Si son solteros, ser mayores de edad y presentar certificado de Confirmación y Carnet de Identidad (original y fotocopia).</li>
                                        </ul>
                                        <p class="bautizo-aviso"><strong>No pueden ser padrinos</strong> quienes viven en concubinato, están casados solo por lo civil, son divorciados o no realizaron el sacramento de la Confirmación.</p>
                                    </article>

                                    <article class="bautizo-tarjeta">
                                        <h3><i class="bi bi-calendar-week" aria-hidden="true"></i> Días y horarios de Bautizos</h3>
                                        <div class="bautizo-horarios">
                                            <div class="bautizo-horario">
                                                <strong>Jueves</strong>
                                                <span>a hrs. 14:30</span>
                                            </div>
                                            <div class="bautizo-horario">
                                                <strong>Domingo</strong>
                                                <span>a hrs. 12:30</span>
                                            </div>
                                        </div>
                                    </article>

                                    <article class="bautizo-tarjeta">
                                        <h3><i class="bi bi-pencil-square" aria-hidden="true"></i> Inscripción</h3>
                                        <ul class="bautizo-lista">
                                            <li>Durante la semana, de manera presencial en la parroquia.</li>
                                        </ul>
                                    </article>

                                    <article class="bautizo-tarjeta bautizo-notas">
                                        <h3><i class="bi bi-info-circle" aria-hidden="true"></i> Información importante</h3>
                                        <ul class="bautizo-lista">
                                            <li>Los niños de 0 a 6 años no pasan cursillos.</li>
                                            <li>Los niños de 7 a 10 años pasan cursillos.</li>
                                            <li>Si los niños son mayores de 10 años, deben realizar durante un año la Catequesis del sacramento que les corresponde según su edad.</li>
                                        </ul>
                                    </article>
                                </div>
                            </section>

                        </div>
                    </div>
                </div>
            </div>
        </div>

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
                        <p class="text-center mb-0">©2026 <a class="text-body" target="_blank"
                                href="https://www.facebook.com/ParroquiaSanPedrodeSacaba/">Parroquia San Pedro </a>de
                            Sacaba.</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>
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
