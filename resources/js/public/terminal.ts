// Terminal interactif du hero. Amélioration progressive : sans JavaScript, le terminal reste l'équivalent
// HTML statique rendu par Laravel. Les réponses n'utilisent que les données publiées embarquées dans la page
// (profil, compétences, projets, parcours, liens publics) : rien n'est inventé et aucune coordonnée privée n'y figure.

type Entry = { title: string; url: string };
type Data = {
    locale: string;
    about: { name: string | null; role: string; summary: string | null; location: string | null; availability: string | null };
    skills: { name: string; items: string[] }[];
    projects: Entry[];
    projectsUrl: string;
    experience: { period: string; title: string; org: string }[];
    links: { label: string; url: string }[];
    alternateUrl: string;
    text: Record<string, string> & { help_desc: Record<string, string> };
};

const MAX_INPUT = 80;
const MAX_HISTORY = 50;
const MAX_SKILLS = 8; // Garde la réponse lisible : le détail complet reste dans la section Compétences.
const MAX_ITEMS = 12;
const MAX_EXCERPT = 280;
const HOME = '~/portfolio';
// Sections dont le contenu vient des données publiées embarquées plutôt que du texte de la page.
const SECTION_COMMANDS: Record<string, string> = { projets: 'projects', experience: 'experience', competences: 'skills', contact: 'contact' };
const USAGE: Record<string, string> = { ls: ' [section]', cd: ' <section>', cat: ' <section>', open: ' <section>', echo: ' <text>', lang: ' fr|en', theme: ' light|dark' };

type Section = { id: string; title: string; element: HTMLElement };

const clean = (value: string | null | undefined): string => (value ?? '').replace(/\s+/g, ' ').trim();

function safeHref(url: string): string | null {
    return /^(https?:\/\/|\/|#)/i.test(url) ? url : null;
}

function init(terminal: HTMLElement): void {
    const script = terminal.querySelector<HTMLScriptElement>('[data-terminal-data]');
    const log = terminal.querySelector<HTMLElement>('[data-terminal-log]');
    const form = terminal.querySelector<HTMLFormElement>('[data-terminal-form]');
    const input = terminal.querySelector<HTMLInputElement>('[data-terminal-input]');
    const idle = terminal.querySelector<HTMLElement>('[data-terminal-idle]');
    const body = terminal.querySelector<HTMLElement>('.terminal__body');

    if (!script || !log || !form || !input || !body) {
        return;
    }

    let data: Data;
    try {
        data = JSON.parse(script.textContent ?? '') as Data;
    } catch {
        return; // Données illisibles : le terminal statique reste en place.
    }

    const t = data.text;
    const root = document.documentElement;
    const history: string[] = [];
    let cursor = 0;
    let draft = '';

    // « Système de fichiers » : chaque section de la page est un dossier, ses titres d'éléments en sont le contenu.
    const sections: Section[] = Array.from(document.querySelectorAll<HTMLElement>('section[id][aria-labelledby]'))
        .filter((element) => !element.contains(terminal))
        .map((element) => ({ id: element.id, title: clean(document.getElementById(element.getAttribute('aria-labelledby') ?? '')?.textContent), element }));
    let cwd: Section | null = null;
    const path = (): string => (cwd ? `${HOME}/${cwd.id}` : HOME);
    const barTitle = terminal.querySelector<HTMLElement>('.terminal__bar span');

    // Résout un argument de chemin : `null` = racine, `undefined` = introuvable.
    function resolve(arg: string): Section | null | undefined {
        const name = arg.toLowerCase().replace(/\/+$/, '').replace(/^(\.\/|~\/portfolio\/|~\/|\/)/, '');
        if (name === '' || name === '~' || name === '..' || name === '~/portfolio') {
            return null;
        }
        if (name === '.') {
            return cwd;
        }

        return sections.find((section) => section.id.toLowerCase() === name);
    }

    const items = (section: Section): string[] =>
        Array.from(new Set(Array.from(section.element.querySelectorAll('h3'), (heading) => clean(heading.textContent)).filter(Boolean))).slice(0, MAX_ITEMS);

    function line(parts: (string | HTMLElement)[], className = 'terminal__out'): HTMLElement {
        const p = document.createElement('p');
        p.className = `terminal__line terminal__reply ${className}`.trim();
        p.append(...parts);
        log!.append(p);

        return p;
    }

    const text = (value: string, className = ''): HTMLElement => {
        const span = document.createElement('span');
        span.className = className;
        span.textContent = value;

        return span;
    };

    function link(label: string, url: string): HTMLElement | string {
        const href = safeHref(url);
        if (!href) {
            return label;
        }
        const a = document.createElement('a');
        a.href = href;
        a.textContent = label;
        if (/^https?:/i.test(href)) {
            a.rel = 'noopener noreferrer';
        }

        return a;
    }

    const labelled = (label: string, value: string): HTMLElement => line([text(label, 'terminal__key'), ` ${value}`]);

    function currentTheme(): 'dark' | 'light' {
        const set = root.dataset.theme;
        if (set === 'dark' || set === 'light') {
            return set;
        }

        return window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
    }

    const missing = (command: string, name: string): void => {
        line([t.no_such.replace(':command', command).replace(':name', name)]);
    };

    // Commandes sur une section : argument obligatoire sauf `fallback`, erreur façon shell si elle n'existe pas.
    function target(command: string, args: string[], fallback?: Section | null): Section | null | undefined {
        if (args.length === 0 && fallback !== undefined) {
            return fallback;
        }
        if (args.length !== 1) {
            line([`${t.usage} ${command}${USAGE[command] ?? ''}`]);

            return undefined;
        }
        const found = resolve(args[0]!);
        if (found === undefined) {
            missing(command, args[0]!);
        }

        return found;
    }

    const commands: Record<string, (args: string[]) => void> = {
        help: () => {
            line([t.help_intro]);
            Object.keys(commands).forEach((name) => {
                line([text(`${name}${USAGE[name] ?? ''}`, 'terminal__key'), ` — ${t.help_desc[name] ?? ''}`]);
            });
        },
        ls: (args) => {
            const section = target('ls', args, cwd);
            if (section === undefined) {
                return;
            }
            if (section === null) {
                const row = line([]);
                row.classList.add('terminal__ls');
                sections.forEach((item) => {
                    const dir = link(`${item.id}/`, `#${item.id}`);
                    if (dir instanceof HTMLElement) {
                        dir.className = 'terminal__dir';
                        dir.title = item.title;
                    }
                    row.append(dir);
                });

                return;
            }
            if (section.id === 'contact') {
                commands.contact!([]);

                return;
            }
            const names = items(section);
            if (names.length === 0) {
                line([t.empty]);
            }
            names.forEach((name) => line([name]));
        },
        cd: (args) => {
            const section = target('cd', args, null);
            if (section === undefined) {
                return;
            }
            cwd = section;
            if (barTitle) {
                barTitle.textContent = path();
            }
        },
        pwd: () => {
            line([path()]);
        },
        cat: (args) => {
            const section = target('cat', args);
            if (section === undefined) {
                return;
            }
            if (section === null) {
                commands.about!([]);

                return;
            }
            line([text(section.title, 'terminal__key')]);
            const delegate = SECTION_COMMANDS[section.id];
            if (delegate) {
                commands[delegate]!([]);

                return;
            }
            const paragraphs = Array.from(section.element.querySelectorAll('p'), (p) => clean(p.textContent)).filter((value) => value.length >= 60);
            if (paragraphs.length === 0) {
                items(section).forEach((name) => line([name]));
            }
            paragraphs.slice(0, 2).forEach((value) => line([value.length > MAX_EXCERPT ? `${value.slice(0, MAX_EXCERPT)}…` : value]));
        },
        open: (args) => {
            const section = target('open', args);
            if (section === undefined) {
                return;
            }
            line([text('→', 'terminal__ok'), ` ${section ? section.title : HOME}`]);
            const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            (section ? section.element : document.documentElement).scrollIntoView({ behavior: reduced ? 'auto' : 'smooth', block: 'start' });
        },
        about: () => {
            const { name, role, summary, location, availability } = data.about;
            if (name) {
                line([name]);
            }
            line([role]);
            if (summary) {
                line([summary]);
            }
            if (location) {
                labelled(t.location, location);
            }
            if (availability) {
                labelled(t.availability, availability);
            }
        },
        skills: () => {
            if (data.skills.length === 0) {
                line([t.empty]);

                return;
            }
            data.skills.forEach((category) => {
                const shown = category.items.slice(0, MAX_SKILLS).join(' · ');
                labelled(category.name, category.items.length > MAX_SKILLS ? `${shown} · …` : shown);
            });
        },
        projects: () => {
            if (data.projects.length === 0) {
                line([t.empty]);

                return;
            }
            data.projects.forEach((project) => line([text('✓', 'terminal__ok'), ' ', link(project.title, project.url)]));
            line([text('→', 'terminal__ok'), ' ', link(t.all_projects, data.projectsUrl)]);
        },
        experience: () => {
            if (data.experience.length === 0) {
                line([t.empty]);

                return;
            }
            data.experience.forEach((item) => line([text(item.period, 'terminal__key'), ' ', [item.title, item.org].filter(Boolean).join(' · ')]));
        },
        contact: () => {
            data.links.forEach((item) => line([text('→', 'terminal__ok'), ' ', link(item.label, item.url)]));
            line([text('→', 'terminal__ok'), ' ', link(t.contact_section, '#contact')]);
        },
        lang: (args) => {
            const target = args[0]?.toLowerCase();
            if (args.length !== 1 || (target !== 'fr' && target !== 'en')) {
                line([t.usage_lang]);

                return;
            }
            if (target === data.locale) {
                line([t.lang_same]);

                return;
            }
            line([t.lang_switching]);
            window.location.assign(data.alternateUrl);
        },
        theme: (args) => {
            const target = args[0]?.toLowerCase();
            if (args.length !== 1 || (target !== 'light' && target !== 'dark')) {
                line([t.usage_theme]);

                return;
            }
            if (currentTheme() !== target) {
                // Le bouton existant applique le thème, l'enregistre et gère la transition.
                const toggle = document.querySelector<HTMLButtonElement>('[data-theme-toggle]');
                if (toggle) {
                    toggle.click();
                } else {
                    root.dataset.theme = target;
                }
            }
            line([t.theme_set.replace(':theme', target)]);
        },
        echo: (args) => {
            line([args.join(' ')]);
        },
        date: () => {
            line([new Date().toLocaleString(data.locale)]);
        },
        whoami: () => {
            line([t.whoami]);
        },
        history: () => {
            history.forEach((entry, index) => line([text(String(index + 1).padStart(3, ' '), 'terminal__key'), `  ${entry}`]));
        },
        clear: () => clearScreen(),
    };

    // Efface tout l'écran, y compris les lignes de démonstration rendues par le serveur.
    function clearScreen(): void {
        body!.querySelectorAll(':scope > .terminal__line').forEach((node) => node.remove());
        log!.replaceChildren();
    }

    function run(raw: string): void {
        const value = raw.replace(/\s+/g, ' ').trim().slice(0, MAX_INPUT);
        if (value === '') {
            line([text('$', 'terminal__prompt')], '');

            return;
        }

        const [first, ...args] = value.split(' ');
        const name = (first ?? '').toLowerCase();
        if (name !== 'clear') {
            line([text('$', 'terminal__prompt'), ` ${value}`], '');
        }
        if (name === 'sudo') {
            line([t.sudo]);

            return;
        }

        const handler = Object.hasOwn(commands, name ?? '') ? commands[name as string] : undefined;
        if (handler) {
            handler(args);
        } else {
            line([t.unknown.replace(':command', value)]);
        }
    }

    // Activation : le terminal devient un vrai élément interactif.
    terminal.removeAttribute('aria-hidden');
    terminal.setAttribute('role', 'group');
    terminal.setAttribute('aria-label', t.label ?? '');
    terminal.classList.add('terminal--live');
    idle?.setAttribute('hidden', '');
    log.removeAttribute('hidden');
    form.removeAttribute('hidden');
    input.maxLength = MAX_INPUT;
    line([t.welcome], 'terminal__out terminal__welcome');

    // Curseur bloc façon terminal : police à chasse fixe, donc la position se déduit du nombre de caractères.
    const cursor_el = terminal.querySelector<HTMLElement>('[data-terminal-cursor]');
    const syncCursor = (): void => {
        const field = input.parentElement;
        if (!cursor_el || !field) {
            return;
        }
        field.style.setProperty('--pos', String(input.selectionStart ?? input.value.length));
        field.style.setProperty('--scroll', String(input.scrollLeft));
    };
    ['input', 'keyup', 'keydown', 'click', 'select', 'focus', 'scroll'].forEach((name) => input.addEventListener(name, () => requestAnimationFrame(syncCursor)));
    input.classList.add('terminal__input--block');
    syncCursor();

    // Fenêtre déplaçable (barre de titre : souris, tactile, flèches) et redimensionnable (poignée native en bas à droite).
    // La propriété `translate` évite tout conflit avec l'animation d'apparition (`transform`).
    const bar = terminal.querySelector<HTMLElement>('.terminal__bar');
    let x = 0;
    let y = 0;
    const place = (nx: number, ny: number): void => {
        const rect = terminal.getBoundingClientRect();
        const docTop = rect.top + window.scrollY - y;
        const docLeft = rect.left - x;
        const minX = 64 - (docLeft + rect.width);
        const maxX = window.innerWidth - 64 - docLeft;
        x = Math.min(maxX, Math.max(minX, nx));
        y = Math.min(document.documentElement.scrollHeight - 48 - docTop, Math.max(-docTop, ny));
        terminal.style.translate = x === 0 && y === 0 ? '' : `${Math.round(x)}px ${Math.round(y)}px`;
        terminal.classList.toggle('terminal--moved', x !== 0 || y !== 0);
    };

    if (bar) {
        bar.setAttribute('role', 'button');
        bar.setAttribute('tabindex', '0');
        bar.setAttribute('aria-label', t.move ?? '');
        let start: { px: number; py: number; x: number; y: number } | null = null;

        bar.addEventListener('pointerdown', (event) => {
            if (event.button !== 0) {
                return;
            }
            start = { px: event.clientX, py: event.clientY, x, y };
            bar.setPointerCapture(event.pointerId);
            terminal.classList.add('terminal--dragging');
        });
        bar.addEventListener('pointermove', (event) => {
            if (start) {
                place(start.x + event.clientX - start.px, start.y + event.clientY - start.py);
            }
        });
        const stop = (): void => {
            start = null;
            terminal.classList.remove('terminal--dragging');
        };
        bar.addEventListener('pointerup', stop);
        bar.addEventListener('pointercancel', stop);
        bar.addEventListener('dblclick', () => place(0, 0));
        bar.addEventListener('keydown', (event) => {
            const step = event.shiftKey ? 48 : 16;
            const moves: Record<string, [number, number]> = { ArrowLeft: [-step, 0], ArrowRight: [step, 0], ArrowUp: [0, -step], ArrowDown: [0, step] };
            if (event.altKey && moves[event.key]) {
                event.preventDefault();
                pin();
                const [mx, my] = moves[event.key]!;
                resize('se', box(), mx, my);
            } else if (event.key === 'Home') {
                event.preventDefault();
                place(0, 0);
            } else if (moves[event.key]) {
                event.preventDefault();
                place(x + moves[event.key]![0], y + moves[event.key]![1]);
            }
        });
    }

    // Redimensionnement par les 4 bords et les 4 coins (poignées invisibles, souris et tactile).
    const MIN_W = 272;
    const MIN_H = 168;
    // Au premier redimensionnement, la fenêtre cesse d'être centrée dans sa colonne : son coin haut-gauche reste en place.
    const pin = (): void => {
        if (terminal.classList.contains('terminal--sized')) {
            return;
        }
        const before = terminal.getBoundingClientRect();
        terminal.classList.add('terminal--sized');
        const after = terminal.getBoundingClientRect();
        place(x + before.left - after.left, y + before.top - after.top);
    };
    // Position et taille de la fenêtre en coordonnées de document.
    type Box = { left: number; top: number; w: number; h: number };
    const box = (): Box => {
        const rect = terminal.getBoundingClientRect();

        return { left: rect.left + window.scrollX, top: rect.top + window.scrollY, w: rect.width, h: rect.height };
    };
    const resize = (dir: string, from: Box, dx: number, dy: number): void => {
        let { left, top, w, h } = from;
        if (dir.includes('e')) {
            w = Math.max(MIN_W, from.w + dx);
        }
        if (dir.includes('w')) {
            w = Math.max(MIN_W, from.w - dx);
            left = from.left + from.w - w;
        }
        if (dir.includes('s')) {
            h = Math.max(MIN_H, from.h + dy);
        }
        if (dir.includes('n')) {
            h = Math.max(MIN_H, from.h - dy);
            top = from.top + from.h - h;
        }
        terminal.style.width = `${Math.round(w)}px`;
        terminal.style.height = `${Math.round(h)}px`;
        terminal.classList.add('terminal--sized');
        // La mise en page (colonne centrée) déplace la fenêtre quand sa taille change : on la recale pour que
        // seuls les bords saisis bougent, le bord opposé reste exactement en place.
        const now = box();
        place(x + left - now.left, y + top - now.top);
    };

    ['n', 's', 'e', 'w', 'ne', 'nw', 'se', 'sw'].forEach((dir) => {
        const handle = document.createElement('span');
        handle.className = `terminal__handle terminal__handle--${dir}`;
        handle.setAttribute('aria-hidden', 'true');
        handle.addEventListener('pointerdown', (event) => {
            if (event.button !== 0) {
                return;
            }
            event.preventDefault();
            pin();
            const start = { px: event.clientX, py: event.clientY, from: box() };
            handle.setPointerCapture(event.pointerId);
            terminal.classList.add('terminal--dragging');
            const move = (e: PointerEvent): void => resize(dir, start.from, e.clientX - start.px, e.clientY - start.py);
            const end = (): void => {
                handle.removeEventListener('pointermove', move);
                handle.removeEventListener('pointerup', end);
                handle.removeEventListener('pointercancel', end);
                terminal.classList.remove('terminal--dragging');
            };
            handle.addEventListener('pointermove', move);
            handle.addEventListener('pointerup', end);
            handle.addEventListener('pointercancel', end);
        });
        terminal.append(handle);
    });

    const scrollToEnd = (): void => {
        body.scrollTop = body.scrollHeight;
    };

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        const value = input.value;
        input.value = '';
        if (value.trim() !== '') {
            history.push(value.trim());
            if (history.length > MAX_HISTORY) {
                history.shift();
            }
        }
        cursor = history.length;
        draft = '';
        run(value);
        scrollToEnd();
    });

    input.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowUp' || event.key === 'ArrowDown') {
            if (history.length === 0) {
                return;
            }
            event.preventDefault();
            if (cursor === history.length) {
                draft = input.value;
            }
            cursor = Math.min(history.length, Math.max(0, cursor + (event.key === 'ArrowUp' ? -1 : 1)));
            input.value = cursor === history.length ? draft : (history[cursor] ?? '');
            input.setSelectionRange(input.value.length, input.value.length);
        } else if (event.key.toLowerCase() === 'c' && event.ctrlKey && !event.metaKey && input.selectionStart === input.selectionEnd) {
            // Comme dans un terminal : interrompt la saisie en cours et rend une invite vierge (avec une sélection, Ctrl+C copie).
            event.preventDefault();
            line([text('$', 'terminal__prompt'), ` ${input.value}^C`], '');
            input.value = '';
            cursor = history.length;
            draft = '';
            syncCursor();
            scrollToEnd();
        } else if (event.key === 'l' && event.ctrlKey) {
            event.preventDefault();
            clearScreen();
        } else if (event.key === 'Tab' && !event.shiftKey && !event.altKey && !event.ctrlKey && !event.metaKey) {
            // Complétion : uniquement quand elle aboutit, sinon Tab quitte le terminal normalement (pas de piège de focus).
            const words = input.value.split(' ');
            const last = (words[words.length - 1] ?? '').toLowerCase();
            const pool = words.length === 1 ? Object.keys(commands) : sections.map((section) => section.id);
            const matches = pool.filter((candidate) => candidate.startsWith(last));
            if (last === '' || matches.length === 0 || input.selectionStart !== input.value.length) {
                return;
            }
            let common = matches[0]!;
            matches.forEach((candidate) => {
                while (!candidate.startsWith(common)) {
                    common = common.slice(0, -1);
                }
            });
            const completed = matches.length === 1 && words.length === 1 ? `${common} ` : common;
            if (completed !== last) {
                event.preventDefault();
                words[words.length - 1] = completed;
                input.value = words.join(' ');
                syncCursor();
            }
        }
    });

    // Un clic dans la fenêtre place le curseur dans le champ, sauf pour sélectionner du texte ou suivre un lien.
    body.addEventListener('click', (event) => {
        const selection = window.getSelection();
        if ((event.target as HTMLElement).closest('a') || (selection && selection.toString() !== '')) {
            return;
        }
        input.focus({ preventScroll: true });
    });
}

const terminal = document.querySelector<HTMLElement>('[data-terminal]');

if (terminal) {
    init(terminal);
}
