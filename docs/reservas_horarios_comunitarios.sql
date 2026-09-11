-- MariaDB. Aplicar después de reservas_horario_unico.sql.
-- Solo modifica la columna calculada; no borra ni cancela reservas.
ALTER TABLE reserva
    MODIFY COLUMN HorarioOcupado TINYINT
        GENERATED ALWAYS AS (
            CASE WHEN EstadoRes = 'Cancelado' OR Realizacion = 'Comunitario'
                THEN NULL ELSE 1 END
        ) VIRTUAL;

-- El índice existente mantiene exclusividad entre reservas no comunitarias.
-- El modelo coordina altas y modificaciones con GET_LOCK para impedir mezclar
-- comunitarias con privadas/Otro y evitar conflictos entre peticiones simultáneas.
