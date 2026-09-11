# Confirmaciones y cancelaciones de reservas con Brevo

La integración está en Personal > Reservas, en los formularios de bautizo,
matrimonio y misa. Introducir el correo y marcar «Enviar confirmación por correo».
El correo se guarda en la reserva aunque no se marque la confirmación inicial.
Al cancelar una reserva con correo se envía automáticamente el aviso de cancelación.
Si el campo queda vacío, se puede registrar o cancelar, pero no enviar avisos.

## Cancelar una reserva

1. Abrir Celebración > Vista General, seleccionar la fecha y pulsar el ojo de la reserva.
2. En Modificar datos de la reserva, comprobar el correo del solicitante. Las reservas
   antiguas no tienen un correo recuperable; se puede completar en este formulario.
3. Seleccionar Cancelado y pulsar Modificar Reserva.

La cancelación conserva su fecha y hora originales. Primero se confirma el cambio
en la base de datos y luego se solicita el envío a Brevo. La pantalla informa si
Brevo aceptó el aviso, si no había correo o si el envío no pudo confirmarse.
El fallo del correo no revierte la cancelación. Editar solamente fecha, hora o correo
no envía un aviso de cancelación.

Solo las reservas en estado Reservado se pueden modificar. Un bloqueo de la fila
durante la actualización impide que dos peticiones cancelen y notifiquen la misma
reserva. El formulario también tiene un token de sesión y redirige después del guardado.
No hay reintentos automáticos ni una cola persistente de mensajes: si el proceso
se interrumpe después de guardar y antes del envío, el aviso puede quedar sin enviar.

## Actualización de la base de datos

Ejecutar `docs/reservas_correo_cancelacion.sql` en la base de datos de la aplicación
antes de usar esta versión. Agrega `reserva.CorreoSolicitante`, opcional, con capacidad
de 254 caracteres. Se puede ejecutar nuevamente en MariaDB; no borra datos ni modifica
los procedimientos almacenados. Esta actualización ya se aplicó a la base local.

El alta y el correo se guardan en una misma transacción. Los tres procedimientos de
registro actuales terminan insertando la fila de Reserva; el modelo usa el
`LAST_INSERT_ID()` de esa misma conexión para asociar el correo a la reserva correcta.

## Opción sencilla: colocar la clave en PHP

Abrir **Conexion/correo.local.php** y completar los valores entre comillas:

```php
return [
    'api_key' => 'PEGA_AQUI_TU_CLAVE_DE_BREVO',
    'remitente' => 'tu-remitente@tu-dominio.com',
    'nombre' => 'Parroquia San Pedro de Sacaba',
];
```

Editar únicamente esos valores y conservar la protección del comienzo del archivo.
Guardar y usar el formulario de reservas. Esta opción no requiere modificar Apache
ni guardar la clave en la base de datos. El remitente y su dominio deben estar
configurados en Brevo. No compartir el archivo ni incluirlo en copias públicas.

El archivo está excluido de Git y protegido contra acceso web directo mediante PHP
y Conexion/.htaccess. Los valores locales no vacíos tienen prioridad sobre Apache;
los vacíos conservan la configuración de Apache o el nombre predeterminado.
Si se despliega el proyecto desde Git, crear este archivo privado en el servidor.

## Alternativa: colocar la clave en Apache (XAMPP)

1. En el panel de XAMPP, junto a Apache, abrir **Config > Apache (httpd.conf)**.
   En esta instalación corresponde a **C:/xampp/apache/conf/httpd.conf**.
2. Agregar al final del archivo el siguiente bloque, fuera de otros bloques.
   Sustituir los dos valores de ejemplo por la clave privada de Brevo y el
   correo remitente configurado en tu cuenta:

```apache
<Directory "C:/xampp/htdocs/MVCSanPedro">
    SetEnv BREVO_API_KEY "PEGA_AQUI_TU_CLAVE_DE_BREVO"
    SetEnv BREVO_SENDER_EMAIL "tu-remitente@tu-dominio.com"
    SetEnv BREVO_SENDER_NAME "Parroquia San Pedro de Sacaba"
</Directory>
```

3. Guardar el archivo y reiniciar Apache desde XAMPP.
4. Configurar el remitente y autenticar su dominio según las indicaciones de Brevo.
   La clave por sí sola no reemplaza la configuración del remitente.

La clave NO se escribe en index.php, JavaScript, ni en este documento.
Conexion/configCorreo.php lee estas variables del servidor con getenv() y aplica
los valores opcionales de Conexion/correo.local.php.
El archivo httpd.conf queda fuera de htdocs. No compartirlo una vez que contenga
la clave. No se requiere instalar Laravel, Composer ni un lector de archivos .env.
Las variables SetEnv de Apache no se aplican a PHP ejecutado desde la terminal.

## Comportamiento

- El correo es obligatorio solo si se solicita la confirmación inicial. Todo correo
  no vacío se valida en PHP antes de registrar o modificar una reserva.
- Se guarda primero mediante los métodos y procedimientos existentes.
- El mensaje contiene celebración, fecha y hora; no incluye cédulas ni datos
  de padres o padrinos. Distingue entre reserva registrada y reserva cancelada.
- Brevo acepta el mensaje para envío: eso no garantiza su entrega. Consultar
  los registros de correos transaccionales de Brevo para conocer entrega o rechazo.
- Si falta configuración, Brevo rechaza el envío o hay un problema de conexión,
  se mantiene la reserva y se muestra una advertencia. No registrar de nuevo.
  Ante un tiempo de espera agotado, el proveedor podría haber recibido el mensaje.
- No se realizan reintentos automáticos, recordatorios ni reenvíos posteriores.
- El aviso se muestra una vez después de redirigir. Los formularios usan un token
  de sesión que se renueva al guardar para rechazar su reenvío accidental.

## Archivos

- VistaPersonal/VistaReservas.php: formularios, validación de token y avisos.
- VistaPersonal/modificar_reserva.php: correo editable, cancelación y avisos.
- Modelo/modeloReserva.php: persistencia del correo y transacciones de las reservas.
- Controlador/controladorReserva.php: validación y coordinación después del guardado.
- Modelo/modeloCorreo.php: mensaje HTML y petición HTTPS a Brevo con PHP cURL.
- Conexion/configCorreo.php: lectura de configuración privada.

## Verificación

Pruebas con lecturas de la base local, guardado simulado o tablas temporales y correo
simulado. No modifican reservas reales ni envían mensajes:

```powershell
C:/xampp/php/php.exe tests/unit/correo_reservas_test.php
C:/xampp/php/php.exe tests/unit/cancelacion_reservas_test.php
```

Después de configurar Apache y Brevo, probar con una reserva autorizada y un
correo propio: comprobar que se guarda una sola vez y revisar la entrega en
Brevo y en la bandeja de entrada. No se ha comprobado un envío real como parte
de la implementación.

Se requiere PHP cURL con certificados de confianza configurados. Si el registro
de PHP informa cURL 60, revisar curl.cainfo en php.ini y su archivo de certificados;
no desactivar la verificación HTTPS. El registro técnico solo informa códigos,
sin imprimir claves ni direcciones de correo.

Referencias: [Envío con Brevo](https://developers.brevo.com/docs/send-a-transactional-email)
y [SetEnv en Apache](https://httpd.apache.org/docs/2.4/mod/mod_env.html#setenv).
