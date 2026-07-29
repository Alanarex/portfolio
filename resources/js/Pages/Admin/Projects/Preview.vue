<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@admin/Layouts/AdminLayout.vue';

type Locale = 'fr' | 'en';
type Project = {
  id: number;
  slug: string;
  lifecycle_status: string;
  technologies: string[];
  publication_status: string;
  repository_visibility: string;
  repository_url: string;
  show_repository: boolean;
  demo_url: string;
  show_demo: boolean;
  translations: Record<Locale, { title: string; summary: string; role: string }>;
  sections: Array<{
    type: string;
    is_public: boolean;
    is_verified: boolean;
    translations: Record<Locale, { heading: string; body: string }>;
  }>;
};
type Media = {
  id: number;
  kind: string;
  original_filename: string;
  is_public: boolean;
  translations: Record<Locale, { alt_text: string; caption: string }>;
};

defineProps<{ project: Project; media: Media[] }>();
const locales: Locale[] = ['fr', 'en'];
</script>

<template>
  <Head :title="`Prévisualisation ${project.translations.fr.title || project.slug}`" />
  <AdminLayout>
    <header class="flex flex-wrap items-start justify-between gap-4">
      <div><p class="text-sm font-semibold text-primary">Prévisualisation privée</p><h1 class="mt-1 text-3xl font-bold">{{ project.translations.fr.title || project.slug }}</h1><p class="mt-2 text-sm text-muted-foreground">Cette route authentifiée peut afficher les brouillons. Elle n’est pas un lecteur public.</p></div>
      <Link :href="`/dashboard/projects/${project.id}/edit`" class="admin-button">Modifier</Link>
    </header>
    <div class="mt-6 grid gap-4 rounded-2xl border border-border bg-surface p-5 sm:grid-cols-3">
      <div><p class="text-xs uppercase text-muted-foreground">Publication</p><p class="mt-1 font-bold">{{ project.publication_status }}</p></div>
      <div><p class="text-xs uppercase text-muted-foreground">Cycle</p><p class="mt-1 font-bold">{{ project.lifecycle_status }}</p></div>
      <div><p class="text-xs uppercase text-muted-foreground">Dépôt</p><p class="mt-1 font-bold">{{ project.repository_visibility }} · {{ project.show_repository ? 'visible' : 'masqué' }}</p></div>
    </div>
    <section v-for="locale in locales" :key="locale" class="mt-6 rounded-2xl border border-border bg-surface p-6">
      <p class="text-sm font-semibold text-primary">{{ locale.toUpperCase() }}</p>
      <h2 class="mt-2 text-2xl font-bold">{{ project.translations[locale].title || 'Traduction manquante' }}</h2>
      <p class="mt-2 font-semibold">{{ project.translations[locale].role }}</p>
      <p class="mt-4 whitespace-pre-line text-muted-foreground">{{ project.translations[locale].summary }}</p>
      <div class="mt-5 flex flex-wrap gap-2"><span v-for="technology in project.technologies" :key="technology" class="rounded-full bg-accent px-3 py-1 text-sm">{{ technology }}</span></div>
      <article v-for="section in project.sections" :key="section.type" class="mt-6 border-t border-border pt-5">
        <div class="flex flex-wrap items-center gap-2"><h3 class="text-lg font-bold">{{ section.translations[locale].heading || section.type }}</h3><span class="rounded-full bg-accent px-2 py-1 text-xs">{{ section.is_public ? 'publique' : 'privée' }}</span><span class="rounded-full bg-accent px-2 py-1 text-xs">{{ section.is_verified ? 'vérifiée' : 'non vérifiée' }}</span></div>
        <p class="mt-3 whitespace-pre-line">{{ section.translations[locale].body || 'Contenu manquant' }}</p>
      </article>
    </section>
    <section class="mt-6 rounded-2xl border border-border bg-surface p-6">
      <h2 class="text-2xl font-bold">Médias</h2>
      <p v-if="media.length === 0" class="mt-3 text-muted-foreground">Aucun média.</p>
      <ul v-else class="mt-4 space-y-3"><li v-for="asset in media" :key="asset.id" class="rounded-xl border border-border p-4"><span class="font-bold">{{ asset.original_filename }}</span> · {{ asset.kind }} · {{ asset.is_public ? 'public' : 'privé' }}<p class="mt-2 text-sm text-muted-foreground">{{ asset.translations.fr.alt_text || 'Sans texte alternatif FR' }}</p></li></ul>
    </section>
  </AdminLayout>
</template>
