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

    if ('IntersectionObserver' in window) {
        const preloadObserver = new IntersectionObserver(
            (entries, observer) => {
                if (entries.some((entry) => entry.isIntersecting)) {
                    observer.disconnect();
                    void loadPreview();
                }
            },
            { rootMargin: '200px 0px' },
        );

        preloadObserver.observe(preview);
    }
}
