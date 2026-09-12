<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../logout.php");
    exit();
}
$usuario = $_SESSION['usuario'];

require_once '../Controlador/controladorGoogleDrive.php';

$drive = new ControladorGoogleDrive();
$estaConectado = $drive->estaConectado();
$archivos = [];
$mensaje = '';
$tipoMensaje = '';

if (isset($_GET['drive'])) {
    if ($_GET['drive'] === 'conectado') {
        $mensaje = "Google Drive conectado correctamente.";
        $tipoMensaje = 'success';
        $estaConectado = true;
    } elseif ($_GET['drive'] === 'error') {
        $mensaje = "Error al conectar: " . ($_GET['msg'] ?? 'Error desconocido');
        $tipoMensaje = 'danger';
    }
}

if ($estaConectado) {
    $archivos = $drive->listarArchivos();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['desconectar'])) {
    $drive->desconectar();
    $estaConectado = false;
    $archivos = [];
    $mensaje = "Google Drive desconectado.";
    $tipoMensaje = 'warning';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar'])) {
    try {
        $drive->eliminarArchivo($_POST['fileId']);
        $archivos = $drive->listarArchivos();
        $mensaje = "Archivo eliminado de Drive.";
        $tipoMensaje = 'success';
    } catch (Exception $e) {
        $mensaje = "Error al eliminar: " . $e->getMessage();
        $tipoMensaje = 'danger';
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <title>Google Drive - San Pedro</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="shortcut icon" href="../assets/images/Icono.png">
    <link rel="stylesheet" type="text/css" href="../assets/vendor/font-awesome/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="../assets/vendor/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" type="text/css" href="../assets/css/style.css">
</head>

<body class="sidebar-start-enabled">
    <header class="navbar-light fixed-top header-static bg-mode">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="VistaPersonal.php">
                    <img class="light-mode-item navbar-brand-item" src="../assets/images/logo.png" alt="logo">
                </a>
                <ul class="nav flex-nowrap align-items-center ms-sm-3 list-unstyled">
                    <li class="nav-item ms-2">
                        <a class="nav-link bg-light icon-md btn btn-light p-0" href="VistaPersonal.php">
                            <i class="bi bi-house-fill fs-6"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <main>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 vstack gap-4">

                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-cloud-arrow-up-fill fs-1 text-primary"></i>
                            <h3 class="mt-3">Google Drive - Respaldo de Certificados</h3>
                            <p class="text-muted">Conecta tu cuenta de Google Drive para respaldar automáticamente los certificados generados.</p>

                            <?php if ($mensaje): ?>
                                <div class="alert alert-<?php echo $tipoMensaje; ?> alert-dismissible fade show" role="alert">
                                    <?php echo $mensaje; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <?php if (!$estaConectado): ?>
                                <a href="<?php echo $drive->obtenerUrlConexion(); ?>" class="btn btn-primary btn-lg mt-3">
                                    <i class="bi bi-google"></i> Conectar con Google Drive
                                </a>
                            <?php else: ?>
                                <div class="d-flex justify-content-center gap-2 mt-3">
                                    <span class="badge bg-success fs-6"><i class="bi bi-check-circle"></i> Conectado</span>
                                    <form method="POST" class="d-inline">
                                        <button type="submit" name="desconectar" class="btn btn-outline-danger btn-sm" onclick="return confirm('¿Desconectar Google Drive?')">
                                            <i class="bi bi-box-arrow-right"></i> Desconectar
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($estaConectado && count($archivos) > 0): ?>
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-folder2-open"></i> Archivos Respaldados</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Tipo</th>
                                                <th>Fecha</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($archivos as $archivo): ?>
                                                <tr>
                                                    <td>
                                                        <i class="bi bi-file-earmark-pdf text-danger"></i>
                                                        <?php echo htmlspecialchars($archivo->getName()); ?>
                                                    </td>
                                                    <td><small class="text-muted"><?php echo $archivo->getMimeType(); ?></small></td>
                                                    <td><small><?php echo date('d/m/Y H:i', strtotime($archivo->getCreatedTime())); ?></small></td>
                                                    <td>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="fileId" value="<?php echo $archivo->getId(); ?>">
                                                            <button type="submit" name="eliminar" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar este archivo de Drive?')">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php elseif ($estaConectado): ?>
                        <div class="card">
                            <div class="card-body text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1"></i>
                                <p class="mt-2">No hay certificados respaldados aún.</p>
                                <small>Los certificados se respaldarán automáticamente al generarlos.</small>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </main>

    <script src="../assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/functions.js"></script>
</body>

</html>
