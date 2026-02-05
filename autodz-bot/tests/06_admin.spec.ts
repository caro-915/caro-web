import { test, expect } from '@playwright/test';
import { login, baseUrl } from './_helpers';

test('Admin can access admin dashboard', async ({ page }) => {
  test.setTimeout(60000);

  await login(page, 'admin');

  const base = baseUrl();
  await page.goto(base + '/admin', { waitUntil: 'domcontentloaded' });

  await expect(page).toHaveURL(/\/admin\/?$/i);

  // Un élément unique de la page admin
  await expect(
    page.getByRole('heading', { name: /dashboard admin/i })
  ).toBeVisible({ timeout: 15000 });
});
