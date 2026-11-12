<script setup lang="ts">
import { computed, reactive } from 'vue';
import AppSwitch from './AppSwitch.vue';
import FloatingField from './FloatingField.vue';

export interface ProjectSectionDraft {
  title: string;
  body: string;
  metric: string;
}

export interface ProjectDraft {
  title: string;
  type: string;
  summary: string;
  role: string;
  period: string;
  stack: string;
  live_url: string;
  source_url: string;
  published: boolean;
  sections: ProjectSectionDraft[];
}

const props = defineProps<{
  initial?: Partial<ProjectDraft>;
  errors?: Record<string, string>;
  processing?: boolean;
  submitLabel?: string;
}>();

const emit = defineEmits<{ submit: [draft: ProjectDraft]; cancel: [] }>();

const emptySection = (): ProjectSectionDraft => ({ title: '', body: '', metric: '' });

const draft = reactive<ProjectDraft>({
  title: '',
  type: '',
  summary: '',
  role: '',
  period: '',
  stack: '',
  live_url: '',
  source_url: '',
  published: false,
  ...props.initial,
  sections: props.initial?.sections?.length ? props.initial.sections.map((section) => ({ ...section })) : [emptySection()],
});

const stackCount = computed(() => draft.stack.split(',').map((item) => item.trim()).filter(Boolean).length);
const error = (key: string): string | undefined => props.errors?.[key];
const addSection = (): void => {
  draft.sections.push(emptySection());
};
const removeSection = (index: number): void => {
  draft.sections.splice(index, 1);
};
const move = (index: number, direction: -1 | 1): void => {
  const target = index + direction;
  if (target < 0 || target >= draft.sections.length) {
    return;
  }
  const [section] = draft.sections.splice(index, 1);
  draft.sections.splice(target, 0, section);
};
const submit = (): void => emit('submit', { ...draft, sections: draft.sections.map((section) => ({ ...section })) });
</script>

<template>
  <form
    class="project-form"
    novalidate
    @submit.prevent="submit"
  >
    <fieldset
      class="project-form__group"
      :disabled="processing"
    >
      <legend class="eyebrow">
        Identité
      </legend>
      <FloatingField
        id="project-title"
        v-model="draft.title"
        label="Titre du projet"
        required
        :error="error('title')"
      />
      <div class="project-form__row">
        <FloatingField
          id="project-type"
          v-model="draft.type"
          label="Type de projet"
          :error="error('type')"
        />
        <FloatingField
          id="project-role"
          v-model="draft.role"
          label="Rôle"
          :error="error('role')"
        />
        <FloatingField
          id="project-period"
          v-model="draft.period"
          label="Période"
          :error="error('period')"
        />
      </div>
      <FloatingField
        id="project-summary"
        v-model="draft.summary"
        label="Résumé (une ou deux phrases)"
        multiline
        :rows="3"
        :maxlength="280"
        :error="error('summary')"
      />
      <FloatingField
        id="project-stack"
        v-model="draft.stack"
        label="Technologies, séparées par des virgules"
        :error="error('stack')"
      />
      <p class="hint">
        {{ stackCount }} technologie{{ stackCount > 1 ? 's' : '' }}
      </p>
    </fieldset>

    <fieldset
      class="project-form__group"
      :disabled="processing"
    >
      <legend class="eyebrow">
        Liens
      </legend>
      <div class="project-form__row">
        <FloatingField
          id="project-live"
          v-model="draft.live_url"
          type="url"
          label="Site en ligne"
          autocomplete="off"
          :error="error('live_url')"
        />
        <FloatingField
          id="project-source"
          v-model="draft.source_url"
          type="url"
          label="Code source"
          autocomplete="off"
          :error="error('source_url')"
        />
      </div>
    </fieldset>

    <fieldset
      class="project-form__group"
      :disabled="processing"
    >
      <legend class="eyebrow">
        Sections de l’étude de cas
      </legend>
      <ol class="project-form__sections">
        <li
          v-for="(section, index) in draft.sections"
          :key="index"
          class="card project-form__section"
        >
          <div class="project-form__section-head">
            <span class="mono">Section {{ String(index + 1).padStart(2, '0') }}</span>
            <span class="project-form__tools">
              <button
                type="button"
                class="button button--small button--ghost"
                :disabled="index === 0"
                :aria-label="`Monter la section ${index + 1}`"
                @click="move(index, -1)"
              >↑</button>
              <button
                type="button"
                class="button button--small button--ghost"
                :disabled="index === draft.sections.length - 1"
                :aria-label="`Descendre la section ${index + 1}`"
                @click="move(index, 1)"
              >↓</button>
              <button
                type="button"
                class="button button--small button--secondary"
                :disabled="draft.sections.length === 1"
                :aria-label="`Supprimer la section ${index + 1}`"
                @click="removeSection(index)"
              >Supprimer</button>
            </span>
          </div>
          <FloatingField
            :id="`section-${index}-title`"
            v-model="section.title"
            label="Titre de la section"
            :error="error(`sections.${index}.title`)"
          />
          <FloatingField
            :id="`section-${index}-body`"
            v-model="section.body"
            label="Contenu"
            multiline
            :rows="6"
            :error="error(`sections.${index}.body`)"
          />
          <FloatingField
            :id="`section-${index}-metric`"
            v-model="section.metric"
            label="Métrique vérifiée (facultatif)"
            :error="error(`sections.${index}.metric`)"
          />
        </li>
      </ol>
      <button
        type="button"
        class="button button--ghost"
        @click="addSection"
      >
        Ajouter une section
      </button>
      <p class="hint">
        Une métrique n’est publiée que si une preuve existe.
      </p>
    </fieldset>

    <div class="project-form__footer">
      <AppSwitch
        id="project-published"
        v-model="draft.published"
        label="Publié"
        hint="Visible sur le site public. Désactivé : brouillon privé."
        :disabled="processing"
      />
      <span class="project-form__actions">
        <button
          type="button"
          class="button button--ghost"
          :disabled="processing"
          @click="emit('cancel')"
        >
          Annuler
        </button>
        <button
          type="submit"
          class="button"
          :disabled="processing"
          :aria-busy="processing"
        >
          <span
            v-if="processing"
            class="spinner"
            aria-hidden="true"
          />
          <span>{{ submitLabel ?? 'Enregistrer' }}</span>
        </button>
      </span>
    </div>
  </form>
</template>
