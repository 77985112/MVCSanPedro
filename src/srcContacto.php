<?php
session_start();
require_once '../Controlador/controladorSMS.php';

$controlador = new ControladorMensaje();
$resultado = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resultado = $controlador->registrarMensaje();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
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
    <link rel="stylesheet" type="text/css" href="../assets/vendor/tiny-slider/dist/tiny-slider.css">
    <link rel="stylesheet" type="text/css" href="../assets/vendor/plyr/plyr.css">
    <link rel="stylesheet" type="text/css" href="../assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="../assets/css/logo-cabecera.css?v=<?= filemtime(__DIR__ . '/../assets/css/logo-cabecera.css') ?>">
    <link rel="stylesheet" type="text/css" href="../assets/css/Index.css?v=<?= filemtime(__DIR__ . '/../assets/css/Index.css') ?>">
    <link rel="stylesheet" type="text/css" href="../assets/css/contacto-publico.css?v=<?= filemtime(__DIR__ . '/../assets/css/contacto-publico.css') ?>">
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

        <section class="contacto-pagina">
            <div class="container">

                <div class="contacto-cabecera">
                    <span class="contacto-eyebrow"><i class="bi bi-chat-heart" aria-hidden="true"></i> Estamos cerca de ti</span>
                    <h1>Contáctate con nosotros</h1>
                    <div class="contacto-adorno" aria-hidden="true"><i class="bi bi-cross"></i></div>
                    <p>Estamos para escucharte, orientarte y acompañarte. Visítanos en el centro de Sacaba o comunícate directamente con la parroquia.</p>
                </div>

                <div class="contacto-grid">
                    <article class="contacto-card">
                        <h2 class="contacto-card-titulo"><i class="bi bi-geo-alt-fill" aria-hidden="true"></i> Visítanos</h2>
                        <p class="contacto-card-subtitulo">Conoce nuestra ubicación y forma parte de la comunidad parroquial.</p>
                        <iframe class="contacto-mapa"
                            title="Ubicación de la Parroquia San Pedro de Sacaba"
                            src="https://www.google.com/maps?q=Parroquia+San+Pedro+de+Sacaba,+Plaza+6+de+Agosto,+Sacaba,+Bolivia&amp;output=embed"
                            loading="lazy"
                            allowfullscreen
                            referrerpolicy="no-referrer-when-downgrade"></iframe>
                        <p class="contacto-direccion"><i class="bi bi-geo-alt-fill" aria-hidden="true"></i><span>Plaza Principal 6 de Agosto, lado Este, Parroquia San Pedro de Sacaba.</span></p>
                        <div class="contacto-acciones-mapa">
                            <a class="contacto-boton contacto-boton-primario" href="https://maps.app.goo.gl/72ZYHKxSxeMV9Hkj9" target="_blank" rel="noopener noreferrer"><i class="bi bi-sign-turn-right" aria-hidden="true"></i> Cómo llegar</a>
                            <a class="contacto-boton contacto-boton-secundario" href="https://maps.app.goo.gl/72ZYHKxSxeMV9Hkj9" target="_blank" rel="noopener noreferrer"><i class="bi bi-box-arrow-up-right" aria-hidden="true"></i> Ver en Google Maps</a>
                        </div>
                    </article>

                    <article class="contacto-card">
                        <h2 class="contacto-card-titulo"><i class="bi bi-chat-heart" aria-hidden="true"></i> Estamos para ayudarte</h2>
                        <p class="contacto-card-subtitulo">Puedes comunicarte con nosotros por los siguientes medios.</p>

                        <div class="contacto-datos">
                            <div class="contacto-dato contacto-dato-whatsapp">
                                <i class="bi bi-whatsapp" aria-hidden="true"></i>
                                <div><strong>WhatsApp</strong><span>77985112<br>Número temporal de prueba</span></div>
                            </div>
                            <div class="contacto-dato">
                                <i class="bi bi-telephone" aria-hidden="true"></i>
                                <div><strong>Teléfono</strong><span>44701041<br>Parroquia San Pedro</span></div>
                            </div>
                            <div class="contacto-dato contacto-horario">
                                <i class="bi bi-clock" aria-hidden="true"></i>
                                <div>
                                    <strong>Horario de atención</strong>
                                    <span>Lunes, miércoles, jueves y viernes: 08:00–12:00 y 14:00–18:00.<br>Martes: 08:00–12:00; por la tarde no hay atención.<br>Sábado y domingo: atención hasta las 12:00.</span>
                                </div>
                            </div>
                        </div>

                        <div class="contacto-whatsapp">
                            <i class="bi bi-whatsapp" aria-hidden="true"></i>
                            <div class="contacto-whatsapp-texto">
                                <strong>Conversa directamente con la parroquia</strong>
                                <span>Te responderemos durante el horario de atención.</span>
                            </div>
                            <a class="contacto-boton contacto-boton-whatsapp" href="https://wa.me/59177985112?text=Hola%2C%20Parroquia%20San%20Pedro%20de%20Sacaba.%20Deseo%20realizar%20una%20consulta." target="_blank" rel="noopener noreferrer"><i class="bi bi-whatsapp" aria-hidden="true"></i> Escribir</a>
                        </div>
                    </article>
                </div>

                <article class="contacto-card contacto-formulario-card">
                    <div class="contacto-form-layout">
                        <div class="contacto-form-intro">
                            <h2 class="contacto-card-titulo"><i class="bi bi-pencil-square" aria-hidden="true"></i> Prepara tu consulta</h2>
                            <p class="contacto-card-subtitulo">Completa los datos y elige si deseas abrir el mensaje en WhatsApp o guardarlo como consulta interna.</p>
                            <p class="contacto-nota"><i class="bi bi-info-circle" aria-hidden="true"></i> WhatsApp se abrirá con el mensaje preparado; tú decidirás cuándo enviarlo.</p>
                        </div>

                        <form action="" method="POST" id="formulario-contacto" class="contacto-formulario">
                            <div class="contacto-form-grid">
                                <div>
                                    <label for="contacto-nombre">Nombre completo</label>
                                    <input type="text" id="contacto-nombre" name="nombre" class="form-control" placeholder="Tu nombre completo" required>
                                </div>
                                <div>
                                    <label for="contacto-telefono">Teléfono de contacto</label>
                                    <input type="tel" id="contacto-telefono" name="numcorr" class="form-control" placeholder="Tu número de contacto" inputmode="numeric" required>
                                </div>
                                <div class="contacto-campo-ancho">
                                    <label for="contacto-concepto">Motivo de la consulta</label>
                                    <input type="text" id="contacto-concepto" name="concepto" class="form-control" placeholder="Ej.: consulta sobre Bautizo" required>
                                </div>
                                <div class="contacto-campo-ancho">
                                    <label for="contacto-detalle">Mensaje</label>
                                    <textarea id="contacto-detalle" class="form-control" name="detalle" rows="5" placeholder="Escribe aquí tu consulta" required></textarea>
                                </div>
                            </div>
                            <div class="contacto-form-acciones">
                                <button type="button" id="preparar-whatsapp" class="contacto-boton contacto-boton-whatsapp"><i class="bi bi-whatsapp" aria-hidden="true"></i> Preparar en WhatsApp</button>
                                <button type="submit" name="submit" class="contacto-boton contacto-boton-secundario"><i class="bi bi-inbox" aria-hidden="true"></i> Guardar consulta</button>
                            </div>
                        </form>
                    </div>
                </article>

                <?php if ($resultado) { ?>
                    <p class="mt-3 text-success"><?php echo htmlspecialchars($resultado); ?></p>
                <?php } ?>

                <div class="contacto-ventajas">
                    <div class="contacto-ventaja">
                        <i class="bi bi-patch-check-fill" aria-hidden="true"></i>
                        <div><strong>Ubicación verificada</strong><span>En el centro de Sacaba.</span></div>
                    </div>
                    <div class="contacto-ventaja">
                        <i class="bi bi-people-fill" aria-hidden="true"></i>
                        <div><strong>Atención parroquial</strong><span>Estamos para escucharte y orientarte.</span></div>
                    </div>
                    <div class="contacto-ventaja">
                        <i class="bi bi-whatsapp" aria-hidden="true"></i>
                        <div><strong>Respuesta por WhatsApp</strong><span>Durante el horario de atención.</span></div>
                    </div>
                </div>

            </div>
        </section>

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
    <script src="../assets/js/contacto-whatsapp.js?v=<?= filemtime(__DIR__ . '/../assets/js/contacto-whatsapp.js') ?>"></script>
<?php if (isset($_GET['error_login'])): ?>
<script>
    (function () {
        var formulario = document.getElementById('login-form');
        var modalEl = formulario ? formulario.closest('.modal') : null;
        if (!modalEl) { return; }
        if (window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
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
