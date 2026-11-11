<?php
require_once '../Controlador/controladorSolicitud.php';
require_once('../fpdf/fpdf.php');
require_once('../FPDI-master/src/autoload.php');

use setasign\Fpdi\Fpdi;

$controller = new ControladorInsSacramento();
$codIns = filter_var($_GET['CodIns'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$certificadoData = $codIns ? $controller->VerCertificadoMatrimonio($codIns) : null;
if (!$certificadoData) {
    http_response_code(404);
    exit('No se encontraron datos para el certificado de matrimonio.');
}
$certificadoExt = $controller->VerCertificadoExtraB($certificadoData['CiNovio'], 'Novio', $codIns);
$certificadoExtN = $controller->VerCertificadoExtraB($certificadoData['CiNovia'], 'Novia', $codIns);

$leerFecha = function ($valor) {
    $valor = trim((string) ($valor ?? ''));
    $fecha = DateTimeImmutable::createFromFormat('!Y-m-d', $valor);
    return $fecha && $fecha->format('Y-m-d') === $valor ? $fecha : null;
};
$fechaReal = $leerFecha($certificadoData['FechaReal'] ?? '');
$fechaEmision = $leerFecha($certificadoData['FechaEmision'] ?? '');
$nacimientoNovio = $leerFecha($certificadoData['fechaNovio'] ?? '');
$nacimientoNovia = $leerFecha($certificadoData['fechaNovia'] ?? '');
$faltantes = [];
if (!$fechaReal || !$fechaEmision || $fechaReal > $fechaEmision) {
    $faltantes[] = 'Fechas válidas de matrimonio y emisión.';
}
foreach (['novio' => $nacimientoNovio, 'novia' => $nacimientoNovia] as $persona => $nacimiento) {
    if (!$nacimiento || ($fechaReal && $nacimiento > $fechaReal)) {
        $faltantes[] = 'Fecha de nacimiento del ' . $persona . ' en Persona.';
    }
}
foreach (['novio' => $certificadoExt, 'novia' => $certificadoExtN] as $persona => $documento) {
    if (trim((string) ($documento['ParroquiaBau'] ?? '')) === '') {
        $faltantes[] = 'Parroquia de bautismo del ' . $persona . ' en su documento de bautizo (botón Doc).';
    }
}
foreach (['NombreNovio' => 'Nombre del novio', 'NombreNovia' => 'Nombre de la novia',
          'NroLibro' => 'Libro', 'NroPag' => 'Folio', 'NroPart' => 'Número de partida',
          'CelebrantePres' => 'Sacerdote que celebra', 'PresbiteroEm' => 'Presbítero que certifica'] as $campo => $etiqueta) {
    if (trim((string) ($certificadoData[$campo] ?? '')) === '') {
        $faltantes[] = $etiqueta . '.';
    }
}
foreach (['EstadoCivilNovio' => ['Soltero', 'Viudo'], 'EstadoCivilNovia' => ['Soltera', 'Viuda']] as $campo => $opciones) {
    if (!in_array($certificadoData[$campo] ?? '', $opciones, true)) {
        $faltantes[] = 'Estado civil ' . ($campo === 'EstadoCivilNovio' ? 'del novio' : 'de la novia') . ' antes del matrimonio, en el detalle de la partida.';
    }
}
if ($faltantes) {
    http_response_code(422);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<h2>Complete los datos antes de emitir el certificado</h2><ul>';
    foreach ($faltantes as $faltante) {
        echo '<li>' . htmlspecialchars($faltante, ENT_QUOTES, 'UTF-8') . '</li>';
    }
    echo '</ul><a href="VistaRegistradoMatrimonio.php?CodIns=' . (int) $codIns . '">Volver al certificado</a>';
    exit;
}

$pdf = new Fpdi();
$pdf->setSourceFile('../assets/Documentos/CertificadoMatrimonio.pdf');
$tplIdx = $pdf->importPage(1);
$tamano = $pdf->getTemplateSize($tplIdx);
$pdf->AddPage($tamano['orientation'], [$tamano['width'], $tamano['height']]);
$pdf->useTemplate($tplIdx);
$pdf->SetAutoPageBreak(false);
$pdf->SetTextColor(0, 0, 0);
// Se importa la plantilla completa y se escribe por encima de sus puntos,
// sin cubrir ni borrar las líneas originales.
$pdf->SetDrawColor(0, 0, 0);
$pdf->SetLineWidth(0.25);
$limpiar = function ($valor) {
    return trim(preg_replace('/\s+/u', ' ', (string) ($valor ?? '')));
};
$nombrePropio = function ($valor) use ($limpiar) {
    $texto = mb_convert_case(mb_strtolower($limpiar($valor), 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    $texto = preg_replace_callback('/(?<=\s)(De|Del|La|Las|Los|Y|E|Da|Das|Do|Dos|Van|Von)(?=\s)/u', function ($parte) {
        return mb_strtolower($parte[0], 'UTF-8');
    }, $texto);
    return preg_replace_callback('/\b(?:X{0,3}(?:IX|IV|V?I{1,3})|X{1,3}|X{0,3}V)\b/iu', function ($parte) {
        return strtoupper($parte[0]);
    }, $texto);
};
$nombre = function ($valor) use ($limpiar) {
    $valor = $limpiar($valor);
    return mb_strtoupper($valor, 'UTF-8');
};
$escribir = function ($x, $y, $ancho, $valor, $tamano = 10.5, $centrado = false) use ($pdf, $limpiar) {
    $valor = $limpiar($valor);
    if ($valor === '') {
        // Una raya indica que el dato no consta; los puntos permanecen visibles.
        $largo = min(28, $ancho - 2);
        $inicio = $x + ($ancho - $largo) / 2;
        $pdf->Line($inicio, $y - 1.3, $inicio + $largo, $y - 1.3);
        return;
    }
    $texto = iconv('UTF-8', 'Windows-1252//TRANSLIT', $valor);
    $pdf->SetFont('Arial', 'B', $tamano);
    $anchoTexto = $pdf->GetStringWidth($texto);
    if ($anchoTexto > $ancho) {
        $pdf->SetFont('Arial', 'B', $tamano * $ancho / $anchoTexto);
        $anchoTexto = $pdf->GetStringWidth($texto);
    }
    $pdf->Text($centrado ? $x + ($ancho - $anchoTexto) / 2 : $x, $y - 0.9, $texto);
};
$meses = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
          'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$escribir(81.7, 74.66, 20, mb_strtoupper($certificadoData['NroLibro'], 'UTF-8'), 11, true);
$escribir(117, 74.66, 14.5, mb_strtoupper($certificadoData['NroPag'], 'UTF-8'), 11, true);
$escribir(144.2, 74.66, 13.6, mb_strtoupper($certificadoData['NroPart'], 'UTF-8'), 11, true);
$pareja = $nombre($certificadoData['NombreNovio']) . ' con ' . $nombre($certificadoData['NombreNovia']);
$pdf->SetFont('Arial', 'B', 10);
if ($pdf->GetStringWidth(iconv('UTF-8', 'Windows-1252//TRANSLIT', $pareja)) > 160.5) {
    // El espacio superior permite dos renglones sin reducir los nombres a letra diminuta.
    $escribir(28, 90.83, 160.5, $nombre($certificadoData['NombreNovio']), 11, true);
    $escribir(28, 95.83, 160.5, 'con ' . $nombre($certificadoData['NombreNovia']), 11, true);
} else {
    $escribir(28, 95.83, 160.5, $pareja, 12, true);
}
$escribirContrayente = function ($x, $y, $ancho, $valor) use ($pdf, $escribir, $nombre) {
    $texto = $nombre($valor);
    $pdf->SetFont('Arial', 'B', 8.5);
    if ($pdf->GetStringWidth(iconv('UTF-8', 'Windows-1252//TRANSLIT', $texto)) <= $ancho) {
        $escribir($x, $y, $ancho, $texto);
        return;
    }
    $palabras = preg_split('/\s+/u', $texto);
    $primera = '';
    while ($palabras) {
        $prueba = trim($primera . ' ' . $palabras[0]);
        if ($primera !== '' && $pdf->GetStringWidth(iconv('UTF-8', 'Windows-1252//TRANSLIT', $prueba)) > $ancho) {
            break;
        }
        $primera = $prueba;
        array_shift($palabras);
    }
    $escribir($x, $y - 2.1, $ancho, $primera, 8.5);
    $escribir($x, $y + 0.8, $ancho, implode(' ', $palabras), 8.5);
};
$escribir(37.2, 112.49, 9.9, $fechaReal->format('j'), 11, true);
$escribir(73.6, 112.49, 58.5, ucfirst($meses[(int) $fechaReal->format('n')]), 11, true);
$escribir(148, 112.49, 40.7, $fechaReal->format('Y'), 11, true);
$escribir(101.2, 130.77, 87.4, $nombrePropio($certificadoData['CelebrantePres']));
$escribir(29, 136.84, 69.5, $certificadoData['ImpedimentoMat'] ?? '');
$escribirContrayente(29, 142.98, 88.3, $certificadoData['NombreNovio']);
$escribir(123.5, 142.98, 10.6, $nacimientoNovio->diff($fechaReal)->y, 11, true);
// Tachado sobre la palabra impresa, sin borrar «soltero o viudo».
$tachar = function ($x1, $x2, $y) use ($pdf) {
    $pdf->SetLineWidth(0.28);
    $pdf->Line($x1, $y, $x2, $y);
    $pdf->SetLineWidth(0.25);
};
if ($certificadoData['EstadoCivilNovio'] === 'Soltero') {
    $tachar(172.70, 181.64, 141.65); // viudo
} else {
    $tachar(157.90, 168.82, 141.65); // soltero
}
if ($certificadoData['EstadoCivilNovia'] === 'Soltera') {
    $tachar(173.54, 182.44, 159.86); // viuda
} else {
    $tachar(158.81, 169.69, 159.86); // soltera
}
$escribir(76, 149.05, 59.2, $nombrePropio($certificadoExt['ParroquiaBau']));
$escribir(144.6, 149.05, 44.2, $nombrePropio($certificadoData['FiliacionNovio'] ?? ''));
$escribir(33.6, 155.12, 75.2, $nombrePropio($certificadoData['NombrePadreNovio'] ?? ''));
$escribir(118, 155.12, 70.5, $nombrePropio($certificadoData['NombreMadreNovio'] ?? ''));
$escribirContrayente(35.5, 161.19, 82.7, $certificadoData['NombreNovia']);
$escribir(124.4, 161.19, 10.6, $nacimientoNovia->diff($fechaReal)->y, 11, true);
$escribir(76.7, 167.33, 58.3, $nombrePropio($certificadoExtN['ParroquiaBau']));
$escribir(144.3, 167.33, 44.2, $nombrePropio($certificadoData['FiliacionNovia'] ?? ''));
$escribir(33.6, 173.40, 75.2, $nombrePropio($certificadoData['NombrePadreNovia'] ?? ''));
$escribir(118, 173.40, 70.5, $nombrePropio($certificadoData['NombreMadreNovia'] ?? ''));
$escribir(79.4, 179.47, 103.3, $nombrePropio($certificadoData['NombreTestigoNovio'] ?? ''));
$escribir(29, 185.54, 159.5, $nombrePropio($certificadoData['NombreTestigoNovia'] ?? ''));
$presenciales = array_filter([
    $limpiar($certificadoData['NombrePadrino']), $limpiar($certificadoData['NombreMadrina'])
], function ($valor) { return $valor !== ''; });
$escribir(74.8, 191.68, 113.7, $presenciales ? implode(' y ', array_map($nombrePropio, $presenciales)) : '');
$firmaPartida = $limpiar($certificadoData['CertFirmado'] ?? '');
$escribir(83.9, 197.74, 104.2, $nombrePropio($firmaPartida !== '' ? $firmaPartida : $certificadoData['PresbiteroEm']));

// Observaciones continúan en el segundo renglón cuando es necesario.
$palabras = preg_split('/\s+/u', $limpiar($certificadoData['Observacion'] ?? ''));
$primera = '';
$pdf->SetFont('Arial', 'B', 10.5);
while ($palabras) {
    $prueba = trim($primera . ' ' . $palabras[0]);
    if ($primera !== '' && $pdf->GetStringWidth(iconv('UTF-8', 'Windows-1252//TRANSLIT', $prueba)) > 134.3) {
        break;
    }
    $primera = $prueba;
    array_shift($palabras);
}
$escribir(54, 203.81, 134.3, $primera);
if ($palabras) {
    $escribir(29, 209.95, 159.4, implode(' ', $palabras));
}
$escribir(95.7, 226.61, 8, $fechaEmision->format('j'), 11, true);
$escribir(109.8, 226.61, 31.3, ucfirst($meses[(int) $fechaEmision->format('n')]), 11, true);
$escribir(148.2, 226.61, 32.2, $fechaEmision->format('Y'), 11, true);
// Nombre debajo de la línea de firma; el sello y la firma se colocan físicamente.
// La nueva plantilla ya incluye «Pbro.» a la izquierda y «PARROCO» debajo.
$firma = preg_replace('/^(?:(?:Pbro|P|Padre|Presb[ií]tero)\.?\s+)+/iu', '', $nombrePropio($certificadoData['PresbiteroEm']));
$escribir(117, 254.55, 50.5, $firma, 10, true);

$tmpFile = tempnam(sys_get_temp_dir(), 'cert_mat_');
$pdf->Output('F', $tmpFile);
$pdfContent = file_get_contents($tmpFile);

try {
    require_once __DIR__ . '/../Controlador/controladorRespaldoLocal.php';
    $respaldoLocal = new ControladorRespaldoLocal();
    $respaldoLocal->guardarPDF($pdfContent, 'Matrimonio', $pareja);
} catch (Throwable $e) {
    error_log('No se pudo guardar el respaldo local de Matrimonio: ' . $e->getMessage());
}

@unlink($tmpFile);
$pdf->Output('I', 'Certificado-Matrimonio.pdf');
