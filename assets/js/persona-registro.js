(function () {
  'use strict';

  var catalogoPromise = fetch('../assets/data/ubicaciones_bolivia.json', { cache: 'force-cache' })
    .then(function (respuesta) { return respuesta.ok ? respuesta.json() : []; })
    .catch(function () { return []; });

  function opciones(select, valores) {
    select.innerHTML = '<option value="">Seleccionar...</option>';
    valores.forEach(function (valor) {
      var option = document.createElement('option');
      option.value = valor;
      option.textContent = valor;
      select.appendChild(option);
    });
  }

  function unicos(valores) {
    return Array.from(new Set(valores.filter(Boolean))).sort(function (a, b) {
      return a.localeCompare(b, 'es');
    });
  }

  function iniciar(form) {
    var tipo = form.querySelector('[data-tipo-documento]');
    var numero = form.querySelector('[data-numero-documento]');
    var marca = form.querySelector('[data-documento-obligatorio]');

    function actualizarDocumento() {
      var sinDocumento = tipo.value === 'Sin documento';
      numero.required = !sinDocumento;
      numero.disabled = sinDocumento;
      if (sinDocumento) numero.value = '';
      marca.classList.toggle('d-none', sinDocumento);
      numero.placeholder = sinDocumento ? 'Se generará un identificador interno' : 'Número de documento';
    }
    tipo.addEventListener('change', actualizarDocumento);
    actualizarDocumento();

    var hoy = new Date().toISOString().slice(0, 10);
    form.querySelectorAll('input[type=date]').forEach(function (campo) { campo.max = hoy; });

    function actualizarAlerta() {
      var solicitado = form.querySelector('[data-sacramento-solicita]');
      var matrimonio = form.querySelector('input[name="Antecedentes[Matrimonio][Realizado]"][value="Si"]');
      var alerta = form.querySelector('[data-alerta-matrimonio]');
      if (alerta) alerta.classList.toggle('d-none', !(solicitado && solicitado.value.indexOf('Matrimonio') !== -1 && matrimonio && matrimonio.checked));
    }

    form.querySelectorAll('[data-antecedente-card]').forEach(function (card) {
      var detalle = card.querySelector('[data-antecedente-detalle]');
      var chevron = card.querySelector('[data-chevron]');
      var selectorPais = card.querySelector('[data-pais-selector]');
      var paisValor = card.querySelector('[data-pais-valor]');
      var paisManual = card.querySelector('[data-pais-manual]');
      var ubicacionBolivia = card.querySelector('[data-ubicacion-bolivia]');
      var departamento = card.querySelector('[name$="[Departamento]"]');
      var provincia = card.querySelector('[data-provincia]');
      var municipio = card.querySelector('[data-municipio]');
      var provinciaCheck = card.querySelector('[data-provincia-manual-check]');
      var municipioCheck = card.querySelector('[data-municipio-manual-check]');
      var provinciaManual = card.querySelector('[data-provincia-manual]');
      var municipioManual = card.querySelector('[data-municipio-manual]');
      var catalogo = [];

      function manual(check, select, campo) {
        var activo = check.checked;
        select.disabled = activo;
        campo.disabled = !activo;
        campo.classList.toggle('d-none', !activo);
        if (!activo) campo.value = '';
      }

      function cargarProvincias() {
        var filas = catalogo.filter(function (fila) { return fila.departamento === departamento.value; });
        opciones(provincia, unicos(filas.map(function (fila) { return fila.provincia; })));
        opciones(municipio, []);
      }

      function cargarMunicipios() {
        var filas = catalogo.filter(function (fila) {
          return fila.departamento === departamento.value && fila.provincia === provincia.value;
        });
        opciones(municipio, unicos(filas.map(function (fila) { return fila.municipio; })));
      }

      function actualizarPais() {
        var otro = selectorPais.value === 'Otro';
        paisManual.classList.toggle('d-none', !otro);
        paisManual.required = otro;
        ubicacionBolivia.classList.toggle('d-none', otro);
        paisValor.value = otro ? paisManual.value : 'Bolivia';
        if (otro) {
          provinciaCheck.checked = true;
          municipioCheck.checked = true;
          manual(provinciaCheck, provincia, provinciaManual);
          manual(municipioCheck, municipio, municipioManual);
        }
      }

      function alternarDetalle() {
        var realizado = card.querySelector('input[value=Si]').checked;
        detalle.classList.toggle('d-none', !realizado);
        chevron.className = 'bi ' + (realizado ? 'bi-chevron-up' : 'bi-chevron-down');
        detalle.querySelectorAll('[data-campo-detalle]').forEach(function (campo) { campo.disabled = !realizado; });
        actualizarAlerta();
      }

      card.querySelectorAll('input[type=radio]').forEach(function (radio) { radio.addEventListener('change', alternarDetalle); });
      selectorPais.addEventListener('change', actualizarPais);
      paisManual.addEventListener('input', function () { paisValor.value = paisManual.value; });
      departamento.addEventListener('change', cargarProvincias);
      provincia.addEventListener('change', cargarMunicipios);
      provinciaCheck.addEventListener('change', function () { manual(provinciaCheck, provincia, provinciaManual); });
      municipioCheck.addEventListener('change', function () { manual(municipioCheck, municipio, municipioManual); });

      catalogoPromise.then(function (datos) { catalogo = datos; cargarProvincias(); });
      manual(provinciaCheck, provincia, provinciaManual);
      manual(municipioCheck, municipio, municipioManual);
      actualizarPais();
      alternarDetalle();
    });

    form.querySelector('[data-sacramento-solicita]').addEventListener('change', actualizarAlerta);
    form.addEventListener('submit', function (evento) {
      var paterno = form.elements.ApPaterno.value.trim();
      var materno = form.elements.ApMaterno.value.trim();
      if (!paterno && !materno) {
        evento.preventDefault();
        form.elements.ApPaterno.setCustomValidity('Ingrese al menos un apellido.');
        form.elements.ApPaterno.reportValidity();
        return;
      }
      form.elements.ApPaterno.setCustomValidity('');
      var nacimiento = form.querySelector('[data-fecha-nacimiento]').value;
      var invalida = false;
      form.querySelectorAll('[data-fecha-sacramento]').forEach(function (fecha) {
        if (fecha.value && nacimiento && fecha.value < nacimiento) {
          fecha.setCustomValidity('La fecha no puede ser anterior al nacimiento.');
          invalida = true;
        } else fecha.setCustomValidity('');
      });
      if (invalida) {
        evento.preventDefault();
        form.querySelector('[data-fecha-sacramento]:invalid').reportValidity();
        return;
      }
      if (!form.checkValidity()) {
        evento.preventDefault();
        form.classList.add('was-validated');
      }
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-persona-form]').forEach(iniciar);
  });
})();
