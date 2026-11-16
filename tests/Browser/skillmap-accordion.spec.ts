import { expect, test, type Page } from '@playwright/test';

// Accordéon des compétences (mobile) : ouvrir une étape ne fait jamais sauter la page.
// Le bord haut de l'étape qui vient de s'ouvrir doit rester au même endroit à l'écran pendant toute l'animation.
test.use({ viewport: { width: 375, height: 812 }, hasTouch: true });

const TOLERANCE = 3;

async function prepare(page: Page, reducedMotion: 'reduce' | 'no-preference'): Promise<void> {
    await page.emulateMedia({ reducedMotion });
    await page.goto('/fr');
    await page.waitForLoadState('load');
    await page.evaluate(() => sessionStorage.setItem('loaded', '1'));
    // La page (sections chargées en différé) doit avoir fini de changer de hauteur avant de mesurer.
    await page.waitForFunction(
        () =>
            new Promise<boolean>((resolve) => {
                const height = document.documentElement.scrollHeight;
                setTimeout(() => resolve(document.documentElement.scrollHeight === height), 1500);
            }),
        undefined,
        { timeout: 30_000, polling: 'raf' },
    );
    await page.locator('.skillmap__panels').scrollIntoViewIfNeeded();
    // Les ouvertures automatiques déclenchées par ce premier défilement doivent être terminées.
    await page.waitForFunction(
        () =>
            new Promise<boolean>((resolve) => {
                const state = (): string => [...document.querySelectorAll('[data-map-panel]')].map((panel) => panel.className).join('|') + Math.round(window.scrollY);
                const before = state();
                setTimeout(() => resolve(state() === before), 1500);
            }),
        undefined,
        { timeout: 30_000, polling: 'raf' },
    );

    // À chaque ouverture, on suit le bord haut de la nouvelle étape ouverte pendant 700 ms et on garde le pire écart.
    await page.evaluate(() => {
        const panels = [...document.querySelectorAll<HTMLElement>('[data-map-panel]')];
        const w = window as unknown as { __opens: { index: number; drift: number }[] };
        w.__opens = [];
        let session = 0;
        const track = (panel: HTMLElement, index: number): void => {
            const current = ++session;
            const reference = panel.getBoundingClientRect().top;
            const record = { index, drift: 0 };
            w.__opens.push(record);
            const started = performance.now();
            const frame = (now: number): void => {
                if (current !== session) {
                    return;
                }
                record.drift = Math.max(record.drift, Math.abs(panel.getBoundingClientRect().top - reference));
                if (now - started < 700) {
                    requestAnimationFrame(frame);
                }
            };
            requestAnimationFrame(frame);
        };
        new MutationObserver((records) => {
            for (const record of records) {
                const target = record.target as HTMLElement;
                if (target.classList.contains('is-open') && !(record.oldValue ?? '').includes('is-open')) {
                    track(target, panels.indexOf(target));
                }
            }
        }).observe(document.querySelector('.skillmap__panels') as Element, { subtree: true, attributes: true, attributeFilter: ['class'], attributeOldValue: true });
    });
}

const opens = (page: Page): Promise<{ index: number; drift: number }[]> =>
    page.evaluate(() => (window as unknown as { __opens: { index: number; drift: number }[] }).__opens.map((open) => ({ ...open })));

for (const reducedMotion of ['no-preference', 'reduce'] as const) {
    test(`toucher une étape ne fait pas sauter la page (${reducedMotion})`, async ({ page }) => {
        await prepare(page, reducedMotion);
        // Une étape fermée visible à l'écran (la première trouvée sous l'étape ouverte, sinon au-dessus).
        const index = await page.evaluate(() => {
            const panels = [...document.querySelectorAll<HTMLElement>('[data-map-panel]')];
            const open = panels.findIndex((panel) => panel.classList.contains('is-open'));
            const visible = (panel: HTMLElement): boolean => panel.getBoundingClientRect().top > 70 && panel.getBoundingClientRect().bottom < innerHeight - 20;
            const below = panels.findIndex((panel, position) => position > open && visible(panel));
            return below >= 0 ? below : panels.findIndex((panel, position) => position < open && visible(panel));
        });
        expect(index).toBeGreaterThanOrEqual(0);

        // locator.tap() attend que le bouton soit stable et visible avant de toucher : plus fiable qu'un tap par coordonnées calculées avant.
        const toggle = page.locator('[data-map-toggle]').nth(index);
        const box = await toggle.boundingBox();
        expect(box).not.toBeNull();
        await toggle.tap({ position: { x: 24, y: Math.round((box?.height ?? 0) / 2) } });
        await page.waitForTimeout(1000);

        const recorded = await opens(page);
        expect(recorded.map((open) => open.index)).toContain(index);
        for (const open of recorded) {
            expect(open.drift, `étape ${open.index}`).toBeLessThanOrEqual(TOLERANCE);
        }
    });
}

test("l'ouverture automatique au défilement ne fait pas sauter la page", async ({ page }) => {
    await prepare(page, 'no-preference');
    // Défilement vers le bas par paliers (instantanés, pour ne pas mélanger le mouvement du doigt avec la mesure) :
    // plusieurs étapes s'ouvrent l'une après l'autre ; on attend la fin de chaque animation.
    for (let step = 0; step < 12; step += 1) {
        await page.evaluate(() => window.scrollBy({ top: 200, behavior: 'instant' }));
        await page.waitForTimeout(850);
    }

    const recorded = await opens(page);
    expect(recorded.length).toBeGreaterThanOrEqual(1);
    for (const open of recorded) {
        expect(open.drift, `étape ${open.index}`).toBeLessThanOrEqual(TOLERANCE);
    }
});
