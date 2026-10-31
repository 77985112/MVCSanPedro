<?php
session_start();


$diassemana = array("Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado");
$meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");


require_once '../Controlador/controladorSacramentoFecha.php';

$controller = new ControladorSacramento();
$sacramentos = $controller->Comunion();

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
    <link rel="stylesheet" type="text/css" href="../assets/css/sacramentos-publicos.css?v=<?= filemtime(__DIR__ . '/../assets/css/sacramentos-publicos.css') ?>">
    <style>
        .bg-mode {
            background-color: #ffffff;
            color: #000000;
        }

        .navbar-light .navbar-brand {
            color: #000000;
        }

        .event {
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #f9f9f9;
        }

        .event h2 {
            margin-top: 0;
        }

        .countdown {
            font-weight: bold;
            color: #d9534f;
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
                                <li> <a class="dropdown-item" href="srcSacramentoBautizo.php">Bautizo</a></li>
                                <li> <a class="dropdown-item active" href="srcSacramentoComunion.php">Primera Comunión</a></li>
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

                            <section class="sacramento-contenido">
                                <div class="sacramento-presentacion">
                                    <img class="sacramento-imagen" src="../assets/images/post/Sacramentos/comunion.jpg" alt="Celebración de la Primera Comunión">
                                    <div>
                                        <h2>¿Qué es la Primera Comunión?</h2>
                                        <p>La Primera Comunión es la celebración de la Iglesia Católica donde el niño y la niña participan por primera vez en el Sacramento de la Eucaristía, recibiendo la Comunión.</p>
                                        <p>La Sagrada Comunión es la presencia de Jesucristo en la Eucaristía, donde lo recibimos sacramentado bajo las especies del pan y del vino. <strong>“Yo soy el pan vivo, bajado del cielo. Si uno come de este pan, vivirá para siempre, y el pan que yo le voy a dar es mi carne por la vida del mundo”.</strong> (Jn 6, 51)</p>
                                    </div>
                                </div>

                                <div class="sacramento-encabezado">
                                    <h2>Requisitos para Primera Comunión</h2>
                                    <p>Todo lo que necesitas para realizar la Primera Comunión.</p>
                                </div>

                                <div class="sacramento-requisitos">
                                    <article class="sacramento-tarjeta">
                                        <h3><i class="bi bi-person-heart" aria-hidden="true"></i> Del niño o niña</h3>
                                        <ul class="sacramento-lista">
                                            <li>Certificado de Nacimiento.</li>
                                            <li>Certificado de Bautizo.</li>
                                        </ul>
                                    </article>

                                    <article class="sacramento-tarjeta">
                                        <h3><i class="bi bi-people" aria-hidden="true"></i> Sobre los padrinos</h3>
                                        <p>Para la Primera Comunión no es indispensable tener padrinos.</p>
                                        <p>Los padres de familia pueden acompañar al niño o niña ese día para celebrar juntos este sacramento.</p>
                                        <p class="sacramento-aviso">Si se solicitan padrinos, no pueden ser quienes viven en concubinato, están casados solo por lo civil, son divorciados o no realizaron el sacramento de la Confirmación.</p>
                                    </article>

                                    <article class="sacramento-tarjeta sacramento-tarjeta-ancha sacramento-agenda">
                                        <h3><i class="bi bi-calendar-event" aria-hidden="true"></i> Cuándo se realiza la Primera Comunión</h3>
                                        <div class="sacramento-eventos">
                                            <?php if (count($sacramentos) > 0): ?>
                                                <?php foreach ($sacramentos as $row): ?>
                                                    <?php
                                                    $fechaIni = strtotime($row['Fecha_Ini']);
                                                    $fechaFin = strtotime($row['Fecha_Fin']);

                                                    $diaSemanaIni = $diassemana[date('w', $fechaIni)];
                                                    $diaIni = date('d', $fechaIni);
                                                    $mesIni = $meses[date('n', $fechaIni) - 1];

                                                    $diaSemanaFin = $diassemana[date('w', $fechaFin)];
                                                    $diaFin = date('d', $fechaFin);
                                                    $mesFin = $meses[date('n', $fechaFin) - 1];
                                                    ?>

                                                    <?php if ($row['ActividadSac'] == 'Inscripción'): ?>
                                                        <div class="sacramento-evento">
                                                            <h4><i class="bi bi-pencil-square" aria-hidden="true"></i> Inscripción a Primera Comunión</h4>
                                                            <p><i class="bi bi-calendar3" aria-hidden="true"></i><span><strong>Fecha:</strong> <?php echo "$diaSemanaIni, $diaIni de $mesIni"; ?> a <?php echo "$diaSemanaFin, $diaFin de $mesFin"; ?></span></p>
                                                            <p><i class="bi bi-clock" aria-hidden="true"></i><span><strong>Hora:</strong> <?php echo htmlspecialchars(date('H:i', $fechaIni)); ?> a <?php echo htmlspecialchars(date('H:i', $fechaFin)); ?></span></p>
                                                            <p id="inscripcion<?php echo $row['idSacramento']; ?>" class="sacramento-estado"></p>
                                                        </div>

                                                    <?php elseif ($row['ActividadSac'] == 'Comunión'): ?>
                                                        <div class="sacramento-evento">
                                                            <h4><i class="bi bi-cup-hot" aria-hidden="true"></i> Celebración de Primera Comunión</h4>
                                                            <p><i class="bi bi-calendar3" aria-hidden="true"></i><span><strong>Fecha:</strong> <?php echo "$diaSemanaIni, $diaIni de $mesIni"; ?> a <?php echo "$diaSemanaFin, $diaFin de $mesFin"; ?></span></p>
                                                            <p><i class="bi bi-clock" aria-hidden="true"></i><span><strong>Hora:</strong> <?php echo htmlspecialchars(date('H:i', $fechaIni)); ?> a <?php echo htmlspecialchars(date('H:i', $fechaFin)); ?></span></p>
                                                            <p id="comunion<?php echo $row['idSacramento']; ?>" class="sacramento-estado"></p>
                                                        </div>

                                                    <?php endif; ?>
                                                <?php endforeach; ?>

                                                <script>
                                                    // Función para actualizar el estado según la fecha actual
                                                    function actualizarEstado(fechaInicio, fechaFin, elementoId) {
                                                        const fechaIni = new Date(fechaInicio).getTime();
                                                        const fechaFinal = new Date(fechaFin).getTime();
                                                        const ahora = new Date().getTime();

                                                        if (ahora < fechaIni) {
                                                            document.getElementById(elementoId).innerHTML = "Aún no está vigente.";
                                                        } else if (ahora > fechaFinal) {
                                                            document.getElementById(elementoId).innerHTML = "La actividad ha concluido.";
                                                        } else {
                                                            actualizarCuentaRegresiva(fechaFinal, elementoId);
                                                        }
                                                    }
                                                    function actualizarCuentaRegresiva(fechaObjetivo, elementoId) {
                                                        const fechaFinal = new Date(fechaObjetivo).getTime();

                                                        const intervalo = setInterval(function() {
                                                            const ahora = new Date().getTime();
                                                            const distancia = fechaFinal - ahora;

                                                            const dias = Math.floor(distancia / (1000 * 60 * 60 * 24));
                                                            const horas = Math.floor((distancia % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                                            const minutos = Math.floor((distancia % (1000 * 60 * 60)) / (1000 * 60));
                                                            const segundos = Math.floor((distancia % (1000 * 60)) / 1000);

                                                            document.getElementById(elementoId).innerHTML =
                                                                `${dias} días, ${horas} horas, ${minutos} minutos, ${segundos} segundos`;

                                                            if (distancia < 0) {
                                                                clearInterval(intervalo);
                                                                document.getElementById(elementoId).innerHTML = "¡Tiempo agotado!";
                                                            }
                                                        }, 1000);
                                                    }

                                                    // Aplicar las funciones a cada sacramento
                                                    <?php foreach ($sacramentos as $row): ?>
                                                        actualizarEstado(
                                                            "<?php echo $row['Fecha_Ini']; ?>",
                                                            "<?php echo $row['Fecha_Fin']; ?>",
                                                            "<?php echo $row['ActividadSac'] == 'Inscripción' ? 'inscripcion' . $row['idSacramento'] : 'comunion' . $row['idSacramento']; ?>"
                                                        );
                                                    <?php endforeach; ?>
                                                </script>

                                            <?php else: ?>
                                                <p>No hay fechas disponibles.</p>
                                            <?php endif; ?>
                                        </div>
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
