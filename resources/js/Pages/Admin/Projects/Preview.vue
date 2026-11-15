<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PageHeader from '@admin/Components/PageHeader.vue';
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
    <PageHeader
      eyebrow="Prévisualisation privée"
      :title="project.translations.fr.title || project.slug"
      description="Cette route authentifiée peut afficher les brouillons. Elle n’est pas un lecteur public."
    >
      <Link :href="`/dashboard/projects/${project.id}/edit`" class="admin-button">Modifier</Link>
    </PageHeader>
    <div class="mt-6 grid gap-4 admin-card admin-panel sm:grid-cols-3">
      <div><p class="text-xs uppercase text-muted-foreground">Publication</p><p class="mt-1 font-semibold">{{ project.publication_status }}</p></div>
      <div><p class="text-xs uppercase text-muted-foreground">Cycle</p><p class="mt-1 font-semibold">{{ project.lifecycle_status }}</p></div>
      <div><p class="text-xs uppercase text-muted-foreground">Dépôt</p><p class="mt-1 font-semibold">{{ project.repository_visibility }} · {{ project.show_repository ? 'visible' : 'masqué' }}</p></div>
    </div>
    <section v-for="locale in locales" :key="locale" class="mt-6 admin-card admin-panel">
      <p class="admin-eyebrow">{{ locale.toUpperCase() }}</p>
      <h2 class="mt-2 text-2xl font-semibold">{{ project.translations[locale].title || 'Traduction manquante' }}</h2>
      <p class="mt-2 font-semibold">{{ project.translations[locale].role }}</p>
      <p class="mt-4 whitespace-pre-line text-muted-foreground">{{ project.translations[locale].summary }}</p>
      <div class="mt-5 flex flex-wrap gap-2"><span v-for="technology in project.technologies" :key="technology" class="admin-badge">{{ technology }}</span></div>
      <article v-for="section in project.sections" :key="section.type" class="mt-6 border-t border-border pt-5">
        <div class="flex flex-wrap items-center gap-2"><h3 class="text-lg font-semibold">{{ section.translations[locale].heading || section.type }}</h3><span class="admin-badge">{{ section.is_public ? 'publique' : 'privée' }}</span><span class="admin-badge">{{ section.is_verified ? 'vérifiée' : 'non vérifiée' }}</span></div>
        <p class="mt-3 whitespace-pre-line">{{ section.translations[locale].body || 'Contenu manquant' }}</p>
      </article>
    </section>
    <section class="mt-6 admin-card admin-panel">
      <h2 class="text-2xl font-semibold">Médias</h2>
      <p v-if="media.length === 0" class="mt-3 text-muted-foreground">Aucun média.</p>
      <ul v-else class="mt-4 space-y-3"><li v-for="asset in media" :key="asset.id" class="admin-subcard"><span class="font-semibold">{{ asset.original_filename }}</span> · {{ asset.kind }} · {{ asset.is_public ? 'public' : 'privé' }}<p class="mt-2 text-sm text-muted-foreground">{{ asset.translations.fr.alt_text || 'Sans texte alternatif FR' }}</p></li></ul>
    </section>
  </AdminLayout>
</template>
