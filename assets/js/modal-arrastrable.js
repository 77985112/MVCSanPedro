// Arrastre opcional de ventanas Bootstrap desde su encabezado.
(function () {
    'use strict';

    function iniciar() {
        document.querySelectorAll('.modal[data-modal-arrastrable]').forEach(function (modal) {
            const dialogo = modal.querySelector('.modal-dialog');
            const encabezado = modal.querySelector('.modal-header');
            if (!dialogo || !encabezado) return;

            let arrastre = null;
            modal.style.scrollbarGutter = 'stable';
            encabezado.style.cursor = 'move';
            encabezado.style.userSelect = 'none';
            encabezado.style.webkitUserSelect = 'none';
            encabezado.style.touchAction = 'none';
            encabezado.title = 'Mantén pulsado el encabezado y arrastra con el mouse o el dedo';

            function terminarArrastre(evento) {
                if (!arrastre || (evento && evento.pointerId !== arrastre.id)) return;
                const id = arrastre.id;
                arrastre = null;
                if (encabezado.hasPointerCapture(id)) {
                    encabezado.releasePointerCapture(id);
                }
            }

            function restablecerPosicion() {
                terminarArrastre();
                dialogo.style.left = '';
                dialogo.style.top = '';
            }

            encabezado.addEventListener('pointerdown', function (evento) {
                if (!evento.isPrimary || evento.button !== 0 || arrastre) return;
                if (evento.target.closest('button, a, input, select, textarea, label')) return;

                const rectangulo = dialogo.getBoundingClientRect();
                arrastre = {
                    id: evento.pointerId,
                    x: evento.clientX,
                    y: evento.clientY,
                    izquierda: rectangulo.left,
                    arriba: rectangulo.top,
                    ancho: rectangulo.width,
                    altoEncabezado: encabezado.getBoundingClientRect().height,
                    desplazamientoX: parseFloat(dialogo.style.left) || 0,
                    desplazamientoY: parseFloat(dialogo.style.top) || 0
                };
                encabezado.setPointerCapture(evento.pointerId);
                evento.preventDefault();
            });

            encabezado.addEventListener('pointermove', function (evento) {
                if (!arrastre || evento.pointerId !== arrastre.id) return;

                const margen = 8;
                // Permitir apartar el formulario, conservando una zona de agarre visible.
                const agarreVisible = Math.min(200, arrastre.ancho, modal.clientWidth - margen * 2);
                const minX = margen - arrastre.ancho + agarreVisible;
                const maxX = Math.max(minX, modal.clientWidth - agarreVisible - margen);
                const maxY = Math.max(margen, modal.clientHeight - arrastre.altoEncabezado - margen);
                const izquierda = Math.min(maxX, Math.max(minX,
                    arrastre.izquierda + evento.clientX - arrastre.x));
                const arriba = Math.min(maxY, Math.max(margen,
                    arrastre.arriba + evento.clientY - arrastre.y));

                dialogo.style.left = (arrastre.desplazamientoX + izquierda - arrastre.izquierda) + 'px';
                dialogo.style.top = (arrastre.desplazamientoY + arriba - arrastre.arriba) + 'px';
            });

            encabezado.addEventListener('pointerup', terminarArrastre);
            encabezado.addEventListener('pointercancel', terminarArrastre);
            encabezado.addEventListener('lostpointercapture', terminarArrastre);
            modal.addEventListener('show.bs.modal', restablecerPosicion);
            modal.addEventListener('hidden.bs.modal', restablecerPosicion);
            window.addEventListener('resize', restablecerPosicion);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciar, { once: true });
    } else {
        iniciar();
    }
}());
