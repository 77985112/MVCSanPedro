<?php
session_start();
include '../Conexion/Conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conexion = (new Conexion())->getConnection();
    $Catequista = $_SESSION['CiPersona'] ?? null;

    $celebrantes = $_POST['celebrantes'];
    $response = ["success" => true, "celebrantes" => []];
    $ciCel='';
    $detallesPermitidos = ['Certificado de Nacimiento', 'Bautizo'];

    $stmtDoc = null;
    if ($Catequista && !empty($Catequista['Gestion'])) {
        $stmtDoc = $conexion->prepare("INSERT INTO documento
            (Gestion, CiCat, IdGrupo, IdInscripcion, NumeroDoc, DetalleDoc, LibroDoc, PaginaDoc, PartidaDoc, ParroquiaDoc, FechaDoc)
            VALUES (?, ?, ?, ?, 'Presentado', ?, '', '', '', ?, NULLIF(?, ''))");
    }

    foreach ($celebrantes as $celebrante) {
        $ciCel = $celebrante['ciCel'];
        $sacramento = $celebrante['sacramento'];
        $usuarioCel = $ciCel;
        // Contraseña de un solo uso para el primer ingreso.
        $claveTemporal = 'Tmp' . strtoupper(bin2hex(random_bytes(4)));
        $claveCel = password_hash($claveTemporal, PASSWORD_DEFAULT);
        $idGrupo = $celebrante['idGrupo'];
        $preValor = $celebrante['preValor'];
        $codTipoItem = $celebrante['codTipoItem'];
        $documentos = $celebrante['documentos'] ?? [];
        if (!is_array($documentos)) {
            $documentos = [];
        }
        $documentos = array_values(array_intersect($detallesPermitidos, $documentos));

        if ($sacramento === 'Primera Comunión') {
            $stmtEdad = $conexion->prepare("SELECT TIMESTAMPDIFF(YEAR, FechaNac, CURDATE()) AS Edad,
                                                   CONCAT(Nombre, ' ', ApPaterno, ' ', ApMaterno) AS Nombre
                                            FROM persona WHERE CiPersona = ?");
            $stmtEdad->bind_param("s", $ciCel);
            $stmtEdad->execute();
            $personaEdad = $stmtEdad->get_result()->fetch_assoc();
            $stmtEdad->close();

            if ($personaEdad && (int) $personaEdad['Edad'] >= 16) {
                $response['success'] = false;
                $response['message'] = trim($personaEdad['Nombre']) . ' tiene ' . (int) $personaEdad['Edad'] . ' años. Para Primera Comunión se aceptan hasta 15 años; debe registrarse en Confirmación.';
                break;
            }
        }

        $stmt = $conexion->prepare("CALL RegistrarCelebrante(?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param("ssssisi", $ciCel, $sacramento, $usuarioCel, $claveCel, $idGrupo, $preValor, $codTipoItem);

        if ($stmt->execute()) {
            $result = $conexion->query("SELECT CONCAT(percel.Nombre, ' ', percel.ApPaterno,' ', percel.ApMaterno) AS Celebrante, celebrante.UsuarioCel as usuario, celebrante.ClaveCel as clave, ins.IdInscripcion as idins FROM `celebrante` INNER JOIN inscripcion ins ON ins.CiCel = celebrante.CiCel INNER JOIN `persona` percel ON percel.CiPersona = celebrante.CiCel WHERE celebrante.CiCel = '$ciCel'");
            
            if ($row = $result->fetch_assoc()) {
                // La respuesta al navegador muestra la clave de un solo uso,
                // no el hash que quedó guardado en la base.
                $row['clave'] = $claveTemporal;
                $response["celebrantes"][] = $row;
                $idInscripcion = (int) $row['idins'];

                if ($stmtDoc && $idInscripcion > 0) {
                    foreach ($documentos as $detalle) {
                        $parroquiaDoc = '';
                        $fechaDoc = '';

                        if ($detalle === 'Certificado de Nacimiento') {
                            $fechaDoc = date('Y-m-d');
                        } elseif ($detalle === 'Bautizo') {
                            $stmtBautizo = $conexion->prepare("SELECT Fecha, Parroquia
                                                               FROM antecedente_sacramental
                                                               WHERE CiPersona = ? AND Sacramento = 'Bautizo'
                                                               ORDER BY Fecha DESC
                                                               LIMIT 1");
                            $stmtBautizo->bind_param("s", $ciCel);
                            $stmtBautizo->execute();
                            $bautizo = $stmtBautizo->get_result()->fetch_assoc();
                            $stmtBautizo->close();

                            if ($bautizo) {
                                $fechaDoc = $bautizo['Fecha'] ?? '';
                                $parroquiaDoc = $bautizo['Parroquia'] ?? '';
                            }
                        }

                        $stmtDoc->bind_param("ssiisss", $Catequista['Gestion'], $Catequista['CiCat'], $Catequista['IdGrupo'], $idInscripcion, $detalle, $parroquiaDoc, $fechaDoc);
                        $stmtDoc->execute();
                    }
                }
            }
        } else {
            $response["success"] = false;
            $response["message"] = "Error al registrar el catequisando: " . $stmt->error;
            break;
        }

        $stmt->close();
    }

    if ($stmtDoc) {
        $stmtDoc->close();
    }

    echo json_encode($response);
    $conexion->close();
}
?>