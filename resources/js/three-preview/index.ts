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

    // Mobile / économiseur de données : le chargement reste à la demande.
    const connection = (navigator as Navigator & { connection?: { saveData?: boolean } }).connection;
    const onDemandOnly = window.matchMedia('(pointer: coarse)').matches || connection?.saveData === true;

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
