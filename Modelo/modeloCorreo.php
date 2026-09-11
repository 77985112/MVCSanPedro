<?php

class ModeloCorreo
{
    private $config;

    public function __construct($config = null)
    {
        $this->config = $config ?? require __DIR__ . '/../Conexion/configCorreo.php';
    }

    public function enviarConfirmacionReserva($destinatario, $tipo, $fecha, $hora)
    {
        return $this->enviarAvisoReserva($destinatario, $tipo, $fecha, $hora, false);
    }

    public function enviarCancelacionReserva($destinatario, $tipo, $fecha, $hora)
    {
        return $this->enviarAvisoReserva($destinatario, $tipo, $fecha, $hora, true);
    }

    private function enviarAvisoReserva($destinatario, $tipo, $fecha, $hora, $cancelada)
    {
        if (!is_string($destinatario) || !filter_var($destinatario, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        if (empty($this->config['api_key']) ||
            !filter_var($this->config['remitente'] ?? '', FILTER_VALIDATE_EMAIL)) {
            error_log('Brevo reservas: falta configurar la clave o el remitente.');
            return false;
        }

        $escapar = function ($valor) {
            return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        };
        $fechaObjeto = DateTimeImmutable::createFromFormat('!Y-m-d', (string) $fecha);
        $fechaTexto = $fechaObjeto ? $fechaObjeto->format('d/m/Y') : $fecha;
        $html = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"></head>'
            . '<body style="font-family:Arial,sans-serif;color:#243247;background:#f5f6f8;padding:24px">'
            . '<div style="max-width:560px;margin:auto;background:white;padding:28px;border-radius:8px">'
            . '<h2>Parroquia San Pedro de Sacaba</h2><h3>' . ($cancelada ? 'Reserva cancelada' : 'Reserva registrada') . '</h3>'
            . '<p>' . ($cancelada ? 'Se ha cancelado tu reserva prevista para la siguiente fecha y hora:' : 'Se ha registrado tu reserva con los siguientes datos:') . '</p>'
            . '<p><strong>Celebración:</strong> ' . $escapar($tipo) . '</p>'
            . '<p><strong>Fecha:</strong> ' . $escapar($fechaTexto) . '</p>'
            . '<p><strong>Hora:</strong> ' . $escapar(substr((string) $hora, 0, 5)) . '</p>'
            . '<p>Para consultar requisitos o solicitar cambios, comunícate con la parroquia.</p>'
            . '<p>Gracias por tu confianza.</p></div></body></html>';

        $payload = json_encode([
            'sender' => ['name' => $this->config['nombre'], 'email' => $this->config['remitente']],
            'to' => [['email' => $destinatario]],
            'subject' => ($cancelada ? 'Reserva cancelada - ' : 'Registro de reserva - ') . $tipo,
            'htmlContent' => $html,
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        $respuesta = $this->solicitarEnvio($payload);
        $contenido = json_decode($respuesta['body'], true);
        if ($respuesta['error'] === 0 && $respuesta['status'] === 201 &&
            is_array($contenido) && !empty($contenido['messageId'])) {
            // Aceptado para envío; no confirma la entrega en la bandeja del destinatario.
            return true;
        }
        // No registrar claves, destinatarios ni el cuerpo de la respuesta del proveedor.
        error_log('Brevo reservas: HTTP ' . $respuesta['status'] . ', cURL ' . $respuesta['error']);
        return false;
    }

    protected function solicitarEnvio($payload)
    {
        if (!function_exists('curl_init')) {
            throw new RuntimeException('cURL no disponible');
        }
        $curl = curl_init('https://api.brevo.com/v3/smtp/email');
        if ($curl === false) {
            throw new RuntimeException('No se pudo iniciar cURL');
        }
        try {
            curl_setopt_array($curl, [
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'accept: application/json',
                    'content-type: application/json',
                    'api-key: ' . $this->config['api_key'],
                ],
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => 12,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_FOLLOWLOCATION => false,
            ]);
            $body = curl_exec($curl);
            return [
                'body' => $body === false ? '' : $body,
                'status' => (int) curl_getinfo($curl, CURLINFO_HTTP_CODE),
                'error' => curl_errno($curl),
            ];
        } finally {
            curl_close($curl);
        }
    }
}
