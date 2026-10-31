<?php
require_once '../Modelo/modeloPersona.php';

class ControladorPersona {
    private $modelo;
    private $ciPersonaRegistrada = null;
    private $error = null;
    private const TIPOS_DOCUMENTO = ['CI boliviano', 'Pasaporte', 'Documento extranjero', 'Sin documento'];
    private const SACRAMENTOS_SOLICITADOS = [
        'Reserva de Bautizo', 'Reserva de Matrimonio', 'Reserva de misa',
        'Certificado de Bautizo', 'Certificado de Confirmación', 'Certificado de Matrimonio',
        'Registro pastoral',
        // Valores anteriores conservados para los formularios antiguos que todavía los envían.
        'Bautizo', 'Primera Comunión', 'Confirmación', 'Matrimonio', 'Solo registro pastoral'
    ];
    private const DEPARTAMENTOS_BOLIVIA = ['Beni', 'Chuquisaca', 'Cochabamba', 'La Paz', 'Oruro', 'Pando', 'Potosí', 'Santa Cruz', 'Tarija'];

    public function __construct() {
        $this->modelo = new Persona();
    }

    public function crearPersona($data) {
        $this->ciPersonaRegistrada = null;
        $this->error = null;

        try {
            $tipoDocumento = trim($data['TipoDocumento'] ?? 'CI boliviano');
            if (!in_array($tipoDocumento, self::TIPOS_DOCUMENTO, true)) {
                throw new InvalidArgumentException('Seleccione un tipo de documento válido.');
            }

            $numeroDocumento = trim($data['NumeroDocumento'] ?? $data['CiPersona'] ?? $data['ciPersona'] ?? '');
            if ($tipoDocumento !== 'Sin documento' && $numeroDocumento === '') {
                throw new InvalidArgumentException('Ingrese el número de documento.');
            }
            if ($numeroDocumento !== '' && !preg_match('/^[A-Za-z0-9.-]{3,30}$/', $numeroDocumento)) {
                throw new InvalidArgumentException('El número de documento contiene caracteres no permitidos.');
            }

            $ciPersona = $this->generarIdentificadorInterno($tipoDocumento, $numeroDocumento, trim($data['CiPersona'] ?? $data['ciPersona'] ?? ''));
            $nombre = trim($data['Nombre'] ?? $data['nombre'] ?? '');
            $apPaterno = trim($data['ApPaterno'] ?? $data['apPaterno'] ?? '');
            $apMaterno = trim($data['ApMaterno'] ?? $data['apMaterno'] ?? '');
            if ($nombre === '') {
                throw new InvalidArgumentException('Ingrese el nombre de la persona.');
            }
            if ($apPaterno === '' && $apMaterno === '') {
                throw new InvalidArgumentException('Debe ingresar al menos un apellido (paterno o materno).');
            }

            $sexo = trim($data['Sexo'] ?? $data['sexo'] ?? '');
            if (!in_array($sexo, ['Varon', 'Mujer'], true)) {
                throw new InvalidArgumentException('Seleccione el sexo.');
            }
            $fechaNac = trim($data['FechaNac'] ?? $data['fechaNac'] ?? '');
            if (!$this->fechaValida($fechaNac) || $fechaNac > date('Y-m-d')) {
                throw new InvalidArgumentException('Ingrese una fecha de nacimiento válida.');
            }

            $estado = trim($data['Estado_per'] ?? $data['estadoCivil'] ?? $data['estadoPer'] ?? '');
            if ($estado === '') {
                throw new InvalidArgumentException('Seleccione el estado civil.');
            }
            $sacramentoSolicita = trim($data['SacramentoSolicita'] ?? $data['sacramentoSolicita'] ?? 'Registro pastoral');
            if (!in_array($sacramentoSolicita, self::SACRAMENTOS_SOLICITADOS, true)) {
                throw new InvalidArgumentException('Seleccione el trámite o sacramento solicitado.');
            }

            $contacto = trim($data['Contacto'] ?? $data['contacto'] ?? '');
            if ($contacto === '' || !preg_match('/^[0-9+ -]{6,15}$/', $contacto)) {
                throw new InvalidArgumentException('Ingrese un número de contacto válido.');
            }
            $correo = trim($data['Correo'] ?? $data['correo'] ?? '');
            if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException('Ingrese un correo electrónico válido.');
            }

            $nacionalidad = trim($data['Nacionalidad'] ?? 'Boliviana');
            if ($nacionalidad === '') {
                throw new InvalidArgumentException('Ingrese la nacionalidad.');
            }

            $antecedentes = $this->prepararAntecedentes($data, $fechaNac);

            $oficialia = trim($data['Oficialia'] ?? '');
            $libroNacimiento = trim($data['Libro'] ?? '');
            $partidaNacimiento = trim($data['Partida'] ?? '');
            $folio = trim($data['Folio'] ?? '');
            $certificadoNacimiento = '';
            if ($oficialia !== '' || $libroNacimiento !== '' || $partidaNacimiento !== '' || $folio !== '') {
                $certificadoNacimiento = "Ofic.{$oficialia}-Lib.{$libroNacimiento}-Part.{$partidaNacimiento}-Fol.{$folio}";
            }

            $resultado = $this->modelo->crear(
                $ciPersona, $nombre, $apPaterno, $apMaterno, $sexo, $fechaNac,
                trim($data['Direccion'] ?? $data['direccion'] ?? ''), $contacto, $estado,
                $certificadoNacimiento, $correo,
                trim($data['ZonaSector'] ?? $data['zonaSector'] ?? ''),
                trim($data['TelefonoAlternativo'] ?? $data['telefonoAlternativo'] ?? ''),
                $sacramentoSolicita,
                trim($data['Observaciones'] ?? $data['observaciones'] ?? ''),
                $tipoDocumento, $numeroDocumento === '' ? null : $numeroDocumento,
                $nacionalidad, $antecedentes
            );
            if ($resultado) {
                $this->ciPersonaRegistrada = $ciPersona;
            }
            return $resultado;
        } catch (Throwable $e) {
            $this->error = $e instanceof InvalidArgumentException || $e instanceof RuntimeException
                ? $e->getMessage()
                : 'No se pudo registrar la persona. Revise los datos e intente nuevamente.';
            return false;
        }
    }

    private function generarIdentificadorInterno($tipoDocumento, $numeroDocumento, $ciEnviado) {
        if ($tipoDocumento === 'CI boliviano' && $numeroDocumento !== '' && strlen($numeroDocumento) <= 15) {
            return $numeroDocumento;
        }
        if ($ciEnviado !== '' && strlen($ciEnviado) <= 15) {
            return $ciEnviado;
        }
        return 'SP' . strtoupper(substr(bin2hex(random_bytes(8)), 0, 13));
    }

    private function prepararAntecedentes(array $data, $fechaNacimiento) {
        $entrada = $data['Antecedentes'] ?? [];
        if (!is_array($entrada)) {
            return [];
        }

        $mapa = [
            'Bautizo' => 'Bautizo',
            'PrimeraComunion' => 'Primera Comunión',
            'Confirmacion' => 'Confirmación',
            'Matrimonio' => 'Matrimonio anterior'
        ];
        $resultado = [];
        foreach ($mapa as $clave => $nombre) {
            $fila = isset($entrada[$clave]) && is_array($entrada[$clave]) ? $entrada[$clave] : [];
            if (($fila['Realizado'] ?? 'No') !== 'Si') {
                continue;
            }

            $pais = trim($fila['Pais'] ?? 'Bolivia');
            $departamento = trim($fila['Departamento'] ?? '');
            $provincia = trim(($fila['ProvinciaManual'] ?? '') !== '' ? $fila['ProvinciaManual'] : ($fila['Provincia'] ?? ''));
            $municipio = trim(($fila['MunicipioManual'] ?? '') !== '' ? $fila['MunicipioManual'] : ($fila['Municipio'] ?? ''));
            $parroquia = trim($fila['Parroquia'] ?? '');
            if ($pais === '' || $parroquia === '') {
                throw new InvalidArgumentException("Complete el país y la parroquia del antecedente de {$nombre}.");
            }
            if ($pais === 'Bolivia' && !in_array($departamento, self::DEPARTAMENTOS_BOLIVIA, true)) {
                throw new InvalidArgumentException("Seleccione un departamento válido para {$nombre}.");
            }

            $fecha = trim($fila['Fecha'] ?? '');
            if ($fecha !== '' && (!$this->fechaValida($fecha) || $fecha < $fechaNacimiento || $fecha > date('Y-m-d'))) {
                throw new InvalidArgumentException("La fecha del antecedente de {$nombre} no es válida.");
            }

            $documento = $this->guardarDocumentoAntecedente($clave);
            $resultado[] = [
                'Sacramento' => $nombre,
                'Pais' => $pais,
                'Departamento' => $departamento,
                'Provincia' => $provincia,
                'Municipio' => $municipio,
                'Parroquia' => $parroquia,
                'Libro' => $nombre === 'Primera Comunión' ? '' : trim($fila['Libro'] ?? ''),
                'Pagina' => $nombre === 'Primera Comunión' ? '' : trim($fila['Pagina'] ?? ''),
                'Partida' => $nombre === 'Primera Comunión' ? '' : trim($fila['Partida'] ?? ''),
                'Fecha' => $fecha === '' ? null : $fecha,
                'Documento' => $documento,
                'NombreConyuge' => $nombre === 'Matrimonio anterior' ? trim($fila['NombreConyuge'] ?? '') : '',
                'Observacion' => $nombre === 'Matrimonio anterior' ? trim($fila['Observacion'] ?? '') : ''
            ];
        }
        return $resultado;
    }

    private function guardarDocumentoAntecedente($clave) {
        if (empty($_FILES['DocumentoAntecedente']['name'][$clave])) {
            return '';
        }
        $error = $_FILES['DocumentoAntecedente']['error'][$clave] ?? UPLOAD_ERR_NO_FILE;
        if ($error !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('No se pudo recibir uno de los documentos adjuntos.');
        }
        $tmp = $_FILES['DocumentoAntecedente']['tmp_name'][$clave];
        $tamano = (int) ($_FILES['DocumentoAntecedente']['size'][$clave] ?? 0);
        if ($tamano <= 0 || $tamano > 5 * 1024 * 1024) {
            throw new InvalidArgumentException('Cada documento debe pesar como máximo 5 MB.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp);
        $permitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'application/pdf' => 'pdf'];
        if (!isset($permitidos[$mime])) {
            throw new InvalidArgumentException('Los documentos deben ser JPG, PNG o PDF.');
        }

        $directorio = __DIR__ . '/../assets/documentos_persona/';
        if (!is_dir($directorio) && !mkdir($directorio, 0755, true)) {
            throw new RuntimeException('No se pudo preparar la carpeta de documentos.');
        }
        $nombre = strtoupper($clave) . '_' . bin2hex(random_bytes(12)) . '.' . $permitidos[$mime];
        if (!move_uploaded_file($tmp, $directorio . $nombre)) {
            throw new RuntimeException('No se pudo guardar el documento adjunto.');
        }
        return $nombre;
    }

    private function fechaValida($fecha) {
        $objeto = DateTime::createFromFormat('Y-m-d', $fecha);
        return $objeto && $objeto->format('Y-m-d') === $fecha;
    }

    public function obtenerError() { return $this->error; }
    public function obtenerCiPersonaRegistrada() { return $this->ciPersonaRegistrada; }
    public function buscarPersona($identificacion) { return $this->modelo->buscar($identificacion); }
    public function buscarPersonas($termino, $limite = 15) { return $this->modelo->buscarCoincidencias($termino, $limite); }

    public function actualizarPersona($data) {
        return $this->modelo->actualizar($data['CiPersona'], $data['Nombre'], $data['ApPaterno'], $data['ApMaterno'], $data['Sexo'], $data['FechaNac'], $data['Direccion'], $data['Contacto'], $data['Estado_per2'], $data['Correo'] ?? null);
    }

    /**
     * Indica si la persona se registró sin CI real: usando la partida de su
     * certificado de nacimiento como número de documento, o sin documento.
     */
    public function identificacionProvisional($persona) {
        $resultado = ['puede' => false, 'partida' => '', 'motivo' => ''];
        if (!$persona) {
            return $resultado;
        }

        $partida = '';
        if (!empty($persona['id_certificado_nacimiento'])
            && preg_match('/Part\.?\s*([^\-]+)/iu', (string) $persona['id_certificado_nacimiento'], $coincidencia)) {
            $partida = trim($coincidencia[1]);
        }

        $ciActual = trim((string) ($persona['CiPersona'] ?? ''));
        $documento = trim((string) ($persona['NumeroDocumento'] ?? ''));
        if ($partida !== '' && ($ciActual === $partida || $documento === $partida)) {
            return ['puede' => true, 'partida' => $partida, 'motivo' => 'partida'];
        }
        if (($persona['TipoDocumento'] ?? '') === 'Sin documento') {
            return ['puede' => true, 'partida' => '', 'motivo' => 'sin documento'];
        }
        return $resultado;
    }

    public function actualizarIdentificacion($data) {
        $this->error = null;

        try {
            $ciActual = trim((string) ($data['CiPersona'] ?? ''));
            if ($ciActual === '') {
                throw new InvalidArgumentException('No se identificó a la persona.');
            }

            $persona = $this->modelo->buscar($ciActual);
            $disponible = $this->identificacionProvisional($persona);
            if (!$disponible['puede']) {
                throw new RuntimeException('Esta persona ya tiene un CI registrado; no es necesario cambiarlo.');
            }

            $nuevoCi = trim((string) ($data['NuevaCi'] ?? ''));
            if ($nuevoCi === $ciActual) {
                throw new InvalidArgumentException('El nuevo CI debe ser distinto al registrado actualmente.');
            }
            if (!preg_match('/^[A-Za-z0-9.-]{4,15}$/', $nuevoCi)) {
                throw new InvalidArgumentException('Ingrese un CI válido de 4 a 15 caracteres (números, letras o guion).');
            }

            $this->modelo->cambiarIdentificacion($ciActual, $nuevoCi);
            return true;
        } catch (Throwable $e) {
            $this->error = $e instanceof InvalidArgumentException || $e instanceof RuntimeException
                ? $e->getMessage()
                : 'No se pudo actualizar el CI. Revise los datos e intente nuevamente.';
            return false;
        }
    }
}
?>
