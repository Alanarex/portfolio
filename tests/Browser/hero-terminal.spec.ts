import AxeBuilder from '@axe-core/playwright';
import { expect, test } from '@playwright/test';

test.describe('hero terminal', () => {
    test.use({ reducedMotion: 'reduce' });

    test('typing commands: help, about, clear, unknown command, history', async ({ page }) => {
        await page.goto('/fr');
        await page.click('[data-terminal-launch]');
        const terminal = page.locator('[data-terminal]');
        const input = terminal.getByRole('textbox', { name: 'Commande du terminal' });
        const log = terminal.getByRole('log');

        await expect(terminal).not.toHaveAttribute('aria-hidden', 'true');
        await expect(log).toContainText('Tapez help pour voir les commandes disponibles.');

        await input.click();
        await page.keyboard.type('help');
        await page.keyboard.press('Enter');
        for (const name of ['help', 'ls [section]', 'cd <section>', 'cat <section>', 'pwd', 'about', 'skills', 'projects', 'experience', 'contact', 'lang fr|en', 'theme light|dark', 'clear']) {
            await expect(log).toContainText(name);
        }

        await page.keyboard.type('projects');
        await page.keyboard.press('Enter');
        await expect(log.getByRole('link').first()).toBeVisible();

        await page.keyboard.type('nope');
        await page.keyboard.press('Enter');
        await expect(log).toContainText('Commande inconnue : nope');

        // Historique : flèche haut rappelle la dernière commande.
        await page.keyboard.press('ArrowUp');
        await expect(input).toHaveValue('nope');
        await page.keyboard.press('ArrowUp');
        await expect(input).toHaveValue('projects');
        await page.keyboard.press('ArrowDown');
        await expect(input).toHaveValue('nope');
        await input.fill('');

        await page.keyboard.type('clear');
        await page.keyboard.press('Enter');
        await expect(log).not.toContainText('Commande inconnue');
        await expect(log.locator('p')).toHaveCount(0);

        // Pas de piège de focus : Tab quitte le terminal.
        await page.keyboard.press('Tab');
        await expect(input).not.toBeFocused();
    });

    test('theme and lang commands act on the page', async ({ page }) => {
        await page.goto('/fr');
        await page.click('[data-terminal-launch]');
        const input = page.getByRole('textbox', { name: 'Commande du terminal' });

        await input.click();
        await page.keyboard.type('theme light');
        await page.keyboard.press('Enter');
        await expect(page.locator('html')).toHaveAttribute('data-theme', 'light');
        await page.keyboard.type('theme dark');
        await page.keyboard.press('Enter');
        await expect(page.locator('html')).toHaveAttribute('data-theme', 'dark');

        await page.keyboard.type('lang');
        await page.keyboard.press('Enter');
        await expect(page.getByRole('log')).toContainText('Usage : lang fr|en');

        await page.keyboard.type('lang en');
        await page.keyboard.press('Enter');
        await expect(page).toHaveURL(/\/en$/);
        await page.click('[data-terminal-launch]');
        await expect(page.getByRole('log')).toContainText('Type help to see the available commands.');

        await page.getByRole('textbox', { name: 'Terminal command' }).click();
        await page.keyboard.type('foo');
        await page.keyboard.press('Enter');
        await expect(page.getByRole('log')).toContainText('Unknown command: foo');
    });

    test('the window can be dragged, moved with the keyboard and resized', async ({ page }) => {
        await page.goto('/fr');
        await page.click('[data-terminal-launch]');
        const terminal = page.locator('[data-terminal]');
        const bar = terminal.locator('.terminal__bar');
        const grip = bar.locator('.mono');
        await expect(grip).toHaveAttribute('role', 'button');
        await terminal.scrollIntoViewIfNeeded();
        await expect(page.locator('.loader')).toBeHidden();

        const before = (await terminal.boundingBox())!;
        const handle = (await bar.boundingBox())!;
        await page.mouse.move(handle.x + handle.width / 2, handle.y + handle.height / 2);
        await page.mouse.down();
        await page.mouse.move(handle.x + handle.width / 2 - 120, handle.y + handle.height / 2 + 60, { steps: 5 });
        await page.mouse.up();
        const dragged = (await terminal.boundingBox())!;
        expect(Math.round(before.x - dragged.x)).toBe(120);
        expect(Math.round(dragged.y - before.y)).toBe(60);

        await grip.focus();
        await page.keyboard.press('ArrowRight');
        expect(Math.round((await terminal.boundingBox())!.x - dragged.x)).toBe(16);
        await page.keyboard.press('Home');
        expect(Math.round((await terminal.boundingBox())!.x)).toBe(Math.round(before.x));
    });

    test('behaves like a real terminal: empty lines, Ctrl+C, fixed line spacing', async ({ page }) => {
        await page.goto('/fr');
        await page.click('[data-terminal-launch]');
        await expect(page.locator('.loader')).toBeHidden();
        const input = page.getByRole('textbox', { name: 'Commande du terminal' });
        const log = page.getByRole('log');

        await input.click();
        await page.keyboard.press('Enter');
        await page.keyboard.press('Enter');
        await page.keyboard.type('abc');
        await page.keyboard.press('Control+c');
        await expect(input).toHaveValue('');
        await expect(log).toContainText('$ abc^C');
        await page.keyboard.type('help');
        await page.keyboard.press('Enter');

        // Deux lignes vides + ^C + welcome + echo + aide : aucune marge entre les lignes, vides comprises.
        const gaps = await log.locator('p').evaluateAll((nodes) => nodes.slice(1).map((node, index) => node.getBoundingClientRect().top - nodes[index]!.getBoundingClientRect().bottom));
        expect(gaps.length).toBeGreaterThan(8);
        gaps.forEach((gap) => expect(Math.abs(gap)).toBeLessThan(1));
        const empties = await log.locator('p').evaluateAll((nodes) => nodes.filter((node) => node.textContent?.trim() === '$').map((node) => node.getBoundingClientRect().height));
        expect(empties).toHaveLength(2);
        empties.forEach((height) => expect(height).toBeGreaterThan(14));
    });

    test('can be resized from every corner and border', async ({ page }) => {
        await page.goto('/fr');
        await page.click('[data-terminal-launch]');
        await expect(page.locator('.loader')).toBeHidden();
        const terminal = page.locator('[data-terminal]');
        await terminal.evaluate((node) => {
            node.style.left = '120px';
            node.style.top = '80px';
        });
        const drag = async (dir: string, dx: number, dy: number): Promise<void> => {
            const handle = (await terminal.locator(`.terminal__handle--${dir}`).boundingBox())!;
            const cx = handle.x + handle.width / 2;
            const cy = handle.y + handle.height / 2;
            await page.mouse.move(cx, cy);
            await page.mouse.down();
            await page.mouse.move(cx + dx, cy + dy, { steps: 4 });
            await page.mouse.up();
        };

        const start = (await terminal.boundingBox())!;
        await drag('se', 80, 60);
        const se = (await terminal.boundingBox())!;
        expect(Math.round(se.width - start.width)).toBe(80);
        expect(Math.round(se.height - start.height)).toBe(60);
        expect(Math.abs(Math.round(se.x - start.x))).toBe(0);

        await drag('nw', -50, -40); // agrandit vers le haut-gauche : la fenêtre s'étend et son coin bouge
        const nw = (await terminal.boundingBox())!;
        expect(Math.round(nw.width - se.width)).toBe(50);
        expect(Math.round(nw.height - se.height)).toBe(40);
        expect(Math.round(se.x - nw.x)).toBe(50);

        await drag('e', -100, 0);
        await drag('s', 0, -60);
        const edges = (await terminal.boundingBox())!;
        expect(Math.round(nw.width - edges.width)).toBe(100);
        expect(Math.round(nw.height - edges.height)).toBe(60);

        await drag('w', 9999, 0); // taille minimale respectée
        expect((await terminal.boundingBox())!.width).toBeGreaterThanOrEqual(272);
    });

    test('shell commands: ls lists the page sections, cd, pwd, cat, echo, tab completion, clear wipes everything', async ({ page }) => {
        await page.goto('/fr');
        await page.click('[data-terminal-launch]');
        await expect(page.locator('.loader')).toBeHidden();
        const terminal = page.locator('[data-terminal]');
        const input = terminal.getByRole('textbox', { name: 'Commande du terminal' });
        const log = terminal.getByRole('log');
        const run = async (command: string): Promise<void> => {
            await page.keyboard.type(command);
            await page.keyboard.press('Enter');
        };

        await input.click();
        await run('ls');
        const ids = await page.locator('section[id][aria-labelledby]:not(#top)').evaluateAll((nodes) => nodes.map((node) => node.id));
        expect(ids).toContain('projets');
        for (const id of ids) {
            await expect(log.locator('.terminal__ls').getByRole('link', { name: `${id}/`, exact: true })).toBeVisible();
        }

        await run('cd projets');
        await run('pwd');
        await expect(log).toContainText('~/portfolio/projets');
        await expect(terminal.locator('.terminal__bar .mono')).toHaveText('~/portfolio/projets');
        await run('cd ..');
        await expect(terminal.locator('.terminal__bar .mono')).toHaveText('~/portfolio');

        await run('cd nulle-part');
        await expect(log).toContainText('cd: nulle-part: aucun fichier ou dossier de ce type');
        await run('cat');
        await expect(log).toContainText('Usage : cat <section>');
        await run('echo Bonjour Monde');
        await expect(log.locator('p').last()).toHaveText('Bonjour Monde');

        // Complétion : commande puis nom de section.
        await page.keyboard.type('whoa');
        await page.keyboard.press('Tab');
        await expect(input).toHaveValue('whoami ');
        await input.fill('cat proj');
        await page.keyboard.press('Tab');
        await expect(input).toHaveValue('cat projets');
        await page.keyboard.press('Enter');
        await expect(log.getByRole('link').last()).toBeVisible();

        // clear efface aussi les lignes de démonstration rendues par le serveur.
        await expect(terminal).toContainText('php artisan about');
        await run('clear');
        await expect(log.locator('p')).toHaveCount(0);
        await expect(terminal.locator('.terminal__body')).not.toContainText('php artisan about');
    });

    test('resizing keeps the opposite border fixed and the text pinned to the top', async ({ page }) => {
        await page.goto('/fr');
        await page.click('[data-terminal-launch]');
        await expect(page.locator('.loader')).toBeHidden();
        const terminal = page.locator('[data-terminal]');
        await terminal.evaluate((node) => {
            node.style.left = '120px';
            node.style.top = '80px';
        });
        await terminal.getByRole('textbox').click();
        await page.keyboard.type('clear');
        await page.keyboard.press('Enter');
        await page.keyboard.type('pwd');
        await page.keyboard.press('Enter');
        const lineGap = async (): Promise<number> => terminal.getByRole('log').locator('p').evaluateAll((nodes) => nodes[1]!.getBoundingClientRect().top - nodes[0]!.getBoundingClientRect().top);
        const gapBefore = await lineGap();

        const drag = async (dir: string, dx: number, dy: number): Promise<void> => {
            const handle = (await terminal.locator(`.terminal__handle--${dir}`).boundingBox())!;
            const cx = handle.x + handle.width / 2;
            const cy = handle.y + handle.height / 2;
            await page.mouse.move(cx, cy);
            await page.mouse.down();
            await page.mouse.move(cx + dx, cy + dy, { steps: 4 });
            await page.mouse.up();
        };
        const absolute = async (): Promise<{ top: number; bottom: number; left: number; right: number }> =>
            terminal.evaluate((node) => {
                const rect = node.getBoundingClientRect();

                return { top: rect.top + window.scrollY, bottom: rect.bottom + window.scrollY, left: rect.left, right: rect.right };
            });

        const start = await absolute();
        await drag('s', 0, 90); // bord bas : le haut ne bouge pas
        const south = await absolute();
        expect(Math.abs(south.top - start.top)).toBeLessThan(1);
        expect(Math.round(south.bottom - start.bottom)).toBe(90);

        await drag('s', 0, -40);
        expect(Math.abs((await absolute()).top - start.top)).toBeLessThan(1);

        const beforeNorth = await absolute();
        await drag('n', 0, -30); // bord haut : le bas ne bouge pas
        const north = await absolute();
        expect(Math.abs(north.bottom - beforeNorth.bottom)).toBeLessThan(1);
        expect(Math.round(beforeNorth.top - north.top)).toBe(30);

        await drag('e', 60, 0); // bord droit : la gauche ne bouge pas
        expect(Math.abs((await absolute()).left - north.left)).toBeLessThan(1);

        // Le texte reste collé en haut, avec le même interligne, quelle que soit la hauteur.
        await drag('s', 0, 160);
        expect(Math.abs((await lineGap()) - gapBefore)).toBeLessThan(0.5);
        const offset = await terminal.evaluate((node) => node.querySelector('[role="log"] p')!.getBoundingClientRect().top - node.querySelector('.terminal__body')!.getBoundingClientRect().top);
        expect(offset).toBeLessThan(40);
    });

    test('floating window: launcher opens it, minimize hides it, it stays fixed while scrolling', async ({ page }) => {
        await page.goto('/fr');
        const terminal = page.locator('[data-terminal]');
        const launcher = page.locator('[data-terminal-launch]');
        await expect(terminal).toBeHidden();
        await expect(launcher).toHaveAttribute('aria-expanded', 'false');

        await launcher.click();
        await expect(terminal).toBeVisible();
        await expect(launcher).toHaveAttribute('aria-expanded', 'true');
        const before = (await terminal.boundingBox())!;

        await page.evaluate(() => window.scrollTo(0, 900));
        const scrolled = (await terminal.boundingBox())!;
        expect(Math.round(scrolled.y)).toBe(Math.round(before.y));

        await terminal.locator('[data-terminal-minimize]').click();
        await expect(terminal).toBeHidden();
        await expect(launcher).toHaveAttribute('aria-expanded', 'false');

        await launcher.click();
        await expect(terminal).toBeVisible();
        await launcher.click();
        await expect(terminal).toBeHidden();
    });

    test('responses never expose the private contact details', async ({ page }) => {
        await page.goto('/fr');
        await page.click('[data-terminal-launch]');
        await page.getByRole('textbox', { name: 'Commande du terminal' }).click();
        await page.keyboard.type('contact');
        await page.keyboard.press('Enter');
        const output = (await page.getByRole('log').innerText()).toLowerCase();
        expect(output).not.toMatch(/@|mailto:|tel:|\+\d/);
    });

    for (const theme of ['light', 'dark'] as const) {
        test(`has no axe violation on the terminal (${theme})`, async ({ page }) => {
            await page.addInitScript((value) => localStorage.setItem('portfolio-theme', value), theme);
            await page.goto('/fr');
        await page.click('[data-terminal-launch]');
            await page.getByRole('textbox', { name: 'Commande du terminal' }).click();
            await page.keyboard.type('skills');
            await page.keyboard.press('Enter');

            const results = await new AxeBuilder({ page }).include('[data-terminal]').analyze();
            expect(results.violations).toEqual([]);
        });
    }

    test('is a static decorative terminal without JavaScript', async ({ browser }) => {
        const context = await browser.newContext({ javaScriptEnabled: false, reducedMotion: 'reduce' });
        const page = await context.newPage();
        await page.goto('/fr');

        await expect(page.locator('[data-terminal]')).toContainText('php artisan about');
        await expect(page.locator('[data-terminal-launch]')).toBeHidden();
        await expect(page.locator('[data-terminal-form]')).toBeHidden();
        await expect(page.locator('[data-terminal-log]')).toBeHidden();
        await context.close();
    });

    test('stays usable on a phone viewport without horizontal overflow', async ({ browser }) => {
        const context = await browser.newContext({ viewport: { width: 375, height: 812 }, hasTouch: true, isMobile: true, reducedMotion: 'reduce' });
        const page = await context.newPage();
        await page.goto('/fr');

        await page.locator('[data-terminal-launch]').tap();
        const input = page.getByRole('textbox', { name: 'Commande du terminal' });
        await input.tap();
        await page.keyboard.type('experience');
        await page.keyboard.press('Enter');
        await expect(page.getByRole('log')).toContainText('—');

        const overflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
        expect(overflow).toBeLessThanOrEqual(0);
        await context.close();
    });
});
