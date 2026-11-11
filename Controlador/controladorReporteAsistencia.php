<?php
require_once '../Modelo/modeloReporteAsistencia.php';
require_once '../fpdf/fpdf.php';
require_once '../FPDI-master/src/autoload.php';

class ReporteAsistenciaPDF extends FPDF
{
    public $titulo = 'Reporte Anual de Asistencia de Catequistas';
    public $subtitulo = '';
    public $mostrarLeyenda = true;
    public $mesesCortos = [];
    private $texto;

    public function __construct($orientacion, $unidad, $formato, callable $texto)
    {
        parent::__construct($orientacion, $unidad, $formato);
        $this->texto = $texto;
    }

    private function t($valor)
    {
        return call_user_func($this->texto, $valor);
    }

    public function Header()
    {
        $this->Image('../assets/images/logo.png', 10, 8, 25);

        $this->SetFont('Arial', 'B', 14);
        $this->SetXY(0, 10);
        $this->Cell(0, 8, $this->t('Parroquia San Pedro de Sacaba'), 0, 1, 'C');

        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 7, $this->t($this->titulo), 0, 1, 'C');

        $this->SetFont('Arial', '', 9);
        $this->Cell(0, 5, $this->t($this->subtitulo), 0, 1, 'C');

        if ($this->mostrarLeyenda) {
            $this->SetFont('Arial', '', 8);
            $this->Cell(0, 5, $this->t('P = Presente   ·   F = Falta   ·   Pe = Permiso'), 0, 1, 'R');
        }

        $this->SetY(38);
    }

    public function Footer()
    {
        $this->SetY(-12);
        $this->SetFont('Arial', 'I', 7);
        $this->SetTextColor(107, 114, 128);
        $this->Cell(140, 5, $this->t('Sistema MVCSanPedro · Reporte anual de asistencia'), 0, 0, 'L');
        $this->Cell(0, 5, $this->t('Página ' . $this->PageNo() . ' de {nb}'), 0, 0, 'R');
        $this->SetTextColor(0, 0, 0);
    }

    public function tablaHeader()
    {
        $this->SetX(8);
        $this->SetFont('Arial', 'B', 6.5);
        $this->SetFillColor(31, 41, 55);
        $this->SetTextColor(255, 255, 255);

        $this->Cell(52, 6, $this->t('Catequista'), 1, 0, 'L', true);
        $this->Cell(10, 6, $this->t('Grupo'), 1, 0, 'C', true);
        $this->Cell(18, 6, $this->t('Rol'), 1, 0, 'C', true);

        foreach ($this->mesesCortos as $mes) {
            $this->Cell(15, 6, $this->t($mes), 1, 0, 'C', true);
        }

        $this->Cell(13, 6, $this->t('Total'), 1, 1, 'C', true);
        $this->SetTextColor(0, 0, 0);
    }

    public function filaCatequista($catequista, $conteo, $rellenar)
    {
        $this->SetX(8);
        $this->SetFillColor($rellenar ? 248 : 255, $rellenar ? 250 : 255, $rellenar ? 252 : 255);
        $this->SetFont('Arial', '', 6.5);
        $this->SetTextColor(31, 41, 55);

        $nombre = trim($catequista['Nombre'] . ' ' . $catequista['ApPaterno'] . ' ' . $catequista['ApMaterno']);
        $this->Cell(52, 6, $this->t($nombre), 1, 0, 'L', true);
        $this->Cell(10, 6, $this->t($catequista['NombreGrupo'] ?? ''), 1, 0, 'C', true);
        $this->Cell(18, 6, $this->t($catequista['RolCat'] ?? ''), 1, 0, 'C', true);

        $totalP = 0;
        $totalF = 0;
        $totalPe = 0;

        for ($mes = 1; $mes <= 12; $mes++) {
            $p = (int) ($conteo[$mes]['Presente'] ?? 0);
            $f = (int) ($conteo[$mes]['Falta'] ?? 0);
            $pe = (int) ($conteo[$mes]['Permiso'] ?? 0);

            $totalP += $p;
            $totalF += $f;
            $totalPe += $pe;

            $this->celdaAsistencia($p, $f, $pe, 15, 6, $rellenar, false);
        }

        $this->celdaAsistencia($totalP, $totalF, $totalPe, 13, 6, $rellenar, true);
        $this->Ln();
        $this->SetTextColor(0, 0, 0);
    }

    private function celdaAsistencia($presente, $falta, $permiso, $ancho, $alto, $rellenar, $negrita)
    {
        $x = $this->GetX();
        $y = $this->GetY();

        $this->Cell($ancho, $alto, '', 1, 0, 'C', $rellenar);
        $this->SetXY($x, $y);

        $this->SetFont('Arial', $negrita ? 'B' : '', 6.5);
        $anchoNumero = ($ancho - 2) / 3;

        $this->SetTextColor(25, 135, 84);
        $this->Cell($anchoNumero, $alto, $presente, 0, 0, 'R');

        $this->SetTextColor(156, 163, 175);
        $this->Cell(1, $alto, '/', 0, 0, 'C');

        $this->SetTextColor(220, 53, 69);
        $this->Cell($anchoNumero, $alto, $falta, 0, 0, 'R');

        $this->SetTextColor(156, 163, 175);
        $this->Cell(1, $alto, '/', 0, 0, 'C');

        $this->SetTextColor(180, 122, 0);
        $this->Cell($anchoNumero, $alto, $permiso, 0, 0, 'R');

        $this->SetTextColor(0, 0, 0);
        $this->SetXY($x + $ancho, $y);
    }
}

class ReporteAsistenciaControlador
{
    private $modelo;

    public function __construct()
    {
        $this->modelo = new ReporteAsistenciaModelo();
    }

    public function generarReportePDF($sacramento, $anio)
    {
        $asistencias = $this->modelo->obtenerAsistenciasCatequistas($sacramento, $anio);
        $catequistas = $this->modelo->obtenerCatequistasPorSacramento($sacramento);

        // Una fila por catequista: se conserva la primera asignación según el orden del modelo.
        $catequistasUnicos = [];
        foreach ($catequistas as $catequista) {
            $ci = $catequista['CiPersona'];
            if (!isset($catequistasUnicos[$ci])) {
                $catequistasUnicos[$ci] = $catequista;
            }
        }
        $catequistas = array_values($catequistasUnicos);

        $conteos = [];
        $fechasPorMes = [];

        foreach ($asistencias as $asistencia) {
            $mes = (int) date('n', strtotime($asistencia['FechaAsisCat']));
            $ci = $asistencia['CiCat'];
            $detalle = $asistencia['DetalleAsis'];

            if (!in_array($detalle, ['Presente', 'Falta', 'Permiso'], true)) {
                $detalle = 'Falta';
            }

            $conteos[$ci][$mes][$detalle] = ($conteos[$ci][$mes][$detalle] ?? 0) + 1;
            $fechasPorMes[$mes][$asistencia['FechaAsisCat']] = true;
        }

        $mesesCortos = [
            1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun',
            7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic',
        ];

        $mesesLargos = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
            7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];

        $texto = function ($valor) {
            return iconv('UTF-8', 'Windows-1252//IGNORE', (string) $valor);
        };

        $pdf = new ReporteAsistenciaPDF('L', 'mm', 'A4', $texto);
        $pdf->mesesCortos = $mesesCortos;
        $pdf->titulo = 'Reporte Anual de Asistencia de Catequistas';
        $pdf->subtitulo = 'Detalle mensual por catequista · ' . $sacramento . ' · Año ' . $anio;
        $pdf->mostrarLeyenda = true;

        $pdf->SetMargins(8, 8, 8);
        $pdf->SetAutoPageBreak(true, 16);
        $pdf->AliasNbPages();

        $pdf->AddPage();
        $pdf->tablaHeader();

        $fila = 0;
        foreach ($catequistas as $catequista) {
            if ($pdf->GetY() + 6 > 196) {
                $pdf->AddPage();
                $pdf->tablaHeader();
            }

            $pdf->filaCatequista(
                $catequista,
                $conteos[$catequista['CiPersona']] ?? [],
                $fila % 2 === 1
            );
            $fila++;
        }

        // Página final: fechas de toma de asistencia.
        $pdf->titulo = 'Fechas de Toma de Asistencia';
        $pdf->subtitulo = 'Días con asistencia registrada · ' . $sacramento . ' · Año ' . $anio;
        $pdf->mostrarLeyenda = false;
        $pdf->AddPage();

        $pdf->SetY(42);
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(75, 85, 99);
        $pdf->Cell(0, 6, $texto('Días en los que se registró asistencia de catequistas, agrupados por mes.'), 0, 1, 'L');
        $pdf->SetTextColor(0, 0, 0);

        $inicioX = 8;
        $inicioY = $pdf->GetY() + 4;
        $anchoTarjeta = 88;
        $altoTarjeta = 31;
        $separacionX = 4;
        $separacionY = 3;

        for ($mes = 1; $mes <= 12; $mes++) {
            $columna = ($mes - 1) % 3;
            $filaTarjeta = intdiv($mes - 1, 3);
            $x = $inicioX + $columna * ($anchoTarjeta + $separacionX);
            $y = $inicioY + $filaTarjeta * ($altoTarjeta + $separacionY);

            $pdf->SetDrawColor(209, 213, 219);
            $pdf->Rect($x, $y, $anchoTarjeta, $altoTarjeta);

            $pdf->SetXY($x, $y);
            $pdf->SetFillColor(31, 41, 55);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('Arial', 'B', 8.5);
            $pdf->Cell($anchoTarjeta, 7, $texto($mesesLargos[$mes]), 0, 0, 'L', true);

            $pdf->SetXY($x + 3, $y + 10);
            $pdf->SetTextColor(31, 41, 55);
            $pdf->SetFont('Arial', '', 7.5);

            if (!empty($fechasPorMes[$mes])) {
                $fechas = array_keys($fechasPorMes[$mes]);
                sort($fechas);
                $fechasTexto = implode('   ·   ', array_map(function ($fecha) {
                    return date('d/m/Y', strtotime($fecha));
                }, $fechas));
                $pdf->MultiCell($anchoTarjeta - 6, 4.5, $texto($fechasTexto), 0, 'L');
            } else {
                $pdf->SetFont('Arial', 'I', 7.5);
                $pdf->SetTextColor(156, 163, 175);
                $pdf->MultiCell($anchoTarjeta - 6, 4.5, $texto('Sin registros'), 0, 'L');
            }

            $pdf->SetDrawColor(0, 0, 0);
            $pdf->SetTextColor(0, 0, 0);
        }

        $nombre = 'Reporte_Asistencia_' .
            preg_replace('/[^A-Za-z0-9]+/', '_', iconv('UTF-8', 'ASCII//TRANSLIT', $sacramento)) .
            '_' . $anio . '.pdf';
        $pdf->Output('I', $nombre);
    }
}
?>