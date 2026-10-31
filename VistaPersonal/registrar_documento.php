<?php
require_once '../Controlador/controladorReserva.php';

session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../logout.php");
    exit();
}
$usuario = $_SESSION['usuario'];
require_once __DIR__ . '/../Controlador/controladorAcceso.php';
ControladorAcceso::exigir('certificado');

$controller = new ControladorReserva();
$codIns = isset($_GET['CodIns']) ? (int) $_GET['CodIns'] : 0;
$mensaje = '';
$error = '';

$situaciones = [];
foreach (['padrino', 'madrina'] as $clave) {
    if (isset($_GET['cond_' . $clave]) && in_array((string) $_GET['cond_' . $clave], ['5', '8'], true)) {
        $situaciones[$clave] = (int) $_GET['cond_' . $clave];
    }
}

$busqueda = isset($_GET['buscar']) && is_string($_GET['buscar']) ? trim($_GET['buscar']) : '';
$sacramentoVolver = isset($_GET['sac']) && is_string($_GET['sac']) ? $_GET['sac'] : '';
if (!in_array($sacramentoVolver, ['bautizo', 'confirmacion', 'matrimonio'], true)) {
    $sacramentoVolver = '';
}
$contextoVolver = '';
$urlVolver = 'VistaSolicitante.php';
if ($busqueda !== '' && $sacramentoVolver !== '') {
    $contextoVolver = '&buscar=' . rawurlencode($busqueda) . '&sac=' . $sacramentoVolver;
    $urlVolver .= '?ciPersonaBus=' . rawurlencode($busqueda) . '&' . $sacramentoVolver . '=';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['subir_documento'])) {
        if ($controller->registrarDocumentoRequerido($_POST, $_FILES)) {
            header("Location: registrar_documento.php?CodIns=" . $codIns . "&doc=1" . $contextoVolver);
            exit();
        }
        $error = $controller->obtenerErrorDocumento() ?: 'No se pudo guardar el archivo.';
    } elseif (isset($_POST['registrar_documento'])) {
        if ($controller->registrarDocumentoExt($_POST, $_FILES)) {
            header("Location: registrar_documento.php?CodIns=" . $codIns . "&doc=1" . $contextoVolver);
            exit();
        }
        $error = $controller->obtenerErrorDocumento() ?: 'No se pudo registrar el documento.';
    }
}

if (isset($_GET['doc'])) {
    $mensaje = 'Documento registrado correctamente.';
}

$inscripcion = $codIns > 0 ? $controller->obtenerInscripcionSacramento($codIns) : null;
$requisitos = $inscripcion ? $controller->construirRequisitos($codIns, $situaciones) : null;
$personas = $requisitos['personas'] ?? [];
$documentos = $requisitos['documentos'] ?? [];
$tiposDocumento = $controller->obtenerTiposDocumento();

$nombrePrincipal = '';
$nombreNovio = '';
$nombreNovia = '';
foreach ($personas as $persona) {
    $nombreCompleto = trim(implode(' ', array_filter([$persona['Nombre'], $persona['ApPaterno'], $persona['ApMaterno']])));
    if ($nombrePrincipal === '' && in_array($persona['Rol'], ['Bautizado', 'Celebrante', 'Confirmante'], true)) {
        $nombrePrincipal = $nombreCompleto;
    }
    if ($persona['Rol'] === 'Novio') {
        $nombreNovio = $nombreCompleto;
    }
    if ($persona['Rol'] === 'Novia') {
        $nombreNovia = $nombreCompleto;
    }
}
if ($nombrePrincipal === '') {
    $nombrePrincipal = trim($nombreNovio . ($nombreNovia !== '' ? ' y ' . $nombreNovia : ''));
}

$porcentaje = $requisitos && $requisitos['total'] > 0
    ? (int) round($requisitos['adjuntos'] * 100 / $requisitos['total'])
    : 0;

$notasSacramento = [
    1 => 'Los padrinos deben ser católicos y no pueden vivir en concubinato ni estar casados solo por lo civil.',
    3 => 'Inscripciones: 23 de enero, de 8:00 a 12:00 y de 14:00 a 16:00.',
    4 => 'Reservar 6 meses antes de la boda y presentar todos los documentos 3 meses antes. Informes matrimoniales: martes de 8:30 a 11:00.',
];
$notaSacramento = $inscripcion ? ($notasSacramento[(int) $inscripcion['CodSac']] ?? '') : '';

$etiquetaFecha = 'Registro';
$fechaCabecera = $inscripcion['FechaIns'] ?? '';
if ($inscripcion && (int) $inscripcion['CodSac'] === 1 && !empty($inscripcion['FechaSacramento'])) {
    $etiquetaFecha = 'Sacramento';
    $fechaCabecera = $inscripcion['FechaSacramento'];
}

$estadosDocumento = [
    'adjunto' => ['texto' => 'Adjunto', 'clase' => 'bg-success-subtle text-success', 'icono' => 'bi-check-circle'],
    'registrado' => ['texto' => 'Datos registrados', 'clase' => 'bg-primary-subtle text-primary', 'icono' => 'bi-database-check'],
    'pendiente' => ['texto' => 'Pendiente', 'clase' => 'bg-secondary-subtle text-secondary', 'icono' => 'bi-clock'],
    'noaplica' => ['texto' => 'No aplica', 'clase' => 'bg-warning-subtle text-warning', 'icono' => 'bi-slash-circle'],
];

$etiquetasRol = [
    'Bautizado' => 'Bautizado', 'Celebrante' => 'Feligrés', 'Confirmante' => 'Confirmante',
    'Novio' => 'Novio', 'Novia' => 'Novia', 'Padre' => 'Padre', 'Madre' => 'Madre',
    'Papá' => 'Papá', 'Mamá' => 'Mamá', 'Papá Novio' => 'Papá del novio',
    'Mamá Novio' => 'Mamá del novio', 'Papá Novia' => 'Papá de la novia',
    'Mamá Novia' => 'Mamá de la novia', 'Padrino' => 'Padrino', 'Madrina' => 'Madrina',
    'Testigo Novio' => 'Testigo del novio', 'Testigo Novia' => 'Testigo de la novia',
];

$badgeEstado = function ($estado) use ($estadosDocumento) {
    $info = $estadosDocumento[$estado] ?? $estadosDocumento['pendiente'];
    return '<span class="badge ' . $info['clase'] . '"><i class="bi ' . $info['icono'] . ' me-1"></i>' . $info['texto'] . '</span>';
};

$accionDocumento = function ($item) use ($codIns) {
    if (!$item['aplica']) {
        return '<span class="text-muted">—</span>';
    }

    $html = '';
    if (!empty($item['archivo'])) {
        $html .= '<a class="btn btn-sm btn-light border me-1" target="_blank" href="ver_documento_presentado.php?archivo=' . rawurlencode($item['archivo']) . '"><i class="bi bi-eye"></i> Ver</a>';
    }
    $texto = !empty($item['archivo']) ? 'Reemplazar' : 'Adjuntar';
    $html .= '<form method="POST" enctype="multipart/form-data" class="d-inline-flex align-items-center mb-0">'
        . '<input type="hidden" name="subir_documento" value="1">'
        . '<input type="hidden" name="CodIns" value="' . (int) $codIns . '">'
        . '<input type="hidden" name="CodTDoc" value="' . (int) $item['tipo'] . '">'
        . '<input type="hidden" name="Dequien" value="' . htmlspecialchars($item['rol'], ENT_QUOTES, 'UTF-8') . '">'
        . '<input type="hidden" name="CodificDoc" value="' . htmlspecialchars($item['codificDoc'], ENT_QUOTES, 'UTF-8') . '">'
        . '<label class="btn btn-sm btn-success-soft mb-0" title="' . $texto . '">'
        . '<i class="bi bi-paperclip"></i> ' . $texto
        . '<input type="file" name="Archivo" accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf" class="d-none" onchange="this.form.submit()">'
        . '</label>'
        . '</form>';

    return $html;
};

$cambiarCondicional = function ($item) use ($codIns, $situaciones, $busqueda, $sacramentoVolver) {
    if (empty($item['grupoCondicional']) || !$item['aplica']) {
        return '';
    }

    $parametros = ['CodIns' => $codIns];
    if ($busqueda !== '' && $sacramentoVolver !== '') {
        $parametros['buscar'] = $busqueda;
        $parametros['sac'] = $sacramentoVolver;
    }
    foreach ($situaciones as $clave => $valor) {
        $parametros['cond_' . $clave] = $valor;
    }
    $parametros['cond_' . $item['grupoCondicional']] = (int) $item['tipo'] === 8 ? 5 : 8;

    return '<a class="small ms-1" href="registrar_documento.php?' . htmlspecialchars(http_build_query($parametros), ENT_QUOTES, 'UTF-8') . '">Cambiar</a>';
};

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
                            <img class="avatar-img rounded border border-white border-3" src="<?php echo ($usuario['PerfilPer'] == 'Ninguno' || empty($usuario['PerfilPer'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$usuario['PerfilPer']}?v=" . time(); ?>" alt="">
                        </a>
                        <ul class="dropdown-menu dropdown-animation dropdown-menu-end pt-3 small me-md-n3" aria-labelledby="profileDropdown">
                            <li class="px-3">
                                <div class="d-flex align-items-center position-relative">
                                    <div class="avatar me-3">
                                        <img class="avatar-img rounded-circle" src="<?php echo ($usuario['PerfilPer'] == 'Ninguno' || empty($usuario['PerfilPer'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$usuario['PerfilPer']}?v=" . time(); ?>" alt="avatar">
                                    </div>
                                    <div>
                                        <a class="h6 stretched-link" href="#"><?php echo $usuario['Nombre'] . " " . $usuario['Paterno'] . " " . $usuario['Materno']; ?></a>
                                        <p class="small m-0"><?php echo $usuario['DescripCar']; ?> <i class="bi bi-patch-check-fill text-success small"></i></p>
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
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sun fa-fw mode-switch" viewbox="0 0 16 16">
                                            <path d="M8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6zm0 1a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z">
                                            </path>
                                            <use href="#"></use>
                                        </svg>
                                    </button>
                                    <button type="button" class="btn btn-modeswitch nav-link text-primary-hover mb-0" data-bs-theme-value="dark" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Oscuro">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-moon-stars fa-fw mode-switch" viewbox="0 0 16 16">
                                            <path d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278zM4.858 1.311A7.269 7.269 0 0 0 1.025 7.71c0 4.02 3.279 7.276 7.319 7.276a7.316 7.316 0 0 0 5.205-2.162c-.337.042-.68.063-1.029.063-4.61 0-8.343-3.714-8.343-8.29 0-1.167.242-2.278.681-3.286z">
                                            </path>
                                            <path d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.734 1.734 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.734 1.734 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.734 1.734 0 0 0 1.097-1.097l.387-1.162zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.156 1.156 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.156 1.156 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732L13.863.1z">
                                            </path>
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
                                    <div class="h-50px" style="background-image:url(<?php echo ($usuario['FondoPer'] == 'Ninguno' || empty($usuario['FondoPer'])) ? '../assets/images/fondo/Ninguno.jpg' : "../assets/images/fondo/{$usuario['FondoPer']}?v=" . time(); ?>); background-position: center; background-size: cover; background-repeat: no-repeat;">
                                    </div>
                                    <div class="card-body pt-0">
                                        <div class="text-center">
                                            <div class="avatar avatar-lg mt-n5 mb-3">
                                                <img class="avatar-img rounded border border-white border-3" src="<?php echo ($usuario['PerfilPer'] == 'Ninguno' || empty($usuario['PerfilPer'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$usuario['PerfilPer']}?v=" . time(); ?>" alt="">
                                            </div>
                                            <h5 class="mb-0"> <a href="PerfilAdmin.php"><?php echo $usuario['Nombre'] . " " . $usuario['Paterno'] . " " . $usuario['Materno']; ?></a> </h5>
                                            <small><?php echo $usuario['DescripCar']; ?></small>
                                            <p class="mt-3"><?php echo $usuario['FrasePer']; ?></p>
                                        </div>

                                        <hr>

                                        <?php require_once __DIR__ . '/../Controlador/controladorAcceso.php'; ControladorAcceso::renderMenu(); ?>
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

                <div class="col-md-12 col-lg-9 vstack gap-4">
                    <?php if ($mensaje): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                        </div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card p-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                            <div>
                                <h2 class="h3 mb-1">Entrega de Documentos</h2>
                                <?php if ($inscripcion): ?>
                                    <div class="d-flex flex-wrap align-items-center gap-2 small text-muted">
                                        <span class="badge bg-primary-subtle text-primary"><?php echo htmlspecialchars($inscripcion['DescripSac'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <span class="fw-semibold text-body"><?php echo htmlspecialchars($nombrePrincipal, ENT_QUOTES, 'UTF-8'); ?></span>
                                        <span>·</span>
                                        <span><?php echo $etiquetaFecha; ?>: <?php echo !empty($fechaCabecera) ? date('d/m/Y', strtotime($fechaCabecera)) : '—'; ?></span>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted mb-0">Seleccione un certificado desde Información de Certificados.</p>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <?php if ($codIns > 0): ?>
                                    <span class="small text-muted">Código interno: <strong class="text-body"><?php echo (int) $codIns; ?></strong></span>
                                <?php endif; ?>
                                <a href="<?php echo htmlspecialchars($urlVolver, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-warning-soft btn-sm"><i class="bi bi-arrow-left"></i> Volver</a>
                            </div>
                        </div>
                    </div>

                    <?php if ($requisitos): ?>
                        <div class="card">
                            <div class="card-body d-flex flex-wrap align-items-center gap-4">
                                <div class="flex-grow-1" style="min-width:260px;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <strong><?php echo (int) $requisitos['adjuntos']; ?> de <?php echo (int) $requisitos['total']; ?> documentos adjuntos</strong>
                                        <span class="small text-muted"><?php echo $porcentaje; ?>% completado</span>
                                    </div>
                                    <div class="progress" style="height:8px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width:<?php echo $porcentaje; ?>%" aria-valuenow="<?php echo $porcentaje; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                <div class="border-start ps-4 text-end">
                                    <div class="small text-muted">Datos reutilizables del registro</div>
                                    <div class="fw-bold text-primary"><?php echo (int) $requisitos['reutilizables']; ?> documento<?php echo $requisitos['reutilizables'] === 1 ? '' : 's'; ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header bg-transparent d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <div>
                                    <h6 class="mb-0">Personas del sacramento</h6>
                                    <small class="text-muted">Se cargan automáticamente de la inscripción.</small>
                                </div>
                                <span class="badge bg-primary-subtle text-primary"><i class="bi bi-people me-1"></i><?php echo count($personas); ?> persona<?php echo count($personas) === 1 ? '' : 's'; ?></span>
                            </div>
                            <div class="card-body">
                                <div class="row g-2">
                                    <?php foreach ($personas as $persona): ?>
                                        <?php $nombreCompleto = trim(implode(' ', array_filter([$persona['Nombre'], $persona['ApPaterno'], $persona['ApMaterno']]))); ?>
                                        <div class="col-sm-6 col-xl-4">
                                            <div class="border rounded-3 p-2 h-100 d-flex align-items-center gap-2">
                                                <span class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:34px;height:34px;">
                                                    <i class="bi bi-person"></i>
                                                </span>
                                                <div class="min-w-0">
                                                    <div class="text-uppercase text-muted" style="font-size:.68rem;letter-spacing:.04em;"><?php echo htmlspecialchars($etiquetasRol[$persona['Rol']] ?? $persona['Rol'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                    <div class="fw-semibold small text-truncate"><?php echo htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8'); ?></div>
                                                    <div class="small text-muted">CI <?php echo htmlspecialchars((string) (!empty($persona['NumeroDocumento']) ? $persona['NumeroDocumento'] : $persona['CiPersona']), ENT_QUOTES, 'UTF-8'); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <?php foreach ($requisitos['secciones'] as $seccion): ?>
                            <div class="card">
                                <div class="card-header bg-transparent d-flex flex-wrap justify-content-between align-items-center gap-2">
                                    <div>
                                        <h6 class="mb-0"><?php echo htmlspecialchars($seccion['titulo'], ENT_QUOTES, 'UTF-8'); ?></h6>
                                        <?php if ($seccion['modo'] === 'lista'): ?>
                                            <small class="text-muted">Los datos ya registrados se reutilizan; solo falta adjuntar la copia digital.</small>
                                        <?php endif; ?>
                                    </div>
                                    <?php
                                    $cantidadAplicables = 0;
                                    if ($seccion['modo'] === 'lista') {
                                        foreach ($seccion['items'] as $item) {
                                            if ($item['aplica']) {
                                                $cantidadAplicables++;
                                            }
                                        }
                                    } else {
                                        foreach ($seccion['filas'] as $fila) {
                                            foreach ($fila['celdas'] as $celda) {
                                                if ($celda['aplica']) {
                                                    $cantidadAplicables++;
                                                }
                                            }
                                        }
                                    }
                                    ?>
                                    <span class="badge bg-secondary-subtle text-secondary"><i class="bi bi-list-check me-1"></i><?php echo $cantidadAplicables; ?> requerido<?php echo $cantidadAplicables === 1 ? '' : 's'; ?></span>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table align-middle mb-0">
                                            <?php if ($seccion['modo'] === 'pareja'): ?>
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="width:34%">Documento</th>
                                                        <th>Novio</th>
                                                        <th>Novia</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($seccion['filas'] as $fila): ?>
                                                        <tr>
                                                            <td class="fw-semibold"><?php echo htmlspecialchars($fila['documento'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                            <?php foreach ($fila['celdas'] as $celda): ?>
                                                                <td>
                                                                    <div class="d-flex flex-column gap-1 align-items-start">
                                                                        <?php echo $badgeEstado($celda['estado']); ?>
                                                                        <?php if ($celda['datos'] !== ''): ?>
                                                                            <small class="text-muted"><?php echo htmlspecialchars($celda['datos'], ENT_QUOTES, 'UTF-8'); ?></small>
                                                                        <?php endif; ?>
                                                                        <div class="d-flex flex-wrap align-items-center gap-1">
                                                                            <?php echo $accionDocumento($celda); ?>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            <?php endforeach; ?>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            <?php else: ?>
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="width:32%">Documento</th>
                                                        <th style="width:20%">Pertenece a</th>
                                                        <th style="width:22%">Datos ya registrados</th>
                                                        <th style="width:12%">Estado</th>
                                                        <th class="text-end">Archivo digital</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($seccion['items'] as $item): ?>
                                                        <tr>
                                                            <td>
                                                                <div class="fw-semibold"><?php echo htmlspecialchars($item['documento'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                                <?php if ($item['detalle'] !== ''): ?>
                                                                    <small class="text-muted"><?php echo htmlspecialchars($item['detalle'], ENT_QUOTES, 'UTF-8'); ?></small>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <?php if ($item['persona'] !== ''): ?>
                                                                    <div class="small"><?php echo htmlspecialchars($item['persona'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                                    <small class="text-muted"><?php echo htmlspecialchars($etiquetasRol[$item['rol']] ?? $item['rol'], ENT_QUOTES, 'UTF-8'); ?></small>
                                                                <?php else: ?>
                                                                    <span class="text-muted">—</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <?php if ($item['datos'] !== ''): ?>
                                                                    <small class="text-muted"><?php echo htmlspecialchars($item['datos'], ENT_QUOTES, 'UTF-8'); ?></small>
                                                                <?php else: ?>
                                                                    <small class="text-muted">—</small>
                                                                <?php endif; ?>
                                                                <?php if (!empty($item['generarCodIns'])): ?>
                                                                    <div><a class="small" target="_blank" href="<?php echo htmlspecialchars($item['generarPagina'], ENT_QUOTES, 'UTF-8'); ?>?CodIns=<?php echo (int) $item['generarCodIns']; ?>">Ver certificado</a></div>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <?php echo $badgeEstado($item['estado']); ?>
                                                                <?php echo $cambiarCondicional($item); ?>
                                                            </td>
                                                            <td class="text-end">
                                                                <?php echo $accionDocumento($item); ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            <?php endif; ?>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <div class="card">
                            <div class="card-body small text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Se guarda la copia digital; el original y la fotocopia se verifican en ventanilla.
                                <?php if ($notaSacramento !== ''): ?>
                                    <?php echo htmlspecialchars($notaSacramento, ENT_QUOTES, 'UTF-8'); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="bi bi-file-earmark-x display-6 text-muted"></i>
                                <h5 class="mt-3">Certificado no encontrado</h5>
                                <p class="text-muted mb-3">Verifique el código del certificado o vuelva a la búsqueda.</p>
                                <a href="<?php echo htmlspecialchars($urlVolver, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-warning-soft"><i class="bi bi-arrow-left"></i> Volver a Información de Certificados</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($inscripcion): ?>
                        <div class="card">
                            <div class="card-header bg-transparent d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <div>
                                    <h6 class="mb-0">Documentos registrados</h6>
                                    <small class="text-muted">Documentos ya guardados para este certificado.</small>
                                </div>
                                <span class="badge bg-secondary-subtle text-secondary"><?php echo count($documentos); ?></span>
                            </div>
                            <div class="card-body p-0">
                                <?php if (count($documentos) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table align-middle mb-0">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Código Documento</th>
                                                    <th>Pertenece a</th>
                                                    <th>Tipo Documento</th>
                                                    <th>Presentado</th>
                                                    <th>Archivo</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($documentos as $documento): ?>
                                                    <tr>
                                                        <td><?php echo !empty($documento['CodificDoc']) ? htmlspecialchars((string) $documento['CodificDoc'], ENT_QUOTES, 'UTF-8') : '—'; ?></td>
                                                        <td><?php echo htmlspecialchars($etiquetasRol[$documento['Dequien']] ?? $documento['Dequien'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><?php echo htmlspecialchars($documento['DescripTDoc'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><?php echo !empty($documento['FechaPres']) ? date('d/m/Y', strtotime($documento['FechaPres'])) : '—'; ?></td>
                                                        <td>
                                                            <?php if (!empty($documento['Archivo'])): ?>
                                                                <a class="btn btn-sm btn-light border" target="_blank" href="ver_documento_presentado.php?archivo=<?php echo rawurlencode($documento['Archivo']); ?>"><i class="bi bi-eye"></i> Ver</a>
                                                            <?php else: ?>
                                                                <span class="text-muted">—</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="p-4 text-center text-muted">No se han registrado documentos.</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header bg-transparent">
                                <h6 class="mb-0">Agregar un documento adicional</h6>
                                <small class="text-muted">Para documentos que no están en la lista del sacramento.</small>
                            </div>
                            <div class="card-body">
                                <form method="POST" enctype="multipart/form-data" class="row g-3">
                                    <input type="hidden" name="CodIns" value="<?php echo (int) $codIns; ?>">
                                    <input type="hidden" name="registrar_documento" value="1">

                                    <div class="form-group col-sm-6 col-lg-3">
                                        <label for="CodTDoc" class="form-label">Tipo de Documento</label>
                                        <select name="CodTDoc" class="form-select" id="CodTDoc" required onchange="toggleFields()">
                                            <?php foreach ($tiposDocumento as $tipo): ?>
                                                <option value="<?php echo (int) $tipo['CodTDoc']; ?>"><?php echo htmlspecialchars($tipo['DescripTDoc'], ENT_QUOTES, 'UTF-8'); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-6 col-lg-3">
                                        <label class="form-label">Fecha Presentada</label>
                                        <input type="date" class="form-control" name="FechaPres" required max="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                    <div class="form-group col-sm-6 col-lg-3">
                                        <label class="form-label">Código de Documento</label>
                                        <input type="text" class="form-control" name="CodificDoc" placeholder="Código Documento" maxlength="20">
                                    </div>
                                    <div class="form-group col-sm-6 col-lg-3">
                                        <label class="form-label">A quién Pertenece</label>
                                        <select name="Dequien" class="form-select" required>
                                            <?php if ($personas): ?>
                                                <?php foreach ($personas as $persona): ?>
                                                    <?php $nombreCompleto = trim(implode(' ', array_filter([$persona['Nombre'], $persona['ApPaterno'], $persona['ApMaterno']]))); ?>
                                                    <option value="<?php echo htmlspecialchars($persona['Rol'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars(($etiquetasRol[$persona['Rol']] ?? $persona['Rol']) . ' — ' . $nombreCompleto, ENT_QUOTES, 'UTF-8'); ?></option>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <option value="Celebrante">Feligrés</option>
                                                <option value="Novio">Novio</option>
                                                <option value="Novia">Novia</option>
                                                <option value="Padrino">Padrino</option>
                                                <option value="Madrina">Madrina</option>
                                            <?php endif; ?>
                                        </select>
                                    </div>

                                    <div class="col-12" id="conditionalFields" style="display:none;">
                                        <div class="row g-3">
                                            <div class="form-group col-sm-6 col-lg-4">
                                                <label class="form-label">Donde se realizó</label>
                                                <input type="text" class="form-control" name="ParroquiaBau" placeholder="Nombre de lugar" maxlength="30">
                                            </div>
                                            <div class="form-group col-sm-6 col-lg-2">
                                                <label class="form-label">Libro</label>
                                                <input type="text" class="form-control" name="NroLibro" placeholder="Libro" maxlength="10">
                                            </div>
                                            <div class="form-group col-sm-6 col-lg-2">
                                                <label class="form-label">Página</label>
                                                <input type="text" class="form-control" name="NroPag" placeholder="Página" maxlength="10">
                                            </div>
                                            <div class="form-group col-sm-6 col-lg-2">
                                                <label class="form-label">Partida</label>
                                                <input type="text" class="form-control" name="NroPart" placeholder="Partida" maxlength="10">
                                            </div>
                                            <div class="form-group col-sm-6 col-lg-2">
                                                <label class="form-label">Fecha Registro</label>
                                                <input type="date" class="form-control" name="FechaBau" max="<?php echo date('Y-m-d'); ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label" for="ArchivoAdicional">Archivo (JPG, PNG o PDF, máximo 5 MB)</label>
                                        <input type="file" class="form-control" id="ArchivoAdicional" name="Archivo" accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf">
                                    </div>

                                    <div class="col-12 d-flex flex-wrap gap-2">
                                        <a href="<?php echo htmlspecialchars($urlVolver, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-warning-soft"><i class="bi bi-arrow-bar-left"></i> Volver a Anterior</a>
                                        <button type="submit" class="btn btn-success-soft">Registrar Documento <i class="bi bi-upload"></i></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
    </main>

    <script>
        $(document).ready(function() {
            function obtenerEstado() {
                $.ajax({
                    url: '../VerificarPersonal.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.Estado && response.DescripCar) {
                            $('#estado').text(response.Estado);
                            $('#cargo').text(response.DescripCar);

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
    </script>
    <script>
        function toggleFields() {
            const select = document.getElementById('CodTDoc');
            const conditionalFields = document.getElementById('conditionalFields');
            if (!select || !conditionalFields) {
                return;
            }

            const tiposConPartida = ['2', '3', '5', '6', '8'];
            conditionalFields.style.display = tiposConPartida.indexOf(select.value) !== -1 ? 'block' : 'none';
        }

        document.addEventListener('DOMContentLoaded', toggleFields);
    </script>
    <script src="../assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/functions.js"></script>
    <script src="../assets/js/tema.js"></script>
</body>

</html>