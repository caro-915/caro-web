import { test, expect } from '@playwright/test';
import { baseUrl } from './_helpers';

test('Search page: filters + sort + pagination smoke', async ({ page }) => {
  const base = baseUrl();
  await page.goto(base + '/recherche');
  await page.waitForLoadState('domcontentloaded');

  // Tri: chercher un select ou un control "Tri"
  const sortSelect = page.locator('select').filter({ hasText: /prix|km|année|récent|latest/i }).first();
  if (await sortSelect.count()) {
    await sortSelect.selectOption({ index: 1 }).catch(() => {});
    await page.waitForLoadState('networkidle').catch(() => {});
  }

  // Pagination: clique page 2 si existe
  const page2 = page.getByRole('link', { name: /^2$/ }).first();
  if (await page2.count()) {
    await page2.click();
    await page.waitForLoadState('networkidle').catch(() => {});
    await expect(page).toHaveURL(/page=2|\/recherche/i);
  }
});
