// Mobile : filtre des projets (chips) et effet au défilement des cartes.
// Chaque carte [data-pr] reçoit --pr (0 en bas de l'écran, 1 à 45 % de la hauteur) : elle monte, s'éclaire et grandit.
// Sans JavaScript ou avec « réduire les animations », --pr reste à 1 (rendu complet, voir mobile.css).
const mobile = window.matchMedia('(width <= 44rem)');
const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
const cards = [...document.querySelectorAll<HTMLElement>('[data-pr]')];

const clamp = (value: number, min: number, max: number): number => Math.min(Math.max(value, min), max);

const progress = (): void => {
    const height = window.innerHeight;
    cards.forEach((card) => {
        if (card.hidden) {
            return;
        }
        const top = card.getBoundingClientRect().top;
        const value = 1 - clamp((top - height * 0.45) / (height * 0.5), 0, 1);
        card.style.setProperty('--pr', value.toFixed(3));
    });
};

let pending = false;
const schedule = (): void => {
    if (pending) {
        return;
    }
    pending = true;
    requestAnimationFrame(() => {
        pending = false;
        progress();
    });
};

let active = false;
const sync = (): void => {
    const next = mobile.matches && !reduced.matches && cards.length > 0;
    if (next === active) {
        return;
    }
    active = next;
    if (next) {
        window.addEventListener('scroll', schedule, { passive: true });
        window.addEventListener('resize', schedule, { passive: true });
        progress();
    } else {
        window.removeEventListener('scroll', schedule);
        window.removeEventListener('resize', schedule);
        cards.forEach((card) => card.style.removeProperty('--pr'));
    }
};

mobile.addEventListener('change', sync);
reduced.addEventListener('change', sync);
sync();

// Filtre des projets : les chips ne s'affichent que si le JavaScript est là (sans lui, tous les projets restent visibles).
const filters = document.querySelector<HTMLElement>('[data-project-filters]');
const stack = document.querySelector<HTMLElement>('[data-project-stack]');

if (filters && stack) {
    filters.hidden = false;
    const buttons = [...filters.querySelectorAll<HTMLButtonElement>('[data-filter]')];
    const items = [...stack.querySelectorAll<HTMLElement>('[data-status]')];
    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            const filter = button.dataset.filter ?? 'all';
            buttons.forEach((other) => other.setAttribute('aria-pressed', String(other === button)));
            items.forEach((item) => {
                item.hidden = filter !== 'all' && item.dataset.status !== filter;
            });
            if (active) {
                requestAnimationFrame(progress);
            }
        });
    });
}
