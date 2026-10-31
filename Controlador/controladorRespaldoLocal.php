<?php

/**
 * Guarda una copia local de cada certificado emitido dentro del proyecto.
 * La carpeta está protegida por Apache para que los PDF no sean públicos.
 */
class ControladorRespaldoLocal {
    private const DIRECTORIO_BASE = __DIR__ . '/../respaldos/certificados';

    public function guardarPDF($contenidoPDF, $sacramento, $nombrePersona) {
        if (!is_string($contenidoPDF) || $contenidoPDF === '') {
            throw new InvalidArgumentException('El PDF está vacío.');
        }

        $sacramentoSeguro = $this->nombreSeguro($sacramento, 'Certificado');
        $personaSegura = $this->nombreSeguro($nombrePersona, 'Sin_nombre');
        $directorio = self::DIRECTORIO_BASE
            . DIRECTORY_SEPARATOR . $sacramentoSeguro
            . DIRECTORY_SEPARATOR . date('Y')
            . DIRECTORY_SEPARATOR . date('m');

        if (!is_dir($directorio) && !mkdir($directorio, 0775, true) && !is_dir($directorio)) {
            throw new RuntimeException('No se pudo crear la carpeta de respaldo local.');
        }

        $base = date('Y-m-d_H-i-s') . '_' . $sacramentoSeguro . '_' . $personaSegura;
        $ruta = $directorio . DIRECTORY_SEPARATOR . $base . '.pdf';
        $numero = 1;

        while (file_exists($ruta)) {
            $ruta = $directorio . DIRECTORY_SEPARATOR . $base . '_' . $numero . '.pdf';
            $numero++;
        }

        if (file_put_contents($ruta, $contenidoPDF, LOCK_EX) === false) {
            throw new RuntimeException('No se pudo guardar el respaldo local del certificado.');
        }

        return $ruta;
    }

    private function nombreSeguro($texto, $alternativo) {
        $texto = trim((string) $texto);
        if ($texto === '') {
            return $alternativo;
        }

        $convertido = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
        if (is_string($convertido) && $convertido !== '') {
            $texto = $convertido;
        }

        $texto = preg_replace('/[^A-Za-z0-9_-]+/', '_', $texto);
        $texto = trim((string) $texto, '._-');
        return $texto !== '' ? substr($texto, 0, 100) : $alternativo;
    }
}
