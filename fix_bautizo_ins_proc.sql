DELIMITER $$
DROP PROCEDURE IF EXISTS VerInsSacramentoBautizo$$
CREATE PROCEDURE VerInsSacramentoBautizo(IN ciPersona VARCHAR(15))
BEGIN
    SELECT 
        CONCAT(bautizado.Nombre, ' ', bautizado.ApPaterno, ' ', bautizado.ApMaterno) AS NombreBautizado,
        s.Rol,
        ins.CodIns,
        ins.CodSac,
        ins.CodPer,
        ins.FechaIns,
        ts.DescripSac,
        CONCAT(padre.Nombre, ' ', padre.ApPaterno, ' ', padre.ApMaterno) AS NombrePadre,
        CONCAT(madre.Nombre, ' ', madre.ApPaterno, ' ', madre.ApMaterno) AS NombreMadre,
        CONCAT(padrino.Nombre, ' ', padrino.ApPaterno, ' ', padrino.ApMaterno) AS NombrePadrino,
        CONCAT(madrina.Nombre, ' ', madrina.ApPaterno, ' ', madrina.ApMaterno) AS NombreMadrina,
        carg.DescripCar AS cargo,
        CONCAT(per.Nombre, ' ', per.Paterno, ' ', per.Materno) AS Inscriptor
    FROM 
        Solicitante s
    INNER JOIN 
        persona bautizado ON s.CiPersona = bautizado.CiPersona
    INNER JOIN 
        inssacramento ins ON s.CodIns = ins.CodIns
    INNER JOIN 
        personal per ON ins.CodPer = per.CodPer
    INNER JOIN 
        cargo carg ON per.CodCar = carg.CodCar
    INNER JOIN 
        tiposacramento ts ON ins.CodSac = ts.CodSac
    LEFT JOIN 
        Solicitante spPadre ON spPadre.CodIns = s.CodIns AND spPadre.Rol = 'Papá'
    LEFT JOIN 
        persona padre ON spPadre.CiPersona = padre.CiPersona
    LEFT JOIN 
        Solicitante spMadre ON spMadre.CodIns = s.CodIns AND spMadre.Rol = 'Mamá'
    LEFT JOIN 
        persona madre ON spMadre.CiPersona = madre.CiPersona  
    LEFT JOIN 
        Solicitante spPadrino ON spPadrino.CodIns = s.CodIns AND spPadrino.Rol = 'Padrino'
    LEFT JOIN 
        persona padrino ON spPadrino.CiPersona = padrino.CiPersona
    LEFT JOIN 
        Solicitante spMadrina ON spMadrina.CodIns = s.CodIns AND spMadrina.Rol = 'Madrina'
    LEFT JOIN 
        persona madrina ON spMadrina.CiPersona = madrina.CiPersona
    WHERE 
        bautizado.CiPersona = ciPersona 
        AND s.Rol = 'Bautizado'
        AND ts.CodSac = 1
    GROUP BY ins.CodIns;
END$$
DELIMITER ;
