(function () {
  'use strict';

  function limpiar(contenedor) {
    while (contenedor.firstChild) contenedor.removeChild(contenedor.firstChild);
  }

  function linea(contenedor, principal, secundario) {
    var elemento = document.createElement('div');
    elemento.className = 'seguimiento-linea';
    elemento.textContent = principal;
    if (secundario) {
      var detalle = document.createElement('span');
      detalle.className = 'text-muted d-block';
      detalle.textContent = secundario;
      elemento.appendChild(detalle);
    }
    contenedor.appendChild(elemento);
  }

  function vacio(contenedor, mensaje) {
    var elemento = document.createElement('div');
    elemento.className = 'text-muted';
    elemento.textContent = mensaje;
    contenedor.appendChild(elemento);
  }

  function iniciar() {
    var modalElemento = document.getElementById('ModalSeguimientoPastoral');
    if (!modalElemento || typeof bootstrap === 'undefined') return;
    var modal = bootstrap.Modal.getOrCreateInstance(modalElemento);
    var cargando = modalElemento.querySelector('[data-seguimiento-cargando]');
    var contenido = modalElemento.querySelector('[data-seguimiento-contenido]');
    var error = modalElemento.querySelector('[data-seguimiento-error]');
    var persona = modalElemento.querySelector('[data-seguimiento-persona]');
    var estado = modalElemento.querySelector('[data-seguimiento-estado]');
    var solicitud = modalElemento.querySelector('[data-seguimiento-solicitud]');
    var reservas = modalElemento.querySelector('[data-seguimiento-reservas]');
    var catequesis = modalElemento.querySelector('[data-seguimiento-catequesis]');
    var sacramentos = modalElemento.querySelector('[data-seguimiento-sacramentos]');

    document.querySelectorAll('[data-seguimiento-ci]').forEach(function (boton) {
      boton.addEventListener('click', function () {
        var ci = boton.getAttribute('data-seguimiento-ci') || '';
        cargando.classList.remove('d-none'); contenido.classList.add('d-none'); error.classList.add('d-none'); persona.textContent = '';
        modal.show();
        fetch('VistaPersona.php?seguimientoPersona=' + encodeURIComponent(ci), { headers: { 'Accept': 'application/json' }, cache: 'no-store' })
          .then(function (respuesta) {
            return respuesta.json().then(function (datos) {
              if (!respuesta.ok) throw new Error(datos.error || 'No se pudo consultar el seguimiento.');
              return datos;
            });
          }).then(function (datos) {
            var procesos = datos.ProcesosPastorales || {};
            persona.textContent = (datos.NombreCompleto || '') + ' · ' + (datos.CiPersona || '');
            estado.textContent = procesos.estadoActual || 'Registrado';
            solicitud.textContent = datos.SacramentoSolicita ? 'Solicita: ' + datos.SacramentoSolicita : '';
            solicitud.classList.toggle('d-none', !datos.SacramentoSolicita);
            limpiar(reservas); limpiar(catequesis); limpiar(sacramentos);
            if ((procesos.reservas || []).length) procesos.reservas.forEach(function (fila) { linea(reservas, (fila.TipoCel || '') + ': ' + (fila.EstadoRes || 'Registrado'), fila.FechaReal || ''); });
            else vacio(reservas, 'Sin reservas');
            if ((procesos.catequesis || []).length) procesos.catequesis.forEach(function (fila) { linea(catequesis, (fila.Sacramento || '') + ': ' + (fila.IdInscripcion ? 'Inscrito' : (fila.EstadoCel || 'Registrado'))); });
            else vacio(catequesis, 'Sin inscripción');
            if ((procesos.sacramentos || []).length) procesos.sacramentos.forEach(function (fila) { linea(sacramentos, (fila.DescripSac || '') + ': ' + (fila.EstadoProceso || 'Registrado')); });
            else vacio(sacramentos, 'Sin sacramentos registrados');
            cargando.classList.add('d-none'); contenido.classList.remove('d-none');
          }).catch(function (excepcion) {
            cargando.classList.add('d-none'); error.textContent = excepcion.message; error.classList.remove('d-none');
          });
      });
    });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', iniciar); else iniciar();
}());
