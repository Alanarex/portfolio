const preview = document.querySelector<HTMLElement>('[data-three-preview]');

if (preview) {
    const loadButton = preview.querySelector<HTMLButtonElement>('[data-three-preview-load]');
    let loading: Promise<void> | undefined;

    const loadPreview = (): Promise<void> => {
        if (loading) {
            return loading;
        }

        preview.dataset.state = 'loading';
        loadButton?.setAttribute('aria-busy', 'true');

        loading = import('./scene')
            .then(({ mountThreePreview }) => mountThreePreview(preview))
            .then(() => {
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

    // Économiseur de données ou mouvement réduit : le chargement reste à la demande.
    const connection = (navigator as Navigator & { connection?: { saveData?: boolean } }).connection;
    // Le toucher ne suffit plus à différer la scène : sur téléphone aussi, elle démarre à l'approche de la section.
    const onDemandOnly = window.matchMedia('(prefers-reduced-motion: reduce)').matches || connection?.saveData === true;

    if (!onDemandOnly) {
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
