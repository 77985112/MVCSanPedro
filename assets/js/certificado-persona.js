(function () {
    'use strict';

    const FORMULARIOS = ['formCertificadoBautizo', 'formCertificadoConfirmacion', 'formCertificadoMatrimonio'];

    // Avisos informativos (no bloquean el guardado) según el perfil de la persona.
    const REGLAS = {
        CiPersonaNovio: function (persona) {
            if (persona.Sexo === 'Mujer') return 'La persona es mujer.';
            if (persona.Estado_per === 'Casado' || persona.Estado_per === 'Casada') return 'La persona ya está casada.';
            if (!(persona.Sexo === 'Varon' && persona.Estado_per === 'Soltero')) return 'Verifique o registre a la persona.';
            return '';
        },
        CiPersonaNovia: function (persona) {
            if (persona.Sexo === 'Varon') return 'La persona es varón.';
            if (persona.Estado_per === 'Casada') return 'La persona ya está casada.';
            if (!(persona.Sexo === 'Mujer' && persona.Estado_per === 'Soltera')) return 'Verifique o registre a la persona.';
            return '';
        }
    };

    let origenModal = null;

    // Deja el campo de CI vacío al abrir la página (evita que el navegador
    // reponga un CI escrito antes), salvo cuando el servidor repite los datos
    // de un envío con errores (data-rellenar="1").
    function limpiarCampo(input) {
        if (document.activeElement === input) return;
        input.value = '';
        input.setCustomValidity('');
        const contenedor = input.parentElement;
        const nombre = contenedor ? contenedor.querySelector('[data-nombre-persona]') : null;
        if (nombre) nombre.textContent = '';
        input.dispatchEvent(new Event('input', { bubbles: true }));
    }

    function limpiarFormulario(form) {
        if (form.dataset.rellenar === '1') return;
        form.querySelectorAll('input[data-persona]').forEach(limpiarCampo);
    }

    function pintarNombre(nombre, texto, advertencia) {
        nombre.textContent = texto;
        if (!advertencia) return;
        nombre.appendChild(document.createTextNode(' · '));
        const aviso = document.createElement('span');
        aviso.className = 'text-warning';
        aviso.textContent = advertencia;
        nombre.appendChild(aviso);
    }

    function prepararFormulario(form) {
        const esBautizo = form.id === 'formCertificadoBautizo';
        const esConfirmacion = form.id === 'formCertificadoConfirmacion';
        const nacimiento = (esBautizo || esConfirmacion) ? form.elements.FechaNac : null;
        const civil = ['OficialiaCivil', 'LibroCivil', 'PartidaCivil', 'FolioCivil'];
        const camposPersona = esConfirmacion
            ? ['FechaNac', 'CiPapa', 'CiMama', 'ParroquiaBau', 'FechaBau', 'LibroBau', 'PaginaBau', 'PartidaBau']
            : ['FechaNac', 'LugarNac', 'FechaRegistroCivil'].concat(civil);
        const inputCelebrante = esBautizo ? form.elements.CiPersonaCelebranteB
            : (esConfirmacion ? form.elements.CiPersonaCelebranteC : null);
        const avisoBautizo = esConfirmacion ? form.querySelector('#confBautizoAviso') : null;
        const avisoNacimiento = esConfirmacion ? form.querySelector('#confNacimientoAviso') : null;
        const ayudaNacimiento = avisoNacimiento ? avisoNacimiento.textContent : '';
        let personaActual = inputCelebrante ? inputCelebrante.value.trim() || null : null;

        function fechas() {
            if (!nacimiento || !form.elements.FechaReal) return;
            form.elements.FechaReal.min = nacimiento.value;
            if (form.elements.FechaEmision) form.elements.FechaEmision.min = form.elements.FechaReal.value;
            if (esConfirmacion) {
                const bautizo = form.elements.FechaBau;
                bautizo.min = nacimiento.value;
                bautizo.max = form.elements.FechaReal.value;
                form.elements.FechaReal.min = [nacimiento.value, bautizo.value].sort().pop();
                const edad = form.querySelector('#confEdad');
                const fechaNac = nacimiento.value;
                const fechaReal = form.elements.FechaReal.value;
                edad.value = '';
                if (fechaNac && fechaReal && fechaReal >= fechaNac) {
                    let anios = Number(fechaReal.slice(0, 4)) - Number(fechaNac.slice(0, 4));
                    if (fechaReal.slice(5) < fechaNac.slice(5)) anios--;
                    edad.value = anios + (anios === 1 ? ' año' : ' años');
                }
            }
        }
        if (esBautizo || esConfirmacion) {
            [nacimiento, form.elements.FechaReal, esConfirmacion ? form.elements.FechaBau : null]
                .filter(Boolean).forEach(function (campo) {
                    campo.addEventListener('input', fechas);
                    campo.addEventListener('change', fechas);
                });
        }

        form.querySelectorAll('input[data-persona]').forEach(function (input) {
            const contenedor = input.parentElement;
            const nombre = contenedor ? contenedor.querySelector('[data-nombre-persona]') : null;
            const enlace = contenedor ? contenedor.querySelector('[data-registrar-persona]') : null;
            if (!nombre) return;
            let temporizador;
            let solicitud;
            let version = 0;

            function buscar(completar) {
                clearTimeout(temporizador);
                if (solicitud) solicitud.abort();
                const actual = ++version;
                const ci = input.value.trim();
                input.setCustomValidity('');
                nombre.textContent = '';
                if (esConfirmacion && input === inputCelebrante) {
                    nacimiento.value = '';
                    if (avisoNacimiento) avisoNacimiento.textContent = ayudaNacimiento;
                    fechas();
                }
                if (!ci) return;
                input.setCustomValidity('Espere a que termine la búsqueda de la persona.');
                nombre.textContent = 'Buscando persona…';
                solicitud = new AbortController();
                const url = new URL('VistaSolicitante.php', window.location.href);
                url.searchParams.set('ciPersona', ci);
                if (esConfirmacion && input === inputCelebrante) {
                    url.searchParams.set('datosConfirmacion', '1');
                }

                fetch(url, { signal: solicitud.signal })
                    .then(function (respuesta) {
                        if (!respuesta.ok) throw new Error('No se pudo consultar la persona.');
                        return respuesta.json();
                    })
                    .then(function (persona) {
                        if (actual !== version || input.value.trim() !== ci) return;
                        if (persona.error || !persona.CiPersona) {
                            nombre.textContent = 'Persona no encontrada. Use «registrar nuevo».';
                            input.setCustomValidity('Registre a esta persona antes de guardar el certificado.');
                            return;
                        }
                        const advertencia = REGLAS[input.name] ? REGLAS[input.name](persona) : '';
                        const nombreCompleto = [persona.Nombre, persona.ApPaterno, persona.ApMaterno].filter(Boolean).join(' ');
                        const seguimiento = persona.ProcesosPastorales
                            ? persona.ProcesosPastorales.estadoActual : 'Registrado';
                        pintarNombre(nombre, nombreCompleto + ' · ' + seguimiento, advertencia);
                        input.value = persona.CiPersona;
                        input.setCustomValidity('');
                        if (esConfirmacion && input === inputCelebrante) {
                            const cambioPersona = personaActual !== persona.CiPersona;
                            personaActual = persona.CiPersona;
                            nacimiento.value = persona.FechaNac || '';
                            if (!nacimiento.value) {
                                const mensaje = 'El confirmante no tiene una fecha de nacimiento válida guardada. Complete ese dato en Persona.';
                                input.setCustomValidity(mensaje);
                                if (avisoNacimiento) avisoNacimiento.textContent = mensaje;
                            }
                            const bautizo = persona.BautizoConfirmante;
                            if (completar && cambioPersona && bautizo) {
                                ['ParroquiaBau', 'FechaBau', 'LibroBau', 'PaginaBau', 'PartidaBau', 'CiPapa', 'CiMama']
                                    .forEach(function (campo) {
                                        form.elements[campo].value = bautizo[campo] || '';
                                        if (campo === 'CiPapa' || campo === 'CiMama') {
                                            form.elements[campo].dispatchEvent(new Event('input', { bubbles: true }));
                                        }
                                    });
                            }
                            if (avisoBautizo) {
                                avisoBautizo.textContent = bautizo
                                    ? 'Bautismo encontrado en San Pedro de Sacaba. Revise los datos antes de guardar.'
                                    : 'No se encontró una partida de bautismo en San Pedro de Sacaba para este CI. Complete los datos de su certificado de bautismo.';
                            }
                            fechas();
                        }
                        if (esBautizo && input.name === 'CiPersonaCelebranteB') {
                            personaActual = persona.CiPersona;
                            if (completar || !nacimiento.value) nacimiento.value = persona.FechaNac || '';
                            const datosCivil = /^Ofic\.(.*?)-Lib\.(.*?)-Part\.(.*?)-Fol\.(.*)$/.exec(persona.id_certificado_nacimiento || '');
                            if (datosCivil) {
                                civil.forEach(function (campo, indice) {
                                    if (form.elements[campo] && (completar || !form.elements[campo].value)) {
                                        form.elements[campo].value = datosCivil[indice + 1];
                                    }
                                });
                            }
                            fechas();
                        }
                    })
                    .catch(function (error) {
                        if (error.name === 'AbortError' || actual !== version) return;
                        nombre.textContent = 'No se pudo consultar. Revise su conexión e intente nuevamente.';
                        input.setCustomValidity('No se pudo verificar la persona. Vuelva a escribir su identificación.');
                    });
            }

            input.addEventListener('input', function () {
                clearTimeout(temporizador);
                if (solicitud) solicitud.abort();
                version++;
                input.setCustomValidity(input.value.trim() ? 'Espere a que termine la búsqueda de la persona.' : '');
                nombre.textContent = '';
                if ((esBautizo && input === inputCelebrante && personaActual && input.value.trim() !== personaActual)
                    || (esConfirmacion && input === inputCelebrante && input.value.trim() !== personaActual)) {
                    camposPersona.forEach(function (campo) {
                        if (!form.elements[campo]) return;
                        form.elements[campo].value = '';
                        if (esConfirmacion && (campo === 'CiPapa' || campo === 'CiMama')) {
                            form.elements[campo].dispatchEvent(new Event('input', { bubbles: true }));
                        }
                    });
                    if (avisoBautizo) avisoBautizo.textContent = 'Ingrese el CI para buscar su partida de bautismo en San Pedro de Sacaba.';
                    if (avisoNacimiento) avisoNacimiento.textContent = ayudaNacimiento;
                    personaActual = null;
                    fechas();
                }
                temporizador = setTimeout(function () { buscar(true); }, 300);
            });
            input.addEventListener('change', function () { buscar(true); });
            if (enlace) {
                enlace.addEventListener('click', function (evento) {
                    evento.preventDefault();
                    const modal = document.getElementById(input.dataset.modalPersona);
                    if (!modal) return;
                    origenModal = input;
                    const ciModal = modal.querySelector('[name="CiPersona"]');
                    if (ciModal) ciModal.value = input.value.trim();
                    bootstrap.Modal.getOrCreateInstance(modal).show();
                });
            }
            input.addEventListener('persona-creada', function () { buscar(true); });
            if (input.value.trim()) buscar(false);
        });

        limpiarFormulario(form);
        fechas();
    }

    function iniciar() {
        const formularios = FORMULARIOS
            .map(function (id) { return document.getElementById(id); })
            .filter(Boolean);
        if (!formularios.length) return;

        formularios.forEach(prepararFormulario);

        document.addEventListener('persona-certificado-registrada', function (evento) {
            if (!origenModal || origenModal.dataset.modalPersona !== evento.detail.modalId || !evento.detail.ciPersona) return;
            origenModal.value = evento.detail.ciPersona;
            origenModal.dispatchEvent(new Event('input', { bubbles: true }));
            origenModal.dispatchEvent(new Event('persona-creada'));
            origenModal = null;
        });
        document.querySelectorAll('#MostrarModalBautizo, #MostrarModalDemas, #MostrarModalConfirmante').forEach(function (modal) {
            modal.addEventListener('hidden.bs.modal', function () { origenModal = null; });
        });

        // Si el navegador repone valores (autocompletado o al volver atrás),
        // se vuelven a limpiar; si hay un foco activo se respeta lo que se está escribiendo.
        const reponer = function () {
            formularios.forEach(limpiarFormulario);
        };
        window.addEventListener('load', reponer);
        window.addEventListener('pageshow', reponer);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciar);
    } else {
        iniciar();
    }
}());
