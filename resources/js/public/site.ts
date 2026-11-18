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

// Compteur du loader : Safari ne redessine pas un `counter()` piloté par une propriété animée (il reste à 0 puis saute à
// la valeur finale). On écrit donc le nombre en JavaScript, calé sur l'animation CSS `boot` (même durée et mêmes paliers).
const loaderSpeed = document.querySelector<HTMLElement>('.loader .gauge__speed');

if (loaderSpeed && !root.classList.contains('no-loader')) {
    // cubic-bezier(0.45, 0, 0.2, 1), l'accélération appliquée à chaque palier de l'animation.
    const bezier = (x: number): number => {
        const [x1, y1, x2, y2] = [0.45, 0, 0.2, 1];
        const at = (a: number, b: number, t: number): number => 3 * a * (1 - t) ** 2 * t + 3 * b * (1 - t) * t ** 2 + t ** 3;
        let lo = 0;
        let hi = 1;

        for (let i = 0; i < 20; i++) {
            const mid = (lo + hi) / 2;
            if (at(x1, x2, mid) < x) lo = mid;
            else hi = mid;
        }

        return at(y1, y2, (lo + hi) / 2);
    };
    // Paliers de `boot` (3,4 s) : 0 → 320 km/h à 52 %, retour au ralenti (29) à 78 %.
    const speedAt = (ms: number): number => {
        const progress = Math.min(Math.max(ms / 3400, 0), 1);

        if (progress <= 0.52) return 320 * bezier(progress / 0.52);
        if (progress <= 0.78) return 320 + (29 - 320) * bezier((progress - 0.52) / 0.26);

        return 29;
    };
    const boot = (): CSSAnimation | undefined =>
        document
            .querySelector('.loader')
            ?.getAnimations()
            .find((animation): animation is CSSAnimation => (animation as CSSAnimation).animationName === 'boot');
    const startedAt = performance.now();
    let last = -1;

    loaderSpeed.dataset.live = '';

    const tick = (now: number): void => {
        const current = boot()?.currentTime;
        const elapsed = typeof current === 'number' ? current : now - startedAt;
        const value = Math.round(speedAt(elapsed));

        if (value !== last) {
            loaderSpeed.textContent = String(value);
            last = value;
        }

        if (elapsed < 3400) requestAnimationFrame(tick);
    };

    requestAnimationFrame(tick);
}

// Défilement inertiel (désactivé si mouvement réduit ou écran tactile).
export let lenis: Lenis | undefined;

if (!reducedMotion && !coarsePointer) {
    lenis = new Lenis({ lerp: 0.1, anchors: true });
    const smooth = lenis;
    root.classList.add('lenis', 'lenis-smooth');
    const frame = (time: number): void => {
        smooth.raf(time);
        requestAnimationFrame(frame);
    };
    requestAnimationFrame(frame);
}

// La hauteur réelle de la navigation flottante (elle peut passer sur deux lignes) décale le début du hero.
const header = document.querySelector<HTMLElement>('.site-header');

if (header && 'ResizeObserver' in window) {
    new ResizeObserver(() => root.style.setProperty('--header-h', `${header.offsetHeight}px`)).observe(header);
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
