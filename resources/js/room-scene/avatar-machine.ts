export type AvatarState = 'LOADING' | 'IDLE_CODING' | 'TURNING_TO_USER' | 'GREETING' | 'RETURNING_TO_CODING';

export interface AvatarMachine {
    readonly state: AvatarState;
    /** La scène est prête : LOADING → IDLE_CODING. */
    ready: () => void;
    /** Clic ou activation clavier : IDLE_CODING → TURNING_TO_USER → GREETING. */
    greet: () => void;
    /** Ferme le message ; sans appel, il se ferme après `greetingMs`. */
    dismiss: () => void;
    dispose: () => void;
}

interface AvatarMachineOptions {
    reducedMotion: boolean;
    onChange: (state: AvatarState, previous: AvatarState) => void;
    turnMs?: number;
    greetingMs?: number;
    returnMs?: number;
}

/**
 * Machine d'état de l'avatar (docs/product/three-d-preview.md). Elle ne connaît ni Three.js ni le DOM :
 * l'animation finale (poses, modèle définitif) se branche sur `onChange` sans changer ce contrat.
 */
export function createAvatarMachine(options: AvatarMachineOptions): AvatarMachine {
    const turnMs = options.reducedMotion ? 0 : (options.turnMs ?? 700);
    const returnMs = options.reducedMotion ? 0 : (options.returnMs ?? 700);
    const greetingMs = options.greetingMs ?? 6000;
    let state: AvatarState = 'LOADING';
    let timer: ReturnType<typeof setTimeout> | undefined;

    const go = (next: AvatarState, after?: { ms: number; to: () => void }): void => {
        const previous = state;
        state = next;
        clearTimeout(timer);
        timer = undefined;
        options.onChange(next, previous);
        if (after) {
            timer = setTimeout(after.to, after.ms);
        }
    };

    const returnToCoding = (): void => {
        go('RETURNING_TO_CODING', { ms: returnMs, to: () => go('IDLE_CODING') });
    };

    return {
        get state() {
            return state;
        },
        ready() {
            if (state === 'LOADING') {
                go('IDLE_CODING');
            }
        },
        greet() {
            if (state !== 'IDLE_CODING') {
                return;
            }
            go('TURNING_TO_USER', { ms: turnMs, to: () => go('GREETING', { ms: greetingMs, to: returnToCoding }) });
        },
        dismiss() {
            if (state === 'GREETING' || state === 'TURNING_TO_USER') {
                returnToCoding();
            }
        },
        dispose() {
            clearTimeout(timer);
            timer = undefined;
        },
    };
}
