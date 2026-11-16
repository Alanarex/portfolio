import { inject, nextTick, provide, reactive, type InjectionKey } from 'vue';

// Groupe de sections repliables d'une page : état ouvert/fermé mémorisé dans localStorage (par page),
// actions « Tout déplier / Tout replier » et gestion du focus vers le premier champ en erreur.
export type SectionGroup = {
  state: Record<string, boolean>;
  ids: Set<string>;
  isOpen(id: string, fallback: boolean): boolean;
  set(id: string, open: boolean): void;
  register(id: string, fallback: boolean): void;
  unregister(id: string): void;
  expandAll(): void;
  collapseAll(): void;
  focusAfterOpen(target: HTMLElement | null, scope: HTMLElement | null): void;
};

export const sectionGroupKey: InjectionKey<SectionGroup> = Symbol('admin-section-group');

const storagePrefix = 'admin-sections:';

function readStorage(pageKey: string): Record<string, boolean> {
  try {
    const raw = window.localStorage.getItem(storagePrefix + pageKey);
    const parsed: unknown = raw ? JSON.parse(raw) : {};
    if (parsed === null || typeof parsed !== 'object') return {};
    return Object.fromEntries(Object.entries(parsed).filter((entry): entry is [string, boolean] => typeof entry[1] === 'boolean'));
  } catch {
    return {};
  }
}

function writeStorage(pageKey: string, value: Record<string, boolean>): void {
  try {
    window.localStorage.setItem(storagePrefix + pageKey, JSON.stringify(value));
  } catch {
    /* stockage indisponible : l'état reste valable pour la session de la page */
  }
}

export function provideSectionGroup(pageKey: string): SectionGroup {
  const state = reactive<Record<string, boolean>>(typeof window === 'undefined' ? {} : readStorage(pageKey));
  const ids = reactive(new Set<string>());
  const defaults = new Map<string, boolean>();
  let pendingFocus: HTMLElement | null = null;

  const persist = (): void => writeStorage(pageKey, { ...state });

  function setAll(open: boolean): void {
    for (const id of ids) state[id] = open;
    persist();
  }

  const group: SectionGroup = {
    state,
    ids,
    isOpen: (id, fallback) => state[id] ?? fallback,
    set(id, open) {
      state[id] = open;
      persist();
    },
    register(id, fallback) {
      ids.add(id);
      defaults.set(id, fallback);
    },
    unregister(id) {
      ids.delete(id);
      defaults.delete(id);
    },
    expandAll: () => setAll(true),
    collapseAll: () => setAll(false),
    // Plusieurs sections peuvent s'ouvrir d'un coup (erreurs de validation) : seul le champ le plus haut
    // dans la page reçoit le focus.
    focusAfterOpen(target, scope) {
      if (!target) return;
      const candidate = scope?.querySelector<HTMLElement>('[aria-invalid="true"]') ?? target;
      const isFirst = !pendingFocus;
      if (pendingFocus && !(candidate.compareDocumentPosition(pendingFocus) & Node.DOCUMENT_POSITION_FOLLOWING)) return;
      pendingFocus = candidate;
      if (isFirst) {
        void nextTick().then(() => {
          requestAnimationFrame(() => {
            pendingFocus?.focus({ preventScroll: false });
            pendingFocus = null;
          });
        });
      }
    },
  };

  provide(sectionGroupKey, group);
  return group;
}

export function useSectionGroup(): SectionGroup | null {
  return inject(sectionGroupKey, null);
}

// Vrai si une clé d'erreur Inertia (ex. « categories.0.key ») appartient à l'un des préfixes donnés.
export function hasErrorUnder(errors: Record<string, string>, ...prefixes: string[]): boolean {
  return Object.keys(errors).some((key) => prefixes.some((prefix) => key === prefix || key.startsWith(`${prefix}.`)));
}
