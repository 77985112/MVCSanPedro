<?php
require_once '../Controlador/controladorPersonal.php';
require_once __DIR__ . '/../Controlador/controladorAcceso.php';

session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: ../logout.php");
  exit();
}
$usuario = $_SESSION['usuario'];

if ($usuario['DescripCar'] != 'Párroco') {
  header("Location: VistaPersonal.php");
  exit();
}

if (empty($_SESSION['csrf_acceso'])) {
  $_SESSION['csrf_acceso'] = bin2hex(random_bytes(32));
}

$controller = new ControladorPersonal();

$mensaje = '';
$mensaje1 = '';
$personal = null;
$listaPersonal = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registrar'])) {
  $_POST['UsuarioPer'] = trim($_POST['UsuarioPer'] ?? '');
  $_POST['ClavePer'] = trim($_POST['ClavePer'] ?? '');
  if ($controller->registrarNuevoPersonal($_POST)) {
    $registrado = $controller->buscarPersonalPorCi($_POST['CiPersonal']);
    $_SESSION['registroPersonal'] = [
      'Nombre' => $_POST['Nombre'],
      'UsuarioPer' => $_POST['UsuarioPer'],
      'ClavePer' => $_POST['ClavePer'],
      'DescripCar' => $registrado['DescripCar'] ?? ''
    ];
    $mensaje = "<strong>Personal registrado con éxito.</strong> Nombre: " . htmlspecialchars($_POST['Nombre'], ENT_QUOTES, 'UTF-8')
      . ", Usuario: " . htmlspecialchars($_POST['UsuarioPer'], ENT_QUOTES, 'UTF-8')
      . ", Clave: " . htmlspecialchars($_POST['ClavePer'], ENT_QUOTES, 'UTF-8');
  } else {
    $mensaje = "Error al registrar el personal.";
  }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['modificar'])) {
  if ($controller->modificarPersonalPorCi($_POST)) {
    header("Location: vistaGesPersonal.php?Cper=" . urlencode($_POST['CiPersonal']) . "&actualizado=1");
    exit;
  }
  $mensaje1 = "No se pudo actualizar. Verifica que el usuario no esté en uso y que los datos sean válidos.";
  $personal = $controller->buscarPersonalPorCi($_POST['CiPersonal']);
}

if (isset($_GET['Cper'])) {
  $texto = trim($_GET['Cper']);
  if ($texto == $usuario['CiPersonal']) {
    $mensaje1 = "No puedes buscar tu propio registro.";
  } elseif ($texto !== '') {
    // Primero búsqueda exacta por CI; si no existe, por nombre o apellidos.
    $personal = $controller->buscarPersonalPorCi($texto);
    if (!$personal) {
      $resultados = $controller->buscarPersonalPorTexto($texto);
      $resultados = array_values(array_filter($resultados, function ($fila) use ($usuario) {
        return $fila['CiPersonal'] != $usuario['CiPersonal'];
      }));
      if (count($resultados) == 1) {
        $personal = $resultados[0];
      } elseif (count($resultados) > 1) {
        $listaPersonal = $resultados;
      }
    }
  }
}

// Listado completo del personal (se muestra mientras no haya búsqueda ni registro abierto).
$porPagina = isset($_GET['porPagina']) ? (int) $_GET['porPagina'] : 15;
if (!in_array($porPagina, [15, 25, 50], true)) {
  $porPagina = 15;
}
$pagina = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
if ($pagina < 1) {
  $pagina = 1;
}
$totalPersonal = 0;
$totalPaginas = 1;
$personalTabla = [];
if (!isset($_GET['Cper'])) {
  $totalPersonal = $controller->contarPersonal();
  $totalPaginas = max(1, (int) ceil($totalPersonal / $porPagina));
  if ($pagina > $totalPaginas) {
    $pagina = $totalPaginas;
  }
  $personalTabla = $controller->listarPersonal(($pagina - 1) * $porPagina, $porPagina);
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
  <link rel="stylesheet" type="text/css" href="../assets/css/style.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/style.css'); ?>">
  <link rel="stylesheet" type="text/css" href="../assets/css/logo-cabecera.css?v=<?= filemtime(__DIR__ . '/../assets/css/logo-cabecera.css') ?>">
  <style>
    /* Selector de cargo del modal de registro: resaltado para notar que es un desplegable. */
    .modal-body .form-select-cargo {
      background-color: #d4a017;
      background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23ffffff'%3E%3Cpath d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 0.75rem center;
      background-size: 12px 12px;
      border: 2px solid #000000;
      border-radius: 8px;
      color: #ffffff;
      font-weight: 600;
      padding-right: 2.25rem;
      cursor: pointer;
    }
    .modal-body .form-select-cargo:focus {
      border-color: #000000;
      box-shadow: 0 0 0 0.2rem rgba(0, 0, 0, 0.2);
    }
    .modal-body .form-select-cargo option {
      background-color: #ffffff;
      color: #000000;
      font-weight: 500;
    }

    /* Tabla del personal registrado. */
    .tabla-personal {
      font-size: 0.78rem;
      width: 100%;
      min-width: 0;
    }
    .tabla-personal thead th {
      background-color: #d4a017;
      color: #ffffff;
      font-size: 0.68rem;
      text-transform: uppercase;
      letter-spacing: 0.02em;
      font-weight: 700;
      border: 1px solid #000000;
      white-space: nowrap;
      padding: 0.4rem 0.3rem;
    }
    .tabla-personal tbody td {
      vertical-align: middle;
      white-space: nowrap;
      padding: 0.4rem 0.3rem;
    }
    .tabla-personal .badge-cargo {
      font-size: 0.65rem;
      padding: 0.3em 0.45em;
    }
    .tabla-personal tbody tr:hover {
      background: #fcfaf3;
    }
    .tabla-personal .ci {
      font-variant-numeric: tabular-nums;
      color: #495057;
    }
    .tabla-personal .dato-vacio {
      color: #b3b8bf;
    }
    .tabla-personal .correo-celda {
      display: inline-block;
      max-width: 180px;
      overflow: hidden;
      text-overflow: ellipsis;
      vertical-align: bottom;
    }

    /* El ancho se define en assets/css/style.css para todas las vistas.
       Aquí solo se ajusta el contenido en tablet y celular. */
    @media (max-width: 991.98px) {
      .personal-dashboard-container {
        width: 100%;
      }
      .personal-dashboard-row > .personal-dashboard-content {
        width: 100%;
      }
      .tabla-personal .correo-celda {
        max-width: 140px;
      }
    }
    .badge-cargo {
      font-size: 0.7rem;
      font-weight: 600;
      padding: 0.35em 0.6em;
      border-radius: 6px;
    }
    .cargo-parroco {
      background: rgba(212, 160, 23, 0.18);
      color: #7d6008;
    }
    .cargo-sacerdote {
      background: rgba(13, 110, 253, 0.12);
      color: #0a58ca;
    }
    .cargo-secretario {
      background: rgba(25, 135, 84, 0.13);
      color: #146c43;
    }
    .cargo-sacristan {
      background: rgba(108, 117, 125, 0.15);
      color: #495057;
    }
    .cargo-diacono {
      background: rgba(111, 66, 193, 0.15);
      color: #59359a;
    }
    .btn-ver-tabla {
      border: 1px solid #dee2e6;
      background: #ffffff;
      color: #2d3a4b;
    }
    .btn-ver-tabla:hover {
      background: #d4a017;
      border-color: #d4a017;
      color: #ffffff;
    }

    /* Aviso visual al actualizar un registro. */
    @keyframes parpadeoActualizado {
      0% {
        box-shadow: inset 0 0 0 3px #198754;
        background-color: rgba(25, 135, 84, 0.12);
      }
      100% {
        box-shadow: inset 0 0 0 3px rgba(25, 135, 84, 0);
        background-color: transparent;
      }
    }
    .form-actualizado {
      border-radius: 0.5rem;
      animation: parpadeoActualizado 1.1s ease-in-out 2;
    }

    /* Botón de la llave en la tabla. */
    .btn-acceso {
      border: 1px solid #d4a017;
      background: #ffffff;
      color: #a37c0a;
    }
    .btn-acceso:hover {
      background: #d4a017;
      border-color: #d4a017;
      color: #ffffff;
    }

    /* Panel de permisos de acceso. */
    .modal-acceso .modal-content {
      border: 0;
      border-radius: 14px;
      overflow: hidden;
    }
    .acceso-cabecera {
      padding: 18px 22px 0;
    }
    .acceso-logo {
      display: block;
      height: 58px;
      width: auto;
      margin: 0 auto;
    }
    .acceso-linea {
      height: 3px;
      margin: 14px -22px 0;
      background: linear-gradient(90deg, rgba(212, 160, 23, 0), #d4a017 18%, #d4a017 82%, rgba(212, 160, 23, 0));
    }
    .acceso-titulo {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 12px;
      padding-top: 14px;
    }
    .acceso-titulo h5 {
      margin: 0;
      font-weight: 700;
      color: #22303f;
    }
    .acceso-titulo p {
      margin: 3px 0 0;
      font-size: 0.82rem;
      color: #8a929c;
    }
    .btn-x-acceso {
      width: 38px;
      height: 38px;
      flex: 0 0 38px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border: 2px solid #d6293e;
      border-radius: 8px;
      background: #ffffff;
      color: #d6293e;
      padding: 0;
    }
    .btn-x-acceso:hover {
      background: #d6293e;
      color: #ffffff;
    }
    .acceso-persona {
      display: flex;
      align-items: center;
      gap: 14px;
      margin: 16px 22px 0;
      padding: 13px 15px;
      background: #fffaf0;
      border: 1px solid #f2e3b6;
      border-left: 4px solid #d4a017;
      border-radius: 10px;
    }
    .acceso-avatar {
      width: 46px;
      height: 46px;
      border-radius: 50%;
      flex: 0 0 46px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #d4a017;
      color: #ffffff;
      font-weight: 700;
      font-size: 1rem;
    }
    .acceso-nombre {
      font-weight: 700;
      color: #22303f;
      line-height: 1.2;
    }
    .acceso-meta {
      font-size: 0.78rem;
      color: #7d8590;
      margin-top: 3px;
    }
    .acceso-cargo {
      margin-left: auto;
      font-size: 0.72rem;
      font-weight: 700;
      padding: 0.35em 0.65em;
      border-radius: 6px;
      background: rgba(212, 160, 23, 0.16);
      color: #7d6008;
      white-space: nowrap;
    }
    .acceso-seccion {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin: 16px 22px 8px;
    }
    .acceso-seccion .titulo {
      font-size: 0.7rem;
      font-weight: 700;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      color: #9aa1a9;
    }
    .acceso-modulos {
      padding: 0 22px;
    }
    .modulo-acceso {
      display: flex;
      align-items: center;
      gap: 0.65rem;
      height: 100%;
      padding: 0.65rem 0.8rem;
      background: #ffffff;
      border: 1px solid #e3e7ee;
      border-radius: 10px;
      cursor: pointer;
    }
    .modulo-acceso:hover {
      border-color: #d4a017;
      background: #fffdf5;
    }
    .modulo-acceso.activo {
      border-color: #d4a017;
      background: #fff8e1;
      box-shadow: inset 0 0 0 1px #d4a017;
    }
    .modulo-acceso .icono {
      width: 32px;
      height: 32px;
      flex: 0 0 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 8px;
      background: #f4f5f7;
      color: #495057;
      font-size: 0.95rem;
    }
    .modulo-acceso.activo .icono {
      background: rgba(212, 160, 23, 0.18);
      color: #8a6a10;
    }
    .modulo-acceso .nombre {
      display: block;
      font-size: 0.86rem;
      font-weight: 600;
      color: #2d3a4b;
      line-height: 1.2;
    }
    .modulo-acceso .detalle {
      display: block;
      font-size: 0.71rem;
      color: #8a929c;
    }
    .modulo-acceso .form-check-input {
      margin-left: auto;
      flex: 0 0 auto;
      width: 1.1rem;
      height: 1.1rem;
    }
    .modulo-acceso .form-check-input:checked {
      background-color: #d4a017;
      border-color: #d4a017;
    }
    .modulo-acceso.bloqueado {
      background: #f5f6f7;
      border-style: dashed;
      cursor: not-allowed;
    }
    .modulo-acceso.bloqueado .nombre {
      color: #6c757d;
    }
    .modulo-acceso.bloqueado .icono {
      background: #e9ecef;
      color: #868e96;
    }
    .etiqueta-parroco,
    .etiqueta-no {
      margin-left: auto;
      font-size: 0.64rem;
      font-weight: 700;
      padding: 0.22em 0.45em;
      border-radius: 5px;
      white-space: nowrap;
    }
    .etiqueta-parroco {
      background: rgba(212, 160, 23, 0.16);
      color: #7d6008;
    }
    .etiqueta-no {
      background: #e9ecef;
      color: #6c757d;
    }
    .alerta-acceso-ok {
      display: none;
      align-items: center;
      gap: 8px;
      margin: 14px 22px 0;
      padding: 10px 12px;
      background: #fff8e1;
      border: 1px solid #e6c66a;
      border-left: 4px solid #d4a017;
      border-radius: 8px;
      color: #6b5a1e;
      font-size: 0.85rem;
      font-weight: 600;
    }
    .alerta-acceso-ok.visible {
      display: flex;
    }
    .alerta-acceso-error {
      display: none;
      margin: 14px 22px 0;
      padding: 10px 12px;
      background: #fdecee;
      border: 1px solid #f5c2c7;
      border-left: 4px solid #d6293e;
      border-radius: 8px;
      color: #a32637;
      font-size: 0.82rem;
    }
    .alerta-acceso-error.visible {
      display: block;
    }
    .alerta-acceso-lectura {
      display: none;
      margin: 14px 22px 0;
      padding: 10px 12px;
      background: #f5f6f7;
      border: 1px solid #dee2e6;
      border-left: 4px solid #6c757d;
      border-radius: 8px;
      color: #495057;
      font-size: 0.82rem;
    }
    .alerta-acceso-lectura.visible {
      display: block;
    }
    @keyframes parpadeoAcceso {
      0% {
        box-shadow: 0 0 0 3px rgba(212, 160, 23, 0.55);
        background-color: rgba(212, 160, 23, 0.12);
      }
      100% {
        box-shadow: 0 0 0 3px rgba(212, 160, 23, 0);
        background-color: transparent;
      }
    }
    .acceso-guardado-flash {
      border-radius: 10px;
      animation: parpadeoAcceso 1s ease-in-out 2;
    }
    .acceso-pie {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      padding: 14px 22px;
      background: #fbfbfc;
      border-top: 1px solid #eef0f3;
    }
    .acceso-pie .nota {
      font-size: 0.76rem;
      color: #7d8590;
      max-width: 420px;
    }
    .btn-acceso-guardar {
      background: #d4a017;
      border: 1px solid #d4a017;
      color: #ffffff;
      font-weight: 600;
    }
    .btn-acceso-guardar:hover,
    .btn-acceso-guardar:focus {
      background: #b8890f;
      border-color: #b8890f;
      color: #ffffff;
    }
    .btn-acceso-guardar.guardado {
      background: #b8890f;
      border-color: #b8890f;
      color: #ffffff;
    }
    .acceso-pie .form-check-input:checked {
      background-color: #d4a017;
      border-color: #d4a017;
    }
  </style>
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
              <img class="avatar-img rounded border border-white border-3" src="../assets/images/avatar/Ninguno.png" alt="">
            </a>
            <ul class="dropdown-menu dropdown-animation dropdown-menu-end pt-3 small me-md-n3" aria-labelledby="profileDropdown">
              <li class="px-3">
                <div class="d-flex align-items-center position-relative">
                  <div class="avatar me-3">
                    <img class="avatar-img rounded-circle" src="../assets/images/avatar/Ninguno.png" alt="avatar">
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
  <header class="navbar-light fixed-top header-static bg-mode">
    <nav class="navbar navbar-expand-lg">
      <div class="container">
        <a class="navbar-brand" href="VistaPersonal.php">
          <img class="light-mode-item navbar-brand-item" src="../assets/images/logo.png?v=<?= filemtime(__DIR__ . '/../assets/images/logo.png') ?>" alt="logo">
          <img class="dark-mode-item navbar-brand-item" src="../assets/images/logo-light.png?v=<?= filemtime(__DIR__ . '/../assets/images/logo-light.png') ?>" alt="logo">
        </a>

        <ul class="nav flex-nowrap align-items-center ms-sm-3 list-unstyled">
          <li class="nav-item ms-2">
            <a class="nav-link bg-light icon-md btn btn-light p-0" href="#" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Configurar">
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
                <a class="dropdown-item" href="#"><i class="bi bi-gear fa-fw me-2"></i>Configuración</a>
              </li>
              <li>
                <a class="dropdown-item" href="#" target="_blank"> <i class="fa-fw bi bi-card-text me-2"></i>Reglamento</a>
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
      <div class="row g-4 personal-dashboard-row">
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
                      <small id="cargo"><?php echo $usuario['DescripCar']; ?></small>
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

        <?php $mostrarListado = (!$personal && empty($listaPersonal) && !isset($_GET['Cper'])); ?>
        <div class="col-md-8 <?php echo $mostrarListado ? 'col-lg-9' : 'col-lg-6'; ?> vstack gap-4 personal-dashboard-content">

          <div class="card h-100">
            <div class="card-header d-sm-flex align-items-center text-center justify-content-sm-between border-0 pb-0">
              <h1 class="h4 card-title">Datos del Personal</h1>
              <form method="GET" class="mb-3 d-flex align-items-end">
                <div class="form-group mb-0 me-2">
                  <input type="text" class="form-control" name="Cper" placeholder="CI, nombre o apellidos" autocomplete="off" required>
                </div>
                <button type="submit" name="search" class="btn btn-primary-soft me-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Buscar">
                  <i class="bi bi-search"></i>
                </button>
                <button type="button" class="btn btn-primary-soft me-2" data-bs-toggle="modal" data-bs-target="#MostrarModal">
                  <i class="bi bi-plus-circle"></i>
                </button>
                <?php if ($personal || !empty($listaPersonal)): ?>
                  <a href="vistaGesPersonal.php" class="btn btn-danger-soft border" style="border-color: #d6293e; border-width: 2px;" data-bs-toggle="tooltip" data-bs-placement="top" title="Cerrar">
                    <i class="bi bi-x-lg"></i>
                  </a>
                <?php endif; ?>
              </form>
            </div>

            <div class="card-body">
              <?php if (isset($_GET['actualizado'])): ?>
                <div class="alert alert-success d-flex align-items-center" role="alert" id="alertaActualizado">
                  <i class="bi bi-check-circle-fill me-2"></i>
                  <strong>Datos actualizados correctamente.</strong>
                </div>
              <?php endif; ?>
              <?php if ($mensaje): ?>
                <div class="alert alert-success" role="alert">
                  <?php echo $mensaje; ?>
                  <a href="generar_ticket.php" target="_blank" class="btn btn-xs btn-success mt-2 mt-lg-0 ms-lg-4">Generar ticket</a>
                </div>
              <?php endif; ?>
              <?php if ($mensaje1): ?>
                <div class="alert alert-warning" role="alert">
                  <?php echo $mensaje1; ?>
                </div>
              <?php endif; ?>

              <?php if (!empty($listaPersonal)): ?>
                <p class="small text-body-secondary mb-2">Se encontraron varios registros. Selecciona uno:</p>
                <div class="list-group mb-3">
                  <?php foreach ($listaPersonal as $item): ?>
                    <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" href="vistaGesPersonal.php?Cper=<?php echo urlencode($item['CiPersonal']); ?>">
                      <span>
                        <strong><?php echo htmlspecialchars($item['Nombre'] . ' ' . $item['Paterno'] . ' ' . $item['Materno']); ?></strong><br>
                        <small class="text-body-secondary">CI: <?php echo htmlspecialchars($item['CiPersonal']); ?> · <?php echo htmlspecialchars($item['DescripCar']); ?></small>
                      </span>
                      <i class="bi bi-chevron-right"></i>
                    </a>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>

              <?php if ($personal): ?>
                <form method="POST" class="row g-3 <?php echo isset($_GET['actualizado']) ? 'form-actualizado' : ''; ?>" id="formEditarPersonal" autocomplete="off">
                  <input type="hidden" name="CiPersonal" value="<?php echo $personal['CiPersonal']; ?>">
                  <input type="hidden" name="PerfilPer" value="<?php echo $personal['PerfilPer']; ?>">
                  <input type="hidden" name="FondoPer" value="<?php echo $personal['FondoPer']; ?>">
                  <input type="hidden" name="FrasePer" value="<?php echo $personal['FrasePer']; ?>">

                  <div class="col-sm-6 col-lg-3 form-group">
                    <label class="form-label">Nombres</label>
                    <input type="text" class="form-control" name="Nombre" value="<?php echo $personal['Nombre']; ?>" autocomplete="off">
                  </div>
                  <div class="col-sm-6 col-lg-3 form-group">
                    <label class="form-label">Apellido Paterno</label>
                    <input type="text" class="form-control" name="Paterno" value="<?php echo $personal['Paterno']; ?>" autocomplete="off">
                  </div>
                  <div class="col-sm-6 col-lg-3 form-group">
                    <label class="form-label">Apellido Materno</label>
                    <input type="text" class="form-control" name="Materno" value="<?php echo $personal['Materno']; ?>" autocomplete="off">
                  </div>
                  <div class="col-sm-6 col-lg-3 form-group">
                    <label class="form-label">Numero de Contacto</label>
                    <input type="text" class="form-control" name="ContactoPer" value="<?php echo $personal['ContactoPer']; ?>" autocomplete="off">
                  </div>

                  <div class="col-sm-6 col-lg-3 form-group">
                    <label class="form-label">Correo Electrónico</label>
                    <input type="email" class="form-control" name="Correo" value="<?php echo $personal['Correo'] ?? ''; ?>" placeholder="Opcional" autocomplete="off">
                  </div>

                  <div class="col-sm-6 col-lg-3 form-group">
                    <label class="form-label">Fecha de Nacimiento</label>
                    <input type="date" class="form-control" name="FechaNac" value="<?php echo $personal['FechaNac'] ?? ''; ?>" autocomplete="off">
                  </div>

                  <div class="col-sm-6 col-lg-3 form-group" id="grupoFechaOrdenacionEdit">
                    <label class="form-label">Fecha de Ordenación</label>
                    <input type="date" class="form-control" name="FechaOrdenacion" id="fechaOrdenacionEdit" value="<?php echo $personal['FechaOrdenacion'] ?? ''; ?>" autocomplete="off">
                  </div>

                  <div class="col-sm-6 col-lg-3 form-group">
                    <label class="form-label">Usuario</label>
                    <input type="text" class="form-control" name="UsuarioPer" value="<?php echo htmlspecialchars($personal['UsuarioPer']); ?>" autocomplete="off" required>
                  </div>

                  <div class="col-sm-6 col-lg-3 form-group">
                    <label class="form-label">Contraseña</label>
                    <input type="text" class="form-control" name="ClavePer" value="<?php echo htmlspecialchars($personal['ClavePer']); ?>" autocomplete="off" required>
                  </div>

                  <div class="col-sm-6 col-lg-3 form-group">
                    <label class="form-label">Sexo</label>
                    <?php $genero = $personal['Sexo']; ?>
                    <div>
                      <input type="radio" name="Sexo" value="Varon" <?php echo ($genero == 'Varon') ? 'checked' : ''; ?>>
                      <label for="varon">Varon</label>
                      &nbsp;&nbsp;&nbsp;
                      <input type="radio" name="Sexo" value="Mujer" <?php echo ($genero == 'Mujer') ? 'checked' : ''; ?>>
                      <label for="mujer">Mujer</label>
                    </div>
                  </div>

                  <div class="col-sm-6 col-lg-3 form-group">
                    <label class="form-label">Estado</label>
                    <?php $Estado = $personal['Estado']; ?>
                    <div>
                      <input type="radio" name="Estado" value="Activo" <?php echo ($Estado == 'Activo') ? 'checked' : ''; ?>>
                      <label for="Activo">Activo</label>
                      &nbsp;&nbsp;&nbsp;
                      <input type="radio" name="Estado" value="Inactivo" <?php echo ($Estado == 'Inactivo') ? 'checked' : ''; ?>>
                      <label for="Inactivo">Inactivo</label>
                    </div>
                  </div>

                  <div class="col-sm-6 col-lg-3 form-group">
                    <label class="form-label">Fecha de inicio de gestión</label>
                    <input type="date" class="form-control" value="<?php echo htmlspecialchars($personal['FechaIng'] ?? ''); ?>" disabled>
                  </div>

                  <div class="col-sm-6 col-lg-3 form-group">
                    <label class="form-label">Fecha de fin de gestión</label>
                    <input type="date" class="form-control" value="<?php echo htmlspecialchars($personal['FechaFinGestion'] ?? ''); ?>" disabled>
                    <div class="form-text">Se registra automáticamente al cambiar el estado a Inactivo.</div>
                  </div>

                  <div class="col-sm-6 col-lg-3 me-2">
                    <label class="form-label" class="form-control">Cargo</label>
                    <select class="form-control" name="CodCar_2" id="cargoEditar" data-search-enabled="true" autocomplete="off">
                      <option value="3" <?php if ($personal['CodCar'] == "3") echo 'selected'; ?>>Párroco</option>
                      <option value="4" <?php if ($personal['CodCar'] == "4") echo 'selected'; ?>>Secretario (a) Parroquial</option>
                      <option value="5" <?php if ($personal['CodCar'] == "5") echo 'selected'; ?>>Sacerdote</option>
                      <option value="6" <?php if ($personal['CodCar'] == "6") echo 'selected'; ?>>Sacristán</option>
                      <option value="7" <?php if ($personal['CodCar'] == "7") echo 'selected'; ?>>Diácono</option>
                      <?php if (!in_array((int) $personal['CodCar'], [1, 3, 4, 5, 6, 7], true)): ?>
                        <option value="<?php echo (int) $personal['CodCar']; ?>" selected><?php echo htmlspecialchars($personal['DescripCar']); ?></option>
                      <?php endif; ?>
                      <option value="otro">Otro</option>
                    </select>
                  </div>

                  <div class="col-sm-6 col-lg-3 form-group" id="grupoCargoOtroEdit" style="display: none;">
                    <label class="form-label">¿Cuál cargo?</label>
                    <input type="text" class="form-control" name="CargoOtro" id="cargoOtroEdit" placeholder="Escribe el cargo" autocomplete="off">
                  </div>

                  <br>
                  <button type="submit" name="modificar" id="btnActualizar" class="btn btn-success-soft">Actualizar</button>
                  <a href="generar_ticket.php?ci=<?php echo $personal['CiPersonal']; ?>" target="_blank" class="btn btn-xs btn-success ms-2">Ver ticket de acceso</a>
                </form>

                <script>
                  // La fecha de ordenación solo se muestra para Párroco, Sacerdote y Diácono.
                  (function() {
                    var cargo = document.getElementById('cargoEditar');
                    var grupo = document.getElementById('grupoFechaOrdenacionEdit');
                    var fecha = document.getElementById('fechaOrdenacionEdit');
                    var grupoOtro = document.getElementById('grupoCargoOtroEdit');
                    var textoOtro = document.getElementById('cargoOtroEdit');
                    if (!cargo || !grupo || !fecha) return;
                    function actualizarFechaOrdenacion() {
                      var esOrdenado = (cargo.value === '3' || cargo.value === '5' || cargo.value === '7');
                      grupo.style.display = esOrdenado ? '' : 'none';
                      if (!esOrdenado) { fecha.value = ''; }
                      if (grupoOtro && textoOtro) {
                        var esOtro = (cargo.value === 'otro');
                        grupoOtro.style.display = esOtro ? '' : 'none';
                        textoOtro.required = esOtro;
                        if (!esOtro) { textoOtro.value = ''; }
                      }
                    }
                    cargo.addEventListener('change', actualizarFechaOrdenacion);
                    actualizarFechaOrdenacion();
                  })();
                </script>
              <?php endif; ?>

              <?php
              // Clases de color para la etiqueta del cargo en la tabla.
              $clasesCargo = [
                3 => 'cargo-parroco',
                4 => 'cargo-secretario',
                5 => 'cargo-sacerdote',
                6 => 'cargo-sacristan',
                7 => 'cargo-diacono',
              ];
              // Etiquetas cortas para que la tabla no se desborde.
              $etiquetasCargo = [
                3 => 'Párroco',
                4 => 'Secretario (a)',
                5 => 'Sacerdote',
                6 => 'Sacristán',
                7 => 'Diácono',
              ];
              ?>

              <?php if (!$personal && empty($listaPersonal) && !isset($_GET['Cper'])): ?>
                <div class="d-flex justify-content-end align-items-center gap-2 mb-3">
                  <form method="GET" class="d-flex align-items-center gap-2 small text-body-secondary mb-0">
                    <span>Registros por página:</span>
                    <select name="porPagina" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                      <?php foreach ([15, 25, 50] as $opcion): ?>
                        <option value="<?php echo $opcion; ?>" <?php if ($opcion == $porPagina) echo 'selected'; ?>><?php echo $opcion; ?></option>
                      <?php endforeach; ?>
                    </select>
                  </form>
                </div>

                <?php if (empty($personalTabla)): ?>
                  <div class="my-sm-5 py-sm-5 text-center">
                    <i class="display-1 text-body-secondary bi bi-people"></i>
                    <h4 class="mt-2 mb-3 text-body">Aún no hay personal registrado</h4>
                  </div>
                <?php else: ?>
                  <div class="table-responsive">
                    <table class="table table-hover tabla-personal align-middle mb-0">
                      <thead>
                        <tr>
                          <th>CI</th>
                          <th>Nombre</th>
                          <th>Paterno</th>
                          <th>Materno</th>
                          <th>F. Nacimiento</th>
                          <th>F. Ordenación</th>
                          <th>Contacto</th>
                          <th>Correo</th>
                          <th>Cargo</th>
                          <th>Estado</th>
                          <th>Fin de gestión</th>
                          <th class="text-center">Acceso</th>
                          <th class="text-center">Ver</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($personalTabla as $p): ?>
                          <tr>
                            <td class="ci"><?php echo htmlspecialchars($p['CiPersonal']); ?></td>
                            <td><strong><?php echo htmlspecialchars($p['Nombre']); ?></strong></td>
                            <td><?php echo htmlspecialchars($p['Paterno']); ?></td>
                            <td><?php echo $p['Materno'] !== '' ? htmlspecialchars($p['Materno']) : '<span class="dato-vacio">—</span>'; ?></td>
                            <td><?php echo $p['FechaNac'] ? date('d/m/Y', strtotime($p['FechaNac'])) : '<span class="dato-vacio">—</span>'; ?></td>
                            <td><?php echo $p['FechaOrdenacion'] ? date('d/m/Y', strtotime($p['FechaOrdenacion'])) : '<span class="dato-vacio">—</span>'; ?></td>
                            <td>
                              <?php if ($p['ContactoPer'] !== ''): ?>
                                <a href="tel:<?php echo htmlspecialchars($p['ContactoPer']); ?>"><?php echo htmlspecialchars($p['ContactoPer']); ?></a>
                              <?php else: ?>
                                <span class="dato-vacio">—</span>
                              <?php endif; ?>
                            </td>
                            <td>
                              <?php if (!empty($p['Correo'])): ?>
                                <a class="correo-celda" href="mailto:<?php echo htmlspecialchars($p['Correo']); ?>" title="<?php echo htmlspecialchars($p['Correo']); ?>"><?php echo htmlspecialchars($p['Correo']); ?></a>
                              <?php else: ?>
                                <span class="dato-vacio">—</span>
                              <?php endif; ?>
                            </td>
                            <td><span class="badge-cargo <?php echo $clasesCargo[(int) $p['CodCar']] ?? 'cargo-sacristan'; ?>"><?php echo htmlspecialchars($etiquetasCargo[(int) $p['CodCar']] ?? $p['DescripCar']); ?></span></td>
                            <td><?php echo htmlspecialchars($p['Estado'] ?? ''); ?></td>
                            <td><?php echo !empty($p['FechaFinGestion']) ? date('d/m/Y', strtotime($p['FechaFinGestion'])) : '<span class="dato-vacio">—</span>'; ?></td>
                            <td class="text-center">
                              <?php $esOtroParroco = ((int) $p['CodCar'] === 3 && $p['CiPersonal'] != $usuario['CiPersonal']); ?>
                              <button type="button" class="btn btn-sm btn-acceso"
                                title="<?php echo $esOtroParroco ? 'Permisos de acceso (solo lectura)' : 'Permisos de acceso'; ?>"
                                data-bs-toggle="modal" data-bs-target="#modalAcceso"
                                data-ci="<?php echo htmlspecialchars($p['CiPersonal']); ?>">
                                <i class="bi bi-key"></i>
                              </button>
                            </td>
                            <td class="text-center">
                              <?php if ($p['CiPersonal'] == $usuario['CiPersonal']): ?>
                                <span class="badge-cargo cargo-sacristan" title="Tu propio registro no se puede editar">Tú</span>
                              <?php else: ?>
                                <a href="vistaGesPersonal.php?Cper=<?php echo urlencode($p['CiPersonal']); ?>" class="btn btn-sm btn-ver-tabla" title="Ver registro"><i class="bi bi-eye"></i></a>
                              <?php endif; ?>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>

                  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                    <span class="small text-body-secondary">Página <?php echo $pagina; ?> de <?php echo $totalPaginas; ?> · <?php echo $totalPersonal; ?> <?php echo $totalPersonal == 1 ? 'registro' : 'registros'; ?></span>
                    <nav>
                      <ul class="pagination pagination-sm mb-0">
                        <li class="page-item <?php if ($pagina <= 1) echo 'disabled'; ?>">
                          <a class="page-link" href="vistaGesPersonal.php?pagina=<?php echo max(1, $pagina - 1); ?>&porPagina=<?php echo $porPagina; ?>">&laquo;</a>
                        </li>
                        <?php
                        $desde = max(1, $pagina - 2);
                        $hasta = min($totalPaginas, $pagina + 2);
                        for ($i = $desde; $i <= $hasta; $i++):
                        ?>
                          <li class="page-item <?php if ($i == $pagina) echo 'active'; ?>">
                            <a class="page-link" href="vistaGesPersonal.php?pagina=<?php echo $i; ?>&porPagina=<?php echo $porPagina; ?>"><?php echo $i; ?></a>
                          </li>
                        <?php endfor; ?>
                        <li class="page-item <?php if ($pagina >= $totalPaginas) echo 'disabled'; ?>">
                          <a class="page-link" href="vistaGesPersonal.php?pagina=<?php echo min($totalPaginas, $pagina + 1); ?>&porPagina=<?php echo $porPagina; ?>">&raquo;</a>
                        </li>
                      </ul>
                    </nav>
                  </div>
                <?php endif; ?>
              <?php endif; ?>

              <?php if (!$personal && empty($listaPersonal) && isset($_GET['Cper'])): ?>
                <div class="my-sm-5 py-sm-5 text-center">
                  <i class="display-1 text-body-secondary bi bi-calendar2-event"></i>
                  <h4 class="mt-2 mb-3 text-body">Personal no encontrado</h4>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>


      </div>
    </div>
  </main>


  <div class="modal fade modal-xl" id="MostrarModal" tabindex="-1" aria-labelledby="MostrarModal" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addModalLabel">Agregar Nuevo Personal</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form method="POST" class="row g-3" id="formNuevoPersonal" autocomplete="off">

            <div class="col-sm-6 col-lg-3 me-2">
              <label class="form-label">Cargo:</label>
              <select class="form-control form-select-cargo" name="CodCar" id="cargoNuevoPersonal" data-search-enabled="true" autocomplete="off">
                <option value="3">Párroco</option>
                <option value="4">Secretario (a) Parroquial</option>
                <option value="5">Sacerdote</option>
                <option value="6">Sacristán</option>
                <option value="7">Diácono</option>
                <option value="otro">Otro</option>
              </select>
            </div>

            <div class="form-group col-sm-6 col-lg-3" id="grupoCargoOtro" style="display: none;">
              <label class="form-label">¿Cuál cargo?</label>
              <input type="text" class="form-control" name="CargoOtro" id="cargoOtro" placeholder="Escribe el cargo" autocomplete="off">
            </div>

            <div class="form-group col-sm-1 col-lg-2">
              <label class="form-label">Ci Personal:</label>
              <input type="text" class="form-control" name="CiPersonal" placeholder="CI Personal" autocomplete="off" required>
            </div>
            <div class="form-group col-sm-1 col-lg-2">
              <label class="form-label">Nombre Completo:</label>
              <input type="text" class="form-control" name="Nombre" placeholder="Nombre Completo" autocomplete="off" required>
            </div>
            <div class="form-group col-sm-1 col-lg-2">
              <label class="form-label">Apellido Paterno:</label>
              <input type="text" class="form-control" name="Paterno" placeholder="Apellido Paterno" autocomplete="off" required>
            </div>
            <div class="form-group col-sm-1 col-lg-2">
              <label class="form-label">Apellido Materno:</label>
              <input type="text" class="form-control" name="Materno" placeholder="Apellido Materno" autocomplete="off">
            </div>

            <div class="form-group col-sm-1 col-lg-2">
              <label class="form-label">Número de Contacto:</label>
              <input type="text" class="form-control" name="ContactoPer" placeholder="Número de Contacto" autocomplete="off" required>
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

            <div class="form-group col-lg-3">
              <label class="form-label">Fecha de Nacimiento:</label>
              <input type="date" class="form-control" name="FechaNac" autocomplete="off">
            </div>

            <div class="form-group col-lg-3">
              <label class="form-label">Correo Electrónico:</label>
              <input type="email" class="form-control" name="Correo" placeholder="Correo Electrónico (opcional)" autocomplete="off">
            </div>

            <div class="form-group col-lg-3" id="grupoFechaOrdenacion" style="display: none;">
              <label class="form-label">Fecha de Ordenación:</label>
              <input type="date" class="form-control" name="FechaOrdenacion" autocomplete="off">
            </div>

            <div class="form-group col-lg-3">
              <label class="form-label">Usuario:</label>
              <input type="text" class="form-control" name="UsuarioPer" placeholder="Usuario de acceso" autocomplete="off" maxlength="100" required>
            </div>

            <div class="form-group col-lg-3">
              <label class="form-label">Contraseña:</label>
              <input type="text" class="form-control" name="ClavePer" placeholder="Contraseña de acceso" autocomplete="new-password" maxlength="100" required>
            </div>

            <button type="submit" class="btn btn-success-soft" name="registrar"><i class="fas fa-save me-1" aria-hidden="true"></i>Guardar y Cerrar</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalAcceso" tabindex="-1" aria-labelledby="tituloAcceso" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content modal-acceso">
        <div class="acceso-cabecera">
          <img src="../assets/images/logo.png" alt="Parroquia San Pedro de Sacaba" class="acceso-logo">
          <div class="acceso-linea"></div>
          <div class="acceso-titulo">
            <div>
              <h5 id="tituloAcceso"><i class="bi bi-key me-2" style="color: #d4a017;"></i>Permisos de acceso</h5>
              <p>Define qué módulos podrá usar esta persona al iniciar sesión.</p>
            </div>
            <button type="button" class="btn-x-acceso" data-bs-dismiss="modal" aria-label="Cerrar">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>
        </div>

        <div class="modal-body p-0">
          <div class="acceso-persona">
            <div class="acceso-avatar" id="accesoAvatar">—</div>
            <div>
              <div class="acceso-nombre" id="accesoNombre">Cargando…</div>
              <div class="acceso-meta" id="accesoMeta">—</div>
            </div>
            <span class="acceso-cargo" id="accesoCargo">—</span>
          </div>

          <div class="alerta-acceso-lectura" id="accesoAvisoLectura" role="alert">
            <i class="bi bi-lock-fill me-1"></i>
            <span id="accesoMotivoLectura">Los permisos del Párroco administrador son permanentes.</span>
          </div>

          <div class="acceso-seccion">
            <span class="titulo">Módulos habilitados</span>
            <div class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" id="accesoTodos">
              <label class="form-check-label small text-body-secondary" for="accesoTodos">Seleccionar todos</label>
            </div>
          </div>

          <div class="acceso-modulos" id="accesoModulos">
            <div class="row g-2">
              <?php foreach (ModeloAcceso::MODULOS as $claveModulo => $modulo): ?>
                <div class="col-12 col-sm-6">
                  <label class="modulo-acceso" data-clave="<?php echo $claveModulo; ?>">
                    <span class="icono"><i class="bi <?php echo $modulo['icono']; ?>"></i></span>
                    <span>
                      <span class="nombre"><?php echo $modulo['nombre']; ?></span>
                      <span class="detalle"><?php echo $modulo['detalle']; ?></span>
                    </span>
                    <?php if ($claveModulo === 'personal'): ?>
                      <span class="etiqueta-parroco" id="accesoEtiquetaParroco">PÁRROCO</span>
                      <span class="etiqueta-no" id="accesoEtiquetaNo" style="display: none;">SIN ACCESO</span>
                    <?php endif; ?>
                    <input class="form-check-input" type="checkbox" data-clave="<?php echo $claveModulo; ?>">
                  </label>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="alerta-acceso-ok" id="accesoGuardado">
            <i class="bi bi-check-circle-fill"></i>
            <span>¡Cambios guardados!</span>
          </div>
          <div class="alerta-acceso-error" id="accesoError"></div>
        </div>

        <div class="acceso-pie">
          <span class="nota">
            <i class="bi bi-info-circle me-1"></i>
            El módulo Personal solo puede habilitarse para un Párroco. Los cambios se aplican cuando la persona vuelva a iniciar sesión.
          </span>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-acceso-guardar" id="btnGuardarAcceso">
              <i class="bi bi-save me-1"></i>Guardar cambios
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="../assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Panel de permisos de acceso.
    (function () {
      var modal = document.getElementById('modalAcceso');
      if (!modal || !window.bootstrap) return;

      var endpoint = '../Controlador/controladorAcceso.php';
      var csrf = <?php echo json_encode($_SESSION['csrf_acceso']); ?>;
      var estado = { ci: null, esParroco: false, puedeEditar: true };
      var botonGuardar = document.getElementById('btnGuardarAcceso');
      var htmlGuardar = botonGuardar.innerHTML;
      var alertaOk = document.getElementById('accesoGuardado');
      var alertaError = document.getElementById('accesoError');
      var avisoLectura = document.getElementById('accesoAvisoLectura');
      var contenedorModulos = document.getElementById('accesoModulos');
      var switchTodos = document.getElementById('accesoTodos');

      function iniciales(nombre) {
        var partes = String(nombre || '').trim().split(/\s+/);
        return ((partes[0] || '') .charAt(0) + (partes[1] || '').charAt(0)).toUpperCase() || '—';
      }

      function actualizarSwitchTodos() {
        var marcables = contenedorModulos.querySelectorAll('input[type="checkbox"]:not(:disabled)');
        var marcados = contenedorModulos.querySelectorAll('input[type="checkbox"]:not(:disabled):checked');
        switchTodos.checked = marcables.length > 0 && marcables.length === marcados.length;
      }

      function pintarTarjeta(input) {
        var tarjeta = input.closest('.modulo-acceso');
        if (tarjeta) {
          tarjeta.classList.toggle('activo', input.checked && !input.disabled);
        }
      }

      function aplicarPermisos(permisos, esParroco, puedeEditar) {
        puedeEditar = puedeEditar !== false;
        contenedorModulos.querySelectorAll('input[type="checkbox"]').forEach(function (input) {
          var clave = input.getAttribute('data-clave');
          var tarjeta = input.closest('.modulo-acceso');
          var bloqueado = !puedeEditar || (clave === 'personal' && !esParroco);

          input.disabled = bloqueado;
          tarjeta.classList.toggle('bloqueado', bloqueado);

          if (clave === 'personal') {
            document.getElementById('accesoEtiquetaParroco').style.display = esParroco ? '' : 'none';
            document.getElementById('accesoEtiquetaNo').style.display = esParroco ? 'none' : '';
          }

          // En modo solo lectura se conservan marcados los permisos actuales.
          // El módulo Personal se muestra sin marcar solo cuando el cargo no es Párroco.
          input.checked = clave === 'personal' && !esParroco ? false : !!permisos[clave];
          pintarTarjeta(input);
        });
        switchTodos.disabled = !puedeEditar;
        if (avisoLectura) {
          avisoLectura.classList.toggle('visible', !puedeEditar);
        }
        botonGuardar.disabled = !puedeEditar;
        botonGuardar.innerHTML = puedeEditar ? htmlGuardar : '<i class="bi bi-lock me-1"></i>Solo lectura';
        actualizarSwitchTodos();
      }

      contenedorModulos.querySelectorAll('input[type="checkbox"]').forEach(function (input) {
        input.addEventListener('change', function () {
          pintarTarjeta(input);
          actualizarSwitchTodos();
        });
      });

      switchTodos.addEventListener('change', function () {
        contenedorModulos.querySelectorAll('input[type="checkbox"]:not(:disabled)').forEach(function (input) {
          input.checked = switchTodos.checked;
          pintarTarjeta(input);
        });
      });

      modal.addEventListener('show.bs.modal', function (event) {
        var boton = event.relatedTarget;
        if (!boton) return;

        estado.ci = boton.getAttribute('data-ci');
        estado.puedeEditar = true;
        alertaOk.classList.remove('visible');
        alertaError.classList.remove('visible');
        if (avisoLectura) {
          avisoLectura.classList.remove('visible');
        }
        switchTodos.disabled = true;
        contenedorModulos.classList.remove('acceso-guardado-flash');
        botonGuardar.classList.remove('guardado');
        botonGuardar.innerHTML = htmlGuardar;
        botonGuardar.disabled = true;
        document.getElementById('accesoNombre').textContent = 'Cargando…';
        document.getElementById('accesoMeta').textContent = '—';
        document.getElementById('accesoCargo').textContent = '—';
        document.getElementById('accesoAvatar').textContent = '—';

        fetch(endpoint + '?accion=obtener&ci=' + encodeURIComponent(estado.ci), { credentials: 'same-origin' })
          .then(function (respuesta) { return respuesta.json(); })
          .then(function (data) {
            if (!data.ok) throw new Error(data.error || 'No se pudieron cargar los permisos.');

            estado.esParroco = !!data.persona.esParroco;
            estado.puedeEditar = data.puedeEditar !== false;
            var motivoLectura = document.getElementById('accesoMotivoLectura');
            if (motivoLectura && data.motivoSoloLectura) {
              motivoLectura.textContent = data.motivoSoloLectura;
            }
            document.getElementById('accesoAvatar').textContent = iniciales(data.persona.nombre);
            document.getElementById('accesoNombre').textContent = data.persona.nombre;
            document.getElementById('accesoMeta').textContent = 'CI ' + data.persona.ci + ' · Usuario: ' + data.persona.usuario;
            document.getElementById('accesoCargo').textContent = data.persona.cargo;
            aplicarPermisos(data.permisos, estado.esParroco, estado.puedeEditar);
            botonGuardar.disabled = !estado.puedeEditar;
          })
          .catch(function (error) {
            alertaError.textContent = error.message;
            alertaError.classList.add('visible');
          });
      });

      botonGuardar.addEventListener('click', function () {
        if (!estado.ci || botonGuardar.disabled || !estado.puedeEditar) return;

        var datos = new URLSearchParams();
        datos.append('accion', 'guardar');
        datos.append('ci', estado.ci);
        datos.append('csrf', csrf);
        contenedorModulos.querySelectorAll('input[type="checkbox"]').forEach(function (input) {
          datos.append(input.getAttribute('data-clave'), input.checked ? '1' : '0');
        });

        botonGuardar.disabled = true;
        botonGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Guardando…';
        alertaError.classList.remove('visible');

        fetch(endpoint, {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
          body: datos.toString()
        })
          .then(function (respuesta) { return respuesta.json(); })
          .then(function (data) {
            if (!data.ok) throw new Error(data.error || 'No se pudieron guardar los permisos.');

            aplicarPermisos(data.permisos, estado.esParroco, estado.puedeEditar);
            alertaOk.classList.add('visible');
            contenedorModulos.classList.remove('acceso-guardado-flash');
            void contenedorModulos.offsetWidth;
            contenedorModulos.classList.add('acceso-guardado-flash');
            botonGuardar.classList.add('guardado');
            botonGuardar.innerHTML = '<i class="bi bi-check-circle me-1"></i>Guardado';

            setTimeout(function () {
              botonGuardar.classList.remove('guardado');
              botonGuardar.innerHTML = htmlGuardar;
              botonGuardar.disabled = false;
              alertaOk.classList.remove('visible');
            }, 3000);
          })
          .catch(function (error) {
            alertaError.textContent = error.message;
            alertaError.classList.add('visible');
            botonGuardar.innerHTML = htmlGuardar;
            botonGuardar.disabled = false;
          });
      });

      modal.addEventListener('hidden.bs.modal', function () {
        botonGuardar.classList.remove('guardado');
        botonGuardar.innerHTML = htmlGuardar;
        botonGuardar.disabled = false;
      });
    })();
  </script>

  <script>
    // La fecha de ordenación solo se pide cuando el cargo es Párroco, Sacerdote o Diácono.
    (function() {
      var cargo = document.getElementById('cargoNuevoPersonal');
      var grupoFecha = document.getElementById('grupoFechaOrdenacion');
      var grupoOtro = document.getElementById('grupoCargoOtro');
      var textoOtro = document.getElementById('cargoOtro');
      if (!cargo || !grupoFecha) return;
      function actualizarFechaOrdenacion() {
        grupoFecha.style.display = (cargo.value === '3' || cargo.value === '5' || cargo.value === '7') ? '' : 'none';
        if (grupoOtro && textoOtro) {
          var esOtro = (cargo.value === 'otro');
          grupoOtro.style.display = esOtro ? '' : 'none';
          textoOtro.required = esOtro;
          if (!esOtro) { textoOtro.value = ''; }
        }
      }
      cargo.addEventListener('change', actualizarFechaOrdenacion);
      actualizarFechaOrdenacion();
    })();
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

              if (response.DescripCar === "Personal") {
                window.location.replace("VistaPersonal.php");
              }

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
  <script src="../assets/js/functions.js"></script>
  <script src="../assets/js/tema.js"></script>
  <?php if (isset($_GET['actualizado'])): ?>
    <script>
      // Aviso de actualización: botón verde con "Actualizado" y luego vuelve a la normalidad.
      (function () {
        var boton = document.getElementById('btnActualizar');
        var alerta = document.getElementById('alertaActualizado');

        if (boton) {
          boton.classList.remove('btn-success-soft');
          boton.classList.add('btn-success');
          boton.innerHTML = '<i class="bi bi-check-circle me-1"></i>Actualizado';
        }

        setTimeout(function () {
          if (boton) {
            boton.classList.remove('btn-success');
            boton.classList.add('btn-success-soft');
            boton.innerHTML = 'Actualizar';
          }
          if (alerta) {
            alerta.style.transition = 'opacity .5s';
            alerta.style.opacity = '0';
            setTimeout(function () { alerta.style.display = 'none'; }, 550);
          }
        }, 3500);
      })();
    </script>
  <?php endif; ?>
  <script>
    // Bloquea la lista negra de autocompletado del navegador (datos personales/autofill)
    // en los formularios de personal. Los campos se activan al enfocarlos y al enviar.
    (function () {
      var contenedores = [
        document.getElementById('formNuevoPersonal'),
        document.getElementById('formEditarPersonal')
      ];

      contenedores.forEach(function (form) {
        if (!form) return;

        var campos = form.querySelectorAll('input[type="text"], input[type="email"], input[type="date"], input[type="tel"]');

        function habilitarTodos() {
          campos.forEach(function (campo) {
            campo.removeAttribute('readonly');
          });
        }

        campos.forEach(function (campo) {
          campo.setAttribute('readonly', 'readonly');
          campo.setAttribute('autocomplete', 'off');
          // Al enfocar se habilita el campo (el navegador ya no ofrece la lista negra).
          campo.addEventListener('focus', function () {
            campo.removeAttribute('readonly');
          });
          // Al salir se vuelve a bloquear para que tampoco aparezca en el siguiente clic.
          campo.addEventListener('blur', function () {
            campo.setAttribute('readonly', 'readonly');
          });
        });

        // Al enviar se habilitan todos para que "required" siga funcionando.
        var boton = form.querySelector('[type="submit"]');
        if (boton) {
          boton.addEventListener('mousedown', habilitarTodos);
          boton.addEventListener('touchstart', habilitarTodos);
          boton.addEventListener('click', habilitarTodos);
        }
        form.addEventListener('keydown', function (evento) {
          if (evento.key === 'Enter') {
            habilitarTodos();
          }
        });
        form.addEventListener('submit', habilitarTodos);
      });
    })();
  </script>
</body>

</html>
