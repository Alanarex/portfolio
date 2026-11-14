import AxeBuilder from '@axe-core/playwright';
import { expect, test, type Page } from '@playwright/test';

const HOTSPOT_KEYS = [
    'main_monitor',
    'secondary_monitor',
    'bookshelf',
    'certificates',
    'calendar',
    'kanban_board',
    'server_rack',
    'phone',
    'airplane_model',
    'car_model',
    'fishing_object',
];

function trackScripts(page: Page): string[] {
    const urls: string[] = [];
    page.on('request', (request) => {
        if (request.resourceType() === 'script') {
            urls.push(request.url());
        }
    });

    return urls;
}

async function scrollToRoom(page: Page): Promise<void> {
    await page.locator('#atelier').scrollIntoViewIfNeeded();
    await page.evaluate(() => window.dispatchEvent(new Event('scroll')));
}

test('every hotspot has an HTML equivalent and the section works without JavaScript', async ({ browser }) => {
    const context = await browser.newContext({ javaScriptEnabled: false });
    const page = await context.newPage();
    await page.goto('/fr');

    await expect(page.getByRole('heading', { name: 'Mon espace de travail en 3D' })).toBeVisible();
    for (const key of HOTSPOT_KEYS) {
        await expect(page.locator(`#atelier a[data-room-hotspot="${key}"]`)).toBeVisible();
    }
    await expect(page.locator('#atelier [data-room-stage]')).toBeHidden();
    await context.close();
});

test.describe('desktop', () => {
    test('Three.js is loaded lazily, rendering pauses outside the viewport and DPR is clamped', async ({ browser }) => {
        const context = await browser.newContext({ viewport: { width: 1440, height: 900 }, deviceScaleFactor: 3 });
        const page = await context.newPage();
        const scripts = trackScripts(page);
        await page.goto('/fr');
        await page.waitForLoadState('networkidle');

        expect(scripts.filter((url) => /\/(room|three\.module)-/.test(url))).toEqual([]);
        await expect(page.locator('#atelier')).toHaveAttribute('data-state', 'poster');

        await scrollToRoom(page);
        await expect(page.locator('#atelier')).toHaveAttribute('data-state', 'ready', { timeout: 20_000 });
        expect(scripts.some((url) => /\/room-/.test(url))).toBe(true);
        await expect(page.locator('#atelier canvas')).toBeVisible();
        await expect(page.locator('#atelier')).toHaveAttribute('data-room-rendering', 'true');

        const dpr = Number(await page.locator('#atelier').getAttribute('data-room-dpr'));
        expect(dpr).toBeLessThanOrEqual(1.5);

        await page.evaluate(() => window.scrollTo(0, 0));
        await expect(page.locator('#atelier')).toHaveAttribute('data-room-rendering', 'false', { timeout: 5_000 });
        await scrollToRoom(page);
        await expect(page.locator('#atelier')).toHaveAttribute('data-room-rendering', 'true', { timeout: 5_000 });
        await context.close();
    });

    test('runtime payload stays within the documented budget', async ({ page }) => {
        const sizes: Record<string, number> = {};
        page.on('response', async (response) => {
            const url = response.url();
            if (/\/build\/assets\/(room|three\.module)-.*\.js$/.test(url)) {
                sizes[url.split('/').pop() ?? url] = (await response.body()).length;
            }
        });
        await page.goto('/fr');
        await scrollToRoom(page);
        await expect(page.locator('#atelier')).toHaveAttribute('data-state', 'ready', { timeout: 20_000 });
        const total = Object.values(sizes).reduce((sum, size) => sum + size, 0);
        console.log(`room runtime payload (uncompressed): ${total} bytes`, sizes);
        expect(Object.keys(sizes).length).toBeGreaterThanOrEqual(2);
        expect(total).toBeLessThan(4 * 1024 * 1024);
    });

    test('fullscreen dialog opens, traps focus, closes with Escape and restores focus', async ({ page }) => {
        await page.goto('/fr');
        await scrollToRoom(page);
        await expect(page.locator('#atelier')).toHaveAttribute('data-state', 'ready', { timeout: 20_000 });

        const enter = page.getByRole('button', { name: 'Plein écran' });
        await enter.focus();
        await page.keyboard.press('Enter');
        const dialog = page.getByRole('dialog', { name: 'Scène 3D en plein écran' });
        await expect(dialog).toBeVisible();
        await expect(dialog.locator('canvas')).toBeVisible();
        await expect(dialog.getByRole('button', { name: 'Fermer' })).toBeFocused();

        // Le focus reste dans la boîte de dialogue.
        for (let i = 0; i < 20; i += 1) {
            await page.keyboard.press('Tab');
            expect(await page.evaluate(() => document.activeElement?.closest('dialog') != null)).toBe(true);
        }

        const results = await new AxeBuilder({ page }).include('dialog').withTags(['wcag2a', 'wcag2aa']).analyze();
        expect(results.violations).toEqual([]);

        await page.keyboard.press('Escape');
        await expect(dialog).toBeHidden();
        await expect(enter).toBeFocused();
        await expect(page.locator('#atelier [data-room-canvas] canvas')).toBeVisible();
    });

    test('a hotspot link inside the fullscreen dialog closes it and jumps to the section', async ({ page }) => {
        await page.goto('/fr');
        await scrollToRoom(page);
        await expect(page.locator('#atelier')).toHaveAttribute('data-state', 'ready', { timeout: 20_000 });
        await page.getByRole('button', { name: 'Plein écran' }).click();
        const dialog = page.getByRole('dialog');
        await dialog.getByRole('link', { name: /Bibliothèque/ }).click();
        await expect(dialog).toBeHidden();
        await expect(page).toHaveURL(/#competences$/);
    });

    test('the avatar greets on activation and the message can be dismissed', async ({ page }) => {
        await page.goto('/fr');
        await scrollToRoom(page);
        await expect(page.locator('#atelier')).toHaveAttribute('data-state', 'ready', { timeout: 20_000 });
        await expect(page.locator('#atelier')).toHaveAttribute('data-avatar-state', 'IDLE_CODING');

        await page.getByRole('button', { name: 'Saluer l’avatar' }).click();
        await expect(page.locator('#atelier')).toHaveAttribute('data-avatar-state', 'GREETING', { timeout: 5_000 });
        await expect(page.locator('[data-room-bubble]')).toBeVisible();
        await expect(page.locator('[data-room-bubble]')).toContainText('Bienvenue');

        await page.locator('[data-room-bubble] button').click();
        await expect(page.locator('#atelier')).toHaveAttribute('data-avatar-state', 'IDLE_CODING', { timeout: 5_000 });
        await expect(page.locator('[data-room-bubble]')).toBeHidden();
    });

    test('the skip control removes the canvas and the scene can be loaded again', async ({ page }) => {
        await page.goto('/fr');
        await scrollToRoom(page);
        await expect(page.locator('#atelier')).toHaveAttribute('data-state', 'ready', { timeout: 20_000 });
        await page.getByRole('button', { name: 'Passer la scène 3D' }).click();
        await expect(page.locator('#atelier')).toHaveAttribute('data-state', 'poster');
        await expect(page.locator('#atelier canvas')).toHaveCount(0);
        await page.getByRole('button', { name: 'Charger la scène 3D' }).click();
        await expect(page.locator('#atelier')).toHaveAttribute('data-state', 'ready', { timeout: 20_000 });
    });
});

test.describe('reduced motion', () => {
    test('renders on demand instead of looping', async ({ page }) => {
        await page.emulateMedia({ reducedMotion: 'reduce' });
        await page.goto('/fr');
        await scrollToRoom(page);
        await expect(page.locator('#atelier')).toHaveAttribute('data-state', 'ready', { timeout: 20_000 });
        await expect(page.locator('#atelier')).toHaveAttribute('data-room-rendering', 'false', { timeout: 5_000 });

        await page.getByRole('button', { name: 'Saluer l’avatar' }).click();
        await expect(page.locator('#atelier')).toHaveAttribute('data-avatar-state', 'GREETING', { timeout: 5_000 });
    });
});

test.describe('mobile', () => {
    test.use({ viewport: { width: 390, height: 844 }, hasTouch: true, isMobile: true });

    test('keeps the poster and loads the scene only on demand', async ({ page }) => {
        const scripts = trackScripts(page);
        await page.goto('/fr');
        await scrollToRoom(page);
        await page.waitForTimeout(1000);
        await expect(page.locator('#atelier')).toHaveAttribute('data-state', 'fallback');
        expect(scripts.some((url) => /\/room-/.test(url))).toBe(false);

        await page.getByRole('button', { name: 'Charger la scène 3D' }).click();
        await expect(page.locator('#atelier')).toHaveAttribute('data-state', 'ready', { timeout: 20_000 });
        const dpr = Number(await page.locator('#atelier').getAttribute('data-room-dpr'));
        expect(dpr).toBeLessThanOrEqual(1);
    });
});

test.describe('WebGL failure', () => {
    test('shows the poster message and keeps the HTML links', async ({ page }) => {
        await page.addInitScript(() => {
            HTMLCanvasElement.prototype.getContext = () => null;
        });
        await page.goto('/fr');
        await scrollToRoom(page);
        await expect(page.locator('#atelier')).toHaveAttribute('data-state', 'unavailable');
        await expect(page.locator('#atelier [data-room-status]')).toContainText('pas disponible');
        await expect(page.locator('#atelier canvas')).toHaveCount(0);
        for (const key of HOTSPOT_KEYS) {
            await expect(page.locator(`#atelier a[data-room-hotspot="${key}"]`)).toBeVisible();
        }
    });
});

test('English locale exposes localized hotspot links', async ({ page }) => {
    await page.goto('/en');
    await expect(page.locator('#atelier a[data-room-hotspot="main_monitor"]')).toContainText('Main monitor');
});
