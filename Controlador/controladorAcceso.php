<?php
require_once __DIR__ . '/../Modelo/modeloAcceso.php';

/**
 * Permisos de acceso por persona: sesión, menú lateral, protección de
 * módulos y el servicio que usa la pantalla de personal.
 */
class ControladorAcceso {
    private $modelo;

    public function __construct() {
        $this->modelo = new ModeloAcceso();
    }

    /** Permisos vigentes del usuario conectado. */
    public static function permisosSesion() {
        $cargo = $_SESSION['usuario']['DescripCar'] ?? '';
        // El Párroco administra todo el sistema y sus accesos no pueden retirarse.
        // También corrige permisos antiguos que hayan quedado guardados en la sesión.
        if ($cargo === 'Párroco') {
            $_SESSION['permisos'] = ModeloAcceso::permisosPorDefecto($cargo);
            return $_SESSION['permisos'];
        }

        if (isset($_SESSION['permisos']) && is_array($_SESSION['permisos'])) {
            return $_SESSION['permisos'];
        }

        $_SESSION['permisos'] = ModeloAcceso::permisosPorDefecto($cargo);
        return $_SESSION['permisos'];
    }

    public static function puede($modulo) {
        $permisos = self::permisosSesion();
        if (empty($permisos[$modulo])) {
            return false;
        }

        // El módulo Personal nunca se habilita fuera de un Párroco.
        if ($modulo === 'personal' && ($_SESSION['usuario']['DescripCar'] ?? '') !== 'Párroco') {
            return false;
        }

        return true;
    }

    /** Corta la navegación directa a un módulo sin permiso. */
    public static function exigir($modulo) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['usuario'])) {
            header('Location: ../logout.php');
            exit();
        }
        if (!self::puede($modulo)) {
            $_SESSION['acceso_denegado'] = true;
            header('Location: VistaPersonal.php');
            exit();
        }
    }

    /** Módulo activo según el archivo que se está mostrando. */
    private static function moduloActual() {
        $archivo = basename($_SERVER['SCRIPT_NAME'] ?? '');
        $mapa = [
            'VistaDashboard.php' => 'dashboard',
            'VistaPersonal.php' => 'inicio',
            'DetalleActividad.php' => 'inicio',
            'VistaPersona.php' => 'persona',
            'VistaReservas.php' => 'celebracion',
            'modificar_reserva.php' => 'celebracion',
            'VistaGesPersonal.php' => 'personal',
            'vistaGesPersonal.php' => 'personal',
            'VistaCoordinadorAdmin.php' => 'coordinador',
            'VistaSolicitante.php' => 'certificado',
            'registrar_documento.php' => 'certificado',
            'VistaRegistradoBautizo.php' => 'certificado',
            'VistaRegistradoConfirmacion.php' => 'certificado',
            'VistaRegistradoMatrimonio.php' => 'certificado',
            'VistaEmitidos.php' => 'emitidos',
            'VistaCostos.php' => 'costos',
        ];
        return $mapa[$archivo] ?? '';
    }

    /**
     * Menú lateral compartido por las vistas de personal.
     * $opciones: 'costos' (mostrar precios). Google Drive se muestra cuando
     * la persona tiene permiso para ese módulo.
     */
    public static function renderMenu(array $opciones = []) {
        $activo = self::moduloActual();
        $items = [
            'dashboard'   => ['VistaDashboard.php', 'Dashboard', 'img', '../assets/images/icon/imac-outline-filled.svg'],
            'inicio'      => ['VistaPersonal.php', 'Inicio', 'img', '../assets/images/icon/mensaje.png'],
            'persona'     => ['VistaPersona.php', 'Persona', 'img', '../assets/images/icon/nuevo.png'],
            'celebracion' => ['VistaReservas.php', 'Celebración', 'img', '../assets/images/icon/instrucciones.png'],
            'personal'    => ['vistaGesPersonal.php', 'Personal', 'img', '../assets/images/icon/seguro.png'],
            'coordinador' => ['VistaCoordinadorAdmin.php', 'Coordinadores', 'flama', '🔥'],
            'certificado' => ['VistaSolicitante.php', 'Certificado', 'img', '../assets/images/icon/contrato.png'],
            'emitidos'    => ['VistaEmitidos.php', 'Certificado Emitido', 'img', '../assets/images/icon/Emitido.png'],
            'costos'      => ['VistaCostos.php', 'Precios Sacramentales', 'img', '../assets/images/icon/Monetario.png'],
        ];

        $grupos = [
            'Principal'  => ['dashboard', 'inicio'],
            'Gestión'    => ['personal', 'persona', 'celebracion', 'coordinador'],
            'Documentos' => ['certificado', 'emitidos', 'costos'],
        ];

        $cargo = $_SESSION['usuario']['DescripCar'] ?? '';

        echo '<ul class="nav nav-link-secondary flex-column fw-bold side-nav">';

        foreach ($grupos as $titulo => $claves) {
            $filas = '';
            foreach ($claves as $clave) {
                $item = $items[$clave];
                $permiso = $clave === 'dashboard' ? 'inicio' : $clave;

                if ($clave === 'costos') {
                    if (empty($opciones['costos'])) continue;
                } else {
                    if (!self::puede($permiso)) continue;
                    if ($clave === 'personal' && $cargo !== 'Párroco') continue;
                }

                $clase = 'nav-link side-item' . ($activo === $clave ? ' active' : '');

                if ($item[2] === 'img') {
                    $icono = '<span class="side-icon"><img src="' . $item[3] . '" alt=""></span>';
                } elseif ($item[2] === 'bi') {
                    $icono = '<span class="side-icon"><i class="fa-fw bi ' . $item[3] . '"></i></span>';
                } else {
                    $icono = '<span class="side-icon side-flame" aria-hidden="true">' . $item[3] . '</span>';
                }

                $filas .= '<li class="nav-item"><a class="' . $clase . '" href="' . $item[0] . '">'
                    . $icono
                    . '<span class="side-txt">' . $item[1] . '</span>'
                    . ($activo === $clave ? '<span class="side-dot" aria-hidden="true"></span>' : '')
                    . '</a></li>';
            }

            if ($filas === '') continue;

            echo '<li class="nav-item side-label-item"><span class="side-label">'
                . '<span class="side-label-bar" aria-hidden="true"></span>' . $titulo
                . '</span></li>';
            echo $filas;
        }

        echo '</ul>';
    }

    /** Atiende las peticiones de la pantalla de personal. */
    public function manejarSolicitud() {
        header('Content-Type: application/json; charset=utf-8');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $usuario = $_SESSION['usuario'] ?? null;
        $esParroco = $usuario && ($usuario['DescripCar'] ?? '') === 'Párroco' && self::puede('personal');
        if (!$esParroco) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'No tienes permiso para administrar accesos.']);
            return;
        }

        $accion = $_REQUEST['accion'] ?? '';
        $ci = trim((string) ($_REQUEST['ci'] ?? ''));

        if ($accion === 'obtener') {
            $persona = $this->modelo->obtenerPersonaPorCi($ci);
            if (!$persona) {
                http_response_code(404);
                echo json_encode(['ok' => false, 'error' => 'No se encontró a la persona.']);
                return;
            }

            $esMismoUsuario = (int) $persona['CodPer'] === (int) ($usuario['CodPer'] ?? 0);
            $esParrocoObjetivo = $persona['DescripCar'] === 'Párroco';

            echo json_encode([
                'ok' => true,
                'persona' => [
                    'ci' => $persona['CiPersonal'],
                    'nombre' => trim($persona['Nombre'] . ' ' . $persona['Paterno'] . ' ' . $persona['Materno']),
                    'cargo' => $persona['DescripCar'],
                    'usuario' => $persona['UsuarioPer'],
                    'esParroco' => $persona['DescripCar'] === 'Párroco',
                ],
                'puedeEditar' => !$esParrocoObjetivo,
                'motivoSoloLectura' => $esParrocoObjetivo
                    ? ($esMismoUsuario
                        ? 'Tus permisos permanecen habilitados porque eres el administrador del sistema.'
                        : 'Los permisos de otro Párroco son permanentes y solo pueden consultarse.')
                    : '',
                'permisos' => $this->modelo->obtener((int) $persona['CodPer'], $persona['DescripCar']),
            ]);
            return;
        }

        if ($accion === 'guardar') {
            $token = $_POST['csrf'] ?? '';
            if (!is_string($token) || empty($_SESSION['csrf_acceso']) || !hash_equals($_SESSION['csrf_acceso'], $token)) {
                http_response_code(403);
                echo json_encode(['ok' => false, 'error' => 'La sesión del formulario venció. Cierra la ventana y vuelve a abrirla.']);
                return;
            }

            $persona = $this->modelo->obtenerPersonaPorCi($ci);
            if (!$persona) {
                http_response_code(404);
                echo json_encode(['ok' => false, 'error' => 'No se encontró a la persona.']);
                return;
            }

            // Todo Párroco es administrador: sus accesos son permanentes y no
            // pueden retirarse desde la interfaz ni enviando una petición manual.
            if ($persona['DescripCar'] === 'Párroco') {
                http_response_code(403);
                echo json_encode([
                    'ok' => false,
                    'error' => 'Los permisos del Párroco administrador son permanentes.',
                ]);
                return;
            }

            $permisos = [];
            foreach (array_keys(ModeloAcceso::MODULOS) as $clave) {
                $permisos[$clave] = !empty($_POST[$clave]) ? 1 : 0;
            }

            if (!$this->modelo->guardar((int) $persona['CodPer'], $permisos, $persona['DescripCar'])) {
                http_response_code(500);
                echo json_encode(['ok' => false, 'error' => 'No se pudieron guardar los permisos. Intenta nuevamente.']);
                return;
            }

            $guardados = $this->modelo->obtener((int) $persona['CodPer'], $persona['DescripCar']);

            // Si la persona edita su propio acceso, se refleja de inmediato.
            if ((int) $persona['CodPer'] === (int) ($usuario['CodPer'] ?? 0)) {
                $_SESSION['permisos'] = $guardados;
            }

            echo json_encode(['ok' => true, 'permisos' => $guardados]);
            return;
        }

        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Solicitud no válida.']);
    }
}

if (basename($_SERVER['SCRIPT_NAME'] ?? '') === 'controladorAcceso.php') {
    $accion = $_REQUEST['accion'] ?? '';
    if ($accion !== '') {
        $controlador = new ControladorAcceso();
        $controlador->manejarSolicitud();
    }
}
