<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import PageHeader from '@admin/Components/PageHeader.vue';
import ConfirmDialog from '@admin/Components/ConfirmDialog.vue';
import { useConfirm } from '@admin/lib/confirm';
import AdminLayout from '@admin/Layouts/AdminLayout.vue';

type Locale = 'fr' | 'en';
type Status = 'draft' | 'published';
type Post = { id: number; locale: Locale; body: string; link_url: string; link_label: string; status: Status; published_at: string | null };

defineProps<{ posts: Post[] }>();

const confirm = useConfirm();
const statusLabels: Record<Status, string> = { draft: 'Brouillon', published: 'Publié' };
const editingId = ref<number | null>(null);
const form = useForm({ locale: 'fr' as Locale, body: '', link_url: '', link_label: '', status: 'published' as Status });

function edit(post: Post): void {
  editingId.value = post.id;
  form.clearErrors();
  form.locale = post.locale;
  form.body = post.body;
  form.link_url = post.link_url;
  form.link_label = post.link_label;
  form.status = post.status;
  document.getElementById('post-body')?.focus();
}

function reset(): void {
  editingId.value = null;
  form.reset();
  form.clearErrors();
}

function save(): void {
  const options = { preserveScroll: true, onSuccess: () => reset() };
  if (editingId.value === null) {
    form.post('/dashboard/posts', options);
  } else {
    form.put(`/dashboard/posts/${editingId.value}`, options);
  }
}

function remove(post: Post): void {
  confirm.ask({
    title: 'Supprimer cette publication ?',
    message: 'Cette action est définitive.',
    action: () => router.delete(`/dashboard/posts/${post.id}`, { preserveScroll: true, onSuccess: () => { if (editingId.value === post.id) reset(); } }),
  });
}

function formatDate(value: string | null): string {
  return value === null ? 'Non publiée' : new Date(value).toLocaleString('fr-FR', { dateStyle: 'medium', timeStyle: 'short' });
}
</script>

<template>
  <Head title="Publications — Administration" />
  <AdminLayout>
    <PageHeader class="mb-8" title="Publications" description="Courtes actualités affichées dans la section « Publications » de l’accueil. Texte brut uniquement : les retours à la ligne sont conservés, le HTML n’est pas interprété." />

    <form class="space-y-4 admin-card admin-panel" :aria-busy="form.processing" aria-labelledby="post-form-title" @submit.prevent="save">
      <h2 id="post-form-title" class="text-xl font-semibold">{{ editingId === null ? 'Nouvelle publication' : 'Modifier la publication' }}</h2>
      <label class="block text-sm font-semibold">Texte<textarea id="post-body" v-model="form.body" class="admin-input min-h-40" required maxlength="3000" :aria-invalid="Boolean(form.errors.body)" :aria-describedby="form.errors.body ? 'post-body-error' : undefined" /><span v-if="form.errors.body" id="post-body-error" class="admin-error" role="alert">{{ form.errors.body }}</span></label>
      <div class="grid gap-4 md:grid-cols-4">
        <label class="admin-field">Langue<select v-model="form.locale" class="admin-input"><option value="fr">Français</option><option value="en">English</option></select></label>
        <label class="admin-field">Statut<select v-model="form.status" class="admin-input"><option v-for="(label, value) in statusLabels" :key="value" :value="value">{{ label }}</option></select></label>
        <label class="admin-field">Lien (https, facultatif)<input v-model="form.link_url" type="url" class="admin-input" :aria-invalid="Boolean(form.errors.link_url)" :aria-describedby="form.errors.link_url ? 'post-link-error' : undefined"><span v-if="form.errors.link_url" id="post-link-error" class="admin-error" role="alert">{{ form.errors.link_url }}</span></label>
        <label class="admin-field">Libellé du lien<input v-model="form.link_label" class="admin-input" maxlength="120"></label>
      </div>
      <div class="flex flex-wrap justify-end gap-3">
        <button v-if="editingId !== null" type="button" class="admin-button admin-button-secondary admin-button-compact" @click="reset">Annuler</button>
        <button type="submit" class="admin-button" :disabled="form.processing">{{ editingId === null ? 'Publier' : 'Enregistrer' }}</button>
      </div>
    </form>

    <section class="mt-8" aria-labelledby="posts-list-title">
      <h2 id="posts-list-title" class="mb-4 text-xl font-semibold">Publications existantes</h2>
      <p v-if="posts.length === 0" class="admin-empty">Aucune publication.</p>
      <ul v-else class="space-y-4">
        <li v-for="post in posts" :key="post.id" class="admin-card admin-panel">
          <p class="text-sm text-muted-foreground">{{ post.locale.toUpperCase() }} · {{ statusLabels[post.status] }} · {{ formatDate(post.published_at) }}</p>
          <p class="mt-2 whitespace-pre-line">{{ post.body }}</p>
          <p v-if="post.link_url" class="mt-2 text-sm"><a :href="post.link_url" class="admin-link underline" rel="noopener noreferrer" target="_blank">{{ post.link_label || post.link_url }}</a></p>
          <div class="mt-4 flex gap-2">
            <button type="button" class="admin-button admin-button-secondary admin-button-compact" @click="edit(post)">Modifier</button>
            <button type="button" class="admin-button admin-button-danger admin-button-compact" @click="remove(post)">Supprimer</button>
          </div>
        </li>
      </ul>
    </section>

    <ConfirmDialog :open="confirm.request.value !== null" :title="confirm.request.value?.title ?? ''" :message="confirm.request.value?.message" :confirm-label="confirm.request.value?.confirmLabel" @confirm="confirm.accept" @cancel="confirm.dismiss" />
  </AdminLayout>
</template>
