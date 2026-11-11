<?php
session_start();
require_once 'Controlador/controladorCatequistas.php';
require_once 'Controlador/controladorActividad.php';
$controller = new CatequistaController();

$CatequistasT = $controller->mostrarCatequistasActivos();

// Portada: las actividades publicadas más recientes primero.
$controladorActividades = new ControladorActividad();
$ultimasActividades = array_slice($controladorActividades->obtenerActividades(null, 'publicacion_desc'), 0, 4);
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
    <link rel="stylesheet" type="text/css" href="assets/css/logo-cabecera.css?v=<?= filemtime(__DIR__ . '/assets/css/logo-cabecera.css') ?>">
    <link rel="stylesheet" type="text/css" href="assets/css/Index.css?v=<?= filemtime(__DIR__ . '/assets/css/Index.css') ?>">
    <link rel="stylesheet" type="text/css" href="assets/css/loader.css">
    <style>
        .bg-mode {
            background-color: #ffffff;
            color: #000000;
        }

        .navbar-light .navbar-brand {
            color: #000000;
        }

        /* Requisitos sacramentales (inicio) */
        .requisitos-sacramentales .rs-cabecera {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 16px;
            margin-bottom: 24px;
            text-align: center;
        }
        .requisitos-sacramentales .rs-titulo {
            font-size: 34px;
            font-weight: 800;
            color: #000000;
            margin: 0 0 8px;
            letter-spacing: -.02em;
        }
        .requisitos-sacramentales .rs-sub {
            color: #667085;
            font-size: 16px;
            line-height: 1.5;
            margin: 0;
        }
        .requisitos-sacramentales .rs-sello {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: #fff;
            border: 1px solid #e4e9f0;
            border-radius: 999px;
            padding: 6px 13px;
            font-size: 13px;
            font-weight: 600;
            color: #475467;
            white-space: nowrap;
        }
        .requisitos-sacramentales .rs-card {
            background: #fff;
            border: 1px solid #e6ebf2;
            border-radius: 16px;
            box-shadow: 0 2px 6px rgba(16, 24, 40, .04);
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow: hidden;
        }
        .requisitos-sacramentales .rs-body {
            padding: 18px 18px 14px;
            flex: 1;
        }
        .requisitos-sacramentales .rs-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 12px;
        }
        .requisitos-sacramentales .rs-icono {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: #eef4ff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex: none;
        }
        .requisitos-sacramentales .rs-icono img {
            width: 38px;
            height: 38px;
            object-fit: contain;
        }
        .requisitos-sacramentales .rs-logo2 {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            border: 1px solid #e6ebf2;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex: none;
        }
        .requisitos-sacramentales .rs-logo2 img {
            width: 44px;
            height: 44px;
            object-fit: contain;
        }
        .requisitos-sacramentales .rs-nombre {
            font-size: 19px;
            font-weight: 800;
            color: #0b2545;
            margin: 0 0 6px;
        }
        .requisitos-sacramentales .rs-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 999px;
            background: #eef4ff;
            color: #1f5fbf;
            margin-bottom: 11px;
        }
        .requisitos-sacramentales .rs-chip.verde { background: #e8f7ee; color: #177a45; }
        .requisitos-sacramentales .rs-chip.ambar { background: #fff6e5; color: #9a6700; }
        .requisitos-sacramentales .rs-lista {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            gap: 6px;
        }
        .requisitos-sacramentales .rs-lista li {
            display: flex;
            gap: 6px;
            align-items: flex-start;
            font-size: 14px;
            line-height: 1.45;
            color: #344054;
        }
        .requisitos-sacramentales .rs-lista li i {
            color: #2a6fdb;
            font-size: 13px;
            margin-top: 3px;
            flex: none;
        }
        .requisitos-sacramentales .rs-lista strong { color: #1d2939; }
        .requisitos-sacramentales .rs-nota {
            font-size: 12.5px;
            color: #7a8699;
            line-height: 1.35;
            margin: 9px 0 0;
        }
        .requisitos-sacramentales .rs-pie {
            border-top: 1px solid #eef1f6;
            padding: 9px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            background: #fbfcfe;
        }
        .requisitos-sacramentales .rs-dato {
            font-size: 12.5px;
            color: #667085;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .requisitos-sacramentales .rs-enlace {
            font-size: 13px;
            font-weight: 700;
            color: #1f5fbf;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .requisitos-sacramentales .rs-matrimonio .rs-body { padding: 20px; }
        .requisitos-sacramentales .rs-mat-cab {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 14px;
        }
        .requisitos-sacramentales .rs-mat-cab .rs-head {
            margin: 0;
            align-items: center;
        }
        .requisitos-sacramentales .rs-mat-cab .rs-icono {
            width: 48px;
            height: 48px;
            border-radius: 12px;
        }
        .requisitos-sacramentales .rs-mat-cab .rs-icono img {
            width: 34px;
            height: 34px;
        }
        .requisitos-sacramentales .rs-columnas {
            display: grid;
            grid-template-columns: 1.35fr 1fr 1fr 1.15fr;
            gap: 16px;
        }
        .requisitos-sacramentales .rs-col {
            border-left: 1px solid #eef1f6;
            padding-left: 16px;
        }
        .requisitos-sacramentales .rs-col:first-child {
            border-left: 0;
            padding-left: 0;
        }
        .requisitos-sacramentales .rs-col h3 {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #667085;
            font-weight: 800;
            margin: 0 0 8px;
        }
        .requisitos-sacramentales .rs-reserva {
            background: #f8fafc;
            border: 1px solid #eef1f6;
            border-radius: 12px;
            padding: 11px;
        }
        .requisitos-sacramentales .rs-mes {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            font-size: 12.5px;
            padding: 3px 0;
            border-bottom: 1px dashed #e6ebf2;
        }
        .requisitos-sacramentales .rs-mes:last-of-type { border-bottom: 0; }
        .requisitos-sacramentales .rs-mes strong { color: #0b2545; }
        .requisitos-sacramentales .rs-mes span { color: #667085; }
        .requisitos-sacramentales .rs-aviso {
            margin-top: 12px;
            background: #fff9ec;
            border: 1px solid #f3e3bd;
            border-radius: 10px;
            padding: 8px 11px;
            font-size: 12.5px;
            color: #7a5b12;
            display: flex;
            gap: 7px;
            align-items: flex-start;
        }
        .requisitos-sacramentales .rs-aviso i { color: #b7791f; margin-top: 2px; }
        @media (max-width: 991.98px) {
            .requisitos-sacramentales .rs-columnas { grid-template-columns: 1fr 1fr; }
            .requisitos-sacramentales .rs-col:nth-child(3) { border-left: 0; padding-left: 0; }
        }
        @media (max-width: 575.98px) {
            .requisitos-sacramentales .rs-cabecera { align-items: center; }
            .requisitos-sacramentales .rs-titulo { font-size: 28px; }
            .requisitos-sacramentales .rs-sub { font-size: 15px; }
            .requisitos-sacramentales .rs-columnas { grid-template-columns: 1fr; }
            .requisitos-sacramentales .rs-col {
                border-left: 0;
                padding-left: 0;
                border-top: 1px solid #eef1f6;
                padding-top: 12px;
            }
            .requisitos-sacramentales .rs-col:first-child {
                border-top: 0;
                padding-top: 0;
            }
        }

        /* Horarios de atención y misas */
        .horarios-inicio {
            background: linear-gradient(180deg, #f4f7fb 0%, #eef2f7 100%);
        }
        .horarios-inicio-cabecera {
            max-width: 760px;
            margin-right: auto;
            margin-left: auto;
        }
        .horarios-inicio-etiqueta {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            margin-bottom: .55rem;
            color: #9a6d08;
            font-size: .76rem;
            font-weight: 800;
            letter-spacing: .13em;
            text-transform: uppercase;
        }
        .horarios-inicio-titulo {
            margin-bottom: .65rem;
            color: #000000;
            font-size: clamp(2rem, 4vw, 2.75rem);
            font-weight: 700;
            line-height: 1.2;
            letter-spacing: normal;
        }
        .horarios-inicio-descripcion {
            color: #475467;
            font-size: 1.02rem;
        }
        .horario-card {
            position: relative;
            height: 100%;
            padding: 1.5rem;
            overflow: hidden;
            background: #ffffff;
            border: 1px solid #e0e6ee;
            border-radius: 18px;
            box-shadow: 0 12px 30px rgba(15, 45, 82, .08);
        }
        .horario-card::before {
            position: absolute;
            top: 0;
            right: 0;
            left: 0;
            height: 4px;
            background: linear-gradient(90deg, #12345b, #2e6fac);
            content: "";
        }
        .horario-card-misas::before {
            background: linear-gradient(90deg, #bd8700, #e0b43f);
        }
        .horario-card-cabecera {
            display: flex;
            gap: .9rem;
            align-items: center;
            margin-bottom: 1.25rem;
        }
        .horario-card-icono {
            display: inline-grid;
            width: 3rem;
            height: 3rem;
            flex: 0 0 3rem;
            place-items: center;
            color: #ffffff;
            background: #12345b;
            border-radius: 13px;
            font-size: 1.35rem;
        }
        .horario-card-misas .horario-card-icono {
            color: #755000;
            background: #fff0c4;
        }
        .horario-card-cabecera h3 {
            margin: 0 0 .2rem;
            color: #101828;
            font-size: 1.35rem;
            font-weight: 800;
        }
        .horario-card-cabecera p {
            margin: 0;
            color: #667085;
            font-size: .9rem;
        }
        .horario-lista {
            display: grid;
            gap: .7rem;
            padding: 0;
            margin: 0;
            list-style: none;
        }
        .horario-fila {
            display: grid;
            grid-template-columns: minmax(150px, .9fr) minmax(0, 1.1fr);
            gap: .8rem;
            padding: .85rem 1rem;
            align-items: center;
            background: #f7f9fc;
            border: 1px solid #e7ebf1;
            border-radius: 11px;
        }
        .horario-dia {
            display: flex;
            gap: .55rem;
            align-items: center;
            color: #101828;
            font-weight: 750;
        }
        .horario-dia i { color: #1f5f9d; }
        .horario-horas {
            color: #344054;
            line-height: 1.45;
        }
        .horario-fila-aviso {
            background: #fff9ec;
            border-color: #f1dfb6;
        }
        .horario-fila-aviso .horario-dia i { color: #a66f00; }
        .horario-nota {
            display: inline-block;
            padding: .16rem .5rem;
            margin-left: .3rem;
            color: #815b06;
            background: #ffedbd;
            border-radius: 999px;
            font-size: .76rem;
            font-weight: 700;
        }
        @media (max-width: 575.98px) {
            .horario-card { padding: 1.15rem; }
            .horario-fila { grid-template-columns: 1fr; gap: .35rem; }
            .horario-nota { margin: .25rem 0 0; }
        }

        /* Tipografía común para los títulos principales de la página. */
        .titulo-seccion-inicio,
        .requisitos-sacramentales .rs-titulo,
        .horarios-inicio-titulo {
            color: #000000;
            font-family: inherit;
            font-size: clamp(1.75rem, 2.8vw, 2.25rem);
            font-weight: 700;
            line-height: 1.2;
            letter-spacing: normal;
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
    <header class="navbar-light fixed-top header-static bg-mode cabecera-publica">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="index.php">
                    <img class="light-mode-item navbar-brand-item" src="assets/images/logo.png?v=<?= filemtime(__DIR__ . '/assets/images/logo.png') ?>" alt="logo">
                    <img class="dark-mode-item navbar-brand-item" src="assets/images/logo-light.png?v=<?= filemtime(__DIR__ . '/assets/images/logo-light.png') ?>" alt="logo">
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
                <div class="cabecera-acceso">
                    <button type="button" class="btn cabecera-ingresar" aria-label="Iniciar sesión" data-bs-toggle="modal"
                        data-bs-target="#exampleModal">
                        <i class="bi bi-person" aria-hidden="true"></i><span>Ingresar</span>
                    </button>
                </div>
                <div class="cabecera-contacto">
                    <a type="button" href="src/srcContacto.php" class="btn cabecera-mensajes" aria-label="Contactos">
                        <i class="bi bi-chat-left-dots" aria-hidden="true"></i><span>Contactos</span>
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
                                        <div class="me-4 position-relative">
                                            <?php
                                            // El video del templo pesa 51 MB, así que se incrusta desde
                                            // YouTube. El iframe se carga recién al pulsar el botón.
                                            $videoTemploYouTube = 'https://youtu.be/GJHT_DHPU78';
                                            $videoTemploId = '';
                                            if (preg_match('#(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/|live/))([A-Za-z0-9_-]{6,20})#', $videoTemploYouTube, $coincideVideo)) {
                                                $videoTemploId = $coincideVideo[1];
                                            }
                                            ?>
                                            <?php if ($videoTemploId !== ''): ?>
                                            <button id="showVideoButton"
                                                class="btn btn-primary icon-md rounded-circle playerbtn"><i
                                                    class="bi bi-play-fill"></i></button>
                                            <div id="videoContainer" class="player-wrapper rounded-3"
                                                style="display:none;position:absolute;top:0;left:0;width:100%;max-width:520px;z-index:1000;">
                                                <button id="closeVideoButton"
                                                    class="btn btn-danger icon-md rounded-circle mb-3"> <i
                                                        class="bi bi-x-lg"></i></button>
                                                <div class="ratio ratio-16x9 rounded-3 overflow-hidden">
                                                    <iframe id="videoPlayer" title="Video del templo"
                                                        data-src="https://www.youtube.com/embed/<?php echo htmlspecialchars($videoTemploId, ENT_QUOTES, 'UTF-8'); ?>?autoplay=1&amp;rel=0"
                                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                                        allowfullscreen referrerpolicy="strict-origin-when-cross-origin"
                                                        style="border:0;"></iframe>
                                                </div>
                                            </div>
                                            <?php endif; ?>
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
                        <h2 class="h1 titulo-seccion-inicio">Nuestros Sacramentos</h2>
                        <p>Conoce los sacramentos que puedes recibir en nuestra parroquia.</p>
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
                        <p class="mb-0">Tiempo en el cual se prepara para el primer encuentro con Cristo.</p>
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

        <section class="py-4 py-sm-5 actividades-inicio">
            <div class="container">
                <?php
                $escaparIndex = function ($valor) {
                    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                };
                ?>
                <div class="row justify-content-center">
                    <div class="col-lg-12 text-center mb-4">
                        <p class="mb-2" style="letter-spacing:.16em; text-transform:uppercase; font-size:.75rem; font-weight:700; color:#9a7420;">FE Y TRADICIÓN</p>
                        <h2 class="h1 actividades-inicio-titulo titulo-seccion-inicio">Actividades de nuestra parroquia</h2>
                        <p>Encuentros, celebraciones y novedades para compartir nuestra fe.</p>
                    </div>
                </div>
                <?php if ($ultimasActividades): ?>
                    <div class="row g-4">
                        <?php foreach ($ultimasActividades as $indice => $actividad): ?>
                            <?php
                            list($imagenActividad, $videoActividad) = ControladorActividad::archivosActividad($actividad);
                            $hayVideoActividad = $videoActividad || !empty($actividad['VideoUrl']);
                            $fechaTs = strtotime($actividad['FechaActividad']);
                            $rutaMini = $imagenActividad ?: ($videoActividad ?: 'Generico.png');
                            ?>
                            <div class="col-sm-6 col-xl-3">
                                <div class="card h-100 shadow-sm border-0 overflow-hidden rounded-4">
                                    <div class="position-relative">
                                        <img src="assets/images/events/<?php echo rawurlencode($rutaMini); ?>" alt="" class="w-100" style="height:170px; object-fit:cover;">
                                        <?php if ($hayVideoActividad): ?>
                                            <span class="badge rounded-pill bg-dark position-absolute top-0 start-0 m-2"><i class="bi bi-play-btn me-1"></i>Video</span>
                                        <?php endif; ?>
                                        <?php if ($indice === 0): ?>
                                            <span class="badge rounded-pill position-absolute top-0 end-0 m-2" style="background:#d9a441; color:#221603;"><i class="bi bi-stars me-1"></i>Más reciente</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body d-flex flex-column">
                                        <span class="text-uppercase fw-bold" style="font-size:.68rem; letter-spacing:.12em; color:#9a7420;"><?php echo $escaparIndex($actividad['TipoActividad']); ?></span>
                                        <h5 class="mt-1" style="overflow-wrap:anywhere;"><?php echo $escaparIndex($actividad['TituloActividad']); ?></h5>
                                        <p class="small actividades-inicio-dato mb-1"><i class="bi bi-calendar2-week me-1"></i><?php echo date('d/m/Y', $fechaTs); ?><?php echo !empty($actividad['HoraAct']) ? ' · ' . $escaparIndex(substr($actividad['HoraAct'], 0, 5)) : ''; ?></p>
                                        <p class="small actividades-inicio-dato mb-3"><i class="bi bi-geo-alt me-1"></i><?php echo $escaparIndex($actividad['LugarAct']); ?></p>
                                        <a href="src/srcActividadGeneral.php" class="mt-auto btn btn-sm btn-outline-dark rounded-pill">Ver actividad <i class="bi bi-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="text-center mt-4">
                        <a class="btn btn-dark rounded-pill px-4" href="src/srcActividadGeneral.php">Ver todas las actividades <i class="bi bi-arrow-right ms-1"></i></a>
                    </div>
                <?php else: ?>
                    <p class="text-center text-muted mb-0">Próximamente publicaremos nuevas actividades.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="py-4 py-sm-5 horarios-inicio">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-12 text-center mb-4 horarios-inicio-cabecera">
                        <span class="horarios-inicio-etiqueta"><i class="bi bi-calendar2-week" aria-hidden="true"></i> Información parroquial</span>
                        <h2 class="horarios-inicio-titulo titulo-seccion-inicio">Horarios de Atención y Misas</h2>
                        <p class="horarios-inicio-descripcion">Consulta nuestros horarios de atención parroquial y las celebraciones de la Santa Misa.</p>
                    </div>
                </div>
                <div class="row g-4">
                    <div class="col-md-6">
                        <article class="horario-card">
                            <div class="horario-card-cabecera">
                                <span class="horario-card-icono"><i class="bi bi-clock" aria-hidden="true"></i></span>
                                <div><h3>Horarios de Atención</h3><p>Reservas, consultas y atención en secretaría.</p></div>
                            </div>
                            <ul class="horario-lista">
                                <li class="horario-fila">
                                    <span class="horario-dia"><i class="bi bi-calendar-check" aria-hidden="true"></i> Lun., mié., jue. y vie.</span>
                                    <span class="horario-horas">08:00–12:00 y 14:00–18:00</span>
                                </li>
                                <li class="horario-fila horario-fila-aviso">
                                    <span class="horario-dia"><i class="bi bi-calendar-minus" aria-hidden="true"></i> Martes</span>
                                    <span class="horario-horas">08:00–12:00 <span class="horario-nota">Sin atención por la tarde</span></span>
                                </li>
                                <li class="horario-fila">
                                    <span class="horario-dia"><i class="bi bi-calendar-heart" aria-hidden="true"></i> Sábado y domingo</span>
                                    <span class="horario-horas">Atención por la mañana, hasta las 12:00</span>
                                </li>
                            </ul>
                        </article>
                    </div>
                    <div class="col-md-6">
                        <article class="horario-card horario-card-misas">
                            <div class="horario-card-cabecera">
                                <span class="horario-card-icono"><i class="bi bi-cross" aria-hidden="true"></i></span>
                                <div><h3>Horarios de Misas</h3><p>Celebraciones de la Santa Misa.</p></div>
                            </div>
                            <ul class="horario-lista">
                                <li class="horario-fila">
                                    <span class="horario-dia"><i class="bi bi-calendar3" aria-hidden="true"></i> Lunes a viernes</span>
                                    <span class="horario-horas">07:00 · 12:00 · 19:00</span>
                                </li>
                                <li class="horario-fila">
                                    <span class="horario-dia"><i class="bi bi-calendar3" aria-hidden="true"></i> Sábado</span>
                                    <span class="horario-horas">07:00 · 17:00 <span class="horario-nota">Misa de vigilia</span></span>
                                </li>
                                <li class="horario-fila">
                                    <span class="horario-dia"><i class="bi bi-calendar3" aria-hidden="true"></i> Domingo</span>
                                    <span class="horario-horas">08:00 · 10:00 · 12:00 · 18:00</span>
                                </li>
                            </ul>
                        </article>
                    </div>
                </div>
            </div>
        </section>
        <section class="py-4 py-sm-5 bg-light d-flex align-items-center justify-content-between">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-12 text-center mb-4">
                        <h2 class="h1 titulo-seccion-inicio">Nuestros Catequistas</h2>
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
                                                    <span class="badge bg-primary-soft text-primary mt-1"><?php echo htmlspecialchars($catequista['Roles']); ?></span>
                                                    <p class="mb-0 small text-muted"><?php echo htmlspecialchars($catequista['Grupos']); ?></p>
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
                        <h2 class="h2 titulo-seccion-inicio">Información General</h2>
                    </div>
                </div>

                <div class="row g-4 g-lg-5 align-items-center">
                    <div class="col-lg-4">
                        <h2 class="h1 titulo-seccion-inicio">Nuestras inscripciones</h2>
                        <p class="mb-4">¿Tienes todo lo necesario para inscribirte?</p>
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


                    <div class="col-12 requisitos-sacramentales mt-4 mt-lg-5">
                        <div class="rs-cabecera">
                            <div>
                                <h2 class="rs-titulo titulo-seccion-inicio">Requisitos sacramentales</h2>
                                <p class="rs-sub">Documentos y condiciones para Bautizo, Primera Comunión, Confirmación y Matrimonio.</p>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-4">
                                <article class="rs-card">
                                    <div class="rs-body">
                                        <div class="rs-head">
                                            <div class="rs-icono"><img src="assets/media/resources/BautizoInf.png" alt=""></div>
                                        </div>
                                        <h3 class="rs-nombre">Bautizo</h3>
                                        <span class="rs-chip"><i class="bi bi-person"></i> Menos de 10 años</span>
                                        <ul class="rs-lista">
                                            <li><i class="bi bi-check-circle-fill"></i><span><strong>Certificado de Nacimiento</strong> (original y fotocopia).</span></li>
                                            <li><i class="bi bi-check-circle-fill"></i><span><strong>Padrinos católicos:</strong> si están casados por la Iglesia, Certificado de Matrimonio Religioso y Carnet de Identidad.</span></li>
                                            <li><i class="bi bi-check-circle-fill"></i><span>Si son solteros, Certificado de Confirmación y Carnet de Identidad.</span></li>
                                        </ul>
                                        <p class="rs-nota">No pueden ser padrinos quienes viven en concubinato, están casados solo por lo civil, son divorciados o no hicieron la Confirmación.</p>
                                    </div>
                                    <div class="rs-pie">
                                        <span class="rs-dato"><i class="bi bi-calendar-week"></i> Jueves 14:30 · Domingo 12:30</span>
                                        <a class="rs-enlace" href="src/srcSacramentoBautizo.php">Ver detalles <i class="bi bi-arrow-right"></i></a>
                                    </div>
                                </article>
                            </div>

                            <div class="col-md-4">
                                <article class="rs-card">
                                    <div class="rs-body">
                                        <div class="rs-head">
                                            <div class="rs-icono"><img src="assets/media/resources/ComunionInf.png" alt=""></div>
                                            <div class="rs-logo2"><img src="assets/media/resources/PriCom.svg" alt="Logo PRI-COM"></div>
                                        </div>
                                        <h3 class="rs-nombre">Primera Comunión</h3>
                                        <span class="rs-chip verde"><i class="bi bi-person"></i> De 10 a 15 años</span>
                                        <ul class="rs-lista">
                                            <li><i class="bi bi-check-circle-fill"></i><span><strong>Certificado de Nacimiento</strong> (1 fotocopia).</span></li>
                                            <li><i class="bi bi-check-circle-fill"></i><span><strong>Certificado de Bautismo</strong> (1 fotocopia).</span></li>
                                        </ul>
                                    </div>
                                    <div class="rs-pie">
                                        <span class="rs-dato"><i class="bi bi-file-earmark-text"></i> Solo requisitos</span>
                                        <a class="rs-enlace" href="src/srcSacramentoComunion.php">Ver detalles <i class="bi bi-arrow-right"></i></a>
                                    </div>
                                </article>
                            </div>

                            <div class="col-md-4">
                                <article class="rs-card">
                                    <div class="rs-body">
                                        <div class="rs-head">
                                            <div class="rs-icono"><img src="assets/media/resources/ConfirmacionInf.png" alt=""></div>
                                            <div class="rs-logo2"><img src="assets/media/resources/ConfirmacionLogo.svg" alt="Logo Confirmación"></div>
                                        </div>
                                        <h3 class="rs-nombre">Confirmación</h3>
                                        <span class="rs-chip ambar"><i class="bi bi-person"></i> 14 años en adelante · Grupo especial 16</span>
                                        <ul class="rs-lista">
                                            <li><i class="bi bi-check-circle-fill"></i><span><strong>Certificado de Nacimiento</strong> (fotocopia).</span></li>
                                            <li><i class="bi bi-check-circle-fill"></i><span><strong>Certificado de Bautismo</strong> (fotocopia).</span></li>
                                        </ul>
                                    </div>
                                    <div class="rs-pie">
                                        <span class="rs-dato"><i class="bi bi-file-earmark-text"></i> Solo requisitos</span>
                                        <a class="rs-enlace" href="src/srcSacramentoConfirmacion.php">Ver detalles <i class="bi bi-arrow-right"></i></a>
                                    </div>
                                </article>
                            </div>

                            <div class="col-12">
                                <article class="rs-card rs-matrimonio">
                                    <div class="rs-body">
                                        <div class="rs-mat-cab">
                                            <div class="rs-head">
                                                <div class="rs-icono"><img src="assets/media/resources/MatrimonioInf.png" alt=""></div>
                                                <div>
                                                    <h3 class="rs-nombre" style="margin:0 0 4px;">Matrimonio</h3>
                                                    <span class="rs-chip" style="margin:0;"><i class="bi bi-people"></i> Novios, padrinos y testigos</span>
                                                </div>
                                            </div>
                                            <a class="rs-enlace" href="src/srcSacramentoMatrimonio.php">Ver detalles <i class="bi bi-arrow-right"></i></a>
                                        </div>

                                        <div class="rs-columnas">
                                            <div class="rs-col">
                                                <h3>Los novios deben presentar</h3>
                                                <ul class="rs-lista">
                                                    <li><i class="bi bi-check-circle-fill"></i><span>Certificado de Nacimiento</span></li>
                                                    <li><i class="bi bi-check-circle-fill"></i><span>Certificado de Bautizo</span></li>
                                                    <li><i class="bi bi-check-circle-fill"></i><span>Certificado de Confirmación</span></li>
                                                    <li><i class="bi bi-check-circle-fill"></i><span>Certificado de Matrimonio Civil</span></li>
                                                    <li><i class="bi bi-check-circle-fill"></i><span>Carnet de Identidad</span></li>
                                                    <li><i class="bi bi-check-circle-fill"></i><span>Certificado de Cursillos Pre-matrimoniales</span></li>
                                                </ul>
                                                <p class="rs-nota">Todos en original y fotocopia.</p>
                                            </div>

                                            <div class="rs-col">
                                                <h3>Los padrinos</h3>
                                                <ul class="rs-lista">
                                                    <li><i class="bi bi-check-circle-fill"></i><span>Certificado de Matrimonio Religioso Católico</span></li>
                                                    <li><i class="bi bi-check-circle-fill"></i><span>Carnet de Identidad</span></li>
                                                </ul>
                                                <p class="rs-nota">Original y fotocopia.</p>
                                            </div>

                                            <div class="rs-col">
                                                <h3>Dos testigos</h3>
                                                <ul class="rs-lista">
                                                    <li><i class="bi bi-check-circle-fill"></i><span>Uno para el novio y otro para la novia.</span></li>
                                                    <li><i class="bi bi-check-circle-fill"></i><span>Deben conocerlos al menos 5 años.</span></li>
                                                    <li><i class="bi bi-check-circle-fill"></i><span>No deben ser familiares.</span></li>
                                                    <li><i class="bi bi-check-circle-fill"></i><span>Carnet de Identidad.</span></li>
                                                </ul>
                                            </div>

                                            <div class="rs-col">
                                                <h3>Reservas 2026</h3>
                                                <div class="rs-reserva">
                                                    <div class="rs-mes"><span>Desde el <strong>2 de mayo</strong></span><span>para octubre</span></div>
                                                    <div class="rs-mes"><span>Desde el <strong>1 de junio</strong></span><span>para noviembre</span></div>
                                                    <div class="rs-mes"><span>Desde el <strong>1 de julio</strong></span><span>para diciembre</span></div>
                                                    <p class="rs-nota" style="margin-bottom:0;">Reservar con 6 meses de anticipación y presentar los documentos 3 meses antes.</p>
                                                </div>
                                                <p class="rs-nota"><i class="bi bi-clock me-1"></i> Informes matrimoniales: martes de 8:30 a 11:00.</p>
                                            </div>
                                        </div>

                                        <div class="rs-aviso">
                                            <i class="bi bi-info-circle-fill"></i>
                                            <span>Los requisitos se entregan en secretaría. El original se verifica en ventanilla y se conserva la fotocopia.</span>
                                        </div>
                                    </div>
                                </article>
                            </div>
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
                        <h2 class="h1 titulo-seccion-inicio">Parroquia "San Pedro de Sacaba"</h2>
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


        <div class="modal fade login-modal" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-describedby="login-description" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <button type="button" class="login-close" data-bs-dismiss="modal" aria-label="Cerrar inicio de sesión">
                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                    </button>
                    <div class="modal-body login-panel">
                        <img class="login-logo" src="assets/images/logo/logParroSP.png?v=<?= filemtime(__DIR__ . '/assets/images/logo/logParroSP.png') ?>" alt="Parroquia San Pedro">
                        <div class="login-divider" aria-hidden="true"><span></span></div>
                        <h2 class="modal-title login-title" id="exampleModalLabel">INICIAR SESIÓN</h2>
                        <?php if (isset($_SESSION['usuario']) || isset($_SESSION['CiPersona'])) { ?>
                            <p class="login-description" id="login-description">Ya tienes una sesión activa.</p>
                            <a href="logout.php" class="login-submit login-session-exit">
                                Cerrar sesión <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                            </a>
                        <?php } else { ?>
                            <p class="login-description" id="login-description">Ingresa tus datos para continuar.</p>
                            <form action="Controlador/controladorLogin.php" method="POST" class="form-validator" id="login-form">
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

        if (showVideoButton && closeVideoButton && videoPlayer) {
            showVideoButton.addEventListener('click', function() {
                videoContainer.style.display = 'block';
                // Se oculta con visibility para que el botón conserve su espacio
                // y el video aparezca exactamente donde estaba.
                showVideoButton.style.visibility = 'hidden';
                if (videoPlayer.tagName === 'IFRAME') {
                    if (!videoPlayer.getAttribute('src')) {
                        videoPlayer.setAttribute('src', videoPlayer.dataset.src);
                    }
                } else {
                    videoPlayer.play();
                }
            });

            closeVideoButton.addEventListener('click', function() {
                videoContainer.style.display = 'none';
                showVideoButton.style.visibility = '';
                if (videoPlayer.tagName === 'IFRAME') {
                    videoPlayer.removeAttribute('src');
                } else {
                    videoPlayer.pause();
                }
            });
        }


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
    <script src="assets/js/acceso.js?v=<?= filemtime(__DIR__ . '/assets/js/acceso.js') ?>" defer></script>
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
