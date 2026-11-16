<script setup lang="ts">
import { computed } from 'vue';
import { useSectionGroup } from '@admin/lib/collapsible';

// « Tout déplier / Tout replier » pour les pages à plusieurs sections (à placer sous le PageHeader).
const group = useSectionGroup();
const visible = computed(() => (group?.ids.size ?? 0) > 1);
</script>

<template>
  <div v-if="group && visible" class="admin-collapsible-controls" role="group" aria-label="Sections de la page">
    <button type="button" class="admin-button admin-button-secondary admin-button-compact" @click="group.expandAll()">Tout déplier</button>
    <button type="button" class="admin-button admin-button-secondary admin-button-compact" @click="group.collapseAll()">Tout replier</button>
  </div>
</template>
