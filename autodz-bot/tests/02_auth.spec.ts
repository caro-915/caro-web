import { test, expect } from '@playwright/test';
import { login, baseUrl } from './_helpers';

test('User can login and access protected pages', async ({ page }) => {
  await login(page, 'user');

  await page.goto(baseUrl() + '/mes-annonces');
  await expect(page).not.toHaveURL(/login/i);

  await page.goto(baseUrl() + '/favoris');
  await expect(page).not.toHaveURL(/login/i);

  await page.goto(baseUrl() + '/messages');
  await expect(page).not.toHaveURL(/login/i);
});
