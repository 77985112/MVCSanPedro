<?php
require_once '../Controlador/controladorReserva.php';
require_once '../Controlador/ControladorPersona.php';

session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../logout.php");
    exit();
}
$usuario = $_SESSION['usuario'];

$controller = new ControladorPersona();

$persona = null;

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


$diassemana = array("Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado");
$meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");


$controller = new ControladorReserva();
$reservasMatrimonio = [];
$reservasBautizo = [];
$reservasMisa = [];
$avisoReserva = $_SESSION['aviso_reserva'] ?? [];
unset($_SESSION['aviso_reserva']);
if (empty($_SESSION['token_reserva'])) {
    $_SESSION['token_reserva'] = bin2hex(random_bytes(32));
}



$accionesReserva = [
    'reservar_misa' => 'reservarMisa',
    'reservar_bautizo' => 'reservarBautizo',
    'reservar_matrimonio' => 'reservarMatrimonio',
];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($accionesReserva as $accion => $metodo) {
        if (!isset($_POST[$accion])) {
            continue;
        }
        if (!isset($_POST['TokenReserva']) || !is_string($_POST['TokenReserva']) ||
            !hash_equals($_SESSION['token_reserva'], $_POST['TokenReserva'])) {
            $avisoReserva = ['tipo' => 'warning', 'mensaje' =>
                'Este formulario venció o ya fue procesado. Comprueba el calendario antes de registrar otra reserva.'];
            break;
        }
        $datosReserva = $_POST;
        $datosReserva['CodPer'] = $usuario['CodPer'];
        try {
            $guardada = $controller->$metodo($datosReserva);
            $avisoReserva = $controller->obtenerAvisoReserva();
        } catch (Throwable $e) {
            $guardada = false;
            $avisoReserva = ['tipo' => 'danger', 'mensaje' =>
                'No se pudo confirmar el registro. Comprueba el calendario y consulta al administrador antes de intentarlo de nuevo.'];
            error_log('Reservas: error al procesar el registro.');
        }
        if ($guardada) {
            $_SESSION['aviso_reserva'] = $avisoReserva;
            $_SESSION['token_reserva'] = bin2hex(random_bytes(32));
            header('Location: VistaReservas.php', true, 303);
            exit();
        }
        break;
    }
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

    <style>
        .containerRes {
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: flex-start;
            gap: 20px;
            width: 100%;
            margin-top: 0px;
            flex-wrap: wrap;
        }

        #reminders {
            width: 300px;
            height: 400px;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        #calendar {
            flex-grow: 1;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            max-height: 900px;
        }

        #current_day {
            background: rgba(255, 255, 255, 0.4);
            padding: 0.2rem 0.5rem;
            text-transform: capitalize;
            height: 50px;
            font-size: 1.6rem;
            line-height: 2.2rem;
            transition: 0.5s;
        }

        #week-container {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            grid-gap: 10px;
            margin-top: 20px;
        }

        .day.reserved {
            color: #243247;
        }

        .calendar-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 6px 12px;
            margin-top: 16px;
            font-size: 0.75rem;
        }

        .calendar-legend span {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .calendar-legend i {
            width: 12px;
            height: 12px;
            border-radius: 3px;
            border: 1px solid rgba(0, 0, 0, 0.15);
        }

        .reserva-calendario {
            margin-top: 8px;
            padding: 7px 9px;
            border-left: 4px solid;
            border-radius: 5px;
            color: #243247;
            font-size: 0.9rem;
        }

        .reserva-calendario .estado-reserva {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
        }

        #reminders {
            height: auto;
            min-height: 400px;
        }

        .day {
            background-color: #fafafa;
            border-radius: 10px;
            padding: 5px;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.3s ease;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            font-size: 1.2rem;
        }

        .day:hover {
            background-color: #87CEFA;
        }

        .day.selected {
            outline: 2px solid #0d6efd;
            outline-offset: 2px;
            font-weight: bold;
        }


        .day-name {
            font-weight: bold;
            text-align: center;
            margin-top: 5px;
            font-size: 0.8rem;
        }

        @media screen and (max-width: 768px) {
            .containerRes {

                padding: 0px;
            }

            #calendar {
                max-width: 100%;
                max-height: 100%;
                margin-bottom: 20px;
            }

            #reminders {
                width: 100%;
                max-width: 100%;
                height: auto;
            }

            #week-container {
                grid-template-columns: repeat(7, 1fr);
            }

            #monthDisplay {
                font-weight: bold;
                font-size: 0.8rem;
            }

            .day {
                padding: 5px;
                font-size: 1rem;
                height: 40px;
                width: 30px;
                text-align: center;
                cursor: pointer;
                transition: background-color 0.3s ease;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
            }

            .day-name {
                font-size: 0.7rem;
            }
        }

        @media screen and (max-width: 480px) {
            #week-container {
                grid-template-columns: repeat(7, 1fr);
            }

            #monthDisplay {
                font-weight: bold;
                font-size: 0.8rem;
            }

            .day {
                padding: 5px;
                font-size: 0.8rem;
                height: 40px;
                width: 30px;
                text-align: center;
                cursor: pointer;
                transition: background-color 0.3s ease;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
            }

            .day-name {
                font-size: 0.6rem;
            }
        }

        #monthDisplay {
            font-weight: bold;
        }
    </style>


    <script src="../assets/js/jquery-3.6.4.min.js"></script>
    <script>
        function generarReporte(tipo) {
            const fechaSeleccionada = document.getElementById("fecha").value;

            if (!fechaSeleccionada) {
                alert("Por favor, selecciona una fecha.");
                return;
            }

            const [anio, mes, dia] = fechaSeleccionada.split("-");

            const form = document.createElement("form");
            form.action = "VistaReporteEsp.php";
            form.method = "GET";
            form.target = "_blank";

            const inputAnio = document.createElement("input");
            inputAnio.type = "hidden";
            inputAnio.name = "anio";
            inputAnio.value = anio;
            form.appendChild(inputAnio);

            const inputMes = document.createElement("input");
            inputMes.type = "hidden";
            inputMes.name = "mes";
            inputMes.value = mes;
            form.appendChild(inputMes);

            if (tipo === "diario") {
                const inputDia = document.createElement("input");
                inputDia.type = "hidden";
                inputDia.name = "dia";
                inputDia.value = dia;
                form.appendChild(inputDia);
            }

            const inputTipo = document.createElement("input");
            inputTipo.type = "hidden";
            inputTipo.name = "tipo";
            inputTipo.value = tipo;
            form.appendChild(inputTipo);

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }
    </script>
</head>

<body>
    <header class="navbar-light fixed-top header-static bg-mode">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="VistaPersonal.php">
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
                            <img class="avatar-img rounded border border-white border-3" src="<?php echo ($usuario['PerfilPer'] == 'Ninguno' || empty($usuario['PerfilPer'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$usuario['PerfilPer']}"; ?>" alt="">
                        </a>
                        <ul class="dropdown-menu dropdown-animation dropdown-menu-end pt-3 small me-md-n3" aria-labelledby="profileDropdown">
                            <li class="px-3">
                                <div class="d-flex align-items-center position-relative">
                                    <div class="avatar me-3">
                                        <img class="avatar-img rounded-circle" src="<?php echo ($usuario['PerfilPer'] == 'Ninguno' || empty($usuario['PerfilPer'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$usuario['PerfilPer']}"; ?>" alt="avatar">
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
                                    <div class="h-50px" style="background-image:url(<?php echo ($usuario['FondoPer'] == 'Ninguno' || empty($usuario['FondoPer'])) ? '../assets/images/fondo/Ninguno.jpg' : "../assets/images/fondo/{$usuario['FondoPer']}"; ?>); background-position: center; background-size: cover; background-repeat: no-repeat;">
                                    </div>
                                    <div class="card-body pt-0">
                                        <div class="text-center">
                                            <div class="avatar avatar-lg mt-n5 mb-3">
                                                <img class="avatar-img rounded border border-white border-3" src="<?php echo ($usuario['PerfilPer'] == 'Ninguno' || empty($usuario['PerfilPer'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$usuario['PerfilPer']}"; ?>" alt="">
                                            </div>
                                            <h5 class="mb-0"> <a href="PerfilAdmin.php"><?php echo $usuario['Nombre'] . " " . $usuario['Paterno'] . " " . $usuario['Materno']; ?></a> </h5>
                                            <small><?php echo $usuario['DescripCar']; ?></small>
                                            <p class="mt-3"><?php echo $usuario['FrasePer']; ?></p>
                                        </div>

                                        <hr>

                                        <ul class="nav nav-link-secondary flex-column fw-bold gap-2">
                                            <li class="nav-item">
                                                <a class="nav-link" href="VistaPersonal.php"> <img class="me-2 h-20px fa-fw" src="../assets/images/icon/mensaje.png" alt=""><span>Inicio </span></a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" href="VistaPersona.php"> <img class="me-2 h-20px fa-fw" src="../assets/images/icon/nuevo.png" alt=""><span>Persona </span></a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link active" href="VistaReservas.php"> <img class="me-2 h-20px fa-fw" src="../assets/images/icon/instrucciones.png" alt=""><span>Celebración </span></a>
                                            </li>
                                            <?php if ($usuario['DescripCar'] == 'Párroco') { ?>
                                                <li class="nav-item">
                                                    <a class="nav-link" href="VistaGesPersonal.php"> <img class="me-2 h-20px fa-fw" src="../assets/images/icon/seguro.png" alt=""><span>Personal</span></a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" href="VistaCoordinadorAdmin.php"> <span>🔥 ⚪ Coordinador</span></a>
                                                </li>
                                            <?php } ?>
                                            <li class="nav-item">
                                                <a class="nav-link" href="VistaSolicitante.php"> <img class="me-2 h-20px fa-fw" src="../assets/images/icon/contrato.png" alt=""><span>Certificado </span></a>
                                            </li>
                                            <?php if ($usuario['DescripCar'] != "Personal") { ?>
                                                <li class="nav-item">
                                                    <a class="nav-link" href="VistaEmitidos.php"> <img class="me-2 h-20px fa-fw" src="../assets/images/icon/Emitido.png" alt=""><span>Certificado Emitido </span></a>
                                                </li>
                                            <?php } ?>

                                        </ul>
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



                <div class="col-md-12 col-lg-6 vstack gap-4">

                    <div class="card h-100">
                        <div class="card-body">
                            <h1 class="h4 card-title">Información de Reservas Sacramentales</h1>
                            <?php if (!empty($avisoReserva['mensaje'])): ?>
                                <div class="alert alert-<?php echo htmlspecialchars($avisoReserva['tipo'], ENT_QUOTES, 'UTF-8'); ?>" role="alert">
                                    <?php echo htmlspecialchars($avisoReserva['mensaje'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            <?php endif; ?>
                            <ul class="nav nav-tabs nav-bottom-line justify-content-center justify-content-md-start">
                                <li class="nav-item"> <a class="nav-link active" data-bs-toggle="tab" href="#tab-1">Vista General</a></li>
                                <li class="nav-item"> <a class="nav-link" data-bs-toggle="tab" href="#tab-2">Reservar Bautizo</a></li>
                                <li class="nav-item"> <a class="nav-link" data-bs-toggle="tab" href="#tab-3">Reservar Matrimonio</a></li>
                                <li class="nav-item"> <a class="nav-link" data-bs-toggle="tab" href="#tab-4">Reservar Misa</a></li>
                            </ul>
                            <div class="tab-content mb-0 pb-0">

                                <div class="tab-pane fade show active" id="tab-1">

                                    <div class="containerRes">
                                        <div id="calendar">
                                            <div class="col-md-12 col-lg-12 d-sm-flex align-items-center text-center justify-content-sm-between ">
                                                <button class="btn btn-primary-soft" id="prevMonth">Anterior</button>
                                                <div id="current_day">
                                                    <p id="monthDisplay" class="w-100">Mes y Año</p>
                                                </div>
                                                <button class="btn btn-primary-soft" id="nextMonth">Siguiente</button>
                                            </div>

                                            <div id="week-container"></div>
                                            <div class="calendar-legend" aria-label="Colores de las celebraciones">
                                                <span><i style="background: #fecaca"></i>Confirmación</span>
                                                <span><i style="background: #fef08a"></i>Primera Comunión</span>
                                                <span><i style="background: #bae6fd"></i>Matrimonio</span>
                                                <span><i style="background: #bbf7d0"></i>Misas</span>
                                                <span><i style="background: #ffffff; border: 2px solid #d4af37;"></i>Bautizo</span>
                                            </div>
                                        </div>

                                        <div id="reminders">
                                            <h5>Reservas Realizadas</h5>
                                            <div id="reminderList">Seleccione una fecha.</div>

                                            <div class="col-sm-12 col-lg-12 d-flex py-3">
                                                <input type="hidden" id="fecha" required>
                                                <div class="col-sm-5 col-lg-5">
                                                    <button onclick="generarReporte('mensual')" class="btn btn-primary-soft" data-bs-toggle="tooltip" data-bs-placement="top" title="Mensual"><i class="bi bi-calendar2-minus"></i></button>
                                                    <button onclick="generarReporte('diario')" class="btn btn-primary-soft" data-bs-toggle="tooltip" data-bs-placement="top" title="Diario"><i class="bi bi-sunrise-fill"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if ($usuario['DescripCar'] != "Personal") { ?>
                                        <div class="col-sm-12 col-lg-12 py-2">
                                            <a href="VistaReporteReservas.php" class="btn btn-primary-soft w-100" target="_blank"><i class="bi bi-file-text"></i> Generar Reporte de Reservas Anual</a>
                                        </div>
                                    <?php } ?>
                                </div>


                                <div class="tab-pane fade" id="tab-2">
                                    <div id="formReservaBautizo">
                                        <h5>Registrar Nueva Reserva de Bautizo</h5>
                                        <form method="POST" class="row g-3">
                                            <br>
                                            <input type="hidden" name="CodPer" placeholder="Código Personal" value="<?php echo $usuario['CodPer']; ?>" required>

                                            <div class="col-sm-6 col-lg-4">
                                                <label class="form-label">Quien Realiza la reserva</label>
                                                <input type="text" class="form-control" name="Quien" placeholder="Quien Realiza la Reserva" required>
                                            </div>

                                            <div class="col-sm-5 col-lg-3">
                                                <label class="form-label">Fecha a celebrar</label>
                                                <input type="date" class="form-control" name="FechaReal" id="FechaReal1" required>
                                            </div>

                                            <div class="col-sm-5 col-lg-2">
                                                <label class="form-label">Hora a celebrar</label>
                                                <input type="time" class="form-control" name="HoraReal" id="HoraReal1" step="1800" required>
                                            </div>

                                            <div class="col-sm-6 col-lg-5">
                                                <label class="form-label">Tipo de Reserva</label>
                                                <div class=" d-sm-flex align-items-center text-center justify-content-sm-betwee">
                                                    <div class="form-check me-2">
                                                        <input class="form-check-input" type="radio" name="Realizacion" id="bautizo-privado" value="Privado" required>
                                                        <label class="form-check-label form-label" for="bautizo-privado">
                                                            Privado
                                                        </label>
                                                    </div>
                                                    <div class="form-check me-2">
                                                        <input class="form-check-input" type="radio" name="Realizacion" id="bautizo-comunitario" value="Comunitario">
                                                        <label class="form-check-label form-label" for="bautizo-comunitario">
                                                            Comunitario
                                                        </label>
                                                    </div>
                                                    <div class="form-check me-2">
                                                        <input class="form-check-input" type="radio" name="Realizacion" id="bautizo-otro" value="Otro">
                                                        <label class="form-check-label form-label" for="bautizo-otro">
                                                            Otro
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="mt-2" id="detalle-otro-bautizo" hidden>
                                                    <label class="form-label" for="realizacion-otro-bautizo">Especifica el tipo de bautizo</label>
                                                    <input type="text" class="form-control" id="realizacion-otro-bautizo" name="RealizacionOtro" maxlength="30" placeholder="Ej.: En una capilla familiar" disabled>
                                                    <small class="text-muted">Descripción breve, máximo 30 caracteres.</small>
                                                </div>
                                            </div>

                                            <div class="col-sm-6 col-lg-4">
                                                <label class="form-label">Bautizando/a</label>
                                                <input type="text" class="form-control" name="CiPersonaCelebranteB" placeholder="CI o N° Partida de Nacimiento" required minlength="3" maxlength="15">
                                                <a class="celebranteB btn btn-sm btn-dashed rounded mt-0"><i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalBautizo"></i>registrar nuevo</a>
                                            </div>
                                            <div class="col-sm-6 col-lg-3">
                                                <label class="form-label">Identificación del Padre</label>
                                                <input type="text" class="form-control" name="CiPapa" placeholder="CI Papá (opcional)" required minlength="4" maxlength="12">
                                                <a class="papacelebrante btn btn-sm btn-dashed rounded mt-0"><i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo</a>
                                            </div>
                                            <div class="col-sm-6 col-lg-3">
                                                <label class="form-label">Identificación de la Madre</label>
                                                <input type="text" class="form-control" name="CiMama" placeholder="CI Mamá (opcional)" required minlength="4" maxlength="12">
                                                <a class="mamacelebrante btn btn-sm btn-dashed rounded mt-0"><i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo</a>
                                            </div>
                                            <div class="col-sm-6 col-lg-3">
                                                <label class="form-label">Identificación del Padrino</label>
                                                 <input type="text" class="form-control" name="CiPadrino" placeholder="CI Padrino (opcional)" minlength="4" maxlength="12">
                                                <a class="padrinocelebrante btn btn-sm btn-dashed rounded mt-0"><i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo</a>
                                            </div>
                                            <div class="col-sm-6 col-lg-3">
                                                <label class="form-label">Identificación de la Madrina</label>
                                                 <input type="text" class="form-control" name="CiMadrina" placeholder="CI Madrina (opcional)" minlength="4" maxlength="12">
                                                <a class="madrinacelebrante btn btn-sm btn-dashed rounded mt-0"><i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo</a>
                                            </div>

                                            <input type="hidden" name="TipoCel" value="Bautizo">

                                            <input type="hidden" name="TokenReserva" value="<?php echo htmlspecialchars($_SESSION['token_reserva'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <div class="col-12">
                                                <label class="form-label" for="correo-bautizo">Correo del solicitante (opcional)</label>
                                                <input type="email" class="form-control" id="correo-bautizo" name="CorreoSolicitante" maxlength="254" placeholder="ejemplo@correo.com" autocomplete="email">
                                                <div class="form-check mt-2">
                                                    <input class="form-check-input confirmacion-correo" type="checkbox" id="enviar-bautizo" name="EnviarConfirmacion" value="1" data-correo="correo-bautizo">
                                                    <label class="form-check-label" for="enviar-bautizo">Enviar confirmación por correo</label>
                                                </div>
                                                <small class="text-muted">El correo se guardará para avisarte si se cancela la reserva. Marca la casilla si también deseas la confirmación del registro.</small>
                                            </div>

                                            <button type="submit" class="btn btn-success-soft me-2" name="reservar_bautizo"> Reservar Bautizo</button>
                                        </form>
                                    </div>

                                </div>

                                <div class="tab-pane fade" id="tab-3">

                                    <div id="formReservaMatrimonio">
                                        <h5>Registrar Nueva Reserva de Matrimonio</h5>

                                        <form method="POST" class="row g-3">
                                            <br>
                                            <input type="hidden" name="CodPer" placeholder="Código Personal" value="<?php echo $usuario['CodPer']; ?>" required>

                                            <div class="col-sm-6 col-lg-5">
                                                <label class="form-label">Quien Realiza la reserva</label>
                                                <input type="text" class="form-control" name="Quien" placeholder="Quien Realiza la Reserva" required>
                                            </div>

                                            <div class="col-sm-5 col-lg-3">
                                                <label class="form-label">Fecha a celebrar</label>
                                                <input type="date" class="form-control" name="FechaReal" id="FechaReal2" required>
                                            </div>

                                            <div class="col-sm-5 col-lg-2">
                                                <label class="form-label">Hora a celebrar</label>
                                                <input type="time" class="form-control" name="HoraReal" id="HoraReal2" step="1800" required>
                                            </div>


                                            <div class="col-sm-6 col-lg-5">
                                                <label class="form-label">Tipo de Reserva</label>
                                                <div class=" d-sm-flex align-items-center text-center justify-content-sm-betwee">
                                                    <div class="form-check me-2">
                                                        <input class="form-check-input" type="radio" name="Realizacion" id="matrimonio-privado" value="Privado" required>
                                                        <label class="form-check-label form-label" for="matrimonio-privado">
                                                            Privado
                                                        </label>
                                                    </div>
                                                    <div class="form-check me-2">
                                                        <input class="form-check-input" type="radio" name="Realizacion" id="matrimonio-comunitario" value="Comunitario">
                                                        <label class="form-check-label form-label" for="matrimonio-comunitario">
                                                            Comunitario
                                                        </label>
                                                    </div>
                                                    <div class="form-check me-2">
                                                        <input class="form-check-input" type="radio" name="Realizacion" id="matrimonio-otro" value="Otro">
                                                        <label class="form-check-label form-label" for="matrimonio-otro">
                                                            Otro
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-12" id="detalle-otro-matrimonio" hidden>
                                                <label class="form-label" for="realizacion-otro-matrimonio">Describe cómo será el matrimonio</label>
                                                <input type="text" class="form-control" id="realizacion-otro-matrimonio" name="RealizacionOtro" maxlength="30" placeholder="Ej.: En una capilla familiar" disabled>
                                                <small class="text-muted">Descripción breve, máximo 30 caracteres.</small>
                                            </div>

                                            <div class="col-sm-6 col-lg-3">
                                                <label class="form-label">Ci del Novio</label>
                                                <input type="text" class="form-control col-lg-4" name="CiPersonaNovio" placeholder="CI Novio" required minlength="4" maxlength="12">
                                                <a class="novio btn btn-sm btn-dashed rounded mt-0"><i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo</a>
                                            </div>

                                            <div class="col-sm-6 col-lg-3">
                                                <label class="form-label">Ci de la Novia</label>
                                                <input type="text" class="form-control col-lg-4" name="CiPersonaNovia" placeholder="CI Novia" required minlength="4" maxlength="12">
                                                <a class="novia btn btn-sm btn-dashed rounded mt-0"><i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo</a>
                                            </div>

                                            <div class="col-sm-6 col-lg-3">
                                                <label class="form-label">Ci del Papá del Novio</label>
                                                <input type="text" class="form-control col-lg-4" name="CiPaNovio" placeholder="CI Papá Novio" minlength="4" maxlength="12">
                                                <a class="papanovio btn btn-sm btn-dashed rounded mt-0"><i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo</a>
                                            </div>

                                            <div class="col-sm-6 col-lg-3">
                                                <label class="form-label">Ci del Mamá del Novio</label>
                                                <input type="text" class="form-control col-lg-4" name="CiMaNovio" placeholder="CI Mamá Novio" maxlength="12">
                                                <a class="mamanovio btn btn-sm btn-dashed rounded mt-0"><i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo</a>
                                            </div>

                                            <div class="col-sm-6 col-lg-3">
                                                <label class="form-label">Ci del Papá de la Novia</label>
                                                <input type="text" class="form-control col-lg-4" name="CiPaNovia" placeholder="CI Papá Novia" maxlength="12">
                                                <a class="papanovia btn btn-sm btn-dashed rounded mt-0"><i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo</a>
                                            </div>

                                            <div class="col-sm-6 col-lg-3">
                                                <label class="form-label">Ci del Mamá de la Novia</label>
                                                <input type="text" class="form-control col-lg-4" name="CiMaNovia" placeholder="CI Mamá Novia" maxlength="12">
                                                <a class="mamanovia btn btn-sm btn-dashed rounded mt-0"><i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo</a>
                                            </div>

                                            <div class="col-sm-6 col-lg-3">
                                                <label class="form-label">Ci del padrino</label>
                                                <input type="text" class="form-control" name="CiPadrino" placeholder="CI Padrino" maxlength="12">
                                                <a class="padrinocelebrante btn btn-sm btn-dashed rounded mt-0"><i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo</a>
                                            </div>

                                            <div class="col-sm-6 col-lg-3">
                                                <label class="form-label">Ci de la Madrina</label>
                                                <input type="text" class="form-control" name="CiMadrina" placeholder="CI Madrina" maxlength="12">
                                                <a class="madrinacelebrante btn btn-sm btn-dashed rounded mt-0"><i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo</a>
                                            </div>

                                            <div class="col-sm-6 col-lg-3">
                                                <label class="form-label">Testigo del Novio</label>
                                                <input type="text" class="form-control" name="CiTestigoNovio" placeholder="CI Testigo" required minlength="4" maxlength="12">
                                                <a class="testigonovio btn btn-sm btn-dashed rounded mt-0"><i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo</a>
                                            </div>

                                            <div class="col-sm-6 col-lg-3">
                                                <label class="form-label">Testigo de la Novia</label>
                                                <input type="text" class="form-control" name="CiTestigoNovia" placeholder="CI Testigo" required minlength="4" maxlength="12">
                                                <a class="testigonovia btn btn-sm btn-dashed rounded mt-0"><i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo</a>
                                            </div>
                                            <input type="hidden" name="TipoCel" value="Matrimonio">

                                            <input type="hidden" name="TokenReserva" value="<?php echo htmlspecialchars($_SESSION['token_reserva'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <div class="col-12">
                                                <label class="form-label" for="correo-matrimonio">Correo del solicitante (opcional)</label>
                                                <input type="email" class="form-control" id="correo-matrimonio" name="CorreoSolicitante" maxlength="254" placeholder="ejemplo@correo.com" autocomplete="email">
                                                <div class="form-check mt-2">
                                                    <input class="form-check-input confirmacion-correo" type="checkbox" id="enviar-matrimonio" name="EnviarConfirmacion" value="1" data-correo="correo-matrimonio">
                                                    <label class="form-check-label" for="enviar-matrimonio">Enviar confirmación por correo</label>
                                                </div>
                                                <small class="text-muted">El correo se guardará para avisarte si se cancela la reserva. Marca la casilla si también deseas la confirmación del registro.</small>
                                            </div>

                                            <button type="submit" class="btn btn-success-soft me-2" name="reservar_matrimonio"> Reservar Matrimonio</button>
                                        </form>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="tab-4">
                                    <div id="formReservaMisa">
                                        <h5>Registrar Nueva Reserva de Misa</h5>
                                        <form method="POST" class="row g-3">
                                            <br>
                                            <input type="hidden" name="CodPer" value="<?php echo $usuario['CodPer']; ?>" required>

                                            <div class="col-sm-4 col-lg-3">
                                                <label class="form-label">Quien Realiza la reserva</label>
                                                <input type="text" class="form-control" name="CiPersonademas" placeholder="Ci del Reservante" required minlength="4" maxlength="12">
                                                <a class="demas btn btn-sm btn-dashed rounded mt-0"><i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>registrar nuevo</a>
                                            </div>

                                            <div class="col-sm-4 col-lg-3">
                                                <label class="form-label">Fecha a realizar</label>
                                                <input type="date" class="form-control" name="FechaReal" id="FechaReal3" required>
                                            </div>

                                            <div class="col-sm-4 col-lg-3">
                                                <label class="form-label">Hora a realizar</label>
                                                <input type="time" class="form-control" name="HoraReal" id="HoraReal3" step="1800" required>
                                            </div>
                                            <input type="hidden" name="TipoCel" value="Misa">

                                            <div class="col-sm-6 col-lg-5">
                                                <label class="form-label">Tipo de Reserva</label>
                                                <div class=" d-sm-flex align-items-center text-center justify-content-sm-betwee">
                                                    <div class="form-check me-2">
                                                        <input class="form-check-input" type="radio" name="Realizacion" id="misa-privado" value="Privado" required>
                                                        <label class="form-check-label form-label" for="misa-privado">
                                                            Privado
                                                        </label>
                                                    </div>
                                                    <div class="form-check me-2">
                                                        <input class="form-check-input" type="radio" name="Realizacion" id="misa-comunitario" value="Comunitario">
                                                        <label class="form-check-label form-label" for="misa-comunitario">
                                                            Comunitario
                                                        </label>
                                                    </div>
                                                    <div class="form-check me-2">
                                                        <input class="form-check-input" type="radio" name="Realizacion" id="misa-otro" value="Otro">
                                                        <label class="form-check-label form-label" for="misa-otro">
                                                            Otro
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="mt-2" id="detalle-otro-misa" hidden>
                                                    <label class="form-label" for="realizacion-otro-misa">Especifica el tipo de misa</label>
                                                    <input type="text" class="form-control" id="realizacion-otro-misa" name="RealizacionOtro" maxlength="30" placeholder="Ej.: Acción de gracias" disabled>
                                                    <small class="text-muted">Descripción breve, máximo 30 caracteres.</small>
                                                </div>
                                            </div>

                                            <div class="col-sm-6 col-lg-4">
                                                <label class="form-label">Para Quien</label>
                                                <textarea name="Quien" rows="5" class="form-control" cols="50" placeholder="Para Quien" required maxlength="255"></textarea>
                                            </div>

                                            <input type="hidden" name="TokenReserva" value="<?php echo htmlspecialchars($_SESSION['token_reserva'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <div class="col-12">
                                                <label class="form-label" for="correo-misa">Correo del solicitante (opcional)</label>
                                                <input type="email" class="form-control" id="correo-misa" name="CorreoSolicitante" maxlength="254" placeholder="ejemplo@correo.com" autocomplete="email">
                                                <div class="form-check mt-2">
                                                    <input class="form-check-input confirmacion-correo" type="checkbox" id="enviar-misa" name="EnviarConfirmacion" value="1" data-correo="correo-misa">
                                                    <label class="form-check-label" for="enviar-misa">Enviar confirmación por correo</label>
                                                </div>
                                                <small class="text-muted">El correo se guardará para avisarte si se cancela la reserva. Marca la casilla si también deseas la confirmación del registro.</small>
                                            </div>

                                            <button type="submit" class="btn btn-success-soft me-2" name="reservar_misa"> Reservar Misa</button>
                                        </form>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <div class="modal fade modal-xl" id="MostrarModalDemas" tabindex="-1" aria-labelledby="MostrarModalDemas" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addModalLabel">Agregar Nueva Persona</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" class="row g-3" id="formModalDemas">
                        <input type="hidden" name="redirigir" value="bautizo">
                        <div class="form-group col-sm-1 col-lg-2">
                            <label for="CiPersona">CiPersona:</label>
                            <input type="text" class="form-control" name="CiPersona" placeholder="CI Persona" required minlength="4" maxlength="12">
                        </div>
                        <div class="form-group col-sm-2 col-lg-3">
                            <label for="Nombre">Nombre:</label>
                            <input type="text" class="form-control" name="Nombre" placeholder="Nombre Completo"
                                required>
                        </div>
                        <div class="form-group col-sm-2 col-lg-2">
                            <label for="ApPaterno">Apellido Paterno:</label>
                            <input type="text" class="form-control" name="ApPaterno" placeholder="Apellido Paterno"
                                required>
                        </div>
                        <div class="form-group col-sm-2 col-lg-2">
                            <label for="ApMaterno">Apellido Materno:</label>
                            <input type="text" class="form-control" name="ApMaterno" placeholder="Apellido Materno">
                        </div>
                        <div class="form-group col-sm-2 col-lg-2">
                            <label class="form-label">Sexo:</label>
                            <div>
                                <input type="radio" name="Sexo" value="Varon" checked>
                                <label for="varon">Varon</label>
                                &nbsp;&nbsp;&nbsp;
                                <input type="radio" name="Sexo" value="Mujer">
                                <label for="mujer">Mujer</label>
                            </div>
                        </div>
                        <br>
                        <div class="form-group col-lg-2">
                            <label for="FechaNac">Fecha de Nacimiento:</label>
                            <input type="date" class="form-control" name="FechaNac" id="FechaNacDemas" required>
                        </div>
                        <div class="form-group col-lg-4">
                            <label for="Direccion">Dirección:</label>
                            <input type="text" class="form-control" name="Direccion" placeholder="Dirección">
                        </div>
                        <div class="form-group col-lg-3">
                            <label for="Contacto">Contacto:</label>
                            <input type="text" class="form-control" name="Contacto" placeholder="Número de contacto">
                        </div>
                        <div class="form-group col-lg-3">
                            <label for="Estado_per">Estado Civil:</label>
                            <select name="Estado_per" class="form-control" required>
                                <option value="Soltero">Soltero</option>
                                <option value="Soltera">Soltera</option>
                                <option value="Casado">Casado</option>
                                <option value="Casada">Casada</option>
                                <option value="Viudo">Viudo</option>
                                <option value="Viuda">Viuda</option>
                            </select>
                        </div><br>
                        <button type="submit" name="crear" class="btn btn-success-soft">Registrar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade modal-xl" id="MostrarModalBautizo" tabindex="-1" aria-labelledby="MostrarModalBautizo" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addModalLabel">Agregar Nueva Persona</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" class="row g-3" id="formModalBautizo">
                        <input type="hidden" name="redirigir" value="bautizo">
                        <div class="form-group col-sm-1 col-lg-2">
                            <label for="CiPersona">CI:</label>
                            <input type="text" class="form-control" name="CiPersona" placeholder="N° CI" minlength="4" maxlength="15">
                        </div>
                        <div class="form-group col-sm-2 col-lg-3">
                            <label for="Nombre">Nombre:</label>
                            <input type="text" class="form-control" name="Nombre" placeholder="Nombre Completo"
                                required>
                        </div>
                        <div class="form-group col-sm-2 col-lg-2">
                            <label for="ApPaterno">Apellido Paterno:</label>
                            <input type="text" class="form-control" name="ApPaterno" placeholder="Apellido Paterno"
                                required>
                        </div>
                        <div class="form-group col-sm-2 col-lg-2">
                            <label for="ApMaterno">Apellido Materno:</label>
                            <input type="text" class="form-control" name="ApMaterno" placeholder="Apellido Materno">
                        </div>
                        <div class="form-group col-sm-2 col-lg-2">
                            <label class="form-label">Sexo:</label>
                            <div>
                                <input type="radio" name="Sexo" value="Varon" checked>
                                <label for="varon">Varon</label>
                                &nbsp;&nbsp;&nbsp;
                                <input type="radio" name="Sexo" value="Mujer">
                                <label for="mujer">Mujer</label>
                            </div>
                        </div>
                        <br>
                        <div class="form-group col-lg-12">
                            <label class="form-label text-muted">Si no tiene CI, complete los datos del Certificado de Nacimiento:</label>
                        </div>
                        <div class="form-group col-sm-2 col-lg-2">
                            <label for="Oficialia">Oficialía:</label>
                            <input type="text" class="form-control" name="Oficialia" placeholder="N° Oficialía">
                        </div>
                        <div class="form-group col-sm-2 col-lg-2">
                            <label for="Libro">Libro:</label>
                            <input type="text" class="form-control" name="Libro" placeholder="N° Libro">
                        </div>
                        <div class="form-group col-sm-2 col-lg-2">
                            <label for="Partida">Partida:</label>
                            <input type="text" class="form-control" name="Partida" placeholder="N° Partida">
                        </div>
                        <div class="form-group col-sm-2 col-lg-2">
                            <label for="Folio">Folio:</label>
                            <input type="text" class="form-control" name="Folio" placeholder="N° Folio">
                        </div>
                        <br>
                        <div class="form-group col-lg-2">
                            <label for="FechaNac">Fecha de Nacimiento:</label>
                            <input type="date" class="form-control" name="FechaNac" id="FechaNacB" max="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="form-group col-lg-4">
                            <label for="Direccion">Dirección:</label>
                            <input type="text" class="form-control" name="Direccion" placeholder="Dirección">
                        </div>
                        <div class="form-group col-lg-3">
                            <label for="Contacto">Contacto:</label>
                            <input type="text" class="form-control" name="Contacto" placeholder="Número de contacto">
                        </div><br>
                        <button type="submit" name="crear" class="btn btn-success-soft">Registrar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            var urlParams = new URLSearchParams(window.location.search);
            var tab = urlParams.get('tab');
            if (tab) {
                var tabId = '';
                if (tab === 'bautizo') tabId = '#tab-2';
                else if (tab === 'matrimonio') tabId = '#tab-3';
                else if (tab === 'misa') tabId = '#tab-4';
                
                if (tabId) {
                    $('a[href="' + tabId + '"]').tab('show');
                }
            }

            var camposBautizoMisa = [
                ['#tab-2', 'CiPersonaCelebranteB', '.celebranteB', '#MostrarModalBautizo', 3],
                ['#tab-2', 'CiPapa', '.papacelebrante'],
                ['#tab-2', 'CiMama', '.mamacelebrante'],
                ['#tab-2', 'CiPadrino', '.padrinocelebrante'],
                ['#tab-2', 'CiMadrina', '.madrinacelebrante'],
                ['#tab-4', 'CiPersonademas', '.demas']
            ];
            camposBautizoMisa.forEach(function(config) {
                var campo = $(config[0] + ' input[name="' + config[1] + '"]');
                var resultado = $(config[0] + ' ' + config[2]);
                var minimo = config[4] || 4;
                var temporizador, solicitud;
                var version = 0;
                resultado.attr('aria-live', 'polite');
                function mostrarRegistro(texto) {
                    resultado.html('<i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="' + (config[3] || '#MostrarModalDemas') + '"></i>' + texto);
                }
                function buscarPersonaReserva() {
                    clearTimeout(temporizador);
                    var actual = ++version;
                    if (solicitud) solicitud.abort();
                    var ciPersona = campo.val().trim();
                    if (ciPersona.length < minimo) {
                        mostrarRegistro('registrar nuevo');
                        return;
                    }
                    resultado.text('Buscando...');
                    temporizador = setTimeout(function() {
                        solicitud = $.ajax({
                            url: 'VistaReservas.php', type: 'GET', dataType: 'json', cache: false,
                            data: { ciPersona: ciPersona },
                            success: function(persona) {
                                if (actual !== version || campo.val().trim() !== ciPersona) return;
                                if (!persona || persona.error) {
                                    mostrarRegistro('registrar nuevo');
                                    return;
                                }
                                var estado = (persona.Estado_per || '').trim();
                                if (config[1] === 'CiPersonaCelebranteB' && (estado === 'Casado' || estado === 'Casada')) {
                                    resultado.text('La persona no se registrará.');
                                    return;
                                }
                                resultado.text([persona.Nombre, persona.ApPaterno, persona.ApMaterno].filter(Boolean).join(' '));
                            },
                            error: function(xhr, estado) {
                                if (actual !== version || estado === 'abort') return;
                                resultado.text('No se pudo consultar el CI. Vuelva a seleccionar el campo para reintentar.');
                            }
                        });
                    }, 250);
                }
                campo.on('input change focus', buscarPersonaReserva);
                $(window).on('pageshow', buscarPersonaReserva);
                campo.closest('form').on('reset', function() { setTimeout(buscarPersonaReserva, 0); });
                buscarPersonaReserva();
            });
            function actualizarTipoMisa() {
                var esOtro = $('#tab-4 input[name="Realizacion"]:checked').val() === 'Otro';
                $('#detalle-otro-misa').prop('hidden', !esOtro);
                $('#realizacion-otro-misa').prop('disabled', !esOtro).prop('required', esOtro);
            }
            $('#tab-4 input[name="Realizacion"]').on('change', actualizarTipoMisa);
            $('#realizacion-otro-misa').on('input', function() {
                this.setCustomValidity(this.value.trim() ? '' : 'Describe cómo será la misa.');
            });
            $('#tab-4 form').on('reset', function() {
                setTimeout(function() {
                    $('#realizacion-otro-misa')[0].setCustomValidity('');
                    actualizarTipoMisa();
                }, 0);
            });
            $(window).on('pageshow', actualizarTipoMisa);
            actualizarTipoMisa();

            function actualizarTipoBautizo() {
                var esOtro = $('#tab-2 input[name="Realizacion"]:checked').val() === 'Otro';
                $('#detalle-otro-bautizo').prop('hidden', !esOtro);
                $('#realizacion-otro-bautizo').prop('disabled', !esOtro).prop('required', esOtro);
            }
            $('#tab-2 input[name="Realizacion"]').on('change', actualizarTipoBautizo);
            $('#realizacion-otro-bautizo').on('input', function() {
                this.setCustomValidity(this.value.trim() ? '' : 'Describe cómo será el bautizo.');
            });
            $('#tab-2 form').on('reset', function() {
                setTimeout(function() {
                    $('#realizacion-otro-bautizo')[0].setCustomValidity('');
                    actualizarTipoBautizo();
                }, 0);
            });
            $(window).on('pageshow', actualizarTipoBautizo);
            actualizarTipoBautizo();

            function actualizarTipoMatrimonio() {
                var esOtro = $('#tab-3 input[name="Realizacion"]:checked').val() === 'Otro';
                $('#detalle-otro-matrimonio').prop('hidden', !esOtro);
                $('#realizacion-otro-matrimonio').prop('disabled', !esOtro).prop('required', esOtro);
            }
            $('#tab-3 input[name="Realizacion"]').on('change', actualizarTipoMatrimonio);
            $('#realizacion-otro-matrimonio').on('input', function() {
                this.setCustomValidity(this.value.trim() ? '' : 'Describe cómo será el matrimonio.');
            });
            $('#tab-3 form').on('reset', function() {
                setTimeout(function() {
                    $('#realizacion-otro-matrimonio')[0].setCustomValidity('');
                    actualizarTipoMatrimonio();
                    refrescarPersonasMatrimonio();
                }, 0);
            });
            $(window).on('pageshow', actualizarTipoMatrimonio);
            actualizarTipoMatrimonio();

            var camposMatrimonio = {
                CiPersonaNovio: '.novio',
                CiPersonaNovia: '.novia',
                CiPaNovio: '.papanovio',
                CiMaNovio: '.mamanovio',
                CiPaNovia: '.papanovia',
                CiMaNovia: '.mamanovia',
                CiPadrino: '.padrinocelebrante',
                CiMadrina: '.madrinacelebrante',
                CiTestigoNovio: '.testigonovio',
                CiTestigoNovia: '.testigonovia'
            };
            var actualizarMatrimonio = [];

            $.each(camposMatrimonio, function(nombre, selector) {
                var campo = $('#tab-3 input[name="' + nombre + '"]');
                var resultado = $('#tab-3 ' + selector);
                var temporizador;
                var solicitud;
                var version = 0;
                resultado.attr('aria-live', 'polite');

                function mostrarRegistro(texto) {
                    resultado.html('<i class="bi bi-plus-circle-dotted me-1" data-bs-toggle="modal" data-bs-target="#MostrarModalDemas"></i>' + texto);
                }

                function buscarPersonaMatrimonio() {
                    clearTimeout(temporizador);
                    var actual = ++version;
                    if (solicitud) solicitud.abort();
                    var ciPersona = campo.val().trim();
                    if (ciPersona.length < 4) {
                        if (ciPersona) resultado.text('Ingrese al menos 4 caracteres del CI.');
                        else mostrarRegistro('registrar nuevo');
                        return;
                    }
                    resultado.text('Buscando...');
                    temporizador = setTimeout(function() {
                        solicitud = $.ajax({
                            url: 'VistaReservas.php',
                            type: 'GET',
                            dataType: 'json',
                            cache: false,
                            data: { ciPersona: ciPersona },
                            success: function(persona) {
                                if (actual !== version || campo.val().trim() !== ciPersona) return;
                                if (!persona || persona.error) {
                                    mostrarRegistro('registrar nuevo');
                                    return;
                                }
                                var sexo = (persona.Sexo || '').trim();
                                var estado = (persona.Estado_per || '').trim();
                                var esNovio = nombre === 'CiPersonaNovio';
                                var esNovia = nombre === 'CiPersonaNovia';
                                if (esNovio || esNovia) {
                                    if (sexo === (esNovia ? 'Varon' : 'Mujer')) {
                                        resultado.text(esNovia ? 'La persona es varon.' : 'La persona es mujer.');
                                        return;
                                    }
                                    if (estado === 'Casado' || estado === 'Casada') {
                                        resultado.text(esNovia ? 'La persona ya est\u00e1 casada.' : 'La persona ya est\u00e1 casado.');
                                        return;
                                    }
                                    if (sexo !== (esNovia ? 'Mujer' : 'Varon') ||
                                        (estado !== 'Soltero' && estado !== 'Soltera')) {
                                        mostrarRegistro('verificar o registrar');
                                        return;
                                    }
                                }
                                resultado.text([persona.Nombre, persona.ApPaterno, persona.ApMaterno].filter(Boolean).join(' '));
                            },
                            error: function(xhr, estado) {
                                if (actual !== version || estado === 'abort') return;
                                resultado.text('No se pudo consultar el CI. Vuelva a seleccionar el campo para reintentar.');
                            }
                        });
                    }, 250);
                }

                campo.on('input change focus', buscarPersonaMatrimonio);
                actualizarMatrimonio.push(buscarPersonaMatrimonio);
                buscarPersonaMatrimonio();
            });

            function refrescarPersonasMatrimonio() {
                actualizarMatrimonio.forEach(function(buscar) { buscar(); });
            }
            $('#MostrarModalDemas').on('hidden.bs.modal', refrescarPersonasMatrimonio);
            $('a[href="#tab-3"]').on('shown.bs.tab', refrescarPersonasMatrimonio);
            $(window).on('pageshow', refrescarPersonasMatrimonio);
        });
    </script>
    <script>
        function setTime(inputId) {
            const inputTime = document.getElementById(inputId);
            const timeValue = inputTime.value;
            if (!timeValue) return;

            let [hours, minutes] = timeValue.split(':').map(Number);

            if (minutes >= 0 && minutes < 30) {
                minutes = 0;
            } else {
                minutes = 30;
            }

            const formattedTime = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
            inputTime.value = formattedTime;
        }

        function setMinDate() {
            const today = new Date();
            today.setDate(today.getDate());

            const dd = String(today.getDate() + 1).padStart(2, '0');
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const yyyy = today.getFullYear();

            const minDate = `${yyyy}-${mm}-${dd}`;

            document.getElementById('FechaReal1').setAttribute('min', minDate);
            document.getElementById('FechaReal2').setAttribute('min', minDate);
            document.getElementById('FechaReal3').setAttribute('min', minDate);
        }

        window.onload = function() {
            setMinDate();
            document.getElementById('HoraReal1').addEventListener('input', function() {
                setTime('HoraReal1');
            });
            document.getElementById('HoraReal2').addEventListener('input', function() {
                setTime('HoraReal2');
            });
            document.getElementById('HoraReal3').addEventListener('input', function() {
                setTime('HoraReal3');
            });
        };
    </script>

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
        function submitPersonaModal(formId, modalId) {
            var form = document.getElementById(formId);
            var elementoModal = document.getElementById(modalId);
            if (!form || !elementoModal) return;

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                if (form.dataset.enviando === '1') return;
                var campoOrigen = elementoModal.campoReserva;
                if (!campoOrigen) {
                    alert('Abre Registrar nuevo desde la casilla de la reserva.');
                    return;
                }
                var formData = new FormData(form);
                formData.append('crear', '1');
                var boton = form.querySelector('button[type="submit"]');
                form.dataset.enviando = '1';
                boton.disabled = true;
                fetch('../Controlador/ajaxCrearPersona.php', {
                    method: 'POST',
                    body: formData
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.success && data.ciPersona) {
                        // Usar el identificador confirmado antes de limpiar el modal.
                        campoOrigen.value = data.ciPersona;
                        campoOrigen.dispatchEvent(new Event('input', { bubbles: true }));
                        campoOrigen.dispatchEvent(new Event('change', { bubbles: true }));
                        form.reset();
                        var modal = bootstrap.Modal.getInstance(elementoModal);
                        if (modal) modal.hide();
                    } else {
                        alert('Error: ' + (data.message || 'No se pudo recuperar el CI registrado.'));
                    }
                })
                .catch(function(error) {
                    alert('Error de conexión: ' + error);
                })
                .finally(function() {
                    form.dataset.enviando = '0';
                    boton.disabled = false;
                });
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Guardar el campo exacto: los padrinos de cada pestaña tienen nombres iguales.
            document.querySelectorAll('#tab-2, #tab-3, #tab-4').forEach(function(tab) {
                tab.addEventListener('click', function(e) {
                    var enlace = e.target.closest('a.btn-dashed');
                    if (!enlace) return;
                    var icono = enlace.querySelector('[data-bs-target]');
                    var campo = enlace.previousElementSibling;
                    if (!icono || !campo || campo.tagName !== 'INPUT') return;
                    e.preventDefault();
                    e.stopPropagation();
                    var elementoModal = document.querySelector(icono.dataset.bsTarget);
                    var form = elementoModal.querySelector('form');
                    if (form.dataset.enviando === '1') return;
                    form.reset();
                    form.elements.CiPersona.value = campo.value.trim();
                    elementoModal.campoReserva = campo;
                    bootstrap.Modal.getOrCreateInstance(elementoModal).show(enlace);
                });
            });
            submitPersonaModal('formModalDemas', 'MostrarModalDemas');
            submitPersonaModal('formModalBautizo', 'MostrarModalBautizo');
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var formularios = Array.from(document.querySelectorAll('#tab-2 form, #tab-3 form, #tab-4 form'));
            var token = formularios[0].elements.TokenReserva.value;
            var clave = 'reservaBorrador:' + location.pathname + ':' + formularios[0].elements.CodPer.value;
            var restaurando = true;
            function camposEditables(form) {
                return Array.from(form.elements).filter(function(campo) {
                    return campo.name && !['hidden', 'submit', 'button', 'reset', 'file'].includes(campo.type);
                });
            }
            function guardarBorrador() {
                if (restaurando) return;
                var datos = { token: token, formularios: {} };
                var activa = document.querySelector('.tab-pane.active');
                datos.tab = activa ? activa.id : '';
                formularios.forEach(function(form) {
                    var campos = {};
                    camposEditables(form).forEach(function(campo) {
                        if (campo.type === 'radio') {
                            if (!(campo.name in campos)) campos[campo.name] = '';
                            if (campo.checked) campos[campo.name] = campo.value;
                        } else {
                            campos[campo.name] = campo.type === 'checkbox' ? campo.checked : campo.value;
                        }
                    });
                    datos.formularios[form.closest('.tab-pane').id] = campos;
                });
                try { sessionStorage.setItem(clave, JSON.stringify(datos)); } catch (e) { /* El guardado de personas sigue disponible. */ }
            }
            try {
                var borrador = JSON.parse(sessionStorage.getItem(clave) || 'null');
                if (borrador && borrador.token === token && borrador.formularios) {
                    formularios.forEach(function(form) {
                        var datos = borrador.formularios[form.closest('.tab-pane').id] || {};
                        camposEditables(form).forEach(function(campo) {
                            if (!Object.prototype.hasOwnProperty.call(datos, campo.name)) return;
                            if (campo.type === 'radio') campo.checked = datos[campo.name] === campo.value;
                            else if (campo.type === 'checkbox') campo.checked = datos[campo.name] === true;
                            else campo.value = datos[campo.name];
                            campo.dispatchEvent(new Event('change', { bubbles: true }));
                        });
                    });
                    if (['tab-2', 'tab-3', 'tab-4'].includes(borrador.tab) && !new URLSearchParams(location.search).has('tab')) {
                        bootstrap.Tab.getOrCreateInstance(document.querySelector('a[href="#' + borrador.tab + '"]')).show();
                    }
                } else {
                    // El token cambia cuando la reserva se guarda correctamente.
                    sessionStorage.removeItem(clave);
                }
            } catch (e) { /* No impedir el formulario si el navegador bloquea el almacenamiento. */ }
            restaurando = false;
            formularios.forEach(function(form) {
                form.addEventListener('input', guardarBorrador);
                form.addEventListener('change', guardarBorrador);
                form.addEventListener('submit', guardarBorrador);
                form.addEventListener('reset', function() { setTimeout(guardarBorrador, 0); });
            });
            document.addEventListener('shown.bs.tab', guardarBorrador);
            window.addEventListener('pagehide', guardarBorrador);
        });
    </script>
    <script>
        function verificarPadrinos() {
            var form = document.getElementById('formReservaBautizo');
            if (!form) return;
            var ciPadrino = form.querySelector('input[name="CiPadrino"]').value.trim();
            var ciMadrina = form.querySelector('input[name="CiMadrina"]').value.trim();
            var btnReservar = form.querySelector('button[name="reservar_bautizo"]');

            if (!ciPadrino && !ciMadrina) {
                btnReservar.removeAttribute('disabled');
                return;
            }

            var ciVerificar = ciPadrino || ciMadrina;

            fetch('VistaReservas.php?ciPersona=' + encodeURIComponent(ciVerificar))
                .then(function(response) { return response.json(); })
                .then(function(persona) {
                    if (persona && !persona.error) {
                        var estado = (persona.Estado_per || '').trim();
                        var esCasado = (estado === 'Casado' || estado === 'Casada');

                        if (esCasado) {
                            if (!ciPadrino || !ciMadrina) {
                                btnReservar.setAttribute('disabled', 'disabled');
                                alert('El padrino/madrina es casado/a, por favor registre a ambos.');
                            } else {
                                btnReservar.removeAttribute('disabled');
                            }
                        } else {
                            btnReservar.removeAttribute('disabled');
                        }
                    } else {
                        btnReservar.removeAttribute('disabled');
                    }
                })
                .catch(function() {
                    btnReservar.removeAttribute('disabled');
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            var form = document.getElementById('formReservaBautizo');
            if (!form) return;
            var ciPadrino = form.querySelector('input[name="CiPadrino"]');
            var ciMadrina = form.querySelector('input[name="CiMadrina"]');

            if (ciPadrino) {
                ciPadrino.addEventListener('blur', verificarPadrinos);
            }
            if (ciMadrina) {
                ciMadrina.addEventListener('blur', verificarPadrinos);
            }
        });
    </script>
    <script>
        const todayD = new Date();
        const maxDateD = new Date(todayD.getFullYear() - 18, todayD.getMonth(), todayD.getDate());
        const yearD = maxDateD.getFullYear();
        const monthD = String(maxDateD.getMonth() + 1).padStart(2, '0');
        const dayD = String(maxDateD.getDate()).padStart(2, '0');
        document.getElementById('FechaNacDemas').setAttribute('max', `${yearD}-${monthD}-${dayD}`);

        const todayB = new Date();
        const maxDateB = new Date(todayB.getFullYear(), todayB.getMonth(), todayB.getDate());
        const yearB = maxDateB.getFullYear();
        const monthB = String(maxDateB.getMonth() + 1).padStart(2, '0');
        const dayB = String(maxDateB.getDate()).padStart(2, '0');
        document.getElementById('FechaNacB').setAttribute('max', `${yearB}-${monthB}-${dayB}`);
    </script>

    <script>
        const months = [
            "Enero", "Febrero", "Marzo", "Abril", "Mayo",
            "Junio", "Julio", "Agosto", "Septiembre",
            "Octubre", "Noviembre", "Diciembre"
        ];

        const dayNames = ["Lun", "Mar", "Mié", "Jue", "Vie", "Sáb", "Dom"];

        let currentMonthIndex = new Date().getMonth();
        let currentYear = new Date().getFullYear();

        const coloresCelebracion = {
            confirmacion: { nombre: 'Confirmación', fondo: '#fecaca', borde: '#dc2626' },
            comunion: { nombre: 'Primera Comunión', fondo: '#fef08a', borde: '#a16207' },
            matrimonio: { nombre: 'Matrimonio', fondo: '#bae6fd', borde: '#0284c7' },
            misa: { nombre: 'Misa', fondo: '#bbf7d0', borde: '#15803d' },
            bautizo: { nombre: 'Bautizo', fondo: '#ffffff', borde: '#d4af37' }
        };

        function tipoCelebracion(reserva) {
            const tipo = String(reserva.tipocel || reserva.DescripSac || '')
                .normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
            if (tipo.includes('confirmacion')) return 'confirmacion';
            if (tipo.includes('comunion')) return 'comunion';
            if (tipo.includes('matrimonio')) return 'matrimonio';
            if (tipo.includes('misa') || tipo.includes('oracion')) return 'misa';
            if (tipo.includes('bautizo')) return 'bautizo';
            return 'bautizo';
        }

        function displayMonth() {
            const monthDisplay = document.getElementById('monthDisplay');
            monthDisplay.innerText = `${months[currentMonthIndex]} ${currentYear}`;

            displayDays();
        }

        function displayDays() {
            const weekContainer = document.getElementById('week-container');
            weekContainer.innerHTML = '';
            document.getElementById('fecha').value = '';
            document.getElementById('reminderList').textContent = 'Seleccione una fecha.';

            const firstDayOfMonth = new Date(currentYear, currentMonthIndex, 1).getDay();
            const daysInMonth = new Date(currentYear, currentMonthIndex + 1, 0).getDate();
            let currentDay = new Date().getDate();

            const adjustedFirstDay = (firstDayOfMonth + 6) % 7;
            for (let i = 0; i < adjustedFirstDay; i++) {
                const emptyDiv = document.createElement('div');
                weekContainer.appendChild(emptyDiv);
            }

            for (let day = 1; day <= daysInMonth; day++) {
                const dayDiv = document.createElement('div');
                dayDiv.classList.add('day');
                dayDiv.dataset.dia = day;
                dayDiv.innerText = day;
                const dayNameDiv = document.createElement('div');
                dayNameDiv.classList.add('day-name');
                const dayOfWeekIndex = (adjustedFirstDay + day - 1) % 7;
                dayNameDiv.innerText = dayNames[dayOfWeekIndex];

                dayDiv.appendChild(dayNameDiv);
                dayDiv.addEventListener('click', () => {
                    toggleSelection(dayDiv);
                    showReminders(day);
                });

                weekContainer.appendChild(dayDiv);

                if (day === currentDay && currentMonthIndex === new Date().getMonth() && currentYear === new Date().getFullYear()) {
                    toggleSelection(dayDiv);
                }
            }

            highlightReservedDays(currentMonthIndex + 1, currentYear);
        }

        function toggleSelection(dayDiv) {
            const previouslySelected = document.querySelector('.selected');
            if (previouslySelected) {
                previouslySelected.classList.remove('selected');
            }
            dayDiv.classList.add('selected');
        }

        function showReminders(day) {
            const remindersDiv = document.getElementById('reminderList');
            const fechaSeleccionada = `${currentYear}-${(currentMonthIndex + 1).toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;

            document.getElementById("fecha").value = fechaSeleccionada;

            remindersDiv.innerHTML = `<h6>Fecha: ${day} ${months[currentMonthIndex]}</h6>`;

            fetch(`../Controlador/controladorReserva.php?fecha=${fechaSeleccionada}`)
                .then(response => response.json())
                .then(data => {
                    if (document.getElementById('fecha').value !== fechaSeleccionada) return;
                    if (data.length > 0) {
                        data.forEach(reserva => {
                            const [hora, minutos] = reserva.HoraReal.split(':');
                            const horaFormateada = `${hora}:${minutos}`;

                            let realizacionIcon;
                            switch (reserva.Realizacion) {
                                case 'Comunitario':
                                    realizacionIcon = '<i class="bi bi-people-fill"></i>';
                                    break;
                                case 'Privado':
                                    realizacionIcon = '<i class="bi bi-person-fill"></i>';
                                    break;
                                case 'Otro':
                                    realizacionIcon = '<i class="bi bi-app-indicator"></i>';
                                    break;
                                default:
                                    realizacionIcon = '';
                            }

                            const color = coloresCelebracion[tipoCelebracion(reserva)];
                            const detalle = document.createElement('div');
                            detalle.className = 'reserva-calendario';
                            
                            if (reserva.EstadoRes === 'Cancelado') {
                                detalle.style.backgroundColor = '#000000';
                                detalle.style.borderLeftColor = '#dc2626';
                                detalle.style.color = '#ffffff';
                            } else {
                                detalle.style.backgroundColor = color.fondo;
                                detalle.style.borderLeftColor = color.borde;
                                if (tipoCelebracion(reserva) === 'bautizo') {
                                    detalle.style.border = '2px solid ' + color.borde;
                                }
                            }
                            
                            const icono = document.createElement('span');
                            icono.innerHTML = realizacionIcon;
                            detalle.appendChild(icono);
                            
                            let textoHorario = ` | ${horaFormateada} · ${reserva.tipocel}`;
                            if (reserva.HoraFin && reserva.DuracionHoras) {
                                textoHorario = ` | ${horaFormateada} - ${reserva.HoraFin} · ${reserva.tipocel} (${reserva.DuracionHoras}h)`;
                            }
                            detalle.appendChild(document.createTextNode(textoHorario));
                            if (['matrimonio', 'bautizo', 'misa'].includes(tipoCelebracion(reserva)) && reserva.Realizacion) {
                                const tipoReserva = document.createElement('div');
                                tipoReserva.className = 'small';
                                tipoReserva.textContent = 'Tipo de reserva: ' + reserva.Realizacion;
                                detalle.appendChild(tipoReserva);
                            }

                            if (reserva.EstadoRes && reserva.EstadoRes !== 'Especial') {
                                const estado = document.createElement('span');
                                estado.className = 'estado-reserva';
                                if (reserva.EstadoRes === 'Cancelado') {
                                    estado.style.backgroundColor = '#dc2626';
                                    estado.style.color = '#ffffff';
                                }
                                estado.textContent = reserva.EstadoRes;
                                detalle.appendChild(estado);
                            }

                            if (reserva.CodRes) {
                                const enlace = document.createElement('a');
                                enlace.href = 'modificar_reserva.php?' + new URLSearchParams({
                                    CodRes: reserva.CodRes, TipoCel: reserva.tipocel, CodIns: reserva.CodIns
                                });
                                enlace.className = 'ms-2';
                                enlace.title = 'Ver reserva';
                                enlace.setAttribute('aria-label', 'Ver reserva');
                                enlace.innerHTML = '<i class="bi bi-eye-fill"></i>';
                                detalle.appendChild(enlace);
                            }
                            remindersDiv.appendChild(detalle);
                        });
                    } else {
                        remindersDiv.innerHTML += `<div>Sin reservas.</div>`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (document.getElementById('fecha').value === fechaSeleccionada) {
                        remindersDiv.textContent = 'Error al cargar los datos.';
                    }
                });
        }

        function changeMonth(direction) {
            if (direction === 'next') {
                currentMonthIndex++;
                if (currentMonthIndex > 11) {
                    currentMonthIndex = 0;
                    currentYear++;
                }
            } else if (direction === 'prev') {
                currentMonthIndex--;
                if (currentMonthIndex < 0) {
                    currentMonthIndex = 11;
                    currentYear--;
                }
            }
            displayMonth();
        }

        function highlightReservedDays(mes, year) {
            fetch(`../Controlador/controladorReserva.php?mes=${mes}&year=${year}`)
                .then(response => response.json())
                .then(data => {
                    if (mes !== currentMonthIndex + 1 || year !== currentYear) return;
                    const tiposPorDia = new Map();
                    data.forEach(reserva => {
                        const dia = Number(reserva.dia);
                        if (!Number.isInteger(dia) || dia < 1 || dia > 31) return;
                        if (!tiposPorDia.has(dia)) tiposPorDia.set(dia, new Set());
                        tiposPorDia.get(dia).add(tipoCelebracion(reserva));
                    });
                    tiposPorDia.forEach((tipos, dia) => {
                        const dayElement = document.querySelector(`#week-container .day[data-dia="${dia}"]`);
                        if (dayElement) {
                            const categorias = Object.keys(coloresCelebracion).filter(tipo => tipos.has(tipo));
                            const fondos = categorias.map(tipo => coloresCelebracion[tipo].fondo);
                            const franjas = fondos.map((color, index) =>
                                `${color} ${index * 100 / fondos.length}% ${(index + 1) * 100 / fondos.length}%`);
                            dayElement.classList.add('reserved');
                            dayElement.style.background = fondos.length === 1
                                ? fondos[0] : `linear-gradient(135deg, ${franjas.join(', ')})`;
                            dayElement.style.boxShadow = tipos.has('bautizo')
                                ? 'inset 0 0 0 2px ' + coloresCelebracion.bautizo.borde : '';
                            dayElement.title = categorias.map(tipo => coloresCelebracion[tipo].nombre).join(', ');
                            dayElement.setAttribute('aria-label', `${dia} de ${months[mes - 1]}: ${dayElement.title}`);
                        }
                    });
                })
                .catch(error => {
                    console.error('Error al cargar los días reservados:', error);
                });
        }

        displayMonth();
        document.getElementById('prevMonth').addEventListener('click', () => changeMonth('prev'));
        document.getElementById('nextMonth').addEventListener('click', () => changeMonth('next'));
    </script>
    <script>
        document.querySelectorAll('.confirmacion-correo').forEach(function (casilla) {
            const correo = document.getElementById(casilla.dataset.correo);
            const actualizar = function () {
                correo.required = casilla.checked;
                correo.labels[0].textContent = casilla.checked
                    ? 'Correo del solicitante (obligatorio para enviar)'
                    : 'Correo del solicitante (opcional)';
            };
            casilla.addEventListener('change', actualizar);
            actualizar();
        });
    </script>
    <script src="../assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/functions.js"></script>
    <script src="../assets/js/tema.js"></script>
</body>

</html>
