// Carte des compétences : la voiture suit le tracé au défilement, avec une inertie douce.
const skillmap = document.querySelector<HTMLElement>('[data-skillmap]');
const mapPath = skillmap?.querySelector<SVGPathElement>('[data-map-path]');

if (skillmap && mapPath) {
    const stops = [...skillmap.querySelectorAll<SVGAElement>('[data-map-stop]')];
    const panels = [...skillmap.querySelectorAll<HTMLElement>('[data-map-panel]')];
    const stage = window.matchMedia('(prefers-reduced-motion: no-preference) and (width > 45rem)');
    const clamp = (value: number, min: number, max: number): number => Math.min(Math.max(value, min), max);
    // Le dernier domaine est atteint avant la fin de la section épinglée : la dernière carte reste affichée un moment
    // au lieu de n'apparaître qu'au moment où l'on quitte la section.
    const TRAVEL_SHARE = 0.8;

    // Position de chaque arrêt le long du tracé (0 à 1), par le point d'échantillonnage le plus proche.
    const total = mapPath.getTotalLength();
    const samples = Array.from({ length: 400 }, (_, index) => {
        const point = mapPath.getPointAtLength((total * index) / 399);
        return { x: point.x, y: point.y, at: index / 399 };
    });
    const fractions = stops.map((stop) => {
        const x = Number(stop.dataset.x);
        const y = Number(stop.dataset.y);
        return samples.reduce((best, sample) =>
            Math.hypot(sample.x - x, sample.y - y) < Math.hypot(best.x - x, best.y - y) ? sample : best,
        ).at;
    });
    const first = fractions[0] ?? 0;
    const last = fractions[fractions.length - 1] ?? 1;

    let smoothed = 0;
    let visible = false;
    let running = false;

    const apply = (progress: number): void => {
        const length = first + (last - first) * progress;
        skillmap.style.setProperty('--p', length.toFixed(4));
        let active = 0;
        fractions.forEach((fraction, index) => {
            if (fraction <= length + 0.003) {
                active = index;
            }
        });
        stops.forEach((stop, index) => {
            stop.classList.toggle('is-active', index === active);
            stop.classList.toggle('is-passed', index < active);
        });
        panels.forEach((panel, index) => {
            panel.classList.toggle('is-active', index === active);
            panel.classList.toggle('is-passed', index < active);
        });
    };

    const target = (): number => {
        const rect = skillmap.getBoundingClientRect();
        const range = (rect.height - window.innerHeight) * TRAVEL_SHARE;
        return range > 0 ? clamp(-rect.top / range, 0, 1) : 1;
    };

    const tick = (): void => {
        if (!visible || !stage.matches) {
            running = false;
            return;
        }
        smoothed += (target() - smoothed) * 0.12;
        apply(smoothed);
        requestAnimationFrame(tick);
    };

    const start = (): void => {
        if (!running && visible && stage.matches) {
            running = true;
            requestAnimationFrame(tick);
        }
    };

    const reset = (): void => {
        if (stage.matches) {
            smoothed = target();
            apply(smoothed);
        } else {
            skillmap.style.removeProperty('--p');
            stops.forEach((stop, index) => {
                stop.classList.toggle('is-active', index === 0);
                stop.classList.remove('is-passed');
            });
            panels.forEach((panel) => {
                panel.classList.add('is-active');
                panel.classList.remove('is-passed');
            });
        }
        start();
    };

    new IntersectionObserver(
        (entries) => {
            visible = entries.some((entry) => entry.isIntersecting);
            start();
        },
        { rootMargin: '200px 0px' },
    ).observe(skillmap);
    stage.addEventListener('change', reset);
    reset();

    // Un clic sur un arrêt fait défiler jusqu'au point où la voiture l'atteint.
    stops.forEach((stop, index) => {
        stop.addEventListener('click', (event) => {
            if (!stage.matches) {
                return;
            }
            event.preventDefault();
            const progress = last > first ? ((fractions[index] ?? first) - first) / (last - first) : 0;
            const top = window.scrollY + skillmap.getBoundingClientRect().top + progress * (skillmap.offsetHeight - window.innerHeight) * TRAVEL_SHARE;
            window.scrollTo({ top, behavior: 'smooth' });
        });
    });
}
