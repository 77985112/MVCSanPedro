/**
 * Compresión automática de archivos de actividades.
 *
 * Los formularios de actividades suben imágenes y videos. El hosting gratuito
 * limita cada petición a ~10 MB, así que antes de enviar el formulario:
 *   - las imágenes de más de 2 MB se redimensionan y recomprimen,
 *   - los videos de más de 9,5 MB se recodifican en el navegador (WebM)
 *     hasta quedar por debajo del límite.
 * Si el navegador no puede comprimir, el archivo se deja como está y el
 * servidor responde con un mensaje claro.
 */
(function () {
    'use strict';

    var LIMITE_VIDEO = 9.5 * 1024 * 1024;   // margen bajo el límite de 10 MB
    var OBJETIVO_VIDEO = 8.0 * 1024 * 1024; // tamaño buscado al recomprimir
    var LIMITE_IMAGEN = 2.0 * 1024 * 1024;
    var OBJETIVO_IMAGEN = 1.8 * 1024 * 1024;
    var MAX_ANCHO_IMAGEN = 1600;
    var pendientes = 0;

    function soportaVideo() {
        return typeof window.MediaRecorder !== 'undefined'
            && typeof HTMLMediaElement.prototype.captureStream === 'function';
    }

    function cajaAviso(entrada) {
        var id = 'avisoCompresion-' + (entrada.id || 'x');
        var caja = document.getElementById(id);
        if (!caja) {
            caja = document.createElement('div');
            caja.id = id;
            caja.className = 'small mt-1 text-muted';
            if (entrada.parentNode) {
                entrada.parentNode.insertBefore(caja, entrada.nextSibling);
            }
        }
        return caja;
    }

    function aviso(entrada, texto, clase) {
        if (!entrada.parentNode) return;
        var caja = cajaAviso(entrada);
        caja.className = 'small mt-1 ' + (clase || 'text-muted');
        caja.textContent = texto || '';
        if (!texto) {
            caja.remove();
        }
    }

    function ocupar(entrada) {
        entrada.dataset.comprimiendo = '1';
        pendientes += 1;
        actualizarBotones(entrada.form);
    }

    function liberar(entrada) {
        delete entrada.dataset.comprimiendo;
        pendientes = Math.max(0, pendientes - 1);
        actualizarBotones(entrada.form);
    }

    function actualizarBotones(form) {
        if (!form) return;
        var botones = form.querySelectorAll('button[type="submit"]');
        Array.prototype.forEach.call(botones, function (boton) {
            boton.disabled = pendientes > 0;
        });
    }

    function reemplazarArchivo(entrada, archivo) {
        var seleccion = new DataTransfer();
        seleccion.items.add(archivo);
        entrada.files = seleccion.files;
    }

    function reemplazarArchivos(entrada, archivos) {
        var seleccion = new DataTransfer();
        archivos.forEach(function (archivo) { seleccion.items.add(archivo); });
        entrada.files = seleccion.files;
    }

    function nombreBase(nombre) {
        return String(nombre || 'archivo').replace(/\.[^.]+$/, '');
    }

    function comprimirImagen(archivo) {
        return new Promise(function (resolver) {
            var url = URL.createObjectURL(archivo);
            var img = new Image();
            img.onload = function () {
                var escala = Math.min(1, MAX_ANCHO_IMAGEN / img.width);
                var lienzo = document.createElement('canvas');
                lienzo.width = Math.max(1, Math.round(img.width * escala));
                lienzo.height = Math.max(1, Math.round(img.height * escala));
                var ctx = lienzo.getContext('2d');
                ctx.drawImage(img, 0, 0, lienzo.width, lienzo.height);
                URL.revokeObjectURL(url);

                var calidad = 0.85;
                var intentar = function () {
                    lienzo.toBlob(function (blob) {
                        if (!blob) { resolver(null); return; }
                        if (blob.size > OBJETIVO_IMAGEN && calidad > 0.5) {
                            calidad -= 0.1;
                            intentar();
                            return;
                        }
                        resolver(new File([blob], nombreBase(archivo.name) + '.jpg', {
                            type: 'image/jpeg',
                            lastModified: Date.now()
                        }));
                    }, 'image/jpeg', calidad);
                };
                intentar();
            };
            img.onerror = function () {
                URL.revokeObjectURL(url);
                resolver(null);
            };
            img.src = url;
        });
    }

    function comprimirVideo(archivo, alProgreso, factor) {
        factor = factor || 1;
        return new Promise(function (resolver, rechazar) {
            if (!soportaVideo()) { rechazar(new Error('sin-soporte')); return; }

            var url = URL.createObjectURL(archivo);
            var video = document.createElement('video');
            video.src = url;
            video.muted = true;
            video.playsInline = true;
            video.preload = 'auto';
            video.style.cssText = 'position:fixed;left:-9999px;top:0;width:2px;height:2px;opacity:0;';
            document.body.appendChild(video);

            var limpiar = function () {
                URL.revokeObjectURL(url);
                if (video.parentNode) video.parentNode.removeChild(video);
            };

            video.onloadedmetadata = function () {
                var duracion = video.duration;
                if (!isFinite(duracion) || duracion <= 0) {
                    limpiar();
                    rechazar(new Error('duracion'));
                    return;
                }

                // Se dibuja en un lienzo a 720 px de ancho: así el grabador sí
                // respeta el bitrate pedido (captureStream directo lo ignora).
                var anchoFuente = video.videoWidth || 640;
                var altoFuente = video.videoHeight || 360;
                var ancho = Math.min(720, anchoFuente);
                var alto = Math.max(2, Math.round(ancho * altoFuente / anchoFuente));
                if (ancho % 2) ancho -= 1;
                if (alto % 2) alto -= 1;

                var lienzo = document.createElement('canvas');
                lienzo.width = ancho;
                lienzo.height = alto;
                var ctx = lienzo.getContext('2d');

                var stream;
                try {
                    stream = lienzo.captureStream(24);
                } catch (e) {
                    limpiar();
                    rechazar(new Error('captura'));
                    return;
                }

                // Se conserva el audio original, si lo tiene.
                try {
                    var origenAudio = video.captureStream();
                    origenAudio.getAudioTracks().forEach(function (pista) {
                        stream.addTrack(pista);
                    });
                } catch (e) { /* sin audio */ }

                var audioBits = 64000;
                var totalBits = OBJETIVO_VIDEO * factor * 8 * 0.9;
                var videoBits = Math.max(400000, Math.floor(totalBits / duracion) - audioBits);

                var mime = ['video/webm;codecs=vp8,opus', 'video/webm;codecs=vp9,opus', 'video/webm']
                    .filter(function (tipo) { return MediaRecorder.isTypeSupported(tipo); })[0];

                if (!mime) {
                    limpiar();
                    rechazar(new Error('codec'));
                    return;
                }

                var trozos = [];
                var grabador;
                try {
                    grabador = new MediaRecorder(stream, {
                        mimeType: mime,
                        videoBitsPerSecond: videoBits,
                        audioBitsPerSecond: audioBits
                    });
                } catch (e) {
                    limpiar();
                    rechazar(new Error('grabador'));
                    return;
                }

                grabador.ondataavailable = function (evento) {
                    if (evento.data && evento.data.size) trozos.push(evento.data);
                };

                var terminado = false;
                var finalizar = function () {
                    if (terminado) return;
                    terminado = true;
                    clearInterval(reloj);
                    limpiar();
                    var blob = new Blob(trozos, { type: 'video/webm' });
                    if (!blob.size) { rechazar(new Error('vacio')); return; }
                    resolver(new File([blob], nombreBase(archivo.name) + '.webm', {
                        type: 'video/webm',
                        lastModified: Date.now()
                    }));
                };

                grabador.onstop = finalizar;

                var dibujar = function () {
                    if (terminado || video.ended) return;
                    try {
                        ctx.drawImage(video, 0, 0, ancho, alto);
                    } catch (e) { /* cuadro no disponible */ }
                    requestAnimationFrame(dibujar);
                };

                video.onended = function () {
                    if (grabador.state !== 'inactive') grabador.stop();
                };

                var reloj = setInterval(function () {
                    var avance = duracion ? Math.min(99, Math.round((video.currentTime / duracion) * 100)) : 0;
                    alProgreso(avance);
                }, 300);

                grabador.start(1000);
                video.play().then(function () {
                    requestAnimationFrame(dibujar);
                }).catch(function () {
                    if (grabador.state !== 'inactive') grabador.stop();
                });
            };

            video.onerror = function () {
                limpiar();
                rechazar(new Error('lectura'));
            };
        });
    }

    function procesarVideo(entrada) {
        if (entrada.dataset.comprimiendo === '1') return;
        var archivo = entrada.files && entrada.files[0];
        if (!archivo || archivo.size <= LIMITE_VIDEO) return;

        if (!soportaVideo()) {
            aviso(entrada, 'Este navegador no puede comprimir el video. Usa Chrome o Edge, o pega un enlace de YouTube.', 'text-warning');
            return;
        }

        ocupar(entrada);
        aviso(entrada, 'Comprimiendo video… 0 %', 'text-primary');

        var progreso = function (pct) {
            aviso(entrada, 'Comprimiendo video… ' + pct + ' %', 'text-primary');
        };
        var aceptar = function (nuevo) {
            reemplazarArchivo(entrada, nuevo);
            aviso(entrada, 'Video comprimido: ' + (nuevo.size / 1024 / 1024).toFixed(1) + ' MB', 'text-success');
            liberar(entrada);
            entrada.dispatchEvent(new Event('change', { bubbles: true }));
        };
        var fallar = function () {
            aviso(entrada, 'No se pudo dejar el video bajo 10 MB. Prueba con un video más corto o pega un enlace de YouTube.', 'text-danger');
            liberar(entrada);
        };

        comprimirVideo(archivo, progreso, 1).then(function (nuevo) {
            if (nuevo && nuevo.size <= LIMITE_VIDEO) { aceptar(nuevo); return; }
            // Segundo intento con menos bitrate si el primero se pasó.
            aviso(entrada, 'Ajustando el tamaño del video…', 'text-primary');
            return comprimirVideo(archivo, progreso, 0.6).then(function (segundo) {
                if (segundo && segundo.size <= LIMITE_VIDEO) { aceptar(segundo); }
                else { fallar(); }
            });
        }).catch(function () {
            aviso(entrada, 'No se pudo comprimir el video. Pega un enlace de YouTube o usa un archivo menor a 10 MB.', 'text-danger');
            liberar(entrada);
        });
    }

    function formatearPeso(bytes) {
        if (bytes >= 1024 * 1024) {
            return (bytes / 1024 / 1024).toFixed(1) + ' MB';
        }
        return Math.max(1, Math.round(bytes / 1024)) + ' KB';
    }

    function textoPesos(archivos, comprimidas) {
        var pesos = archivos.map(function (a) { return formatearPeso(a.size); }).join(' · ');
        var etiqueta = archivos.length === 1 ? 'Imagen' : 'Imágenes';
        var sufijo = comprimidas ? (archivos.length === 1 ? ' comprimida' : ' comprimidas') : '';
        return etiqueta + sufijo + ': ' + pesos;
    }

    function procesarImagenes(entrada) {
        if (entrada.dataset.comprimiendo === '1') return;
        var archivos = Array.prototype.slice.call(entrada.files || []);
        if (!archivos.length) { aviso(entrada, ''); return; }

        var recien = entrada.dataset.recienComprimidas === '1';
        delete entrada.dataset.recienComprimidas;

        var grandes = archivos.filter(function (a) { return a.size > LIMITE_IMAGEN; });
        if (!grandes.length) {
            aviso(entrada, textoPesos(archivos, recien), recien ? 'text-success' : 'text-muted');
            return;
        }

        ocupar(entrada);
        aviso(entrada, 'Comprimiendo imágenes…', 'text-primary');

        Promise.all(archivos.map(function (archivo) {
            if (archivo.size <= LIMITE_IMAGEN) return Promise.resolve(archivo);
            return comprimirImagen(archivo).then(function (nuevo) { return nuevo || archivo; });
        })).then(function (resultado) {
            reemplazarArchivos(entrada, resultado);
            entrada.dataset.recienComprimidas = '1';
            liberar(entrada);
            aviso(entrada, textoPesos(resultado, true), 'text-success');
            entrada.dispatchEvent(new Event('change', { bubbles: true }));
        }).catch(function () {
            liberar(entrada);
            aviso(entrada, '');
        });
    }

    function conectar(entrada) {
        if (entrada.dataset.compresionConectada === '1') return;
        entrada.dataset.compresionConectada = '1';
        entrada.addEventListener('change', function () {
            if (entrada.hasAttribute('data-actividad-video')) {
                procesarVideo(entrada);
            } else {
                procesarImagenes(entrada);
            }
        });
    }

    function iniciar() {
        var entradas = document.querySelectorAll('input[data-actividad-video], input[data-actividad-imagen]');
        Array.prototype.forEach.call(entradas, conectar);
    }

    // Bloquea el envío mientras haya archivos comprimiéndose.
    document.addEventListener('submit', function (evento) {
        if (pendientes > 0) {
            evento.preventDefault();
            evento.stopPropagation();
            var form = evento.target;
            var boton = form && form.querySelector('button[type="submit"]');
            if (boton) {
                boton.disabled = true;
                boton.textContent = 'Comprimiendo…';
                setTimeout(function () {
                    boton.disabled = false;
                    boton.textContent = boton.dataset.textoOriginal || 'Guardar';
                }, 2500);
            }
        }
    }, true);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciar);
    } else {
        iniciar();
    }
})();
