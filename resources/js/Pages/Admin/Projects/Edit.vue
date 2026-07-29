<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { shallowRef, watch } from 'vue';
import AdminLayout from '@admin/Layouts/AdminLayout.vue';

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

const props = defineProps<{
  project: Project;
  media: Media[];
  isNew: boolean;
  options: {
    publication_statuses: string[];
    lifecycle_statuses: string[];
    repository_visibilities: string[];
    media_kinds: string[];
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

const form = useForm(structuredClone(props.project));
const createMediaForms = (media: Media[]) => media.map((asset) => useForm(structuredClone(asset)));
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
  if (window.confirm('Supprimer définitivement ce média privé ?')) {
    router.delete(`/dashboard/media/${id}`, { preserveScroll: true });
  }
}
</script>

<template>
  <Head :title="`${isNew ? 'Créer' : 'Modifier'} un projet — Administration`" />
  <AdminLayout>
    <header class="flex flex-wrap items-start justify-between gap-4">
      <div>
        <p class="text-sm font-semibold text-primary">PORT-007</p>
        <h1 class="mt-1 text-3xl font-bold">{{ isNew ? 'Créer un projet' : `Modifier ${form.translations.fr.title || form.slug}` }}</h1>
        <p class="mt-2 max-w-3xl text-sm text-muted-foreground">Les slugs sont stables. Les dépôts privés et chemins de stockage ne sont jamais inclus dans les lecteurs publics.</p>
      </div>
      <div class="flex gap-2">
        <Link href="/dashboard/projects" class="admin-link min-h-11 rounded-md border border-border px-4 py-3">Retour</Link>
        <Link v-if="!isNew" :href="`/dashboard/projects/${project.id}/preview`" class="admin-button">Prévisualiser</Link>
      </div>
    </header>

    <div v-if="Object.keys(form.errors).length" class="mt-6 rounded-xl border border-destructive/50 bg-destructive/10 p-4 text-sm text-destructive" role="alert">
      <p class="font-semibold">Certains champs sont invalides.</p>
      <ul class="mt-2 list-disc space-y-1 pl-5"><li v-for="(message, path) in form.errors" :key="path"><span class="font-mono">{{ path }}</span> : {{ message }}</li></ul>
    </div>

    <form class="mt-8 space-y-8" :aria-busy="form.processing" @submit.prevent="save">
      <section class="rounded-2xl border border-border bg-surface p-5 shadow-card sm:p-6" aria-labelledby="project-core">
        <h2 id="project-core" class="text-xl font-bold">Identité, statut et ordre</h2>
        <div class="mt-5 grid gap-5 md:grid-cols-3">
          <label class="text-sm font-semibold">Slug stable
            <input v-model="form.slug" class="admin-input mt-2" required :readonly="!isNew" :aria-invalid="Boolean(form.errors.slug)">
          </label>
          <label class="text-sm font-semibold">Cycle
            <select v-model="form.lifecycle_status" class="admin-input mt-2"><option v-for="status in options.lifecycle_statuses" :key="status" :value="status">{{ status }}</option></select>
          </label>
          <label class="text-sm font-semibold">Publication
            <select v-model="form.publication_status" class="admin-input mt-2"><option v-for="status in options.publication_statuses" :key="status" :value="status">{{ status }}</option></select>
          </label>
          <label class="text-sm font-semibold">Ordre global
            <input v-model="form.sort_order" type="number" min="0" class="admin-input mt-2">
          </label>
          <label class="flex min-h-11 items-center gap-3 self-end"><input v-model="form.is_featured" type="checkbox"> Projet Featured</label>
          <label class="text-sm font-semibold">Ordre Featured
            <input v-model="form.featured_order" type="number" min="0" class="admin-input mt-2" :disabled="!form.is_featured">
          </label>
        </div>
      </section>

      <section v-for="locale in locales" :key="locale" class="rounded-2xl border border-border bg-surface p-5 shadow-card sm:p-6" :aria-labelledby="`project-${locale}`">
        <h2 :id="`project-${locale}`" class="text-xl font-bold">{{ locale.toUpperCase() }}</h2>
        <div class="mt-5 grid gap-5 md:grid-cols-2">
          <label class="text-sm font-semibold">Titre
            <input v-model="form.translations[locale].title" class="admin-input mt-2" maxlength="180">
          </label>
          <label class="text-sm font-semibold">Rôle
            <input v-model="form.translations[locale].role" class="admin-input mt-2" maxlength="180">
          </label>
          <label class="text-sm font-semibold md:col-span-2">Résumé
            <textarea v-model="form.translations[locale].summary" class="admin-input mt-2 min-h-28" maxlength="5000" />
          </label>
          <label class="text-sm font-semibold">Titre SEO
            <input v-model="form.translations[locale].seo_title" class="admin-input mt-2" maxlength="180">
          </label>
          <label class="text-sm font-semibold">Description SEO
            <textarea v-model="form.translations[locale].seo_description" class="admin-input mt-2 min-h-20" maxlength="320" />
          </label>
        </div>
      </section>

      <section class="rounded-2xl border border-border bg-surface p-5 shadow-card sm:p-6" aria-labelledby="project-details">
        <h2 id="project-details" class="text-xl font-bold">Technologies, dates et liens</h2>
        <div class="mt-5 grid gap-5 md:grid-cols-4">
          <label class="text-sm font-semibold">Début — année<input v-model="form.start_year" type="number" min="1950" max="2100" class="admin-input mt-2"></label>
          <label class="text-sm font-semibold">Début — mois<input v-model="form.start_month" type="number" min="1" max="12" class="admin-input mt-2"></label>
          <label class="text-sm font-semibold">Fin — année<input v-model="form.end_year" type="number" min="1950" max="2100" class="admin-input mt-2" :disabled="form.is_ongoing"></label>
          <label class="text-sm font-semibold">Fin — mois<input v-model="form.end_month" type="number" min="1" max="12" class="admin-input mt-2" :disabled="form.is_ongoing"></label>
        </div>
        <label class="mt-4 flex min-h-11 items-center gap-3"><input v-model="form.is_ongoing" type="checkbox"> Projet en cours</label>
        <div class="mt-5">
          <div class="flex items-center justify-between gap-3"><h3 class="font-bold">Technologies vérifiées</h3><button type="button" class="admin-link min-h-11 rounded-md border border-border px-3" @click="addTechnology">Ajouter</button></div>
          <div v-for="(_, index) in form.technologies" :key="index" class="mt-3 flex gap-2"><input v-model="form.technologies[index]" class="admin-input" maxlength="80"><button type="button" class="min-h-11 rounded-md border border-destructive px-3 text-destructive" :aria-label="`Supprimer la technologie ${index + 1}`" @click="form.technologies.splice(index, 1)">Supprimer</button></div>
        </div>
        <div class="mt-6 grid gap-5 md:grid-cols-2">
          <fieldset class="rounded-xl border border-border p-4">
            <legend class="px-1 font-bold">Dépôt</legend>
            <label class="text-sm font-semibold">Visibilité<select v-model="form.repository_visibility" class="admin-input mt-2"><option v-for="visibility in options.repository_visibilities" :key="visibility" :value="visibility">{{ visibility }}</option></select></label>
            <label class="mt-4 block text-sm font-semibold">URL chiffrée au repos<input v-model="form.repository_url" type="url" class="admin-input mt-2"></label>
            <label class="mt-4 flex min-h-11 items-center gap-3"><input v-model="form.show_repository" type="checkbox"> Afficher dans le lecteur public</label>
          </fieldset>
          <fieldset class="rounded-xl border border-border p-4">
            <legend class="px-1 font-bold">Démo</legend>
            <label class="text-sm font-semibold">URL<input v-model="form.demo_url" type="url" class="admin-input mt-2"></label>
            <label class="mt-4 flex min-h-11 items-center gap-3"><input v-model="form.show_demo" type="checkbox"> Afficher dans le lecteur public</label>
          </fieldset>
        </div>
      </section>

      <section class="space-y-5" aria-labelledby="case-study-heading">
        <div class="flex items-center justify-between gap-3"><div><h2 id="case-study-heading" class="text-2xl font-bold">Étude de cas</h2><p class="mt-1 text-sm text-muted-foreground">Une section publique doit être vérifiée et complète en FR/EN.</p></div><button type="button" class="admin-link min-h-11 rounded-md border border-border px-3" :disabled="form.sections.length >= 8" @click="addSection">Ajouter une section</button></div>
        <article v-for="(section, index) in form.sections" :key="section.id ?? `${section.type}-${index}`" class="rounded-2xl border border-border bg-surface p-5 shadow-card sm:p-6">
          <div class="flex flex-wrap items-center justify-between gap-3">
            <h3 class="text-lg font-bold">{{ sectionLabels[section.type]?.fr ?? section.type }}</h3>
            <div class="flex flex-wrap gap-4">
              <label class="flex min-h-11 items-center gap-2"><input v-model="section.is_verified" type="checkbox"> Vérifiée</label>
              <label class="flex min-h-11 items-center gap-2"><input v-model="section.is_public" type="checkbox"> Publique</label>
              <button type="button" class="min-h-11 rounded-md border border-destructive px-3 text-destructive" @click="form.sections.splice(index, 1)">Supprimer</button>
            </div>
          </div>
          <div class="mt-4 grid gap-4 md:grid-cols-2">
            <fieldset v-for="locale in locales" :key="locale" class="rounded-xl border border-border p-4">
              <legend class="px-1 font-bold">{{ locale.toUpperCase() }}</legend>
              <label class="text-sm font-semibold">Titre<input v-model="section.translations[locale].heading" class="admin-input mt-2" maxlength="180"></label>
              <label class="mt-4 block text-sm font-semibold">Contenu<textarea v-model="section.translations[locale].body" class="admin-input mt-2 min-h-40" maxlength="20000" /></label>
            </fieldset>
          </div>
        </article>
      </section>

      <div class="sticky bottom-4 flex justify-end"><button type="submit" class="admin-button shadow-glow" :disabled="form.processing">{{ form.processing ? 'Enregistrement…' : 'Enregistrer le projet' }}</button></div>
    </form>

    <section v-if="!isNew" class="mt-12" aria-labelledby="media-heading">
      <h2 id="media-heading" class="text-2xl font-bold">Médias privés</h2>
      <p class="mt-2 text-sm text-muted-foreground">JPEG, PNG et WebP : 8 Mo maximum. PDF : 20 Mo maximum. Aucun nom fourni n’est utilisé comme chemin de stockage.</p>

      <form class="mt-5 grid gap-4 rounded-2xl border border-border bg-surface p-5 md:grid-cols-2" :aria-busy="newMedia.processing" enctype="multipart/form-data" @submit.prevent="uploadMedia">
        <div v-if="Object.keys(newMedia.errors).length" class="rounded-lg border border-destructive/50 bg-destructive/10 p-3 text-sm text-destructive md:col-span-2" role="alert">{{ Object.values(newMedia.errors).join(' ') }}</div>
        <label class="text-sm font-semibold">Type<select v-model="newMedia.kind" class="admin-input mt-2"><option v-for="kind in options.media_kinds" :key="kind" :value="kind">{{ kind }}</option></select></label>
        <label class="text-sm font-semibold">Fichier<input type="file" class="admin-input mt-2" accept=".jpg,.jpeg,.png,.webp,.pdf" required @change="selectFile"></label>
        <fieldset v-for="locale in locales" :key="locale" class="rounded-xl border border-border p-4">
          <legend class="px-1 font-bold">{{ locale.toUpperCase() }}</legend>
          <label class="text-sm font-semibold">Texte alternatif<input v-model="newMedia.translations[locale].alt_text" class="admin-input mt-2" maxlength="240"></label>
          <label class="mt-4 block text-sm font-semibold">Légende<textarea v-model="newMedia.translations[locale].caption" class="admin-input mt-2 min-h-20" maxlength="2000" /></label>
        </fieldset>
        <label class="flex min-h-11 items-center gap-3"><input v-model="newMedia.is_public" type="checkbox"> Préparer comme média public</label>
        <div class="flex justify-end"><button type="submit" class="admin-button" :disabled="newMedia.processing">{{ newMedia.processing ? 'Téléversement…' : 'Téléverser' }}</button></div>
      </form>

      <form v-for="(asset, index) in mediaForms" :key="asset.id" class="mt-5 rounded-2xl border border-border bg-surface p-5" :aria-busy="asset.processing" @submit.prevent="updateMedia(index)">
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div><h3 class="font-bold">{{ asset.original_filename }}</h3><p class="mt-1 text-sm text-muted-foreground">{{ asset.kind }} · {{ asset.mime_type }} · {{ asset.size_bytes }} octets<span v-if="asset.width"> · {{ asset.width }}×{{ asset.height }}</span></p></div>
          <div class="flex flex-wrap items-center gap-2">
            <span v-if="asset.deletion_pending" class="rounded-full bg-warning/15 px-2 py-1 text-xs text-warning">Suppression à réessayer</span>
            <button type="button" class="min-h-11 rounded-md border border-destructive px-3 text-destructive" @click="deleteMedia(asset.id)">{{ asset.deletion_pending ? 'Réessayer' : 'Supprimer' }}</button>
          </div>
        </div>
        <div v-if="Object.keys(asset.errors).length" class="mt-4 rounded-lg border border-destructive/50 bg-destructive/10 p-3 text-sm text-destructive" role="alert">{{ Object.values(asset.errors).join(' ') }}</div>
        <div class="mt-4 grid gap-4 md:grid-cols-2"><fieldset v-for="locale in locales" :key="locale" class="rounded-xl border border-border p-4"><legend class="px-1 font-bold">{{ locale.toUpperCase() }}</legend><label class="text-sm font-semibold">Texte alternatif<input v-model="asset.translations[locale].alt_text" class="admin-input mt-2" maxlength="240"></label><label class="mt-4 block text-sm font-semibold">Légende<textarea v-model="asset.translations[locale].caption" class="admin-input mt-2 min-h-20" maxlength="2000" /></label></fieldset></div>
        <div class="mt-4 flex flex-wrap items-center justify-between gap-3"><label class="flex min-h-11 items-center gap-3"><input v-model="asset.is_public" type="checkbox"> Média public</label><button type="submit" class="admin-button" :disabled="asset.processing">Enregistrer le média</button></div>
      </form>
    </section>
  </AdminLayout>
</template>
