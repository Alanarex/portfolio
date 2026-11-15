import { ref } from 'vue';

type Request = { title: string; message?: string; confirmLabel?: string; action: () => void };

// État d'une modale de confirmation : `ask` ouvre, `accept` exécute l'action, `dismiss` annule.
export function useConfirm() {
  const request = ref<Request | null>(null);

  function ask(options: Request): void {
    request.value = options;
  }

  function accept(): void {
    const current = request.value;
    request.value = null;
    current?.action();
  }

  function dismiss(): void {
    request.value = null;
  }

  return { request, ask, accept, dismiss };
}
