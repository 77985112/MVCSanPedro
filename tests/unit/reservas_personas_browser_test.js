// node tests/unit/reservas_personas_browser_test.js
// Chrome sin interfaz; HTML y JS de la vista, API simulada y perfil temporal.
const fs = require('node:fs');
const path = require('node:path');
const http = require('node:http');
const { spawn } = require('node:child_process');
const { once } = require('node:events');
const assert = require('node:assert/strict');
const raiz = path.resolve(__dirname, '../..');
const fuente = fs.readFileSync(path.join(raiz, 'VistaPersonal/VistaReservas.php'), 'utf8');
const scripts = [...fuente.matchAll(/<script>([\s\S]*?)<\/script>/g)].map(m => m[1])
    .filter(s => /var urlParams|function submitPersonaModal|var formularios = Array.from|document.querySelectorAll\('\.confirmacion-correo'/.test(s));
assert.equal(scripts.length, 4, 'Encuentra los scripts reales del flujo');
scripts.forEach(s => new Function(s));
let token = 'token-prueba-1', envios = 0, fallar = false;
const personas = new Map();
const demora = new Map();
const estaticos = {
    '/jquery.js': 'assets/js/jquery-3.6.4.min.js',
    '/bootstrap.js': 'assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js',
    '/bootstrap.css': 'assets/css/style.css'
};
const servidor = http.createServer(async (req, res) => {
    const url = new URL(req.url, 'http://localhost');
    function json(datos) { res.setHeader('Content-Type', 'application/json'); res.end(JSON.stringify(datos)); }
    if (url.pathname in estaticos) {
        res.setHeader('Content-Type', url.pathname.endsWith('.css') ? 'text/css' : 'text/javascript');
        return res.end(fs.readFileSync(path.join(raiz, estaticos[url.pathname])));
    }
    if (url.searchParams.has('ciPersona')) {
        const ci = url.searchParams.get('ciPersona');
        await pausa(demora.get(ci) || 0);
        return json(personas.get(ci) || { error: 'Persona no encontrada' });
    }
    if (req.method === 'POST') {
        envios++;
        let cuerpo = '';
        for await (const trozo of req) cuerpo += trozo;
        const datos = Object.fromEntries([...cuerpo.matchAll(/name="([^"]+)"\r\n\r\n([\s\S]*?)\r\n--/g)].map(m => [m[1], m[2]]));
        await pausa(100);
        if (fallar) return json({ success: false, message: 'Fallo de prueba' });
        const ciPersona = (datos.CiPersona || datos.Partida || '').trim();
        personas.set(ciPersona, { ...datos, CiPersona: ciPersona });
        return json({ success: true, message: 'Guardado', ciPersona });
    }
    let cuerpo = fuente.slice(fuente.indexOf('<body>'), fuente.lastIndexOf('</body>'))
        .replace(/<\?php[\s\S]*?\?>/g, '').replace(/<script\b[^>]*>[\s\S]*?<\/script>/g, '')
        .replace(/<img\b[^>]*>/g, '');
    cuerpo = cuerpo.replace(/(name="CodPer"[^>]*value=")[^"]*/g, (_, p) => p + '42')
        .replace(/(name="TokenReserva"[^>]*value=")[^"]*/g, (_, p) => p + token);
    res.setHeader('Content-Type', 'text/html; charset=utf-8');
    res.end('<!doctype html><html><head><link rel="stylesheet" href="/bootstrap.css">' +
        '<script src="/jquery.js"></script><script src="/bootstrap.js"></script>' +
        '<script>window.erroresPrueba=[]; window.avisosPrueba=[]; window.alert=m=>avisosPrueba.push(m);' +
        'window.addEventListener("error",e=>erroresPrueba.push(e.message));</script></head>' +
        cuerpo + scripts.map(s => '<script>' + s + '</script>').join('') + '</body></html>');
});
function pausa(ms) { return new Promise(r => setTimeout(r, ms)); }
let chrome, ws, perfil, contador = 0, comprobaciones = 0;
const pendientes = new Map();
function cdp(method, params = {}) {
    return new Promise((resolve, reject) => {
        const id = ++contador;
        const temporizador = setTimeout(() => { pendientes.delete(id); reject(new Error('Tiempo agotado: ' + method)); }, 15000);
        pendientes.set(id, { resolve, reject, temporizador });
        ws.send(JSON.stringify({ id, method, params }));
    });
}
async function evaluar(fn, ...args) {
    const r = await cdp('Runtime.evaluate', { expression: '(' + fn.toString() + ')(' + args.map(a => JSON.stringify(a)).join(',') + ')', awaitPromise: true, returnByValue: true });
    if (r.exceptionDetails) throw new Error(JSON.stringify(r.exceptionDetails));
    return r.result.value;
}
async function esperar(fn, ...args) {
    for (let i = 0; i < 100; i++) {
        if (await evaluar(fn, ...args)) return;
        await pausa(50);
    }
    throw new Error('No se cumplió: ' + fn.toString() + ' ' + JSON.stringify(args));
}
function comprobar(valor, mensaje) { assert.ok(valor, mensaje); comprobaciones++; }
(async () => {
    await new Promise(r => servidor.listen(0, '127.0.0.1', r));
    perfil = fs.mkdtempSync(path.join(raiz, 'tests/.tmp-personas-browser-'));
    const ejecutable = process.env.CHROME_PATH || 'C:/Program Files/Google/Chrome/Application/chrome.exe';
    chrome = spawn(ejecutable, ['--headless=new', '--disable-gpu', '--disable-background-networking', '--no-first-run', '--remote-debugging-port=0', '--user-data-dir=' + perfil, 'about:blank'], { windowsHide: true, stdio: ['ignore', 'ignore', 'pipe'] });
    const direccion = await new Promise((resolve, reject) => {
        const limite = setTimeout(() => reject(new Error('Chrome no inició')), 15000);
        chrome.once('error', reject);
        chrome.stderr.on('data', trozo => {
            const m = trozo.toString().match(/DevTools listening on (ws:\/\/[^\s]+)/);
            if (m) { clearTimeout(limite); resolve(m[1]); }
        });
    });
    const depuracion = new URL(direccion);
    const paginas = await fetch('http://' + depuracion.host + '/json/list').then(r => r.json());
    ws = new WebSocket(paginas.find(p => p.type === 'page').webSocketDebuggerUrl);
    await once(ws, 'open');
    ws.addEventListener('message', e => {
        const m = JSON.parse(e.data), p = pendientes.get(m.id);
        if (!p) return;
        pendientes.delete(m.id); clearTimeout(p.temporizador);
        if (m.error) p.reject(new Error(m.error.message)); else p.resolve(m.result);
    });
    await cdp('Page.enable');
    await cdp('Page.navigate', { url: 'http://127.0.0.1:' + servidor.address().port + '/VistaPersonal/VistaReservas.php' });
    await esperar(() => document.readyState === 'complete' && typeof submitPersonaModal === 'function');
    await pausa(100);
    const casos = [
        ['tab-2', 'CiPersonaCelebranteB'], ['tab-2', 'CiPapa'], ['tab-2', 'CiMama'],
        ['tab-2', 'CiPadrino'], ['tab-2', 'CiMadrina'],
        ...['CiPersonaNovio', 'CiPersonaNovia', 'CiPaNovio', 'CiMaNovio', 'CiPaNovia', 'CiMaNovia', 'CiPadrino', 'CiMadrina', 'CiTestigoNovio', 'CiTestigoNovia'].map(n => ['tab-3', n]),
        ['tab-4', 'CiPersonademas']
    ];
    await evaluar(() => {
        const form = document.querySelector('#tab-2 form');
        for (const [nombre, valor] of Object.entries({ Quien: 'Reserva sin terminar', FechaReal: '2099-10-20', HoraReal: '10:30', CorreoSolicitante: 'prueba@example.com', RealizacionOtro: 'En capilla' })) {
            form.elements[nombre].value = valor;
            form.elements[nombre].dispatchEvent(new Event('input', { bubbles: true }));
        }
        form.querySelector('[name="Realizacion"][value="Otro"]').click();
        form.elements.EnviarConfirmacion.click();
    });
    for (let i = 0; i < casos.length; i++) {
        const [tab, nombre] = casos[i], ci = '00' + (100000 + i);
        await evaluar((tab, nombre) => {
            bootstrap.Tab.getOrCreateInstance(document.querySelector('a[href="#' + tab + '"]')).show();
            document.querySelector('#' + tab + ' input[name="' + nombre + '"]').nextElementSibling.click();
        }, tab, nombre);
        await esperar(() => !!document.querySelector('.modal.show'));
        await pausa(350); // Esperar la animacion de apertura, como al completar el formulario.
        const antes = envios;
        await evaluar((ci, i, nombre) => {
            const form = document.querySelector('.modal.show form');
            form.elements.CiPersona.value = i === 0 ? '' : '  ' + ci + '  ';
            if (i === 0) form.elements.Partida.value = ci;
            form.elements.Nombre.value = 'Persona' + i;
            form.elements.ApPaterno.value = 'Prueba';
            form.elements.FechaNac.value = '1980-01-01';
            const mujer = nombre === 'CiPersonaNovia';
            form.querySelector('[name="Sexo"][value="' + (mujer ? 'Mujer' : 'Varon') + '"]').checked = true;
            if (form.elements.Estado_per) form.elements.Estado_per.value = mujer ? 'Soltera' : 'Soltero';
            form.requestSubmit(); form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
        }, ci, i, nombre);
        await esperar((tab, nombre, ci, i) => {
            const campo = document.querySelector('#' + tab + ' input[name="' + nombre + '"]');
            return campo.value === ci && campo.nextElementSibling.textContent === 'Persona' + i + ' Prueba';
        }, tab, nombre, ci, i);
        comprobar(envios === antes + 1, tab + ' ' + nombre + ': no duplica envío');
        comprobar(await evaluar((tab, nombre, ci) => document.querySelector('#' + tab + ' input[name="' + nombre + '"]').value === ci, tab, nombre, ci), 'Conserva CI en casilla correcta');
        await esperar(() => !document.querySelector('.modal.show') && !document.querySelector('.modal-backdrop'));
    }
    await cdp('Page.reload');
    await esperar(() => document.readyState === 'complete' && !!document.querySelector('#tab-4.active'));
    for (let i = 0; i < casos.length; i++) {
        await esperar((tab, nombre, ci, i) => {
            const campo = document.querySelector('#' + tab + ' input[name="' + nombre + '"]');
            return campo.value === ci && campo.nextElementSibling.textContent === 'Persona' + i + ' Prueba';
        }, ...casos[i], '00' + (100000 + i), i);
        comprobar(true, 'Recarga conserva CI y consulta nombre');
    }
    comprobar(await evaluar(() => {
        const form = document.querySelector('#tab-2 form');
        return form.elements.Quien.value === 'Reserva sin terminar' && form.elements.FechaReal.value === '2099-10-20' && form.elements.HoraReal.value === '10:30' && form.elements.CorreoSolicitante.value === 'prueba@example.com' && form.elements.EnviarConfirmacion.checked && form.elements.CorreoSolicitante.required && form.elements.Realizacion.value === 'Otro' && form.elements.RealizacionOtro.value === 'En capilla' && !form.elements.RealizacionOtro.disabled;
    }), 'Recarga conserva los demás datos y los campos condicionales');
    fallar = true;
    await evaluar(() => {
        const campo = document.querySelector('#tab-4 input[name="CiPersonademas"]');
        campo.value = '9999000'; campo.dispatchEvent(new Event('input', { bubbles: true }));
    });
    await esperar(() => !!document.querySelector('#tab-4 .demas [data-bs-target]'));
    await evaluar(() => document.querySelector('#tab-4 .demas').click());
    await esperar(() => !!document.querySelector('.modal.show'));
    await evaluar(() => {
        const form = document.querySelector('.modal.show form');
        form.elements.CiPersona.value = '9999001'; form.elements.Nombre.value = 'Pendiente';
        form.elements.ApPaterno.value = 'Prueba'; form.elements.FechaNac.value = '1980-01-01';
        form.requestSubmit();
    });
    await esperar(() => avisosPrueba.length > 0);
    comprobar(await evaluar(() => document.querySelector('#tab-4 input[name="CiPersonademas"]').value === '9999000' && document.querySelector('.modal.show form').elements.CiPersona.value === '9999001'), 'Error conserva CI anterior y datos del modal');
    await evaluar(() => bootstrap.Modal.getInstance(document.querySelector('.modal.show')).hide());
    demora.set('00100000', 900);
    await evaluar(() => {
        const campo = document.querySelector('#tab-2 input[name="CiPapa"]');
        campo.value = '00100000'; campo.dispatchEvent(new Event('input', { bubbles: true }));
    });
    await pausa(350);
    await evaluar(() => {
        const campo = document.querySelector('#tab-2 input[name="CiPapa"]');
        campo.value = '00100001'; campo.dispatchEvent(new Event('input', { bubbles: true }));
    });
    await pausa(1100);
    comprobar(await evaluar(() => document.querySelector('#tab-2 .papacelebrante').textContent === 'Persona1 Prueba'), 'Respuesta antigua no reemplaza el nombre actual');
    comprobar(await evaluar(() => erroresPrueba.length === 0), 'Sin errores JavaScript');
    token = 'token-prueba-2'; // Simula el token nuevo después de guardar la reserva.
    await cdp('Page.reload');
    await esperar(() => document.querySelector('[name="TokenReserva"]')?.value === 'token-prueba-2');
    await pausa(400);
    comprobar(await evaluar(() => [...document.querySelectorAll('#tab-2 input[type="text"], #tab-3 input[type="text"], #tab-4 input[type="text"]')].every(c => c.value === '')), 'Reserva guardada descarta el borrador anterior');
    console.log('OK: ' + comprobaciones + ' comprobaciones en Chrome; 16 casillas, recarga, errores y correo/API simulados.');
})().catch(e => { console.error(e); process.exitCode = 1; }).finally(async () => {
    if (ws?.readyState === 1) { try { await cdp('Browser.close'); } catch (_) {} ws.close(); }
    if (chrome && chrome.exitCode === null) { chrome.kill(); await pausa(500); }
    servidor.closeAllConnections(); servidor.close();
    if (perfil && path.resolve(perfil).startsWith(path.join(raiz, 'tests/.tmp-personas-browser-'))) {
        fs.rmSync(perfil, { recursive: true, force: true, maxRetries:5, retryDelay: 200 });
    }
});
