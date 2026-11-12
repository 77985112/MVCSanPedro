<?php
require_once __DIR__ . '/../Modelo/modeloActividad.php';

class ControladorActividad
{
    public const LIMITE_DESCRIPCION = 5000;
    public const LIMITE_LUGAR = 255;
    /** Máximo de imágenes que puede tener una actividad (principal + adicionales). */
    public const MAXIMO_IMAGENES = 5;
    /** Tipos de actividad gestionables en el sistema. */
    public const TIPOS_ACTIVIDAD = ['Parroquia', 'Confirmación', 'Primera Comunión'];

    private $actividadModel;

    public function __construct()
    {
        $this->actividadModel = new Actividad();
    }

    public function obtenerActividades($tipo = null, $orden = null)
    {
        return $this->actividadModel->obtenerActividades($tipo, $orden);
    }

    public function registrarActividad($data)
    {
        $data = $this->validarTextosActividad($data);
        $this->validarFechaActividad($data['FechaActividad'] ?? null);
        $data['TipoActividad'] = trim((string) ($data['TipoActividad'] ?? ''));
        if (!in_array($data['TipoActividad'], self::TIPOS_ACTIVIDAD, true)) {
            throw new InvalidArgumentException("Elija el tipo de actividad según la pestaña del formulario (Parroquia, Primera Comunión o Confirmación).");
        }
        $data['VideoUrl'] = self::normalizarVideoUrl($data['VideoUrl'] ?? null);
        list($data['ImagenActividad'], $data['ImagenesExtras'], $data['VideoActividad']) = $this->prepararArchivosActividad(false);
        if ($data['ImagenActividad'] === 'Ninguno' && empty($data['VideoActividad']) && empty($data['VideoUrl'])) {
            throw new InvalidArgumentException("Adjunte una imagen, un video o pegue un enlace válido de YouTube, TikTok o Facebook.");
        }
        try {
            return $this->actividadModel->registrarActividad($data);
        } catch (Throwable $e) {
            $this->retirarArchivosActividad($data['ImagenActividad'], $data['VideoActividad']);
            $this->retirarArchivosNuevos($data['ImagenesExtras']);
            throw $e;
        }
    }

    private function validarTextosActividad($data)
    {
        foreach (['TituloActividad' => [150, 'título'], 'DetalleActividad' => [self::LIMITE_DESCRIPCION, 'descripción'],
                  'LugarAct' => [self::LIMITE_LUGAR, 'lugar']] as $campo => [$limite, $nombre]) {
            $valor = isset($data[$campo]) && is_string($data[$campo]) ? trim($data[$campo]) : '';
            if ($valor === '' || !mb_check_encoding($valor, 'UTF-8') || mb_strlen($valor, 'UTF-8') > $limite) {
                throw new InvalidArgumentException("Complete el {$nombre} (máximo {$limite} caracteres).");
            }
            $data[$campo] = $valor;
        }
        return $data;
    }

    /** Hoy según el reloj parroquial (America/La_Paz), en formato Y-m-d. */
    public static function hoyActividad()
    {
        return (new DateTimeImmutable('now', new DateTimeZone('America/La_Paz')))->format('Y-m-d');
    }

    public static function fechaMinimaActividad()
    {
        // Solo se publican actividades desde hoy en adelante.
        return self::hoyActividad();
    }

    private function validarFechaActividad($fechaTexto)
    {
        $fecha = is_string($fechaTexto) && preg_match('/^\d{4}-\d{2}-\d{2}$/D', $fechaTexto)
            ? DateTimeImmutable::createFromFormat('!Y-m-d', $fechaTexto, new DateTimeZone('America/La_Paz'))
            : false;
        if (!$fecha || $fecha->format('Y-m-d') !== $fechaTexto
            || $fechaTexto < self::fechaMinimaActividad()) {
            throw new InvalidArgumentException("Seleccione una fecha válida desde hoy en adelante.");
        }
    }

    public static function esVideoActividad($archivo)
    {
        return in_array(strtolower(pathinfo((string) $archivo, PATHINFO_EXTENSION)), ['mp4', 'webm'], true);
    }

    /**
     * URL para incrustar un video de YouTube, TikTok o Facebook.
     * Devuelve null si el enlace no pertenece a esos proveedores.
     */
    public static function videoEmbedUrl($url)
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        $partes = parse_url($url);
        if (!$partes || empty($partes['host'])) {
            return null;
        }

        $host = strtolower(preg_replace('/^www\./', '', $partes['host']));
        $ruta = (string) ($partes['path'] ?? '');
        $consulta = [];
        if (!empty($partes['query'])) {
            parse_str($partes['query'], $consulta);
        }

        // YouTube
        if (in_array($host, ['youtube.com', 'm.youtube.com', 'youtube-nocookie.com'], true)) {
            $id = isset($consulta['v']) && is_string($consulta['v']) ? $consulta['v'] : '';
            if ($id === '' && preg_match('#^/(?:shorts|embed|live|v)/([A-Za-z0-9_-]{6,20})#', $ruta, $coincide)) {
                $id = $coincide[1];
            }
            return preg_match('/^[A-Za-z0-9_-]{6,20}$/', $id) ? 'https://www.youtube.com/embed/' . $id : null;
        }

        if ($host === 'youtu.be') {
            return preg_match('#^/([A-Za-z0-9_-]{6,20})#', $ruta, $coincide)
                ? 'https://www.youtube.com/embed/' . $coincide[1] : null;
        }

        // TikTok
        if (in_array($host, ['tiktok.com', 'm.tiktok.com'], true)) {
            return preg_match('#/video/(\d{6,25})#', $ruta, $coincide)
                ? 'https://www.tiktok.com/embed/v2/' . $coincide[1] : null;
        }

        // Facebook
        if (in_array($host, ['facebook.com', 'm.facebook.com', 'web.facebook.com', 'fb.watch'], true)) {
            return 'https://www.facebook.com/plugins/video.php?href=' . rawurlencode($url) . '&show_text=false';
        }

        return null;
    }

    /** Valida el enlace del formulario y devuelve la URL o null. */
    private static function normalizarVideoUrl($url)
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        $esquema = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        if (mb_strlen($url, 'UTF-8') > 255 || !filter_var($url, FILTER_VALIDATE_URL)
            || !in_array($esquema, ['http', 'https'], true)) {
            throw new InvalidArgumentException("El enlace del video no es válido.");
        }

        if (self::videoEmbedUrl($url) === null) {
            throw new InvalidArgumentException("Use un enlace de YouTube, TikTok o Facebook.");
        }

        return $url;
    }

    /**
     * Archivos reales (imagen y video) de una actividad.
     * Devuelve [imagen|null, video|null]; 'Ninguno' o vacío se considera sin imagen.
     */
    public static function archivosActividad($actividad)
    {
        $imagen = !empty($actividad['ImagenActividad']) && $actividad['ImagenActividad'] !== 'Ninguno'
            ? basename($actividad['ImagenActividad']) : null;
        $video = !empty($actividad['VideoActividad'])
            ? basename($actividad['VideoActividad']) : null;
        return [$imagen, $video];
    }

    /**
     * Todas las imágenes de la actividad en orden: la principal y las adicionales (hasta 5).
     */
    public static function imagenesActividad($actividad)
    {
        list($imagen) = self::archivosActividad($actividad);
        $extras = [];
        foreach (($actividad['ImagenesExtras'] ?? []) as $extra) {
            if (is_string($extra) && $extra !== '' && $extra !== 'Ninguno') {
                $extras[] = basename($extra);
            }
        }
        return $imagen !== null ? array_merge([$imagen], $extras) : $extras;
    }

    /** Filas (idActImg + nombre) de las imágenes adicionales; para los formularios de edición. */
    public function imagenesExtras($idActividad)
    {
        return $this->actividadModel->imagenesExtras((int) $idActividad);
    }

    /**
     * HTML de los medios de la tarjeta: imagen arriba y video debajo (puede haber solo uno).
     * Si no hay ninguno se muestra la imagen genérica.
     */
    public static function mediaActividad($actividad, $alto = '150px', $modalImagen = false)
    {
        list($imagen, $video) = self::archivosActividad($actividad);
        $base = '../assets/images/events/';
        $html = '';
        if ($imagen !== null || $video === null) {
            $imagenes = self::imagenesActividad($actividad);
            if (count($imagenes) > 1) {
                $html .= '<span class="badge bg-dark text-white position-absolute top-0 start-0 m-2"'
                    . ' style="z-index:2;"><i class="bi bi-images me-1"></i>' . count($imagenes) . ' fotos</span>';
            }
            $rutaImagen = $base . rawurlencode($imagen ?? 'Generico.png');
            $atributos = '';
            if ($modalImagen && $imagen !== null) {
                $atributos = ' data-bs-toggle="modal" data-bs-target="#modalImagen"'
                    . ' onclick="abrirModal(\'' . $rutaImagen . '\')"';
            }
            $html .= '<img class="img-fluid rounded-top fixed-height" src="' . $rutaImagen
                . '" alt="" style="width: 100%; height:' . $alto . ';"' . $atributos . '>';
        }
        if ($video !== null) {
            $rutaVideo = $base . rawurlencode($video);
            $html .= '<video class="w-100 rounded actividad-video" src="' . $rutaVideo
                . '" controls playsinline preload="none"'
                . ' style="height:' . $alto . '; object-fit:contain; background:#000;">'
                . 'Su navegador no puede reproducir este video. <a href="' . $rutaVideo . '">Abrir video</a></video>';
        }
        return $html;
    }

    /**
     * Guarda el archivo del campo indicado. $tipo: 'imagen' (JPG, PNG, WEBP, GIF, etc. 2 MB) o 'video' (MP4/WebM 10 MB).
     * Devuelve el nombre generado o null si no se adjuntó nada (solo cuando es opcional).
     */
    private function guardarArchivoActividad($campo, $tipo, $opcional = false)
    {
        $archivo = $_FILES[$campo] ?? null;
        if ($opcional && ($archivo === null || (is_array($archivo)
            && ($archivo['error'] ?? null) === UPLOAD_ERR_NO_FILE))) {
            return null;
        }
        return $this->validarYGuardarArchivo(is_array($archivo) ? $archivo : null, $tipo);
    }

    /**
     * Valida un archivo individual (imagen JPG, PNG, WEBP, GIF, etc. 2 MB o video MP4/WebM 10 MB) y lo guarda.
     * Devuelve el nombre generado.
     */
    private function validarYGuardarArchivo($archivo, $tipo)
    {
        $esImagen = $tipo === 'imagen';
        if (!is_array($archivo) || ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
            || !is_string($archivo['tmp_name'] ?? null) || !is_uploaded_file($archivo['tmp_name'])) {
            throw new InvalidArgumentException($esImagen
                ? "Seleccione una imagen válida (JPG, PNG, WEBP, GIF, etc.) de hasta 2 MB."
                : "Seleccione un video MP4/WebM (hasta 10 MB).");
        }
        $extension = is_string($archivo['name'] ?? null)
            ? strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION)) : '';
        $tipos = $esImagen
            ? [
                'jpg' => ['image/jpeg'],
                'jpeg' => ['image/jpeg'],
                'jfif' => ['image/jpeg', 'image/jfif'],
                'png' => ['image/png'],
                'webp' => ['image/webp'],
                'gif' => ['image/gif'],
                'bmp' => ['image/bmp', 'image/x-ms-bmp'],
                'avif' => ['image/avif'],
                'tif' => ['image/tiff'],
                'tiff' => ['image/tiff'],
            ]
            : ['mp4' => ['video/mp4', 'application/mp4'], 'webm' => ['video/webm']];
        if (!isset($tipos[$extension])) {
            throw new InvalidArgumentException($esImagen
                ? "El formato de la imagen no es compatible. Use JPG, PNG, WEBP, GIF, BMP o AVIF."
                : "El video debe estar en formato MP4 o WebM.");
        }
        $limite = ($esImagen ? 2 : 10) * 1024 * 1024;
        $tamano = filesize($archivo['tmp_name']);
        if (!$tamano || $tamano > $limite) {
            throw new InvalidArgumentException($esImagen
                ? "La imagen debe pesar como máximo 2 MB y no estar vacía."
                : "El video debe pesar como máximo 10 MB y no estar vacío. Se comprime al adjuntarlo; si sigue superando el límite, usa un enlace de YouTube.");
        }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
        if (!in_array($mime, $tipos[$extension], true)) {
            throw new InvalidArgumentException($esImagen
                ? "El contenido del archivo no corresponde a una imagen válida."
                : "El contenido del archivo no corresponde a un video MP4/WebM válido.");
        }
        if ($esImagen && @getimagesize($archivo['tmp_name']) === false) {
            throw new InvalidArgumentException("La imagen seleccionada no es válida.");
        }
        if (!$esImagen) {
            $cabecera = file_get_contents($archivo['tmp_name'], false, null, 0, 4096);
            $valido = $extension === 'mp4'
                ? substr($cabecera, 4, 4) === 'ftyp'
                : substr($cabecera, 0, 4) === "\x1a\x45\xdf\xa3" && strpos($cabecera, 'webm') !== false;
            if (!$valido) {
                throw new InvalidArgumentException("El video seleccionado no es un MP4 o WebM válido.");
            }
        }

        $nombre = 'ACT_' . bin2hex(random_bytes(8)) . '.' . (($extension === 'jpeg' || $extension === 'jfif') ? 'jpg' : $extension);
        if (!move_uploaded_file($archivo['tmp_name'], __DIR__ . '/../assets/images/events/' . $nombre)) {
            throw new RuntimeException("No se pudo guardar el archivo de la actividad.");
        }
        return $nombre;
    }

    /**
     * Guarda las imágenes (hasta 5; la primera es la principal) y el video adjuntos;
     * al menos uno es obligatorio.
     * Devuelve [imagen principal ('Ninguno' si no hay), imágenes adicionales, video (null si no hay)].
     */
    private function prepararArchivosActividad($requerirArchivo = true)
    {
        $imagenes = $this->guardarImagenesActividad(self::MAXIMO_IMAGENES);
        try {
            $video = $this->guardarArchivoActividad('NuevaVideoActividad', 'video', true);
        } catch (Throwable $e) {
            $this->retirarArchivosNuevos($imagenes);
            throw $e;
        }
        if ($requerirArchivo && !$imagenes && $video === null) {
            throw new InvalidArgumentException("Adjunte una imagen (JPG, PNG, WEBP, GIF, etc.), un video MP4/WebM (hasta 10 MB) o ambos.");
        }
        $principal = $imagenes ? array_shift($imagenes) : 'Ninguno';
        return [$principal, $imagenes, $video];
    }

    /**
     * Guarda hasta $maximas imágenes del campo NuevaImagenActividad (input múltiple).
     * Devuelve la lista de nombres guardados (vacía si no se adjuntó ninguna).
     */
    private function guardarImagenesActividad($maximas)
    {
        $archivo = $_FILES['NuevaImagenActividad'] ?? null;
        if (!is_array($archivo) || !is_array($archivo['name'] ?? null)) {
            // Compatible con un campo sin múltiple (una sola imagen).
            if ($maximas < 1 && is_array($archivo) && ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                throw new InvalidArgumentException("La actividad ya tiene el máximo de imágenes; quite alguna antes de agregar otra.");
            }
            $unica = $this->guardarArchivoActividad('NuevaImagenActividad', 'imagen', true);
            return $unica === null ? [] : [$unica];
        }
        $total = 0;
        foreach (($archivo['error'] ?? []) as $codigo) {
            if ($codigo !== UPLOAD_ERR_NO_FILE) {
                $total++;
            }
        }
        if ($total === 0) {
            return [];
        }
        if ($total > $maximas) {
            throw new InvalidArgumentException("Puede adjuntar hasta {$maximas} imágenes por actividad.");
        }
        $guardadas = [];
        try {
            foreach (array_keys($archivo['name']) as $indice) {
                if (($archivo['error'][$indice] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                $guardadas[] = $this->validarYGuardarArchivo([
                    'name' => $archivo['name'][$indice],
                    'type' => $archivo['type'][$indice] ?? '',
                    'tmp_name' => $archivo['tmp_name'][$indice] ?? '',
                    'error' => $archivo['error'][$indice],
                    'size' => $archivo['size'][$indice] ?? 0,
                ], 'imagen');
            }
        } catch (Throwable $e) {
            $this->retirarArchivosNuevos($guardadas);
            throw $e;
        }
        return $guardadas;
    }

    /** Retira del disco varios archivos recién guardados. */
    private function retirarArchivosNuevos(array $nombres)
    {
        foreach ($nombres as $nombre) {
            $this->retirarArchivoNuevo($nombre);
        }
    }

    private function retirarArchivoNuevo($nombre)
    {
        // Solo se retira el archivo generado por esta operación si falla el guardado.
        if (is_string($nombre) && preg_match('/^ACT_[a-f0-9]{16}\.(jpg|jpeg|png|webp|gif|bmp|avif|tif|tiff|mp4|webm)$/D', $nombre)) {
            $ruta = __DIR__ . '/../assets/images/events/' . $nombre;
            if (is_file($ruta)) {
                unlink($ruta);
            }
        }
    }

    private function retirarArchivosActividad($imagen, $video)
    {
        $this->retirarArchivoNuevo($imagen);
        $this->retirarArchivoNuevo($video);
    }

    public function puedePublicarParroquia($usuario)
    {
        $codPer = filter_var($usuario['CodPer'] ?? null, FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]);
        return $codPer !== false && $this->actividadModel->puedePublicarParroquia($codPer);
    }

    public function puedeGestionarActividadParroquia($usuario)
    {
        $codPer = filter_var($usuario['CodPer'] ?? null, FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]);
        return $codPer !== false && $this->actividadModel->puedeGestionarParroquia($codPer);
    }

    public function puedeEditarActividadParroquia($idActividad, $usuario)
    {
        $codPer = filter_var($usuario['CodPer'] ?? null, FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]);
        return $codPer !== false && $this->actividadModel->puedeEditarActividadParroquia((int) $idActividad, $codPer);
    }

    public function puedeEliminarActividadParroquia($idActividad, $usuario)
    {
        $codPer = filter_var($usuario['CodPer'] ?? null, FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]);
        return $codPer !== false && $this->actividadModel->puedeEliminarActividadParroquia((int) $idActividad, $codPer);
    }

    public function registrarActividadParroquia($data, $usuario)
    {
        if (!$this->puedePublicarParroquia($usuario)) {
            throw new DomainException("No tienes permiso para publicar actividades de Parroquia.");
        }

        $data = $this->validarTextosActividad($data);

        $this->validarFechaActividad($data['FechaActividad'] ?? null);
        if (!is_string($data['HoraAct'] ?? null)
            || !preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/D', $data['HoraAct'])) {
            throw new InvalidArgumentException("Indique una hora válida para la actividad.");
        }

        $data['VideoUrl'] = self::normalizarVideoUrl($data['VideoUrl'] ?? null);
        list($data['ImagenActividad'], $data['ImagenesExtras'], $data['VideoActividad']) = $this->prepararArchivosActividad(false);
        if ($data['ImagenActividad'] === 'Ninguno' && empty($data['VideoActividad']) && empty($data['VideoUrl'])) {
            throw new InvalidArgumentException("Adjunte una imagen, un video o pegue un enlace válido de YouTube, TikTok o Facebook.");
        }
        try {
            return $this->actividadModel->registrarActividadParroquia($data, (int) $usuario['CodPer']);
        } catch (Throwable $e) {
            $this->retirarArchivosActividad($data['ImagenActividad'], $data['VideoActividad']);
            $this->retirarArchivosNuevos($data['ImagenesExtras']);
            throw $e;
        }
    }

    public function eliminarActividad($idActividad, $usuario = null)
    {
        $actividad = $this->actividadModel->obtenerActividadPorId((int) $idActividad);
        if (!$actividad) {
            throw new InvalidArgumentException("No se encontró la actividad.");
        }
        if ($usuario !== null && !$this->puedeEliminarActividadParroquia($idActividad, $usuario)) {
            throw new DomainException("No tienes permiso para eliminar esta actividad.");
        }
        return $this->actividadModel->eliminarActividad((int) $idActividad);
    }

    private function eliminarArchivosDeLaActividad(array $actividad)
    {
        // Borra del disco la imagen/video subidos para la actividad (nunca el genérico).
        list(, $video) = self::archivosActividad($actividad);
        foreach (array_merge(self::imagenesActividad($actividad), [$video]) as $archivo) {
            $this->borrarArchivoActividad($archivo);
        }
    }

    /**
     * Una actividad puede editarse mientras esté activa (no concluida) y sea de un
     * tipo gestionable: Parroquia, Confirmación o Primera Comunión.
     */
    public static function esActividadEditable($actividad)
    {
        $tipo = (string) ($actividad['TipoActividad'] ?? '');
        $estado = trim((string) ($actividad['EstActividad'] ?? ''));
        return in_array($tipo, self::TIPOS_ACTIVIDAD, true)
            && strcasecmp($estado, 'Activo') === 0;
    }

    /** Actividad por id (incluye sus imágenes adicionales) o null si no existe. */
    public function obtenerActividadPorId($idActividad)
    {
        return $this->actividadModel->obtenerActividadPorId((int) $idActividad);
    }

    public function actualizarActividadParroquia($data, $usuario)
    {
        $idActividad = filter_var($data['idActividad'] ?? null, FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]);
        if ($idActividad === false) {
            throw new InvalidArgumentException("No se encontró la actividad.");
        }
        if (!$this->puedeEditarActividadParroquia($idActividad, $usuario)) {
            throw new DomainException("No tienes permiso para editar esta actividad de Parroquia.");
        }
        $actual = $this->actividadModel->obtenerActividadPorId($idActividad);
        if (!$actual) {
            throw new InvalidArgumentException("No se encontró la actividad.");
        }
        if (!self::esActividadEditable($actual)) {
            throw new DomainException("Solo puede editar actividades activas que no estén concluidas.");
        }
        // La actividad solo puede quedar con fechas de hoy en adelante.
        $this->validarFechaActividad($data['FechaActividad'] ?? null);
        if (!is_string($data['HoraAct'] ?? null)
            || !preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/D', $data['HoraAct'])) {
            throw new InvalidArgumentException("Indique una hora válida para la actividad.");
        }
        $data['idActividad'] = $idActividad;
        return $this->guardarCambiosActividad($data, $actual, (int) $usuario['CodPer']);
    }

    public function actualizarActividad($data)
    {
        $actual = $this->actividadModel->obtenerActividadPorId((int) ($data['idActividad'] ?? 0));
        if (!$actual) {
            throw new InvalidArgumentException("No se encontró la actividad.");
        }
        $this->validarFechaActividad($data['FechaActividad'] ?? null);
        return $this->guardarCambiosActividad($data, $actual);
    }

    private function guardarCambiosActividad($data, $actual, $codPerParroquia = null)
    {
        $data = $this->validarTextosActividad($data);
        // El estado ya no se elige en el formulario: se deriva de la fecha
        // (la actividad se mantiene Activa hasta que pase su fecha).
        $data['EstActividad'] = ($data['FechaActividad'] ?? '') < self::hoyActividad()
            ? 'Concluido' : 'Activo';
        // Enlace de video: se valida y "Quitar video" también lo elimina.
        $data['VideoUrl'] = self::normalizarVideoUrl($data['VideoUrl'] ?? null);
        if (!empty($data['QuitarVideo'])) {
            $data['VideoUrl'] = null;
        }
        $quitarTodas = !empty($data['QuitarImagen']);
        $idsQuitar = array_map('intval', (array) ($data['QuitarImagenExtra'] ?? []));
        $conservadas = [];
        if (!$quitarTodas) {
            list($principal) = self::archivosActividad($actual);
            if ($principal !== null) {
                $conservadas[] = $principal;
            }
            foreach ($actual['ImagenesExtrasFilas'] ?? [] as $extra) {
                if (!in_array((int) $extra['idActImg'], $idsQuitar, true)) {
                    $conservadas[] = basename($extra['ImagenActividad']);
                }
            }
        }
        $nuevas = $this->guardarImagenesActividad(max(0, self::MAXIMO_IMAGENES - count($conservadas)));
        $nuevoVideo = null;
        try {
            $nuevoVideo = $this->guardarArchivoActividad('NuevaVideoActividad', 'video', true);
            // Añadir imágenes conserva la principal y el orden de las fotos anteriores.
            $finales = array_merge($conservadas, $nuevas);
            if (count($finales) > self::MAXIMO_IMAGENES) {
                throw new InvalidArgumentException("Puede guardar hasta 5 imágenes por actividad.");
            }
            $data['ImagenActividad'] = $finales[0] ?? 'Ninguno';
            $data['ImagenesExtras'] = array_slice($finales, 1);
            $data['VideoActividad'] = $nuevoVideo
                ?? (!empty($data['QuitarVideo']) ? null : ($actual['VideoActividad'] ?? null));
            if (!$finales && empty($data['VideoActividad']) && empty($data['VideoUrl'])) {
                throw new InvalidArgumentException("Conserve o adjunte al menos una imagen o un video.");
            }
            $resultado = $this->actividadModel->actualizarActividadConImagenes($data, $actual, $codPerParroquia);
        } catch (Throwable $e) {
            $this->retirarArchivosNuevos($nuevas);
            $this->retirarArchivoNuevo($nuevoVideo);
            throw $e;
        }
        // Solo retira los archivos antiguos después de guardar toda la galería correctamente.
        foreach (array_diff(self::imagenesActividad($actual), $finales) as $nombre) {
            $this->borrarArchivoActividad($nombre);
        }
        return $resultado;
    }

    /** Borra del disco un archivo de actividad (solo ACT...); nunca el genérico. */
    private function borrarArchivoActividad($nombre)
    {
        if (is_string($nombre) && strpos($nombre, 'ACT') === 0
            && preg_match('/\.(jpg|jpeg|png|webp|gif|bmp|avif|tif|tiff|mp4|webm)$/D', $nombre)) {
            $ruta = __DIR__ . '/../assets/images/events/' . basename($nombre);
            if (is_file($ruta)) {
                unlink($ruta);
            }
        }
    }
}
