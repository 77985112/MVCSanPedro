(function () {
    'use strict';
    if (!window.bootstrap || !bootstrap.Carousel) return;

    const DURACION = 8000;
    const movimientoReducido = window.matchMedia('(prefers-reduced-motion: reduce)');
    const controles = [];
    let automatico = !movimientoReducido.matches;

    function cambiarAutomatico(valor) {
        automatico = valor;
        controles.forEach(function (control) { control.actualizar(); });
    }

    document.querySelectorAll('.actividad-carrusel').forEach(function (elemento) {
        const diapositivas = Array.from(elemento.querySelectorAll('.carousel-item'));
        const numeros = Array.from(elemento.querySelectorAll('.actividad-numero'));
        const botonAutomatico = elemento.querySelector('.actividad-automatico');
        const estado = elemento.querySelector('[data-actividad-estado]');
        const progreso = elemento.querySelector('.actividad-progreso');
        const panel = elemento.closest('.tab-pane');
        const carrusel = bootstrap.Carousel.getOrCreateInstance(elemento, {
            interval: false, ride: false, pause: false, touch: true, keyboard: false, wrap: true
        });
        elemento.style.setProperty('--actividad-intervalo', DURACION + 'ms');
        let temporizador;
        let transicion = false;
        let pendiente = null;

        function detener() {
            window.clearTimeout(temporizador);
            temporizador = null;
            if (progreso) progreso.classList.add('pausado');
        }
        function visible() {
            return !panel || panel.classList.contains('active');
        }
        function reiniciarBarra() {
            if (!progreso) return;
            progreso.classList.remove('corriendo');
            progreso.classList.add('pausado');
        }
        function programar() {
            detener();
            if (!automatico || diapositivas.length < 2 || transicion || !visible() || document.hidden
                || document.querySelector('.modal.show')) return;
            if (Array.from(elemento.querySelectorAll('video')).some(function (video) { return !video.paused && !video.ended; })) return;
            if (progreso) {
                progreso.classList.remove('corriendo', 'pausado');
                void progreso.offsetWidth;
                progreso.classList.add('corriendo');
            }
            // Bootstrap mantiene el ciclo: tras la última diapositiva pasa a la primera.
            temporizador = window.setTimeout(function () { carrusel.next(); }, DURACION);
        }
        function mostrarMiniatura(indice) {
            if (!visible() || !numeros[indice]) return;
            const lista = numeros[indice].parentElement;
            const tarjeta = numeros[indice].getBoundingClientRect();
            const zona = lista.getBoundingClientRect();
            if (tarjeta.left < zona.left) lista.scrollLeft += tarjeta.left - zona.left - 2;
            else if (tarjeta.right > zona.right) lista.scrollLeft += tarjeta.right - zona.right + 2;
        }
        function ajustarDescripcion() {
            const activa = elemento.querySelector('.carousel-item.active');
            if (!visible() || !activa) return;
            const descripcion = activa.querySelector('.actividad-descripcion');
            const boton = activa.querySelector('.actividad-leer');
            if (descripcion && boton) {
                boton.hidden = !descripcion.classList.contains('completa')
                    && descripcion.scrollHeight <= descripcion.clientHeight + 1;
            }
        }
        function ajustarAltura() {
            const interior = elemento.querySelector('.carousel-inner');
            if (!interior || !visible()) return;
            // La altura depende de la pantalla y los controles, nunca del texto de una actividad.
            const zona = interior.getBoundingClientRect();
            const navegacion = elemento.getBoundingClientRect().height - zona.height;
            const disponible = window.innerHeight - (zona.top + window.scrollY) - navegacion - 16;
            const movil = window.matchMedia('(max-width: 767.98px)').matches;
            const altura = Math.floor(Math.max(movil ? 440 : 300, Math.min(movil ? 600 : 640, disponible)));
            elemento.style.setProperty('--actividad-alto', altura + 'px');
            ajustarDescripcion();
        }
        function actualizar() {
            const indice = diapositivas.findIndex(function (diapositiva) { return diapositiva.classList.contains('active'); });
            diapositivas.forEach(function (diapositiva, i) {
                diapositiva.inert = i !== indice;
                diapositiva.setAttribute('aria-hidden', String(i !== indice));
            });
            numeros.forEach(function (boton, i) {
                boton.classList.toggle('active', i === indice);
                if (i === indice) boton.setAttribute('aria-current', 'true');
                else boton.removeAttribute('aria-current');
            });
            if (estado && indice >= 0) {
                estado.setAttribute('aria-live', automatico ? 'off' : 'polite');
                estado.textContent = 'Mostrando actividad ' + diapositivas[indice].dataset.actividadNumero;
            }
            if (botonAutomatico) {
                botonAutomatico.setAttribute('aria-pressed', String(automatico));
                botonAutomatico.setAttribute('aria-label', automatico ? 'Pausar cambio automático' : 'Reanudar cambio automático');
                botonAutomatico.querySelector('i').className = automatico ? 'bi bi-pause-fill' : 'bi bi-play-fill';
                botonAutomatico.querySelector('span').textContent = automatico ? 'Pausar' : 'Reanudar';
            }
            mostrarMiniatura(indice);
            ajustarDescripcion();
            programar();
        }
        controles.push({ actualizar: actualizar, detener: detener });

        function elegir(indice) {
            cambiarAutomatico(false);
            if (transicion) { pendiente = indice; return; }
            carrusel.to(indice);
        }
        elemento.addEventListener('slide.bs.carousel', function (event) {
            transicion = true;
            detener();
            reiniciarBarra();
            const saliente = elemento.querySelector('.carousel-item.active');
            if (saliente && saliente.contains(document.activeElement) && numeros[event.to]) {
                numeros[event.to].focus({ preventScroll: true });
            }
            elemento.querySelectorAll('video').forEach(function (video) { video.pause(); });
        });
        elemento.addEventListener('slid.bs.carousel', function () {
            transicion = false;
            if (pendiente !== null) {
                const destino = pendiente;
                pendiente = null;
                if (!diapositivas[destino].classList.contains('active')) {
                    carrusel.to(destino);
                    return;
                }
            }
            actualizar();
        });

        elemento.addEventListener('click', function (event) {
            const boton = event.target.closest('.actividad-numero, .actividad-flecha');
            if (boton && elemento.contains(boton)) {
                // Gestiona la selección antes de que la API de datos de Bootstrap la repita.
                event.preventDefault();
                event.stopPropagation();
                if (boton.disabled) return;
                let indice = numeros.indexOf(boton);
                if (indice < 0) {
                    const actual = diapositivas.findIndex(function (item) { return item.classList.contains('active'); });
                    indice = (actual + (boton.dataset.bsSlide === 'prev' ? -1 : 1) + diapositivas.length) % diapositivas.length;
                }
                elegir(indice);
            } else if (event.target.closest('.actividad-destacada')) {
                cambiarAutomatico(false);
            }
        });
        // Un gesto sobre la actividad también deja la selección en pausa.
        elemento.querySelectorAll('.actividad-destacada').forEach(function (articulo) {
            articulo.addEventListener('pointerdown', function () { cambiarAutomatico(false); });
        });
        elemento.addEventListener('keydown', function (event) {
            if (event.target.closest('video, input, textarea, select') || event.altKey || event.ctrlKey || event.metaKey) return;
            if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
                event.preventDefault();
                const actual = diapositivas.findIndex(function (item) { return item.classList.contains('active'); });
                elegir((actual + (event.key === 'ArrowLeft' ? -1 : 1) + diapositivas.length) % diapositivas.length);
            }
        });
        elemento.addEventListener('focusin', function (event) {
            if (event.target !== botonAutomatico && event.target.matches(':focus-visible')) cambiarAutomatico(false);
        });
        if (botonAutomatico) botonAutomatico.addEventListener('click', function () {
            cambiarAutomatico(!automatico);
        });
        elemento.querySelectorAll('video').forEach(function (video) {
            video.addEventListener('play', function () { cambiarAutomatico(false); });
            // Los gestos del reproductor no deben cambiar de actividad.
            video.addEventListener('pointerdown', function (event) { event.stopPropagation(); });
            video.addEventListener('touchstart', function (event) { event.stopPropagation(); }, { passive: true });
        });
        elemento.querySelectorAll('[data-actividad-fotos]').forEach(function (galeria) {
            let fotos;
            try { fotos = JSON.parse(galeria.dataset.actividadFotos || '[]'); } catch (error) { fotos = []; }
            if (!Array.isArray(fotos) || fotos.length < 2) return;
            const imagen = galeria.querySelector('img');
            const contador = galeria.querySelector('[data-actividad-foto-contador]');
            const escena = galeria.closest('.actividad-escena');
            const textoImagen = imagen.alt;
            let indice = 0;
            let inicioGesto = null;

            function mostrarFoto(destino) {
                indice = (destino + fotos.length) % fotos.length;
                imagen.src = fotos[indice];
                imagen.alt = textoImagen + ' (foto ' + (indice + 1) + ' de ' + fotos.length + ')';
                galeria.dataset.actividadFotoIndice = String(indice);
                contador.textContent = 'Foto ' + (indice + 1) + ' / ' + fotos.length;
                escena.querySelectorAll('[data-actividad-galeria]').forEach(function (boton) {
                    boton.dataset.actividadFotoIndice = String(indice);
                });
            }
            function moverFoto(paso) {
                cambiarAutomatico(false);
                mostrarFoto(indice + paso);
            }
            galeria.querySelectorAll('[data-actividad-foto-paso]').forEach(function (boton) {
                boton.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    moverFoto(Number(boton.dataset.actividadFotoPaso));
                });
            });
            galeria.addEventListener('keydown', function (event) {
                if (event.altKey || event.ctrlKey || event.metaKey) return;
                if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
                    event.preventDefault();
                    event.stopPropagation();
                    moverFoto(event.key === 'ArrowLeft' ? -1 : 1);
                }
            });
            // Un gesto sobre las fotos no debe cambiar la actividad que se está leyendo.
            galeria.addEventListener('pointerdown', function (event) {
                event.stopPropagation();
                cambiarAutomatico(false);
                inicioGesto = event.pointerType !== 'mouse' && !event.target.closest('button')
                    ? { x: event.clientX, y: event.clientY, id: event.pointerId } : null;
            });
            galeria.addEventListener('pointerup', function (event) {
                event.stopPropagation();
                if (inicioGesto && inicioGesto.id === event.pointerId) {
                    const dx = event.clientX - inicioGesto.x;
                    const dy = event.clientY - inicioGesto.y;
                    if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy) * 1.25) moverFoto(dx < 0 ? 1 : -1);
                }
                inicioGesto = null;
            });
            galeria.addEventListener('pointercancel', function () { inicioGesto = null; });
            ['touchstart', 'touchmove', 'touchend'].forEach(function (tipo) {
                galeria.addEventListener(tipo, function (event) { event.stopPropagation(); }, { passive: true });
            });
            galeria.addEventListener('actividad:mostrar-foto', function (event) {
                if (Number.isInteger(event.detail) && event.detail >= 0 && event.detail < fotos.length) mostrarFoto(event.detail);
            });
            mostrarFoto(0);
        });
        elemento.querySelectorAll('[data-actividad-medio]').forEach(function (boton) {
            boton.addEventListener('click', function () {
                cambiarAutomatico(false);
                const diapositiva = boton.closest('.carousel-item');
                const destino = boton.getAttribute('aria-controls');
                diapositiva.querySelectorAll('[data-actividad-medio]').forEach(function (otro) {
                    otro.setAttribute('aria-pressed', String(otro.getAttribute('aria-controls') === destino));
                });
                diapositiva.querySelectorAll('.actividad-archivo').forEach(function (archivo) {
                    archivo.hidden = archivo.id !== destino;
                    archivo.querySelectorAll('video').forEach(function (video) {
                        if (archivo.hidden) video.pause();
                        else if (video.preload === 'none') {
                            video.preload = 'metadata';
                            video.load();
                        }
                    });
                });
                diapositiva.querySelector('.actividad-escena').classList.toggle('mostrando-video', destino.endsWith('-video'));
            });
        });
        elemento.querySelectorAll('.actividad-leer').forEach(function (boton) {
            boton.addEventListener('click', function () {
                cambiarAutomatico(false);
                const descripcion = document.getElementById(boton.getAttribute('aria-controls'));
                const completa = descripcion.classList.toggle('completa');
                boton.setAttribute('aria-expanded', String(completa));
                boton.textContent = completa ? 'Leer menos' : 'Leer más';
            });
        });
        window.addEventListener('resize', ajustarAltura);
        window.addEventListener('load', ajustarAltura);
        if (document.fonts) document.fonts.ready.then(ajustarAltura);
        actualizar();
        window.requestAnimationFrame(ajustarAltura);
    });

    movimientoReducido.addEventListener('change', function () {
        if (movimientoReducido.matches) cambiarAutomatico(false);
    });
    document.addEventListener('visibilitychange', function () {
        controles.forEach(function (control) { control.actualizar(); });
    });
    document.addEventListener('show.bs.modal', function () {
        controles.forEach(function (control) { control.detener(); });
    });
    document.addEventListener('hidden.bs.modal', function () {
        controles.forEach(function (control) { control.actualizar(); });
    });
    document.addEventListener('hidden.bs.tab', function (event) {
        const destino = event.target.getAttribute('data-bs-target');
        const panel = destino && document.getElementById(destino.substring(1));
        if (panel) panel.querySelectorAll('video').forEach(function (video) { video.pause(); });
        controles.forEach(function (control) { control.detener(); });
    });
    document.addEventListener('shown.bs.tab', function () {
        controles.forEach(function (control) { control.actualizar(); });
    });

    const modal = document.getElementById('modalImagen');
    if (modal) {
        const imagen = modal.querySelector('#imagenModal');
        const botonAnterior = modal.querySelector('[data-galeria-anterior]');
        const botonSiguiente = modal.querySelector('[data-galeria-siguiente]');
        const contador = modal.querySelector('[data-galeria-contador]');
        let galeriaActual = [];
        let indiceActual = 0;
        let etiquetaBase = '';
        let galeriaOrigen = null;

        function pintarGaleria() {
            const hayGaleria = galeriaActual.length > 1;
            imagen.src = galeriaActual[indiceActual] || '';
            if (galeriaOrigen) {
                galeriaOrigen.dispatchEvent(new CustomEvent('actividad:mostrar-foto', { detail: indiceActual }));
            }
            imagen.alt = etiquetaBase + (hayGaleria
                ? ' (imagen ' + (indiceActual + 1) + ' de ' + galeriaActual.length + ')' : '');
            if (botonAnterior) botonAnterior.hidden = !hayGaleria;
            if (botonSiguiente) botonSiguiente.hidden = !hayGaleria;
            if (contador) {
                contador.hidden = !hayGaleria;
                contador.textContent = (indiceActual + 1) + ' / ' + galeriaActual.length;
            }
        }

        function moverGaleria(paso) {
            if (galeriaActual.length < 2) return;
            indiceActual = (indiceActual + paso + galeriaActual.length) % galeriaActual.length;
            pintarGaleria();
        }

        modal.addEventListener('show.bs.modal', function (event) {
            const boton = event.relatedTarget;
            if (!boton) return;
            try {
                galeriaActual = JSON.parse(boton.getAttribute('data-actividad-galeria') || '[]');
            } catch (error) {
                galeriaActual = [];
            }
            if (!Array.isArray(galeriaActual)) galeriaActual = [];
            if (!galeriaActual.length && boton.dataset.actividadImagenModal) {
                galeriaActual = [boton.dataset.actividadImagenModal];
            }
            const escena = boton.closest('.actividad-escena');
            galeriaOrigen = escena && escena.querySelector('[data-actividad-fotos]');
            const seleccionado = Number(boton.dataset.actividadFotoIndice || 0);
            indiceActual = Number.isInteger(seleccionado) && seleccionado >= 0 && seleccionado < galeriaActual.length
                ? seleccionado : 0;
            etiquetaBase = boton.getAttribute('aria-label') || '';
            pintarGaleria();
        });

        if (botonAnterior) botonAnterior.addEventListener('click', function () { moverGaleria(-1); });
        if (botonSiguiente) botonSiguiente.addEventListener('click', function () { moverGaleria(1); });
        document.addEventListener('keydown', function (evento) {
            if (!modal.classList.contains('show') || galeriaActual.length < 2) return;
            if (evento.key === 'ArrowLeft') { moverGaleria(-1); }
            if (evento.key === 'ArrowRight') { moverGaleria(1); }
        });
    }
})();
