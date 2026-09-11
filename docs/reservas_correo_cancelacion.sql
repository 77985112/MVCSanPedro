-- MariaDB: conserva los datos existentes y permite volver a ejecutar la migración.
ALTER TABLE reserva
    ADD COLUMN IF NOT EXISTS CorreoSolicitante VARCHAR(254) NULL DEFAULT NULL;
