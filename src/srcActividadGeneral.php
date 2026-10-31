<?php
session_start();
require_once '../Controlador/controladorActividad.php';

$controller = new ControladorActividad();
// Actividades públicas: más antigua publicada primero (Actividad 1, 2, 3...).
$actividades = array_values($controller->obtenerActividades(null, 'publicacion_asc'));
$totalActividades = count($actividades);
$grupo = 'todos';
$listaActividades = $actividades;
$totalGrupo = $totalActividades;
$indicePrincipal = $actividades ? array_key_last($actividades) : null;
$idCarrusel = 'carruselActividades';
$escaparActividad = function ($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};

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
    <link rel="stylesheet" href="../assets/css/actividades-publicas.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/actividades-publicas.css'); ?>">
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

<body data-bs-theme="light" class="pagina-actividades">

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
                <div class="col-12">
                    <section class="actividades-publicas" aria-label="Actividades de la parroquia">
                        <div class="actividades-encabezado">
                            <div>
                                <p class="actividades-etiqueta">Fe y tradición</p>
                                <p>Parroquia San Pedro de Sacaba</p>
                            </div>
                            <span class="actividades-total"><i class="bi bi-calendar2-week" aria-hidden="true"></i><?php echo $totalActividades; ?> <?php echo $totalActividades === 1 ? 'publicación' : 'publicaciones'; ?></span>
                        </div>
                        <div class="actividad-listado">
                                <?php if (!$listaActividades): ?>
                                    <div class="actividad-vacia">
                                        <i class="bi bi-calendar2-event" aria-hidden="true"></i>
                                        <h2>No hay actividades publicadas<?php echo $grupo === 'todos' ? '' : ' en esta categoría'; ?></h2>
                                        <p>Las próximas publicaciones aparecerán aquí.</p>
                                    </div>
                                <?php else: ?>
                                <div id="<?php echo $idCarrusel; ?>" class="carousel slide actividad-carrusel" role="region" aria-roledescription="carrusel" aria-label="Todas las actividades" data-bs-interval="false">
                                    <div class="carousel-inner">
                                        <?php $posicion = 0; foreach ($listaActividades as $indice => $actividad): ?>
                                            <?php
                                            $esPrincipal = $indice === $indicePrincipal;
                                            $numeroActividad = $indice + 1;
                                            $timestampFecha = strtotime($actividad['FechaActividad']);
                                            $fechaFormateada = date('j', $timestampFecha) . ' de ' . mb_strtolower($meses[date('n', $timestampFecha) - 1], 'UTF-8') . ' de ' . date('Y', $timestampFecha);
                                            list($imagenActividad, $videoActividad) = ControladorActividad::archivosActividad($actividad);
                                            $rutaImagen = $imagenActividad !== null ? '../assets/images/events/' . rawurlencode($imagenActividad) : null;
                                            $rutaVideo = $videoActividad !== null ? '../assets/images/events/' . rawurlencode($videoActividad) : null;
                                            $rutasGaleria = array_map(static function ($nombreArchivo) {
                                                return '../assets/images/events/' . rawurlencode($nombreArchivo);
                                            }, ControladorActividad::imagenesActividad($actividad));
                                            $idMedio = $grupo . '-actividad-' . (int) $actividad['idActividad'];
                                            ?>
                                            <div class="carousel-item<?php echo $esPrincipal ? ' active' : ''; ?>" data-actividad-numero="<?php echo $numeroActividad; ?>" role="group" aria-roledescription="diapositiva" aria-label="Actividad <?php echo $numeroActividad; ?>: <?php echo $escaparActividad($actividad['TituloActividad']); ?>" aria-hidden="<?php echo $esPrincipal ? 'false' : 'true'; ?>"<?php echo !$esPrincipal ? ' inert' : ''; ?>>
                                                <article class="actividad-destacada" aria-labelledby="<?php echo $idMedio; ?>-titulo">
                                                    <div class="actividad-escena">
                                                        <div class="actividad-archivo" data-actividad-fotos="<?php echo htmlspecialchars(json_encode($rutasGaleria, JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8'); ?>"<?php echo count($rutasGaleria) > 1 ? ' tabindex="0" role="group" aria-label="Fotos de la actividad"' : ''; ?> id="<?php echo $idMedio; ?>-imagen"<?php echo !$rutaImagen && $rutaVideo ? ' hidden' : ''; ?>>
                                                            <img class="actividad-fondo" id="<?php echo $idMedio; ?>-foto" src="<?php echo $rutaImagen ?: '../assets/images/events/Generico.png'; ?>" alt="Imagen de <?php echo $escaparActividad($actividad['TituloActividad']); ?>" loading="<?php echo $esPrincipal ? 'eager' : 'lazy'; ?>" draggable="false">
                                                            <?php if (count($rutasGaleria) > 1): ?>
                                                                <div class="actividad-galeria-controles">
                                                                    <button type="button" class="actividad-foto-flecha anterior" data-actividad-foto-paso="-1" aria-label="Foto anterior" aria-controls="<?php echo $idMedio; ?>-foto"><i class="bi bi-chevron-left" aria-hidden="true"></i></button>
                                                                    <span class="actividad-foto-contador" data-actividad-foto-contador aria-live="polite" aria-atomic="true">Foto 1 / <?php echo count($rutasGaleria); ?></span>
                                                                    <button type="button" class="actividad-foto-flecha siguiente" data-actividad-foto-paso="1" aria-label="Foto siguiente" aria-controls="<?php echo $idMedio; ?>-foto"><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <?php if ($rutaVideo): ?>
                                                            <div class="actividad-archivo" id="<?php echo $idMedio; ?>-video"<?php echo $rutaImagen ? ' hidden' : ''; ?>>
                                                                <video class="actividad-fondo" src="<?php echo $rutaVideo; ?>" controls playsinline preload="<?php echo $rutaImagen ? 'none' : 'metadata'; ?>" aria-label="Video de <?php echo $escaparActividad($actividad['TituloActividad']); ?>">Su navegador no puede reproducir este video. <a href="<?php echo $rutaVideo; ?>">Abrir video</a></video>
                                                            </div>
                                                        <?php endif; ?>
                                                        <?php if ($rutaImagen && $rutaVideo): ?>
                                                            <div class="actividad-selector-medios" role="group" aria-label="Archivos de la actividad <?php echo $numeroActividad; ?>">
                                                                <button type="button" data-actividad-medio aria-controls="<?php echo $idMedio; ?>-imagen" aria-pressed="true"><i class="bi bi-image" aria-hidden="true"></i> <?php echo count($rutasGaleria) > 1 ? 'Fotos' : 'Imagen'; ?></button>
                                                                <button type="button" data-actividad-medio aria-controls="<?php echo $idMedio; ?>-video" aria-pressed="false"><i class="bi bi-play-circle" aria-hidden="true"></i> Video</button>
                                                            </div>
                                                        <?php endif; ?>
                                                        <?php if (count($rutasGaleria) > 1): ?>
                                                            <button type="button" class="actividad-ver-fotos" data-bs-toggle="modal" data-bs-target="#modalImagen" data-actividad-galeria="<?php echo htmlspecialchars(json_encode($rutasGaleria, JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8'); ?>" aria-label="Ver las <?php echo count($rutasGaleria); ?> fotos de <?php echo $escaparActividad($actividad['TituloActividad']); ?>"><i class="bi bi-images" aria-hidden="true"></i> Ver <?php echo count($rutasGaleria); ?> fotos</button>
                                                        <?php endif; ?>
                                                        <?php if ($rutaImagen): ?>
                                                            <button type="button" class="actividad-ampliar" data-bs-toggle="modal" data-bs-target="#modalImagen" data-actividad-imagen-modal="<?php echo $rutaImagen; ?>" data-actividad-galeria="<?php echo htmlspecialchars(json_encode($rutasGaleria, JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8'); ?>" aria-label="Ampliar imagen de <?php echo $escaparActividad($actividad['TituloActividad']); ?>" title="Ampliar imagen"><i class="bi bi-arrows-fullscreen" aria-hidden="true"></i></button>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="actividad-contenido">
                                                        <div class="actividad-cabecera">
                                                            <div class="actividad-insignias">
                                                                <?php if ($esPrincipal): ?><span class="actividad-reciente"><i class="bi bi-star-fill" aria-hidden="true"></i> Última publicación</span><?php endif; ?>
                                                                <span class="actividad-estado-categoria">
                                                                    <span class="actividad-estado<?php echo $actividad['EstActividad'] === 'Activo' ? ' activa' : ''; ?>"><?php echo $escaparActividad($actividad['EstActividad']); ?></span>
                                                                    <span class="actividad-categoria"><?php echo $escaparActividad($actividad['TipoActividad']); ?></span>
                                                                </span>
                                                            </div>
                                                            <span class="actividad-contador" aria-hidden="true"><?php echo str_pad((string) (++$posicion), 2, '0', STR_PAD_LEFT); ?><span> / <?php echo str_pad((string) $totalGrupo, 2, '0', STR_PAD_LEFT); ?></span></span>
                                                        </div>
                                                        <div class="actividad-informacion" tabindex="0" role="region" aria-label="Información de la actividad <?php echo $numeroActividad; ?>">
                                                        <h2 class="actividad-titulo" id="<?php echo $idMedio; ?>-titulo"><?php echo $escaparActividad($actividad['TituloActividad']); ?></h2>
                                                        <dl class="actividad-datos">
                                                            <div><i class="bi bi-calendar2-week" aria-hidden="true"></i><div><dt>Fecha y hora</dt><dd><time datetime="<?php echo $escaparActividad($actividad['FechaActividad'] . 'T' . $actividad['HoraAct']); ?>"><?php echo $fechaFormateada; ?> · <?php echo $escaparActividad(substr($actividad['HoraAct'], 0, 5)); ?></time></dd></div></div>
                                                            <div><i class="bi bi-geo-alt" aria-hidden="true"></i><div><dt>Lugar</dt><dd><?php echo $escaparActividad($actividad['LugarAct']); ?></dd></div></div>
                                                        </dl>
                                                        <?php if (!empty($actividad['DetalleActividad'])): ?>
                                                            <div class="actividad-resumen">
                                                                <p class="actividad-descripcion" id="<?php echo $idMedio; ?>-descripcion"><?php echo $escaparActividad($actividad['DetalleActividad']); ?></p>
                                                                <button class="actividad-leer" type="button" aria-expanded="false" aria-controls="<?php echo $idMedio; ?>-descripcion" hidden>Leer más</button>
                                                            </div>
                                                        <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </article>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="actividad-controles">
                                        <button class="actividad-flecha" type="button" data-bs-target="#<?php echo $idCarrusel; ?>" data-bs-slide="prev" aria-label="Actividad anterior"<?php echo $totalGrupo < 2 ? ' disabled' : ''; ?>><i class="bi bi-arrow-left" aria-hidden="true"></i></button>
                                        <div class="actividad-progreso" aria-hidden="true"><span></span></div>
                                        <button class="actividad-flecha" type="button" data-bs-target="#<?php echo $idCarrusel; ?>" data-bs-slide="next" aria-label="Actividad siguiente"<?php echo $totalGrupo < 2 ? ' disabled' : ''; ?>><i class="bi bi-arrow-right" aria-hidden="true"></i></button>
                                        <?php if ($totalGrupo > 1): ?><button class="actividad-automatico" type="button" aria-label="Pausar cambio automático" aria-pressed="true"><i class="bi bi-pause-fill" aria-hidden="true"></i> <span>Pausar</span></button><?php endif; ?>
                                    </div>
                                    <nav class="actividad-numeros" aria-label="Elegir actividad por orden de publicación">
                                        <?php $posicion = 0; foreach ($listaActividades as $indice => $actividad): ?>
                                            <?php
                                            list($imagenMiniatura, $videoMiniatura) = ControladorActividad::archivosActividad($actividad);
                                            $principalMiniatura = $indice === $indicePrincipal;
                                            ?>
                                            <button class="actividad-numero<?php echo $principalMiniatura ? ' active' : ''; ?>" type="button" data-bs-target="#<?php echo $idCarrusel; ?>" data-bs-slide-to="<?php echo $posicion++; ?>" aria-label="Ver actividad <?php echo $indice + 1; ?>: <?php echo $escaparActividad($actividad['TituloActividad']); ?>"<?php echo $principalMiniatura ? ' aria-current="true"' : ''; ?>>
                                                <span class="actividad-mini-numero"><?php echo $indice + 1; ?></span>
                                                <?php if ($imagenMiniatura): ?>
                                                    <img class="actividad-mini-imagen" src="../assets/images/events/<?php echo rawurlencode($imagenMiniatura); ?>" alt="" loading="lazy" draggable="false">
                                                <?php else: ?>
                                                    <span class="actividad-mini-imagen actividad-mini-vacia"><i class="bi <?php echo $videoMiniatura ? 'bi-play-circle' : 'bi-calendar2-event'; ?>" aria-hidden="true"></i></span>
                                                <?php endif; ?>
                                                <span class="actividad-mini-texto">
                                                    <?php if ($principalMiniatura): ?><span class="actividad-mini-reciente"><i class="bi bi-star-fill" aria-hidden="true"></i> Última publicación</span><?php endif; ?>
                                                    <span class="actividad-mini-titulo"><?php echo $escaparActividad($actividad['TituloActividad']); ?></span>
                                                </span>
                                                <i class="bi bi-chevron-right actividad-mini-flecha" aria-hidden="true"></i>
                                            </button>
                                        <?php endforeach; ?>
                                    </nav>
                                    <span class="visually-hidden" data-actividad-estado aria-live="off">Mostrando actividad <?php echo $indicePrincipal + 1; ?></span>
                                </div>
                                <?php endif; ?>
                        </div>
                    </section>
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

        <div class="modal fade" id="modalImagen" tabindex="-1" aria-labelledby="modalImagenLabel" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <h2 id="modalImagenLabel" class="visually-hidden">Imagen de la actividad</h2>
                    <div class="modal-body">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        <button type="button" class="galeria-navegacion galeria-anterior" data-galeria-anterior aria-label="Imagen anterior" hidden><i class="bi bi-chevron-left" aria-hidden="true"></i></button>
                        <img id="imagenModal" src="" alt="" class="img-fluid" style="width: 100%; height: auto;">
                        <button type="button" class="galeria-navegacion galeria-siguiente" data-galeria-siguiente aria-label="Imagen siguiente" hidden><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
                        <span class="galeria-contador" data-galeria-contador hidden></span>
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
  <script src="../assets/js/actividad-multimedia.js"></script>
    <script src="../assets/js/actividades-publicas.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/actividades-publicas.js'); ?>"></script>
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