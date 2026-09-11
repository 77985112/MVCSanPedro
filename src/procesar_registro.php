<?php
include '../Conexion/Conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conexion = (new Conexion())->getConnection();

    $celebrantes = $_POST['celebrantes'];
    $response = ["success" => true, "celebrantes" => []];
    $ciCel='';

    foreach ($celebrantes as $celebrante) {
        $ciCel = $celebrante['ciCel'];
        $sacramento = $celebrante['sacramento'];
        $usuarioCel = $ciCel;
        $claveCel = $ciCel;
        $idGrupo = $celebrante['idGrupo'];
        $preValor = $celebrante['preValor'];
        $codTipoItem = $celebrante['codTipoItem'];
        $stmt = $conexion->prepare("CALL RegistrarCelebrante(?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param("ssssisi", $ciCel, $sacramento, $usuarioCel, $claveCel, $idGrupo, $preValor, $codTipoItem);

        if ($stmt->execute()) {
            $result = $conexion->query("SELECT CONCAT(percel.Nombre, ' ', percel.ApPaterno,' ', percel.ApMaterno) AS Celebrante, celebrante.UsuarioCel as usuario, celebrante.ClaveCel as clave, ins.IdInscripcion as idins FROM Celebrante INNER JOIN inscripcion ins ON ins.CiCel = celebrante.CiCel INNER JOIN persona percel ON percel.CiPersona = celebrante.CiCel WHERE celebrante.CiCel = '$ciCel'");
            
            if ($row = $result->fetch_assoc()) {
                $response["celebrantes"][] = $row;
            }
        } else {
            $response["success"] = false;
            $response["message"] = "Error al registrar el catequisando: " . $stmt->error;
            break;
        }

        $stmt->close();
    }

    echo json_encode($response);
    $conexion->close();
}
?>