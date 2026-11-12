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

// Phares du hero : la zone éclairée suit le curseur.
const hero = document.querySelector<HTMLElement>('[data-hero]');
const lamp = document.querySelector<HTMLInputElement>('[data-hero-lamp]');

if (hero && lamp) {
    hero.addEventListener('pointermove', (event) => {
        const rect = hero.getBoundingClientRect();
        hero.style.setProperty('--mx', `${event.clientX - rect.left}px`);
        hero.style.setProperty('--my', `${event.clientY - rect.top}px`);
    });
    lamp.addEventListener('change', () => hero.classList.toggle('is-lit', !lamp.checked));
}
