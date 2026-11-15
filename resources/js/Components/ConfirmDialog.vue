<script setup lang="ts">
import { nextTick, useId, useTemplateRef, watch } from 'vue';

// Modale de confirmation basée sur <dialog> natif : focus piégé, Échap pour annuler, focus rendu
// à l'élément déclencheur à la fermeture. Le focus initial est sur « Annuler » (action la moins risquée).
const props = defineProps<{ open: boolean; title: string; message?: string; confirmLabel?: string }>();
const emit = defineEmits<{ confirm: []; cancel: [] }>();

const dialog = useTemplateRef<HTMLDialogElement>('dialog');
const cancelButton = useTemplateRef<HTMLButtonElement>('cancelButton');
const titleId = useId();

watch(
  () => props.open,
  async (open) => {
    const element = dialog.value;
    if (!element) return;
    if (open && !element.open) {
      element.showModal();
      await nextTick();
      cancelButton.value?.focus();
    } else if (!open && element.open) {
      element.close();
    }
  },
);
</script>

<template>
  <dialog ref="dialog" class="admin-dialog" :aria-labelledby="titleId" @cancel.prevent="emit('cancel')">
    <h2 :id="titleId" class="text-xl font-semibold">{{ title }}</h2>
    <p v-if="message" class="mt-2 text-sm text-muted-foreground">{{ message }}</p>
    <div class="mt-6 flex flex-wrap justify-end gap-3">
      <button ref="cancelButton" type="button" class="admin-button admin-button-secondary" @click="emit('cancel')">Annuler</button>
      <button type="button" class="admin-button admin-button-danger" @click="emit('confirm')">{{ confirmLabel ?? 'Supprimer' }}</button>
    </div>
  </dialog>
</template>
