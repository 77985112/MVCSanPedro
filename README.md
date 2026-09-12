# MVCSanPedro

Sistema de gestión de la Parroquia San Pedro de Sacaba, desarrollado en PHP con
MySQLi y MariaDB. Incluye personas, catequesis, reservas, certificados, avisos
por correo con Brevo y una integración de Google Drive para respaldar PDF.

## Instalación local

Entorno utilizado: XAMPP en Windows, PHP 8.2 y MariaDB 10.4.

1. Colocar el proyecto en `C:\xampp\htdocs\MVCSanPedro`.
2. Copiar `Conexion/Conexion.example.php` como `Conexion/Conexion.php` y ajustar
   la conexión local.
3. En una instalación nueva, crear una base vacía `bdsanpedro` e importar el
   [esquema de la versión inicial](https://github.com/77985112/MVCSanPedro/blob/a8228bb62227e4d3c67081eee8b8dddcc08eb0eb/docs/bdsanpedro_estructura.sql).
   Preparar los catálogos y las cuentas de acceso: el esquema no contiene
   usuarios, contraseñas ni registros personales.
4. Desde la carpeta del proyecto, ejecutar `composer install --no-dev`.
   Se utilizan las versiones de `composer.lock`; `vendor/` no se publica en Git.
5. Configurar las credenciales privadas de las APIs como se indica abajo.
6. Iniciar Apache y MySQL y abrir `http://localhost/MVCSanPedro/`.

Para una instalación existente, conservar su base de datos y su configuración
privada. El esquema inicial sirve para una base vacía; no reemplaza una migración
ni un respaldo de la instalación actual.

Hay referencias con diferencias de mayúsculas y minúsculas. Antes de instalar
en Linux deben revisarse las rutas afectadas.

## Brevo: correos de reservas

Los formularios de bautizo, matrimonio y misa guardan el correo en
`reserva.CorreoSolicitante`. La confirmación inicial se solicita marcando
“Enviar confirmación por correo”. Una cancelación confirmada solicita el aviso
si la reserva tiene correo. El fallo del correo no revierte la operación guardada.

`Conexion/configCorreo.php` lee `BREVO_API_KEY`, `BREVO_SENDER_EMAIL` y
`BREVO_SENDER_NAME` del entorno, o la configuración privada de
`Conexion/correo.local.php`. La clave y el remitente se configuran en cada equipo;
no se incluyen en GitHub.

El envío se realiza mediante HTTPS con cURL. La aceptación de Brevo no confirma
la entrega en la bandeja del destinatario. La
[documentación previa del módulo](https://github.com/77985112/MVCSanPedro/blob/a8228bb62227e4d3c67081eee8b8dddcc08eb0eb/docs/correo_brevo.md)
permanece disponible en el historial.

## Google Drive: certificados

Proyecto de Google Cloud: **SanPedroRespaldoCertificados**.
Se utiliza Google Drive API, OAuth 2.0 y el permiso
`https://www.googleapis.com/auth/drive.file`.

En cada instalación se necesitan los archivos privados:
- `Config/credentials.json`: credenciales del cliente OAuth de aplicación web.
- `Config/google_token.json`: token creado al autorizar la cuenta desde el sistema.

Ambos están excluidos de Git. `Config/.htaccess` bloquea el acceso HTTP a JSON
cuando Apache permite estas reglas. En otros servidores debe configurarse la
protección equivalente o mover los secretos fuera de la carpeta pública y
actualizar sus rutas.

URI de redireccionamiento configurada en PHP:
`http://localhost/MVCSanPedro/Controlador/googleDriveCallback.php`.
Debe coincidir con la del cliente OAuth. Cambiar ambas al usar otro dominio o puerto.

El panel incluye un enlace a `VistaPersonal/VistaGoogleDrive.php`. Los archivos
`DocBautizo.php`, `DocConfirmacion.php` y `DocMatrimonio.php` incorporan la
llamada de respaldo antes de mostrar el PDF.

### Estado de esta versión

Esta publicación guarda la implementación actual. La revisión del código detectó
pendientes: asignar la carpeta al PDF al subirlo, guardar el ID y estado del respaldo
en la base de datos, evitar duplicados y completar los controles OAuth/CSRF y el
manejo de errores. La existencia del código o del token no demuestra que una
subida real haya terminado correctamente.

## Organización

- `Controlador/`: coordinación de los módulos.
- `Modelo/`: acceso a datos y operaciones con las APIs.
- `Conexion/`: configuración de base de datos y correo.
- `Config/`: configuración del cliente de Google.
- `VistaPersonal/`, `VistaCatequista/`, `VistaCelebrante/`,
  `VistaCoordinador/`: pantallas por rol. El nombre interno VistaCelebrante se
  conserva; el rol se muestra como Catequisando.
- `src/` y `assets/`: páginas públicas y recursos de interfaz.
- `fpdf/` y `FPDI-master/`: generación de certificados y reportes.

Las carpetas `docs/` y `tests/`, y los scripts de mantenimiento de la raíz,
se movieron fuera de esta carpeta de trabajo. Esta versión refleja esa decisión.
Los archivos anteriormente publicados permanecen en el historial de Git; los
archivos externos que nunca se publicaron no forman parte de este repositorio.


La copia de GitHub omite las instrucciones locales del asistente y los manuales,
ejemplos y herramientas de generación de fuentes de FPDF. Se conservan en este
equipo, junto con los archivos privados excluidos. La biblioteca FPDF, sus fuentes
utilizadas y la licencia siguen incluidos para generar los certificados.

## Datos privados y comprobaciones

El repositorio excluye credenciales, tokens, la conexión local, respaldos SQL con
datos personales, fotografías cargadas y dependencias descargadas. GitHub respalda
el código publicado; no sustituye un respaldo privado de la base de datos, los
archivos cargados o las carpetas trasladadas fuera del proyecto.

Para comprobar la sintaxis de un archivo PHP sin ejecutarlo:

```powershell
C:\xampp\php\php.exe -l VistaPersonal/DocBautizo.php
```

Las pruebas anteriores pueden consultarse en el historial. Si se recuperan para
ejecutarlas desde otra ubicación, revisar primero sus rutas y sus efectos sobre
la base de datos. Esta publicación no constituye una prueba real de correo ni
de subida a Google Drive.
