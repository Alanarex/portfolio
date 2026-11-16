<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { cloneProps } from '@admin/lib/clone';
import { computed, shallowRef, watch } from 'vue';
import ErrorSummary from '@admin/Components/ErrorSummary.vue';
import CollapsibleControls from '@admin/Components/CollapsibleControls.vue';
import CollapsibleSection from '@admin/Components/CollapsibleSection.vue';
import PageHeader from '@admin/Components/PageHeader.vue';
import ToggleSwitch from '@admin/Components/ToggleSwitch.vue';
import ConfirmDialog from '@admin/Components/ConfirmDialog.vue';
import { useConfirm } from '@admin/lib/confirm';
import AdminLayout from '@admin/Layouts/AdminLayout.vue';
import { hasErrorUnder, provideSectionGroup } from '@admin/lib/collapsible';
import { lifecycleLabel, mediaKindLabel, publicationLabel, visibilityLabel } from '@admin/lib/labels';

type Locale = 'fr' | 'en';
type Translation = { title: string; summary: string; role: string; seo_title: string; seo_description: string };
type SectionTranslation = { heading: string; body: string };
type Section = {
  id?: number;
  type: string;
  is_public: boolean;
  is_verified: boolean;
  sort_order: number;
  translations: Record<Locale, SectionTranslation>;
};
type Project = {
  id?: number;
  slug: string;
  lifecycle_status: string;
  technologies: string[];
  skill_keys: string[];
  start_year: number | null;
  start_month: number | null;
  end_year: number | null;
  end_month: number | null;
  is_ongoing: boolean;
  repository_visibility: string;
  repository_url: string;
  show_repository: boolean;
  demo_url: string;
  show_demo: boolean;
  publication_status: string;
  is_featured: boolean;
  featured_order: number | null;
  sort_order: number;
  translations: Record<Locale, Translation>;
  sections: Section[];
};
type Media = {
  id: number;
  uuid: string;
  kind: string;
  original_filename: string;
  mime_type: string;
  size_bytes: number;
  width: number | null;
  height: number | null;
  is_public: boolean;
  sort_order: number;
  deletion_pending: boolean;
  translations: Record<Locale, { alt_text: string; caption: string }>;
};

const confirm = useConfirm();

const props = defineProps<{
  project: Project;
  media: Media[];
  isNew: boolean;
  options: {
    publication_statuses: string[];
    lifecycle_statuses: string[];
    repository_visibilities: string[];
    media_kinds: string[];
    skills: { key: string; name: string; category: string }[];
  };
}>();

const locales: Locale[] = ['fr', 'en'];
const sectionLabels: Record<string, Record<Locale, string>> = {
  context: { fr: 'Contexte', en: 'Context' },
  problem: { fr: 'Problème', en: 'Problem' },
  approach: { fr: 'Approche', en: 'Approach' },
  architecture: { fr: 'Architecture', en: 'Architecture' },
  contribution: { fr: 'Contribution', en: 'Contribution' },
  results: { fr: 'Résultats', en: 'Results' },
  metrics: { fr: 'Métriques', en: 'Metrics' },
  lessons: { fr: 'Enseignements', en: 'Lessons' },
};

const form = useForm(cloneProps(props.project));
const sectionGroup = provideSectionGroup('project-edit');
const sectionKey = (section: Section, index: number): string => `case-${section.id ?? `${section.type}-${index}`}`;
const skillGroups = computed(() => {
  const groups = new Map<string, { category: string; skills: { key: string; name: string }[] }>();
  for (const skill of props.options.skills) {
    if (!groups.has(skill.category)) groups.set(skill.category, { category: skill.category, skills: [] });
    groups.get(skill.category)?.skills.push(skill);
  }
  return [...groups.values()];
});
const createMediaForms = (media: Media[]) => media.map((asset) => useForm(cloneProps(asset)));
const mediaForms = shallowRef(createMediaForms(props.media));
const newMedia = useForm({
  project_id: props.project.id ?? null,
  kind: 'image',
  file: null as File | null,
  is_public: false,
  sort_order: (props.media.length + 1) * 10,
  translations: {
    fr: { alt_text: '', caption: '' },
    en: { alt_text: '', caption: '' },
  },
});

watch(
  () => props.media,
  (media) => {
    mediaForms.value = createMediaForms(media);
    newMedia.sort_order = (media.length + 1) * 10;
  },
  { deep: true },
);

function save(): void {
  if (props.isNew) {
    form.post('/dashboard/projects');
  } else {
    form.put(`/dashboard/projects/${props.project.id}`, { preserveScroll: true });
  }
}

function addTechnology(): void {
  form.technologies.push('');
}

function addSection(): void {
  const type = Object.keys(sectionLabels).find((candidate) => !form.sections.some((section) => section.type === candidate));
  if (!type) return;
  sectionGroup.set(`case-${type}-${form.sections.length}`, true);
  form.sections.push({
    type,
    is_public: false,
    is_verified: false,
    sort_order: (form.sections.length + 1) * 10,
    translations: {
      fr: { heading: sectionLabels[type].fr, body: '' },
      en: { heading: sectionLabels[type].en, body: '' },
    },
  });
}

function selectFile(event: Event): void {
  const input = event.target as HTMLInputElement;
  newMedia.file = input.files?.[0] ?? null;
}

function uploadMedia(): void {
  newMedia.post('/dashboard/media', {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => newMedia.reset('file', 'translations'),
  });
}

function updateMedia(index: number): void {
  const mediaForm = mediaForms.value[index];
  mediaForm.put(`/dashboard/media/${mediaForm.id}`, { preserveScroll: true });
}

function deleteMedia(id: number): void {
  confirm.ask({
    title: 'Supprimer ce média privé ?',
    message: 'Le fichier sera supprimé définitivement.',
    action: () => router.delete(`/dashboard/media/${id}`, { preserveScroll: true }),
  });
}
</script>

<template>
  <Head :title="`${isNew ? 'Créer' : 'Modifier'} un projet — Administration`" />
  <AdminLayout>
    <PageHeader
      :title="isNew ? 'Créer un projet' : `Modifier ${form.translations.fr.title || form.slug}`"
      description="Les slugs sont stables. Les dépôts privés et chemins de stockage ne sont jamais inclus dans les lecteurs publics."
    >
      <Link href="/dashboard/projects" class="admin-button admin-button-secondary">Retour</Link>
      <Link v-if="!isNew" :href="`/dashboard/projects/${project.id}/preview`" class="admin-button">Prévisualiser</Link>
    </PageHeader>

    <ErrorSummary :errors="form.errors" class="mt-6" />

    <form class="mt-8 space-y-6" :aria-busy="form.processing" @submit.prevent="save">
      <div class="flex justify-end"><CollapsibleControls /></div>
      <CollapsibleSection id="core" title="Identité, statut et ordre" default-open :summary="publicationLabel(form.publication_status)" :invalid="hasErrorUnder(form.errors, 'slug', 'lifecycle_status', 'publication_status', 'sort_order', 'is_featured', 'featured_order')">
        <div class="grid gap-5 md:grid-cols-3">
          <label class="admin-field">Slug stable
            <input v-model="form.slug" class="admin-input" required :readonly="!isNew" :aria-invalid="Boolean(form.errors.slug)">
          </label>
          <label class="admin-field">Cycle
            <select v-model="form.lifecycle_status" class="admin-input"><option v-for="status in options.lifecycle_statuses" :key="status" :value="status">{{ lifecycleLabel(status) }}</option></select>
          </label>
          <label class="admin-field">Publication
            <select v-model="form.publication_status" class="admin-input"><option v-for="status in options.publication_statuses" :key="status" :value="status">{{ publicationLabel(status) }}</option></select>
          </label>
          <label class="admin-field">Ordre global
            <input v-model="form.sort_order" type="number" min="0" class="admin-input">
          </label>
          <ToggleSwitch v-model="form.is_featured" class="self-end">Projet en avant</ToggleSwitch>
          <label class="admin-field">Ordre de mise en avant
            <input v-model="form.featured_order" type="number" min="0" class="admin-input" :disabled="!form.is_featured">
          </label>
        </div>
      </CollapsibleSection>

      <CollapsibleSection
        v-for="locale in locales" :id="`locale-${locale}`" :key="locale" :title="`Contenu ${locale.toUpperCase()}`" :default-open="isNew || locale === 'fr'"
        :summary="form.translations[locale].title || 'Vide'" :invalid="hasErrorUnder(form.errors, `translations.${locale}`)"
      >
        <div class="grid gap-5 md:grid-cols-2">
          <label class="admin-field">Titre
            <input v-model="form.translations[locale].title" class="admin-input" maxlength="180">
          </label>
          <label class="admin-field">Rôle
            <input v-model="form.translations[locale].role" class="admin-input" maxlength="180">
          </label>
          <label class="admin-field md:col-span-2">Résumé
            <textarea v-model="form.translations[locale].summary" class="admin-input min-h-28" maxlength="5000" />
          </label>
          <label class="admin-field">Titre SEO
            <input v-model="form.translations[locale].seo_title" class="admin-input" maxlength="180">
          </label>
          <label class="admin-field">Description SEO
            <textarea v-model="form.translations[locale].seo_description" class="admin-input min-h-20" maxlength="320" />
          </label>
        </div>
      </CollapsibleSection>

      <CollapsibleSection id="details" title="Technologies, dates et liens" :default-open="isNew" :summary="`${form.technologies.length} technologies · ${form.skill_keys.length} compétences`" :invalid="hasErrorUnder(form.errors, 'technologies', 'skill_keys', 'start_year', 'start_month', 'end_year', 'end_month', 'is_ongoing', 'repository_visibility', 'repository_url', 'show_repository', 'demo_url', 'show_demo')">
        <div class="grid gap-5 md:grid-cols-4">
          <label class="admin-field">Début — année<input v-model="form.start_year" type="number" min="1950" max="2100" class="admin-input"></label>
          <label class="admin-field">Début — mois<input v-model="form.start_month" type="number" min="1" max="12" class="admin-input"></label>
          <label class="admin-field">Fin — année<input v-model="form.end_year" type="number" min="1950" max="2100" class="admin-input" :disabled="form.is_ongoing"></label>
          <label class="admin-field">Fin — mois<input v-model="form.end_month" type="number" min="1" max="12" class="admin-input" :disabled="form.is_ongoing"></label>
        </div>
        <ToggleSwitch v-model="form.is_ongoing" class="mt-4">Projet en cours</ToggleSwitch>
        <div class="mt-5">
          <div class="flex items-center justify-between gap-3"><h3 class="font-semibold">Technologies vérifiées</h3><button type="button" class="admin-button admin-button-secondary admin-button-compact" @click="addTechnology">Ajouter</button></div>
          <div v-for="(_, index) in form.technologies" :key="index" class="mt-3 flex gap-2"><input v-model="form.technologies[index]" class="admin-input" maxlength="80" :aria-label="`Technologie ${index + 1}`"><button type="button" class="admin-button admin-button-danger admin-button-compact" :aria-label="`Supprimer la technologie ${index + 1}`" @click="form.technologies.splice(index, 1)">Supprimer</button></div>
        </div>
        <fieldset class="mt-5 admin-fieldset">
          <legend class="px-2 text-sm font-semibold">Compétences mobilisées</legend>
          <p class="text-sm text-muted-foreground">Ces compétences relient le projet à la carte « Mes compétences » et à sa page publique.</p>
          <div v-for="group in skillGroups" :key="group.category" class="mt-4">
            <p class="admin-field">{{ group.category }}</p>
            <div class="mt-2 flex flex-wrap gap-x-5 gap-y-1">
              <label v-for="skill in group.skills" :key="skill.key" class="admin-check gap-2 text-sm"><input v-model="form.skill_keys" type="checkbox" :value="skill.key"> {{ skill.name }}</label>
            </div>
          </div>
          <p v-if="options.skills.length === 0" class="mt-3 text-sm text-muted-foreground">Aucune compétence : créez-les d’abord dans « Compétences ».</p>
          <p v-if="form.errors.skill_keys" class="admin-error" role="alert">{{ form.errors.skill_keys }}</p>
        </fieldset>
        <div class="mt-6 grid gap-5 md:grid-cols-2">
          <fieldset class="admin-fieldset">
            <legend class="px-1 font-semibold">Dépôt</legend>
            <label class="admin-field">Visibilité<select v-model="form.repository_visibility" class="admin-input"><option v-for="visibility in options.repository_visibilities" :key="visibility" :value="visibility">{{ visibilityLabel(visibility) }}</option></select></label>
            <label class="admin-field mt-4">URL chiffrée au repos<input v-model="form.repository_url" type="url" class="admin-input"></label>
            <ToggleSwitch v-model="form.show_repository" class="mt-4">Afficher dans le lecteur public</ToggleSwitch>
          </fieldset>
          <fieldset class="admin-fieldset">
            <legend class="px-1 font-semibold">Démo</legend>
            <label class="admin-field">URL<input v-model="form.demo_url" type="url" class="admin-input"></label>
            <ToggleSwitch v-model="form.show_demo" class="mt-4">Afficher dans le lecteur public</ToggleSwitch>
          </fieldset>
        </div>
      </CollapsibleSection>

      <CollapsibleSection id="case-study" title="Étude de cas" description="Une section publique doit être vérifiée et complète en FR/EN." :default-open="isNew" :summary="form.sections.length === 1 ? '1 section' : `${form.sections.length} sections`" :invalid="hasErrorUnder(form.errors, 'sections')">
        <template #actions><button type="button" class="admin-button admin-button-secondary admin-button-compact" :disabled="form.sections.length >= 8" @click="addSection">Ajouter une section</button></template>
        <div v-if="form.sections.length === 0" class="admin-empty">Aucune section.</div>
        <CollapsibleSection
          v-for="(section, index) in form.sections" :id="sectionKey(section, index)" :key="section.id ?? `${section.type}-${index}`" class="mt-4" variant="sub" :level="3"
          :default-open="!section.id" :title="sectionLabels[section.type]?.fr ?? section.type" :summary="section.is_public ? 'Publique' : 'Privée'" :invalid="hasErrorUnder(form.errors, `sections.${index}`)"
        >
          <template #actions>
            <button type="button" class="admin-button admin-button-danger admin-button-compact" @click="form.sections.splice(index, 1)">Supprimer</button>
          </template>
          <div class="grid gap-4 sm:grid-cols-2">
            <ToggleSwitch v-model="section.is_verified">Vérifiée</ToggleSwitch>
            <ToggleSwitch v-model="section.is_public">Publique</ToggleSwitch>
          </div>
          <div class="mt-4 grid gap-4 md:grid-cols-2">
            <fieldset v-for="locale in locales" :key="locale" class="admin-fieldset">
              <legend class="px-1 font-semibold">{{ locale.toUpperCase() }}</legend>
              <label class="admin-field">Titre<input v-model="section.translations[locale].heading" class="admin-input" maxlength="180"></label>
              <label class="admin-field mt-4">Contenu<textarea v-model="section.translations[locale].body" class="admin-input min-h-40" maxlength="20000" /></label>
            </fieldset>
          </div>
        </CollapsibleSection>
      </CollapsibleSection>

      <div class="admin-savebar"><button type="submit" class="admin-button shadow-glow" :disabled="form.processing">{{ form.processing ? 'Enregistrement…' : 'Enregistrer le projet' }}</button></div>
    </form>

    <CollapsibleSection v-if="!isNew" id="media" class="mt-8" title="Médias privés" description="JPEG, PNG et WebP : 8 Mo maximum. PDF : 20 Mo maximum. Aucun nom fourni n’est utilisé comme chemin de stockage." :summary="media.length === 1 ? '1 média' : `${media.length} médias`" :invalid="newMedia.hasErrors || mediaForms.some((asset) => asset.hasErrors)">
      <form class="grid gap-4 admin-card admin-panel md:grid-cols-2" :aria-busy="newMedia.processing" enctype="multipart/form-data" @submit.prevent="uploadMedia">
        <div v-if="Object.keys(newMedia.errors).length" class="admin-banner md:col-span-2" role="alert">{{ Object.values(newMedia.errors).join(' ') }}</div>
        <label class="admin-field">Type<select v-model="newMedia.kind" class="admin-input"><option v-for="kind in options.media_kinds" :key="kind" :value="kind">{{ mediaKindLabel(kind) }}</option></select></label>
        <label class="admin-field">Fichier<input type="file" class="admin-input" accept=".jpg,.jpeg,.png,.webp,.pdf" required @change="selectFile"></label>
        <fieldset v-for="locale in locales" :key="locale" class="admin-fieldset">
          <legend class="px-1 font-semibold">{{ locale.toUpperCase() }}</legend>
          <label class="admin-field">Texte alternatif<input v-model="newMedia.translations[locale].alt_text" class="admin-input" maxlength="240"></label>
          <label class="admin-field mt-4">Légende<textarea v-model="newMedia.translations[locale].caption" class="admin-input min-h-20" maxlength="2000" /></label>
        </fieldset>
        <ToggleSwitch v-model="newMedia.is_public">Préparer comme média public</ToggleSwitch>
        <div class="flex justify-end"><button type="submit" class="admin-button" :disabled="newMedia.processing">{{ newMedia.processing ? 'Téléversement…' : 'Téléverser' }}</button></div>
      </form>

      <form v-for="(asset, index) in mediaForms" :key="asset.id" class="mt-5 admin-card admin-panel" :aria-busy="asset.processing" @submit.prevent="updateMedia(index)">
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div><h3 class="font-semibold">{{ asset.original_filename }}</h3><p class="mt-1 text-sm text-muted-foreground">{{ mediaKindLabel(asset.kind) }} · {{ asset.mime_type }} · {{ asset.size_bytes }} octets<span v-if="asset.width"> · {{ asset.width }}×{{ asset.height }}</span></p></div>
          <div class="flex flex-wrap items-center gap-2">
            <span v-if="asset.deletion_pending" class="admin-badge admin-badge-warning">Suppression à réessayer</span>
            <button type="button" class="admin-button admin-button-danger admin-button-compact" @click="deleteMedia(asset.id)">{{ asset.deletion_pending ? 'Réessayer' : 'Supprimer' }}</button>
          </div>
        </div>
        <div v-if="Object.keys(asset.errors).length" class="mt-4 admin-banner" role="alert">{{ Object.values(asset.errors).join(' ') }}</div>
        <div class="mt-4 grid gap-4 md:grid-cols-2"><fieldset v-for="locale in locales" :key="locale" class="admin-fieldset"><legend class="px-1 font-semibold">{{ locale.toUpperCase() }}</legend><label class="admin-field">Texte alternatif<input v-model="asset.translations[locale].alt_text" class="admin-input" maxlength="240"></label><label class="admin-field mt-4">Légende<textarea v-model="asset.translations[locale].caption" class="admin-input min-h-20" maxlength="2000" /></label></fieldset></div>
        <div class="mt-4 flex flex-wrap items-center justify-between gap-3"><ToggleSwitch v-model="asset.is_public" class="!w-auto">Média public</ToggleSwitch><button type="submit" class="admin-button" :disabled="asset.processing">Enregistrer le média</button></div>
      </form>
    </CollapsibleSection>

    <ConfirmDialog :open="confirm.request.value !== null" :title="confirm.request.value?.title ?? ''" :message="confirm.request.value?.message" :confirm-label="confirm.request.value?.confirmLabel" @confirm="confirm.accept" @cancel="confirm.dismiss" />
  </AdminLayout>
</template>
