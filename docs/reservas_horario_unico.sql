-- MariaDB. Ejecutar en la base de la aplicación.
-- Comprobar antes los horarios repetidos. No se borran ni se cancelan reservas.
SELECT FechaReal, HoraReal, COUNT(*) AS Cantidad
FROM reserva
WHERE EstadoRes IS NULL OR EstadoRes <> 'Cancelado'
GROUP BY FechaReal, HoraReal
HAVING COUNT(*) > 1;

-- Un índice único permite varios NULL: las canceladas dejan libre el horario.
-- Si existen coincidencias vigentes, el índice falla y deben revisarse manualmente.
ALTER TABLE reserva
    ADD COLUMN IF NOT EXISTS HorarioOcupado TINYINT
        GENERATED ALWAYS AS (CASE WHEN EstadoRes = 'Cancelado' THEN NULL ELSE 1 END) VIRTUAL,
    ADD UNIQUE INDEX IF NOT EXISTS uq_reserva_fecha_hora (FechaReal, HoraReal, HorarioOcupado);
