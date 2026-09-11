# Instrucciones para trabajar en MVCSanPedro

## Prioridad: conservar la estructura existente

El usuario solicita que los cambios respeten la organización actual del proyecto.
Realizar cambios puntuales dentro del módulo correspondiente. No reestructurar,
mover o renombrar carpetas, archivos, clases o métodos por iniciativa propia.
Una reorganización solo corresponde cuando el usuario la solicite explícitamente.
No incorporar frameworks, enrutadores, capas de servicios ni sistemas de compilación
para resolver cambios que caben en la implementación actual.

## Organización observada

- `index.php`: página pública de inicio; carga un controlador y renderiza HTML.
- `Controlador/`: controladores PHP que coordinan los modelos.
- `Modelo/`: acceso a datos con MySQLi, consultas y procedimientos almacenados.
- `Conexion/`: conexión a la base de datos.
- `VistaPersonal/`, `VistaCatequista/`, `VistaCelebrante/` y `VistaCoordinador/`:
  pantallas y acciones organizadas por rol.
- `src/`: páginas públicas y scripts de procesamiento existentes.
- `assets/`: estilos, JavaScript, imágenes y dependencias de interfaz.
- `Verificar*.php` y `logout.php`: scripts de sesión y verificación en la raíz.
- `fpdf/` y `FPDI-master/`: bibliotecas para PDF.
- `docs/`: documentación y SQL; también existen archivos SQL en la raíz.
- `tests/`: configuración de PHPUnit, pruebas unitarias y funcionales y ejecutor PHP.

## Reglas para los cambios

1. Leer primero la vista, el controlador y el modelo implicados según el alcance.
   Seguir las convenciones del archivo, incluso si otros módulos son diferentes.
2. Mantener nombres de campos, claves de arrays, parámetros, respuestas JSON,
   variables de sesión, enlaces y acciones de formularios salvo que el cambio
   solicitado requiera modificarlos; revisar sus consumidores en ese caso.
3. Preservar las rutas de entrada y las relaciones de `include` y `require_once`.
   Hay referencias con diferencias de mayúsculas, como `../modelo/` frente a
   `Modelo/` y `../conexion/` frente a `Conexion/`. No normalizarlas globalmente;
   verificar las rutas afectadas y usar el nombre real en referencias nuevas.
4. Mantener el acceso a datos en el patrón existente. Antes de modificar una llamada
   `CALL`, comprobar el nombre, orden y tipos de parámetros del procedimiento.
   No cambiar el esquema ni ejecutar scripts SQL de reparación como parte de una
   edición no relacionada. No asumir que un volcado refleja la base activa.
5. Conservar el diseño visual, las clases CSS y las bibliotecas existentes salvo
   que el usuario solicite cambios de interfaz. Evitar reformateos completos y
   cambios de codificación ajenos a la tarea.
6. No editar bibliotecas de terceros para resolver lógica de la aplicación.
7. Mantener intactos los cambios previos del usuario. No revertir archivos ni
   introducir limpiezas o refactorizaciones ajenas al pedido.

## Validación y entrega

- Revisar la sintaxis con `php -l` de los archivos PHP modificados cuando PHP esté
  disponible, y comprobar el flujo afectado según corresponda.
- Revisar los requisitos y efectos de las pruebas antes de ejecutarlas: existen
  pruebas y scripts que dependen de la base de datos y del entorno local.
- Para cambios exclusivamente documentales no es necesario ejecutar la aplicación.
- Explicar qué archivos cambiaron, qué se validó y cualquier limitación real.
- No afirmar que se probó un flujo, la base de datos o todo el proyecto si no se hizo.
