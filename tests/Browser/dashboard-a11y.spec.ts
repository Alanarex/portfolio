import AxeBuilder from '@axe-core/playwright';
import { expect, test, type Page } from '@playwright/test';

// Nécessite un serveur de prévisualisation avec une base jetable et un administrateur de test :
// E2E_ADMIN_EMAIL et E2E_ADMIN_PASSWORD (jamais de vrais identifiants).
const email = process.env.E2E_ADMIN_EMAIL;
const password = process.env.E2E_ADMIN_PASSWORD;

test.skip(!email || !password, 'E2E_ADMIN_EMAIL / E2E_ADMIN_PASSWORD requis (base locale jetable).');

const viewports = {
    desktop: { width: 1440, height: 1000 },
    mobile: { width: 375, height: 812 },
} as const;
const themes = ['light', 'dark'] as const;

async function login(page: Page): Promise<void> {
    await page.goto('/login');
    await page.getByLabel('Adresse e-mail').fill(email ?? '');
    await page.getByLabel('Mot de passe').fill(password ?? '');
    await page.getByRole('button', { name: /connexion|se connecter/i }).click();
    await page.waitForURL('**/dashboard');
}

async function firstProjectId(page: Page): Promise<string> {
    await page.goto('/dashboard/projects');
    const href = await page.locator('a[href$="/edit"]').first().getAttribute('href');
    return /projects\/(\d+)\/edit/.exec(href ?? '')?.[1] ?? '1';
}

for (const [viewportName, viewport] of Object.entries(viewports)) {
    for (const theme of themes) {
        test(`axe WCAG A/AA — dashboard — ${viewportName} ${theme}`, async ({ browser }) => {
            test.setTimeout(120_000);
            const context = await browser.newContext({ viewport });
            await context.addInitScript((value) => {
                try {
                    localStorage.setItem('portfolio-theme', value);
                } catch {
                    /* stockage indisponible */
                }
            }, theme);
            const page = await context.newPage();
            await login(page);
            const projectId = await firstProjectId(page);

            const paths = [
                '/dashboard',
                '/dashboard/profile',
                '/dashboard/career',
                '/dashboard/skills',
                '/dashboard/projects',
                '/dashboard/projects/create',
                `/dashboard/projects/${projectId}/edit`,
                `/dashboard/projects/${projectId}/preview`,
                '/dashboard/posts',
                '/dashboard/settings',
                '/dashboard/scene',
                '/dashboard/account',
            ];

            for (const path of paths) {
                await page.goto(path);
                await expect(page.locator('h1')).toBeVisible();
                await expect(page.locator('html')).toHaveAttribute('data-theme', theme);

                const results = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa']).analyze();
                expect(results.violations.map((v) => `${v.id}: ${v.nodes.map((n) => n.target.join(' ')).join(' | ')}`), path).toEqual([]);

                const horizontalOverflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
                expect(horizontalOverflow, `${path} déborde horizontalement`).toBeLessThanOrEqual(0);
            }

            await context.close();
        });
    }
}

test('la barre d’enregistrement ne masque pas la dernière rangée en bas de page', async ({ browser }) => {
    const context = await browser.newContext({ viewport: viewports.mobile });
    const page = await context.newPage();
    await login(page);

    for (const path of ['/dashboard/skills', '/dashboard/career', '/dashboard/settings', '/dashboard/profile', '/dashboard/scene']) {
        await page.goto(path);
        await page.evaluate(() => window.scrollTo(0, document.documentElement.scrollHeight));
        const overlap = await page.evaluate(() => {
            const bar = document.querySelector('.admin-savebar');
            if (!bar) return null;
            const barTop = bar.getBoundingClientRect().top;
            const scope = bar.closest('form') ?? document.querySelector('main');
            const controls = [...(scope?.querySelectorAll('button, input, select, textarea, a') ?? [])].filter((el) => !bar.contains(el) && !el.closest('[inert]'));
            const lastBottom = Math.max(...controls.map((el) => el.getBoundingClientRect().bottom).filter((b) => b > 0));
            return lastBottom - barTop;
        });
        expect(overlap, path).not.toBeNull();
        expect(overlap, `${path} : un contrôle passe sous la barre`).toBeLessThanOrEqual(1);
    }
    await context.close();
});

// Le focus reste dans la modale ; il peut sortir vers l'interface du navigateur (activeElement = body),
// jamais vers un élément de la page située derrière.
const insideDialogOrBrowserUi = (el: Element): boolean => document.activeElement === document.body || el.contains(document.activeElement);

test('modale de confirmation : focus, Échap, Tab piégé, retour au déclencheur', async ({ page }) => {
    await login(page);
    await page.goto('/dashboard/projects');
    const trigger = page.getByRole('button', { name: 'Supprimer' }).first();
    await trigger.focus();
    await page.keyboard.press('Enter');

    const dialog = page.getByRole('dialog');
    await expect(dialog).toBeVisible();
    await expect(dialog).toHaveAccessibleName(/Supprimer/);
    await expect(dialog.getByRole('button', { name: 'Annuler' })).toBeFocused();

    for (let i = 0; i < 6; i++) {
        await page.keyboard.press('Tab');
        expect(await dialog.evaluate(insideDialogOrBrowserUi)).toBe(true);
    }
    for (let i = 0; i < 6; i++) {
        await page.keyboard.press('Shift+Tab');
        expect(await dialog.evaluate(insideDialogOrBrowserUi)).toBe(true);
    }

    await page.keyboard.press('Escape');
    await expect(dialog).toBeHidden();
    await expect(trigger).toBeFocused();
});

// --- Sections repliables et interrupteurs -------------------------------------------------------------

const wcag = ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa'];

async function freshSettingsPage(page: Page): Promise<void> {
    await login(page);
    await page.goto('/dashboard/settings');
    await page.evaluate(() => window.localStorage.clear());
    await page.reload();
    await expect(page.locator('h1')).toBeVisible();
}

test('section repliable : bouton aria-expanded/aria-controls, clavier, contenu inerte, état mémorisé', async ({ page }) => {
    await freshSettingsPage(page);

    const open = page.getByRole('button', { name: /^Identité du site/ });
    const closed = page.getByRole('button', { name: /^Coordonnées/ });
    await expect(open).toHaveAttribute('aria-expanded', 'true');
    await expect(closed).toHaveAttribute('aria-expanded', 'false');

    // aria-controls pointe vers un élément existant ; le contenu replié est inerte et sort de l'ordre de tabulation.
    const panelId = await closed.getAttribute('aria-controls');
    expect(panelId).toBeTruthy();
    await expect(page.locator(`[id="${panelId}"] [inert]`)).toHaveCount(1);
    await expect(page.getByLabel('Téléphone')).toBeHidden();

    // La barre entière est la cible : 44 px minimum de haut.
    const box = await closed.boundingBox();
    expect(box?.height ?? 0).toBeGreaterThanOrEqual(44);

    // Clavier : Entrée déplie, Espace replie.
    await closed.focus();
    await page.keyboard.press('Enter');
    await expect(closed).toHaveAttribute('aria-expanded', 'true');
    await expect(page.getByLabel('Téléphone')).toBeVisible();
    await page.keyboard.press('Space');
    await expect(closed).toHaveAttribute('aria-expanded', 'false');
    await page.keyboard.press('Enter');

    // L'état est mémorisé par page dans le navigateur.
    await page.reload();
    await expect(page.getByRole('button', { name: /^Coordonnées/ })).toHaveAttribute('aria-expanded', 'true');

    // Tout replier / Tout déplier.
    await page.getByRole('button', { name: 'Tout replier' }).click();
    await expect(page.locator('.admin-collapsible-toggle[aria-expanded="true"]')).toHaveCount(0);
    await page.getByRole('button', { name: 'Tout déplier' }).click();
    await expect(page.locator('.admin-collapsible-toggle[aria-expanded="false"]')).toHaveCount(0);
});

for (const theme of themes) {
    test(`axe — sections repliées puis dépliées et interrupteurs — ${theme}`, async ({ browser }) => {
        const context = await browser.newContext({ viewport: viewports.mobile });
        await context.addInitScript((value) => {
            try {
                localStorage.setItem('portfolio-theme', value);
            } catch {
                /* stockage indisponible */
            }
        }, theme);
        const page = await context.newPage();
        await login(page);
        await page.goto('/dashboard/settings');
        await page.getByRole('button', { name: 'Tout replier' }).click();
        for (const state of ['repliées', 'dépliées']) {
            const results = await new AxeBuilder({ page }).withTags(wcag).analyze();
            expect(results.violations.map((v) => `${v.id}: ${v.nodes.map((n) => n.target.join(' ')).join(' | ')}`), state).toEqual([]);
            await page.getByRole('button', { name: 'Tout déplier' }).click();
        }
        await context.close();
    });
}

test('interrupteur : role=switch, Espace bascule, cible 44 px, état désactivé', async ({ page }) => {
    await page.setViewportSize(viewports.mobile);
    await freshSettingsPage(page);
    await page.getByRole('button', { name: /^Fonctionnalités publiques/ }).click();

    const toggle = page.getByRole('switch', { name: 'Expérience 3D' });
    await expect(toggle).toBeVisible();
    const box = await toggle.boundingBox();
    expect(box?.height ?? 0).toBeGreaterThanOrEqual(44);

    const before = await toggle.getAttribute('aria-checked');
    await toggle.focus();
    await page.keyboard.press('Space');
    await expect(toggle).toHaveAttribute('aria-checked', before === 'true' ? 'false' : 'true');
    await page.keyboard.press('Enter');
    await expect(toggle).toHaveAttribute('aria-checked', before ?? 'false');

    // Aucune case à cocher booléenne ne subsiste sur la page.
    await expect(page.locator('input[type="checkbox"]')).toHaveCount(0);

    // Un interrupteur désactivé (lien social sans URL) ne bascule pas.
    await page.getByRole('button', { name: /^Liens sociaux/ }).click();
    await page.locator('input[id^="social-url-"]').first().fill('');
    await expect(page.getByRole('switch', { name: 'Activé' }).first()).toBeDisabled();
});

test('une section repliée contenant une erreur serveur s’ouvre et le focus va au champ en erreur', async ({ page }) => {
    await freshSettingsPage(page);
    const contact = page.getByRole('button', { name: /^Coordonnées/ });
    await contact.click();
    await page.getByLabel('Téléphone').fill('abc');
    await contact.click();
    await expect(contact).toHaveAttribute('aria-expanded', 'false');

    await page.getByRole('button', { name: 'Enregistrer les paramètres' }).click();
    await expect(contact).toHaveAttribute('aria-expanded', 'true');
    await expect(page.getByLabel('Téléphone')).toBeFocused();
    await expect(page.getByLabel('Téléphone')).toHaveAttribute('aria-invalid', 'true');
});

test('validation native dans une section repliée : la section s’ouvre sur le champ invalide', async ({ page }) => {
    await freshSettingsPage(page);
    const contact = page.getByRole('button', { name: /^Coordonnées/ });
    await contact.click();
    await page.getByLabel('E-mail', { exact: true }).fill('pas-un-email');
    await contact.click();
    await expect(contact).toHaveAttribute('aria-expanded', 'false');

    await page.getByRole('button', { name: 'Enregistrer les paramètres' }).click();
    await expect(contact).toHaveAttribute('aria-expanded', 'true');
    await expect(page.getByLabel('E-mail', { exact: true })).toBeFocused();
});

test('interrupteur : la valeur enregistrée reste un booléen identique à l’ancienne case à cocher', async ({ page }) => {
    await freshSettingsPage(page);
    await page.getByRole('button', { name: /^Fonctionnalités publiques/ }).click();
    const toggle = page.getByRole('switch', { name: 'Activité Git publique' });
    const before = await toggle.getAttribute('aria-checked');
    const flipped = before === 'true' ? 'false' : 'true';

    for (const expected of [flipped, before ?? 'false']) {
        await toggle.click();
        await expect(toggle).toHaveAttribute('aria-checked', expected);
        await page.getByRole('button', { name: 'Enregistrer les paramètres' }).click();
        await expect(page.getByText('Paramètres enregistrés.')).toBeVisible();
        await page.reload();
        // La section reste dépliée (état mémorisé) : on ne la rebascule pas.
        await expect(page.getByRole('button', { name: /^Fonctionnalités publiques/ })).toHaveAttribute('aria-expanded', 'true');
        await expect(page.getByRole('switch', { name: 'Activité Git publique' })).toHaveAttribute('aria-checked', expected);
    }
});
