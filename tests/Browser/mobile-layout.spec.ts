import AxeBuilder from '@axe-core/playwright';
import { expect, test, type Page } from '@playwright/test';

// Responsive mobile : pas de défilement horizontal, cibles tactiles d'au moins 44 px, menu burger accessible.
// Mouvement réduit : les animations d'entrée ne faussent pas les mesures.
test.use({ viewport: { width: 375, height: 812 }, hasTouch: true });

const MIN_TARGET = 43.5;

async function open(page: Page, url: string): Promise<void> {
    await page.emulateMedia({ reducedMotion: 'reduce' });
    await page.goto(url);
    await page.waitForLoadState('load');
    // Le chargeur ne doit pas masquer la page pendant les mesures.
    await page.evaluate(() => sessionStorage.setItem('loaded', '1'));
}

/** Éléments qui dépassent de la fenêtre sans être contenus dans une zone à défilement ou rognée. */
async function overflowingElements(page: Page): Promise<string[]> {
    return page.evaluate(() => {
        const width = window.innerWidth;
        const contained = (element: Element): boolean => {
            for (let parent = element.parentElement; parent && parent !== document.documentElement; parent = parent.parentElement) {
                const style = getComputedStyle(parent);
                if (/(auto|scroll|hidden|clip)/.test(style.overflowX)) {
                    const rect = parent.getBoundingClientRect();
                    if (rect.left >= -1 && rect.right <= width + 1) {
                        return true;
                    }
                }
            }
            return false;
        };

        return [...document.body.querySelectorAll('*')]
            .filter((element) => {
                const style = getComputedStyle(element);
                const rect = element.getBoundingClientRect();
                if (style.display === 'none' || style.position === 'fixed' || rect.width <= 2 || rect.height <= 2 || element.closest('.contact-honeypot')) {
                    return false;
                }
                return (rect.right > width + 1 || rect.left < -1) && !contained(element);
            })
            .slice(0, 8)
            .map((element) => `${element.tagName.toLowerCase()}.${String(element.className).trim().split(/\s+/).join('.')}`);
    });
}

for (const width of [320, 375, 430, 768]) {
    for (const url of ['/fr', '/en', '/fr/projects', '/fr/projects/handicapacite', '/fr/privacy']) {
        test(`no horizontal overflow at ${width}px on ${url}`, async ({ page }) => {
            await page.setViewportSize({ width, height: 800 });
            await open(page, url);
            expect(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).toBe(true);
            expect(await overflowingElements(page)).toEqual([]);
        });
    }
}

async function undersized(page: Page, selector: string): Promise<string[]> {
    const handles = await page.locator(selector).elementHandles();
    const failures: string[] = [];
    for (const handle of handles) {
        const box = await handle.boundingBox();
        if (box === null) {
            continue; // masqué
        }
        if (box.height < MIN_TARGET || box.width < MIN_TARGET) {
            failures.push(`${selector} ${Math.round(box.width)}x${Math.round(box.height)} « ${((await handle.textContent()) ?? '').trim().slice(0, 30)} »`);
        }
    }
    return failures;
}

test('touch targets are at least 44px at 375px', async ({ page }) => {
    await open(page, '/fr');
    const selectors = [
        '.site-header .brand',
        '.site-header .lang-switch',
        '[data-menu-toggle]',
        '.hero .button',
        '[data-project-filters] button',
        '.stack__cta',
        '.stack__link',
        '[data-map-toggle]',
        '.skillmap__related a',
        '.plate__link',
        '.contact__channels .button',
        '.contact-form .button',
        '.site-footer__links a',
    ];
    const failures: string[] = [];
    for (const selector of selectors) {
        failures.push(...(await undersized(page, selector)));
    }
    expect(failures).toEqual([]);

    await page.getByRole('button', { name: 'Ouvrir le menu' }).click();
    const sheet: string[] = [];
    for (const selector of ['[data-menu] nav a', '[data-menu] .theme-toggle', '[data-menu] .menu-sheet__row', '[data-menu] .button']) {
        sheet.push(...(await undersized(page, selector)));
    }
    expect(sheet).toEqual([]);
});

test('burger menu opens a focus-trapped sheet closed by Escape', async ({ page }) => {
    await open(page, '/fr');
    const burger = page.getByRole('button', { name: 'Ouvrir le menu' });
    await expect(burger).toHaveAttribute('aria-expanded', 'false');
    await expect(page.locator('.site-nav')).toBeHidden();

    await burger.click();
    const closer = page.getByRole('button', { name: 'Fermer le menu' });
    await expect(closer).toHaveAttribute('aria-expanded', 'true');
    const sheet = page.locator('[data-menu]');
    await expect(sheet).toBeVisible();
    await expect(sheet.getByRole('link', { name: /Projets/ })).toBeFocused();

    for (let press = 0; press < 12; press++) {
        await page.keyboard.press('Tab');
        expect(await page.evaluate(() => document.activeElement?.closest('[data-menu], .site-header') !== null)).toBe(true);
    }
    for (let press = 0; press < 12; press++) {
        await page.keyboard.press('Shift+Tab');
        expect(await page.evaluate(() => document.activeElement?.closest('[data-menu], .site-header') !== null)).toBe(true);
    }

    const results = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa']).analyze();
    expect(results.violations).toEqual([]);

    await page.keyboard.press('Escape');
    await expect(sheet).toBeHidden();
    await expect(page.getByRole('button', { name: 'Ouvrir le menu' })).toBeFocused();
    await expect(page.getByRole('button', { name: 'Ouvrir le menu' })).toHaveAttribute('aria-expanded', 'false');

    // Un lien du menu referme la feuille et mène à la section.
    await page.getByRole('button', { name: 'Ouvrir le menu' }).click();
    await sheet.getByRole('link', { name: /Contact/ }).click();
    await expect(sheet).toBeHidden();
    await expect(page).toHaveURL(/#contact$/);
});

test('project filter and skills route work on a phone', async ({ page }) => {
    await open(page, '/fr');
    const filters = page.locator('[data-project-filters] button');
    await expect(filters.first()).toBeVisible();
    const cards = page.locator('[data-project-stack] .stack__card');
    const visibleCards = page.locator('[data-project-stack] .stack__card:visible');
    const total = await cards.count();
    expect(total).toBeGreaterThan(1);
    await filters.nth(1).click();
    await expect(filters.nth(1)).toHaveAttribute('aria-pressed', 'true');
    expect(await visibleCards.count()).toBeLessThan(total);
    await filters.first().click();
    expect(await visibleCards.count()).toBe(total);

    const toggles = page.locator('[data-map-toggle]');
    await expect(toggles.first()).toBeEnabled();
    await expect(toggles.first()).toHaveAttribute('aria-expanded', 'true');
    await expect(toggles.nth(1)).toHaveAttribute('aria-expanded', 'false');
    await toggles.nth(1).click();
    await expect(toggles.nth(1)).toHaveAttribute('aria-expanded', 'true');
    await expect(toggles.first()).toHaveAttribute('aria-expanded', 'false');
});

test('home page has no accessibility violations at 375px', async ({ page }) => {
    await open(page, '/fr');
    const results = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa']).analyze();
    expect(results.violations).toEqual([]);
});
