import { lenis } from './site';

// Menu mobile : feuille plein écran ouverte par le bouton burger. Échap la ferme, le focus reste piégé dedans,
// aria-expanded suit l'état, et la page ne défile plus derrière.
const toggle = document.querySelector<HTMLButtonElement>('[data-menu-toggle]');
const sheet = document.querySelector<HTMLElement>('[data-menu]');
const header = document.querySelector<HTMLElement>('.site-header');

if (toggle && sheet && header) {
    const root = document.documentElement;
    const narrow = window.matchMedia('(width <= 44rem)');
    const background = [...document.querySelectorAll<HTMLElement>('main, footer, .scroll-progress')];
    const focusable = 'a[href], button:not([disabled]), input:not([disabled]), [tabindex]:not([tabindex="-1"])';
    let open = false;

    // Le bouton d'en-tête (burger) et la langue restent actifs : ils font partie du cycle de focus de la feuille.
    const cycle = (): HTMLElement[] =>
        [...header.querySelectorAll<HTMLElement>(focusable), ...sheet.querySelectorAll<HTMLElement>(focusable)].filter(
            (element) => element.getClientRects().length > 0,
        );

    const set = (next: boolean, restoreFocus = true): void => {
        if (next === open) {
            return;
        }
        open = next;
        sheet.hidden = !next;
        root.classList.toggle('menu-open', next);
        toggle.setAttribute('aria-expanded', String(next));
        toggle.setAttribute('aria-label', (next ? toggle.dataset.labelClose : toggle.dataset.labelOpen) ?? '');
        background.forEach((element) => {
            if (next) {
                element.setAttribute('inert', '');
            } else {
                element.removeAttribute('inert');
            }
        });
        if (next) {
            lenis?.stop();
            sheet.querySelector<HTMLElement>('a')?.focus();
        } else {
            lenis?.start();
            if (restoreFocus) {
                toggle.focus();
            }
        }
    };

    toggle.addEventListener('click', () => set(!open));

    document.addEventListener('keydown', (event) => {
        if (!open) {
            return;
        }
        if (event.key === 'Escape') {
            event.preventDefault();
            set(false);
            return;
        }
        if (event.key !== 'Tab') {
            return;
        }
        const items = cycle();
        const first = items[0];
        const last = items[items.length - 1];
        if (!first || !last) {
            return;
        }
        const active = document.activeElement;
        if (event.shiftKey && (active === first || !items.includes(active as HTMLElement))) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && (active === last || !items.includes(active as HTMLElement))) {
            event.preventDefault();
            first.focus();
        }
    });

    // Un lien du menu referme la feuille avant que la page ne défile vers l'ancre.
    sheet.addEventListener('click', (event) => {
        if ((event.target as HTMLElement).closest('a[href]')) {
            set(false, false);
        }
    });

    // Retour au bureau (rotation, redimensionnement) : la feuille n'a plus de raison d'être ouverte.
    narrow.addEventListener('change', () => {
        if (!narrow.matches) {
            set(false, false);
        }
    });

}
