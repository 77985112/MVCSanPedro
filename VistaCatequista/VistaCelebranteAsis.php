<?php
require_once '../Controlador/controladorCelebranteAsis.php';
require_once '../Conexion/Conexion.php';

session_start();
if (!isset($_SESSION['CiPersona'])) {
	header("Location: ../logout.php");
	exit();
}
$Catequista = $_SESSION['CiPersona'];

$conexion = new Conexion();
$db = $conexion->getConnection();

$modelo = new AsistenciaModel($db);
$controlador = new AsistenciaController($modelo);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (isset($_POST['registrar'])) {
		$controlador->registrarAsistencia();
	} elseif (isset($_POST['modificar'])) {
		$controlador->modificarAsistencia();
	}
}

$idGrupo = $Catequista['IdGrupo'];
$celebrantes = $controlador->obtenerCelebrantes($idGrupo);

setlocale(LC_TIME, 'es_ES.UTF-8');

$sql_celebrantes = "
    SELECT p.CiPersona, CONCAT(p.Nombre, ' ', p.ApPaterno, ' ', p.ApMaterno) AS NombreCompleto
    FROM persona p
    INNER JOIN celebrante c ON p.CiPersona = c.CiCel
    INNER JOIN inscripcion i ON c.CiCel = i.CiCel
    WHERE i.IdGrupo = $idGrupo
    ORDER BY p.ApPaterno, p.ApMaterno, p.Nombre";
$result_celebrantes = $db->query($sql_celebrantes);

$celebrantesAll = [];
while ($row = $result_celebrantes->fetch_assoc()) {
	$celebrantesAll[$row['CiPersona']] = $row['NombreCompleto'];
}

$sql_fechas = "
    SELECT DISTINCT a.FechaAsisConf
    FROM asistenciacel a
    INNER JOIN inscripcion i ON a.IdInscripcion = i.IdInscripcion
    WHERE i.IdGrupo = $idGrupo AND a.DetalleAsis = 'Catequesis'
    ORDER BY a.FechaAsisConf";
$result_fechas = $db->query($sql_fechas);

$fechas = [];
while ($row = $result_fechas->fetch_assoc()) {
	$fechas[] = $row['FechaAsisConf'];
}

$asistenciasAll = [];
foreach ($celebrantesAll as $ci => $nombre) {
	$asistenciasAll[$ci] = [];
	foreach ($fechas as $fecha) {
		$sql_asistencia = "
            SELECT a.TipoAsis
            FROM asistenciacel a
            INNER JOIN inscripcion i ON a.IdInscripcion = i.IdInscripcion
            WHERE i.CiCel = '$ci' AND a.FechaAsisConf = '$fecha' AND a.DetalleAsis = 'Catequesis'";
		$result_asistencia = $db->query($sql_asistencia);

		if ($result_asistencia->num_rows > 0) {
			$tipoAsis = $result_asistencia->fetch_assoc()['TipoAsis'];
			$asistenciasAll[$ci][$fecha] = ($tipoAsis === 'Presente') ? 'P' : 'F';
		} else {
			$asistenciasAll[$ci][$fecha] = 'F';
		}
	}
}

$dias_es = ["Dom", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb"];
$meses_es = ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];



$conexion = new Conexion();
$conn = $conexion->getConnection();

function getActiveNotifications($conn)
{
    $currentDateTime = date('Y-m-d H:i:s');

    $sql = "SELECT n.idNotificacion, n.TituloNot, n.DetalleNot, n.FechaNotFin, CONCAT(p.Nombre, ' ', p.ApPaterno) AS datoscat 
            FROM notificaciones n 
            INNER JOIN catequista c ON n.CiNotificador = c.CiCat 
            INNER JOIN persona p ON c.CiCat = p.CiPersona 
            WHERE n.EstadoNot = 'Activo' AND n.FechaNotFin > ?
            ORDER BY n.FechaNotFin ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $currentDateTime);
    $stmt->execute();
    $result = $stmt->get_result();

    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }

    return $notifications;
}

$activeNotifications = getActiveNotifications($conn);
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
	<style>
		table {
			border-collapse: collapse;
			margin: 10px 0;
		}

		th,
		td {
			border: 1px solid black;
			padding: 3px;
			text-align: center;
		}

		th {
			background-color: #f2f2f2;
		}

		.name-column {
			text-align: left;
			white-space: nowrap;
		}

		.present {
			position: relative;
		}

		.present::after {
			content: "•";
			position: absolute;
			top: 50%;
			left: 50%;
			transform: translate(-50%, -50%);
			font-size: 20px;
		}

		.vertical-text {
			writing-mode: vertical-rl;
			text-orientation: mixed;
			white-space: nowrap;
			transform: rotate(180deg);
			padding: 5px 0;
		}

		.absent {
			color: black;
			font-weight: bold;
		}
	</style>
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

					<li class="nav-item dropdown ms-2">
                        <a class="nav-link bg-light icon-md btn btn-light p-0" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                            <span class="badge-notif animation-blink"></span>
                            <i class="bi bi-bell-fill fs-6"> </i>
                        </a>
                        <div class="dropdown-menu dropdown-animation dropdown-menu-end dropdown-menu-size-md p-0 shadow-lg border-0" aria-labelledby="notifDropdown">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="m-0">Notificaciones <span class="badge bg-danger ms-2"><?php echo count($activeNotifications); ?> activas</span></h6>
                                </div>
                                <div class="card-body p-0">
                                    <ul class="list-group list-group-flush">
                                        <?php foreach ($activeNotifications as $notification): ?>
                                            <li class="list-group-item">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h6 class="mb-0"><?php echo htmlspecialchars($notification['TituloNot']); ?></h6>
                                                    <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($notification['FechaNotFin'])); ?></small>
                                                </div>
                                                <p class="mb-0 small"><?php echo htmlspecialchars($notification['DetalleNot']); ?></p>
                                                <small class="text-muted">Notificado por: <?php echo htmlspecialchars($notification['datoscat']); ?></small>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <?php if (empty($activeNotifications)): ?>
                                    <div class="card-body">
                                        <p class="text-center mb-0">No hay notificaciones activas.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
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
												<a class="nav-link active" href="VistaCelebranteAsis.php"> <img class="me-2 h-20px fa-fw" src="../assets/images/icon/ListaCelebrante.png" alt=""><span>Asistencia a Catquesis </span></a>
											</li>
											<?php if ($Catequista['PoderCat'] == "Habilitado") { ?>
												<li class="nav-item">
													<a class="nav-link" href="VistaDocCel.php"> <img class="me-2 h-20px fa-fw" src="../assets/images/icon/Documentos.png" alt=""><span>Registros</span></a>
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

					<div class="card h-100">


						<div class="container">
							<div class="py-3">
								<div class="row position-relative">
									<div class="col-lg-10 mx-auto">
										<div class="text-center">
											<h1>Toma de Asistencia (Catequesis)</h1>
											<p>Selecciona una fecha para relizar la toma de asistencia a catequisandos</p>
										</div>
										<div class="mx-auto bg-mode shadow rounded p-4 mt-5 col-lg-6">
											<form method="POST" class="row align-items-end g-4">
												<div class="col-sm-6 col-lg-6">
													<label for="fecha" class="form-label">Fecha Actual:</label>
													<input type="date" name="fecha" id="fecha" class="form-control">
												</div>
												<div class="col-sm-6 col-lg-6">
													<button type="submit" class="btn btn-primary-soft w-100" data-bs-toggle="tooltip" data-bs-placement="top" title="Buscar"><i class="bi bi-search"></i></button>
												</div>
											</form>
											<br>
											<?php if (!empty($fechas)) {?>
											<form action="asistencia_pdf.php" method="post">
												<input type="hidden" value="<?php echo $idGrupo; ?>" name="idGrupo">
												<input type="hidden" value="<?php echo $Catequista['NombreGrupo'];; ?>" name="NombreGr">
												<button type="submit" class="btn btn-primary-soft w-100" target="_blank">
													<i class="bi bi-file-earmark-spreadsheet"></i> Generar PDF de las Asistencias
												</button>
											</form>
											<?php }?>
										</div>
									</div>
								</div>
							</div>

							<div class="p-3">
								<?php if (isset($_POST['fecha'])): ?>
									<?php $asistencias = $controlador->obtenerAsistenciaPorFecha($_POST['fecha']); ?>
									<?php if (empty($asistencias)):  $FechaReg = $_POST['fecha'];?>
										<h2>Registrar Asistencia</h2>
										<form method="post">
											<table class="table">
												<thead class="table-dark">
													<tr>
														<th>Ci Catequisando</th>
														<th>Nombre Completo</th>
														<th>Descripción Item</th>
														<th>Asistencia</th>
													</tr>
												</thead>
												<tbody>
													<?php foreach ($celebrantes as $celebrante): ?>
														<tr>
															<td><?= $celebrante['CiCel'] ?></td>
															<td><?= $celebrante['NombreCompleto'] ?></td>
															<td><?= $celebrante['DescripItem'] ?></td>

															<td>
																<input type="checkbox" name="TipoAsis[<?= $celebrante['IdInscripcion'] ?>]" value="Presente" onchange="this.previousElementSibling.value = this.checked ? 'Presente' : 'Falta';"> Presente
																<input type="hidden" name="IdInscripcion[]" value="<?= $celebrante['IdInscripcion'] ?>">
															</td>
														</tr>
													<?php endforeach; ?>
												</tbody>

											</table>
											<input type="hidden" value="<?php echo $FechaReg; ?>" name="FechaReg">
											<div class="col-sm-12 col-lg-12 d-flex">
												<div class="col-sm-6 col-lg-6 me-2">
													<label class="form-label" class="form-control">Slecciones un detalle:</label>
													<select class="form-control" name="DetalleAsis" data-search-enabled="true">
														<option value="Catequesis">Catequesis</option>
														<option value="Actividad">Actividad</option>
													</select>
												</div>
												<div class="col-sm-6 col-lg-6 me-2">
													<button type="submit" name="registrar" class="btn btn-success-soft w-100">Registrar Asistencias</button>
												</div>
											</div>
										</form>
									<?php else: ?>
										<h2>Modificar Asistencia</h2>
										<form method="post">
											<table class="table">
												<thead class="table-dark">
													<tr>
														<th>Ci Catequisando</th>
														<th>Nombre Completo</th>
														<th>Descripción Item</th>
														<th>Asistencia</th>
													</tr>
												</thead>
												<tbody>
													<?php foreach ($asistencias as $asistencia): ?>
														<tr>
															<td><?= $asistencia['CiCel'] ?></td>
															<td><?= $asistencia['NombreCompleto'] ?></td>
															<td><?= $asistencia['DescripItem'] ?></td>
															<td>
																<input type="hidden" name="TipoAsis[<?= $asistencia['CodAsis'] ?>]" value="Falta">
																<input type="checkbox" name="TipoAsis[<?= $asistencia['CodAsis'] ?>]" value="Presente" <?= strpos($asistencia['TipoAsis'], 'Presente') !== false ? 'checked' : '' ?> onchange="this.previousElementSibling.value = this.checked ? 'Presente' : 'Falta';"> Presente
																<input type="hidden" name="CodAsis[]" value="<?= $asistencia['CodAsis'] ?>">
															</td>
														</tr>
													<?php endforeach; ?>
												</tbody>
											</table>
											<button type="submit" name="modificar" class="btn btn-primary-soft w-100">Modificar Asistencias</button>
										</form>
									<?php endif; ?>
								<?php endif; ?>
							</div>

							<hr>

							<table>
								<thead>
									<tr>
										<th class="name-column">Nº / Nombres</th>
										<?php
										foreach ($fechas as $fecha) {
											$fecha_obj = new DateTime($fecha);
											$dia = $dias_es[$fecha_obj->format('w')];
											$mes = $meses_es[$fecha_obj->format('n') - 1];
											$dia_numero = $fecha_obj->format('d');
											echo "<th><div class='vertical-text'>$dia $dia_numero $mes</div></th>";
										}
										?>
									</tr>
								</thead>
								<tbody>
									<?php
									$counter = 1;
									foreach ($celebrantesAll as $ci => $nombre) {
										echo "<tr>";
										echo "<td class='name-column'>" . $counter . ".- " . $nombre . "</td>";

										foreach ($fechas as $fecha) {
											$tipoAsis = $asistenciasAll[$ci][$fecha];
											if ($tipoAsis === 'P') {
												echo "<td class='present'></td>";
											} else {
												echo "<td class='absent'>$tipoAsis</td>";
											}
										}
										echo "</tr>";
										$counter++;
									}
									?>
								</tbody>
							</table>
						</div>
					</div>
				</div>

			</div>
		</div>
	</main>
	<script>
		function setCurrentDate() {
			var fechaInput = document.getElementById('fecha');

			var today = new Date();
			var year = today.getFullYear();
			var month = ('0' + (today.getMonth() + 1)).slice(-2);
			var day = ('0' + today.getDate()).slice(-2);

			var todayDate = year + '-' + month + '-' + day;
			fechaInput.value = todayDate;
		}
		window.onload = setCurrentDate;

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
	<script src="../assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
	<script src="../assets/js/functions.js"></script>
	<script src="../assets/js/tema.js"></script>
</body>

</html>