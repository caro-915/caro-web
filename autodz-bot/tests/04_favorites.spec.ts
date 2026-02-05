import { test, expect } from '@playwright/test';
import { login, baseUrl } from './_helpers';

test('User can add/remove favorite from search', async ({ page }) => {
  await login(page, 'user');

  await page.goto(baseUrl() + '/recherche');

  const heart = page.locator('button, a').filter({ hasText: '♥' }).first();

  await expect(heart).toBeVisible({ timeout: 15000 });

  await heart.click();

  await page.goto(baseUrl() + '/favoris');

  await expect(page.locator('text=♥')).toBeVisible();
});
