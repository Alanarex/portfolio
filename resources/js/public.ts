import './public/site';
import './public/skillmap';
import './public/contact-form';
import './three-preview/index';
import './room-scene/index';

type Theme = 'dark' | 'light';

const root = document.documentElement;
const toggles = [...document.querySelectorAll<HTMLButtonElement>('[data-theme-toggle]')];

function currentTheme(): Theme {
    const set = root.dataset.theme;
    if (set === 'dark' || set === 'light') {
        return set;
    }

    return window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
}

function syncToggles(theme: Theme): void {
    toggles.forEach((button) => {
        const label = theme === 'dark' ? button.dataset.labelDark : button.dataset.labelLight;
        if (label) {
            button.setAttribute('aria-label', label);
            button.setAttribute('title', label);
        }
        button.setAttribute('aria-pressed', String(theme === 'light'));
    });
}

function applyTheme(theme: Theme): void {
    root.dataset.theme = theme;
    syncToggles(theme);

    try {
        localStorage.setItem('portfolio-theme', theme);
    } catch {
        // The visual preference remains applied when storage is unavailable.
    }
}

type ViewTransitionDocument = Document & {
    startViewTransition?: (update: () => void) => { finished: Promise<unknown> };
};

const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

// Le nouveau thème se déploie en cercle depuis le bouton (View Transitions), sinon changement direct.
function switchTheme(next: Theme, origin: HTMLElement): void {
    const doc = document as ViewTransitionDocument;
    if (reducedMotion || typeof doc.startViewTransition !== 'function') {
        applyTheme(next);
        return;
    }

    const rect = origin.getBoundingClientRect();
    root.style.setProperty('--vt-x', `${rect.left + rect.width / 2}px`);
    root.style.setProperty('--vt-y', `${rect.top + rect.height / 2}px`);
    root.classList.add('theme-vt');
    void doc.startViewTransition(() => applyTheme(next)).finished.finally(() => root.classList.remove('theme-vt'));
}

toggles.forEach((button) => {
    button.addEventListener('click', () => {
        switchTheme(currentTheme() === 'dark' ? 'light' : 'dark', button);
    });
});

// Changement de langue : un rideau balaie l'écran, puis la page dans l'autre langue le révèle.
const langSwitch = document.querySelector<HTMLAnchorElement>('.lang-switch');
const wipe = document.querySelector<HTMLElement>('.lang-wipe');

langSwitch?.addEventListener('click', (event) => {
    if (reducedMotion || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
        return;
    }

    event.preventDefault();
    const target = (langSwitch.getAttribute('hreflang') ?? '').toUpperCase();
    const destination = new URL(langSwitch.href, window.location.href);
    destination.hash = window.location.hash;

    try {
        sessionStorage.setItem('portfolio-wipe', target);
    } catch {
        // Sans stockage, la page suivante apparaît simplement sans rideau.
    }

    const label = wipe?.querySelector('span');
    if (label) {
        label.textContent = target;
    }
    root.classList.add('lang-leaving');
    window.setTimeout(() => window.location.assign(destination.toString()), 420);
});

window.addEventListener('pageshow', (event) => {
    if (event.persisted) {
        root.classList.remove('lang-leaving');
    }
});

syncToggles(currentTheme());

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
