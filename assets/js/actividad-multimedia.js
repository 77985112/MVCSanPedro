(function () {
    'use strict';
    const grupos = new Map();
    const entradas = Array.from(document.querySelectorAll('input[data-actividad-imagen], input[data-actividad-video]'));

    function esContenedor(preview) {
        return preview && preview.tagName !== 'IMG' && preview.tagName !== 'VIDEO';
    }
    function maximasDe(entrada) {
        const base = parseInt(entrada.dataset.actividadMaximas, 10);
        let maximo = Number.isNaN(base) ? 7 : Math.max(0, base);
        const quitarTodas = entrada.form && entrada.form.querySelector('input[data-quitar-actividad="' + entrada.id + '"]');
        if (quitarTodas && quitarTodas.checked) return 5;
        const galeria = document.getElementById(entrada.dataset.actividadGaleria || '');
        if (galeria) maximo += galeria.querySelectorAll('input[name="QuitarImagenExtra[]"]:checked').length;
        return Math.min(5, maximo);
    }
    function errorArchivo(entrada) {
        const archivos = Array.from(entrada.files || []);
        const esVideo = entrada.hasAttribute('data-actividad-video');
        if (!esVideo && archivos.length > maximasDe(entrada)) {
            return 'Puede agregar hasta ' + maximasDe(entrada) + ' imágenes más. El máximo por actividad es 5; quite alguna para liberar espacio.';
        }
        const extensionesVideo = ['mp4', 'webm'];
        const extensionesImagen = ['jpg', 'jpeg', 'png', 'jfif', 'webp', 'gif', 'bmp', 'avif', 'tif', 'tiff'];
        const maximo = (esVideo ? 10 : 20) * 1024 * 1024;
        return archivos.some(function (archivo) {
            const extension = archivo.name.split('.').pop().toLowerCase();
            const esImagenValida = !esVideo
                && ((archivo.type || '').indexOf('image/') === 0 || extensionesImagen.includes(extension));
            const esVideoValido = esVideo && extensionesVideo.includes(extension);
            return (!esImagenValida && !esVideoValido)
                || archivo.size === 0 || archivo.size > maximo;
        }) ? (esVideo ? 'Seleccione un video MP4/WebM de hasta 10 MB.'
            : 'Seleccione una imagen válida (JPG, PNG, WEBP, GIF, etc.) de hasta 20 MB. Se convertirá a JPG automáticamente.') : '';
    }
    function hayVideoUrl() {
        const campo = document.querySelector('input[name="VideoUrl"]');
        return !!(campo && campo.value.trim() !== '');
    }
    function validarGrupos() {
        const urlVideo = hayVideoUrl();
        grupos.forEach(function (grupo) {
            const adjunto = grupo.entradas.some(function (entrada) { return entrada.files.length > 0; });
            grupo.entradas.forEach(function (entrada, indice) {
                const error = errorArchivo(entrada);
                entrada.setCustomValidity(error || (grupo.requerido && !adjunto && !urlVideo && indice === 0
                    ? 'Adjunte hasta 5 imágenes, un video MP4/WebM, ambos o pegue un enlace de video.' : ''));
            });
        });
    }
    function limpiarMiniaturas(preview) {
        (preview.__urls || []).forEach(function (url) { URL.revokeObjectURL(url); });
        preview.__urls = [];
        preview.replaceChildren();
    }
    function ocultarPreview(preview) {
        if (!preview) return;
        if (esContenedor(preview)) limpiarMiniaturas(preview);
        else {
            if (preview.tagName === 'VIDEO') preview.pause();
            preview.removeAttribute('src');
            if (preview.tagName === 'VIDEO') preview.load();
        }
        preview.classList.add('d-none');
    }
    function restaurarPreview(preview, original) {
        if (esContenedor(preview)) {
            limpiarMiniaturas(preview);
            preview.classList.remove('d-none');
        } else if (original) {
            preview.src = original;
            preview.classList.remove('d-none');
        } else ocultarPreview(preview);
    }
    function moverImagen(entrada, origen, destino) {
        const archivos = Array.from(entrada.files || []);
        if (origen === destino || destino < 0 || destino >= archivos.length) return;
        const foto = archivos.splice(origen, 1)[0];
        archivos.splice(destino, 0, foto);
        const seleccion = new DataTransfer();
        archivos.forEach(function (archivo) { seleccion.items.add(archivo); });
        // El orden visual también debe ser el de los archivos que recibe PHP.
        entrada.files = seleccion.files;
        entrada.dispatchEvent(new Event('change', { bubbles: true }));
        const preview = document.getElementById(entrada.dataset.actividadImagen);
        const posicion = preview && preview.querySelectorAll('[data-actividad-posicion]')[destino];
        if (posicion) posicion.focus({ preventScroll: true });
    }
    function pintarMiniaturas(preview, archivos, entrada) {
        limpiarMiniaturas(preview);
        preview.classList.remove('d-flex', 'flex-wrap');
        preview.classList.add('d-grid');
        preview.style.gridTemplateColumns = 'repeat(auto-fill, minmax(130px, 1fr))';
        preview.style.gap = '12px';
        const inicio = entrada.dataset.actividadGaleria ? 7 - maximasDe(entrada) : 0;
        const ayuda = document.createElement('p');
        ayuda.className = 'form-text m-0';
        ayuda.style.gridColumn = '1 / -1';
        ayuda.textContent = inicio === 0
            ? 'Cambia la posición con las flechas o el número. La imagen 1 será la principal.'
            : 'Elige el orden de las imágenes nuevas con las flechas o el número.';
        preview.appendChild(ayuda);
        archivos.forEach(function (archivo, indice) {
            const numero = inicio + indice + 1;
            const caja = document.createElement('div');
            caja.className = 'border rounded p-2 bg-body';
            const cabecera = document.createElement('div');
            cabecera.className = 'd-flex align-items-center justify-content-between mb-2 gap-1';
            const titulo = document.createElement('strong');
            titulo.className = 'small';
            titulo.textContent = 'Imagen ' + numero;
            cabecera.appendChild(titulo);
            if (numero === 1) {
                const principal = document.createElement('span');
                principal.className = 'badge bg-primary-soft text-primary';
                principal.style.fontSize = '.6rem';
                principal.textContent = 'Principal';
                cabecera.appendChild(principal);
            }
            const ampliar = document.createElement('button');
            ampliar.type = 'button';
            ampliar.className = 'border-0 p-0 w-100 bg-transparent';
            ampliar.setAttribute('aria-label', 'Ampliar imagen ' + numero);
            const imagen = document.createElement('img');
            const url = URL.createObjectURL(archivo);
            preview.__urls.push(url);
            imagen.src = url;
            imagen.className = 'rounded w-100';
            imagen.style.cssText = 'height:130px;object-fit:contain;background:#edf1f7';
            imagen.alt = 'Imagen ' + numero + ' seleccionada';
            ampliar.appendChild(imagen);
            ampliar.addEventListener('click', function () { ampliarSeleccion(url, 'Imagen ' + numero); });
            const pie = document.createElement('div');
            pie.className = 'small text-truncate mt-2';
            pie.style.fontSize = '.7rem';
            pie.textContent = archivo.name;
            pie.title = archivo.name;
            const orden = document.createElement('div');
            orden.className = 'd-flex align-items-center gap-1 mt-2';
            orden.setAttribute('role', 'group');
            orden.setAttribute('aria-label', 'Orden de la imagen ' + numero);
            const antes = document.createElement('button');
            antes.type = 'button';
            antes.className = 'btn btn-light btn-sm px-2 mb-0';
            antes.textContent = '←';
            antes.title = 'Mover una posición antes';
            antes.setAttribute('aria-label', 'Mover imagen ' + numero + ' una posición antes');
            antes.disabled = indice === 0;
            antes.addEventListener('click', function () { moverImagen(entrada, indice, indice - 1); });
            const posicion = document.createElement('select');
            posicion.className = 'form-select form-select-sm py-1';
            posicion.style.cssText = 'flex:1;min-width:0;padding-left:8px;padding-right:22px;background-position:right 5px center';
            posicion.dataset.actividadPosicion = '';
            posicion.setAttribute('aria-label', 'Posición de la imagen ' + numero);
            archivos.forEach(function (foto, destino) {
                const opcion = document.createElement('option');
                opcion.value = String(destino);
                opcion.textContent = String(inicio + destino + 1);
                opcion.selected = destino === indice;
                posicion.appendChild(opcion);
            });
            posicion.addEventListener('change', function () {
                moverImagen(entrada, indice, Number(posicion.value));
            });
            const despues = document.createElement('button');
            despues.type = 'button';
            despues.className = 'btn btn-light btn-sm px-2 mb-0';
            despues.textContent = '→';
            despues.title = 'Mover una posición después';
            despues.setAttribute('aria-label', 'Mover imagen ' + numero + ' una posición después');
            despues.disabled = indice === archivos.length - 1;
            despues.addEventListener('click', function () { moverImagen(entrada, indice, indice + 1); });
            orden.append(antes, posicion, despues);
            const quitar = document.createElement('button');
            quitar.type = 'button';
            quitar.className = 'btn btn-outline-danger btn-sm w-100 mt-2 mb-0';
            quitar.textContent = 'Quitar';
            quitar.setAttribute('aria-label', 'Quitar imagen ' + numero);
            quitar.addEventListener('click', function () {
                const seleccion = new DataTransfer();
                Array.from(entrada.files).forEach(function (foto, posicion) {
                    if (posicion !== indice) seleccion.items.add(foto);
                });
                entrada.files = seleccion.files;
                entrada.dispatchEvent(new Event('change', { bubbles: true }));
            });
            caja.append(cabecera, ampliar, pie, orden, quitar);
            preview.appendChild(caja);
        });
        preview.classList.remove('d-none');
    }
    let visor;
    function ampliarSeleccion(url, titulo) {
        if (!visor) {
            visor = document.createElement('dialog');
            visor.style.cssText = 'border:0;border-radius:14px;padding:16px;max-width:94vw;max-height:94vh;background:#fff;box-shadow:0 0 0 100vmax #0009';
            const cerrar = document.createElement('button');
            cerrar.type = 'button';
            cerrar.className = 'btn btn-light btn-sm d-block ms-auto mb-2';
            cerrar.textContent = 'Cerrar';
            cerrar.addEventListener('click', function () { visor.close(); });
            const imagen = document.createElement('img');
            imagen.style.cssText = 'display:block;max-width:88vw;max-height:78vh;object-fit:contain';
            visor.append(cerrar, imagen);
            document.body.appendChild(visor);
            visor.addEventListener('click', function (event) {
                if (event.target === visor) visor.close();
            });
        }
        const imagen = visor.querySelector('img');
        imagen.src = url;
        imagen.alt = titulo;
        visor.setAttribute('aria-label', titulo);
        visor.showModal();
    }
    entradas.forEach(function (entrada) {
        const nombre = entrada.dataset.actividadGrupo || 'actividad';
        if (!grupos.has(nombre)) grupos.set(nombre, { requerido: false, entradas: [] });
        const grupo = grupos.get(nombre);
        grupo.requerido = grupo.requerido || entrada.dataset.actividadRequerido === 'true';
        grupo.entradas.push(entrada);
        const preview = document.getElementById(entrada.dataset.actividadVideo || entrada.dataset.actividadImagen);
        if (!preview) return;
        const original = preview.getAttribute('src') || '';
        let temporal = null;
        entrada.addEventListener('change', function () {
            // En una galería se pueden quitar las anteriores y añadir las nuevas en el mismo guardado.
            const quitar = entrada.form && entrada.form.querySelector('input[data-quitar-actividad="' + entrada.id + '"]');
            if (quitar && !entrada.multiple && entrada.files.length) quitar.checked = false;
            validarGrupos();
            if (errorArchivo(entrada)) {
                ocultarPreview(preview);
                entrada.reportValidity();
                return;
            }
            const archivos = Array.from(entrada.files || []);
            if (!archivos.length) {
                if (temporal) URL.revokeObjectURL(temporal);
                temporal = null;
                restaurarPreview(preview, original);
            } else if (entrada.multiple && esContenedor(preview)) {
                pintarMiniaturas(preview, archivos, entrada);
            } else {
                if (temporal) URL.revokeObjectURL(temporal);
                temporal = URL.createObjectURL(archivos[0]);
                preview.src = temporal;
                preview.classList.remove('d-none');
            }
        });
    });
    document.querySelectorAll('input[data-quitar-actividad]').forEach(function (casilla) {
        const entrada = document.getElementById(casilla.dataset.quitarActividad);
        if (!entrada) return;
        const preview = document.getElementById(entrada.dataset.actividadVideo || entrada.dataset.actividadImagen);
        const original = preview ? preview.getAttribute('src') || '' : '';
        const galeria = document.getElementById(entrada.dataset.actividadGaleria || '');
        casilla.addEventListener('change', function () {
            if (entrada.multiple) {
                if (galeria) galeria.classList.toggle('d-none', casilla.checked);
                entrada.dispatchEvent(new Event('change', { bubbles: true }));
            } else if (casilla.checked) {
                entrada.value = '';
                ocultarPreview(preview);
            } else restaurarPreview(preview, original);
            validarGrupos();
        });
    });
    document.querySelectorAll('input[name="QuitarImagenExtra[]"]').forEach(function (casilla) {
        casilla.addEventListener('change', function () {
            entradas.filter(function (entrada) {
                const galeria = document.getElementById(entrada.dataset.actividadGaleria || '');
                return galeria && galeria.contains(casilla);
            }).forEach(function (entrada) {
                entrada.dispatchEvent(new Event('change', { bubbles: true }));
            });
            validarGrupos();
        });
    });
    document.querySelectorAll('input[name="VideoUrl"]').forEach(function (campo) {
        campo.addEventListener('input', validarGrupos);
        campo.addEventListener('change', validarGrupos);
    });
    validarGrupos();
    document.addEventListener('hidden.bs.modal', function (event) {
        event.target.querySelectorAll('video.actividad-video').forEach(function (video) { video.pause(); });
    });
    document.addEventListener('hidden.bs.tab', function (event) {
        const destino = event.target.getAttribute('href') || '';
        if (!/^#[A-Za-z0-9_-]+$/.test(destino)) return;
        const panel = document.querySelector(destino);
        if (panel) panel.querySelectorAll('video.actividad-video').forEach(function (video) { video.pause(); });
    });
})();
