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
            const controls = [...(scope?.querySelectorAll('button, input, select, textarea, a') ?? [])].filter((el) => !bar.contains(el));
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
