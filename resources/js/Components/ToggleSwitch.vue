<script setup lang="ts">
// Interrupteur pour un état activé / désactivé (publié, visible, actif…). Tout le libellé est cliquable ;
// la valeur reste un booléen (v-model), donc le même champ est envoyé au serveur qu'avec une case à cocher.
// Pour une sélection multiple dans une liste, garder une vraie case à cocher.
defineProps<{ disabled?: boolean }>();
const model = defineModel<boolean>({ required: true });
</script>

<template>
  <button
    type="button"
    role="switch"
    class="admin-switch"
    :aria-checked="model"
    :disabled="disabled"
    @click="model = !model"
  >
    <span class="admin-switch-track" aria-hidden="true"><span class="admin-switch-thumb" /></span>
    <span class="admin-switch-label"><slot /></span>
  </button>
</template>
