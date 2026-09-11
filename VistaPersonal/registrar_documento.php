<?php
require_once '../controlador/ControladorReserva.php';

session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../logout.php");
    exit();
}
$usuario = $_SESSION['usuario'];

$controller = new ControladorReserva();
$documentoModel = new Reserva();
$codIns = $_GET['CodIns'] ?? null;
$documentos = $documentoModel->obtenerDocumentosPorInscripcion($codIns);


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registrar_documento'])) {
    $controller->registrarDocumentoExt($_POST);

    header("Location: registrar_documento.php?CodIns=" . $codIns);
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
    <link rel="stylesheet" type="text/css" href="../assets/css/style.css">
    <script src="../assets/js/jquery-3.6.4.min.js"></script>
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
                                                <a class="nav-link" href="VistaReservas.php"> <img class="me-2 h-20px fa-fw" src="../assets/images/icon/instrucciones.png" alt=""><span>Celebración </span></a>
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
                                                <a class="nav-link active" href="VistaSolicitante.php"> <img class="me-2 h-20px fa-fw" src="../assets/images/icon/contrato.png" alt=""><span>Certificado </span></a>
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
                    <div class="card h-100 p-4 mt-2">

                        <h2>Documentos Registrados</h2>

                        <hr>
                        <?php if (count($documentos) > 0): ?>
                            <div class="col-sm-12 col-lg-12">
                                <table class="table">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Código Documento</th>
                                            <th>Pertenece a</th>
                                            <th>Tipo Documento</th>
                                            <th>Presentado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($documentos as $documento): ?>
                                            <tr>
                                                <td><?php echo $documento['CodificDoc']; ?></td>
                                                <td><?php echo $documento['Dequien']; ?></td>
                                                <td><?php echo $documento['DescripTDoc']; ?></td>
                                                <td><?php echo $documento['FechaPres']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>

                                <br>
                                <h6>Agregar más documentos</h6>
                            </div>

                        <?php else: ?>
                            <div class="col-sm-12 col-lg-12">
                                <p>No se han registrado documentos.</p>
                            </div>
                        <?php endif; ?>

                        <div class="col-sm-12 col-lg-12">
                            <form method="POST" class="row g-3">
                                <input type="hidden" name="CodIns" value="<?php echo $codIns; ?>">
                                <div class="form-group col-sm-4 col-lg-4">
                                    <label for="CodTDoc">Tipo de Documento</label>
                                    <select name="CodTDoc" class="form-control" id="CodTDoc" required onchange="toggleFields()">
                                        <option value="1">Cedula de Identidad</option>
                                        <option value="2">Certificado de Nacimiento</option>
                                        <option value="3">Certificado de Bautizo</option>
                                        <option value="4">Certificado de Primera Comunión</option>
                                        <option value="5">Certificado de Confirmación</option>
                                        <option value="6">Certificado de Matrimonio</option>
                                        <option value="7">Certificado de Defunción</option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4 col-lg-3">
                                    <label for="CiPersona">Fecha Presentada</label>
                                    <input type="date" class="form-control" name="FechaPres" required
                                        max="<?php echo date('Y-m-d'); ?>"
                                        value="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="form-group col-sm-4 col-lg-3">
                                    <label for="CodificDoc">Código de Documento</label>
                                    <input type="text" class="form-control" name="CodificDoc" placeholder="Código Documento" required>
                                </div>
                                <div class="form-group col-sm-3 col-lg-2">
                                    <label for="Dequien">A quien Pertenece</label>
                                    <select name="Dequien" class="form-control" required>
                                        <option value="Celebrante">Feligrés</option>
                                        <option value="Novio">Novio</option>
                                        <option value="Novia">Novia</option>
                                        <option value="Papá">Papá</option>
                                        <option value="Mamá">Mamá</option>
                                        <option value="Padrino">Padrino</option>
                                        <option value="Madrina">Madrina</option>
                                        <option value="Testigo Novio">Testigo Novio</option>
                                        <option value="Testigo Novia">Testigo Novia</option>
                                    </select>
                                </div>

                                <div id="conditionalFields" style="display: none;">
                                    <div class="form-group col-sm-3 col-lg-3">
                                        <label for="ParroquiaBau">Donde se realizó</label>
                                        <input type="text" class="form-control" name="ParroquiaBau" placeholder="Nombre de lugar" required>
                                    </div>

                                    <div class="form-group col-sm-3 col-lg-2">
                                        <label for="NroLibro">Libro</label>
                                        <input type="text" class="form-control" name="NroLibro" placeholder="Libro" required>
                                    </div>

                                    <div class="form-group col-sm-3 col-lg-2">
                                        <label for="NroPag">Página</label>
                                        <input type="text" class="form-control" name="NroPag" placeholder="Página" required>
                                    </div>

                                    <div class="form-group col-sm-3 col-lg-2">
                                        <label for="NroPart">Partida</label>
                                        <input type="text" class="form-control" name="NroPart" placeholder="Partida" required>
                                    </div>

                                    <div class="form-group col-sm-3 col-lg-3">
                                        <label for="FechaBau">Fecha Registro</label>
                                        <input type="date" class="form-control" name="FechaBau" max="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                </div>

                                <div class="col-sm-6 col-lg-6">
                                    <a href="VistaSolicitante.php" class="btn btn-warning-soft w-100"><i class="bi bi-arrow-bar-left"></i> Volver a Anterior</a>
                                </div>
                                <div class="col-sm-6 col-lg-6">
                                    <button type="submit" name="registrar_documento" class="btn btn-success-soft w-100">Registrar Documento <i class="bi bi-upload"></i></button>
                                </div>
                            </form>
                        </div>


                    </div>
                </div>
            </div>
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

            if (select.value === "2" || select.value === "3" || select.value === "5" || select.value === "6") {
                conditionalFields.style.display = 'flex';
            } else {
                conditionalFields.style.display = 'none';
            }
        }
    </script>
    <script src="../assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/functions.js"></script>
    <script src="../assets/js/tema.js"></script>
</body>

</html>