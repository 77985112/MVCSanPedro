# MVCSanPedro

Sistema de gestión de la Parroquia San Pedro de Sacaba, desarrollado en PHP con
MySQLi y MariaDB. Incluye personas, catequesis, reservas de bautizo, matrimonio
y misa, certificados y avisos por correo.

## Entorno local

El proyecto se trabaja con XAMPP en Windows, PHP 8.2 y MariaDB 10.4.

1. Colocar el proyecto en `C:\xampp\htdocs\MVCSanPedro`.
2. Copiar `Conexion/Conexion.example.php` como `Conexion/Conexion.php` y ajustar
   la conexión a la base de datos local.
3. Crear una base de datos vacía llamada `bdsanpedro` e importar
   `docs/bdsanpedro_estructura.sql` desde phpMyAdmin.
4. Preparar en la instalación local los catálogos y las cuentas necesarias para
   ingresar. El esquema publicado no incluye usuarios, contraseñas ni registros.
5. Iniciar Apache y MySQL y abrir `http://localhost/MVCSanPedro/`.

Para una instalación existente, conservar su base de datos. El archivo de
estructura se utiliza sobre una base vacía; no es una actualización de datos.
Las migraciones específicas y sus instrucciones se encuentran en `docs/`.

La configuración de correo con Brevo se describe en `docs/correo_brevo.md`.
Las claves se guardan localmente y quedan excluidas del repositorio.

## Organización

- `Controlador/`: coordinación de los módulos.
- `Modelo/`: consultas y procedimientos almacenados.
- `Conexion/`: configuración de la base de datos y el correo.
- `VistaPersonal/`, `VistaCatequista/`, `VistaCelebrante/`, `VistaCoordinador/`:
  pantallas por rol. La carpeta `VistaCelebrante/` conserva su nombre interno;
  el rol se muestra como Catequisando en la interfaz.
- `src/` y `assets/`: páginas públicas y recursos de la interfaz.
- `docs/`: documentación y SQL.
- `tests/`: pruebas del proyecto.

Hay referencias con diferencias de mayúsculas y minúsculas. La instalación
actual está preparada para Windows/XAMPP; antes de usar Linux deben revisarse
esas rutas.

## Datos locales y Git

`.gitignore` excluye la conexión local, las claves del correo, los respaldos SQL
con datos, las fotografías de perfiles, algunas imágenes cargadas y las pruebas
antiguas que contienen datos reales. Estos archivos permanecen en el equipo.
Los PDF de `assets/Documentos/` son recursos del sistema; los certificados
emitidos se generan desde la aplicación.

La estructura SQL incluida exporta tablas y procedimientos sin copiar sus filas.
El repositorio no sustituye un respaldo privado de la base de datos.

## Comprobaciones

Para revisar un archivo PHP:

```powershell
C:\xampp\php\php.exe -l VistaPersonal/VistaReservas.php
```

Las pruebas de reservas y personas usan tablas temporales o datos simulados.
Revisar sus requisitos antes de ejecutarlas:

```powershell
C:\xampp\php\php.exe tests/unit/personas_reserva_test.php
C:\xampp\php\php.exe tests/unit/reservas_horarios_test.php
C:\xampp\php\php.exe tests/unit/correo_reservas_test.php
C:\xampp\php\php.exe tests/unit/cancelacion_reservas_test.php
node tests/unit/reservas_personas_browser_test.js
```

La prueba del navegador requiere Node.js 22 y Google Chrome en Windows.
