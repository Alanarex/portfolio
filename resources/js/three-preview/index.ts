const preview = document.querySelector<HTMLElement>('[data-three-preview]');

if (preview) {
    const loadButton = preview.querySelector<HTMLButtonElement>('[data-three-preview-load]');
    const loader = preview.querySelector<HTMLElement>('[data-journey-loader]');
    const loaderLabel = preview.querySelector<HTMLElement>('[data-journey-loader-label]');
    const MODEL_URL = '/models/amg-gt-roadster.glb';
    let loading: Promise<void> | undefined;

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
            const total = Number(response.headers.get('content-length')) || 0;
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
                showProgress((received / total) * 0.85);
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
        loading = Promise.all([import('./scene'), fetchModel()])
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

        preloadObserver?.observe(preview);
        window.addEventListener('scroll', check, { passive: true });
        window.addEventListener('resize', check, { passive: true });
        check();
    }
}
