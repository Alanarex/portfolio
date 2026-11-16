<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, useId, useTemplateRef, ref, watch } from 'vue';
import { useSectionGroup } from '@admin/lib/collapsible';

// Section repliable : toute la barre de titre est un bouton (aria-expanded / aria-controls), le contenu
// se replie par une transition de hauteur (grid 0fr → 1fr) sans saut de page. Le contenu replié est
// `inert` : il sort de l'ordre de tabulation et de l'arbre d'accessibilité.
// L'état est mémorisé par page via le groupe (`provideSectionGroup`) ; sans groupe, il reste local.
const props = withDefaults(
  defineProps<{
    id: string;
    title: string;
    /** Résumé affiché à droite du titre quand la section est repliée (ex. « 12 éléments »). */
    summary?: string;
    /** Sous-titre court toujours visible sous le titre. */
    description?: string;
    /** Ouverte par défaut tant que l'utilisateur n'a rien mémorisé. */
    defaultOpen?: boolean;
    /** Contient une erreur de validation : s'ouvre et donne le focus au premier champ invalide. */
    invalid?: boolean;
    /** Niveau de titre (2 à 4). */
    level?: 2 | 3 | 4;
    /** `card` : carte autonome ; `sub` : sous-section dans une carte ; `plain` : sans cadre. */
    variant?: 'card' | 'sub' | 'plain';
  }>(),
  { summary: undefined, description: undefined, defaultOpen: false, invalid: false, level: 2, variant: 'card' },
);

const group = useSectionGroup();
const localOpen = ref(props.defaultOpen);
const open = computed(() => (group ? group.isOpen(props.id, props.defaultOpen) : localOpen.value));
const bodyId = `${useId()}-body`;
const toggle = useTemplateRef<HTMLButtonElement>('toggle');
const body = useTemplateRef<HTMLElement>('body');

function setOpen(value: boolean): void {
  if (group) group.set(props.id, value);
  else localOpen.value = value;
}

group?.register(props.id, props.defaultOpen);
onBeforeUnmount(() => group?.unregister(props.id));

watch(
  () => props.invalid,
  (invalid) => {
    if (!invalid) return;
    setOpen(true);
    void nextTick(() => group?.focusAfterOpen(toggle.value, body.value));
  },
  { immediate: true, flush: 'post' },
);

// Validation native (champ `required` vide dans une section repliée) : le navigateur ne peut pas
// focaliser un champ inerte. On ouvre la section puis on rejoue la bulle de validation.
function onInvalid(event: Event): void {
  if (open.value) return;
  const target = event.target as HTMLInputElement;
  setOpen(true);
  void nextTick(() => requestAnimationFrame(() => target.reportValidity?.()));
}

const headingTag = computed(() => `h${props.level}`);
</script>

<template>
  <section
    class="admin-collapsible"
    :class="{ 'admin-card': variant === 'card', 'admin-subcard': variant === 'sub', 'is-open': open }"
    :data-variant="variant"
    :data-open="open"
  >
    <div class="admin-collapsible-header">
      <component :is="headingTag" class="admin-collapsible-heading">
        <button
          ref="toggle"
          type="button"
          class="admin-collapsible-toggle"
          :aria-expanded="open"
          :aria-controls="bodyId"
          @click="setOpen(!open)"
        >
          <svg class="admin-collapsible-chevron" viewBox="0 0 20 20" width="20" height="20" aria-hidden="true" focusable="false"><path d="m6 8 4 4 4-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
          <span class="admin-collapsible-text">
            <span class="admin-collapsible-title">{{ title }}</span>
            <span v-if="description" class="admin-collapsible-description">{{ description }}</span>
          </span>
          <span v-if="summary && !open" class="admin-collapsible-summary">{{ summary }}</span>
        </button>
      </component>
      <div v-if="$slots.actions" class="admin-collapsible-actions"><slot name="actions" /></div>
    </div>
    <div :id="bodyId" class="admin-collapsible-panel" @invalid.capture="onInvalid">
      <div class="admin-collapsible-clip" :inert="!open">
        <div ref="body" class="admin-collapsible-body"><slot /></div>
      </div>
    </div>
  </section>
</template>
