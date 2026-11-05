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

// Contact details are kept out of the HTML (see App\Support\ContactObfuscator) and decoded on click.
function decodeContact(payload: string): { h: string; l: string } | null {
    try {
        const binary = atob([...payload].reverse().join(''));
        const bytes = Uint8Array.from(binary, (char) => char.charCodeAt(0));
        const data: unknown = JSON.parse(new TextDecoder().decode(bytes));
        if (
            typeof data === 'object' &&
            data !== null &&
            typeof (data as { h?: unknown }).h === 'string' &&
            typeof (data as { l?: unknown }).l === 'string' &&
            /^(mailto|tel):/.test((data as { h: string }).h)
        ) {
            return data as { h: string; l: string };
        }
    } catch {
        // A malformed payload leaves the button as it is.
    }

    return null;
}

document.querySelectorAll<HTMLButtonElement>('[data-contact-reveal]').forEach((button) => {
    button.addEventListener(
        'click',
        () => {
            const contact = decodeContact(button.dataset.contactReveal ?? '');
            if (contact === null) {
                return;
            }

            const link = document.createElement('a');
            link.className = button.className;
            link.href = contact.h;
            const icon = button.querySelector('i');
            if (icon !== null) {
                link.append(icon.cloneNode(true));
            }
            link.append(document.createTextNode(contact.l));
            button.replaceWith(link);
            link.focus();
        },
        { once: true },
    );
});
