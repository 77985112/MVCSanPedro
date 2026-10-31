<?php

class ReporteCertificadosPDF extends FPDF
{
    private $tituloReporte = 'REPORTE DE CERTIFICADOS EMITIDOS';
    private $gestion = '';
    private $periodo = '';
    private $fechaGeneracion = '';
    private $totalCertificados = 0;
    private $anchos = [8, 22, 37, 28, 36, 60];
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
            $this->SetXY(64, 11);
            $this->Cell(91, 7, $this->textoPDF($this->tituloReporte), 0, 1, 'C');

            $this->SetTextColor(176, 128, 18);
            $this->SetFont('Arial', 'B', 12);
            $this->SetX(64);
            $this->Cell(91, 6, $this->textoPDF('Gestión ' . $this->gestion), 0, 1, 'C');

            $this->SetTextColor(35, 35, 35);
            $this->SetFont('Arial', '', 9);
            $this->SetX(64);
            $this->Cell(91, 5, $this->textoPDF('Periodo: ' . $this->periodo), 0, 1, 'C');

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
            $this->Cell(16, 10, (string) $this->totalCertificados, 0, 0, 'C');
            $this->SetFont('Arial', 'B', 11);
            $this->SetXY(37, 48);
            $this->Cell(70, 7, 'certificados emitidos', 0, 0, 'L');
            $this->SetY(66);
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
        $this->Cell(80, 6, $this->textoPDF('Reporte de Certificados Emitidos - Gestión ' . $this->gestion), 0, 0, 'C');
        $this->Cell(43, 6, $this->textoPDF('Página ') . $this->PageNo() . ' de {nb}', 0, 0, 'R');
    }

    private function imprimirCabeceraTabla()
    {
        $columnas = ['#', 'Estado', 'Sacramento', 'Documento', 'Fecha de emisión', 'Emitido por'];
        $this->SetX($this->lMargin);
        $this->SetFillColor(246, 244, 239);
        $this->SetDrawColor(205, 205, 205);
        $this->SetTextColor(25, 25, 25);
        $this->SetFont('Arial', 'B', 8.5);
        foreach ($columnas as $indice => $columna) {
            $this->Cell($this->anchos[$indice], 8, $this->textoPDF($columna), 1, 0, 'C', true);
        }
        $this->Ln();
    }

    private function lineasTexto($texto, $ancho)
    {
        $texto = trim($this->textoPDF($texto));
        if ($texto === '') {
            return [''];
        }

        $lineas = [];
        $linea = '';
        $limite = $ancho - 4;
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

    private function nombreEmisor(array $fila)
    {
        $nombre = trim((string) ($fila['NombreEmisor'] ?? $fila['DatosEm'] ?? ''));
        $nombre = trim((string) preg_replace('/\s*\([^)]*\)\s*$/u', '', $nombre));
        $cargo = trim((string) ($fila['DescripCar'] ?? ''));

        return $cargo === '' ? $nombre : $nombre . ' (' . $cargo . ')';
    }

    private function colorSacramento($sacramento)
    {
        switch ($sacramento) {
            case 'Bautizo':
                return [190, 140, 25];
            case 'Confirmación':
                return [210, 55, 55];
            case 'Matrimonio':
                return [40, 170, 205];
            default:
                return [150, 150, 150];
        }
    }

    private function imprimirFila(array $fila, $indice)
    {
        $this->SetFont('Arial', '', 8.5);
        $nombreEmisor = $this->nombreEmisor($fila);
        $lineasEmisor = $this->lineasTexto($nombreEmisor, $this->anchos[5]);
        $alto = max(9, count($lineasEmisor) * 4.5 + 3);

        if ($this->GetY() + $alto > $this->PageBreakTrigger) {
            $this->AddPage();
        }

        $x = $this->lMargin;
        $y = $this->GetY();
        $valores = [
            (string) ($indice + 1),
            (string) ($fila['EstadoCert'] ?? ''),
            (string) ($fila['DescripSac'] ?? ''),
            (string) ($fila['NroEmision'] ?? ''),
            $this->formatearFecha($fila['FechaEmision'] ?? ''),
            $nombreEmisor,
        ];

        $this->SetFillColor($indice % 2 === 0 ? 255 : 250, $indice % 2 === 0 ? 255 : 250, $indice % 2 === 0 ? 255 : 249);
        $this->SetDrawColor(218, 218, 218);
        $this->SetLineWidth(0.15);
        foreach ($this->anchos as $ancho) {
            $this->Rect($x, $y, $ancho, $alto, 'DF');
            $x += $ancho;
        }

        $x = $this->lMargin;
        $this->SetTextColor(20, 20, 20);
        $this->SetXY($x, $y + ($alto - 5) / 2);
        $this->Cell($this->anchos[0], 5, $valores[0], 0, 0, 'C');
        $x += $this->anchos[0];

        $anchoEstado = 16;
        $this->SetFillColor(232, 246, 235);
        $this->SetDrawColor(194, 226, 201);
        $this->Rect($x + ($this->anchos[1] - $anchoEstado) / 2, $y + ($alto - 5.5) / 2, $anchoEstado, 5.5, 'DF');
        $this->SetFont('Arial', 'B', 8);
        $this->SetXY($x, $y + ($alto - 5) / 2);
        $this->Cell($this->anchos[1], 5, $this->textoPDF($valores[1]), 0, 0, 'C');
        $x += $this->anchos[1];

        $color = $this->colorSacramento($valores[2]);
        $this->SetFillColor(255, 255, 255);
        $this->SetDrawColor($color[0], $color[1], $color[2]);
        $this->SetLineWidth(0.35);
        $this->Rect($x + 2, $y + ($alto - 6) / 2, $this->anchos[2] - 4, 6, 'DF');
        $this->SetTextColor(20, 20, 20);
        $this->SetFont('Arial', 'B', 8);
        $this->SetXY($x, $y + ($alto - 5) / 2);
        $this->Cell($this->anchos[2], 5, $this->textoPDF($valores[2]), 0, 0, 'C');
        $x += $this->anchos[2];

        $this->SetFont('Arial', '', 8.5);
        foreach ([3, 4] as $indiceColumna) {
            $this->SetXY($x, $y + ($alto - 5) / 2);
            $this->Cell($this->anchos[$indiceColumna], 5, $this->textoPDF($valores[$indiceColumna]), 0, 0, 'C');
            $x += $this->anchos[$indiceColumna];
        }

        foreach ($lineasEmisor as $numeroLinea => $linea) {
            $this->SetXY($x + 2, $y + 1.5 + $numeroLinea * 4.5);
            $this->Cell($this->anchos[5] - 4, 4.5, $linea, 0, 0, 'L');
        }

        $this->SetXY($this->lMargin, $y + $alto);
    }

    private function formatearFecha($fecha)
    {
        try {
            return (new DateTimeImmutable((string) $fecha))->format('d/m/Y');
        } catch (Exception $e) {
            return (string) $fecha;
        }
    }

    public function generar(array $certificados, $periodo, $gestion, $nombreArchivo, $destino = 'I')
    {
        $meses = [
            1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
            'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'
        ];
        $hoy = new DateTimeImmutable('now', new DateTimeZone('America/La_Paz'));
        $this->periodo = (string) $periodo;
        $this->gestion = (string) $gestion;
        $this->fechaGeneracion = (int) $hoy->format('j') . ' de ' . $meses[(int) $hoy->format('n')] . ' de ' . $hoy->format('Y');
        $this->totalCertificados = count($certificados);
        $this->SetTitle($this->textoPDF($this->tituloReporte . ' - ' . $this->periodo));
        $this->AddPage();

        if (!$certificados) {
            $this->SetFont('Arial', '', 9);
            $this->SetTextColor(45, 45, 45);
            $this->Cell(array_sum($this->anchos), 12, 'No hay certificados emitidos en este periodo.', 1, 1, 'C');
        } else {
            foreach ($certificados as $indice => $fila) {
                $this->imprimirFila($fila, $indice);
            }
        }

        $this->Output($destino, $nombreArchivo);
    }
}

