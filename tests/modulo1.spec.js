import { test, expect } from '@playwright/test';
import { existsSync, readFileSync } from 'node:fs';
import path from 'node:path';
import os from 'node:os';
import { randomUUID } from 'node:crypto';

// Solo pruebas del módulo 1. No crean usuarios ni modifican registros.
const BASE = (process.env.PW_BASE_URL || 'http://localhost/MVCSanPedro/').replace(/\/?$/, '/');
const CUENTAS = process.env.PW_CUENTAS_FILE || path.join(
  process.env.LOCALAPPDATA || os.homedir(), 'MVCSanPedroPruebas', 'usuarios-modulo1.json',
);
const usuarios = existsSync(CUENTAS) ? JSON.parse(readFileSync(CUENTAS, 'utf8').replace(/^\uFEFF/, '')) : {};
const paneles = {
  personal: 'VistaPersonal/VistaPersonal.php',
  coordinador: 'VistaCoordinador/VistaCoordinador.php',
  catequista: 'VistaCatequista/VistaCatequista.php',
  catequisando: 'VistaCelebrante/VistaCelebrante.php',
};
const url = ruta => new URL(ruta, BASE).href;
const publica = valor => {
  const u = new URL(valor);
  return u.origin === new URL(BASE).origin &&
    [new URL(BASE).pathname, new URL('index.php', BASE).pathname].includes(u.pathname);
};

test.use({
  baseURL: BASE,
  viewport: { width: 1440, height: 1000 },
  screenshot: 'only-on-failure',
  trace: 'retain-on-failure',
});
test.describe.configure({ mode: 'default', timeout: 120000 });

function requerir(...claves) {
  const faltan = claves.filter(clave =>
    !['usuario', 'contrasena', 'nombre'].every(campo =>
      typeof usuarios[clave]?.[campo] === 'string' && usuarios[clave][campo].trim(),
    ),
  );
  test.skip(faltan.length > 0, 'Faltan cuentas de prueba: ' + faltan.join(', ') + '. Completa ' + CUENTAS);
}

function requerirPersonalRoles() {
  const cuentas = usuarios.personalRoles;
  const validas = Array.isArray(cuentas) && cuentas.length > 0 && cuentas.every(cuenta =>
    ['usuario', 'contrasena', 'nombre', 'rol'].every(campo =>
      typeof cuenta?.[campo] === 'string' && cuenta[campo].trim(),
    ),
  );
  test.skip(!validas, 'Falta personalRoles con usuario, contrasena, nombre y rol en ' + CUENTAS);
}

async function nuevaSesion(page) {
  await page.goto('about:blank');
  await page.context().clearCookies();
}

async function abrirLogin(page) {
  const respuesta = await page.goto(BASE);
  expect(respuesta, 'Apache debe responder').not.toBeNull();
  expect(respuesta.status(), 'La portada debe abrir sin error HTTP').toBeLessThan(400);
  await expect(page.locator('#loader')).toBeHidden({ timeout: 15000 });
  await page.locator('.cabecera-ingresar').click();
  await expect(page.locator('#exampleModal')).toBeVisible();
  // Esperar a que termine la animación del modal y se enfoque el usuario.
  // Así se evita que el llenado de la contraseña se desvíe al campo Usuario.
  await expect(page.locator('#username')).toBeFocused();
}

async function enviar(page, usuario, contrasena) {
  const formulario = page.locator('#login-form');
  const campoUsuario = formulario.locator('input[name="username"]');
  const campoContrasena = formulario.locator('input[name="password"]');
  await campoUsuario.fill(usuario);
  await campoContrasena.fill(contrasena);
  await expect(campoUsuario).toHaveValue(usuario);
  await expect(campoContrasena).toHaveValue(contrasena);
  await formulario.locator('button[type="submit"]').click();
}

async function evidencia(page, nombre) {
  const archivo = test.info().outputPath(nombre + '.png');
  await page.screenshot({
    path: archivo, fullPage: false,
    mask: [page.locator('#password'), page.locator('#username')],
  });
  await test.info().attach(nombre, { path: archivo, contentType: 'image/png' });
}

async function entrar(page, clave, panel, rol, sacramento = '') {
  await entrarConCuenta(page, usuarios[clave], panel, rol, sacramento);
}

async function entrarConCuenta(page, cuenta, panel, rol, sacramento = '') {
  await nuevaSesion(page);
  await abrirLogin(page);
  await enviar(page, cuenta.usuario, cuenta.contrasena);
  await expect(page).toHaveURL(url(paneles[panel]), { timeout: 15000 });
  await expect(page.locator('#loader')).toBeHidden({ timeout: 15000 });
  await page.locator('#profileDropdown').click();
  const menu = page.locator('.dropdown-menu[aria-labelledby="profileDropdown"]');
  await expect(menu).toBeVisible();
  await expect(menu.locator('a.h6').first()).toHaveText(cuenta.nombre);
  await expect(menu.locator('p.small').first()).toHaveText(rol);
  if (sacramento) {
    await expect(page.getByRole('heading', { name: 'Estado Actual de ' + sacramento, exact: true })).toBeVisible();
  }
}

// Comprobar la respuesta del servidor también evita aprobar una página que
// expone datos y recién después redirige mediante JavaScript.
async function accesoDenegado(page, ruta, permitidas = []) {
  const respuesta = await page.request.get(url(ruta), { maxRedirects: 0 });
  const estado = respuesta.status();
  const destino = respuesta.headers().location || '';
  await test.info().attach('Acceso ' + ruta, {
    body: Buffer.from(JSON.stringify({ ruta, estado, destino }, null, 2)),
    contentType: 'application/json',
  });
  expect([301, 302, 303, 307, 308, 401, 403], 'La ruta privada debe denegar el acceso: ' + ruta).toContain(estado);
  if (estado < 400) {
    expect(destino, 'La redirección debe indicar su destino').not.toBe('');
    const objetivo = new URL(destino, url(ruta)).href;
    expect(
      publica(objetivo) || objetivo === url('logout.php') || permitidas.some(p => objetivo === url(p)),
      'La redirección no debe abrir otro panel sin autorización',
    ).toBe(true);
  }
}

async function siguePublico(page) {
  await expect.poll(() => publica(page.url()), { timeout: 15000 }).toBe(true);
  await expect(page.locator('#profileDropdown')).toHaveCount(0);
}

test('A01 - Abrir el formulario de inicio de sesión', async ({ page }) => {
  await abrirLogin(page);
  await expect(page.locator('#exampleModalLabel')).toHaveText('INICIAR SESIÓN');
  await expect(page.locator('#username')).toBeVisible();
  await expect(page.locator('#password')).toBeVisible();
  await expect(page.locator('#password')).toHaveAttribute('type', 'password');
  await expect(page.locator('#login-form button[type="submit"]')).toHaveText('Ingresar');
  await evidencia(page, 'A01-formulario');
});

test('A02 - Rechazar campos vacíos o incompletos', async ({ page }) => {
  for (const [nombre, usuario, contrasena] of [
    ['vacios', '', ''], ['solo-usuario', 'prueba_incompleta', ''], ['solo-contrasena', '', 'prueba_incompleta'],
  ]) {
    await test.step(nombre, async () => {
      await nuevaSesion(page);
      await abrirLogin(page);
      let solicitudes = 0;
      const observar = peticion => {
        if (peticion.method() === 'POST' && peticion.url().includes('controladorLogin.php')) solicitudes++;
      };
      page.on('request', observar);
      try {
        await enviar(page, usuario, contrasena);
        await expect(page.locator('#exampleModal')).toBeVisible();
        expect(await page.locator('#login-form').evaluate(formulario => formulario.checkValidity())).toBe(false);
        expect(solicitudes, 'El formulario incompleto no debe enviarse').toBe(0);
        await siguePublico(page);
        for (const ruta of Object.values(paneles)) await accesoDenegado(page, ruta);
        await evidencia(page, 'A02-' + nombre);
      } finally {
        page.off('request', observar);
      }
    });
  }
});

test('A03 - Rechazar credenciales incorrectas', async ({ page }) => {
  requerir('parroco');
  for (const [nombre, usuario, mensaje] of [
    ['usuario-inexistente', 'qa' + randomUUID().slice(0, 8), 'Usuario incorrecto.'],
    ['contrasena-incorrecta', usuarios.parroco.usuario, 'Contraseña incorrecta.'],
  ]) {
    await test.step(nombre, async () => {
      await nuevaSesion(page);
      await abrirLogin(page);
      // Mantener los datos dentro del tamaño admitido por los procedimientos.
      await enviar(page, usuario, '!' + randomUUID().slice(0, 8));
      const aviso = page.locator('#login-form').getByRole('alert');
      await expect(aviso).toBeVisible({ timeout: 15000 });
      await expect(aviso).toHaveText(mensaje);
      await siguePublico(page);
      await expect(page.locator('body')).not.toContainText(/Fatal error|Uncaught mysqli|SQLSTATE|Stack trace:/i);
      for (const ruta of Object.values(paneles)) await accesoDenegado(page, ruta);
      await evidencia(page, 'A03-' + nombre);
    });
  }
});

test('A04 - Acceder como personal autorizado', async ({ page }) => {
  requerirPersonalRoles();
  const roles = usuarios.personalRoles.map(cuenta => cuenta.rol);
  expect(new Set(roles).size, 'Cada variante de A04 debe representar un cargo distinto').toBe(roles.length);
  for (const cuenta of usuarios.personalRoles) {
    await test.step(cuenta.rol, async () => {
      await entrarConCuenta(page, cuenta, 'personal', cuenta.rol);
      const slug = cuenta.rol.toLowerCase().normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/g, '-');
      await evidencia(page, 'A04-' + slug);
    });
  }
});

test('A05 - Acceder como coordinador de cada sacramento', async ({ page }) => {
  requerir('coordinadorPrimera', 'coordinadorConfirmacion');
  for (const [clave, sacramento] of [
    ['coordinadorPrimera', 'Primera Comunión'], ['coordinadorConfirmacion', 'Confirmación'],
  ]) {
    await test.step(sacramento, async () => {
      await entrar(page, clave, 'coordinador', 'Coordinadores', sacramento);
      await evidencia(page, 'A05-' + clave);
    });
  }
});

test('A06 - Acceder como catequista o titular', async ({ page }) => {
  requerir('catequista', 'titular');
  for (const [clave, rol] of [['catequista', 'Catequista'], ['titular', 'Titular']]) {
    await test.step(rol, async () => {
      await entrar(page, clave, 'catequista', rol);
      await evidencia(page, 'A06-' + clave);
    });
  }
});

test('A07 - Acceder como catequisando', async ({ page }) => {
  requerir('catequisandoPrimera', 'catequisandoConfirmacion');
  for (const [clave, sacramento] of [
    ['catequisandoPrimera', 'Primera Comunión'], ['catequisandoConfirmacion', 'Confirmación'],
  ]) {
    await test.step(sacramento, async () => {
      await entrar(page, clave, 'catequisando', sacramento);
      await evidencia(page, 'A07-' + clave);
    });
  }
});

test('A08 - Impedir el acceso de una cuenta inactiva', async ({ page }) => {
  requerir('inactivo');
  // La cuenta debe existir y estar Inactiva antes de esta prueba.
  for (const ruta of Object.values(paneles)) {
    await test.step(ruta, async () => {
      await nuevaSesion(page);
      const respuesta = await page.request.post(url('Controlador/controladorLogin.php'), {
        form: { username: usuarios.inactivo.usuario, password: usuarios.inactivo.contrasena },
        headers: { Referer: BASE }, maxRedirects: 0,
      });
      expect(respuesta.status(), 'El ingreso no debe generar errores del servidor').toBeLessThan(500);
      await accesoDenegado(page, ruta);
    });
  }
  await nuevaSesion(page);
  await abrirLogin(page);
  await enviar(page, usuarios.inactivo.usuario, usuarios.inactivo.contrasena);
  // Esperar la respuesta al envío antes de evaluar la ubicación final.
  await expect(page.locator('#login-form').getByRole('alert')).toBeVisible({ timeout: 15000 });
  await siguePublico(page);
  await evidencia(page, 'A08-cuenta-inactiva');
});

test('A09 - Proteger páginas privadas sin sesión', async ({ page }) => {
  for (const [panel, ruta] of Object.entries(paneles)) {
    await test.step(panel, async () => {
      await nuevaSesion(page);
      await accesoDenegado(page, ruta);
      await page.goto(url(ruta));
      await siguePublico(page);
      await expect(page.locator('#loader')).toBeHidden({ timeout: 15000 });
      await evidencia(page, 'A09-' + panel);
    });
  }
});

test('A10 - Bloquear el acceso a un panel de otro rol', async ({ page }) => {
  requerir('catequista');
  await entrar(page, 'catequista', 'catequista', 'Catequista');
  await evidencia(page, 'A10-rol-autenticado');
  await accesoDenegado(page, paneles.coordinador, [paneles.catequista]);
  await page.goto(url(paneles.coordinador));
  await expect.poll(() => publica(page.url()) || page.url() === url(paneles.catequista)).toBe(true);
  await evidencia(page, 'A10-panel-bloqueado');
});

test('A11 - Cerrar sesión y verificar el bloqueo', async ({ page }) => {
  requerir('parroco');
  await entrar(page, 'parroco', 'personal', 'Párroco');
  await page.locator('.dropdown-menu[aria-labelledby="profileDropdown"] a[href="../logout.php"]').click();
  await siguePublico(page);
  await page.reload();
  await siguePublico(page);
  for (const ruta of Object.values(paneles)) await accesoDenegado(page, ruta);
  await page.goto(url(paneles.personal));
  await siguePublico(page);
  await expect(page.locator('#loader')).toBeHidden({ timeout: 15000 });
  await evidencia(page, 'A11-sesion-cerrada');
});
