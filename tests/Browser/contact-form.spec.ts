import { expect, test } from '@playwright/test';

test('contact form marks required fields and shows red validation messages', async ({ page }) => {
    await page.emulateMedia({ reducedMotion: 'reduce' });
    await page.goto('/fr');
    const form = page.locator('[data-contact-form]');
    await form.scrollIntoViewIfNeeded();

    // Astérisques sur les champs obligatoires uniquement.
    await expect(form.locator('.field__required')).toHaveCount(3);
    await expect(form.getByText('Les champs marqués d’un * sont obligatoires.')).toBeVisible();

    await form.getByRole('button', { name: /Envoyer le message/ }).click();
    await expect(form.getByText('Veuillez indiquer votre nom.')).toBeVisible();
    await expect(form.getByText('Veuillez indiquer votre adresse e-mail.')).toBeVisible();
    await expect(form.getByText('Veuillez écrire votre message.')).toBeVisible();
    await expect(page).toHaveURL(/\/fr$/);
    await expect(form.locator('#contact-name')).toBeFocused();

    const color = await form.getByText('Veuillez indiquer votre nom.').evaluate((element) => getComputedStyle(element).color);
    const [red, green, blue] = (color.match(/\d+/g) ?? []).map(Number);
    expect(red).toBeGreaterThan(green + 50);
    expect(red).toBeGreaterThan(blue + 50);

    await form.locator('#contact-email').fill('pas-un-email');
    await form.locator('#contact-email').blur();
    await expect(form.getByText('Cette adresse e-mail n’est pas valide.')).toBeVisible();
    await form.locator('#contact-message').fill('court');
    await form.locator('#contact-message').blur();
    await expect(form.getByText('Le message doit contenir au moins 10 caractères.')).toBeVisible();

    await form.locator('#contact-name').fill('Camille Martin');
    await form.locator('#contact-email').fill('camille@example.test');
    await form.locator('#contact-message').fill('Bonjour, je souhaite échanger sur un projet.');
    await expect(form.locator('[data-error-for]:visible')).toHaveCount(0);
});

test('the empty featured-projects message keeps its distance from the button', async ({ page }) => {
    await page.goto('/fr');
    const heading = page.locator('#projets .section-heading');
    const state = page.locator('#projets .state');
    if ((await state.count()) === 0) {
        test.skip(true, 'Featured projects are present in this dataset.');
    }
    const gap = await page.evaluate(() => {
        const button = document.querySelector('#projets .section-heading a')?.getBoundingClientRect();
        const box = document.querySelector('#projets .state')?.getBoundingClientRect();
        return button && box ? box.top - button.bottom : 0;
    });
    await expect(heading).toBeVisible();
    expect(gap).toBeGreaterThanOrEqual(16);
});
