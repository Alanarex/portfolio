<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@admin/Layouts/AdminLayout.vue';

type ProjectRow = {
  id: number;
  slug: string;
  title: string;
  lifecycle_status: string;
  publication_status: string;
  is_featured: boolean;
  featured_order: number | null;
  sort_order: number;
  deletion_pending: boolean;
};

const props = defineProps<{
  projects: ProjectRow[];
  filters: { status: string; featured: string };
}>();

const orderForm = useForm({ ids: props.projects.map((project) => project.id) });
const canReorder = !props.filters.status && !props.filters.featured;

function applyFilters(): void {
  router.get('/dashboard/projects', props.filters, { preserveState: true, replace: true });
}

function move(index: number, direction: -1 | 1): void {
  if (!canReorder) return;
  const target = index + direction;
  if (target < 0 || target >= props.projects.length) return;
  [props.projects[index], props.projects[target]] = [props.projects[target], props.projects[index]];
  orderForm.ids = props.projects.map((project) => project.id);
}

function saveOrder(): void {
  orderForm.put('/dashboard/projects/order', { preserveScroll: true });
}

function remove(project: ProjectRow): void {
  if (window.confirm(`Supprimer définitivement « ${project.title} » et ses médias ?`)) {
    router.delete(`/dashboard/projects/${project.id}`);
  }
}
</script>

<template>
  <Head title="Projets — Administration" />
  <AdminLayout>
    <header class="flex flex-wrap items-start justify-between gap-4">
      <div>
        <p class="text-sm font-semibold text-primary">PORT-007</p>
        <h1 class="mt-1 text-3xl font-bold">Projets et études de cas</h1>
        <p class="mt-2 max-w-3xl text-sm text-muted-foreground">Gérez la publication, l’ordre, les études de cas et les médias sans exposer les dépôts privés.</p>
      </div>
      <Link href="/dashboard/projects/create" class="admin-button">Créer un projet</Link>
    </header>

    <section class="mt-8 rounded-2xl border border-border bg-surface p-5" aria-labelledby="filters-heading">
      <h2 id="filters-heading" class="font-bold">Filtres</h2>
      <div class="mt-4 grid gap-4 sm:grid-cols-3">
        <label class="text-sm font-semibold">Publication
          <select v-model="filters.status" class="admin-input mt-2" @change="applyFilters">
            <option value="">Tous</option>
            <option value="draft">Brouillon</option>
            <option value="published">Publié</option>
            <option value="archived">Archivé</option>
          </select>
        </label>
        <label class="text-sm font-semibold">Mise en avant
          <select v-model="filters.featured" class="admin-input mt-2" @change="applyFilters">
            <option value="">Tous</option>
            <option value="yes">Featured</option>
            <option value="no">Secondaire</option>
          </select>
        </label>
        <div class="flex items-end">
          <Link href="/dashboard/projects" class="admin-link min-h-11 rounded-md border border-border px-4 py-3">Réinitialiser</Link>
        </div>
      </div>
      <p v-if="!canReorder" class="mt-3 text-sm text-warning">Réinitialisez les filtres pour modifier l’ordre global.</p>
    </section>

    <div v-if="projects.length === 0" class="mt-6 rounded-2xl border border-dashed border-border bg-surface p-8 text-sm text-muted-foreground">Aucun projet ne correspond aux filtres.</div>
    <section v-else class="mt-6 overflow-hidden rounded-2xl border border-border bg-surface" aria-label="Liste des projets">
      <div v-for="(project, index) in projects" :key="project.id" class="flex flex-wrap items-center justify-between gap-4 border-b border-border p-5 last:border-b-0">
        <div>
          <div class="flex flex-wrap items-center gap-2">
            <h2 class="font-bold">{{ project.title }}</h2>
            <span class="rounded-full bg-accent px-2 py-1 text-xs">{{ project.publication_status }}</span>
            <span v-if="project.is_featured" class="rounded-full bg-primary/15 px-2 py-1 text-xs text-primary">Featured</span>
            <span v-if="project.deletion_pending" class="rounded-full bg-warning/15 px-2 py-1 text-xs text-warning">Suppression à réessayer</span>
          </div>
          <p class="mt-1 text-sm text-muted-foreground">/{{ project.slug }} · {{ project.lifecycle_status }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
          <button type="button" class="admin-link min-h-11 rounded-md border border-border px-3" :disabled="!canReorder || index === 0" :aria-label="`Monter ${project.title}`" @click="move(index, -1)">↑</button>
          <button type="button" class="admin-link min-h-11 rounded-md border border-border px-3" :disabled="!canReorder || index === projects.length - 1" :aria-label="`Descendre ${project.title}`" @click="move(index, 1)">↓</button>
          <Link :href="`/dashboard/projects/${project.id}/preview`" class="admin-link min-h-11 rounded-md border border-border px-3 py-3">Prévisualiser</Link>
          <Link :href="`/dashboard/projects/${project.id}/edit`" class="admin-link min-h-11 rounded-md border border-border px-3 py-3">Modifier</Link>
          <button type="button" class="min-h-11 rounded-md border border-destructive px-3 text-destructive" @click="remove(project)">{{ project.deletion_pending ? 'Réessayer la suppression' : 'Supprimer' }}</button>
        </div>
      </div>
    </section>
    <div v-if="canReorder && projects.length > 1" class="mt-5 flex justify-end">
      <button type="button" class="admin-button" :disabled="orderForm.processing" @click="saveOrder">{{ orderForm.processing ? 'Enregistrement…' : 'Enregistrer l’ordre' }}</button>
    </div>
  </AdminLayout>
</template>
