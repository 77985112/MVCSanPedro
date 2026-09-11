<?php
require('../fpdf/fpdf.php');
require_once '../Conexion/Conexion.php';

if (isset($_GET['ciFamiliar'])) {
    $ciFamiliar = $_GET['ciFamiliar'];

    $conexion = new Conexion();
    $conn = $conexion->getConnection();

    $stmt = $conn->prepare("CALL Compromiso_Cel(?)");
    $stmt->bind_param("s", $ciFamiliar);
    $stmt->execute();
    $result = $stmt->get_result();

    $pdf = new FPDF();
    $pdf->AddPage();

    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'COMPROMISO/REGLAMENTO DE LOS PADRES DE FAMILIA DE - ' . date('Y'), 0, 1, 'C');
    $pdf->Ln(10);


    $rowApoderado = null;
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if (!$rowApoderado) {
                $rowApoderado = [
                    'Nombre' => $row['Apoderado'],
                    'Celebrantes' => []
                ];
            }
            $rowApoderado['Celebrantes'][] = [
                'Nombre' => $row['Celebrante'],
                'Usuario' => $row['Usuario'],
                'Clave' => $row['Clave']
            ];
        }
    }

    if ($rowApoderado) {
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, "Por la presente, YO:", 0, 1);
        $pdf->Cell(0, 10, strtoupper($rowApoderado['Nombre']), 0, 1);
        $pdf->Cell(0, 10, "Como padre de familia o Tutor de:", 0, 1);

        foreach ($rowApoderado['Celebrantes'] as $celebrante) {
            $pdf->Cell(0, 10, "{$celebrante['Nombre']} (Usuario: {$celebrante['Usuario']}, Clave: {$celebrante['Clave']})", 0, 1);
        }

        $pdf->Ln(5);
        $pdf->Cell(0, 10, "Me COMPROMETO a:", 0, 1);
        $pdf->MultiCell(0, 10,
            "1.- Asistir a las reuniones de los padres de familia, cada segundo lunes del mes a hrs. 19:00, y cada vez que se nos haga el llamado correspondiente.
            2.- Mandar a mi hijo/a, apoderado/a todas las actividades de la catequesis en esta gestión. Los días Sábados de las 14:00 a 17:00 y los domingos de las 11:00 a 12:00 a la Santa Eucaristía. Incluidas las actividades deportivas, excursiones, retiros, hasta incluso en los días de Peatón.
            3.- Cumplir y hacer cumplir con las sanciones y obligaciones que se quede en mutuo acuerdo, para el buen desempeño de este sacramento."
        );

        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        $dia = date('d');
        $mes = $meses[date('n')];
        $año = date('Y');
        $fechaActual = "$dia de $mes del $año";

        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 10, "Sacaba, $fechaActual", 0, 1, 'R');
        $pdf->Ln(1);
        
        $pdf->Ln(20);
        $pdf->Cell(0, 10, "__________________________", 0, 1);
        $pdf->Cell(0, 10, "Firma y C.I", 0, 1);
    }

    $stmt->close();
    $conn->close();
    
    $pdf->Output($ciFamiliar.' - Compromiso_Cel.pdf', 'D');
} else {
    echo "No se ha proporcionado el CI del familiar.";
}
?>