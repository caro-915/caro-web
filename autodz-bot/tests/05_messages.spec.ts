import { test, expect } from '@playwright/test';
import { login, baseUrl } from './_helpers';

test('User can access messages', async ({ page }) => {
  await login(page, 'user');

  await page.goto(baseUrl() + '/messages');

  await expect(page).not.toHaveURL(/login/i);

  const convo = page.locator('a, div').filter({ hasText: /conversation|message/i }).first();

  if (await convo.count()) {
    await convo.click();
    await expect(page.locator('textarea, input')).toBeVisible();
  }
});
