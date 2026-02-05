import { expect, type Page } from '@playwright/test';

export function baseUrl() {
  return (process.env.BASE_URL || 'http://127.0.0.1:8000').replace(/\/$/, '');
}

function creds(role: 'user' | 'admin') {
  const email = role === 'admin' ? process.env.ADMIN_EMAIL : process.env.USER_EMAIL;
  const password = role === 'admin' ? process.env.ADMIN_PASSWORD : process.env.USER_PASSWORD;

  if (!email || !password) {
    throw new Error(
      `[ENV] Missing ${role.toUpperCase()} credentials. Set ` +
      (role === 'admin'
        ? 'ADMIN_EMAIL & ADMIN_PASSWORD'
        : 'USER_EMAIL & USER_PASSWORD') +
      ` in tests/.env`
    );
  }

  return { email, password };
}

async function readLoginErrors(page: Page) {
  // Laravel/Breeze/Tailwind: erreurs souvent dans des classes rouges ou role=alert
  const candidates = page.locator(
    '[role="alert"], .text-red-500, .text-red-600, .text-red-700, .alert, .invalid-feedback'
  );

  const texts = (await candidates.allTextContents())
    .map(t => t.trim())
    .filter(Boolean);

  // Fallback: parfois un message global dans la page
  const bodyText = (await page.locator('body').innerText()).toLowerCase();

  const hints: string[] = [];
  if (bodyText.includes('these credentials') || bodyText.includes('identifiants')) {
    hints.push('→ Hint: identifiants incorrects (email/mdp).');
  }
  if (bodyText.includes('too many') || bodyText.includes('trop de tentatives')) {
    hints.push('→ Hint: throttle / trop de tentatives, attends 60s ou reset le throttle.');
  }
  if (bodyText.includes('csrf') || bodyText.includes('token')) {
    hints.push('→ Hint: problème CSRF/token.');
  }

  return { texts, hints };
}

export async function login(page: Page, role: 'user' | 'admin' = 'user') {
  const base = baseUrl();
  const { email, password } = creds(role);

  console.log(`[login] role=${role} email=${email} passwordLen=${password.length}`);

  await page.goto(base + '/login', { waitUntil: 'domcontentloaded' });

  await page.locator('input[name="email"]').fill(email);
  await page.locator('input[name="password"]').fill(password);

  await Promise.all([
    page.waitForNavigation({ waitUntil: 'networkidle' }).catch(() => null),
    page.locator('button[type="submit"]').click(),
  ]);

  // Si on reste sur /login => on lit les erreurs et on fail avec détail
  const url = page.url();
  if (/\/login/i.test(url)) {
    const { texts, hints } = await readLoginErrors(page);

    const msg =
      `Login failed: still on /login\n` +
      (texts.length ? `Messages visibles:\n- ${texts.join('\n- ')}\n` : `Aucun message visible détecté.\n`) +
      (hints.length ? hints.join('\n') + '\n' : '');

    throw new Error(msg);
  }

  // preuve minimale qu'on est loggé (menu user)
  await expect(
    page.getByRole('button', { name: /👤|mon compte|admin|test2/i }).first()
  ).toBeVisible({ timeout: 15000 });
}
