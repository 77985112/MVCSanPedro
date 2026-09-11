<?php
session_start();


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
                            <a class="nav-link active dropdown-toggle" href="#" id="SacramentoMenu"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Sacramentos</a>
                            <ul class="dropdown-menu" aria-labelledby="SacramentoMenu">
                                <li> <a class="dropdown-item" href="srcSacramentoBautizo.php">Bautizo</a></li>
                                <li> <a class="dropdown-item" href="srcSacramentoComunion.php">Primera Comunión</a></li>
                                <li> <a class="dropdown-item" href="srcSacramentoConfirmacion.php">Confirmación</a></li>
                                <li> <a class="dropdown-item active" href="srcSacramentoMatrimonio.php">Matrimonio</a></li>
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
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-8 vstack gap-4">

                    <div class="card h-100">

                        <div class="card-body">

                            <section class="p-4 p-sm-5">
                                <div class="container">
                                    <div class="row">
                                        <div class="col-lg-10 ms-auto">
                                            <div class="row g-4 align-items-center">
                                                <div class="col-md-4 col-lg-4 position-relative">

                                                    <img class="rounded-circle" src="../assets/images/post/Sacramentos/matrimonio.jpg" alt="" style="width: 350px; height: 300px;">

                                                </div>
                                                <div class="col-md-8 col-lg-8">
                                                    <div class="ms-4">
                                                        <h2 class="h2">¿Qué es el Matrimonio?</h2>
                                                        <p class="mb-5">La unión conyugal tiene su origen en Dios, quien al crear al hombre lo hizo una persona que necesita abrirse a los demás, con una necesidad de comunicarse y que necesita compañía. <strong>“No está bien que el hombre esté solo, hagámosle una compañera semejante a él.” (Gen. 2, 18).</strong> Desde el principio de la creación, cuando Dios crea a la primera pareja, la unión entre ambos se convierte en una institución natural, con un vínculo permanente y unidad total <strong>(Mt. 19,6).</strong> Por lo que no puede ser cambiada en sus fines y en sus características, ya que de hacerlo se iría contra la propia naturaleza del hombre. El matrimonio no es, por tanto, efecto de la casualidad o consecuencia de instintos naturales inconscientes.<strong>“Dios creó al hombre y a la mujer a imagen de Dios, hombre y mujer los creó, y los bendijo diciéndoles: procread, y multiplicaos, y llenad la tierra y sometedla”.(Gen. 1, 27- 28).</strong></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <br>
                                <div class="row justify-content-center">
                                    <div class="col-lg-12 text-center mb-2">
                                        <h2 class="h2">Requisitos para Matrimonio</h2>
                                        <p>Todo lo que necesitas para realizar un Matrimonio.</p>
                                    </div>
                                </div>

                                <div class="col-lg-12">
                                    <div class="ms-2">
                                        <h6 class="h6"><i class="bi bi-arrow-return-right"></i> Los Novios deben presentar:</h6>
                                        <p>&nbsp;&nbsp;&nbsp;<i class="bi bi-caret-right-fill"></i> Certificado de Nacimineto (Original y Fotocopia)</p>
                                        <p>&nbsp;&nbsp;&nbsp;<i class="bi bi-caret-right-fill"></i> Certificado de Bautizo (Original y Fotocopia)</p>
                                        <p>&nbsp;&nbsp;&nbsp;<i class="bi bi-caret-right-fill"></i> Certificado de Confirmación (Original y Fotocopia)</p>
                                        <p>&nbsp;&nbsp;&nbsp;<i class="bi bi-caret-right-fill"></i> Certificado de Matrimonio Civil (Original y Fotocopia)</p>
                                        <p>&nbsp;&nbsp;&nbsp;<i class="bi bi-caret-right-fill"></i> Carnet de Identidad (Original y Fotocopia)</p>
                                        <p>&nbsp;&nbsp;&nbsp;<i class="bi bi-caret-right-fill"></i> Certificado de Cursillos Pre-Matrimoniales (Original y Fotocopia)</p>

                                        <h6 class="h6"><i class="bi bi-arrow-return-right"></i> los Padrinos deben presentar:</h6>
                                        <p>&nbsp;&nbsp;&nbsp;<i class="bi bi-caret-right"></i> Certificado de Matrimonio Religioso Católico (Original y Fotocopia)</p>
                                        <p>&nbsp;&nbsp;&nbsp;<i class="bi bi-caret-right"></i> Carnet de Identidad (Original y Fotocopia).</p>

                                        <h6 class="h6"><i class="bi bi-arrow-return-right"></i> Dos Testigos:</h6>
                                        <p>&nbsp;&nbsp;&nbsp;<i class="bi bi-caret-right"></i> Uno para el Novio y otro para la Novia, que ayan conocido por lo menos 5 Años y que no sean Familiares.</p>
                                        <p>&nbsp;&nbsp;&nbsp;<i class="bi bi-caret-right"></i> Carnet de Identidad (Original y Fotocopia).</p>


                                        <p><strong>NO</strong> pueden ser ser padrinos los que viven en concubinato, casados por lo civil y divorciados.</p>
                                    </div>
                                </div>

                                <div class="col-lg-12">
                                    <div class="ms-2">
                                        <h6 class="h6"><i class="bi bi-arrow-return-right"></i> Detalles de Inscripción:</h6>
                                        <p>&nbsp;&nbsp;&nbsp;<i class="bi bi-calendar-check"></i> RESERVAR 6 MESES ANTES DE LA BODA.</p>
                                        <p>&nbsp;&nbsp;&nbsp;<i class="bi bi-calendar-week"></i> PRESENTAR 3 MESES ANTES TODOS LOS DOCUMENTOS.</p>
                                        <p>&nbsp;&nbsp;&nbsp;<i class="bi bi-file-earmark-richtext"></i> ENTREGA DE DOCUMENTOS EN FOLDER AMARILLO.</p>
                                        <p>&nbsp;&nbsp;&nbsp;<i class="bi bi-clock"></i> DIAS MARTES DE 8:30 A 10:30 A.M. DEBEN ESTAR LOS NOVIOS Y TESTIGOS PRESENTES.</p>
                                    </div>
                                </div>

                            </section>

                        </div>
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



    <footer class="pt-1 bg-mode">
        <hr class="mb-0 mt-1">
        <div class="bg- light py-3">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <p class="text-center mb-0">©2026 <a class="text-body" target="_blank"
                                href="https://www.facebook.com/ParroquiaSanPedrodeSacaba/">Parroquia San Pedro </a>de
                            Sacaba.</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var loginForm = document.getElementById("login-form");
            var forgotPasswordForm = document.getElementById("forgot-password-form");
            var forgotPasswordLink = document.getElementById("forgot-password-link");
            var backToLoginLink = document.getElementById("back-to-login-link");
            var forgotPasswordBlock = document.getElementById("forgot-password-block");

            forgotPasswordLink.addEventListener("click", function(e) {
                e.preventDefault();
                loginForm.style.display = "none";
                Menu.style.display = "None"
                Menu1.style.display = "None"
                forgotPasswordBlock.style.display = "block";
            });

            backToLoginLink.addEventListener("click", function(e) {
                e.preventDefault();
                loginForm.style.display = "block";
                Menu.style.display = "block"
                Menu1.style.display = "block"
                forgotPasswordBlock.style.display = "none";
            });
        });


        const showVideoButton = document.getElementById('showVideoButton');
        const videoContainer = document.getElementById('videoContainer');
        const closeVideoButton = document.getElementById('closeVideoButton');
        const videoPlayer = document.getElementById('videoPlayer');

        showVideoButton.addEventListener('click', function() {
            videoContainer.style.display = 'block';
            showVideoButton.style.display = 'none';
            videoPlayer.play();
        });

        closeVideoButton.addEventListener('click', function() {
            videoContainer.style.display = 'none';
            showVideoButton.style.display = 'block';
            videoPlayer.pause();
        });


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
</body>

</html>