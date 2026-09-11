-- Ajustes de reservas para MariaDB. Ejecutar en la base del proyecto.
-- Una reserva completada no puede tener fecha de hoy ni futura.
DELIMITER $$
CREATE OR REPLACE PROCEDURE ModificarReservaBautizo(
    IN p_CodRes INT, IN p_FechaReal DATE, IN p_HoraReal TIME, IN p_EstadoRes VARCHAR(20)
)
BEGIN
    UPDATE Reserva
    SET FechaReal = p_FechaReal,
        HoraReal = p_HoraReal,
        EstadoRes = CASE
            WHEN p_EstadoRes = 'Completado' AND p_FechaReal >= CURDATE() THEN 'Reservado'
            ELSE p_EstadoRes
        END
    WHERE CodRes = p_CodRes;
END$$
CREATE OR REPLACE PROCEDURE ModificarReservaMatrimonio(
    IN p_CodRes INT, IN p_FechaReal DATE, IN p_HoraReal TIME, IN p_EstadoRes VARCHAR(20)
)
BEGIN
    UPDATE Reserva
    SET FechaReal = p_FechaReal,
        HoraReal = p_HoraReal,
        EstadoRes = CASE
            WHEN p_EstadoRes = 'Completado' AND p_FechaReal >= CURDATE() THEN 'Reservado'
            ELSE p_EstadoRes
        END
    WHERE CodRes = p_CodRes;
END$$
CREATE OR REPLACE PROCEDURE ModificarReservaMisa(
    IN p_CodRes INT, IN p_FechaReal DATE, IN p_HoraReal TIME, IN p_EstadoRes VARCHAR(20)
)
BEGIN
    UPDATE Reserva
    SET FechaReal = p_FechaReal,
        HoraReal = p_HoraReal,
        EstadoRes = CASE
            WHEN p_EstadoRes = 'Completado' AND p_FechaReal >= CURDATE() THEN 'Reservado'
            ELSE p_EstadoRes
        END
    WHERE CodRes = p_CodRes;
END$$
ALTER EVENT Actualizar_Reservas
DO
    UPDATE Reserva SET EstadoRes = 'Completado'
    WHERE EstadoRes = 'Reservado' AND FechaReal < CURDATE()$$
DELIMITER ;

UPDATE Reserva SET EstadoRes = 'Reservado'
WHERE EstadoRes = 'Completado' AND FechaReal >= CURDATE();
