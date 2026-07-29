import AxeBuilder from '@axe-core/playwright';
import { expect, test } from '@playwright/test';
import { mkdir } from 'node:fs/promises';
import path from 'node:path';

const captureDirectory = path.resolve('docs/captures/implemented');

test.beforeAll(async () => {
    await mkdir(captureDirectory, { recursive: true });
});

test('essential landing and project content works without JavaScript', async ({ browser }) => {
    const context = await browser.newContext({
        javaScriptEnabled: false,
        viewport: { width: 1440, height: 1000 },
    });
    const page = await context.newPage();

    await page.goto('/fr');
    await expect(page.locator('h1')).toContainText('Développeur PHP / Laravel');
    await expect(page.getByRole('heading', { name: 'Projets à la une' })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Résultats vérifiés' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Découvrir mes projets' })).toHaveAttribute('href', '#projects');
    await expect(page.getByRole('textbox', { name: 'Nom' })).toBeVisible();

    await page.goto('/fr/projects');
    await expect(page.getByRole('heading', { name: 'Projets', exact: true })).toBeVisible();
    const projectLink = page.getByRole('link', { name: 'Handicapacité' }).first();
    await expect(projectLink).toBeVisible();

    await projectLink.click();
    await expect(page).toHaveURL(/\/fr\/projects\/handicapacite$/);
    await expect(page.locator('h1')).toHaveText('Handicapacité');
    await expect(page.getByRole('heading', { name: 'Contexte' })).toBeVisible();

    await context.close();
});

test('keyboard flow and audited public pages meet WCAG A/AA rules', async ({ page }) => {
    for (const url of ['/fr', '/fr/projects', '/fr/projects/handicapacite', '/fr/privacy']) {
        await page.goto(url);
        await page.keyboard.press('Tab');
        await expect(page.getByRole('link', { name: 'Aller au contenu principal' })).toBeFocused();

        const results = await new AxeBuilder({ page })
            .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'])
            .analyze();
        expect(results.violations, `${url} accessibility violations`).toEqual([]);
    }
});

test('contact submission and privacy information work without exposing the recipient', async ({ page }) => {
    await page.goto('/fr');
    await expect(page.locator('body')).not.toContainText('contact@example.test');
    await page.getByRole('textbox', { name: 'Nom' }).fill('Camille Martin');
    await page.getByRole('textbox', { name: 'E-mail' }).fill('camille@example.test');
    await page.getByRole('textbox', { name: 'Objet (facultatif)' }).fill('Projet Laravel');
    await page.getByRole('textbox', { name: 'Message' }).fill('Bonjour, je souhaite discuter d’un projet Laravel accessible.');
    await page.getByRole('button', { name: 'Envoyer le message' }).click();
    await expect(page.getByRole('status')).toContainText('file d’envoi');

    await page.getByRole('link', { name: 'Confidentialité' }).click();
    await expect(page).toHaveURL(/\/fr\/privacy$/);
    await expect(page.getByRole('heading', { name: 'Mesure d’audience' })).toBeVisible();
});

test('light, dark, desktop, and mobile implementation captures render', async ({ browser }) => {
    const desktopContext = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
    const desktop = await desktopContext.newPage();
    await desktop.goto('/fr');
    await desktop.screenshot({
        path: path.join(captureDirectory, 'PORT-008-home-light-desktop.png'),
        fullPage: true,
    });
    await desktop.locator('#contact').screenshot({
        path: path.join(captureDirectory, 'PORT-009-contact-light-desktop.png'),
    });
    await desktop.getByRole('button', { name: /Sombre/ }).click();
    await expect(desktop.locator('html')).toHaveClass(/dark/);
    await desktop.screenshot({
        path: path.join(captureDirectory, 'PORT-008-home-dark-desktop.png'),
        fullPage: true,
    });
    await desktop.goto('/fr/projects');
    await desktop.screenshot({
        path: path.join(captureDirectory, 'PORT-008-projects-dark-desktop.png'),
        fullPage: true,
    });
    await desktop.goto('/fr/projects/handicapacite');
    await desktop.screenshot({
        path: path.join(captureDirectory, 'PORT-008-case-study-dark-desktop.png'),
        fullPage: true,
    });
    await desktopContext.close();

    const mobileContext = await browser.newContext({ viewport: { width: 390, height: 844 } });
    const mobile = await mobileContext.newPage();
    await mobile.goto('/fr');
    await expect(mobile.getByRole('navigation', { name: 'Navigation principale' }).last()).toBeVisible();
    await mobile.screenshot({
        path: path.join(captureDirectory, 'PORT-008-home-light-mobile.png'),
        fullPage: true,
    });
    await mobile.goto('/fr/projects');
    await mobile.screenshot({
        path: path.join(captureDirectory, 'PORT-008-projects-light-mobile.png'),
        fullPage: true,
    });
    await mobile.goto('/fr/privacy');
    await mobile.screenshot({
        path: path.join(captureDirectory, 'PORT-009-privacy-light-mobile.png'),
        fullPage: true,
    });
    await mobileContext.close();
});
