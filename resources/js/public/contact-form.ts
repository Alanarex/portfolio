// Validation côté navigateur du formulaire de contact. Le serveur revalide toujours : ces messages
// n'évitent qu'un aller-retour et reprennent les mêmes règles (nom, e-mail, message d'au moins 10 caractères).
const form = document.querySelector<HTMLFormElement>('[data-contact-form]');

if (form) {
    const fields = {
        name: form.querySelector<HTMLInputElement>('#contact-name'),
        email: form.querySelector<HTMLInputElement>('#contact-email'),
        message: form.querySelector<HTMLTextAreaElement>('#contact-message'),
    };
    const text = form.dataset;
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    const messageFor = (key: keyof typeof fields): string => {
        const value = (fields[key]?.value ?? '').trim();
        if (key === 'name') {
            return value === '' ? (text.msgName ?? '') : '';
        }
        if (key === 'email') {
            if (value === '') {
                return text.msgEmailRequired ?? '';
            }

            return emailPattern.test(value) ? '' : (text.msgEmailInvalid ?? '');
        }
        if (value === '') {
            return text.msgMessage ?? '';
        }

        return value.length < 10 ? (text.msgMessageMin ?? '') : '';
    };

    const show = (key: keyof typeof fields, message: string): void => {
        const input = fields[key];
        const target = form.querySelector<HTMLElement>(`[data-error-for="contact-${key}"]`);
        if (!input || !target) {
            return;
        }
        const wrapper = input.closest('.field');
        target.textContent = message;
        target.hidden = message === '';
        wrapper?.classList.toggle('field--error', message !== '');
        if (message === '') {
            input.removeAttribute('aria-invalid');
            input.removeAttribute('aria-describedby');
        } else {
            input.setAttribute('aria-invalid', 'true');
            input.setAttribute('aria-describedby', target.id);
            // Le message serveur éventuel est remplacé par celui du navigateur.
            form.querySelector<HTMLElement>(`#contact-${key}-error`)?.setAttribute('hidden', '');
        }
    };

    (Object.keys(fields) as Array<keyof typeof fields>).forEach((key) => {
        fields[key]?.addEventListener('blur', () => show(key, messageFor(key)));
        fields[key]?.addEventListener('input', () => {
            if (fields[key]?.getAttribute('aria-invalid') === 'true') {
                show(key, messageFor(key));
            }
        });
    });

    form.addEventListener('submit', (event) => {
        let firstInvalid: HTMLElement | null = null;
        (Object.keys(fields) as Array<keyof typeof fields>).forEach((key) => {
            const message = messageFor(key);
            show(key, message);
            if (message !== '' && firstInvalid === null) {
                firstInvalid = fields[key];
            }
        });
        if (firstInvalid !== null) {
            event.preventDefault();
            (firstInvalid as HTMLElement).focus();
        }
    });
}
