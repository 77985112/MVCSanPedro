<?php
require_once '../Modelo/modeloReserva.php';
require_once '../Modelo/modeloSacramentoFecha.php';
require_once __DIR__ . '/../Modelo/modeloCorreo.php';

class ControladorReserva
{
    private $reservaModel;
    private $correoModel;
    private $avisoReserva = [];
    private $errorDocumento = null;

    public function __construct()
    {
        $this->reservaModel = new Reserva();
    }

    public function obtenerCelebrantesActivos($incluirDiaconos = false)
    {
        return $this->reservaModel->obtenerCelebrantesActivos($incluirDiaconos);
    }

    private function verificarConflictoCelebracion($fecha, $hora)
    {
        $sacramentoModel = new ModeloSacramento();
        $fechasSac = $sacramentoModel->getAllSacramentos();
        
        $fechaReserva = strtotime($fecha);
        $horaReserva = strtotime($hora);
        
        foreach ($fechasSac as $sac) {
            if ($sac['ActividadSac'] === 'Comunión' || $sac['ActividadSac'] === 'Confirmación') {
                $fechaInicio = strtotime(date('Y-m-d', strtotime($sac['Fecha_Ini'])));
                $fechaFin = strtotime(date('Y-m-d', strtotime($sac['Fecha_Fin'])));
                $horaInicio = strtotime(date('H:i:s', strtotime($sac['Fecha_Ini'])));
                $horaFin = strtotime(date('H:i:s', strtotime($sac['Fecha_Fin'])));
                
                if ($fechaReserva >= $fechaInicio && $fechaReserva <= $fechaFin) {
                    if ($horaReserva >= $horaInicio && $horaReserva <= $horaFin) {
                        return [
                            'conflicto' => true,
                            'sacramento' => $sac['Sacramento'],
                            'actividad' => $sac['ActividadSac'],
                            'hora_inicio' => date('H:i', strtotime($sac['Fecha_Ini'])),
                            'hora_fin' => date('H:i', strtotime($sac['Fecha_Fin']))
                        ];
                    }
                }
            }
        }
        
        return ['conflicto' => false];
    }

    public function reservarBautizo($data)
    {
        if (!isset($data['Realizacion']) || !in_array($data['Realizacion'], ['Comunitario', 'Otro'], true)) {
            $this->avisoReserva = ['tipo' => 'danger', 'mensaje' => 'Selecciona un tipo de reserva de bautizo válido.'];
            return false;
        }
        if ($data['Realizacion'] === 'Comunitario') {
            $fechaTexto = isset($data['FechaReal']) && is_string($data['FechaReal']) ? $data['FechaReal'] : '';
            $fecha = DateTime::createFromFormat('!Y-m-d', $fechaTexto);
            $hora = isset($data['HoraReal']) && is_string($data['HoraReal'])
                ? substr(trim($data['HoraReal']), 0, 5) : '';
            $diaSemana = $fecha ? (int) $fecha->format('N') : 0;
            $horarioValido = ($diaSemana === 4 && $hora === '14:30') ||
                ($diaSemana === 7 && $hora === '12:30');
            if (!$horarioValido) {
                $this->avisoReserva = ['tipo' => 'danger', 'mensaje' =>
                    'El bautizo comunitario solo puede reservarse el jueves a las 14:30 o el domingo a las 12:30.'];
                return false;
            }
            $zonaHoraria = new DateTimeZone('America/La_Paz');
            $momentoReserva = DateTime::createFromFormat('!Y-m-d H:i', $fechaTexto . ' ' . $hora, $zonaHoraria);
            if (!$momentoReserva || $momentoReserva <= new DateTime('now', $zonaHoraria)) {
                $this->avisoReserva = ['tipo' => 'danger', 'mensaje' =>
                    'Selecciona un jueves o domingo cuyo horario todavía no haya pasado.'];
                return false;
            }
        }
        if ($data['Realizacion'] === 'Otro') {
            $detalle = isset($data['RealizacionOtro']) && is_string($data['RealizacionOtro'])
                ? trim($data['RealizacionOtro']) : '';
            if ($detalle === '' || mb_strlen($detalle, 'UTF-8') > 30) {
                $this->avisoReserva = ['tipo' => 'danger', 'mensaje' => 'Especifica el tipo de bautizo (máximo 30 caracteres).'];
                return false;
            }
            $data['Realizacion'] = $detalle;
        }
        return $this->guardarConConfirmacion($data, 'reservarBautizo', 'Bautizo', false);
    }

    public function reservarMatrimonio($data)
    {
        if (!isset($data['Realizacion']) || !in_array($data['Realizacion'], ['Privado', 'Comunitario', 'Otro'], true)) {
            $this->avisoReserva = ['tipo' => 'danger', 'mensaje' => 'Selecciona un tipo de reserva de matrimonio válido.'];
            return false;
        }
        if ($data['Realizacion'] === 'Otro') {
            $detalle = isset($data['RealizacionOtro']) && is_string($data['RealizacionOtro'])
                ? trim($data['RealizacionOtro']) : '';
            if ($detalle === '' || mb_strlen($detalle, 'UTF-8') > 30) {
                $this->avisoReserva = ['tipo' => 'danger', 'mensaje' => 'Describe cómo será el matrimonio (máximo 30 caracteres).'];
                return false;
            }
            $data['Realizacion'] = $detalle;
        }
        return $this->guardarConConfirmacion($data, 'reservarMatrimonio', 'Matrimonio', false);
    }

    public function reservarMisa($data)
    {
        if (!isset($data['Realizacion']) || !in_array($data['Realizacion'], ['Privado', 'Comunitario', 'Otro'], true)) {
            $this->avisoReserva = ['tipo' => 'danger', 'mensaje' => 'Selecciona un tipo de reserva de misa válido.'];
            return false;
        }
        $permiteDiacono = $data['Realizacion'] === 'Otro';
        if ($data['Realizacion'] === 'Otro') {
            $detalle = isset($data['RealizacionOtro']) && is_string($data['RealizacionOtro'])
                ? trim($data['RealizacionOtro']) : '';
            if ($detalle === '' || mb_strlen($detalle, 'UTF-8') > 30) {
                $this->avisoReserva = ['tipo' => 'danger', 'mensaje' => 'Especifica el tipo de misa (máximo 30 caracteres).'];
                return false;
            }
            $data['Realizacion'] = $detalle;
        }
        return $this->guardarConConfirmacion($data, 'reservarMisa', 'Misa', $permiteDiacono);
    }

    public function obtenerAvisoReserva()
    {
        return $this->avisoReserva;
    }

    private function guardarConConfirmacion($data, $metodo, $tipo, $permiteDiacono)
    {
        $this->avisoReserva = [];

        $hora = isset($data['HoraReal']) && is_string($data['HoraReal'])
            ? substr(trim($data['HoraReal']), 0, 5) : '';
        if (!preg_match('/^\\d{2}:\\d{2}$/', $hora) || $hora < '05:00' || $hora > '21:59') {
            $this->avisoReserva = ['tipo' => 'danger', 'mensaje' => 'La hora debe estar entre las 5:00 a. m. y las 9:59 p. m.'];
            return false;
        }
        $data['HoraReal'] = $hora;

        $codPerCelebrante = filter_var($data['CodPerCelebrante'] ?? null, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1]
        ]);
        if (!$codPerCelebrante || !$this->reservaModel->esCelebranteActivo($codPerCelebrante, $permiteDiacono)) {
            $mensaje = $permiteDiacono
                ? 'Selecciona un párroco, sacerdote o diácono activo como ministro del sacramento.'
                : 'Selecciona un párroco o sacerdote activo como ministro del sacramento.';
            $this->avisoReserva = ['tipo' => 'danger', 'mensaje' => $mensaje];
            return false;
        }
        $data['CodPerCelebrante'] = $codPerCelebrante;

        $identificacionReservante = $tipo === 'Misa'
            ? trim(is_string($data['CiPersonademas'] ?? null) ? $data['CiPersonademas'] : '')
            : trim(is_string($data['CiReservante'] ?? null) ? $data['CiReservante'] : '');
        if ($identificacionReservante === '') {
            $this->avisoReserva = ['tipo' => 'danger', 'mensaje' => 'Ingrese el CI o documento de quien realiza la reserva.'];
            return false;
        }
        $reservante = $this->reservaModel->buscarPersonaPorIdentificacion($identificacionReservante);
        if (!$reservante) {
            $this->avisoReserva = ['tipo' => 'danger', 'mensaje' => 'El reservante no está registrado. Registre primero a la persona o verifique su documento.'];
            return false;
        }
        $data['CiReservante'] = $reservante['CiPersona'];
        if ($tipo === 'Misa') {
            $data['CiPersonademas'] = $reservante['CiPersona'];
        } else {
            $data['Quien'] = trim(implode(' ', array_filter([
                $reservante['Nombre'], $reservante['ApPaterno'], $reservante['ApMaterno']
            ])));
        }
        
        $conflicto = $this->verificarConflictoCelebracion($data['FechaReal'], $data['HoraReal']);
        if ($conflicto['conflicto']) {
            $this->avisoReserva = ['tipo' => 'danger', 'mensaje' => 
                'No se puede guardar la reserva. La fecha y hora seleccionada está reservada para ' . 
                $conflicto['actividad'] . ' de ' . $conflicto['sacramento'] . 
                ' (de ' . $conflicto['hora_inicio'] . ' a ' . $conflicto['hora_fin'] . ').'];
            return false;
        }

        if ($this->reservaModel->existeReservaEnFechaHora($data['FechaReal'], $data['HoraReal'], $data['Realizacion'])) {
            $this->avisoReserva = ['tipo' => 'warning', 'mensaje' => 'La fecha y hora seleccionadas ya están ocupadas. Solo pueden compartir horario las reservas comunitarias entre sí.'];
            return false;
        }
        
        $enviar = isset($data['EnviarConfirmacion']) && $data['EnviarConfirmacion'] === '1';
        $correo = isset($data['CorreoSolicitante']) && is_string($data['CorreoSolicitante'])
            ? trim($data['CorreoSolicitante']) : '';
        if ((isset($data['CorreoSolicitante']) && !is_string($data['CorreoSolicitante'])) ||
            (($enviar || $correo !== '') && (strlen($correo) > 254 || !filter_var($correo, FILTER_VALIDATE_EMAIL)))) {
            $this->avisoReserva = ['tipo' => 'danger', 'mensaje' => 'No se guardó la reserva. Introduce un correo válido; si no deseas avisos, deja el correo vacío y desmarca la confirmación.'];
            return false;
        }
        $data['CorreoSolicitante'] = $correo === '' ? null : $correo;
        try {
            $guardada = $this->reservaModel->$metodo($data);
        } catch (DomainException $e) {
            $this->avisoReserva = ['tipo' => 'warning', 'mensaje' => $e->getMessage()];
            return false;
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() !== 1062 || strpos($e->getMessage(), 'uq_reserva_fecha_hora') === false) throw $e;
            $this->avisoReserva = ['tipo' => 'warning', 'mensaje' => 'La fecha y hora seleccionadas ya están ocupadas por otra reserva. Elige otro horario.'];
            return false;
        }
        if (!$guardada) {
            $this->avisoReserva = ['tipo' => 'danger', 'mensaje' => 'No se pudo guardar la reserva.'];
            return false;
        }
        $this->avisoReserva = ['tipo' => 'success', 'mensaje' => 'Reserva guardada. No se solicitó envío de correo.'];
        if ($enviar) {
            $aceptado = false;
            try {
                if ($this->correoModel === null) {
                    $this->correoModel = new ModeloCorreo();
                }
                $aceptado = $this->correoModel->enviarConfirmacionReserva(
                    $correo, $tipo, $data['FechaReal'], $data['HoraReal']
                );
            } catch (Throwable $e) {
                error_log('Brevo reservas: no se pudo completar la solicitud de correo.');
            }
            $this->avisoReserva = $aceptado
                ? ['tipo' => 'success', 'mensaje' => 'Reserva guardada. Confirmación aceptada para envío por correo.']
                : ['tipo' => 'warning', 'mensaje' =>
                    'Reserva guardada. No se pudo confirmar el envío del correo. No vuelvas a registrar la reserva; consulta al administrador.'];
        }
        // Mantener el resultado booleano de los métodos existentes, aunque falle el correo.
        return true;
    }

    public function modificarReservaBautizo($data)
    {
        return $this->modificarConAvisoCancelacion($data, 'modificarReservaBautizo', 'Bautizo');
    }

    public function modificarReservaMatrimonio($data)
    {
        return $this->modificarConAvisoCancelacion($data, 'modificarReservaMatrimonio', 'Matrimonio');
    }

    public function modificarReservaMisa($data)
    {
        return $this->modificarConAvisoCancelacion($data, 'modificarReservaMisa', 'Misa');
    }

    private function modificarConAvisoCancelacion($data, $metodo, $tipo)
    {
        $this->avisoReserva = [];
        if (!isset($data['EstadoRes']) || !in_array($data['EstadoRes'], ['Reservado', 'Cancelado', 'Completado'], true) ||
            !isset($data['CodRes']) || !filter_var($data['CodRes'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
            $this->avisoReserva = ['tipo' => 'danger', 'mensaje' => 'No se modificó la reserva. Revisa los datos enviados.'];
            return false;
        }
        if (array_key_exists('CorreoSolicitante', $data)) {
            $correo = is_string($data['CorreoSolicitante']) ? trim($data['CorreoSolicitante']) : null;
            if ($correo === null || ($correo !== '' && (strlen($correo) > 254 || !filter_var($correo, FILTER_VALIDATE_EMAIL)))) {
                $this->avisoReserva = ['tipo' => 'danger', 'mensaje' => 'No se modificó la reserva. Introduce un correo válido o deja el campo vacío.'];
                return false;
            }
            $data['CorreoSolicitante'] = $correo === '' ? null : $correo;
        }
        try {
            $guardada = $this->reservaModel->$metodo($data);
        } catch (DomainException $e) {
            $this->avisoReserva = ['tipo' => 'warning', 'mensaje' => $e->getMessage()];
            return false;
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() !== 1062 || strpos($e->getMessage(), 'uq_reserva_fecha_hora') === false) throw $e;
            $this->avisoReserva = ['tipo' => 'warning', 'mensaje' => 'La fecha y hora seleccionadas ya están ocupadas por otra reserva. Elige otro horario.'];
            return false;
        }
        if (!$guardada) {
            $this->avisoReserva = ['tipo' => 'warning', 'mensaje' => 'No se modificó la reserva. Comprueba si ya fue cancelada o completada.'];
            return false;
        }
        $this->avisoReserva = ['tipo' => 'success', 'mensaje' => 'Reserva modificada.'];
        if ($data['EstadoRes'] !== 'Cancelado') return true;

        $cancelacion = $this->reservaModel->obtenerCancelacionConfirmada();
        if (!$cancelacion || empty($cancelacion['CorreoSolicitante'])) {
            $this->avisoReserva = ['tipo' => 'warning', 'mensaje' => 'Reserva cancelada. No se envió aviso porque no tiene correo del solicitante.'];
            return true;
        }
        $aceptado = false;
        try {
            if ($this->correoModel === null) $this->correoModel = new ModeloCorreo();
            $aceptado = $this->correoModel->enviarCancelacionReserva(
                $cancelacion['CorreoSolicitante'], $tipo, $cancelacion['FechaReal'], $cancelacion['HoraReal']
            );
        } catch (Throwable $e) {
            error_log('Brevo reservas: no se pudo completar el aviso de cancelación.');
        }
        $this->avisoReserva = $aceptado
            ? ['tipo' => 'success', 'mensaje' => 'Reserva cancelada. Aviso de cancelación aceptado para envío por correo.']
            : ['tipo' => 'warning', 'mensaje' => 'Reserva cancelada. No se pudo confirmar el envío del aviso por correo. No vuelvas a cancelar; consulta al administrador.'];
        return true;
    }



    public function registrarDocumento($data)
    {
        return $this->reservaModel->registrarDocumento($data);
    }

    public function registrarDocumentoExt($data, $files = null)
    {
        $this->errorDocumento = null;

        try {
            $codIns = (int) ($data['CodIns'] ?? 0);
            $codTDoc = (int) ($data['CodTDoc'] ?? 0);
            $dequien = trim((string) ($data['Dequien'] ?? ''));
            $fechaPres = trim((string) ($data['FechaPres'] ?? ''));

            if ($codIns <= 0) {
                throw new InvalidArgumentException('No se identificó el certificado.');
            }
            if ($codTDoc <= 0) {
                throw new InvalidArgumentException('Seleccione el tipo de documento.');
            }
            if ($dequien === '') {
                throw new InvalidArgumentException('Seleccione a quién pertenece el documento.');
            }
            if ($fechaPres === '' || !$this->fechaValida($fechaPres)) {
                throw new InvalidArgumentException('Ingrese una fecha de presentación válida.');
            }

            $archivo = $this->guardarArchivo($files['Archivo'] ?? null, $this->carpetaSacramento($codIns));
            $codPres = $this->reservaModel->registrarDocumentoConArchivo($data, $archivo);
            if ($codPres === false) {
                throw new RuntimeException('No se pudo registrar el documento.');
            }

            if (trim((string) ($data['ParroquiaBau'] ?? '')) !== '') {
                $this->reservaModel->guardarDocPresBau($codPres, [
                    'ParroquiaBau' => trim((string) $data['ParroquiaBau']),
                    'NroLibro' => trim((string) ($data['NroLibro'] ?? '')),
                    'NroPag' => trim((string) ($data['NroPag'] ?? '')),
                    'NroPart' => trim((string) ($data['NroPart'] ?? '')),
                    'FechaBau' => trim((string) ($data['FechaBau'] ?? '')),
                ]);
            }

            return true;
        } catch (Throwable $e) {
            $this->errorDocumento = $e instanceof InvalidArgumentException || $e instanceof RuntimeException
                ? $e->getMessage()
                : 'No se pudo registrar el documento.';
            return false;
        }
    }

    public function registrarDocumentoRequerido($data, $files)
    {
        $this->errorDocumento = null;

        try {
            $codIns = (int) ($data['CodIns'] ?? 0);
            $codTDoc = (int) ($data['CodTDoc'] ?? 0);
            $dequien = trim((string) ($data['Dequien'] ?? ''));
            if ($codIns <= 0 || $codTDoc <= 0 || $dequien === '') {
                throw new InvalidArgumentException('No se identificó el documento requerido.');
            }

            $archivo = $this->guardarArchivo($files['Archivo'] ?? null, $this->carpetaSacramento($codIns));
            if ($archivo === '') {
                throw new InvalidArgumentException('Seleccione una imagen o un PDF.');
            }

            $resultado = $this->reservaModel->actualizarArchivoDocumento(
                $codIns,
                $codTDoc,
                $dequien,
                $archivo
            );
            if ($resultado === false) {
                throw new RuntimeException('No se pudo guardar el archivo del documento.');
            }

            return true;
        } catch (Throwable $e) {
            $this->errorDocumento = $e instanceof InvalidArgumentException || $e instanceof RuntimeException
                ? $e->getMessage()
                : 'No se pudo guardar el archivo del documento.';
            return false;
        }
    }

    public function obtenerErrorDocumento() { return $this->errorDocumento; }
    public function obtenerInscripcionSacramento($codIns) { return $this->reservaModel->obtenerInscripcionSacramento($codIns); }
    public function obtenerPersonasSacramento($codIns) { return $this->reservaModel->obtenerPersonasSacramento($codIns); }
    public function obtenerDocumentosPorInscripcion($codIns) { return $this->reservaModel->obtenerDocumentosPorInscripcion($codIns); }
    public function obtenerTiposDocumento() { return $this->reservaModel->obtenerTiposDocumento(); }

    /**
     * Arma el checklist de documentos requeridos según el sacramento, con los
     * datos que ya existen en el sistema y los archivos adjuntos.
     * $situaciones permite forzar el documento condicional del padrino (5 o 8).
     */
    public function construirRequisitos($codIns, $situaciones = [])
    {
        $inscripcion = $this->reservaModel->obtenerInscripcionSacramento($codIns);
        if (!$inscripcion) {
            return null;
        }

        $personas = $this->reservaModel->obtenerPersonasSacramento($codIns);
        $documentos = $this->reservaModel->obtenerDocumentosPorInscripcion($codIns);
        $porRol = [];
        foreach ($personas as $persona) {
            $porRol[$persona['Rol']] = $persona;
        }

        $codSac = (int) $inscripcion['CodSac'];
        $secciones = [];

        if ($codSac === 1) {
            $principal = $porRol['Bautizado'] ?? $porRol['Celebrante'] ?? null;
            $items = [];
            $items[] = $this->crearRequisito('nacimiento', 'Certificado de Nacimiento', 'Original y fotocopia', $principal, [2], $documentos, 'nacimiento');

            foreach (['Padrino' => 'padrino', 'Madrina' => 'madrina'] as $rol => $clave) {
                $persona = $porRol[$rol] ?? null;
                if (!$persona) {
                    continue;
                }
                $items[] = $this->crearRequisito('ci_' . $clave, 'Carnet de Identidad', '', $persona, [1], $documentos, 'ci');
                $aplica = isset($situaciones[$clave]) ? (int) $situaciones[$clave] : $this->situacionPadrino($persona, $documentos, $rol);
                $items[] = $this->crearRequisito('mat_' . $clave, 'Certificado de Matrimonio Religioso', 'Solo si está casado/a por la Iglesia', $persona, [8], $documentos, 'ninguno', $aplica === 8, $clave);
                $items[] = $this->crearRequisito('conf_' . $clave, 'Certificado de Confirmación', $rol === 'Padrino' ? 'Solo si es soltero' : 'Solo si es soltera', $persona, [5], $documentos, 'ninguno', $aplica === 5, $clave);
            }

            $secciones[] = ['titulo' => 'Requisitos para Bautizo', 'modo' => 'lista', 'items' => $items];
        } elseif ($codSac === 3) {
            $confirmante = $porRol['Confirmante'] ?? $porRol['Celebrante'] ?? null;
            $secciones[] = [
                'titulo' => 'Requisitos para Confirmación',
                'modo' => 'lista',
                'items' => [
                    $this->crearRequisito('nacimiento', 'Certificado de Nacimiento', 'Fotocopia', $confirmante, [2], $documentos, 'nacimiento'),
                    $this->crearRequisito('bautismo', 'Certificado de Bautismo', 'Fotocopia', $confirmante, [3], $documentos, 'bautizo'),
                ],
            ];
        } elseif ($codSac === 4) {
            $novio = $porRol['Novio'] ?? null;
            $novia = $porRol['Novia'] ?? null;
            $definicion = [
                ['Certificado de Nacimiento', [2], 'nacimiento'],
                ['Certificado de Bautizo', [3], 'bautizo'],
                ['Certificado de Confirmación', [5], 'confirmacion'],
                ['Certificado de Matrimonio Civil', [6], 'ninguno'],
                ['Carnet de Identidad', [1], 'ci'],
                ['Certificado de Cursillos Pre-matrimoniales', [9], 'ninguno'],
            ];

            $filas = [];
            foreach ($definicion as $indice => $definicionDocumento) {
                $filas[] = [
                    'documento' => $definicionDocumento[0],
                    'celdas' => [
                        $this->crearRequisito('novio_' . $indice, $definicionDocumento[0], '', $novio, $definicionDocumento[1], $documentos, $definicionDocumento[2]),
                        $this->crearRequisito('novia_' . $indice, $definicionDocumento[0], '', $novia, $definicionDocumento[1], $documentos, $definicionDocumento[2]),
                    ],
                ];
            }
            $secciones[] = ['titulo' => 'Los novios deben presentar', 'modo' => 'pareja', 'filas' => $filas];

            $padrinos = $porRol['Padrino'] ?? $porRol['Madrina'] ?? null;
            $secciones[] = [
                'titulo' => 'Los padrinos deben presentar',
                'modo' => 'lista',
                'items' => [
                    $this->crearRequisito('mat_padrinos', 'Certificado de Matrimonio Religioso', 'Basta el de la pareja de padrinos', $padrinos, [8], $documentos, 'ninguno', true, null, ['Padrino', 'Madrina']),
                    $this->crearRequisito('ci_padrino', 'Carnet de Identidad', '', $porRol['Padrino'] ?? null, [1], $documentos, 'ci'),
                    $this->crearRequisito('ci_madrina', 'Carnet de Identidad', '', $porRol['Madrina'] ?? null, [1], $documentos, 'ci'),
                ],
            ];

            $secciones[] = [
                'titulo' => 'Dos testigos',
                'modo' => 'lista',
                'items' => [
                    $this->crearRequisito('ci_testigo_novio', 'Carnet de Identidad', 'Debe conocer al novio al menos 5 años y no ser familiar', $porRol['Testigo Novio'] ?? null, [1], $documentos, 'ci'),
                    $this->crearRequisito('ci_testigo_novia', 'Carnet de Identidad', 'Debe conocer a la novia al menos 5 años y no ser familiar', $porRol['Testigo Novia'] ?? null, [1], $documentos, 'ci'),
                ],
            ];
        }

        $total = 0;
        $adjuntos = 0;
        $reutilizables = 0;
        foreach ($secciones as $seccion) {
            $items = $seccion['items'] ?? [];
            if ($seccion['modo'] === 'pareja') {
                $items = [];
                foreach ($seccion['filas'] as $fila) {
                    foreach ($fila['celdas'] as $celda) {
                        $items[] = $celda;
                    }
                }
            }
            foreach ($items as $item) {
                if (!$item['aplica']) {
                    continue;
                }
                $total++;
                if (!empty($item['archivo'])) {
                    $adjuntos++;
                }
                if ($item['datos'] !== '') {
                    $reutilizables++;
                }
            }
        }

        return [
            'inscripcion' => $inscripcion,
            'personas' => $personas,
            'documentos' => $documentos,
            'secciones' => $secciones,
            'total' => $total,
            'adjuntos' => $adjuntos,
            'reutilizables' => $reutilizables,
        ];
    }

    private function situacionPadrino($persona, $documentos, $rol)
    {
        foreach ([8, 5] as $tipo) {
            foreach ($documentos as $documento) {
                if ((int) $documento['CodTDoc'] === $tipo
                    && $documento['Dequien'] === $rol
                    && !empty($documento['Archivo'])) {
                    return $tipo;
                }
            }
        }

        $estado = strtolower(trim((string) ($persona['Estado_per'] ?? '')));
        return strpos($estado, 'casad') === 0 ? 8 : 5;
    }

    private function crearRequisito($clave, $documento, $detalle, $persona, $tipos, $documentos, $fuenteDatos, $aplica = true, $grupoCondicional = null, $rolesBusqueda = null)
    {
        $rol = $persona['Rol'] ?? '';
        $roles = $rolesBusqueda ?: [$rol];
        if (!$rolesBusqueda && in_array($rol, ['Bautizado', 'Celebrante', 'Confirmante'], true)) {
            $roles = ['Bautizado', 'Celebrante', 'Confirmante'];
        }

        $archivo = null;
        $codPres = null;
        $codificDoc = '';
        $tipoUsado = $tipos[0] ?? null;
        $filaDocumento = null;

        foreach ($tipos as $tipo) {
            foreach ($documentos as $documentoPresentado) {
                if ((int) $documentoPresentado['CodTDoc'] !== (int) $tipo) {
                    continue;
                }
                if (!in_array($documentoPresentado['Dequien'], $roles, true)) {
                    continue;
                }
                $tipoUsado = (int) $tipo;
                $codPres = (int) $documentoPresentado['CodPres'];
                $codificDoc = (string) ($documentoPresentado['CodificDoc'] ?? '');
                $filaDocumento = $documentoPresentado;
                if (!empty($documentoPresentado['Archivo'])) {
                    $archivo = $documentoPresentado['Archivo'];
                }
                break 2;
            }
        }

        $info = $this->datosRequisito($fuenteDatos, $persona);
        if ($info['texto'] === '' && $filaDocumento) {
            if ($fuenteDatos === 'nacimiento') {
                if ($codificDoc !== '') {
                    $info['texto'] = 'Código ' . $codificDoc;
                }
            } else {
                $partes = [];
                if (trim((string) ($filaDocumento['ParroquiaBau'] ?? '')) !== '') {
                    $partes[] = trim($filaDocumento['ParroquiaBau']);
                }
                if (trim((string) ($filaDocumento['NrLib'] ?? '')) !== '') {
                    $partes[] = 'Lib.' . trim($filaDocumento['NrLib']);
                }
                if (trim((string) ($filaDocumento['NrPag'] ?? '')) !== '') {
                    $partes[] = 'Pág.' . trim($filaDocumento['NrPag']);
                }
                if (trim((string) ($filaDocumento['NrPart'] ?? '')) !== '') {
                    $partes[] = 'Part.' . trim($filaDocumento['NrPart']);
                }
                if (!empty($filaDocumento['FechaBau'])) {
                    $partes[] = date('d/m/Y', strtotime($filaDocumento['FechaBau']));
                }
                if ($partes) {
                    $info['texto'] = implode(' · ', $partes);
                } elseif ($codificDoc !== '') {
                    $info['texto'] = 'Código ' . $codificDoc;
                }
            }
        }

        if (!$aplica) {
            $estado = 'noaplica';
        } elseif ($archivo) {
            $estado = 'adjunto';
        } elseif ($info['texto'] !== '') {
            $estado = 'registrado';
        } else {
            $estado = 'pendiente';
        }

        return [
            'clave' => $clave,
            'documento' => $documento,
            'detalle' => $detalle,
            'rol' => $rol,
            'persona' => $persona ? trim(implode(' ', array_filter([$persona['Nombre'], $persona['ApPaterno'], $persona['ApMaterno']]))) : '',
            'ci' => $persona ? trim((string) ($persona['NumeroDocumento'] ?? '')) : '',
            'datos' => $info['texto'],
            'tipo' => $tipoUsado,
            'codPres' => $codPres,
            'codificDoc' => $codificDoc,
            'archivo' => $archivo,
            'aplica' => (bool) $aplica,
            'grupoCondicional' => $grupoCondicional,
            'estado' => $estado,
            'generarCodIns' => $info['codIns'],
            'generarPagina' => $info['pagina'],
        ];
    }

    private function datosRequisito($fuente, $persona)
    {
        $vacio = ['texto' => '', 'codIns' => null, 'pagina' => null];
        if (!$persona) {
            return $vacio;
        }

        if ($fuente === 'nacimiento') {
            return ['texto' => trim((string) ($persona['id_certificado_nacimiento'] ?? '')), 'codIns' => null, 'pagina' => null];
        }

        if ($fuente === 'ci') {
            $documento = trim((string) ($persona['NumeroDocumento'] ?? ''));
            return ['texto' => $documento !== '' ? 'CI ' . $documento : '', 'codIns' => null, 'pagina' => null];
        }

        if ($fuente === 'bautizo' || $fuente === 'confirmacion') {
            $codSac = $fuente === 'bautizo' ? 1 : 3;
            $registro = $this->reservaModel->obtenerSacramentoPersona($persona['CiPersona'], $codSac);
            if (!$registro) {
                return $vacio;
            }

            $partes = [];
            if (trim((string) ($registro['ParroquiaBau'] ?? '')) !== '') {
                $partes[] = trim($registro['ParroquiaBau']);
            }
            if (trim((string) ($registro['NrLib'] ?? '')) !== '') {
                $partes[] = 'Lib.' . trim($registro['NrLib']);
            }
            if (trim((string) ($registro['NrPag'] ?? '')) !== '') {
                $partes[] = 'Pág.' . trim($registro['NrPag']);
            }
            if (trim((string) ($registro['NrPart'] ?? '')) !== '') {
                $partes[] = 'Part.' . trim($registro['NrPart']);
            }
            if (!empty($registro['FechaBau'])) {
                $partes[] = date('d/m/Y', strtotime($registro['FechaBau']));
            }

            $texto = $partes
                ? implode(' · ', $partes)
                : ($fuente === 'bautizo' ? 'Bautizo registrado en el sistema' : 'Confirmación registrada en el sistema');

            return [
                'texto' => $texto,
                'codIns' => (int) $registro['CodIns'],
                'pagina' => $fuente === 'bautizo' ? 'DocBautizo.php' : 'DocConfirmacion.php',
            ];
        }

        return $vacio;
    }

    private function carpetaSacramento($codIns)
    {
        $inscripcion = $this->reservaModel->obtenerInscripcionSacramento($codIns);
        $carpetas = [1 => 'bautizo', 3 => 'confirmacion', 4 => 'matrimonio'];
        return $carpetas[(int) ($inscripcion['CodSac'] ?? 0)] ?? 'otros';
    }

    private function guardarArchivo($archivo, $subcarpeta = '')
    {
        if (!is_array($archivo) || ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return '';
        }
        if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('No se pudo recibir el archivo. Intente nuevamente.');
        }

        $tamano = (int) ($archivo['size'] ?? 0);
        if ($tamano <= 0 || $tamano > 5 * 1024 * 1024) {
            throw new InvalidArgumentException('El archivo debe pesar como máximo 5 MB.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($archivo['tmp_name']);
        $permitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'application/pdf' => 'pdf'];
        if (!isset($permitidos[$mime])) {
            throw new InvalidArgumentException('Solo se permiten archivos JPG, PNG o PDF.');
        }

        $subcarpeta = strtolower(preg_replace('/[^a-z]/i', '', (string) $subcarpeta));
        $directorio = __DIR__ . '/../assets/documentos_presentados/'
            . ($subcarpeta !== '' ? $subcarpeta . '/' : '');
        if (!is_dir($directorio) && !mkdir($directorio, 0755, true)) {
            throw new RuntimeException('No se pudo preparar la carpeta de documentos.');
        }

        $nombre = 'DOC_' . bin2hex(random_bytes(12)) . '.' . $permitidos[$mime];
        if (!move_uploaded_file($archivo['tmp_name'], $directorio . $nombre)) {
            throw new RuntimeException('No se pudo guardar el archivo.');
        }

        return ($subcarpeta !== '' ? $subcarpeta . '/' : '') . $nombre;
    }

    private function fechaValida($fecha)
    {
        $objeto = DateTime::createFromFormat('Y-m-d', $fecha);
        return $objeto && $objeto->format('Y-m-d') === $fecha;
    }


    public function obtenerReservaBautizo($codRes)
    {
        return $this->reservaModel->obtenerReservaBautizo($codRes);
    }

    public function obtenerReservaMatrimonio($codRes)
    {
        return $this->reservaModel->obtenerReservaMatrimonio($codRes);
    }

    public function obtenerReservaMisa($codRes)
    {
        return $this->reservaModel->obtenerReservaMisa($codRes);
    }




    public function getReservasByFecha($fecha)
    {
        $reservas = $this->reservaModel->getReservasByFecha($fecha);
        
        // Integrar Fechas de Sacramentos
        $sacramentoModel = new ModeloSacramento();
        $fechasSac = $sacramentoModel->getAllSacramentos();

        foreach ($fechasSac as $sac) {
            $start = strtotime($sac['Fecha_Ini']);
            $end = strtotime($sac['Fecha_Fin']);
            $check = strtotime($fecha);
            
            $add_event = false;
            if ($sac['ActividadSac'] === 'Inscripción') {
                // Para inscripciones, se verifica si la fecha está en el rango
                if (date('Y-m-d', $check) >= date('Y-m-d', $start) && date('Y-m-d', $check) <= date('Y-m-d', $end)) {
                    $add_event = true;
                }
            } elseif ($sac['ActividadSac'] === 'Comunión' || $sac['ActividadSac'] === 'Confirmación') {
                // Para celebraciones, solo se muestra en la fecha de inicio y fin
                if (date('Y-m-d', $check) == date('Y-m-d', $start) || date('Y-m-d', $check) == date('Y-m-d', $end)) {
                    $add_event = true;
                }
            }

            if ($add_event) {
                $horaInicio = date('H:i', $start);
                $horaFin = date('H:i', $end);
                $duracionHoras = round((strtotime($sac['Fecha_Fin']) - strtotime($sac['Fecha_Ini'])) / 3600, 1);
                
                $reservas[] = [
                    'HoraReal' => $horaInicio,
                    'HoraFin' => $horaFin,
                    'DuracionHoras' => $duracionHoras,
                    'Realizacion' => 'Comunitario',
                    'tipocel' => $sac['ActividadSac'] . ' ' . $sac['Sacramento'],
                    'EstadoRes' => 'Especial',
                    'CodRes' => null,
                    'CodIns' => null
                ];
            }
        }

        echo json_encode($reservas);
    }

    public function getReservasByMes($mes, $year)
    {
        $reservas = $this->reservaModel->getReservasByMes($mes, $year);

        // Integrar Fechas de Sacramentos para resaltar en el calendario
        $sacramentoModel = new ModeloSacramento();
        $fechasSac = $sacramentoModel->getAllSacramentos();

        foreach ($fechasSac as $sac) {
            $start = strtotime($sac['Fecha_Ini']);
            $end = strtotime($sac['Fecha_Fin']);
            
            if ($sac['ActividadSac'] === 'Inscripción') {
                // Para inscripciones, se marca todo el rango
                $current = $start;
                while ($current <= $end) {
                    if (date('n', $current) == $mes && date('Y', $current) == $year) {
                        $horaInicio = date('H:i', $start);
                        $horaFin = date('H:i', $end);
                        $reservas[] = [
                            'dia' => date('j', $current),
                            'tipocel' => $sac['ActividadSac'] . ' ' . $sac['Sacramento'],
                            'HoraReal' => $horaInicio,
                            'HoraFin' => $horaFin
                        ];
                    }
                    $current = strtotime('+1 day', $current);
                }
            } elseif ($sac['ActividadSac'] === 'Comunión' || $sac['ActividadSac'] === 'Confirmación') {
                // Para celebraciones, solo se marcan el inicio y el fin
                $horaInicio = date('H:i', $start);
                $horaFin = date('H:i', $end);
                $duracionHoras = round((strtotime($sac['Fecha_Fin']) - strtotime($sac['Fecha_Ini'])) / 3600, 1);
                
                if (date('n', $start) == $mes && date('Y', $start) == $year) {
                    $reservas[] = [
                        'dia' => date('j', $start),
                        'tipocel' => $sac['ActividadSac'] . ' ' . $sac['Sacramento'],
                        'HoraReal' => $horaInicio,
                        'HoraFin' => $horaFin,
                        'DuracionHoras' => $duracionHoras
                    ];
                }
                if (date('Y-m-d', $start) != date('Y-m-d', $end) && date('n', $end) == $mes && date('Y', $end) == $year) {
                    $reservas[] = [
                        'dia' => date('j', $end),
                        'tipocel' => $sac['ActividadSac'] . ' ' . $sac['Sacramento'],
                        'HoraReal' => $horaInicio,
                        'HoraFin' => $horaFin,
                        'DuracionHoras' => $duracionHoras
                    ];
                }
            }
        }

        echo json_encode($reservas);
    }
}

if (isset($_GET['fecha'])) {
    $fecha = $_GET['fecha'];
    $controller = new ControladorReserva();
    $controller->getReservasByFecha($fecha);
} elseif (isset($_GET['mes']) && isset($_GET['year'])) {
    $mes = $_GET['mes'];
    $year = $_GET['year'];
    $controller = new ControladorReserva();
    $controller->getReservasByMes($mes, $year);
}
