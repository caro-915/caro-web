import { test, expect } from '@playwright/test';
import { login } from './_helpers';

test('Admin can login', async ({ page }) => {
  await login(page, 'admin');
  await expect(page.getByText(/test|admin|tableau|dashboard|déposer/i).first()).toBeVisible();
});
