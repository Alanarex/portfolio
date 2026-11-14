import type { RoomController, RoomManifest } from './types';

/**
 * Point d'entrée léger de la scène 3D de l'atelier. Ce fichier ne charge pas Three.js : le runtime
 * (`./scene`) n'est importé que lorsque la section approche du viewport ou sur demande explicite.
 * Le contenu essentiel (liste des liens équivalents aux hotspots) reste dans le HTML.
 */
const host = document.querySelector<HTMLElement>('[data-room]');

if (host) {
    void bootstrap(host);
}

function readManifest(root: HTMLElement): RoomManifest | null {
    try {
        const raw = root.querySelector<HTMLScriptElement>('[data-room-manifest]')?.textContent;
        const manifest = raw ? (JSON.parse(raw) as RoomManifest) : null;

        return manifest && Array.isArray(manifest.hotspots) ? manifest : null;
    } catch {
        return null;
    }
}

function supportsWebGL(): boolean {
    try {
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('webgl2') ?? canvas.getContext('webgl');
        context?.getExtension('WEBGL_lose_context')?.loseContext();

        return context !== null;
    } catch {
        return false;
    }
}

async function bootstrap(root: HTMLElement): Promise<void> {
    const manifest = readManifest(root);
    const status = root.querySelector<HTMLElement>('[data-room-status]');
    const loadButton = root.querySelector<HTMLButtonElement>('[data-room-load]');
    const setState = (state: string): void => {
        root.dataset.state = state;
    };
    const unavailable = (): void => {
        setState('unavailable');
        if (status) {
            status.textContent = manifest?.strings.unavailable ?? '';
            status.hidden = false;
        }
        if (loadButton) {
            loadButton.hidden = true;
        }
    };

    if (!manifest) {
        unavailable();

        return;
    }

    const connection = (navigator as Navigator & { connection?: { saveData?: boolean } }).connection;
    const coarse = window.matchMedia('(pointer: coarse)').matches || window.matchMedia('(max-width: 47.99rem)').matches;
    const saveData = connection?.saveData === true;
    const reducedMotion = manifest.reducedMotion === 'always' || window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const nav = navigator as Navigator & { deviceMemory?: number };
    const lowPower =
        manifest.performanceMode === 'low-power' ||
        (manifest.performanceMode === 'auto' && (coarse || saveData || (nav.deviceMemory ?? 8) <= 4 || (navigator.hardwareConcurrency ?? 8) <= 2));
    // Mobile et économiseur de données : le poster reste affiché, la 3D se charge uniquement sur demande.
    const onDemandOnly = coarse || saveData || manifest.performanceMode === 'low-power';

    let controller: RoomController | null = null;
    let loading: Promise<void> | null = null;

    const fail = (): void => {
        controller?.dispose();
        controller = null;
        loading = null;
        unavailable();
    };

    const skip = (): void => {
        controller?.dispose();
        controller = null;
        loading = null;
        setState('poster');
        if (loadButton) {
            loadButton.hidden = false;
        }
    };

    const load = (): Promise<void> => {
        if (loading) {
            return loading;
        }
        if (!supportsWebGL()) {
            fail();

            return Promise.resolve();
        }
        setState('loading');
        if (status) {
            status.textContent = manifest.strings.loading;
            status.hidden = false;
        }
        if (loadButton) {
            loadButton.hidden = true;
        }
        const started = performance.now();
        loading = import('./room')
            .then(({ mountRoomScene }) => {
                controller = mountRoomScene(root, manifest, { reducedMotion, lowPower, onFailure: fail });
                root.querySelector('[data-room-skip]')?.addEventListener('click', skip);
                root.dataset.roomLoadMs = String(Math.round(performance.now() - started));
                setState('ready');
                if (status) {
                    status.hidden = true;
                }
            })
            .catch(fail);

        return loading;
    };

    if (loadButton) {
        loadButton.hidden = !onDemandOnly;
        loadButton.addEventListener('click', () => void load());
    }
    if (lowPower && status && onDemandOnly) {
        status.textContent = manifest.strings.lowPower;
        status.hidden = false;
    }

    if (onDemandOnly) {
        setState('fallback');

        return;
    }

    // Chargement dès que la section approche du viewport (contrôle au défilement en filet de sécurité).
    let observer: IntersectionObserver | undefined;
    const near = (): boolean => {
        const rect = root.getBoundingClientRect();

        return rect.bottom > -300 && rect.top < window.innerHeight + 300;
    };
    const check = (): void => {
        if (near()) {
            stop();
            void load();
        }
    };
    function stop(): void {
        observer?.disconnect();
        window.removeEventListener('scroll', check);
        window.removeEventListener('resize', check);
    }

    if ('IntersectionObserver' in window) {
        observer = new IntersectionObserver((entries) => {
            if (entries.some((entry) => entry.isIntersecting)) {
                stop();
                void load();
            }
        }, { rootMargin: '300px 0px' });
        observer.observe(root);
    }
    window.addEventListener('scroll', check, { passive: true });
    window.addEventListener('resize', check, { passive: true });
    check();
}
