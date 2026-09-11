<?php
session_start();

$diassemana = array("Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado");
$meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");


require_once '../Controlador/controladorSacramentoFecha.php';

$controller = new ControladorSacramento();
$sacramentos = $controller->Confirmacion();
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

        .event {
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #f9f9f9;
        }

        .event h2 {
            margin-top: 0;
        }

        .countdown {
            font-weight: bold;
            color: #d9534f;
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
                                <li> <a class="dropdown-item active" href="srcSacramentoConfirmacion.php">Confirmación</a></li>
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

                                                    <img class="rounded-circle" src="../assets/images/post/Sacramentos/confirmacion.jpg" alt="" style="width: 350px; height: 300px;">

                                                </div>
                                                <div class="col-md-8 col-lg-8 ">
                                                    <div class="ms-4">
                                                        <h2 class="h2">¿Qué es la Confirmación?</h2>
                                                        <p>El sacramento de la Confirmación es uno de los tres sacramentos de iniciación cristiana. La misma palabra, Confirmación que significa afirmar o consolidar, nos dice mucho.</p>
                                                        <p>En este sacramento se fortalece y se completa la obra del Bautismo. Por este sacramento, el bautizado se fortalece con el don del Espíritu Santo. Se logra un arraigo más profundo a la filiación divina, se une más íntimamente con la Iglesia, fortaleciéndose para ser testigo de Jesucristo, de palabra y obra. Por él es capaz de defender su fe y de transmitirla. A partir de la Confirmación nos convertimos en cristianos maduros y podremos llevar una vida cristiana más perfecta, más activa. Es el sacramento de la madurez cristiana y que nos hace capaces de ser testigos de Cristo <strong>“Recibe por esta señal de la cruz el don del Espíritu Santo”.</strong> (Catec. no. 1300)
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <br>
                                <div class="row justify-content-center">
                                    <div class="col-lg-12 text-center mb-2">
                                        <h2 class="h2">Requisitos para recibir la Confirmación</h2>
                                        <p>Todo lo que necesitas para confirmarte en la fé.</p>
                                    </div>
                                </div>

                                <div class="col-lg-12">
                                    <div class="ms-2">
                                        <h6 class="h6"><i class="bi bi-arrow-return-right"></i> De los Jovenes y Señoritas:</h6>
                                        <p>&nbsp;&nbsp;&nbsp;<i class="bi bi-caret-right"></i> Certificado de Nacimineto.</p>
                                        <p>&nbsp;&nbsp;&nbsp;<i class="bi bi-caret-right"></i> Certificado de Bautizo.</p>
                                        <h6 class="h6"><i class="bi bi-arrow-return-right"></i> De los Padrinos: (Deben ser católicos)</h6>
                                        <p>&nbsp;&nbsp;&nbsp;<i class="bi bi-caret-right"></i> Ser casados por lo religioso, presentar certificado de Matrimonio Religioso Católico y Carnet de Identidad (Original y Fotocopia).</p>
                                        <p>&nbsp;&nbsp;&nbsp;<i class="bi bi-caret-right"></i> Si son solteros(as), ser mayor de edad, Presentar certificado de Confirmación y Carnet de Identidad (Original y Fotocopia).</p>
                                        <p><strong>NO</strong> pueden ser ser padrinos los que viven en concubinato, casados por lo civil, divorciados y los que no hicieron el sacramento de Confirmación.</p>
                                    </div>
                                </div>


                                <div class="row justify-content-center">
                                    <div class="col-lg-12 text-center mb-2">
                                        <h4 class="h4">Cuando se realiza la Confirmación</h4>
                                    </div>
                                </div>

                                <div class="col-lg-12">
                                    <div class="ms-2">
                                        <div class="col-lg-12">
                                            <?php if (count($sacramentos) > 0): ?>
                                                <?php foreach ($sacramentos as $row): ?>
                                                    <?php
                                                    $fechaIni = strtotime($row['Fecha_Ini']);
                                                    $fechaFin = strtotime($row['Fecha_Fin']);
                                                    
                                                    $diaSemanaIni = $diassemana[date('w', $fechaIni)];
                                                    $diaIni = date('d', $fechaIni);
                                                    $mesIni = $meses[date('n', $fechaIni) - 1];

                                                    $diaSemanaFin = $diassemana[date('w', $fechaFin)];
                                                    $diaFin = date('d', $fechaFin);
                                                    $mesFin = $meses[date('n', $fechaFin) - 1];
                                                    ?>

                                                    <?php if ($row['ActividadSac'] == 'Inscripción'): ?>
                                                        <h6 class="h6"><i class="bi bi-arrow-return-right"></i> <i class="bi bi-backpack-fill"></i> Fecha de <strong>inscripción a Confirmación:</strong></h6>
                                                        <p>&nbsp;&nbsp;&nbsp;<i class="bi bi-align-end"></i> <strong>Fecha: </strong> <?php echo "$diaSemanaIni, $diaIni de $mesIni"; ?> <strong> - a - </strong><?php echo "$diaSemanaFin, $diaFin de $mesFin"; ?></p>
                                                        <p>&nbsp;&nbsp;&nbsp;<i class="bi bi-clock"></i> Hora: <?php echo htmlspecialchars(date('H:i', $fechaIni)); ?> a <?php echo htmlspecialchars(date('H:i', $fechaFin)); ?></p>
                                                        <p id="inscripcion<?php echo $row['idSacramento']; ?>" class="text-center"></p>

                                                    <?php elseif ($row['ActividadSac'] == 'Confirmación'): ?>
                                                        <h6 class="h6"><i class="bi bi-arrow-return-right"></i> <i class="bi bi-backpack-fill"></i> Fecha de <strong>celebración de Confirmación:</strong></h6>
                                                        <p>&nbsp;&nbsp;&nbsp;<i class="bi bi-align-end"></i> <strong>Fecha: </strong> <?php echo "$diaSemanaIni, $diaIni de $mesIni"; ?> <strong> - a - </strong><?php echo "$diaSemanaFin, $diaFin de $mesFin"; ?></p>
                                                        <p>&nbsp;&nbsp;&nbsp;<i class="bi bi-clock"></i> Hora: <?php echo htmlspecialchars(date('H:i', $fechaIni)); ?> a <?php echo htmlspecialchars(date('H:i', $fechaFin)); ?></p>
                                                        <p id="confirmacion<?php echo $row['idSacramento']; ?>" class="text-center"></p>

                                                    <?php endif; ?>
                                                <?php endforeach; ?>

                                                <script>
                                                    function actualizarEstado(fechaInicio, fechaFin, elementoId) {
                                                        const fechaIni = new Date(fechaInicio).getTime();
                                                        const fechaFinal = new Date(fechaFin).getTime();
                                                        const ahora = new Date().getTime();

                                                        if (ahora < fechaIni) {
                                                            document.getElementById(elementoId).innerHTML = "Aún no está vigente.";
                                                        } else if (ahora > fechaFinal) {
                                                            document.getElementById(elementoId).innerHTML = "La actividad ha concluido.";
                                                        } else {
    
                                                            actualizarCuentaRegresiva(fechaFinal, elementoId);
                                                        }
                                                    }

                                                    function actualizarCuentaRegresiva(fechaObjetivo, elementoId) {
                                                        const fechaFinal = new Date(fechaObjetivo).getTime();

                                                        const intervalo = setInterval(function() {
                                                            const ahora = new Date().getTime();
                                                            const distancia = fechaFinal - ahora;

                                                            const dias = Math.floor(distancia / (1000 * 60 * 60 * 24));
                                                            const horas = Math.floor((distancia % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                                            const minutos = Math.floor((distancia % (1000 * 60 * 60)) / (1000 * 60));
                                                            const segundos = Math.floor((distancia % (1000 * 60)) / 1000);

                                                            document.getElementById(elementoId).innerHTML =
                                                                `${dias} días, ${horas} horas, ${minutos} minutos, ${segundos} segundos`;

                                                            if (distancia < 0) {
                                                                clearInterval(intervalo);
                                                                document.getElementById(elementoId).innerHTML = "¡Tiempo agotado!";
                                                            }
                                                        }, 1000);
                                                    }

                                                    <?php foreach ($sacramentos as $row): ?>
                                                        actualizarEstado(
                                                            "<?php echo $row['Fecha_Ini']; ?>",
                                                            "<?php echo $row['Fecha_Fin']; ?>",
                                                            "<?php echo $row['ActividadSac'] == 'Inscripción' ? 'inscripcion' . $row['idSacramento'] : 'confirmacion' . $row['idSacramento']; ?>"
                                                        );
                                                    <?php endforeach; ?>
                                                </script>

                                            <?php else: ?>
                                                <p>No hay datos disponibles.</p>
                                            <?php endif; ?>
                                        </div>
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