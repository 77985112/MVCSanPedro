
import { test, expect } from '@playwright/test';

test('Prueba 1 - Acceso al sistema parroquial', async ({ page }) => {

  // Paso 1: Abrir el sistema web
  const respuesta = await page.goto('http://localhost/MVCSanPedro/');

  // Paso 2: Comprobar que el servidor responde correctamente
  expect(respuesta).not.toBeNull();
  expect(respuesta.status()).toBeLessThan(400);

  // Paso 3: Verificar que la página es visible
  await expect(page.locator('body')).toBeVisible();

  // Paso 4: Verificar que la página tiene contenido
  const contenido = await page.locator('body').innerText();
  expect(contenido.trim().length).toBeGreaterThan(0);

});
