<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../Conexion/Conexion.php';

$response = ['success' => false, 'message' => 'Solicitud no válida.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conexion = (new Conexion())->getConnection();

    $ciFamiliar = trim((string) ($_POST['ciFamiliar'] ?? ''));
    $rolFam = trim((string) ($_POST['rolFam'] ?? ''));
    $parentesco = trim((string) ($_POST['parentesco'] ?? ''));
    $celebrantes = $_POST['celebrantes'] ?? [];

    if (!is_array($celebrantes)) {
        $celebrantes = [$celebrantes];
    }

    if ($ciFamiliar === '' || $rolFam === '' || $parentesco === '' || count($celebrantes) === 0) {
        $response['message'] = 'Selecciona el apoderado, el rol, el parentesco y los catequizandos.';
        echo json_encode($response);
        exit;
    }

    try {
        // Verificar que el apoderado exista como persona.
        $stmt = $conexion->prepare("SELECT COUNT(*) AS total FROM persona WHERE CiPersona = ?");
        $stmt->bind_param("s", $ciFamiliar);
        $stmt->execute();
        $existe = (int) $stmt->get_result()->fetch_assoc()['total'];
        $stmt->close();

        if ($existe === 0) {
            $response['message'] = 'El apoderado no está registrado como persona.';
            echo json_encode($response);
            exit;
        }

        $conexion->begin_transaction();

        // Registrar al familiar si todavía no existe.
        $stmt = $conexion->prepare("INSERT IGNORE INTO familiar (CiFamiliar) VALUES (?)");
        $stmt->bind_param("s", $ciFamiliar);
        $stmt->execute();
        $stmt->close();

        // Vincular al familiar con cada inscripción.
        $stmt = $conexion->prepare("
            INSERT INTO insfamiliar (CiFamiliar, IdInscripcion, RolFam, Parentesco)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE RolFam = VALUES(RolFam), Parentesco = VALUES(Parentesco)
        ");

        foreach ($celebrantes as $idInscripcion) {
            $idInscripcion = (int) $idInscripcion;
            if ($idInscripcion <= 0) {
                continue;
            }

            // Verificar que la inscripción exista.
            $stmtIns = $conexion->prepare("SELECT COUNT(*) AS total FROM inscripcion WHERE IdInscripcion = ?");
            $stmtIns->bind_param("i", $idInscripcion);
            $stmtIns->execute();
            $existeIns = (int) $stmtIns->get_result()->fetch_assoc()['total'];
            $stmtIns->close();

            if ($existeIns === 0) {
                throw new Exception('No existe la inscripción del catequizando.');
            }

            $stmt->bind_param("siss", $ciFamiliar, $idInscripcion, $rolFam, $parentesco);
            $stmt->execute();
        }

        $stmt->close();
        $conexion->commit();

        $response['success'] = true;
        $response['message'] = 'Apoderado registrado correctamente para todos los catequizandos.';
    } catch (Throwable $e) {
        $conexion->rollback();
        $response['message'] = 'Error al registrar apoderado: ' . $e->getMessage();
    }

    $conexion->close();
}

echo json_encode($response);
