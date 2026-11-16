import AxeBuilder from '@axe-core/playwright';
import { expect, test, type Page } from '@playwright/test';

// Panneau « Compte administrateur ». Nécessite une base jetable et un administrateur de test :
// E2E_ADMIN_EMAIL et E2E_ADMIN_PASSWORD (jamais de vrais identifiants). Aucun test ne change le mot de passe.
const email = process.env.E2E_ADMIN_EMAIL;
const password = process.env.E2E_ADMIN_PASSWORD;

test.skip(!email || !password, 'E2E_ADMIN_EMAIL / E2E_ADMIN_PASSWORD requis (base locale jetable).');

// Valeur factice générée à l'exécution (jamais enregistrée : le mot de passe actuel est volontairement faux).
const fake = `Zx${Date.now()}#aB`;
const wcag = ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa'];
const viewports = { desktop: { width: 1440, height: 1000 }, mobile: { width: 390, height: 844 } } as const;

// Une seule connexion pour tout le fichier : la route de connexion est limitée à 5 essais par minute.
const storageState = 'storage/playwright-auth/admin-account.json';

test.beforeAll(async ({ browser }) => {
    const context = await browser.newContext({ storageState: undefined, locale: 'fr-FR' });
    const page = await context.newPage();
    await page.goto('/login');
    await page.getByLabel('Adresse e-mail').fill(email ?? '');
    await page.getByLabel('Mot de passe').fill(password ?? '');
    await page.getByRole('button', { name: /connexion|se connecter/i }).click();
    await page.waitForURL('**/dashboard');
    await context.storageState({ path: storageState });
    await context.close();
});

test.use({ storageState, locale: 'fr-FR' });

async function login(page: Page): Promise<void> {
    await page.goto('/dashboard');
}

async function openAccount(page: Page): Promise<void> {
    await page.goto('/dashboard/account');
    await page.evaluate(() => window.localStorage.removeItem('admin-sections:account'));
    await page.reload();
    await expect(page.getByRole('heading', { level: 1, name: 'Compte administrateur' })).toBeVisible();
}

for (const [name, viewport] of Object.entries(viewports)) {
    for (const theme of ['light', 'dark'] as const) {
        test(`axe WCAG A/AA — compte administrateur — ${name} ${theme}`, async ({ browser }) => {
            const context = await browser.newContext({ viewport, storageState, locale: 'fr-FR' });
            await context.addInitScript((value) => {
                try {
                    localStorage.setItem('portfolio-theme', value);
                } catch {
                    /* stockage indisponible */
                }
            }, theme);
            const page = await context.newPage();
            await login(page);
            await openAccount(page);

            for (const state of ['repliées', 'dépliées']) {
                await page.getByRole('button', { name: state === 'repliées' ? 'Tout replier' : 'Tout déplier' }).click();
                const results = await new AxeBuilder({ page }).withTags(wcag).analyze();
                expect(results.violations.map((v) => `${v.id}: ${v.nodes.map((n) => n.target.join(' ')).join(' | ')}`), state).toEqual([]);
            }
            const overflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
            expect(overflow, 'débordement horizontal').toBeLessThanOrEqual(0);
            await context.close();
        });
    }
}

test('mobile 390 px : boutons et champs de 44 px minimum, pas de débordement', async ({ browser }) => {
    const context = await browser.newContext({ viewport: viewports.mobile, storageState, locale: 'fr-FR' });
    const page = await context.newPage();
    await login(page);
    await openAccount(page);
    await page.getByRole('button', { name: 'Tout déplier' }).click();

    const small = await page.evaluate(() =>
        [...document.querySelectorAll<HTMLElement>('main button, main input')]
            .filter((el) => el.offsetParent !== null && !el.closest('[inert]'))
            .map((el) => ({ label: el.textContent?.trim() || el.id, height: el.getBoundingClientRect().height }))
            .filter((item) => item.height < 43.5),
    );
    expect(small).toEqual([]);
    await context.close();
});

test('indicateur de robustesse : critères et niveau suivent la saisie', async ({ page }) => {
    await login(page);
    await openAccount(page);
    await page.getByRole('button', { name: /^Mot de passe/ }).click();

    const field = page.getByLabel('Nouveau mot de passe', { exact: true });
    await expect(page.getByText('Faible')).toHaveCount(0);
    await field.fill('abc');
    await expect(page.getByText('Faible', { exact: true })).toBeVisible();
    await field.fill('Abcdefghij1');
    await expect(page.getByText('Moyenne', { exact: true })).toBeVisible();
    await field.fill('Abcdefghij1!');
    await expect(page.getByText('Forte', { exact: true })).toBeVisible();
    await expect(page.getByRole('list', { name: 'Critères' }).getByRole('listitem').filter({ hasText: 'respecté' })).toHaveCount(5);
});

test('mauvais mot de passe actuel : erreur sans fuite, section ouverte, aucun changement', async ({ page }) => {
    await login(page);
    await openAccount(page);
    await page.getByRole('button', { name: /^Mot de passe/ }).click();

    await page.getByLabel('Mot de passe actuel').fill('not-the-password');
    await page.getByLabel('Nouveau mot de passe', { exact: true }).fill(fake);
    await page.getByLabel('Confirmer le nouveau mot de passe').fill(fake);
    await page.getByRole('button', { name: 'Changer le mot de passe' }).click();

    await expect(page.getByText('Le mot de passe actuel est incorrect.')).toBeVisible();
    await expect(page.getByLabel('Mot de passe actuel')).toHaveAttribute('aria-invalid', 'true');
    // Les champs de mot de passe sont vidés après l'envoi.
    await expect(page.getByLabel('Nouveau mot de passe', { exact: true })).toHaveValue('');
});

test('changer l’adresse e-mail fait apparaître la demande du mot de passe actuel', async ({ page }) => {
    await login(page);
    await openAccount(page);

    await expect(page.getByLabel(/^Mot de passe actuel \(requis/)).toHaveCount(0);
    await page.getByLabel('Adresse e-mail de connexion').fill('autre@example.test');
    await expect(page.getByLabel(/^Mot de passe actuel \(requis/)).toBeVisible();
});

test('sessions : cet appareil est repéré, déconnexion des autres avec confirmation annulable', async ({ page }) => {
    await login(page);
    await openAccount(page);
    await page.getByRole('button', { name: /^Sessions et appareils/ }).click();

    await expect(page.getByText('Cet appareil')).toBeVisible();
    await page.getByRole('button', { name: 'Déconnecter toutes les autres' }).click();
    const dialog = page.getByRole('dialog');
    await expect(dialog).toBeVisible();
    await expect(dialog.getByRole('button', { name: 'Annuler' })).toBeFocused();
    await page.keyboard.press('Escape');
    await expect(dialog).toBeHidden();
});

test('journal de connexion : la dernière connexion réussie est listée, adresse IP masquée', async ({ page }) => {
    await login(page);
    await openAccount(page);
    await page.getByRole('button', { name: /^Sécurité/ }).click();

    await expect(page.getByText('Réussie').first()).toBeVisible();
    const text = await page.locator('main').innerText();
    expect(text).not.toMatch(/\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b/);
});
