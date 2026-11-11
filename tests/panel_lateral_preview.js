// Vista previa del rediseño del panel lateral (no modifica el sistema).
// Genera comparación clara y oscura con estados hover.
//
// Uso:
//   $env:NODE_PATH='C:\xampp\htdocs\MVCSanPedro\node_modules'
//   node tests/panel_lateral_preview.js

const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const BASE = 'http://localhost/MVCSanPedro';
const SALIDA = path.join(__dirname, '..', 'docs', 'capturas_panel_lateral');
const USUARIO = 'richard72717601';
const CLAVE = '12345parroco';

async function iniciarSesion(page) {
  await page.goto(`${BASE}/index.php`, { waitUntil: 'domcontentloaded' });
  await page.waitForTimeout(1200);
  await page.click('button[data-bs-target="#exampleModal"]');
  await page.waitForSelector('#username', { state: 'visible' });
  await page.fill('#username', USUARIO);
  await page.fill('#password', CLAVE);
  await Promise.all([
    page.waitForLoadState('domcontentloaded'),
    page.click('#login-form button[type="submit"]')
  ]);
}

async function capturarCard(page, nombre) {
  const destino = path.join(SALIDA, nombre);
  await page.locator('#offcanvasSideNavbar .card').first().screenshot({ path: destino });
  return destino;
}

(async () => {
  fs.mkdirSync(SALIDA, { recursive: true });

  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    viewport: { width: 1908, height: 1200 },
    deviceScaleFactor: 2
  });
  const page = await context.newPage();

  await iniciarSesion(page);
  await page.goto(`${BASE}/VistaPersonal/VistaDashboard.php`, { waitUntil: 'domcontentloaded' });
  await page.waitForSelector('#offcanvasSideNavbar .card');
  await page.waitForTimeout(700);

  // --- Antes ---
  const antes = await capturarCard(page, 'panel_lateral_actual.png');

  // Leer info actual del DOM
  const info = await page.evaluate(() => {
    const card = document.querySelector('#offcanvasSideNavbar .card');
    const cover = card.querySelector('.h-50px');
    const avatar = card.querySelector('img.avatar-img');
    const mapa = {
      'VistaDashboard.php': ['Dashboard', 'Principal'],
      'VistaPersonal.php': ['Inicio', 'Principal'],
      'VistaPersona.php': ['Persona', 'Gestión'],
      'VistaReservas.php': ['Celebración', 'Gestión'],
      'VistaGesPersonal.php': ['Personal', 'Gestión'],
      'VistaCoordinadorAdmin.php': ['Coordinadores', 'Gestión'],
      'VistaSolicitante.php': ['Certificado', 'Documentos'],
      'VistaEmitidos.php': ['Certificado Emitido', 'Documentos']
    };
    const items = [];
    Array.from(card.querySelectorAll('ul a')).forEach(a => {
      const href = (a.getAttribute('href') || '').split('/').pop();
      const cfg = mapa[href];
      if (!cfg) return;
      const img = a.querySelector('img');
      const ico = a.querySelector('i');
      let icon = '', tipo = 'img';
      if (img) icon = img.outerHTML;
      else if (ico) { icon = ico.outerHTML; tipo = 'bi'; }
      else { icon = '🔥'; tipo = 'flama'; }
      items.push({ label: cfg[0], grupo: cfg[1], icon, tipo, active: a.classList.contains('active') });
    });
    return {
      cover: cover ? getComputedStyle(cover).backgroundImage : '',
      avatar: avatar ? avatar.src : '',
      name: card.querySelector('h5')?.textContent.trim() || '',
      role: card.querySelector('small')?.textContent.trim() || '',
      phrase: card.querySelector('p')?.textContent.trim() || '',
      items
    };
  });

  // Aplicar nuevo diseño in-page
  await page.evaluate((info) => {
    const grupos = {};
    info.items.forEach(it => { (grupos[it.grupo] ||= []).push(it); });

    const itemHtml = (it) => {
      let icon = (it.tipo === 'flama')
        ? '<span class="side-icon side-flame" aria-hidden="true">🔥</span>'
        : `<span class="side-icon">${it.icon}</span>`;
      return `<a class="nav-link side-item${it.active ? ' active' : ''}" href="#">
        ${icon}
        <span class="side-txt">${it.label}</span>
        ${it.active ? '<span class="side-dot" aria-hidden="true"></span>' : ''}
      </a>`;
    };

    const nav = Object.keys(grupos).map(grupo => `
      <li class="nav-item side-label-item"><span class="side-label"><span class="side-label-bar" aria-hidden="true"></span>${grupo}</span></li>
      ${grupos[grupo].map(itemHtml).join('')}
    `).join('');

    const style = document.createElement('style');
    style.textContent = `
      #offcanvasSideNavbar .card {
        border: 0;
        border-radius: 20px;
        overflow: hidden;
        background: #ffffff;
        box-shadow: 0 22px 52px rgba(15, 23, 42, .22), 0 2px 6px rgba(15, 23, 42, .08);
      }
      #offcanvasSideNavbar .h-50px {
        position: relative;
        height: 104px !important;
      }
      #offcanvasSideNavbar .h-50px::before {
        content: "Parroquia San Pedro";
        position: absolute;
        top: 12px; left: 12px;
        padding: 5px 11px;
        border: 1px solid rgba(255,255,255,.36);
        border-radius: 999px;
        background: rgba(15,23,42,.52);
        color: #ffe9a8;
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .10em;
        text-transform: uppercase;
        backdrop-filter: blur(8px);
      }
      #offcanvasSideNavbar .h-50px::after {
        content: "";
        position: absolute;
        right: 0; bottom: 0; left: 0;
        height: 4px;
        background: linear-gradient(90deg, #d4a017, #f2cd6b 45%, #d4a017);
      }

      #offcanvasSideNavbar .card-body { padding-top: 0; }
      #offcanvasSideNavbar .avatar {
        position: relative;
        margin-top: -48px;
        margin-bottom: 14px;
      }
      #offcanvasSideNavbar .avatar img {
        width: 96px; height: 96px;
        object-fit: cover;
        border: 4px solid #fff !important;
        border-radius: 50%;
        box-shadow: 0 0 0 3px #d4a017, 0 10px 22px rgba(15,23,42,.22);
      }
      #offcanvasSideNavbar .avatar::after {
        content: "";
        position: absolute;
        right: 2px; bottom: 6px;
        width: 17px; height: 17px;
        border: 3px solid #fff;
        border-radius: 50%;
        background: #198754;
      }

      /* LETRAS MÁS GRANDES + aura de contraste clara */
      #offcanvasSideNavbar .card-body h5 a {
        color: #0b0f14;
        font-size: 1.55rem;
        font-weight: 800;
        line-height: 1.1;
        text-decoration: none;
        text-shadow:
          0 1px 0 #fff, 0 -1px 0 #fff, 1px 0 0 #fff, -1px 0 0 #fff,
          0 3px 8px rgba(15, 23, 42, .30);
      }
      #offcanvasSideNavbar .card-body small {
        display: inline-block;
        color: #0b0f14;
        font-size: .82rem;
        font-weight: 800;
        letter-spacing: .13em;
        text-transform: uppercase;
        text-shadow:
          0 1px 0 #fff, 0 -1px 0 #fff, 1px 0 0 #fff, -1px 0 0 #fff,
          0 2px 6px rgba(15, 23, 42, .24);
      }
      #offcanvasSideNavbar .card-body small::after {
        content: "Activo";
        display: inline-flex;
        align-items: center;
        gap: 5px;
        margin-left: 8px;
        padding: 2px 8px;
        border: 1px solid #b7e4c7;
        border-radius: 999px;
        background: #e9f7ef;
        color: #157347;
        font-size: .62rem;
        font-weight: 800;
        letter-spacing: .02em;
        text-transform: none;
        text-shadow: none;
      }
      #offcanvasSideNavbar .card-body p {
        margin: 10px 0 0;
        color: #1c2530;
        font-size: .92rem;
        font-style: italic;
        font-weight: 600;
        line-height: 1.35;
        text-shadow: 0 1px 0 #fff, 1px 0 0 #fff, -1px 0 0 #fff, 0 2px 5px rgba(15,23,42,.18);
      }
      #offcanvasSideNavbar .card-body hr {
        margin: 14px 0 4px;
        border: 0;
        border-top: 1px solid #e8ebef;
        opacity: 1;
      }

      /* Navegación */
      #offcanvasSideNavbar .side-nav { padding: 0 10px 12px; }
      #offcanvasSideNavbar .side-label-item { margin-top: 12px; }
      #offcanvasSideNavbar .side-label {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0 8px 5px;
        color: #0b0f14;
        font-size: .82rem;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
        text-shadow: 0 1px 0 #fff, 1px 0 0 #fff, -1px 0 0 #fff, 0 2px 5px rgba(15,23,42,.20);
      }
      #offcanvasSideNavbar .side-label-bar {
        width: 16px; height: 3px;
        border-radius: 999px;
        background: linear-gradient(90deg, #d4a017, #f2cd6b);
      }

      #offcanvasSideNavbar .side-item {
        position: relative;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 10px;
        border-radius: 12px;
        color: #0b0f14;
        font-size: 1.08rem;
        font-weight: 800;
        text-decoration: none;
        transition: background .16s ease, transform .16s ease, box-shadow .16s ease;
      }
      #offcanvasSideNavbar .side-item + .side-item { margin-top: 2px; }
      #offcanvasSideNavbar .side-item .side-txt {
        text-shadow: 0 1px 0 #fff, 1px 0 0 #fff, -1px 0 0 #fff, 0 2px 5px rgba(15,23,42,.18);
      }
      #offcanvasSideNavbar .side-item:hover,
      #offcanvasSideNavbar .side-item:focus-visible {
        background: #f4f7fb;
        transform: translateX(2px);
        outline: none;
      }
      #offcanvasSideNavbar .side-item:hover .side-txt,
      #offcanvasSideNavbar .side-item:focus-visible .side-txt {
        text-shadow:
          0 1px 0 #fff, 0 -1px 0 #fff, 1px 0 0 #fff, -1px 0 0 #fff,
          0 0 2px #fff, 0 4px 10px rgba(15, 23, 42, .34);
      }
      #offcanvasSideNavbar .side-item.active {
        background: #eef4fd;
        box-shadow: inset 4px 0 0 #d4a017, 0 6px 16px rgba(15, 76, 153, .12);
      }
      #offcanvasSideNavbar .side-dot {
        margin-left: auto;
        width: 7px; height: 7px;
        border-radius: 50%;
        background: #d4a017;
      }

      #offcanvasSideNavbar .side-icon {
        width: 38px; height: 38px;
        flex: 0 0 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e6eaef;
        border-radius: 11px;
        background: #f6f8fa;
        box-shadow: 0 1px 2px rgba(15,23,42,.06);
      }
      #offcanvasSideNavbar .side-icon img { width: 24px; height: 24px; object-fit: contain; }
      #offcanvasSideNavbar .side-icon.bi { font-size: 1.1rem; color: #334155; }
      #offcanvasSideNavbar .side-item.active .side-icon {
        border-color: #e9c46a;
        background: linear-gradient(140deg, #fdf3d6, #f7e3ab);
        box-shadow: 0 4px 10px rgba(212, 160, 23, .22);
      }

      #offcanvasSideNavbar .side-flame {
        border: 1px solid #e9c46a;
        border-radius: 50%;
        background: radial-gradient(circle at 32% 28%, #fff6d8, #f4dd9a 62%, #e8c264);
        box-shadow: 0 3px 8px rgba(212, 160, 23, .28), inset 0 1px 0 rgba(255,255,255,.85);
        font-size: 1.1rem;
        line-height: 1;
      }

      #offcanvasSideNavbar .card-footer {
        padding: 12px 14px 8px;
        border-top: 1px solid #e8ebef;
        background: #fbfcfd;
      }
      #offcanvasSideNavbar .card-footer .btn-link {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #dbe7f7;
        border-radius: 10px;
        background: linear-gradient(90deg, #eef4fd, #f8fafc);
        color: #0b0f14;
        font-size: .92rem;
        font-weight: 800;
        text-decoration: none;
        text-shadow: 0 1px 0 #fff, 0 2px 5px rgba(15,23,42,.16);
      }
      #offcanvasSideNavbar .card-footer .btn-link:hover {
        background: linear-gradient(90deg, #e3edfb, #f1f5f9);
      }

      /* ===== MODO OSCURO ===== */
      [data-bs-theme="dark"] #offcanvasSideNavbar .card { background: #161d26; }
      [data-bs-theme="dark"] #offcanvasSideNavbar .card-body h5 a,
      [data-bs-theme="dark"] #offcanvasSideNavbar .card-body small,
      [data-bs-theme="dark"] #offcanvasSideNavbar .card-body p,
      [data-bs-theme="dark"] #offcanvasSideNavbar .side-label,
      [data-bs-theme="dark"] #offcanvasSideNavbar .side-item .side-txt {
        color: #f3f6fa;
        text-shadow: 0 1px 0 rgba(0,0,0,.55), 0 3px 8px rgba(0,0,0,.45);
      }
      [data-bs-theme="dark"] #offcanvasSideNavbar .card-body small::after {
        background: #1b3a2f;
        border-color: #2d6a4f;
        color: #74c69d;
      }
      [data-bs-theme="dark"] #offcanvasSideNavbar .card-body hr,
      [data-bs-theme="dark"] #offcanvasSideNavbar .card-footer {
        border-color: #2c3846;
      }
      [data-bs-theme="dark"] #offcanvasSideNavbar .side-icon {
        background: #1f2937;
        border-color: #2c3846;
      }
      [data-bs-theme="dark"] #offcanvasSideNavbar .side-icon.bi { color: #f3f6fa; }
      [data-bs-theme="dark"] #offcanvasSideNavbar .side-item.active {
        background: #1b2a3d;
        box-shadow: inset 4px 0 0 #d4a017, 0 6px 16px rgba(0,0,0,.25);
      }
      [data-bs-theme="dark"] #offcanvasSideNavbar .side-item.active .side-icon {
        background: linear-gradient(140deg, #5c440a, #3d2d05);
        border-color: #d4a017;
      }
      [data-bs-theme="dark"] #offcanvasSideNavbar .side-item:hover,
      [data-bs-theme="dark"] #offcanvasSideNavbar .side-item:focus-visible {
        background: linear-gradient(90deg, #c99818, #d4a017, #c99818);
        transform: translateX(2px);
      }
      [data-bs-theme="dark"] #offcanvasSideNavbar .side-item:hover .side-txt,
      [data-bs-theme="dark"] #offcanvasSideNavbar .side-item:focus-visible .side-txt,
      [data-bs-theme="dark"] #offcanvasSideNavbar .side-item:hover .side-icon.bi,
      [data-bs-theme="dark"] #offcanvasSideNavbar .side-item:hover .side-icon img {
        color: #ffffff;
        text-shadow: none;
      }
      [data-bs-theme="dark"] #offcanvasSideNavbar .side-item:hover .side-icon {
        background: rgba(255,255,255,.20);
        border-color: rgba(255,255,255,.45);
      }
      [data-bs-theme="dark"] #offcanvasSideNavbar .side-flame {
        background: radial-gradient(circle at 32% 28%, #6b4c00, #d4a017 62%, #f2cd6b);
        border-color: #f2cd6b;
        box-shadow: 0 4px 12px rgba(212, 160, 23, .45), inset 0 1px 0 rgba(255,255,255,.4);
      }
      [data-bs-theme="dark"] #offcanvasSideNavbar .card-footer .btn-link {
        background: linear-gradient(90deg, #1b2a3d, #162233);
        border-color: #2c4a6e;
        color: #f3f6fa;
      }
      [data-bs-theme="dark"] #offcanvasSideNavbar .card-footer .btn-link:hover {
        background: linear-gradient(90deg, #223a52, #1b2f45);
      }
    `;
    document.head.appendChild(style);

    const card = document.querySelector('#offcanvasSideNavbar .card');
    card.className = 'card overflow-hidden';
    card.innerHTML = `
      <div class="h-50px" style="background-image:${info.cover}; background-position:center; background-size:cover; background-repeat:no-repeat;"></div>
      <div class="card-body pt-0">
        <div class="text-center">
          <div class="avatar avatar-lg mt-n5 mb-3">
            <img class="avatar-img rounded border border-white border-3" src="${info.avatar}" alt="">
          </div>
          <h5 class="mb-0"><a href="PerfilAdmin.php">${info.name}</a></h5>
          <small>${info.role}</small>
          <p class="mt-3">${info.phrase}</p>
        </div>
        <hr>
        <ul class="nav nav-link-secondary flex-column fw-bold side-nav">${nav}</ul>
      </div>
      <div class="card-footer text-center py-2">
        <a class="btn btn-link btn-sm" href="PerfilAdmin.php"><i class="bi bi-person-circle"></i> Ver mi Perfil</a>
      </div>
    `;
  }, info);

  await page.waitForTimeout(400);

  // --- Clara normal ---
  const clara = await capturarCard(page, 'panel_lateral_clara.png');

  // --- Clara hover ---
  await page.hover('#offcanvasSideNavbar .side-nav .side-item:not(.active)');
  await page.waitForTimeout(250);
  const claraHover = await capturarCard(page, 'panel_lateral_clara_hover.png');

  // --- Oscura normal ---
  await page.evaluate(() => {
    document.documentElement.setAttribute('data-bs-theme', 'dark');
  });
  await page.waitForTimeout(350);
  const oscura = await capturarCard(page, 'panel_lateral_oscura.png');

  // --- Oscura hover (fondo dorado, letras blancas) ---
  await page.hover('#offcanvasSideNavbar .side-nav .side-item:not(.active)');
  await page.waitForTimeout(250);
  const oscuraHover = await capturarCard(page, 'panel_lateral_oscura_hover.png');

  // --- Composición final ---
  const b64 = (p) => `data:image/png;base64,${fs.readFileSync(p).toString('base64')}`;
  const final = path.join(SALIDA, 'panel_lateral_final.png');

  const html = `<!doctype html><html><head><meta charset="utf-8"><style>
    *{box-sizing:border-box}
    body{margin:0;padding:30px 34px 34px;background:#f4f6f9;font-family:Segoe UI,Arial,sans-serif;width:max-content}
    h1{margin:0 0 4px;font-size:24px;color:#0f172a}
    p.sub{margin:0 0 18px;font-size:13px;color:#64707d}
    .row{display:flex;gap:28px;align-items:flex-start}
    .col{background:#fff;border:1px solid #dfe4ea;padding:14px;border-radius:10px}
    .tag{display:inline-block;margin-bottom:9px;padding:5px 11px;border-radius:6px;color:#fff;font-size:12px;font-weight:700}
    .tag.a{background:#64707d}.tag.b{background:#1f4f8f}.tag.c{background:#d4a017}.tag.d{background:#111827}
    img{display:block;height:1080px;width:auto}
  </style></head><body>
    <h1>Panel lateral — Propuesta final</h1>
    <p class="sub">Letras más grandes, aura de contraste clara. En modo oscuro el hover pasa a fondo dorado con letras blancas. Coordinadores adaptados al modo oscuro.</p>
    <div class="row">
      <div class="col"><span class="tag a">ACTUAL</span><img src="${b64(antes)}"></div>
      <div class="col"><span class="tag b">CLARA</span><img src="${b64(clara)}"></div>
      <div class="col"><span class="tag c">OSCURA</span><img src="${b64(oscura)}"></div>
      <div class="col"><span class="tag d">OSCURA + HOVER</span><img src="${b64(oscuraHover)}"></div>
    </div>
  </body></html>`;

  const pageFinal = await context.newPage();
  await pageFinal.setViewportSize({ width: 2000, height: 1250 });
  await pageFinal.setContent(html, { waitUntil: 'load' });
  await pageFinal.screenshot({ path: final, fullPage: true });

  await browser.close();
  console.log(JSON.stringify({ antes, clara, claraHover, oscura, oscuraHover, final }, null, 2));
})().catch(err => { console.error(err); process.exit(1); });
