<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../logout.php');
    exit();
}

$usuario = $_SESSION['usuario'];

require_once __DIR__ . '/../Controlador/controladorAcceso.php';
ControladorAcceso::exigir('inicio');

require_once __DIR__ . '/../Controlador/controladorDashboard.php';
$controller = new ControladorDashboard();
$resumen = $controller->obtenerResumen();

$personalActivo = (int) ($resumen['personalActivo'] ?? 0);
$personalTotal = (int) ($resumen['personalTotal'] ?? 0);
$personas = (int) ($resumen['personas'] ?? 0);
$reservas = (int) ($resumen['reservas'] ?? 0);
$certificados = (int) ($resumen['certificados'] ?? 0);
$totalActividades = (int) ($resumen['actividades'] ?? 0);
$proximasReservas = is_array($resumen['proximasReservas'] ?? null) ? $resumen['proximasReservas'] : [];
$actividadesRecientes = is_array($resumen['actividadesRecientes'] ?? null) ? $resumen['actividadesRecientes'] : [];

$puedeCelebracion = ControladorAcceso::puede('celebracion');
$puedePersonal = ControladorAcceso::puede('personal');
$puedePersona = ControladorAcceso::puede('persona');
$puedeCertificado = ControladorAcceso::puede('certificado');

$diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
$meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$mesesCortos = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];

$fechaActual = new DateTimeImmutable('now', new DateTimeZone('America/La_Paz'));
$fechaHero = $diasSemana[(int) $fechaActual->format('w')] . ', ' . (int) $fechaActual->format('j') . ' de ' . $meses[(int) $fechaActual->format('n') - 1];
$anioActual = $fechaActual->format('Y');

$formatearFecha = static function ($fecha, $incluirDia = true) use ($diasSemana, $meses, $mesesCortos) {
    $texto = trim((string) $fecha);
    $data = DateTimeImmutable::createFromFormat('!Y-m-d', $texto);
    if (!$data) {
        return ['dia' => '', 'mesCorto' => '', 'larga' => ''];
    }

    $diaSemana = $diasSemana[(int) $data->format('w')];
    $mes = $meses[(int) $data->format('n') - 1];
    return [
        'dia' => $data->format('d'),
        'mesCorto' => $mesesCortos[(int) $data->format('n') - 1],
        'larga' => ($incluirDia ? $diaSemana . ', ' : '') . (int) $data->format('j') . ' de ' . $mes,
    ];
};

$escapar = static function ($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};

$nombreCompleto = trim(($usuario['Nombre'] ?? '') . ' ' . ($usuario['Paterno'] ?? '') . ' ' . ($usuario['Materno'] ?? ''));
$nombreMostrar = trim((string) ($usuario['Nombre'] ?? 'Usuario'));
$cargo = trim((string) ($usuario['DescripCar'] ?? 'Personal'));
$frase = trim((string) ($usuario['FrasePer'] ?? ''));
$perfilPer = (string) ($usuario['PerfilPer'] ?? 'Ninguno');
$fondoPer = (string) ($usuario['FondoPer'] ?? 'Ninguno');
$rutaPerfil = ($perfilPer === '' || $perfilPer === 'Ninguno')
    ? '../assets/images/avatar/Ninguno.png'
    : '../assets/images/avatar/' . $perfilPer . '?v=' . time();
$rutaFondo = ($fondoPer === '' || $fondoPer === 'Ninguno')
    ? '../assets/images/fondo/Ninguno.jpg'
    : '../assets/images/fondo/' . $fondoPer . '?v=' . time();

$kpis = [];
if ($puedePersonal) {
    $kpis[] = [
        'clase' => 'gold',
        'icono' => 'bi-people-fill',
        'marca' => 'bi-check-circle-fill',
        'etiqueta' => 'Personal activo',
        'valor' => $personalActivo,
        'nota' => 'de ' . $personalTotal . ' registrados',
    ];
}
if ($puedePersona) {
    $kpis[] = [
        'clase' => 'blue',
        'icono' => 'bi-person-vcard',
        'marca' => 'bi-arrow-up-right',
        'etiqueta' => 'Personas',
        'valor' => $personas,
        'nota' => 'en el sistema',
    ];
}
if ($puedeCelebracion) {
    $kpis[] = [
        'clase' => 'purple',
        'icono' => 'bi-calendar2-check',
        'marca' => 'bi-arrow-up-right',
        'etiqueta' => 'Reservas',
        'valor' => $reservas,
        'nota' => 'registradas',
    ];
}
if ($puedeCertificado) {
    $kpis[] = [
        'clase' => 'green',
        'icono' => 'bi-patch-check-fill',
        'marca' => 'bi-check-circle-fill',
        'etiqueta' => 'Certificados',
        'valor' => $certificados,
        'nota' => 'emitidos',
    ];
}

$totalKpis = count($kpis);
if ($totalKpis >= 4) {
    $colKpi = 'col-sm-6 col-xl-3';
} elseif ($totalKpis === 3) {
    $colKpi = 'col-sm-6 col-xl-4';
} elseif ($totalKpis === 2) {
    $colKpi = 'col-sm-6 col-xl-6';
} else {
    $colKpi = 'col-12';
}

$iconosTipo = [
    'misa' => 'vela.png',
    'bautizo' => 'bautismo.png',
    'matrimonio' => 'matrimonio.png',
    'confirmación' => 'confirmacion.png',
    'confirmacion' => 'confirmacion.png',
];
$primeraFecha = (string) ($proximasReservas[0]['FechaReal'] ?? '');
$fechaAgenda = $formatearFecha($primeraFecha);
$cantidadAgenda = count($proximasReservas);

$estadisticasSacramentos = is_array($resumen['estadisticasSacramentos'] ?? null)
    ? $resumen['estadisticasSacramentos']
    : [];
$estadisticasMisas = is_array($resumen['estadisticasMisas'] ?? null)
    ? $resumen['estadisticasMisas']
    : [];
$catequesis = is_array($resumen['catequesis'] ?? null)
    ? $resumen['catequesis']
    : ['activos' => 0, 'grupos' => 0, 'inscripciones' => 0];

$anioInicioEstadisticas = 2026;
$anioFinEstadisticas = max($anioInicioEstadisticas, (int) $anioActual);
$puedeCatequesis = ControladorAcceso::puede('coordinador');

$seriesSacramentos = [
    'bautizo' => [
        'label' => 'Bautizos',
        'color' => '#d4af37',
        'fondo' => '#ffffff',
        'borde' => '#d4af37',
        'permiso' => 'certificado',
        'total' => 0,
    ],
    'matrimonio' => [
        'label' => 'Matrimonios',
        'color' => '#0284c7',
        'fondo' => '#bae6fd',
        'borde' => '#0284c7',
        'permiso' => 'certificado',
        'total' => 0,
    ],
    'confirmacion' => [
        'label' => 'Confirmaciones',
        'color' => '#dc2626',
        'fondo' => '#fecaca',
        'borde' => '#dc2626',
        'permiso' => 'certificado',
        'total' => 0,
    ],
    'misas' => [
        'label' => 'Misas',
        'color' => '#15803d',
        'fondo' => '#bbf7d0',
        'borde' => '#15803d',
        'permiso' => 'celebracion',
        'total' => 0,
    ],
];

$seriesPermitidas = [];
foreach ($seriesSacramentos as $clave => $serie) {
    if ($serie['permiso'] === 'certificado' && !$puedeCertificado) {
        continue;
    }
    if ($serie['permiso'] === 'celebracion' && !$puedeCelebracion) {
        continue;
    }
    $seriesPermitidas[$clave] = $serie;
}
$puedeEstadisticas = !empty($seriesPermitidas);

$mapaSacramentos = [
    'Bautizo' => 'bautizo',
    'Matrimonio' => 'matrimonio',
    'Confirmación' => 'confirmacion',
];

$aniosGrafico = range($anioInicioEstadisticas, $anioFinEstadisticas);
$estadisticasPorAnio = [];
foreach ($aniosGrafico as $anio) {
    $estadisticasPorAnio[$anio] = array_fill_keys(array_keys($seriesPermitidas), 0);
}

$totalEstadisticas = 0;
if ($puedeEstadisticas) {
    foreach ($estadisticasSacramentos as $fila) {
        $clave = $mapaSacramentos[trim((string) ($fila['sacramento'] ?? ''))] ?? '';
        if ($clave === '' || !isset($seriesPermitidas[$clave])) {
            continue;
        }

        $anio = (int) ($fila['anio'] ?? 0);
        $total = (int) ($fila['total'] ?? 0);
        if (!isset($estadisticasPorAnio[$anio]) || $total <= 0) {
            continue;
        }

        $estadisticasPorAnio[$anio][$clave] += $total;
        $seriesPermitidas[$clave]['total'] += $total;
        $totalEstadisticas += $total;
    }

    if (isset($seriesPermitidas['misas'])) {
        foreach ($estadisticasMisas as $fila) {
            $anio = (int) ($fila['anio'] ?? 0);
            $total = (int) ($fila['total'] ?? 0);
            if (!isset($estadisticasPorAnio[$anio]) || $total <= 0) {
                continue;
            }

            $estadisticasPorAnio[$anio]['misas'] += $total;
            $seriesPermitidas['misas']['total'] += $total;
            $totalEstadisticas += $total;
        }
    }
}

$maxValor = 0;
foreach ($aniosGrafico as $anio) {
    foreach ($seriesPermitidas as $clave => $serie) {
        $maxValor = max($maxValor, (int) ($estadisticasPorAnio[$anio][$clave] ?? 0));
    }
}
$pasoY = max(1, (int) ceil($maxValor / 4));
$yMax = $pasoY * 4;
$lineasY = [];
for ($i = 4; $i >= 0; $i--) {
    $lineasY[] = $i * $pasoY;
}

$gruposBarras = [];
foreach ($aniosGrafico as $anio) {
    $barras = [];
    foreach ($seriesPermitidas as $clave => $serie) {
        $valor = (int) ($estadisticasPorAnio[$anio][$clave] ?? 0);
        $barras[] = [
            'clave' => $clave,
            'valor' => $valor,
            'altura' => $yMax > 0 ? max(0, ($valor / $yMax) * 100) : 0,
        ];
    }
    $gruposBarras[] = ['anio' => $anio, 'barras' => $barras];
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
    <link rel="stylesheet" type="text/css" href="../assets/css/style.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/style.css'); ?>">
    <link rel="stylesheet" type="text/css" href="../assets/css/logo-cabecera.css?v=<?= filemtime(__DIR__ . '/../assets/css/logo-cabecera.css') ?>">
    <link rel="stylesheet" type="text/css" href="../assets/css/dashboard.css?v=<?= filemtime(__DIR__ . '/../assets/css/dashboard.css') ?>">
    <script src="../assets/js/jquery-3.6.4.min.js"></script>
</head>

<body>
    <header class="navbar-light fixed-top header-static bg-mode">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="VistaPersonal.php">
                    <img class="light-mode-item navbar-brand-item" src="../assets/images/logo.png?v=<?= filemtime(__DIR__ . '/../assets/images/logo.png') ?>" alt="logo">
                    <img class="dark-mode-item navbar-brand-item" src="../assets/images/logo-light.png?v=<?= filemtime(__DIR__ . '/../assets/images/logo-light.png') ?>" alt="logo">
                </a>

                <ul class="nav flex-nowrap align-items-center ms-sm-3 list-unstyled">
                    <li class="nav-item ms-2">
                        <a class="nav-link bg-light icon-md btn btn-light p-0" href="Configuracion.php" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Configurar">
                            <i class="bi bi-gear-fill fs-6"> </i>
                        </a>
                    </li>

                    <li class="nav-item ms-2 dropdown">
                        <a class="nav-link btn icon-md p-0" href="#" id="profileDropdown" role="button" data-bs-auto-close="outside" data-bs-display="static" data-bs-toggle="dropdown" aria-expanded="false">
                            <img class="avatar-img rounded border border-white border-3" src="<?= $escapar($rutaPerfil) ?>" alt="">
                        </a>
                        <ul class="dropdown-menu dropdown-animation dropdown-menu-end pt-3 small me-md-n3" aria-labelledby="profileDropdown">
                            <li class="px-3">
                                <div class="d-flex align-items-center position-relative">
                                    <div class="avatar me-3">
                                        <img class="avatar-img rounded-circle" src="<?= $escapar($rutaPerfil) ?>" alt="avatar">
                                    </div>
                                    <div>
                                        <a class="h6 stretched-link" href="#"><?= $escapar($nombreCompleto) ?></a>
                                        <p class="small m-0"><?= $escapar($cargo) ?> <i class="bi bi-patch-check-fill text-success small"></i></p>
                                    </div>
                                </div>
                                <a class="dropdown-item btn btn-primary-soft btn-sm my-2 text-center" href="PerfilAdmin.php">Ver mi Perfil</a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="Configuracion.php"><i class="bi bi-gear fa-fw me-2"></i>Configuración</a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="Reglamento.php" target="_blank"> <i class="fa-fw bi bi-card-text me-2"></i>Reglamento</a>
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
                                            <path d="M8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6zm0 1a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z"></path>
                                            <use href="#"></use>
                                        </svg>
                                    </button>
                                    <button type="button" class="btn btn-modeswitch nav-link text-primary-hover mb-0" data-bs-theme-value="dark" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Oscuro">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-moon-stars fa-fw mode-switch" viewBox="0 0 16 16">
                                            <path d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278zM4.858 1.311A7.269 7.269 0 0 0 1.025 7.71c0 4.02 3.279 7.276 7.319 7.276a7.316 7.316 0 0 0 5.205-2.162c-.337.042-.68.063-1.029.063-4.61 0-8.343-3.714-8.343-8.29 0-1.167.242-2.278.681-3.286z"></path>
                                            <path d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.734 1.734 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.734 1.734 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.734 1.734 0 0 0 1.097-1.097l.387-1.162zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.156 1.156 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.156 1.156 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732L13.863.1z"></path>
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
        <div class="container personal-dashboard-container">
            <div class="row g-4">
                <div class="col-lg-3">
                    <div class="d-flex align-items-center d-lg-none">
                        <button class="border-0 bg-transparent" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSideNavbar" aria-controls="offcanvasSideNavbar">
                            <span class="btn btn-primary"><i class="fa-solid fa-sliders-h"></i></span>
                            <span class="h6 mb-0 fw-bold d-lg-none ms-2">Otras Opciones</span>
                        </button>
                    </div>

                    <nav class="navbar navbar-expand-lg mx-0">
                        <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasSideNavbar">
                            <div class="offcanvas-header">
                                <a class="navbar-brand" href="#">
                                    <img class="light-mode-item navbar-brand-item" src="../assets/images/logo.png?v=<?= filemtime(__DIR__ . '/../assets/images/logo.png') ?>" alt="logo">
                                    <img class="dark-mode-item navbar-brand-item" src="../assets/images/logo-light.png?v=<?= filemtime(__DIR__ . '/../assets/images/logo-light.png') ?>" alt="logo">
                                </a>
                                <button type="button" class="btn-close text-reset ms-auto" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                            </div>

                            <div class="offcanvas-body d-block px-2 px-lg-0">
                                <div class="card overflow-hidden">
                                    <div class="h-50px" style="background-image:url(<?= $escapar($rutaFondo) ?>); background-position: center; background-size: cover; background-repeat: no-repeat;">
                                    </div>
                                    <div class="card-body pt-0">
                                        <div class="text-center">
                                            <div class="avatar avatar-lg mt-n5 mb-3">
                                                <img class="avatar-img rounded border border-white border-3" src="<?= $escapar($rutaPerfil) ?>" alt="">
                                            </div>
                                            <h5 class="mb-0"> <a href="PerfilAdmin.php"><?= $escapar($nombreCompleto) ?></a> </h5>
                                            <small><?= $escapar($cargo) ?></small>
                                            <?php if ($frase !== ''): ?>
                                                <p class="mt-3"><?= $escapar($frase) ?></p>
                                            <?php endif; ?>
                                        </div>

                                        <hr>

                                        <?php ControladorAcceso::renderMenu(['drive' => true]); ?>
                                    </div>
                                    <div class="card-footer text-center py-2">
                                        <a class="btn btn-link btn-sm" href="PerfilAdmin.php">Ver mi Perfil</a>
                                    </div>
                                </div>
                                <p class="small text-center mt-1">©2026 <a class="text-reset" target="_blank" href="https://sacaba.gob.bo/index.php/inicio/cultura/arte-y-arquitectura"> San Pedro de Sacaba </a></p>
                            </div>
                        </div>
                    </nav>
                </div>

                <div class="col-md-8 col-lg-6 vstack gap-4">
                    <div class="dashboard-page">
                        <section class="dash-hero">
                            <div class="dash-hero-copy">
                                <span class="dash-eyebrow"><i class="bi bi-stars"></i> Panel de control</span>
                                <h2>Bienvenido, <?= $escapar($nombreMostrar) ?></h2>
                                <p>Una vista rápida de todo lo que sucede en la Parroquia San Pedro de Sacaba.</p>
                            </div>
                            <div class="dash-hero-date">
                                <span class="dash-status"><span class="dash-dot"></span>Sistema al día</span>
                                <div class="dash-hero-date-main">
                                    <span class="dash-date-icon"><i class="bi bi-calendar3"></i></span>
                                    <div>
                                        <strong><?= $escapar($fechaHero) ?></strong>
                                        <span><?= $escapar($anioActual) ?></span>
                                    </div>
                                </div>
                                <span class="dash-role"><i class="bi bi-person-badge"></i> <?= $escapar($cargo) ?></span>
                            </div>
                        </section>

                        <?php if ($totalKpis > 0): ?>
                            <div class="row g-3">
                                <?php foreach ($kpis as $kpi): ?>
                                    <div class="<?= $escapar($colKpi) ?>">
                                        <article class="dash-stat <?= $escapar($kpi['clase']) ?>">
                                            <div class="dash-stat-icon"><i class="bi <?= $escapar($kpi['icono']) ?>"></i></div>
                                            <div class="dash-stat-copy">
                                                <span><?= $escapar($kpi['etiqueta']) ?></span>
                                                <strong><?= (int) $kpi['valor'] ?></strong>
                                                <small><?= $escapar($kpi['nota']) ?></small>
                                            </div>
                                            <span class="dash-stat-mark"><i class="bi <?= $escapar($kpi['marca']) ?>"></i></span>
                                            <i class="bi <?= $escapar($kpi['icono']) ?> dash-watermark"></i>
                                        </article>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="row g-3">
                            <div class="col-xl-7">
                                <section class="dash-panel h-100">
                                    <div class="dash-panel-head">
                                        <div class="dash-panel-title">
                                            <span class="dash-panel-icon gold"><i class="bi bi-calendar2-week"></i></span>
                                            <div>
                                                <h3>Próximas celebraciones</h3>
                                                <p>Reservas programadas para los próximos días.</p>
                                            </div>
                                        </div>
                                        <?php if ($puedeCelebracion): ?>
                                            <a class="dash-link" href="VistaReservas.php">Ver agenda <i class="bi bi-arrow-right"></i></a>
                                        <?php endif; ?>
                                    </div>

                                    <?php if (!$puedeCelebracion): ?>
                                        <div class="dash-empty">
                                            <i class="bi bi-shield-lock"></i>
                                            <strong>No tienes acceso a la agenda de reservas</strong>
                                            <p>Puedes consultar las actividades de la parroquia desde Inicio.</p>
                                        </div>
                                    <?php elseif (empty($proximasReservas)): ?>
                                        <div class="dash-empty">
                                            <i class="bi bi-calendar2-x"></i>
                                            <strong>No hay celebraciones próximas</strong>
                                            <p>Cuando se registren nuevas reservas aparecerán aquí.</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="dash-agenda">
                                            <div class="dash-agenda-date">
                                                <span class="dash-agenda-day"><?= $escapar($fechaAgenda['dia']) ?></span>
                                                <div>
                                                    <strong><?= $escapar($fechaAgenda['larga']) ?></strong>
                                                    <small><?= $cantidadAgenda ?> reservas programadas</small>
                                                </div>
                                                <span class="dash-agenda-count"><?= $cantidadAgenda ?> próximas</span>
                                            </div>
                                            <div class="dash-agenda-list">
                                                <?php foreach ($proximasReservas as $reserva): ?>
                                                    <?php
                                                    $fechaReserva = $formatearFecha($reserva['FechaReal'] ?? '');
                                                    $horaReserva = substr((string) ($reserva['HoraReal'] ?? ''), 0, 5);
                                                    $tipoReserva = trim((string) ($reserva['TipoCel'] ?? ''));
                                                    $iconoTipo = $iconosTipo[strtolower($tipoReserva)] ?? 'instrucciones.png';
                                                    $quienReserva = trim((string) ($reserva['Quien'] ?? ''));
                                                    $realizacionReserva = trim((string) ($reserva['Realizacion'] ?? ''));
                                                    $detalleReserva = $quienReserva !== '' ? $quienReserva : 'Sin referencia';
                                                    if ($realizacionReserva !== '') {
                                                        $detalleReserva .= ' · ' . $realizacionReserva;
                                                    }
                                                    $estadoReserva = trim((string) ($reserva['EstadoRes'] ?? ''));
                                                    $claseEstado = $estadoReserva === 'Reservado' ? '' : ' cancelado';
                                                    ?>
                                                    <div class="dash-agenda-item">
                                                        <span class="dash-agenda-time">
                                                            <?= $escapar($horaReserva) ?>
                                                            <?php if ((string) ($reserva['FechaReal'] ?? '') !== $primeraFecha): ?>
                                                                <small><?= $escapar($fechaReserva['dia'] . ' ' . $fechaReserva['mesCorto']) ?></small>
                                                            <?php endif; ?>
                                                        </span>
                                                        <span class="dash-agenda-icon">
                                                            <img src="../assets/images/icon/<?= $escapar($iconoTipo) ?>" alt="">
                                                        </span>
                                                        <div class="dash-agenda-copy">
                                                            <strong><?= $escapar($tipoReserva !== '' ? $tipoReserva : 'Celebración') ?></strong>
                                                            <small><?= $escapar($detalleReserva) ?></small>
                                                        </div>
                                                        <span class="dash-agenda-badge<?= $escapar($claseEstado) ?>"><?= $escapar($estadoReserva !== '' ? $estadoReserva : 'Reservado') ?></span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </section>
                            </div>

                            <div class="col-xl-5">
                                <section class="dash-panel h-100">
                                    <div class="dash-panel-head">
                                        <div class="dash-panel-title">
                                            <span class="dash-panel-icon blue"><i class="bi bi-clock-history"></i></span>
                                            <div>
                                                <h3>Actividades recientes</h3>
                                                <p><?= $totalActividades === 1 ? '1 evento registrado.' : $totalActividades . ' eventos registrados.' ?></p>
                                            </div>
                                        </div>
                                        <a class="dash-link" href="VistaPersonal.php#tab-1">Ver todas <i class="bi bi-arrow-right"></i></a>
                                    </div>
                                    <?php if (empty($actividadesRecientes)): ?>
                                        <div class="dash-empty">
                                            <i class="bi bi-calendar2-event"></i>
                                            <strong>No hay actividades registradas</strong>
                                            <p>Las actividades publicadas aparecerán en este espacio.</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="dash-activity-list">
                                            <?php foreach ($actividadesRecientes as $actividad): ?>
                                                <?php
                                                $fechaActividad = $formatearFecha($actividad['FechaActividad'] ?? '', false);
                                                $imagenActividad = basename((string) ($actividad['ImagenActividad'] ?? ''));
                                                $rutaImagen = '../assets/images/events/Generico.png';
                                                if ($imagenActividad !== '' && $imagenActividad !== 'Ninguno'
                                                    && is_file(__DIR__ . '/../assets/images/events/' . $imagenActividad)) {
                                                    $rutaImagen = '../assets/images/events/' . rawurlencode($imagenActividad);
                                                }
                                                $estadoActividad = trim((string) ($actividad['EstActividad'] ?? ''));
                                                $claseActividad = $estadoActividad === 'Activo' ? ' activo' : '';
                                                ?>
                                                <div class="dash-activity">
                                                    <div class="dash-activity-thumb">
                                                        <img src="<?= $escapar($rutaImagen) ?>" alt="">
                                                        <span><?= $escapar($fechaActividad['dia'] . ' ' . $fechaActividad['mesCorto']) ?></span>
                                                    </div>
                                                    <div class="dash-activity-copy">
                                                        <strong><?= $escapar($actividad['TituloActividad'] ?? '') ?></strong>
                                                        <small><i class="bi bi-geo-alt me-1"></i><?= $escapar($actividad['LugarAct'] ?? '') ?></small>
                                                    </div>
                                                    <span class="dash-activity-badge<?= $escapar($claseActividad) ?>"><?= $escapar($estadoActividad !== '' ? $estadoActividad : 'Concluido') ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </section>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-xl-7">
                                <section class="dash-panel h-100">
                                    <div class="dash-panel-head">
                                        <div class="dash-panel-title">
                                            <span class="dash-panel-icon purple"><i class="bi bi-bar-chart-line-fill"></i></span>
                                            <div>
                                                <h3>Estadísticas generales</h3>
                                                <p>Sacramentos y misas registradas</p>
                                            </div>
                                        </div>
                                        <span class="dash-panel-badge soft">
                                            <?php if (!$puedeEstadisticas): ?>
                                                Restringido
                                            <?php else: ?>
                                                <?= $totalEstadisticas ?> registros
                                            <?php endif; ?>
                                        </span>
                                    </div>

                                    <?php if (!$puedeEstadisticas): ?>
                                        <div class="dash-empty">
                                            <i class="bi bi-shield-lock"></i>
                                            <strong>No tienes acceso a las estadísticas</strong>
                                            <p>Las estadísticas están disponibles según los permisos de certificados y celebraciones.</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="dash-chart-controls">
                                            <?php foreach ($seriesPermitidas as $clave => $serie): ?>
                                                <label class="dash-chart-toggle" style="--serie-bg: <?= $escapar($serie['fondo']) ?>; --serie-border: <?= $escapar($serie['borde']) ?>;">
                                                    <input type="checkbox" data-dash-toggle="<?= $escapar($clave) ?>" checked aria-label="Mostrar <?= $escapar($serie['label']) ?>">
                                                    <span class="dash-chart-swatch"></span>
                                                    <span><?= $escapar($serie['label']) ?></span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>

                                        <div class="dash-bars">
                                            <div class="dash-bars-y">
                                                <?php foreach ($lineasY as $valor): ?>
                                                    <span><?= (int) $valor ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                            <div class="dash-bars-body">
                                                <div class="dash-bars-grid" aria-hidden="true">
                                                    <?php foreach ($lineasY as $valor): ?>
                                                        <span></span>
                                                    <?php endforeach; ?>
                                                </div>
                                                <div class="dash-bars-groups">
                                                    <?php foreach ($gruposBarras as $grupo): ?>
                                                        <div class="dash-bars-group">
                                                            <div class="dash-bars-columns">
                                                                <?php foreach ($grupo['barras'] as $barra): ?>
                                                                    <?php $serieBarra = $seriesPermitidas[$barra['clave']]; ?>
                                                                    <div class="dash-bar-wrap" data-dash-serie="<?= $escapar($barra['clave']) ?>">
                                                                        <span class="dash-bar-value"><?= (int) $barra['valor'] ?></span>
                                                                        <div class="dash-bar" style="--bar: <?= $escapar($serieBarra['color']) ?>; height: <?= round((float) $barra['altura'], 2) ?>%;"></div>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                            <span class="dash-bars-year"><?= (int) $grupo['anio'] ?></span>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </section>
                            </div>
                            <div class="col-xl-5">
                                <div class="dash-side-stack">
                                    <?php if ($puedeCatequesis): ?>
                                        <section class="dash-panel dash-cate">
                                            <div class="dash-panel-head">
                                                <div class="dash-panel-title">
                                                    <span class="dash-panel-icon green"><i class="bi bi-people-fill"></i></span>
                                                    <div>
                                                        <h3>Catequesis</h3>
                                                        <p>Resumen de coordinación.</p>
                                                    </div>
                                                </div>
                                                <span class="dash-panel-badge soft">Activo</span>
                                            </div>
                                            <div class="dash-cate-metrics">
                                                <div class="dash-cate-metric">
                                                    <strong><?= (int) ($catequesis['activos'] ?? 0) ?></strong>
                                                    <span>Catequistas activos</span>
                                                </div>
                                                <div class="dash-cate-metric">
                                                    <strong><?= (int) ($catequesis['grupos'] ?? 0) ?></strong>
                                                    <span>Grupos registrados</span>
                                                </div>
                                                <div class="dash-cate-metric">
                                                    <strong><?= (int) ($catequesis['inscripciones'] ?? 0) ?></strong>
                                                    <span>Inscripciones</span>
                                                </div>
                                            </div>
                                            <div class="dash-cate-footer">
                                                <a href="VistaCoordinadorAdmin.php">Ver coordinación <i class="bi bi-arrow-right"></i></a>
                                            </div>
                                        </section>
                                    <?php endif; ?>
                                    <section class="dash-panel dash-tip">
                                        <div class="dash-tip-icon"><i class="bi bi-lightbulb-fill"></i></div>
                                        <div class="dash-tip-copy">
                                            <h3>Mantén seguros los accesos</h3>
                                            <p>Actualiza tu contraseña en caso necesario.</p>
                                            <?php if ($puedePersonal): ?>
                                                <a href="vistaGesPersonal.php">Ir a Personal <i class="bi bi-arrow-right"></i></a>
                                            <?php endif; ?>
                                        </div>
                                    </section>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="../assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/functions.js"></script>
    <script src="../assets/js/tema.js"></script>
    <script>
        $(document).ready(function() {
            function obtenerEstado() {
                $.ajax({
                    url: '../VerificarPersonal.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.Estado && response.DescripCar && response.PerfilPer) {
                            if (response.Estado === "Inactivo") {
                                window.location.replace("PerfilAdmin.php");
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
            obtenerEstado();
            setInterval(obtenerEstado, 5000);
        });

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('[data-dash-toggle]').forEach(function(input) {
                var actualizarSerie = function() {
                    var serie = input.getAttribute('data-dash-toggle');
                    document.querySelectorAll('[data-dash-serie="' + serie + '"]').forEach(function(grupo) {
                        grupo.style.display = input.checked ? '' : 'none';
                    });
                    if (input.closest('.dash-chart-toggle')) {
                        input.closest('.dash-chart-toggle').classList.toggle('is-off', !input.checked);
                    }
                };
                input.addEventListener('change', actualizarSerie);
            });
        });
    </script>
</body>

</html>
