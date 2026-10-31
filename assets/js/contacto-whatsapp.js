(function () {
    'use strict';
    document.addEventListener('DOMContentLoaded', function () {
        var boton = document.getElementById('preparar-whatsapp');
        var formulario = document.getElementById('formulario-contacto');
        if (!boton || !formulario) return;
        boton.addEventListener('click', function () {
            if (!formulario.reportValidity()) return;
            var nombre = formulario.elements.nombre.value.trim();
            var telefono = formulario.elements.numcorr.value.trim();
            var concepto = formulario.elements.concepto.value.trim();
            var detalle = formulario.elements.detalle.value.trim();
            var mensaje = ['Hola, Parroquia San Pedro de Sacaba.', '', 'Mi nombre es ' + nombre + '.', 'Mi número de contacto es ' + telefono + '.', 'Motivo: ' + concepto, 'Mensaje: ' + detalle].join('\n');
            window.open('https://wa.me/59177985112?text=' + encodeURIComponent(mensaje), '_blank', 'noopener,noreferrer');
        });
    });
}());
