import { lenis } from '../public/site';

const preview = document.querySelector<HTMLElement>('[data-three-preview]');

if (preview) {
    const loadButton = preview.querySelector<HTMLButtonElement>('[data-three-preview-load]');
    const loader = preview.querySelector<HTMLElement>('[data-journey-loader]');
    const loaderLabel = preview.querySelector<HTMLElement>('[data-journey-loader-label]');
    const MODEL_URL = '/models/amg-gt-roadster.glb';
    let loading: Promise<void> | undefined;
    let model: Promise<ArrayBuffer | undefined> | undefined;

    const showProgress = (share: number): void => {
        const percent = Math.round(Math.min(Math.max(share, 0), 1) * 100);
        preview.style.setProperty('--load', `${percent}%`);
        loader?.setAttribute('aria-valuenow', String(percent));
        if (loaderLabel) {
            loaderLabel.textContent = `${(loaderLabel.dataset.label ?? '').replace('…', '')} · ${percent} %`;
        }
    };

    // Le modèle se télécharge en parallèle du code de la scène (et non après), avec une progression réelle.
    const fetchModel = async (): Promise<ArrayBuffer | undefined> => {
        try {
            const response = await fetch(MODEL_URL);
            // Compressé (zstd/gzip) par le serveur : pas de Content-Length fiable, on prend la taille réelle donnée par la page.
            const compressed = response.headers.has('content-encoding');
            const total = (compressed ? 0 : Number(response.headers.get('content-length'))) || Number(preview.dataset.modelBytes) || 0;
            if (!response.ok || !response.body || total === 0) {
                return response.ok ? await response.arrayBuffer() : undefined;
            }
            const reader = response.body.getReader();
            const chunks: Uint8Array[] = [];
            let received = 0;
            for (;;) {
                const { done, value } = await reader.read();
                if (done) {
                    break;
                }
                chunks.push(value);
                received += value.length;
                showProgress(Math.min(received / total, 1) * 0.85);
            }
            const bytes = new Uint8Array(received);
            let offset = 0;
            chunks.forEach((chunk) => {
                bytes.set(chunk, offset);
                offset += chunk.length;
            });
            return bytes.buffer;
        } catch {
            return undefined;
        }
    };

    const loadPreview = (): Promise<void> => {
        if (loading) {
            return loading;
        }

        preview.dataset.state = 'loading';
        loadButton?.setAttribute('aria-busy', 'true');

        showProgress(0);
        model ??= fetchModel();
        loading = Promise.all([import('./scene'), model])
            .then(([{ mountThreePreview }, model]) => {
                showProgress(0.9);
                return mountThreePreview(preview, model);
            })
            .then(() => {
                showProgress(1);
                preview.dataset.state = 'ready';
            })
            .catch(() => {
                preview.dataset.state = 'unavailable';
                preview.querySelector<HTMLElement>('[data-three-preview-status]')?.removeAttribute('hidden');
            })
            .finally(() => {
                loadButton?.removeAttribute('aria-busy');
            });

        return loading;
    };

    // Lien d'ancre qui dépasse le parcours (ex. « Contact » depuis le hero) : saut direct, sans traverser la section.
    // Actif dès le chargement de la page, que la scène soit prête ou non.
    const jumpTo = (top: number): void => {
        if (lenis) {
            lenis.resize();
            lenis.scrollTo(top, { immediate: true, force: true });
        } else {
            window.scrollTo({ top, behavior: 'instant' });
        }
    };
    window.addEventListener(
        'click',
        (event) => {
            const link = (event.target as Element | null)?.closest<HTMLAnchorElement>('a[href*="#"]');
            if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || link.target === '_blank') {
                return;
            }
            const url = new URL(link.href, window.location.href);
            if (url.origin !== window.location.origin || url.pathname !== window.location.pathname || url.hash.length < 2) {
                return;
            }
            const target = document.getElementById(decodeURIComponent(url.hash.slice(1)));
            if (!target) {
                return;
            }
            const rect = preview.getBoundingClientRect();
            const journeyTop = rect.top + window.scrollY;
            const journeyBottom = journeyTop + rect.height;
            const targetTop = target.getBoundingClientRect().top + window.scrollY - (Number.parseFloat(getComputedStyle(target).scrollMarginTop) || 0);
            const here = window.scrollY;
            const crosses = (here < journeyTop + 1 && targetTop >= journeyBottom - 1) || (here > journeyBottom - window.innerHeight - 1 && targetTop <= journeyTop + 1);
            if (!crosses) {
                return;
            }
            event.preventDefault();
            event.stopPropagation();
            history.pushState(null, '', url.hash);
            jumpTo(targetTop);
        },
        true,
    );

    // La section grandit quand la scène est prête : si elle est déjà derrière nous, on compense pour ne pas être ramené en arrière.
    if ('ResizeObserver' in window) {
        let lastHeight = preview.offsetHeight;
        let lastBottom = preview.getBoundingClientRect().bottom;
        const remember = (): void => {
            lastBottom = preview.getBoundingClientRect().bottom;
        };
        window.addEventListener('scroll', remember, { passive: true });
        new ResizeObserver(() => {
            const height = preview.offsetHeight;
            if (height !== lastHeight && lastBottom <= 0) {
                jumpTo(window.scrollY + (height - lastHeight));
            }
            lastHeight = height;
            remember();
        }).observe(preview);
    }

    loadButton?.addEventListener('click', () => void loadPreview());

    // Mobile : « Voir en liste » remplace la scène par des cartes empilées (et inversement). La scène 3D se met en pause.
    const viewButton = preview.querySelector<HTMLButtonElement>('[data-journey-view]');
    if (viewButton) {
        viewButton.hidden = false;
        viewButton.addEventListener('click', () => {
            const list = preview.dataset.view !== 'list';
            if (list) {
                preview.dataset.view = 'list';
            } else {
                delete preview.dataset.view;
            }
            viewButton.setAttribute('aria-pressed', String(list));
            preview.dispatchEvent(new Event('journey-view'));
            if (list) {
                preview.querySelector<HTMLElement>('.journey__steps')?.scrollIntoView({ block: 'nearest' });
            }
        });
    }

    // Seul l'économiseur de données garde le chargement à la demande.
    const connection = (navigator as Navigator & { connection?: { saveData?: boolean } }).connection;
    const onDemandOnly = connection?.saveData === true;
    // Téléphone : la scène se charge toute seule, en arrière-plan, dès que la page est inactive (sans bouton à toucher).
    const phone = window.matchMedia('(width <= 44rem), (pointer: coarse)').matches;

    if (!onDemandOnly) {
        preview.dataset.auto = 'true';
    }

    if (!onDemandOnly && phone) {
        const begin = (): void => {
            const idle = (window as Window & { requestIdleCallback?: (cb: () => void, o?: { timeout: number }) => void }).requestIdleCallback;
            if (idle) {
                idle(() => void loadPreview(), { timeout: 2000 });
            } else {
                window.setTimeout(() => void loadPreview(), 300);
            }
        };
        if (document.readyState === 'complete') {
            begin();
        } else {
            window.addEventListener('load', begin, { once: true });
        }
    } else if (!onDemandOnly) {
        // Chargement dès que la section approche du viewport. Le contrôle au défilement
        // sert de filet si l'IntersectionObserver ne notifie pas (onglet en arrière-plan, webview).
        let scheduled = false;
        const nearViewport = (): boolean => {
            const rect = preview.getBoundingClientRect();
            return rect.bottom > -200 && rect.top < window.innerHeight + 200;
        };
        const stop = (): void => {
            preloadObserver?.disconnect();
            window.removeEventListener('scroll', check);
            window.removeEventListener('resize', check);
        };
        function check(): void {
            if (scheduled) {
                return;
            }
            scheduled = true;
            requestAnimationFrame(() => {
                scheduled = false;
                if (nearViewport()) {
                    stop();
                    void loadPreview();
                }
            });
        }

        const preloadObserver =
            'IntersectionObserver' in window
                ? new IntersectionObserver(
                      (entries) => {
                          if (entries.some((entry) => entry.isIntersecting)) {
                              stop();
                              void loadPreview();
                          }
                      },
                      { rootMargin: '200px 0px' },
                  )
                : undefined;

        // Préchargement : le téléchargement démarre dès que la page est inactive, sans attendre que la section approche.
        // Hors économiseur de données et connexions 2G ; le fichier est ensuite repris tel quel par loadPreview().
        const effective = (navigator as Navigator & { connection?: { effectiveType?: string } }).connection?.effectiveType ?? '';
        if (!/2g/.test(effective)) {
            const warm = (): void => {
                model ??= fetchModel();
            };
            const idle = (window as Window & { requestIdleCallback?: (cb: () => void, o?: { timeout: number }) => void }).requestIdleCallback;
            const schedule = (): void => (idle ? idle(warm, { timeout: 3000 }) : void window.setTimeout(warm, 1000));
            if (document.readyState === 'complete') {
                schedule();
            } else {
                window.addEventListener('load', schedule, { once: true });
            }
        }

        preloadObserver?.observe(preview);
        window.addEventListener('scroll', check, { passive: true });
        window.addEventListener('resize', check, { passive: true });
        check();
    }
}
