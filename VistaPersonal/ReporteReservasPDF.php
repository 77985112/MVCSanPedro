<?php

class ReporteReservasPDF extends FPDF
{
    private $tituloReporte = 'REPORTE DE RESERVAS';
    private $gestion = '';
    private $tipoPeriodo = '';
    private $detallePeriodo = '';
    private $fechaGeneracion = '';
    private $totalReservas = 0;
    private $totalCanceladas = 0;
    private $anchos = [8, 19, 31, 19, 13, 17, 44, 41];
    private $logo;

    public function __construct()
    {
        parent::__construct('P', 'mm', 'Letter');
        $this->logo = __DIR__ . '/../assets/media/logo.png';
        $this->SetMargins(12, 10, 12);
        $this->SetAutoPageBreak(true, 20);
        $this->AliasNbPages();
    }

    private function textoPDF($texto)
    {
        $convertido = iconv('UTF-8', 'Windows-1252//TRANSLIT', (string) ($texto ?? ''));
        return $convertido === false ? (string) ($texto ?? '') : $convertido;
    }

    private function textoPeriodo()
    {
        return 'Periodo: ' . $this->periodoSinPrefijo();
    }

    private function periodoSinPrefijo()
    {
        if ($this->detallePeriodo === '') {
            return $this->tipoPeriodo;
        }

        return $this->tipoPeriodo . ' - ' . $this->detallePeriodo;
    }

    public function Header()
    {
        if ($this->PageNo() === 1) {
            if (is_file($this->logo)) {
                $this->Image($this->logo, 12, 11, 43);
            }

            $this->SetDrawColor(190, 140, 25);
            $this->SetLineWidth(0.45);
            $this->Line(59, 11, 59, 32);

            $this->SetTextColor(20, 20, 20);
            $this->SetFont('Arial', 'B', 13);
            $this->SetXY(64, 10);
            $this->Cell(91, 7, $this->textoPDF($this->tituloReporte), 0, 1, 'C');

            $this->SetTextColor(176, 128, 18);
            $this->SetFont('Arial', 'B', 12);
            $this->SetX(64);
            $this->Cell(91, 6, $this->textoPDF('Gestión ' . $this->gestion), 0, 1, 'C');

            $this->SetTextColor(35, 35, 35);
            $this->SetFont('Arial', '', 9);
            $this->SetX(64);
            $this->Cell(91, 5, $this->textoPDF($this->textoPeriodo()), 0, 1, 'C');

            $this->SetDrawColor(188, 188, 188);
            $this->SetLineWidth(0.2);
            $this->Rect(160, 11, 44, 19);
            $this->SetFont('Arial', '', 8);
            $this->SetXY(162, 14);
            $this->Cell(40, 4, 'Generado el', 0, 1, 'C');
            $this->SetFont('Arial', 'B', 8.5);
            $this->SetX(162);
            $this->Cell(40, 5, $this->textoPDF($this->fechaGeneracion), 0, 1, 'C');

            $this->SetDrawColor(190, 140, 25);
            $this->SetLineWidth(0.45);
            $this->Line(12, 37, 204, 37);

            $this->SetDrawColor(210, 214, 218);
            $this->SetLineWidth(0.2);
            $this->Rect(12, 43, 192, 18);
            $this->SetTextColor(20, 20, 20);
            $this->SetFont('Arial', 'B', 18);
            $this->SetXY(18, 46);
            $this->Cell(16, 10, (string) $this->totalReservas, 0, 0, 'C');
            $this->SetFont('Arial', 'B', 11);
            $this->SetXY(37, 44);
            $this->Cell(70, 6, 'reservas en el periodo', 0, 1, 'L');
            $this->SetFont('Arial', '', 8);
            $this->SetTextColor(90, 90, 90);
            $this->SetX(37);
            $this->Cell(70, 6, $this->textoPDF('Reservadas: ' . ($this->totalReservas - $this->totalCanceladas) . '    Canceladas: ' . $this->totalCanceladas), 0, 1, 'L');

            $this->SetXY(12, 65);
        } else {
            if (is_file($this->logo)) {
                $this->Image($this->logo, 12, 9, 32);
            }
            $this->SetTextColor(20, 20, 20);
            $this->SetFont('Arial', 'B', 10);
            $this->SetXY(50, 11);
            $this->Cell(154, 6, $this->textoPDF($this->tituloReporte . ' - continuación'), 0, 1, 'R');
            $this->SetDrawColor(190, 140, 25);
            $this->SetLineWidth(0.35);
            $this->Line(12, 26, 204, 26);
            $this->SetY(30);
        }

        $this->imprimirCabeceraTabla();
    }

    public function Footer()
    {
        $this->SetY(-15);
        $this->SetDrawColor(150, 150, 150);
        $this->SetLineWidth(0.2);
        $this->Line(12, $this->GetY(), 204, $this->GetY());
        $this->Ln(2);
        $this->SetTextColor(30, 30, 30);
        $this->SetFont('Arial', 'B', 7.5);
        $this->Cell(68, 6, 'PARROQUIA SAN PEDRO DE SACABA', 0, 0, 'L');
        $this->SetFont('Arial', '', 7.5);
        $this->Cell(80, 6, $this->textoPDF('Reporte de Reservas - ' . $this->periodoSinPrefijo()), 0, 0, 'C');
        $this->Cell(43, 6, $this->textoPDF('Página ') . $this->PageNo() . ' de {nb}', 0, 0, 'R');
    }

    private function imprimirCabeceraTabla()
    {
        $columnas = ['#', 'Estado', 'Sacramento / Misa', 'Fecha', 'Hora', 'Cargo', 'Personal que hizo la reserva', 'Reservante'];
        $this->SetX($this->lMargin);
        $this->SetFillColor(246, 244, 239);
        $this->SetDrawColor(205, 205, 205);
        $this->SetTextColor(25, 25, 25);
        $this->SetFont('Arial', 'B', 8);
        foreach ($columnas as $indice => $columna) {
            $this->Cell($this->anchos[$indice], 9, $this->textoPDF($columna), 1, 0, 'C', true);
        }
        $this->Ln();
    }

    private function lineasTexto($texto, $ancho)
    {
        $texto = trim($this->textoPDF($texto));
        if ($texto === '') {
            return [''];
        }

        $this->SetFont('Arial', 'B', 8.5);
        $lineas = [];
        $linea = '';
        $limite = $ancho - 5;
        foreach (preg_split('/\s+/', $texto) as $palabra) {
            $candidata = $linea === '' ? $palabra : $linea . ' ' . $palabra;
            if ($linea !== '' && $this->GetStringWidth($candidata) > $limite) {
                $lineas[] = $linea;
                $linea = $palabra;
            } else {
                $linea = $candidata;
            }
        }
        $lineas[] = $linea;
        return $lineas;
    }

    private function nombrePersonal(array $fila)
    {
        $nombre = trim((string) ($fila['NombreEmisor'] ?? $fila['DatosEm'] ?? ''));
        return trim((string) preg_replace('/\s*\([^)]*\)\s*$/u', '', $nombre));
    }

    private function estiloSacramento($sacramento)
    {
        $clave = strtolower(trim((string) $sacramento));

        if (strpos($clave, 'confirmaci') !== false) {
            return ['barra' => [210, 55, 55], 'fondo' => [253, 237, 237]];
        }
        if (strpos($clave, 'comuni') !== false || strpos($clave, 'primera') !== false) {
            return ['barra' => [199, 154, 10], 'fondo' => [253, 247, 219]];
        }
        if (strpos($clave, 'matrimonio') !== false) {
            return ['barra' => [40, 170, 205], 'fondo' => [234, 247, 252]];
        }
        if (strpos($clave, 'misa') !== false) {
            return ['barra' => [34, 160, 90], 'fondo' => [237, 250, 241]];
        }
        if (strpos($clave, 'bautizo') !== false) {
            return ['barra' => [190, 140, 25], 'fondo' => [255, 248, 230]];
        }

        return ['barra' => [150, 150, 150], 'fondo' => [255, 255, 255]];
    }

    private function formatearFecha($fecha)
    {
        try {
            return (new DateTimeImmutable((string) $fecha))->format('d/m/Y');
        } catch (Exception $e) {
            return (string) $fecha;
        }
    }

    private function formatearHora($hora)
    {
        $hora = trim((string) $hora);
        if (preg_match('/^(\d{1,2}):(\d{2})/', $hora, $partes)) {
            return str_pad($partes[1], 2, '0', STR_PAD_LEFT) . ':' . $partes[2];
        }
        return $hora;
    }

    private function imprimirFila(array $fila, $indice)
    {
        $cancelado = (($fila['EstadoRes'] ?? '') === 'Cancelado');
        $estilo = $this->estiloSacramento($fila['DescripSac'] ?? '');
        $nombre = $this->nombrePersonal($fila);
        $lineasNombre = $this->lineasTexto($nombre, $this->anchos[6]);
        $reservante = trim((string) ($fila['ReservanteNombre'] ?? ''));
        $documento = trim((string) ($fila['ReservanteDocumento'] ?? ''));
        $lineasReservante = $this->lineasTexto($reservante, $this->anchos[7]);
        $altoDocumento = $documento === '' ? 0 : 3.8;

        $this->SetFont('Arial', '', 8.5);
        $alto = max(
            10,
            count($lineasNombre) * 4.5 + 4,
            count($lineasReservante) * 4.2 + $altoDocumento + 3.5
        );

        if ($this->GetY() + $alto > $this->PageBreakTrigger) {
            $this->AddPage();
        }

        $x = $this->lMargin;
        $y = $this->GetY();

        if ($cancelado) {
            $this->SetFillColor(43, 43, 43);
        } else {
            $this->SetFillColor($estilo['fondo'][0], $estilo['fondo'][1], $estilo['fondo'][2]);
        }
        $this->SetDrawColor(218, 218, 218);
        $this->SetLineWidth(0.15);
        foreach ($this->anchos as $ancho) {
            $this->Rect($x, $y, $ancho, $alto, 'DF');
            $x += $ancho;
        }

        if (!$cancelado) {
            $this->SetFillColor($estilo['barra'][0], $estilo['barra'][1], $estilo['barra'][2]);
            $this->Rect($this->lMargin + 0.2, $y + 0.2, 1.6, $alto - 0.4, 'F');
        }

        $texto = $cancelado ? [255, 255, 255] : [25, 25, 25];

        $x = $this->lMargin;
        $this->SetTextColor($texto[0], $texto[1], $texto[2]);
        $this->SetFont('Arial', '', 8.5);
        $this->SetXY($x, $y + ($alto - 5) / 2);
        $this->Cell($this->anchos[0], 5, (string) ($indice + 1), 0, 0, 'C');
        $x += $this->anchos[0];

        $anchoEstado = 17;
        if ($cancelado) {
            $this->SetFillColor(192, 57, 43);
            $this->SetDrawColor(192, 57, 43);
            $colorEstado = [255, 255, 255];
        } else {
            $this->SetFillColor(232, 246, 235);
            $this->SetDrawColor(194, 226, 201);
            $colorEstado = [30, 107, 58];
        }
        $this->SetLineWidth(0.2);
        $this->Rect($x + ($this->anchos[1] - $anchoEstado) / 2, $y + ($alto - 5.5) / 2, $anchoEstado, 5.5, 'DF');
        $this->SetTextColor($colorEstado[0], $colorEstado[1], $colorEstado[2]);
        $this->SetFont('Arial', 'B', 7.5);
        $this->SetXY($x, $y + ($alto - 5) / 2);
        $this->Cell($this->anchos[1], 5, $this->textoPDF($fila['EstadoRes'] ?? ''), 0, 0, 'C');
        $x += $this->anchos[1];

        $borde = $cancelado ? [255, 255, 255] : $estilo['barra'];
        $this->SetFillColor(255, 255, 255);
        $this->SetDrawColor($borde[0], $borde[1], $borde[2]);
        $this->SetLineWidth(0.35);
        $this->Rect($x + 2, $y + ($alto - 6) / 2, $this->anchos[2] - 4, 6, 'DF');
        $this->SetTextColor($cancelado ? 43 : 25, $cancelado ? 43 : 25, $cancelado ? 43 : 25);
        $this->SetFont('Arial', 'B', 8);
        $this->SetXY($x, $y + ($alto - 5) / 2);
        $this->Cell($this->anchos[2], 5, $this->textoPDF($fila['DescripSac'] ?? ''), 0, 0, 'C');
        $x += $this->anchos[2];

        $this->SetFont('Arial', '', 8.5);
        $this->SetTextColor($texto[0], $texto[1], $texto[2]);
        $valores = [
            [3, $this->formatearFecha($fila['FechaReal'] ?? '')],
            [4, $this->formatearHora($fila['HoraReal'] ?? '')],
            [5, $fila['DescripCar'] ?? ''],
        ];
        foreach ($valores as $par) {
            $this->SetXY($x, $y + ($alto - 5) / 2);
            $this->Cell($this->anchos[$par[0]], 5, $this->textoPDF($par[1]), 0, 0, 'C');
            $x += $this->anchos[$par[0]];
        }

        $this->SetFont('Arial', 'B', 8.5);
        foreach ($lineasNombre as $numeroLinea => $linea) {
            $this->SetXY($x + 3, $y + ($alto - count($lineasNombre) * 4.5) / 2 + $numeroLinea * 4.5);
            $this->Cell($this->anchos[6] - 5, 4.5, $linea, 0, 0, 'L');
        }
        $x += $this->anchos[6];

        $bloque = count($lineasReservante) * 4.2 + $altoDocumento;
        $inicio = $y + ($alto - $bloque) / 2;
        foreach ($lineasReservante as $numeroLinea => $linea) {
            $this->SetXY($x + 3, $inicio + $numeroLinea * 4.2);
            $this->Cell($this->anchos[7] - 5, 4.2, $linea, 0, 0, 'L');
        }

        if ($documento !== '') {
            $this->SetFont('Arial', '', 6.8);
            $this->SetTextColor($cancelado ? 225 : 105, $cancelado ? 225 : 105, $cancelado ? 225 : 105);
            $this->SetXY($x + 3, $inicio + count($lineasReservante) * 4.2);
            $this->Cell($this->anchos[7] - 5, 3.6, $this->textoPDF('Doc. ' . $documento), 0, 0, 'L');
        }

        $this->SetXY($this->lMargin, $y + $alto);
    }

    public function generar(array $reservas, $tipoPeriodo, $gestion, $detallePeriodo, $nombreArchivo, $destino = 'I')
    {
        $meses = [
            1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
            'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'
        ];
        $hoy = new DateTimeImmutable('now', new DateTimeZone('America/La_Paz'));
        $this->tipoPeriodo = (string) $tipoPeriodo;
        $this->detallePeriodo = (string) $detallePeriodo;
        $this->gestion = (string) $gestion;
        $this->fechaGeneracion = (int) $hoy->format('j') . ' de ' . $meses[(int) $hoy->format('n')] . ' de ' . $hoy->format('Y');
        $this->totalReservas = count($reservas);
        $this->totalCanceladas = 0;
        foreach ($reservas as $fila) {
            if (($fila['EstadoRes'] ?? '') === 'Cancelado') {
                $this->totalCanceladas++;
            }
        }
        $this->SetTitle($this->textoPDF($this->tituloReporte . ' - ' . $this->textoPeriodo()));
        $this->SetAuthor($this->textoPDF('Parroquia San Pedro de Sacaba'));
        $this->SetCreator($this->textoPDF('Parroquia San Pedro de Sacaba'));
        $this->AddPage();

        if (!$reservas) {
            $this->SetFont('Arial', '', 9);
            $this->SetTextColor(45, 45, 45);
            $this->Cell(array_sum($this->anchos), 12, 'No hay reservas registradas en este periodo.', 1, 1, 'C');
        } else {
            foreach ($reservas as $indice => $fila) {
                $this->imprimirFila($fila, $indice);
            }
        }

        $this->Output($destino, $nombreArchivo);
    }
}
