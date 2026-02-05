import { test, expect } from '@playwright/test';
import path from 'path';

test('Create an ad (annonce) with required fields + 1 image', async ({ page }) => {
  test.setTimeout(60000);

  const base = (process.env.BASE_URL || 'http://127.0.0.1:8000').replace(/\/$/, '');
  const email = process.env.ADMIN_EMAIL!;
  const password = process.env.ADMIN_PASSWORD!;

  // --- Login ---
  await page.goto(base + '/login');
  await page.fill('input[name="email"]', email);
  await page.fill('input[name="password"]', password);
  await page.click('button[type="submit"]');
  await page.waitForLoadState('networkidle');

  // --- Go to create ad ---
  await page.goto(base + '/annonces/create');
  await page.waitForLoadState('domcontentloaded');

  // 1) Vehicle type (Voiture)
  await page.getByRole('button', { name: /voiture/i }).first().click();

  // 2) Title
  await page.getByPlaceholder(/Renault Clio/i).fill('Test bot - annonce');

  // 3) Price (DA)
  await page.getByPlaceholder(/2500000/i).fill('2500000');

  // ---- SELECTS (robuste par index global) ----
  // Order observed: 0=Marque, 1=Modèle, 2=Carburant, 3=Boîte, then others...
  const selects = page.locator('select');
  await expect(selects.nth(0)).toBeVisible(); // Marque
  await selects.nth(0).selectOption({ index: 1 }); // ex: Audi

  // Modèle: attendre que ça charge (pas "Chargement...")
  await expect(selects.nth(1)).toBeVisible();
  await page.waitForTimeout(800); // laisse le temps au JS d'injecter les modèles
  // si toujours "Chargement...", on attend un peu plus
  const modelText = await selects.nth(1).inputValue().catch(() => '');
  await page.waitForTimeout(800);

  // choisir le 1er modèle dispo si possible
  await selects.nth(1).selectOption({ index: 1 }).catch(async () => {
    // certains cas: modèle vide => on ignore (mais ton backend le demande, donc mieux d'avoir un modèle)
  });

  // Carburant (obligatoire)
  await expect(selects.nth(2)).toBeVisible();
  await selects.nth(2).selectOption({ index: 1 }); // Essence

  // Boîte de vitesses (obligatoire)
  await expect(selects.nth(3)).toBeVisible();
  await selects.nth(3).selectOption({ index: 1 }); // Manuelle

  // Optional: Ville / Wilaya
  const ville = page.getByPlaceholder(/Alger/i);
  if (await ville.count()) {
    await ville.fill('Alger');
  }

  // Upload image
  const imagePath = path.resolve('tests/fixtures/car.jpg');
  const fileInput = page.locator('input[type="file"]').first();
  if (await fileInput.count()) {
    await fileInput.setInputFiles(imagePath);
  }

  // Submit
  await page.getByRole('button', { name: /Publier l'annonce/i }).click();
  await page.waitForLoadState('networkidle').catch(() => {});

  // If still on create => show validation errors
  if (/\/annonces\/create\/?$/i.test(page.url())) {
    const msgs = await page
      .locator('text=/obligatoire|required|erreur|invalid|veuillez|sélectionnez|Sélectionnez/i')
      .allTextContents();

    throw new Error(
      'Still on /annonces/create (validation likely failed). Messages found:\n' +
        (msgs.length ? msgs.join('\n') : '(no visible validation message found)')
    );
  }

  await expect(page).not.toHaveURL(/\/annonces\/create\/?$/i);
});
