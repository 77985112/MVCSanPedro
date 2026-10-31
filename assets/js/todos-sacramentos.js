(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var carrusel = document.querySelector('[data-carrusel-sacramentos]');
        if (!carrusel) return;

        var pista = carrusel.querySelector('.sacramentos-pista');
        var slides = Array.prototype.slice.call(carrusel.querySelectorAll('.sacramento-slide'));
        var anterior = carrusel.querySelector('[data-carrusel-anterior]');
        var siguiente = carrusel.querySelector('[data-carrusel-siguiente]');
        var puntos = Array.prototype.slice.call(carrusel.querySelectorAll('.sacramentos-punto'));
        var pestanas = Array.prototype.slice.call(carrusel.querySelectorAll('.sacramentos-pestana'));
        var indice = 0;
        var temporizador = null;
        var inicioTactil = 0;
        var reducirMovimiento = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (!slides.length) return;

        slides.forEach(function (slide) {
            var titulo = slide.querySelector('.card-title');
            if (!titulo || slide.querySelector('.sacramento-chip')) return;
            var chip = document.createElement('span');
            chip.className = 'sacramento-chip';
            chip.innerHTML = '<i class="bi ' + (slide.dataset.icono || 'bi-stars') + '" aria-hidden="true"></i> ' + (slide.dataset.categoria || 'Sacramento de la Iglesia');
            titulo.insertAdjacentElement('afterend', chip);
        });

        function normalizar(valor) {
            return (valor + slides.length) % slides.length;
        }

        function ajustarAltura() {
            var activo = slides[indice];
            if (!activo) return;
            window.requestAnimationFrame(function () {
                pista.style.height = activo.scrollHeight + 'px';
            });
        }

        function mostrar(nuevoIndice, enfocarPestana) {
            indice = normalizar(nuevoIndice);
            var indiceAnterior = normalizar(indice - 1);
            var indiceSiguiente = normalizar(indice + 1);

            slides.forEach(function (slide, i) {
                slide.classList.toggle('es-activo', i === indice);
                slide.classList.toggle('es-anterior', i === indiceAnterior);
                slide.classList.toggle('es-siguiente', i === indiceSiguiente);
                slide.setAttribute('aria-hidden', i === indice ? 'false' : 'true');
            });

            puntos.forEach(function (punto, i) {
                punto.classList.toggle('es-activo', i === indice);
                punto.setAttribute('aria-current', i === indice ? 'true' : 'false');
            });

            pestanas.forEach(function (pestana, i) {
                pestana.classList.toggle('es-activo', i === indice);
                pestana.setAttribute('aria-selected', i === indice ? 'true' : 'false');
                pestana.setAttribute('tabindex', i === indice ? '0' : '-1');
            });

            if (enfocarPestana && pestanas[indice]) {
                pestanas[indice].focus();
            }
            ajustarAltura();
        }

        function detener() {
            if (temporizador) window.clearInterval(temporizador);
            temporizador = null;
        }

        function iniciar() {
            detener();
            if (reducirMovimiento) return;
            temporizador = window.setInterval(function () {
                mostrar(indice + 1, false);
            }, 9000);
        }

        anterior.addEventListener('click', function () { mostrar(indice - 1, false); iniciar(); });
        siguiente.addEventListener('click', function () { mostrar(indice + 1, false); iniciar(); });

        puntos.forEach(function (punto, i) {
            punto.addEventListener('click', function () { mostrar(i, false); iniciar(); });
        });

        pestanas.forEach(function (pestana, i) {
            pestana.addEventListener('click', function () { mostrar(i, false); iniciar(); });
            pestana.addEventListener('keydown', function (evento) {
                if (evento.key === 'ArrowRight') { evento.preventDefault(); mostrar(indice + 1, true); iniciar(); }
                if (evento.key === 'ArrowLeft') { evento.preventDefault(); mostrar(indice - 1, true); iniciar(); }
            });
        });

        slides.forEach(function (slide, i) {
            slide.addEventListener('click', function () {
                if (slide.classList.contains('es-anterior') || slide.classList.contains('es-siguiente')) {
                    mostrar(i, false);
                    iniciar();
                }
            });
        });

        carrusel.addEventListener('mouseenter', detener);
        carrusel.addEventListener('mouseleave', iniciar);
        carrusel.addEventListener('focusin', detener);
        carrusel.addEventListener('focusout', iniciar);
        carrusel.addEventListener('touchstart', function (evento) { inicioTactil = evento.changedTouches[0].clientX; }, { passive: true });
        carrusel.addEventListener('touchend', function (evento) {
            var diferencia = evento.changedTouches[0].clientX - inicioTactil;
            if (Math.abs(diferencia) > 50) mostrar(indice + (diferencia < 0 ? 1 : -1), false);
            iniciar();
        }, { passive: true });

        window.addEventListener('resize', ajustarAltura);
        mostrar(0, false);
        iniciar();
    });
}());
