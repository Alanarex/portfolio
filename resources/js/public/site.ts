import Lenis from 'lenis';

const root = document.documentElement;
const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const coarsePointer = window.matchMedia('(pointer: coarse)').matches;

// Le loader ne s'affiche qu'une fois par session.
try {
    sessionStorage.setItem('loaded', '1');
} catch {
    /* stockage indisponible : le loader se rejouera, sans conséquence */
}

// Défilement inertiel (désactivé si mouvement réduit ou écran tactile).
if (!reducedMotion && !coarsePointer) {
    const lenis = new Lenis({ lerp: 0.1, anchors: true });
    root.classList.add('lenis', 'lenis-smooth');
    const frame = (time: number): void => {
        lenis.raf(time);
        requestAnimationFrame(frame);
    };
    requestAnimationFrame(frame);
}

// La hauteur réelle de la navigation flottante (elle peut passer sur deux lignes) décale le début du hero.
const header = document.querySelector<HTMLElement>('.site-header');

if (header && 'ResizeObserver' in window) {
    new ResizeObserver(() => root.style.setProperty('--header-h', `${header.offsetHeight}px`)).observe(header);
}

// Phares du hero : la zone éclairée suit le curseur.
const hero = document.querySelector<HTMLElement>('[data-hero]');
const lamp = document.querySelector<HTMLInputElement>('[data-hero-lamp]');

if (hero && lamp) {
    // Écoute la fenêtre entière : la navigation flotte au-dessus du hero et ne doit pas interrompre le faisceau.
    window.addEventListener(
        'pointermove',
        (event) => {
            const rect = hero.getBoundingClientRect();
            if (rect.bottom < 0 || rect.top > window.innerHeight) {
                return;
            }
            hero.style.setProperty('--mx', `${event.clientX - rect.left}px`);
            hero.style.setProperty('--my', `${event.clientY - rect.top}px`);
        },
        { passive: true },
    );
    lamp.addEventListener('change', () => hero.classList.toggle('is-lit', !lamp.checked));
}

// Sommaire des études de cas : met en avant la section visible.
const toc = document.querySelector<HTMLElement>('[data-case-toc]');

if (toc && 'IntersectionObserver' in window) {
    const links = new Map(
        [...toc.querySelectorAll<HTMLAnchorElement>('a[href^="#"]')].map((link) => [link.hash.slice(1), link]),
    );
    const observer = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                if (entry.isIntersecting) {
                    links.forEach((link) => link.classList.remove('is-active'));
                    links.get(entry.target.id)?.classList.add('is-active');
                    links.get(entry.target.id)?.setAttribute('aria-current', 'true');
                } else {
                    links.get(entry.target.id)?.removeAttribute('aria-current');
                }
            }
        },
        { rootMargin: '-35% 0px -55% 0px' },
    );
    links.forEach((_, id) => {
        const section = document.getElementById(id);
        if (section) {
            observer.observe(section);
        }
    });
}

// Animations décoratives (carnet de vol) : elles ne tournent que lorsque l'élément est visible.
const live = [...document.querySelectorAll<HTMLElement>('[data-live]')];

if (live.length > 0) {
    if ('IntersectionObserver' in window) {
        const watcher = new IntersectionObserver(
            (entries) => {
                for (const entry of entries) {
                    entry.target.classList.toggle('is-live', entry.isIntersecting);
                }
            },
            { threshold: 0.2 },
        );
        live.forEach((element) => watcher.observe(element));
    } else {
        live.forEach((element) => element.classList.add('is-live'));
    }
}
