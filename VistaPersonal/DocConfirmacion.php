<?php
require_once '../Controlador/controladorSolicitud.php';
require_once('../fpdf/fpdf.php');
require_once('../FPDI-master/src/autoload.php');

$controller = new ControladorInsSacramento();
$certificadoData = [];

use setasign\Fpdi\Fpdi;

$meses = [
    1 => 'Enero',
    2 => 'Febrero',
    3 => 'Marzo',
    4 => 'Abril',
    5 => 'Mayo',
    6 => 'Junio',
    7 => 'Julio',
    8 => 'Agosto',
    9 => 'Septiembre',
    10 => 'Octubre',
    11 => 'Noviembre',
    12 => 'Diciembre'
];

if (isset($_GET['CodIns'])) {
    $codIns = $_GET['CodIns'];
    $certificadoData = $controller->VerCertificadoConfirmacion($codIns);
    if (!$certificadoData) {
        echo "No se encontraron datos para el certificado.";
        exit;
    }
    $certificadoData['NombrePadre'] = trim((string) ($certificadoData['NombrePadreManual'] ?? '')) !== ''
        ? $certificadoData['NombrePadreManual'] : ($certificadoData['NombrePadre'] ?? '');
    $certificadoData['NombreMadre'] = trim((string) ($certificadoData['NombreMadreManual'] ?? '')) !== ''
        ? $certificadoData['NombreMadreManual'] : ($certificadoData['NombreMadre'] ?? '');
    $certificadoData['NombrePadrino'] = trim((string) ($certificadoData['NombrePadrinoManual'] ?? '')) !== ''
        ? $certificadoData['NombrePadrinoManual'] : ($certificadoData['NombrePadrino'] ?? '');
    $certificadoData['NombreMadrina'] = trim((string) ($certificadoData['NombreMadrinaManual'] ?? '')) !== ''
        ? $certificadoData['NombreMadrinaManual'] : ($certificadoData['NombreMadrina'] ?? '');
    $CiNacimiento = $certificadoData['CiPersona'];
    $certificadoExt = $controller->VerCertificadoExtraC($CiNacimiento, 'Celebrante', $codIns);

    $pdf = new Fpdi();
    $pdf->setSourceFile('../assets/Documentos/CertificadoConfirmacion.pdf');
    $tplIdx = $pdf->importPage(1);
    $tamano = $pdf->getTemplateSize($tplIdx);
    $pdf->AddPage($tamano['orientation'], [$tamano['width'], $tamano['height']]);
    $pdf->useTemplate($tplIdx);
    $pdf->SetAutoPageBreak(false);
    $pdf->SetTextColor(0, 0, 0);

    // Fuentes incrustadas para conservar la misma letra al ver o imprimir el PDF.
    $pdf->AddFont('Calibri', '', 'calibri.php', '../assets/fonts/');
    $pdf->AddFont('Calibri', 'B', 'calibrib.php', '../assets/fonts/');
    $pdf->AddFont('ArialBlack', 'B', 'ariblk.php', '../assets/fonts/');

    // Coordenadas en milímetros, sobre la línea base de la plantilla tamaño carta.
    // Ajustar el texto al espacio reservado sin cortar nombres ni perder tildes.
    $escribir = function ($x, $y, $ancho, $valor, $tamano = 12, $estilo = '', $centrado = false, $familia = 'Calibri') use ($pdf) {
        $texto = iconv('UTF-8', 'Windows-1252//TRANSLIT', trim((string) ($valor ?? '')));
        $texto = preg_replace('/\s+/', ' ', $texto);
        if ($texto === '') {
            return;
        }
        $pdf->SetFont($familia, $estilo, $tamano);
        $anchoTexto = $pdf->GetStringWidth($texto);
        if ($anchoTexto > $ancho) {
            $pdf->SetFont($familia, $estilo, $tamano * $ancho / $anchoTexto);
            $anchoTexto = $pdf->GetStringWidth($texto);
        }
        $pdf->Text($centrado ? $x + ($ancho - $anchoTexto) / 2 : $x, $y, $texto);
    };

    // Formato de presentación del certificado; no modifica la ficha de la persona.
    $limpiarTexto = function ($valor) {
        return trim(preg_replace('/\s+/u', ' ', (string) ($valor ?? '')));
    };
    $nombrePropio = function ($valor) use ($limpiarTexto) {
        $texto = preg_replace('/\b(pbro|mons|p|dr|rdo)\.\s*/iu', '$1. ', $limpiarTexto($valor));
        $texto = mb_convert_case(mb_strtolower($texto, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
        // Conservar las partículas de nombres y parroquias: María del Carmen, San Pedro de Sacaba.
        $texto = preg_replace_callback('/(?<=\s)(De|Del|La|Las|Los|Y|E|Da|Das|Do|Dos|Van|Von)(?=\s)/u', function ($parte) {
            return mb_strtolower($parte[0], 'UTF-8');
        }, $texto);
        // Nombres como San Juan Pablo II conservan el número romano.
        return preg_replace_callback('/\b(?:X{0,3}(?:IX|IV|V?I{1,3})|X{1,3}|X{0,3}V)\b/iu', function ($parte) {
            return strtoupper($parte[0]);
        }, $texto);
    };
    $observacion = function ($valor) use ($limpiarTexto) {
        $texto = $limpiarTexto($valor);
        // Normalizar las respuestas habituales sin alterar nombres o siglas de notas libres.
        if (preg_match('/^(?:ningun[ao]|sin (?:observaciones|anotaciones(?: marginales)?))[.\-]*$/iu', $texto)) {
            $texto = mb_strtolower($texto, 'UTF-8');
        }
        return preg_replace_callback('/(^|[.!?]\s+)(\p{L})/u', function ($parte) {
            return $parte[1] . mb_strtoupper($parte[2], 'UTF-8');
        }, $texto);
    };
    $identificador = function ($valor) use ($limpiarTexto) {
        // Los números no se convierten a letras ni pierden ceros; los códigos y romanos van en mayúsculas.
        return mb_strtoupper($limpiarTexto($valor), 'UTF-8');
    };
    // El tratamiento Pbro. ya figura en la plantilla.
    $presbitero = $nombrePropio(preg_replace('/^(?:pbro\.?|presb[ií]tero)\s+/iu', '', $limpiarTexto($certificadoData['PresbiteroEm'] ?? '')));

    $leerFecha = function ($valor) {
        $valor = substr(trim((string) ($valor ?? '')), 0, 10);
        $fecha = DateTimeImmutable::createFromFormat('!Y-m-d', $valor);
        return $fecha && $fecha->format('Y-m-d') === $valor ? $fecha : null;
    };
    $fechaReal = $leerFecha($certificadoData['FechaReal'] ?? '');
    $fechaNacimiento = $leerFecha($certificadoData['FechaNac'] ?? '');
    $fechaEmision = $leerFecha($certificadoData['FechaEmision'] ?? '') ?? new DateTimeImmutable('today');

    if ($certificadoExt) {
        $formatearFecha = function ($fecha) use ($meses) {
            return $fecha ? $fecha->format('j') . '-' . strtolower($meses[(int) $fecha->format('n')]) . '-' . $fecha->format('Y') : '';
        };
        $lugarConfirmacion = $nombrePropio(explode(',', $certificadoData['LugParroquia'] ?? 'Sacaba')[0]);
        $fechaBautizo = $leerFecha($certificadoExt['FechaBau'] ?? '');
        $anios = $fechaNacimiento && $fechaReal && $fechaNacimiento <= $fechaReal
            ? $fechaNacimiento->diff($fechaReal)->y : null;
        $edad = $anios !== null ? $anios . ($anios === 1 ? ' año' : ' años') : '';
        $padrinos = array_filter([
            $nombrePropio($certificadoData['NombrePadrino'] ?? ''),
            $nombrePropio($certificadoData['NombreMadrina'] ?? '')
        ], function ($nombre) { return $nombre !== ''; });

        // Nombre del presbítero centrado en el espacio libre de la plantilla,
        // entre "El Suscrito Pbro." y ", párroco de la Parroquia".
        $escribir(47, 88.1, 54.3, $presbitero, 12, '', true);
        $escribir(147.5, 88.1, 51.5, $nombrePropio($certificadoData['NomParroquia']));
        $escribir(129, 103.6, 10.5, $identificador($certificadoData['NroLibro']), 12, '', true);
        $escribir(152, 103.6, 8.5, $identificador($certificadoData['NroPag']), 12, '', true);
        $escribir(170, 103.6, 8.5, $identificador($certificadoData['NroPart']), 12, '', true);

        // El certificado muestra el nombre como: apellido paterno, apellido materno y nombre.
        $nombreConfirmante = trim(implode(' ', [
            (string) ($certificadoData['ApPaterno'] ?? ''),
            (string) ($certificadoData['ApMaterno'] ?? ''),
            (string) ($certificadoData['Nombre'] ?? ''),
        ]));
        if ($nombreConfirmante === '') {
            $nombreConfirmante = (string) ($certificadoData['Celebrante'] ?? '');
        }
        $escribir(34, 119.7, 144, mb_strtoupper($nombreConfirmante, 'UTF-8'), 13, 'B', false, 'ArialBlack');
        $escribir(28.5, 125.9, 32, $edad);
        $escribir(30, 132.2, 148, $nombrePropio($certificadoData['NombrePadre'] ?? ''));
        $escribir(31, 138.4, 147, $nombrePropio($certificadoData['NombreMadre'] ?? ''));
        $escribir(40, 144.6, 138, implode(' y ', $padrinos));
        $escribir(67.5, 151, 110.5, $nombrePropio($certificadoExt['ParroquiaBau']));
        $escribir(49.5, 157.2, 128.5, $fechaBautizo ? $fechaBautizo->format('d/m/y') : '');
        $escribir(59, 163.4, 119, $fechaReal ? trim($lugarConfirmacion . ' ' . $formatearFecha($fechaReal)) : '');
        $escribir(77, 169.7, 101, $nombrePropio($certificadoData['CelebrantePres']));

        $escribir(49.5, 201.4, 128.5, $observacion($certificadoData['Observacion']));
        $escribir(84, 218, 8.5, $fechaEmision->format('j'), 12, '', true);
        $escribir(99.5, 218, 27.5, strtolower($meses[(int) $fechaEmision->format('n')]), 12, '', true);
        $escribir(134, 218, 12.5, $fechaEmision->format('Y'), 12, '', true);
        // El mismo presbítero figura como suscrito y en la firma.
        $escribir(94.5, 245.7, 60, $presbitero, 11);
    } else {
        $escribir(25, 119.7, 166, 'Primero debe registrar todos los documentos necesarios.', 11, 'B', true);
    }

    $tmpFile = tempnam(sys_get_temp_dir(), 'cert_conf_');
    $pdf->Output('F', $tmpFile);
    $pdfContent = file_get_contents($tmpFile);

    if ($certificadoExt && is_string($pdfContent)) {
        try {
            require_once __DIR__ . '/../Controlador/controladorRespaldoLocal.php';
            $respaldoLocal = new ControladorRespaldoLocal();
            $respaldoLocal->guardarPDF($pdfContent, 'Confirmacion', $certificadoData['Celebrante']);
        } catch (Throwable $e) {
            error_log('No se pudo guardar el respaldo local de Confirmación: ' . $e->getMessage());
        }

    }

    @unlink($tmpFile);
    $pdf->Output('I', $certificadoData['Celebrante'] . '-Certificado-Confirmacion.pdf');
} else {
    echo "No se recibieron datos.";
}
