<?php
require_once __DIR__ . '/../Modelo/modeloReporteActividad.php';
require_once __DIR__ . '/../fpdf/fpdf.php';

class ReporteActividadControlador
{
    private $modelo;

    /** Anchos de columna para orientación horizontal. */
    private const COL_N = 8;
    private const COL_TITULO = 66;
    private const COL_FECHA = 20;
    private const COL_HORA = 16;
    private const COL_LUGAR = 48;
    private const COL_TIPO = 32;
    private const COL_ESTADO = 25;
    private const COL_PUBLICADO = 42;
    private const COL_PUBLICACION = 20;

    /** Margen inferior usado por SetAutoPageBreak. */
    private const MARGEN_INFERIOR = 15;

    /** Alto de una línea de texto del título. */
    private const ALTO_LINEA_TITULO = 4.2;

    public function __construct()
    {
        $this->modelo = new ReporteActividadModelo();
    }

    /**
     * Verifica que el usuario tenga rol de Párroco.
     */
    public static function esParroco($usuario)
    {
        return isset($usuario['DescripCar']) && trim($usuario['DescripCar']) === 'Párroco';
    }

    /**
     * Genera el reporte PDF mensual o anual.
     */
    public function generarPDF($anio, $tipo = 'anual')
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        if (empty($_SESSION['usuario']) || !self::esParroco($_SESSION['usuario'])) {
            http_response_code(403);
            exit('Acceso denegado. Solo el Párroco puede generar este reporte.');
        }
        $actividades = $this->modelo->obtenerActividadesPorAno((int) $anio);

        $pdf = new FPDF('L', 'mm', 'A4');
        $pdf->SetMargins(10, 10, 10);
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(true, self::MARGEN_INFERIOR);

        // Logo
        $logo = __DIR__ . '/../assets/images/logo.png';
        if (file_exists($logo)) {
            $pdf->Image($logo, 12, 12, 22);
        }

        // Encabezado
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 9, utf8_decode('Parroquia San Pedro de Sacaba'), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 7, utf8_decode('Reporte de Actividades Parroquiales'), 0, 1, 'C');
        $pdf->SetFont('Arial', 'B', 13);
        $tituloTipo = $tipo === 'mensual' ? 'Reporte Mensual' : 'Reporte Anual';
        $pdf->Cell(0, 8, utf8_decode($tituloTipo . ' - Año ' . $anio), 0, 1, 'C');
        $pdf->Ln(4);

        // Resumen
        $total = count($actividades);
        $activas = 0;
        $concluidas = 0;
        $eliminadas = 0;
        foreach ($actividades as $a) {
            $estado = strtolower(trim($a['EstActividad'] ?? ''));
            if ($estado === 'activo') {
                $activas++;
            } elseif ($estado === 'concluido') {
                $concluidas++;
            } elseif ($estado === 'eliminado') {
                $eliminadas++;
            }
        }

        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 6, utf8_decode('Total actividades: ' . $total . '  |  Activas: ' . $activas . '  |  Concluidas: ' . $concluidas . '  |  Eliminadas: ' . $eliminadas), 0, 1, 'C');
        $pdf->Ln(5);

        if (empty($actividades)) {
            $pdf->SetFont('Arial', 'I', 11);
            $pdf->Cell(0, 10, utf8_decode('No se encontraron actividades para el período seleccionado.'), 0, 1, 'C');
        } else {
            if ($tipo === 'mensual') {
                $this->imprimirTablaPorMes($pdf, $actividades);
            } else {
                $this->imprimirTablaAnual($pdf, $actividades);
            }
        }

        // Pie de página
        $generadoPor = 'Párroco';
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        if (!empty($_SESSION['usuario'])) {
            $u = $_SESSION['usuario'];
            $nombreGenerador = trim(($u['Nombre'] ?? '') . ' ' . ($u['Paterno'] ?? '') . ' ' . ($u['Materno'] ?? ''));
            if ($nombreGenerador !== '') {
                $generadoPor = $nombreGenerador;
            }
        }
        $pdf->SetY(-15);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(0, 6, utf8_decode('Generado el ' . date('d/m/Y H:i:s') . ' por ' . $generadoPor), 0, 0, 'L');
        $pdf->Cell(0, 6, utf8_decode('Página ' . $pdf->PageNo()), 0, 0, 'R');

        $pdf->Output('Reporte_Actividades_' . $tipo . '_' . $anio . '.pdf', 'I');
    }

    private function imprimirTablaAnual($pdf, $actividades)
    {
        $this->cabeceraTabla($pdf);
        $pdf->SetFont('Arial', '', 8);
        foreach ($actividades as $index => $a) {
            $this->filaActividad($pdf, $index + 1, $a);
        }
    }

    private function imprimirTablaPorMes($pdf, $actividades)
    {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        $agrupadas = [];
        foreach ($actividades as $a) {
            $mes = (int) date('n', strtotime($a['FechaActividad']));
            $agrupadas[$mes][] = $a;
        }

        foreach ($agrupadas as $mes => $lista) {
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(0, 8, utf8_decode($meses[$mes]), 0, 1, 'L');
            $this->cabeceraTabla($pdf);
            $pdf->SetFont('Arial', '', 8);
            foreach ($lista as $index => $a) {
                $this->filaActividad($pdf, $index + 1, $a);
            }
            $pdf->Ln(3);
        }
    }

    private function cabeceraTabla($pdf)
    {
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->Cell(self::COL_N, 8, 'N', 1, 0, 'C', true);
        $pdf->Cell(self::COL_TITULO, 8, utf8_decode('Título'), 1, 0, 'C', true);
        $pdf->Cell(self::COL_FECHA, 8, 'Fecha', 1, 0, 'C', true);
        $pdf->Cell(self::COL_HORA, 8, 'Hora', 1, 0, 'C', true);
        $pdf->Cell(self::COL_LUGAR, 8, utf8_decode('Lugar'), 1, 0, 'C', true);
        $pdf->Cell(self::COL_TIPO, 8, utf8_decode('Tipo'), 1, 0, 'C', true);
        $pdf->Cell(self::COL_ESTADO, 8, utf8_decode('Estado'), 1, 0, 'C', true);
        $pdf->Cell(self::COL_PUBLICADO, 8, utf8_decode('Publicado por'), 1, 0, 'C', true);
        $pdf->Cell(self::COL_PUBLICACION, 8, utf8_decode('Publicación'), 1, 1, 'C', true);
    }

    private function filaActividad($pdf, $numero, $a)
    {
        $fecha = !empty($a['FechaActividad']) ? date('d/m/Y', strtotime($a['FechaActividad'])) : '-';
        $hora = !empty($a['HoraAct']) ? substr($a['HoraAct'], 0, 5) : '-';
        $publicacion = !empty($a['Fecha_Public']) ? date('d/m/Y', strtotime($a['Fecha_Public'])) : '-';
        $estado = $a['EstActividad'] ?? 'Activo';

        $pdf->SetFont('Arial', '', 8);
        $titulo = utf8_decode(trim((string) ($a['TituloActividad'] ?? '')));
        $anchoTitulo = self::COL_TITULO - 3;
        $lineasTitulo = $this->dividirLineas($pdf, $titulo, $anchoTitulo);
        $alto = max(6, count($lineasTitulo) * self::ALTO_LINEA_TITULO + 1.8);

        // La fila puede ocupar más de una línea: se controla el salto antes de dibujarla.
        if ($pdf->GetY() + $alto > $pdf->GetPageHeight() - self::MARGEN_INFERIOR) {
            $pdf->AddPage();
        }

        // Color según estado
        if (strcasecmp($estado, 'Eliminado') === 0) {
            $pdf->SetTextColor(200, 0, 0);
        } elseif (strcasecmp($estado, 'Activo') === 0) {
            $pdf->SetTextColor(0, 120, 0);
        } else {
            $pdf->SetTextColor(0, 0, 0);
        }

        $x = $pdf->GetX();
        $y = $pdf->GetY();

        $pdf->Cell(self::COL_N, $alto, $numero, 1, 0, 'C');
        $pdf->Cell(self::COL_TITULO, $alto, '', 1, 0, 'L');
        $pdf->Cell(self::COL_FECHA, $alto, $fecha, 1, 0, 'C');
        $pdf->Cell(self::COL_HORA, $alto, $hora, 1, 0, 'C');
        $pdf->Cell(self::COL_LUGAR, $alto, utf8_decode($this->truncate($a['LugarAct'] ?? '', 30)), 1, 0, 'L');
        $pdf->Cell(self::COL_TIPO, $alto, utf8_decode($a['TipoActividad'] ?? ''), 1, 0, 'C');
        $pdf->Cell(self::COL_ESTADO, $alto, utf8_decode($estado), 1, 0, 'C');
        $pdf->Cell(self::COL_PUBLICADO, $alto, utf8_decode($this->truncate($a['PublicadoPor'] ?? '', 28)), 1, 0, 'L');
        $pdf->Cell(self::COL_PUBLICACION, $alto, $publicacion, 1, 1, 'C');

        // El título se escribe completo, repartido en las líneas que necesite.
        $inicio = $y + ($alto - count($lineasTitulo) * self::ALTO_LINEA_TITULO) / 2;
        foreach ($lineasTitulo as $indice => $linea) {
            $pdf->SetXY($x + self::COL_N + 1.5, $inicio + $indice * self::ALTO_LINEA_TITULO);
            $pdf->Cell($anchoTitulo, self::ALTO_LINEA_TITULO, $linea, 0, 0, 'L');
        }

        $pdf->SetXY($x, $y + $alto);
        $pdf->SetTextColor(0, 0, 0);
    }

    /**
     * Divide un texto en líneas que caben dentro del ancho indicado.
     */
    private function dividirLineas($pdf, $texto, $ancho)
    {
        $texto = trim($texto);
        if ($texto === '') {
            return [''];
        }

        $palabras = preg_split('/\s+/', $texto);
        $lineas = [];
        $actual = '';

        foreach ($palabras as $palabra) {
            $prueba = $actual === '' ? $palabra : $actual . ' ' . $palabra;
            if ($actual === '' || $pdf->GetStringWidth($prueba) <= $ancho) {
                $actual = $prueba;
            } else {
                $lineas[] = $actual;
                $actual = $palabra;
            }
        }

        if ($actual !== '') {
            $lineas[] = $actual;
        }

        return $lineas;
    }

    private function truncate($texto, $limite)
    {
        $texto = trim($texto);
        if (strlen($texto) > $limite) {
            return substr($texto, 0, $limite - 3) . '...';
        }
        return $texto;
    }
}
