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
    <link rel="stylesheet" type="text/css" href="../assets/css/Index.css">


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
                            <a class="nav-link active" href="../index.php">Inicio</a>
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

        <section class="py-4 py-sm-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-12 text-center mb-4">
                        <h2 class="h1">Contáctate con nosotros</h2>
                        <p>Estamos abiertos a la participación activa de aquellos que deseen compartir su fe y
                            contribuir al bienestar espiritual de Sacaba. Ya sea como catequista o participante, todos
                            son bienvenidos a formar parte de esta comunidad vibrante y centrada en la fe.</p>
                    </div>
                </div>
                <div class="row g-4 g-lg-5 align-items-center">
                    <div class="col-lg-6 card card-body bg-mode shadow-none border-0 p-3">
                        <div class="text-center">
                            <h5>Más Información</h5>
                        </div>
                        <div class="card-body p-2 pb-0">
                            <h5 class="card-title mb-1 mt-3"><i class="bi bi-geo-alt"></i> Ubicación: </h5>
                            <p">Plaza Principal 6 de Agosto, lado Este - lado Parroquia San Pedro de Sacaba.</p>
                        </div>
                        <div class="card-body p-2 pb-0">
                            <h5><i class="bi bi-envelope"></i> Correo:</h5>
                            <p>info@example.com</p>
                        </div>
                        <div class="card-body p-2 pb-0">
                            <h5><i class="bi bi-phone"></i> Telefono / Celular:</h5>
                            <p>+1 5589 55488 55s</p>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="bg-mode shadow-none border-0 p-3 overflow-hidden">
                            <div class="row g-4 d-flex">
                                <div class="text-center">
                                    <h5>Envíanos un mensaje</h5>
                                </div>
                                <form action="" method="POST">
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label for="nombre">Nombre Completo</label>
                                            <input type="text" name="nombre" class="form-control" placeholder="Nombre Completo" required>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="numcorr">Telefono - Contacto</label>
                                            <input type="number" class="form-control" name="numcorr" placeholder="Número de contacto" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="concepto">Concepto</label>
                                        <input type="text" class="form-control" name="concepto" placeholder="Motivo del Mensaje" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="detalle">Mensaje</label>
                                        <textarea class="form-control" name="detalle" rows="5" required></textarea>
                                    </div>
                                    <br>
                                    <button type="submit" name="submit" class="btn btn-success-soft">Enviar Mensaje</button>
                                </form>


                                <?php if ($resultado) { ?>
                                <p style="color:green;"><?php echo $resultado; ?></p>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 col-lg-12">
                        <div class="card card-body bg-mode shadow-none border-0 p-3">
                            <h4 class="mt-4">Nuestra ubicación exacta..</h4>
                            <p class="mb-0">Pude visualizar la ubicación exacta de nuestra <strong>"Parroquia San Pedro
                                    de Sacaba"</strong></p>
                            <div>
                                <img class="h-100%" src="../assets/images/mockup/Ubicacion.PNG" alt="">
                            </div>
                            <div class="text-center p-2">
                                <a href="https://g.co/kgs/9ERBEq9">
                                    <p><i class="bi bi-geo-alt"></i> San Pedro de Sacaba</p>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>






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
                        echo '<a href="../logout.php" class="btn btn-lg btn-danger-soft"> Cerrar Session</a>';
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
    <script src="../assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/vendor/plyr/plyr.js"></script>
    <script src="../assets/vendor/tiny-slider/dist/tiny-slider.js"></script>
    <script src="../assets/js/functions.js"></script>
    <script src="../assets/js/tema.js"></script>
</body>

</html>