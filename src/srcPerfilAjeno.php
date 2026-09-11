<?php
session_start();
require_once '../Controlador/controladorPersonalPer.php';
$controller = new ContPersonal();

$diassemana = array("Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado");
$meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['CiPer'])) {
    $CiPersonal = $_POST['CiPer'];
    $personalper = $controller->PerfildePersonal($CiPersonal);

    if (!empty($personalper)) {
        $personalper = $personalper[0];
    } else {
        echo "<p>No se encontró información para este personal.</p>";
        exit();
    }
} else {
    header("Location: ../logout.php");
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
    <link rel="stylesheet" type="text/css" href="../assets/css/Index.css">
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

    <header class="navbar-light fixed-top header-static bg-mode">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="../index.php">
                    <img class="light-mode-item navbar-brand-item" src="../assets/images/logo.png" alt="logo">
                    <img class="dark-mode-item navbar-brand-item" src="../assets/images/logo-light.png" alt="logo">
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
                            <a class="nav-link active dropdown-toggle" href="#" id="NosotrosMenu" data-bs-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">Conócenos</a>
                            <ul class="dropdown-menu" aria-labelledby="NosotrosMenu">
                                <li> <a class="dropdown-item" href="srcNosotrosParroquia.php">Parroquia</a></li>
                                <li> <a class="dropdown-item" href="srcNosotrosComunion.php">Primera Comunión</a></li>
                                <li> <a class="dropdown-item" href="srcNosotrosConfirmacion.php">Confirmación</a></li>
                            </ul>
                        </li>

                    </ul>
                </div>
                <div class="ms-3 ms-lg-auto me-2" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Ingresar">
                    <button type="button" class="btn btn-primary-soft" data-bs-toggle="modal"
                        data-bs-target="#exampleModal">
                        <i class="bi bi-person-fill"></i>
                    </button>
                </div>
                <div class="ms-3 ms-lg-auto" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Contactos">
                    <a type="button" href="srcContacto.php" class="btn btn-primary-soft">
                        <i class="bi bi-chat-left-dots"></i>
                    </a>
                </div>
            </div>
        </nav>
    </header>


    <main>
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-8 vstack gap-4">
                    <div class="card">
                        <div class="h-200px rounded-top" style="background-image:url(<?php echo ($personalper['FondoPer'] == 'Ninguno' || empty($personalper['FondoPer'])) ? '../assets/images/fondo/Ninguno.jpg' : "../assets/images/fondo/{$personalper['FondoPer']}"; ?>); background-position: center; background-size: cover; background-repeat: no-repeat;"></div>

                        <div class="card-body py-0">
                            <div class="d-sm-flex align-items-start text-center text-sm-start">
                                <div>
                                    <div class="avatar avatar-xxl mt-n5 mb-3">
                                        <img class="avatar-img rounded-circle border border-white border-3" src="<?php echo ($personalper['PerfilPer'] == 'Ninguno' || empty($personalper['PerfilPer'])) ? '../assets/images/avatar/Ninguno.png' : "../assets/images/avatar/{$personalper['PerfilPer']}"; ?>" alt="">
                                    </div>
                                </div>
                                <div class="ms-sm-4 mt-sm-3">
                                    <h1 class="mb-0 h5"><?php echo $personalper['NombreCompleto']; ?></h1>
                                    <p>Personal de "<strong>San Pedro de Sacaba</strong>"</p>
                                </div>

                            </div>
                            <ul class="list-inline mb-0 text-center text-sm-start mt-3 mt-sm-0">
                                <li class="list-inline-item"><i class="bi bi-briefcase me-1"></i> Cargo <strong><?php echo $personalper['DescripCar']; ?></strong></li>
                                <li class="list-inline-item"><i class="bi bi-geo-alt me-1"></i>Sacaba - Chapare</li>
                            </ul>
                        </div><br>
                    </div>

                    <div class="card card-body">

                        <?php if ($personalper['Estado'] == "Activo") : ?>

                            <div class="card-header border-0 pb-0">
                                <h5 class="card-title">Información de Cuenta</h5>
                            </div>
                            <div class="card-body position-relative pt-0">
                                <p><?php echo $personalper['FrasePer']; ?></p>
                                <ul class="list-unstyled mt-3 mb-0">
                                    <?php
                                    $fechaAdm = $personalper['FechaIng'];
                                    $timestampFecha = strtotime($fechaAdm);
                                    $dia = date('d', $timestampFecha);
                                    $mes = $meses[date('n', $timestampFecha) - 1];
                                    $fechaFormateada = "$dia de $mes";
                                    ?>

                                    <li class="mb-2"> <i class="bi bi-person-workspace me-1"></i> Fecha de Ingreso: <strong> <?php echo $fechaFormateada; ?></strong></li>

                                    <li class="mb-2" <?php if ($personalper['Estado'] == "Activo") : ?>style="color:limegreen;" <?php elseif ($personalper['Estado'] == "Inactivo") : ?>style="color:Red;" <?php endif; ?>> <i class="bi bi-broadcast"></i> Estado: <strong> <?php echo $personalper['Estado']; ?> </strong> </li>

                                    <li> <i class="bi bi-telephone fa-fw pe-1"></i> Contacto: <strong> <?php echo $personalper['ContactoPer']; ?> </strong> </li>
                                </ul>
                            </div>
                            <a class="btn btn-success-soft btn-sm" href="srcNosotrosParroquia.php">Volver a mi Perfil</a>

                        <?php elseif ($personalper['Estado'] == "Inactivo") : ?>
                            <div class="col-lg-8 mx-auto align-items-center text-center justify-content-sm-between">
                                <h1 class="display-1 mt-4">Oops!</h1>
                                <h2 class="mb-2 h1">Esta cuenta fue suspendida!</h2>
                                <p>"Reunete con el administrador para obtener más información"</p>
                                <a class="btn btn-danger-soft btn-sm" href="srcNosotrosParroquia.php">Volver a mi Perfil</a>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Ingrese sus datos</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <?php
                    if (isset($_SESSION['usuario']) || isset($_SESSION['CiPersona'])) {
                        echo '<h2 class="mb-48 text-center">Oops..</h2>';
                        echo '<h6 class="mb-48 text-center">Ya tienes una sesion activa</h6><br>';
                        echo '<a href="logout.php" class="btn btn-lg btn-danger-soft"> Cerrar Session</a>';
                    } else {
                    ?>
                        <div class="modal-body">
                            <form action="../Controlador/controladorLogin.php" method="POST" class="form-validator"
                                id="login-form">
                                <div class="mb-3 position-relative input-group-lg">
                                    <input type="text" class="form-control" placeholder="Usuario" id="username"
                                        name="username">
                                </div>
                                <div class="mb-3">
                                    <div class="input-group input-group-lg">
                                        <input class="form-control fakepassword" type="password" id="password"
                                            name="password" placeholder="Contraseña">
                                        <span class="input-group-text p-0">
                                            <i class="fakepasswordicon fa-solid fa-eye-slash cursor-pointer p-2 w-40px"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-lg btn-primary-soft">Ingresar</button>
                                </div>
                            </form>
                        </div>
                    <?php }
                    ?>
                </div>
            </div>
        </div>
    </main>

    <script src="../assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/functions.js"></script>
    <script src="../assets/js/tema.js"></script>
</body>

</html>