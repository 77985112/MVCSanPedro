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
    $certificadoData = $controller->VerCertificadoBautizo($codIns);
    if (!$certificadoData) {
        echo "No se encontraron datos para el certificado.";
        exit;
    }
    $CiNacimiento = $certificadoData['CiPersona'];
    $certificadoExt = $controller->VerCertificadoExtra($CiNacimiento, $codIns);

    $pdf = new Fpdi();
    $pdf->setSourceFile('../assets/Documentos/CertificadoBautizo.pdf');
    $tplIdx = $pdf->importPage(1);
    $tamano = $pdf->getTemplateSize($tplIdx);
    $pdf->AddPage($tamano['orientation'], [$tamano['width'], $tamano['height']]);
    $pdf->useTemplate($tplIdx);
    $pdf->SetAutoPageBreak(false);
    $pdf->SetTextColor(0, 0, 0);

    // Times New Roman en negrita, incrustada para conservar el estilo del modelo.
    $pdf->AddFont('TimesNewRoman', 'B', 'timesbd.php', '../assets/fonts/');

    // Coordenadas en milímetros, sobre la línea base de la plantilla tamaño carta.
    // Ajustar el texto al espacio reservado sin cortar nombres ni perder tildes.
    $escribir = function ($x, $y, $ancho, $valor, $tamano = 11, $estilo = 'B', $centrado = false) use ($pdf) {
        $texto = iconv('UTF-8', 'Windows-1252//TRANSLIT', trim((string) ($valor ?? '')));
        $texto = preg_replace('/\s+/', ' ', $texto);
        if ($texto === '') {
            return;
        }
        $pdf->SetFont('TimesNewRoman', $estilo, $tamano);
        $anchoTexto = $pdf->GetStringWidth($texto);
        if ($anchoTexto > $ancho) {
            $pdf->SetFont('TimesNewRoman', $estilo, $tamano * $ancho / $anchoTexto);
            $anchoTexto = $pdf->GetStringWidth($texto);
        }
        $pdf->Text($centrado ? $x + ($ancho - $anchoTexto) / 2 : $x, $y, $texto);
    };

    // La parroquia puede continuar en la línea siguiente, como en el modelo sin puntos.
    $escribirParroquia = function ($x, $y, $ancho, $xContinuacion, $valor, $tamano) use ($pdf, $escribir) {
        $palabras = preg_split('/\s+/u', trim((string) $valor), -1, PREG_SPLIT_NO_EMPTY);
        if (!$palabras) {
            return;
        }
        $pdf->SetFont('TimesNewRoman', 'B', $tamano);
        $primera = '';
        $usadas = 0;
        foreach ($palabras as $palabra) {
            $prueba = $primera === '' ? $palabra : $primera . ' ' . $palabra;
            $anchoTexto = $pdf->GetStringWidth(iconv('UTF-8', 'Windows-1252//TRANSLIT', $prueba));
            if ($primera !== '' && $anchoTexto > $ancho) {
                break;
            }
            $primera = $prueba;
            $usadas++;
        }
        $escribir($x, $y, $ancho, $primera, $tamano);
        if ($usadas < count($palabras)) {
            $escribir($xContinuacion, $y + 6.3, 199 - $xContinuacion, implode(' ', array_slice($palabras, $usadas)), $tamano);
        }
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

    // Fechas escritas con letra (día y año), como en el modelo del certificado.
    $numeroALetras = function ($n, $apocopar = false) use (&$numeroALetras) {
        $n = (int) $n;
        if ($n <= 0 || $n > 9999) {
            return (string) $n;
        }
        $u = [
            1 => 'uno', 2 => 'dos', 3 => 'tres', 4 => 'cuatro', 5 => 'cinco',
            6 => 'seis', 7 => 'siete', 8 => 'ocho', 9 => 'nueve', 10 => 'diez',
            11 => 'once', 12 => 'doce', 13 => 'trece', 14 => 'catorce', 15 => 'quince',
            16 => 'dieciséis', 17 => 'diecisiete', 18 => 'dieciocho', 19 => 'diecinueve',
            20 => 'veinte', 21 => 'veintiuno', 22 => 'veintidós', 23 => 'veintitrés',
            24 => 'veinticuatro', 25 => 'veinticinco', 26 => 'veintiséis',
            27 => 'veintisiete', 28 => 'veintiocho', 29 => 'veintinueve',
            30 => 'treinta', 40 => 'cuarenta', 50 => 'cincuenta', 60 => 'sesenta',
            70 => 'setenta', 80 => 'ochenta', 90 => 'noventa',
        ];
        if ($apocopar) {
            $u[1] = 'un';
            $u[21] = 'veintiún';
        }
        if ($n < 30) {
            return $u[$n];
        }
        if ($n < 100) {
            $decena = intdiv($n, 10) * 10;
            $unidad = $n % 10;
            return $u[$decena] . ($unidad ? ' y ' . $u[$unidad] : '');
        }
        if ($n >= 1000) {
            $miles = intdiv($n, 1000);
            $resto = $n % 1000;
            $texto = $miles === 1 ? 'mil' : $numeroALetras($miles, true) . ' mil';
            return $resto ? $texto . ' ' . $numeroALetras($resto, $apocopar) : $texto;
        }
        $centenas = [
            1 => 'ciento', 2 => 'doscientos', 3 => 'trescientos', 4 => 'cuatrocientos',
            5 => 'quinientos', 6 => 'seiscientos', 7 => 'setecientos', 8 => 'ochocientos',
            9 => 'novecientos',
        ];
        $centena = intdiv($n, 100);
        $resto = $n % 100;
        $texto = ($centena === 1) ? ($resto === 0 ? 'cien' : 'ciento') : $centenas[$centena];
        return $resto ? $texto . ' ' . $numeroALetras($resto, $apocopar) : $texto;
    };
    $anioALetras = function ($anio) use ($numeroALetras) {
        $anio = (int) $anio;
        if ($anio >= 1000 && $anio < 2000) {
            $resto = $anio - 1000;
            return 'mil' . ($resto ? ' ' . $numeroALetras($resto) : '');
        }
        if ($anio >= 2000 && $anio < 3000) {
            $resto = $anio - 2000;
            return 'dos mil' . ($resto ? ' ' . $numeroALetras($resto) : '');
        }
        return $numeroALetras($anio);
    };

    // Nombre del bautizado: Apellido Paterno Apellido Materno Nombre (modelo del certificado).
    $nombreBautizado = trim(implode(' ', [
        trim((string) ($certificadoData['ApPaterno'] ?? '')),
        trim((string) ($certificadoData['ApMaterno'] ?? '')),
        trim((string) ($certificadoData['Nombre'] ?? '')),
    ]));
    if ($nombreBautizado === '') {
        $nombreBautizado = (string) ($certificadoData['NombreBautizado'] ?? '');
    }

    $nombreBautizado = mb_strtoupper($nombreBautizado, 'UTF-8');
    $mayusculas = function ($valor) use ($limpiarTexto) {
        return mb_strtoupper($limpiarTexto($valor), 'UTF-8');
    };

    if ($certificadoExt) {
        $parroquia = $nombrePropio($certificadoData['NomParroquia'] ?? 'San Pedro de Sacaba');

        $escribirParroquia(87.5, 53.5, 111.5, 42, $parroquia, 10.5);
        // El lugar de la parroquia («Sacaba, Cochabamba») ya no se imprime.
        $escribir(65.5, 70.3, 88, $presbitero);
        $escribir(70.5, 82.4, 5.5, $identificador($certificadoData['NroLibro']), 11, 'B', true);
        $escribir(156.5, 82.4, 9, $identificador($certificadoData['NroPag']), 11, 'B', true);
        $escribir(184, 82.4, 9.5, $identificador($certificadoData['NroPart']), 11, 'B', true);

        $escribir(21.5, 100.2, 173, $nombreBautizado, 13, 'B', true);
        $escribirParroquia(73.4, 112, 125.6, 21.5, $parroquia, 11);

        if ($fechaReal) {
            // Huecos de la plantilla ya ampliados: el día admite «treinta y uno»
            // a 11 pt sin comprimir el texto.
            $escribir(30.84, 125.2, 22.5, $numeroALetras((int) $fechaReal->format('j'), true), 11, 'B', true);
            $escribir(82.12, 125.2, 19.5, strtolower($meses[(int) $fechaReal->format('n')]), 11, 'B', true);
            $escribir(116.95, 125.2, 33, $anioALetras((int) $fechaReal->format('Y')), 11);
        }

        $escribir(47, 130.8, 77, $mayusculas($certificadoData['CelebrantePres']));
        $escribir(47, 136.8, 77, $nombreBautizado);
        $escribir(47, 143.2, 63, $mayusculas($certificadoData['LugarNac']));

        if ($fechaNacimiento) {
            $escribir(32.14, 149.5, 32.56, $numeroALetras((int) $fechaNacimiento->format('j')), 11, 'B', true);
            $escribir(71.62, 149.5, 30, strtolower($meses[(int) $fechaNacimiento->format('n')]), 11, 'B', true);
            $escribir(116.95, 149.5, 33, $anioALetras((int) $fechaNacimiento->format('Y')), 11);
        }

        $escribir(47, 155.6, 77, $mayusculas($certificadoData['NombrePadre']));
        $escribir(47, 161.8, 77, $mayusculas($certificadoData['NombreMadre']));
        $escribir(71.5, 173.4, 95, $mayusculas($certificadoData['NombrePadrino']));
        $escribir(71.5, 179.8, 95, $mayusculas($certificadoData['NombreMadrina']));

        $escribir(69, 186.4, 19.5, $identificador($certificadoExt['ParroquiaBau']), 11);
        $escribir(102.5, 186.4, 7, $identificador($certificadoExt['NrLib']), 11, 'B', true);
        $escribir(125.5, 186.4, 7.5, $identificador($certificadoExt['NrPart']), 11, 'B', true);
        // «Certifico(Firmado y Rubricado)»: puede ser un presbítero distinto del que certifica.
        $firmRubricado = $nombrePropio($certificadoData['CertFirmado'] ?? '');
        $escribir(79.5, 196.8, 60, $firmRubricado !== '' ? $firmRubricado : $presbitero);
        $escribir(50, 208.1, 148, $observacion($certificadoData['Observacion']), 10.5);

        $escribir(38, 254, 31, $fechaEmision->format('d-m-Y'));
        // Firma al pie: el mismo presbítero que certifica.
        $escribir(96.7, 260.7, 52, $presbitero, 9.5);
    } else {
        $escribir(25, 97, 166, 'Primero debe registrar todos los documentos necesarios.', 11, 'B', true);
    }

    $tmpFile = tempnam(sys_get_temp_dir(), 'cert_baut_');
    $pdf->Output('F', $tmpFile);
    $pdfContent = file_get_contents($tmpFile);

    if ($certificadoExt && is_string($pdfContent)) {
        try {
            require_once __DIR__ . '/../Controlador/controladorRespaldoLocal.php';
            $respaldoLocal = new ControladorRespaldoLocal();
            $respaldoLocal->guardarPDF($pdfContent, 'Bautizo', $certificadoData['NombreBautizado']);
        } catch (Throwable $e) {
            error_log('No se pudo guardar el respaldo local de Bautizo: ' . $e->getMessage());
        }

        try {
            require_once __DIR__ . '/../Controlador/controladorGoogleDrive.php';
            $drive = new ControladorGoogleDrive();
            if ($drive->estaConectado()) {
                $drive->respaldarPDF($pdfContent, 'Bautizo', $certificadoData['NombreBautizado']);
            }
        } catch (Throwable $e) {
            error_log('No se pudo guardar el respaldo de Bautizo en Drive: ' . $e->getMessage());
        }
    }

    @unlink($tmpFile);
    $pdf->Output('I', $certificadoData['NombreBautizado'] . '-Certificado-Bautizo.pdf');

} else {
    echo "No se recibieron datos.";
}
