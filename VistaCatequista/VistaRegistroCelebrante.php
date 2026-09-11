<?php
session_start();
if (!isset($_SESSION['CiPersona'])) {
    header("Location: ../logout.php");
    exit();
}
$Catequista = $_SESSION['CiPersona'];

require_once '../Controlador/ControladorPersona.php';
require_once '../Controlador/controladorCelAdmin.php';

$controllerCel = new CelebranteControllerAdmin();
$DetalleIns = $controllerCel->verificarIns($Catequista['Sacramento']);

$inscripcionesActivas = false;
foreach ($DetalleIns as $inscripcion) {
    if ($inscripcion['EstadoIns'] === 'Online' && $inscripcion['ActividadSac'] === 'Inscripción') {
        $inscripcionesActivas = true;
        break;
    }
}

$controller = new ControladorPersona();
if (isset($_GET['ciPersona'])) {
    $ciPersona = $_GET['ciPersona'];
    $persona = $controller->buscarPersona($ciPersona);

    if ($persona) {
        echo json_encode($persona);
    } else {
        echo json_encode(['error' => 'Persona no encontrada']);
    }
    exit;
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
    <link rel="stylesheet" type="text/css" href="../assets/css/style.css">
    <script src="../assets/js/jquery-3.6.4.min.js"></script>
</head>

<body class="sidebar-start-enabled">
    <header class="navbar-light fixed-top header-static bg-mode">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="VistaCatequista.php">
                    <img class="light-mode-item navbar-brand-item" src="../assets/images/logo.png" alt="logo">
                    <img class="dark-mode-item navbar-brand-item" src="../assets/images/logo-light.png" alt="logo">
                </a>

                <ul class="nav flex-nowrap align-items-center ms-sm-3 list-unstyled">
                    <li class="nav-item ms-2">
                        <a class="nav-link bg-light icon-md btn btn-light p-0" href="Configuracion.php" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Configurar">
                            <i class="bi bi-gear-fill fs-6"> </i>
                        </a>
                    </li>

                    <li class="nav-item ms-2 dropdown">
                        <a class="nav-link btn icon-md p-0" href="#" id="profileDropdown" role="button" data-bs-auto-close="outside" data-bs-display="static" data-bs-toggle="dropdown" aria-expanded="false">
                            <img class="avatar-img rounded border border-white border-3" src="<?php echo ($Catequista['ImagenCat'] == 'Ninguno' || empty($Catequista['ImagenCat'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$Catequista['ImagenCat']}"; ?>" alt="">
                        </a>
                        <ul class="dropdown-menu dropdown-animation dropdown-menu-end pt-3 small me-md-n3" aria-labelledby="profileDropdown">
                            <li class="px-3">
                                <div class="d-flex align-items-center position-relative">
                                    <div class="avatar me-3">
                                        <img class="avatar-img rounded-circle" src="<?php echo ($Catequista['ImagenCat'] == 'Ninguno' || empty($Catequista['ImagenCat'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$Catequista['ImagenCat']}"; ?>" alt="avatar">
                                    </div>
                                    <div>
                                        <a class="h6 stretched-link" href="#"><?php echo $Catequista['Nombre'] . " " . $Catequista['ApPaterno'] . " " . $Catequista['ApMaterno']; ?></a>
                                        <p class="small m-0"><?php echo $Catequista['RolCat']; ?> <i class="bi bi-patch-check-fill text-success small"></i></p>
                                    </div>
                                </div>
                                <a class="dropdown-item btn btn-primary-soft btn-sm my-2 text-center" href="PerfilCatequista.php">Ver mi Perfil</a>
                            </li>
                            <hr>
                            <li>
                                <a class="dropdown-item" href="Configuracion.php"><i class="bi bi-gear fa-fw me-2"></i>Configuración y Privacidad</a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="ReunionesCat.php"><i class="fa-fw bi bi-calendar2-check me-2"></i>Fecha de Reuniones</a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="Documentos.php">
                                    <i class="fa-fw bi bi-card-text me-2"></i>Documentación
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="Reglamento.php">
                                    <i class="fa-fw bi bi-list-check me-2"></i>Reglamento
                                </a>
                            </li>
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
        <div class="container">
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
                                    <img class="light-mode-item navbar-brand-item" src="../assets/images/logo.png" alt="logo">
                                    <img class="dark-mode-item navbar-brand-item" src="../assets/images/logo-light.png" alt="logo">
                                </a>
                                <button type="button" class="btn-close text-reset ms-auto" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                            </div>

                            <div class="offcanvas-body d-block px-2 px-lg-0">
                                <div class="card overflow-hidden">
                                    <div class="h-50px" style="background-image:url(<?php echo ($Catequista['FondoCat'] == 'Ninguno' || empty($Catequista['FondoCat'])) ? '../assets/images/fondo/Ninguno.jpg' : "../assets/images/fondo/{$Catequista['FondoCat']}"; ?>); background-position: center; background-size: cover; background-repeat: no-repeat;">
                                    </div>
                                    <div class="card-body pt-0">
                                        <div class="text-center">
                                            <div class="avatar avatar-lg mt-n5 mb-3">
                                                <img class="avatar-img rounded border border-white border-3" src="<?php echo ($Catequista['ImagenCat'] == 'Ninguno' || empty($Catequista['ImagenCat'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$Catequista['ImagenCat']}"; ?>" alt="">
                                            </div>
                                            <h5 class="mb-0"> <a href="PerfilCatequista.php"><?php echo $Catequista['Nombre'] . " " . $Catequista['ApPaterno'] . " " . $Catequista['ApMaterno']; ?></a> </h5>

                                            <small><?php echo $Catequista['RolCat']; ?></small>
                                            <p class="mt-3"><?php echo $Catequista['FraseCat']; ?></p>
                                            <hr>
                                            <div class="hstack gap-2 gap-xl-3 justify-content-center">
                                                <div>
                                                    <h6 class="mb-0"><?php echo $Catequista['NombreGrupo']; ?></h6>
                                                    <small>Grupo</small>
                                                </div>
                                                <div class="vr"></div>
                                                <div>
                                                    <h6 class="mb-0">
                                                        <?php
                                                        $ahora = new DateTime();
                                                        $fechaInicio = new DateTime($Catequista['FechaRegCat']);
                                                        $diferencia = $ahora->diff($fechaInicio);
                                                        echo $diferencia->format("%y");
                                                        ?> años
                                                    </h6>
                                                    <small>Trayectoria</small>
                                                </div>
                                                <div class="vr"></div>
                                                <div>
                                                    <h6 class="mb-0">0</h6>
                                                    <small><i class="bi bi-star-fill text-danger pe-1"></i></small>
                                                </div>
                                            </div>


                                        </div>

                                        <hr>

                                        <ul class="nav nav-link-secondary flex-column fw-bold gap-2">
                                            <li class="nav-item">
                                                <a class="nav-link" href="VistaCatequista.php"> <img class="me-2 h-20px fa-fw" src="../assets/images/icon/mensaje.png" alt=""><span>Inicio </span></a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" href="VistaPersona.php"> <img class="me-2 h-20px fa-fw" src="../assets/images/icon/nuevo.png" alt=""><span>Persona </span></a>
                                            </li>
                                            <?php if ($Catequista['RolCat'] == "Titular") { ?>
                                                <li class="nav-item">
                                                    <a class="nav-link" href="VistaReuniones.php"> <img class="me-2 h-20px fa-fw" src="../assets/images/icon/instrucciones.png" alt=""><span>Reuniones / Actividades</span></a>
                                                </li>
                                            <?php } ?>
                                            <li class="nav-item">
                                                <a class="nav-link" href="GrupoEsp.php?id=<?php echo $Catequista['IdGrupo']; ?>"> <img class="me-2 h-20px fa-fw" src="../assets/images/icon/contrato.png" alt=""><span>Grupo </span></a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" href="VistaCelebranteAsis.php"> <img class="me-2 h-20px fa-fw" src="../assets/images/icon/ListaCelebrante.png" alt=""><span>Asistencia a Catquesis </span></a>
                                            </li>
                                            <?php if ($Catequista['PoderCat'] == "Habilitado") { ?>
                                                <li class="nav-item">
                                                    <a class="nav-link active" href="VistaDocCel.php"> <img class="me-2 h-20px fa-fw" src="../assets/images/icon/Documentos.png" alt=""><span>Registros</span></a>
                                                </li>
                                            <?php } ?>
                                            <?php if ($Catequista['RolCat'] == "Titular") { ?>
                                                <li class="nav-item">
                                                    <a class="nav-link" href="VistaCelExamen.php"> <img class="me-2 h-20px fa-fw" src="../assets/images/icon/Examen.png" alt=""><span>Pruebas</span></a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" href="VistaReporteGrupo.php"> <img class="me-2 h-20px fa-fw" src="../assets/images/icon/reporte.png" alt=""><span>Reporte General</span></a>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                    <div class="card-footer text-center py-2">
                                        <a class="btn btn-link btn-sm" href="PerfilCatequista.php">Ver mi Perfil</a>
                                    </div>
                                </div>
                                <p class="small text-center mt-1">©2026 <a class="text-reset" target="_blank" href="https://sacaba.gob.bo/index.php/inicio/cultura/arte-y-arquitectura"> San Pedro de Sacaba </a></p>
                            </div>
                        </div>
                    </nav>
                </div>

                <div class="col-md-8 col-lg-6 vstack gap-4">

                    <div class="card h-100 p-3">

                        <div class="card-header d-sm-flex align-items-center text-center justify-content-sm-between border-0 pb-0">
                            <h2 class="align-items-center text-center py-2">Registrar Nuevos Catequisandos de (<?php echo $Catequista['Sacramento']; ?>)</h2>
                        </div>

                        <?php if ($inscripcionesActivas): ?>
                            <button type="button" class="btn btn-primary" onclick="agregarFormularioCelebrante()">Añadir Catequisando</button>

                            <form id="form-celebrantes" method="POST" action="../src/procesar_registro.php">
                                <br>
                                <div id="formularios-celebrantes" class="row g-3"></div>
                                <br>
                                <button type="submit" class="btn btn-success">Registrar Todos los Catequisandos</button>
                            </form>

                            <div id="resumen-celebrantes" class="mt-4" style="display: none;">
                                <h4>Resumen de Catequisandos Registrados</h4>
                                <ul id="lista-resumen"></ul>
                                <br>

                                <h4>Registrar Apoderado para todos los Catequisandos</h4>
                                <form id="form-apoderado" method="POST" action="../src/registrar_apoderado.php">
                                    <div class="form-group">
                                        <label for="ciFamiliar">CI del Apoderado:</label>
                                        <input type="text" class="form-control" id="ciFamiliar" name="ciFamiliar" required>
                                        <a class="celAp btn btn-sm btn-dashed rounded mt-0" href="javascript:abrirModalPersona()"> <i class="bi bi-plus-circle-dotted me-1"></i>registrar a la Persona</a>
                                    </div>

                                    <div class="form-group">
                                        <label for="rolFam">Rol Familiar:</label>
                                        <input type="text" class="form-control" id="rolFam" name="rolFam" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="parentesco">Parentesco:</label>
                                        <input type="text" class="form-control" id="parentesco" name="parentesco" required>
                                    </div>
                                    <br>
                                    <button type="submit" class="btn btn-success-soft">Registrar Apoderado para Todos los Catequisandos</button>
                                    <button type="button" class="btn btn-primary-soft" onclick="GenerarReporte()">Generar Comprobante</button>
                                </form>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning" role="alert"> Las inscripciones no están activas en este momento.
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="modal fade modal-xl" id="modalPersona" tabindex="-1" role="dialog" aria-labelledby="modalPersonaLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <form id="form-persona">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalPersonaLabel">Registrar Persona</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body row g-3">
                                        <input type="hidden" id="celebrante-id-actual">

                                        <div class="form-group col-sm-2 col-lg-2">
                                            <label for="ciPersona">CI Persona</label>
                                            <input type="text" class="form-control" id="ciPersona" placeholder="Identificación" required>
                                        </div>
                                        <div class="form-group col-sm-2 col-lg-3">
                                            <label for="nombre">Nombre</label>
                                            <input type="text" class="form-control" id="nombre" placeholder="Nombres" required>
                                        </div>
                                        <div class="form-group col-sm-2 col-lg-2">
                                            <label for="apPaterno">Apellido Paterno</label>
                                            <input type="text" class="form-control" id="apPaterno" placeholder="Apellido Paterno" required>
                                        </div>
                                        <div class="form-group col-sm-2 col-lg-2">
                                            <label for="apMaterno">Apellido Materno</label>
                                            <input type="text" class="form-control" id="apMaterno" placeholder="Apellido Materno" required>
                                        </div>

                                        <div class="form-group col-sm-2 col-lg-2">
                                            <label class="form-label">Sexo:</label>
                                            <div>
                                                <input type="radio" id="varon" name="sexo" value="Varon" checked>
                                                <label for="varon">Varon</label>
                                                &nbsp;&nbsp;&nbsp;
                                                <input type="radio" id="mujer" name="sexo" value="Mujer">
                                                <label for="mujer">Mujer</label>
                                            </div>
                                        </div>
                                        <div class="form-group col-sm-2 col-lg-3">
                                            <label for="fechaNac">Fecha de Nacimiento</label>
                                            <input type="date" class="form-control" id="fechaNac" required>
                                        </div>
                                        <div class="form-group col-sm-2 col-lg-3">
                                            <label for="direccion">Dirección</label>
                                            <input type="text" class="form-control" id="direccion" placeholder="Ingrese su Dirección" required>
                                        </div>
                                        <div class="form-group col-sm-2 col-lg-3">
                                            <label for="contacto">Contacto</label>
                                            <input type="text" class="form-control" id="contacto" placeholder="Número de Contacto" required>
                                        </div>
                                        <div class="form-group col-sm-2 col-lg-3">
                                            <label for="estadoPer">Estado</label>
                                            <select id="estadoPer" class="form-control" required>
                                                <option value="Soltero">Soltero</option>
                                                <option value="Soltera">Soltera</option>
                                                <option value="Casado">Casado</option>
                                                <option value="Casada">Casada</option>
                                                <option value="Viudo">Viudo</option>
                                                <option value="Viuda">Viuda</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-primary" onclick="registrarPersona()">Registrar Persona</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </main>


    <script>
        $(document).ready(function() {
            $('#formularios-celebrantes').on('input', 'input[name^="celebrantes"][name$="[ciCel]"]', function() {
                var ciPersona = $(this).val();
                var $celebranteDiv = $(this).closest('.form-celebrante');

                if (ciPersona.length >= 6 || ciPersona.length <= 0) {
                    $.ajax({
                        url: '',
                        type: 'GET',
                        data: {
                            ciPersona: ciPersona
                        },
                        success: function(response) {
                            var persona = JSON.parse(response);
                            if (persona.error) {
                                $celebranteDiv.find('.celebrantenew').html(
                                    `<i class="bi bi-plus-circle-dotted me-1"></i> Datos no encontrados`
                                );
                            } else {
                                var form = `${persona.Nombre} ${persona.ApPaterno}`;
                                $celebranteDiv.find('.celebrantenew').html(form);
                            }
                        }
                    });
                }
            });
        });

        $(document).ready(function() {
            $('input[name="ciFamiliar"]').on('input', function() {
                var ciPersona = $(this).val();
                if (ciPersona.length >= 6 || ciPersona.length <= 0) {
                    $.ajax({
                        url: '',
                        type: 'GET',
                        data: {
                            ciPersona: ciPersona
                        },
                        success: function(response) {
                            var persona = JSON.parse(response);
                            if (persona.error) {
                                $('.celAp').html(
                                    `<i class="bi bi-plus-circle-dotted me-1""></i>registrar a la Persona`
                                );
                            } else {
                                var form = `${persona.Nombre} ${persona.ApPaterno}`;
                                $('.celAp').html(form);
                            }
                        }
                    });
                }
            });
        });

        $(document).ready(function() {
            function obtenerEstado() {
                $.ajax({
                    url: '../VerificarCatequista.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.EstadoCat && response.RolCat) {
                            $('#estado').text(response.EstadoCat);
                            $('#cargo').text(response.RolCat);
                            if (response.RolCat === "Coordinador") {
                                window.location.replace("../logout.php");
                            }
                            if (response.EstadoCat === "Inactivo") {
                                window.location.replace("PerfilCatequista.php");
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

            setInterval(obtenerEstado, 5000);

            obtenerEstado();
        });
    </script>

    <script>
        let contadorCelebrantes = 0;
        let resumenCelebrantes = [];

        function agregarFormularioCelebrante() {
            contadorCelebrantes++;
            const formCelebrante = `<br><div class="card-body pt-0 border border-white border-3 rounded col-lg-3 me-2">
        <div class="form-celebrante" id="celebrante-${contadorCelebrantes}">
            <h6>Catequisando ${contadorCelebrantes}</h6>
                <div class="form-group">
                    <label class="form-label">CI del Catequisando</label>
                    <input type="text" class="form-control" id="CiCelNuevo" name="celebrantes[${contadorCelebrantes}][ciCel]" placeholder="CI Persona">
                    <a class="celebrantenew btn btn-sm btn-dashed rounded mt-0" href="javascript:abrirModalPersona(${contadorCelebrantes})"> <i class="bi bi-plus-circle-dotted me-1"></i>registrar a la Persona</a>
                </div>

                <input type="hidden" name="celebrantes[${contadorCelebrantes}][sacramento]" value="<?php echo $Catequista['Sacramento']; ?>">

                <div class="form-group">
                      <label for="CodTipoItem" class="form-label">Sacramento a realizar</label>
                    <?php if ($Catequista['Sacramento'] == 'Primera Comunión'): ?>
                        <input type="hidden" class="form-control" name="celebrantes[${contadorCelebrantes}][idGrupo]" value="1">
                        <select class="form-control" name="celebrantes[${contadorCelebrantes}][codTipoItem]" data-search-enabled="true">
                            <option value="1">Primera Comunión</option>
                            <option value="2">Primera Comunión - Bautizo</option>
                        </select>

                    <?php elseif ($Catequista['Sacramento'] == 'Confirmación'): ?>
                        <input type="hidden" class="form-control" name="celebrantes[${contadorCelebrantes}][idGrupo]" value="22">
                        <select class="form-control" name="celebrantes[${contadorCelebrantes}][codTipoItem]" data-search-enabled="true">
                            <option value="3">Confirmación</option>
                            <option value="4">Confirmación - Primera Comunión</option>
                            <option value="5">Confirmación - Primera Comunión - Bautizo</option>
                        </select>

                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="PreValor" class="form-label">Monto Cancelado</label>
                    <input type="text" class="form-control" name="celebrantes[${contadorCelebrantes}][preValor]" required><br>
                </div>
        </div></div>`;
            document.getElementById('formularios-celebrantes').insertAdjacentHTML('beforeend', formCelebrante);
        }

        function abrirModalPersona(idCelebrante = null) {
            $('#celebrante-id-actual').val(idCelebrante);
            $('#modalPersona').modal('show');
        }

        function mostrarAlerta(mensaje, tipo) {
            const alerta = document.createElement('div');
            alerta.className = `alert alert-${tipo} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
            alerta.style.zIndex = '9999';
            alerta.style.minWidth = '400px';
            alerta.innerHTML = `${mensaje}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
            document.body.appendChild(alerta);
            setTimeout(() => { alerta.remove(); }, 4000);
        }

        function registrarPersona() {
            const idCelebrante = $('#celebrante-id-actual').val();
            const dataPersona = {
                ciPersona: $('#ciPersona').val(),
                nombre: $('#nombre').val(),
                apPaterno: $('#apPaterno').val(),
                apMaterno: $('#apMaterno').val(),
                sexo: $('#sexo').val(),
                fechaNac: $('#fechaNac').val(),
                direccion: $('#direccion').val(),
                contacto: $('#contacto').val(),
                estadoPer: $('#estadoPer').val()
            };

            $.post('../src/registrar_persona.php', dataPersona, function(response) {
                if (response.success) {
                    const ciPersona = dataPersona.ciPersona;
                    if (idCelebrante) {
                        $(`#celebrante-${idCelebrante} [name="celebrantes[${idCelebrante}][ciCel]"]`).val(ciPersona);
                    } else {
                        $('#ciFamiliar').val(ciPersona);
                    }
                    $('#modalPersona').modal('hide');
                    mostrarAlerta('Persona registrada correctamente', 'success');
                } else {
                    mostrarAlerta('Error al registrar: ' + response.message, 'danger');
                }
            }, 'json');
        }

        function actualizarResumenCelebrantes(celebrantes) {
            const listaResumen = document.getElementById('lista-resumen');
            listaResumen.innerHTML = '';
            celebrantes.forEach(celebrante => {
                const li = document.createElement('li');
                li.textContent = `Código: ${celebrante.idins}, Catequisando: ${celebrante.Celebrante}, Usuario: ${celebrante.usuario}, Clave: ${celebrante.clave}`;
                listaResumen.appendChild(li);
            });
        }

        document.getElementById('form-celebrantes').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('../src/procesar_registro.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Catequisandos registrados con éxito');
                        actualizarResumenCelebrantes(data.celebrantes);
                        resumenCelebrantes = data.celebrantes;

                        document.getElementById('formularios-celebrantes').style.display = 'none';
                        document.getElementById('form-celebrantes').style.display = 'none';
                        document.getElementById('resumen-celebrantes').style.display = 'block';
                    } else {
                        alert('Error al registrar catequisandos: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al procesar la solicitud');
                });
        });
        document.getElementById('form-apoderado').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            resumenCelebrantes.forEach(celebrante => {
                formData.append('celebrantes[]', celebrante.idins);
            });

            fetch('../src/registrar_apoderado.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Apoderado registrado con éxito para todos los catequisandos');
                    } else {
                        alert('Error al registrar apoderado: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al procesar la solicitud');
                });
        });

        function GenerarReporte() {
            const ciFamiliar = document.getElementById('ciFamiliar').value;
            // Construir la URL con el parámetro ciFamiliar
            const url = `../src/Reporte_Compromiso.php?ciFamiliar=${encodeURIComponent(ciFamiliar)}`;
            // Abrir la nueva pestaña con la URL
            window.open(url, '_blank', 'width=800,height=600');
        }
    </script>
    <script src="../assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/functions.js"></script>
    <script src="../assets/js/tema.js"></script>
</body>

</html>