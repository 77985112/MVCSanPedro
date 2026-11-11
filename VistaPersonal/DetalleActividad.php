<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../logout.php");
    exit();
}
$usuario = $_SESSION['usuario'];
require_once __DIR__ . '/../Controlador/controladorAcceso.php';
ControladorAcceso::exigir('inicio');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && empty($_FILES)
    && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    http_response_code(413);
    exit('El archivo supera el límite de carga. Use una imagen de hasta 2 MB o un video de hasta 10 MB.');
}


if (!isset($_GET['Actividad'])) {
    header("Location: IndexAdmin.php");
    exit();
}

$Actividad = $_GET['Actividad'];

require_once __DIR__ . '/../Controlador/controladorActividad.php';

$conn = new Conexion();
$conexion = $conn->getConnection();

$sql = "SELECT * FROM actividades WHERE idActividad = ? AND EstActividad <> 'Eliminado'";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $Actividad);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "No se encontró la actividad.";
    exit();
}

$actividad = $result->fetch_assoc();
$stmt->close();
$controller = new ControladorActividad();
$actividad['ImagenesExtrasFilas'] = $controller->imagenesExtras((int) $actividad['idActividad']);
$actividad['ImagenesExtras'] = array_column($actividad['ImagenesExtrasFilas'], 'ImagenActividad');
$esAutorActividad = (int) ($actividad['CodPer'] ?? 0) === (int) ($usuario['CodPer'] ?? 0);
$esParroco = $controller->puedeGestionarActividadParroquia($usuario);
$puedeEditarActividad = ($esAutorActividad || $esParroco)
    && ControladorActividad::esActividadEditable($actividad);
$puedeEliminarActividad = ($esAutorActividad || $esParroco);
$solicitaEdicion = ($_GET['editar'] ?? '') === '1' || $_SERVER['REQUEST_METHOD'] === 'POST';
$errorEdicion = '';
$valoresEdicion = $actividad;
if (empty($_SESSION['csrf_actividad_editar'])) {
    $_SESSION['csrf_actividad_editar'] = bin2hex(random_bytes(32));
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$puedeEditarActividad) {
        http_response_code(403);
        $errorEdicion = 'No tienes permiso para editar esta actividad de Parroquia.';
    } elseif (($_POST['accion'] ?? '') !== 'editar_actividad'
        || !is_string($_POST['csrf_actividad_editar'] ?? null)
        || !hash_equals($_SESSION['csrf_actividad_editar'], $_POST['csrf_actividad_editar'])) {
        http_response_code(403);
        $errorEdicion = 'La sesión del formulario venció. Revise los datos y vuelva a seleccionar los archivos nuevos.';
    } else {
        foreach (['TituloActividad', 'DetalleActividad', 'FechaActividad', 'HoraAct', 'LugarAct'] as $campo) {
            $valoresEdicion[$campo] = is_string($_POST[$campo] ?? null) ? trim($_POST[$campo]) : '';
        }
        try {
            $datosEdicion = $_POST;
            $datosEdicion['idActividad'] = (int) $actividad['idActividad'];
            $controller->actualizarActividadParroquia($datosEdicion, $usuario);
            $_SESSION['actividad_parroquia_actualizada'] = true;
            $_SESSION['csrf_actividad_editar'] = bin2hex(random_bytes(32));
            header('Location: VistaPersonal.php#tab-2', true, 303);
            exit();
        } catch (InvalidArgumentException | DomainException $e) {
            $errorEdicion = $e->getMessage();
        } catch (Throwable $e) {
            error_log('Editar actividad de Parroquia: ' . $e->getMessage());
            $errorEdicion = 'No se pudieron guardar los cambios. Intente nuevamente.';
        }
    }
} elseif ($solicitaEdicion && !$puedeEditarActividad) {
    $errorEdicion = 'Esta actividad no está disponible para editar. Las actividades concluidas se conservan sin cambios.';
}
$valorEdicion = static function ($campo) use ($valoresEdicion) {
    return htmlspecialchars((string) $valoresEdicion[$campo], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};
$fechaMinimaActividad = ControladorActividad::fechaMinimaActividad();

list($archivoImagen, $archivoVideo) = ControladorActividad::archivosActividad($actividad);$esVideoActividad = $archivoVideo !== null;$hayImagen = $archivoImagen !== null;$archivoActividad = rawurlencode($archivoImagen ?? 'Generico.png');

$diassemana = array("Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado");
$meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");

$fecha = new DateTime($actividad['FechaActividad']);
$fechaFormateada = $diassemana[$fecha->format('w')] . ", " . $fecha->format('d') . " de " . $meses[$fecha->format('n') - 1] . " de " . $fecha->format('Y');

$dia = $diassemana[$fecha->format('w')];
$mes = $fecha->format('d') . " de " . $meses[$fecha->format('n') - 1];

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


                <div class="col-md-8 col-lg-9 vstack gap-4">
                    <?php if ($errorEdicion !== ''): ?>
                    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($errorEdicion, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></div>
                    <?php endif; ?>
                    <?php if ($puedeEditarActividad && $solicitaEdicion): ?>
                    <div class="card card-body" id="editarActividad" style="scroll-margin-top:90px;">
                        <h1 class="h4">Editar actividad de Parroquia</h1>
                        <p class="small text-body-secondary">Actualice los datos y guarde los cambios. Si no selecciona archivos nuevos, se conservarán la imagen y el video actuales.</p>
                        <form id="formEditarActividadParroquia" action="DetalleActividad.php?Actividad=<?php echo (int) $actividad['idActividad']; ?>&amp;editar=1#editarActividad" method="POST" class="row g-3" enctype="multipart/form-data">
                            <input type="hidden" name="accion" value="editar_actividad">
                            <input type="hidden" name="csrf_actividad_editar" value="<?php echo htmlspecialchars($_SESSION['csrf_actividad_editar'], ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="col-12">
                                <label class="form-label" for="editarTituloActividad">Título de la actividad *</label>
                                <input type="text" class="form-control" id="editarTituloActividad" name="TituloActividad" maxlength="150" value="<?php echo $valorEdicion('TituloActividad'); ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="editarDetalleActividad">Descripción *</label>
                                <textarea class="form-control" id="editarDetalleActividad" name="DetalleActividad" rows="5" maxlength="<?php echo ControladorActividad::LIMITE_DESCRIPCION; ?>" aria-describedby="ayudaDescripcionEdicion" required><?php echo $valorEdicion('DetalleActividad'); ?></textarea>
                                <div class="form-text" id="ayudaDescripcionEdicion">Hasta 5.000 caracteres.</div>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label" for="editarFechaActividad">Fecha *</label>
                                <input type="date" class="form-control" id="editarFechaActividad" name="FechaActividad" min="<?php echo $fechaMinimaActividad; ?>" value="<?php echo $valorEdicion('FechaActividad'); ?>" aria-describedby="ayudaFechaEdicion" required>
                                <div class="form-text" id="ayudaFechaEdicion">Solo fechas desde hoy en adelante; al pasar la fecha la actividad queda concluida automáticamente.</div>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label" for="editarHoraAct">Hora *</label>
                                <input type="time" class="form-control" id="editarHoraAct" name="HoraAct" step="1" value="<?php echo $valorEdicion('HoraAct'); ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="editarLugarAct">Lugar *</label>
                                <input type="text" class="form-control" id="editarLugarAct" name="LugarAct" maxlength="<?php echo ControladorActividad::LIMITE_LUGAR; ?>" value="<?php echo $valorEdicion('LugarAct'); ?>" aria-describedby="ayudaLugarEdicion" required>
                                <div class="form-text" id="ayudaLugarEdicion">Hasta 255 caracteres; puede incluir la dirección y referencias.</div>
                            </div>
                            <div class="col-sm-6">
                                <?php $imagenesActuales = ControladorActividad::imagenesActividad($actividad); $filasExtras = $actividad['ImagenesExtrasFilas'] ?? []; ?>
                                <label class="form-label" for="editarImagenActividad">Imágenes actuales / nuevas imágenes (hasta 5 en total)</label>
                                <?php if ($imagenesActuales): ?>
                                <div id="galeriaActualImagen" class="d-flex flex-wrap align-items-start gap-2 mb-2">
                                    <div class="text-center">
                                        <img src="../assets/images/events/<?php echo rawurlencode($imagenesActuales[0]); ?>" class="rounded border" style="width:86px; height:66px; object-fit:cover;" alt="Imagen principal">
                                        <div class="form-text" style="font-size:.65rem;">Principal</div>
                                    </div>
                                    <?php foreach ($filasExtras as $filaExtra): ?>
                                    <div class="text-center">
                                        <img src="../assets/images/events/<?php echo rawurlencode($filaExtra['ImagenActividad']); ?>" class="rounded border" style="width:86px; height:66px; object-fit:cover;" alt="Imagen adicional">
                                        <div class="form-check m-0" style="font-size:.65rem;">
                                            <input class="form-check-input" type="checkbox" name="QuitarImagenExtra[]" value="<?php echo (int) $filaExtra['idActImg']; ?>" id="quitarExtra<?php echo (int) $filaExtra['idActImg']; ?>">
                                            <label class="form-check-label" for="quitarExtra<?php echo (int) $filaExtra['idActImg']; ?>">Quitar</label>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" id="editarImagenActividad" name="NuevaImagenActividad[]" multiple accept=".jpg,.jpeg,.png,image/jpeg,image/png" data-actividad-imagen="previewImagenEdicion" data-actividad-grupo="editarActividadParroquia" data-actividad-maximas="<?php echo max(0, ControladorActividad::MAXIMO_IMAGENES - count($imagenesActuales)); ?>" data-actividad-galeria="galeriaActualImagen">
                                <div class="form-text">JPG/PNG, hasta 2 MB c/u. Las imágenes nuevas se agregan a la actividad.</div>
                                <?php if ($hayImagen): ?>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="quitarImagenEdicion" name="QuitarImagen" value="1" data-quitar-actividad="editarImagenActividad">
                                    <label class="form-check-label small" for="quitarImagenEdicion">Quitar todas las imágenes actuales</label>
                                </div>
                                <?php endif; ?>
                                <div id="previewImagenEdicion" class="d-flex flex-wrap gap-2 mt-3"></div>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label" for="editarVideoActividad">Nuevo video (opcional)</label>
                                <input type="file" class="form-control" id="editarVideoActividad" name="NuevaVideoActividad" accept=".mp4,.webm,video/mp4,video/webm" data-actividad-video="previewVideoEdicion" data-actividad-grupo="editarActividadParroquia">
                                <div class="form-text">MP4/WebM, hasta 10 MB (se comprime al adjuntarlo).</div>
                                <?php if ($esVideoActividad || !empty($actividad['VideoUrl'])): ?>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="quitarVideoEdicion" name="QuitarVideo" value="1" data-quitar-actividad="editarVideoActividad">
                                    <label class="form-check-label small" for="quitarVideoEdicion">Quitar el video actual</label>
                                </div>
                                <?php endif; ?>
                                <video id="previewVideoEdicion" <?php if ($esVideoActividad): ?>src="../assets/images/events/<?php echo rawurlencode($archivoVideo); ?>"<?php endif; ?> class="w-100 rounded mt-3 actividad-video <?php echo $esVideoActividad ? '' : 'd-none'; ?>" controls playsinline preload="none" style="max-height:300px;"></video>
                                <div class="mt-3">
                                    <label class="form-label" for="editarVideoUrl">O pega un enlace de YouTube, TikTok o Facebook</label>
                                    <input type="url" class="form-control" id="editarVideoUrl" name="VideoUrl" maxlength="255" placeholder="https://www.youtube.com/watch?v=..." value="<?php echo $valorEdicion('VideoUrl'); ?>">
                                    <div class="form-text">Se reproduce dentro de la actividad. Si pegas un enlace, tiene prioridad sobre el archivo.</div>
                                    <div id="previewVideoUrl" class="mt-2"></div>
                                </div>
                            </div>
                            <div class="col-12 small">Conserve al menos una imagen o un video. Organiza: <strong>Parroquia</strong>.</div>
                            <div class="col-12 d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-success-soft"><i class="bi bi-check2 me-1"></i>Guardar cambios</button>
                                <a class="btn btn-light" href="VistaPersonal.php#tab-2">Cancelar</a>
                            </div>
                        </form>
                    </div>
                    <?php elseif ($puedeEditarActividad): ?>
                    <div><a class="btn btn-primary-soft" href="DetalleActividad.php?Actividad=<?php echo (int) $actividad['idActividad']; ?>&amp;editar=1#editarActividad"><i class="bi bi-pencil-square me-1"></i>Editar actividad</a></div>
                    <?php endif; ?>
                    <div class="card card-body <?php echo $hayImagen || !$esVideoActividad ? 'card-overlay-bottom' : 'bg-dark'; ?> border-0" <?php if ($hayImagen || !$esVideoActividad): ?>style="background-image:url('../assets/images/events/<?php echo $archivoActividad; ?>'); background-position:center; background-size:cover; background-repeat:no-repeat;" data-bs-toggle="modal" data-bs-target="#imageModal"<?php endif; ?>>
                        <?php if ($esVideoActividad): ?>
                          <video class="w-100 rounded actividad-video" src="../assets/images/events/<?php echo rawurlencode($archivoVideo); ?>" controls playsinline preload="none" style="max-height:65vh; background:#000;">Su navegador no puede reproducir este video. <a href="../assets/images/events/<?php echo rawurlencode($archivoVideo); ?>">Abrir video</a></video>
                        <?php endif; ?>
                        <div class="row g-3 justify-content-between">
                            <div class="col-lg-2">
                                <div class="bg-mode text-center rounded overflow-hidden p-1 d-inline-block">
                                    <div class="bg-primary p-2 text-white rounded-top small lh-1"> <?php echo $dia; ?></div>
                                    <h5 class="mb-0 py-2 lh-1"><?php echo $mes; ?></h5>
                                </div>
                            </div>
                        </div>
                        <div class="row g-3 justify-content-between align-items-center <?php echo $esVideoActividad ? 'mt-3' : 'mt-5 pt-5'; ?> position-relative z-index-9">
                            <div class="col-lg-9">
                                <h1 class="h3 mb-1 text-white"><?php echo htmlspecialchars($actividad['TituloActividad'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></h1>
                            </div>
                            <div class="col-lg-3 text-lg-end">
                                <a class="btn btn-light-soft" href="VistaPersonal.php"> Volver </a>
                            </div>
                        </div>
                    </div>
                    <div class="card card-body">
                        <div class="row g-4">
                            <div class="col-12">
                                <p class="mb-0"><?php echo htmlspecialchars($actividad['DetalleActividad'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
                            </div>
                            <hr class="mt-4">
                            <div class="col-sm-6 col-lg-4">
                                <h5>Hora de Actividad</h5>
                                <p class="small mb-0"> <?php echo date("g:i A", strtotime($actividad['HoraAct'])); ?></p>
                            </div>
                            <div class="col-sm-6 col-lg-4">
                                <h5>Fecha de Actividad</h5>
                                <h7 class=" mb-0"> <?php echo $fechaFormateada; ?> </h7>
                            </div>
                            <div class="col-sm-6 col-lg-4">
                                <h5>Organiza</h5>
                                <p class="small mb-0"><?php echo $actividad['TipoActividad']; ?></p>
                            </div>
                            <div class="col-sm-6 col-lg-4">
                                <h5>Estado</h5>
                                <p><span class="badge bg-<?php echo $actividad['EstActividad'] == 'Activo' ? 'success' : 'danger'; ?>"><?php echo ucfirst($actividad['EstActividad']); ?></span></p>
                            </div>
                            <div class="col-lg-6">
                                <h5>¿Donde?</h5>
                                <p> <?php echo htmlspecialchars($actividad['LugarAct'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>


    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel"><?php echo htmlspecialchars($actividad['TituloActividad'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if ($hayImagen || !$esVideoActividad): ?>
                    <img id="imagenGrandeDetalle" src="../assets/images/events/<?php echo $archivoActividad; ?>" class="img-fluid" alt="Imagen completa de <?php echo htmlspecialchars($actividad['TituloActividad'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
                    <?php $imagenesGaleria = ControladorActividad::imagenesActividad($actividad); ?>
                    <?php if (count($imagenesGaleria) > 1): ?>
                    <div class="d-flex flex-wrap justify-content-center gap-2 mt-3">
                        <?php foreach ($imagenesGaleria as $indiceGaleria => $nombreGaleria): ?>
                        <img src="../assets/images/events/<?php echo rawurlencode($nombreGaleria); ?>" class="rounded border" style="width:84px; height:64px; object-fit:cover; cursor:pointer;" data-imagen-modal="../assets/images/events/<?php echo rawurlencode($nombreGaleria); ?>" onclick="var galeriaGrande = document.getElementById('imagenGrandeDetalle'); galeriaGrande.src = this.dataset.imagenModal; galeriaGrande.alt = this.alt;" alt="Imagen <?php echo $indiceGaleria + 1; ?> de la actividad">
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                    <?php if ($esVideoActividad): ?>
                    <video class="w-100 rounded actividad-video<?php echo $hayImagen ? ' mt-3' : ''; ?>" src="../assets/images/events/<?php echo rawurlencode($archivoVideo); ?>" controls playsinline preload="none" style="max-height:65vh; background:#000;">Su navegador no puede reproducir este video. <a href="../assets/images/events/<?php echo rawurlencode($archivoVideo); ?>">Abrir video</a></video>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

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
    <script src="../assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/functions.js"></script>
    <script src="../assets/js/tema.js"></script>
  <script src="../assets/js/actividad-multimedia.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/actividad-multimedia.js'); ?>"></script>
    <script src="../assets/js/actividad-compresion.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/actividad-compresion.js'); ?>"></script>
    <script>
        // Vista previa del video por enlace: permite saber al instante si el
        // video es público o si la plataforma lo tiene restringido.
        (function () {
            var campo = document.getElementById('editarVideoUrl');
            var caja = document.getElementById('previewVideoUrl');
            if (!campo || !caja) return;

            function embed(url) {
                var u;
                try { u = new URL(url); } catch (e) { return null; }
                var host = u.hostname.toLowerCase().replace(/^www\./, '');
                var ruta = u.pathname || '';
                var m;

                if (['youtube.com', 'm.youtube.com', 'youtube-nocookie.com'].indexOf(host) >= 0) {
                    var id = u.searchParams.get('v') || '';
                    if (!id && (m = ruta.match(/^\/(?:shorts|embed|live|v)\/([A-Za-z0-9_-]{6,20})/))) id = m[1];
                    return /^[A-Za-z0-9_-]{6,20}$/.test(id) ? 'https://www.youtube.com/embed/' + id : null;
                }
                if (host === 'youtu.be') {
                    m = ruta.match(/^\/([A-Za-z0-9_-]{6,20})/);
                    return m ? 'https://www.youtube.com/embed/' + m[1] : null;
                }
                if (host === 'tiktok.com' || host === 'm.tiktok.com') {
                    m = ruta.match(/\/video\/(\d{6,25})/);
                    return m ? 'https://www.tiktok.com/embed/v2/' + m[1] : null;
                }
                if (['facebook.com', 'm.facebook.com', 'web.facebook.com', 'fb.watch'].indexOf(host) >= 0) {
                    return 'https://www.facebook.com/plugins/video.php?href=' + encodeURIComponent(url) + '&show_text=false';
                }
                return null;
            }

            function actualizar() {
                var valor = campo.value.trim();
                caja.replaceChildren();
                if (!valor) return;

                var src = embed(valor);
                if (!src) {
                    var error = document.createElement('div');
                    error.className = 'form-text text-danger';
                    error.textContent = 'Use un enlace de YouTube, TikTok o Facebook.';
                    caja.appendChild(error);
                    return;
                }

                var marco = document.createElement('iframe');
                marco.src = src;
                marco.className = 'w-100 rounded';
                marco.style.cssText = 'height:340px;border:0;background:#101722;';
                marco.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share');
                marco.setAttribute('allowfullscreen', '');
                marco.setAttribute('loading', 'lazy');
                marco.setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');
                marco.title = 'Vista previa del video';
                caja.appendChild(marco);

                var nota = document.createElement('div');
                nota.className = 'form-text';
                nota.textContent = 'Si aparece un aviso de inicio de sesión, "contenido no disponible" o un cuadro vacío, el video está restringido y no se verá en la página.';
                caja.appendChild(nota);
            }

            campo.addEventListener('input', actualizar);
            campo.addEventListener('change', actualizar);
            actualizar();
        })();
    </script>
</body>

</html>