type Theme = 'dark' | 'light';

const root = document.documentElement;
const buttons = [...document.querySelectorAll<HTMLButtonElement>('[data-theme-choice]')];

function currentTheme(): Theme {
    return root.classList.contains('dark') ? 'dark' : 'light';
}

function applyTheme(theme: Theme, persist = true): void {
    root.classList.toggle('dark', theme === 'dark');
    buttons.forEach((button) => {
        button.setAttribute('aria-pressed', String(button.dataset.themeChoice === theme));
    });

    if (persist) {
        try {
            localStorage.setItem('portfolio-theme', theme);
        } catch {
            // The visual preference remains applied when storage is unavailable.
        }
    }
}

buttons.forEach((button) => {
    button.addEventListener('click', () => {
        const theme = button.dataset.themeChoice;
        if (theme === 'dark' || theme === 'light') {
            applyTheme(theme);
        }
    });
});

applyTheme(currentTheme(), false);
