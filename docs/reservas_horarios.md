# Horarios de reservas

Las reservas comunitarias pueden compartir fecha y hora entre sí, sean bautizos,
matrimonios o misas. Una reserva Privado u Otro requiere un horario exclusivo:
no se mezcla con otra privada, con Otro ni con una comunitaria vigente.
Otra hora del mismo día sí está permitida.

Al cancelar la última reserva vigente de un horario, este queda disponible.
Se conserva el historial de cancelaciones. Al modificar se permite conservar
el horario propio o reprogramar conforme a la misma regla; el tipo comunitario
se obtiene de la reserva guardada, no de un campo enviado al modificar.

En MariaDB, ejecutar primero `docs/reservas_horario_unico.sql` si todavía no se
aplicó y después `docs/reservas_horarios_comunitarios.sql`. Ambas migraciones
ya se aplicaron a la base local, sin borrar ni cancelar reservas existentes.
En otra instalación, revisar coincidencias vigentes antes de crear el índice
inicial; si existen, el índice inicial no se crea y deben revisarse esos casos.

`HorarioOcupado` es una columna calculada: vale NULL para las canceladas o
comunitarias y 1 para las demás. El índice `uq_reserva_fecha_hora` impide dos
reservas exclusivas vigentes en el mismo horario y permite varias comunitarias.
El modelo usa un bloqueo de MariaDB (`GET_LOCK`) por base de datos para consultar
y guardar dentro del mismo turno y transacción. Así impide también mezclar
privadas con comunitarias ante peticiones simultáneas del sistema. Libera el
bloqueo al terminar, incluso ante errores, antes de solicitar el envío de correo.

Las escrituras SQL o llamadas directas a procedimientos que omitan el modelo
solo tienen la protección parcial del índice; deben usar el modelo para aplicar
la regla completa. No se modificaron las firmas de los procedimientos.
La validación compara la misma hora de inicio, sin calcular duración ni
solapamiento entre horas distintas. Se conserva la restricción existente
para las celebraciones de Comunión y Confirmación al registrar.

El controlador muestra un aviso de horario ocupado. Las transacciones existentes
del modelo revierten un alta o una modificación rechazada, y el controlador no
envía correos de confirmación por esas operaciones fallidas.

Prueba: `php tests/unit/reservas_horarios_test.php`. Usa los procedimientos reales
sobre tablas temporales que copian el índice; simula el correo. Cubre los nueve
cruces entre tipos de celebración para horarios privados y comunitarios,
edición, reprogramación, cancelación parcial y total de horarios compartidos,
reutilización del horario, ausencia de registros parciales y contención del
bloqueo mediante una segunda conexión, sin escribir en las tablas reales.
