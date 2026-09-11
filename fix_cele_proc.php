<?php
$conn = new mysqli("localhost", "root", "", "bdsanpedro");
$conn->set_charset("utf8mb4");

$conn->query("DROP PROCEDURE IF EXISTS CelebranteSinGrupo");

$sql = "CREATE PROCEDURE CelebranteSinGrupo(IN p_Sacramento VARCHAR(50))
BEGIN
    IF p_Sacramento = 'Primera Comunión' THEN
        SELECT 
            I.IdInscripcion,
            c.CiCel, 
            CONCAT(p.Nombre, ' ', p.ApPaterno, ' ', p.ApMaterno) AS NombreCompleto, 
            t.DescripItem,
            TIMESTAMPDIFF(YEAR, p.FechaNac, CURDATE()) AS Edad
        FROM 
            inscripcion i
        INNER JOIN 
            celebrante c ON i.CiCel = c.CiCel
        INNER JOIN 
            persona p ON c.CiCel = p.CiPersona
        INNER JOIN 
            detalleTipoins dti ON i.IdInscripcion = dti.IdInscripcion
        INNER JOIN 
            TipoIns t ON dti.CodTipoItem = t.CodTipoItem
        WHERE 
            i.IdGrupo = 1;
    ELSEIF p_Sacramento = 'Confirmación' THEN
        SELECT  
            I.IdInscripcion,
            c.CiCel, 
            CONCAT(p.Nombre, ' ', p.ApPaterno, ' ', p.ApMaterno) AS NombreCompleto, 
            t.DescripItem,
            TIMESTAMPDIFF(YEAR, p.FechaNac, CURDATE()) AS Edad
        FROM 
            inscripcion i
        INNER JOIN 
            celebrante c ON i.CiCel = c.CiCel
        INNER JOIN 
            persona p ON c.CiCel = p.CiPersona
        INNER JOIN 
            detalleTipoins dti ON i.IdInscripcion = dti.IdInscripcion
        INNER JOIN 
            TipoIns t ON dti.CodTipoItem = t.CodTipoItem
        WHERE 
            i.IdGrupo = 22;
    END IF;
END";

$result = $conn->query($sql);
echo $result ? "OK" : "Error: " . $conn->error;
$conn->close();
