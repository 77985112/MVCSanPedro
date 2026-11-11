// Controles del formulario publico de inicio de sesion.
(function () {
    'use strict';

    function iniciarAcceso() {
        var modal = document.querySelector('.login-modal');
        if (!modal) return;

        var password = modal.querySelector('#password');
        var toggle = modal.querySelector('.login-password-toggle');
        var username = modal.querySelector('#username');
        if (!password || !toggle) return;

        function mostrarPassword(mostrar) {
            password.type = mostrar ? 'text' : 'password';
            toggle.setAttribute('aria-pressed', String(mostrar));
            toggle.setAttribute('aria-label', mostrar ? 'Ocultar contraseña' : 'Mostrar contraseña');
            var icon = toggle.querySelector('.bi');
            icon.classList.toggle('bi-eye', mostrar);
            icon.classList.toggle('bi-eye-slash', !mostrar);
        }

        toggle.addEventListener('click', function () {
            mostrarPassword(password.type === 'password');
        });

        modal.addEventListener('shown.bs.modal', function () {
            if (username && window.matchMedia('(min-width: 768px)').matches) {
                username.focus({ preventScroll: true });
            }
        });

        modal.addEventListener('hidden.bs.modal', function () {
            mostrarPassword(false);
        });
    }

    function interceptarLogin() {
        var formulario = document.getElementById('login-form');
        if (!formulario || !window.fetch || !window.FormData) return;

        formulario.addEventListener('submit', function (evento) {
            evento.preventDefault();

            var boton = formulario.querySelector('button[type="submit"]');
            if (boton) boton.disabled = true;

            fetch(formulario.action, {
                method: 'POST',
                body: new FormData(formulario),
                credentials: 'same-origin',
                redirect: 'follow'
            }).then(function (respuesta) {
                // El controlador redirige al panel o al login con el error.
                // El POST se hizo en segundo plano, así que no queda en el
                // historial; la página de inicio sí queda y "atrás" funciona normal.
                window.location.assign(respuesta.url);
            }).catch(function () {
                if (boton) boton.disabled = false;
                formulario.submit();
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            iniciarAcceso();
            interceptarLogin();
        });
    } else {
        iniciarAcceso();
        interceptarLogin();
    }
}());
