<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import PageHeader from '@admin/Components/PageHeader.vue';
import ConfirmDialog from '@admin/Components/ConfirmDialog.vue';
import { useConfirm } from '@admin/lib/confirm';
import { lifecycleLabel, publicationLabel } from '@admin/lib/labels';
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

const confirm = useConfirm();
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
  confirm.ask({
    title: `Supprimer « ${project.title} » ?`,
    message: 'Le projet et ses médias seront supprimés définitivement.',
    action: () => router.delete(`/dashboard/projects/${project.id}`),
  });
}
</script>

<template>
  <Head title="Projets — Administration" />
  <AdminLayout>
    <PageHeader class="" title="Projets et études de cas" description="Gérez la publication, l’ordre, les études de cas et les médias sans exposer les dépôts privés.">
      <Link href="/dashboard/projects/create" class="admin-button">Créer un projet</Link>
    </PageHeader>

    <section class="mt-8 admin-card admin-panel" aria-labelledby="filters-heading">
      <h2 id="filters-heading" class="font-semibold">Filtres</h2>
      <div class="mt-4 grid gap-4 sm:grid-cols-3">
        <label class="admin-field">Publication
          <select v-model="filters.status" class="admin-input" @change="applyFilters">
            <option value="">Tous</option>
            <option value="draft">Brouillon</option>
            <option value="published">Publié</option>
            <option value="archived">Archivé</option>
          </select>
        </label>
        <label class="admin-field">Mise en avant
          <select v-model="filters.featured" class="admin-input" @change="applyFilters">
            <option value="">Tous</option>
            <option value="yes">En avant</option>
            <option value="no">Secondaire</option>
          </select>
        </label>
        <div class="flex items-end">
          <Link href="/dashboard/projects" class="admin-button admin-button-secondary admin-button-compact">Réinitialiser</Link>
        </div>
      </div>
      <p v-if="!canReorder" class="mt-3 text-sm text-warning">Réinitialisez les filtres pour modifier l’ordre global.</p>
    </section>

    <div v-if="projects.length === 0" class="admin-empty mt-6">Aucun projet ne correspond aux filtres.</div>
    <section v-else class="admin-card mt-6 overflow-hidden" aria-label="Liste des projets">
      <div v-for="(project, index) in projects" :key="project.id" class="flex flex-wrap items-center justify-between gap-4 border-b border-border p-5 last:border-b-0">
        <div>
          <div class="flex flex-wrap items-center gap-2">
            <h2 class="font-semibold">{{ project.title }}</h2>
            <span class="admin-badge">{{ publicationLabel(project.publication_status) }}</span>
            <span v-if="project.is_featured" class="admin-badge admin-badge-accent">En avant</span>
            <span v-if="project.deletion_pending" class="admin-badge admin-badge-warning">Suppression à réessayer</span>
          </div>
          <p class="mt-1 text-sm text-muted-foreground">/{{ project.slug }} · {{ lifecycleLabel(project.lifecycle_status) }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
          <button type="button" class="admin-button admin-button-secondary admin-button-icon" :disabled="!canReorder || index === 0" :aria-label="`Monter ${project.title}`" @click="move(index, -1)">↑</button>
          <button type="button" class="admin-button admin-button-secondary admin-button-icon" :disabled="!canReorder || index === projects.length - 1" :aria-label="`Descendre ${project.title}`" @click="move(index, 1)">↓</button>
          <Link :href="`/dashboard/projects/${project.id}/preview`" class="admin-button admin-button-secondary admin-button-compact">Prévisualiser</Link>
          <Link :href="`/dashboard/projects/${project.id}/edit`" class="admin-button admin-button-secondary admin-button-compact">Modifier</Link>
          <button type="button" class="admin-button admin-button-danger admin-button-compact" @click="remove(project)">{{ project.deletion_pending ? 'Réessayer la suppression' : 'Supprimer' }}</button>
        </div>
      </div>
    </section>
    <div v-if="canReorder && projects.length > 1" class="mt-5 flex justify-end">
      <button type="button" class="admin-button" :disabled="orderForm.processing" @click="saveOrder">{{ orderForm.processing ? 'Enregistrement…' : 'Enregistrer l’ordre' }}</button>
    </div>

    <ConfirmDialog :open="confirm.request.value !== null" :title="confirm.request.value?.title ?? ''" :message="confirm.request.value?.message" :confirm-label="confirm.request.value?.confirmLabel" @confirm="confirm.accept" @cancel="confirm.dismiss" />
  </AdminLayout>
</template>
