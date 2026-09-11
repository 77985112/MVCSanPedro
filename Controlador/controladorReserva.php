<?php
require_once '../modelo/modeloReserva.php';
require_once '../Modelo/modeloSacramentoFecha.php';
require_once __DIR__ . '/../Modelo/modeloCorreo.php';

class ControladorReserva
{
    private $reservaModel;
    private $correoModel;
    private $avisoReserva = [];

    public function __construct()
    {
        $this->reservaModel = new Reserva();
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
        if (!isset($data['Realizacion']) || !in_array($data['Realizacion'], ['Privado', 'Comunitario', 'Otro'], true)) {
            $this->avisoReserva = ['tipo' => 'danger', 'mensaje' => 'Selecciona un tipo de reserva de bautizo válido.'];
            return false;
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
        return $this->guardarConConfirmacion($data, 'reservarBautizo', 'Bautizo');
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
        return $this->guardarConConfirmacion($data, 'reservarMatrimonio', 'Matrimonio');
    }

    public function reservarMisa($data)
    {
        if (!isset($data['Realizacion']) || !in_array($data['Realizacion'], ['Privado', 'Comunitario', 'Otro'], true)) {
            $this->avisoReserva = ['tipo' => 'danger', 'mensaje' => 'Selecciona un tipo de reserva de misa válido.'];
            return false;
        }
        if ($data['Realizacion'] === 'Otro') {
            $detalle = isset($data['RealizacionOtro']) && is_string($data['RealizacionOtro'])
                ? trim($data['RealizacionOtro']) : '';
            if ($detalle === '' || mb_strlen($detalle, 'UTF-8') > 30) {
                $this->avisoReserva = ['tipo' => 'danger', 'mensaje' => 'Especifica el tipo de misa (máximo 30 caracteres).'];
                return false;
            }
            $data['Realizacion'] = $detalle;
        }
        return $this->guardarConConfirmacion($data, 'reservarMisa', 'Misa');
    }

    public function obtenerAvisoReserva()
    {
        return $this->avisoReserva;
    }

    private function guardarConConfirmacion($data, $metodo, $tipo)
    {
        $this->avisoReserva = [];
        
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

    public function registrarDocumentoExt($data)
    {
        return $this->reservaModel->registrarDocumentoExt($data);
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
