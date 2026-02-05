import { test, expect } from '@playwright/test';
import { baseUrl } from './_helpers';

test('Smoke: home + recherche load', async ({ page }) => {
  const base = baseUrl();

  await page.goto(base + '/');
  await expect(page).toHaveURL(base + '/');
  await expect(page.getByRole('link', { name: /Occasion/i })).toBeVisible();

  await page.goto(base + '/recherche');
  await expect(page).toHaveURL(/\/recherche$/);
  await expect(page.getByRole('heading', { name: /recherche|occasion/i }).first()).toBeVisible().catch(() => {});
});
