<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@admin/Layouts/AdminLayout.vue';

type Locale = 'fr' | 'en';
type Status = 'draft' | 'published';
type Post = { id: number; locale: Locale; body: string; link_url: string; link_label: string; status: Status; published_at: string | null };

defineProps<{ posts: Post[] }>();

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
  if (!window.confirm('Supprimer cette publication ?')) return;
  router.delete(`/dashboard/posts/${post.id}`, { preserveScroll: true, onSuccess: () => { if (editingId.value === post.id) reset(); } });
}

function formatDate(value: string | null): string {
  return value === null ? 'Non publiée' : new Date(value).toLocaleString('fr-FR', { dateStyle: 'medium', timeStyle: 'short' });
}
</script>

<template>
  <Head title="Publications — Administration" />
  <AdminLayout>
    <header class="mb-8"><h1 class="text-3xl font-bold">Publications</h1><p class="mt-2 max-w-3xl text-sm text-muted-foreground">Courtes actualités affichées dans la section « Publications » de l'accueil. Texte brut uniquement : les retours à la ligne sont conservés, le HTML n'est pas interprété.</p></header>

    <form class="space-y-4 rounded-2xl border border-border bg-surface p-5 shadow-card sm:p-6" :aria-busy="form.processing" aria-labelledby="post-form-title" @submit.prevent="save">
      <h2 id="post-form-title" class="text-xl font-bold">{{ editingId === null ? 'Nouvelle publication' : 'Modifier la publication' }}</h2>
      <label class="block text-sm font-semibold">Texte<textarea id="post-body" v-model="form.body" class="admin-input mt-2 min-h-40" required maxlength="3000" :aria-invalid="Boolean(form.errors.body)" :aria-describedby="form.errors.body ? 'post-body-error' : undefined" /><span v-if="form.errors.body" id="post-body-error" class="mt-2 block text-destructive" role="alert">{{ form.errors.body }}</span></label>
      <div class="grid gap-4 md:grid-cols-4">
        <label class="text-sm font-semibold">Langue<select v-model="form.locale" class="admin-input mt-2"><option value="fr">Français</option><option value="en">English</option></select></label>
        <label class="text-sm font-semibold">Statut<select v-model="form.status" class="admin-input mt-2"><option v-for="(label, value) in statusLabels" :key="value" :value="value">{{ label }}</option></select></label>
        <label class="text-sm font-semibold">Lien (https, facultatif)<input v-model="form.link_url" type="url" class="admin-input mt-2" :aria-invalid="Boolean(form.errors.link_url)" :aria-describedby="form.errors.link_url ? 'post-link-error' : undefined"><span v-if="form.errors.link_url" id="post-link-error" class="mt-2 block text-destructive" role="alert">{{ form.errors.link_url }}</span></label>
        <label class="text-sm font-semibold">Libellé du lien<input v-model="form.link_label" class="admin-input mt-2" maxlength="120"></label>
      </div>
      <div class="flex flex-wrap justify-end gap-3">
        <button v-if="editingId !== null" type="button" class="admin-link min-h-11 rounded-md border border-border px-4" @click="reset">Annuler</button>
        <button type="submit" class="admin-button" :disabled="form.processing">{{ editingId === null ? 'Publier' : 'Enregistrer' }}</button>
      </div>
    </form>

    <section class="mt-8" aria-labelledby="posts-list-title">
      <h2 id="posts-list-title" class="mb-4 text-xl font-bold">Publications existantes</h2>
      <p v-if="posts.length === 0" class="rounded-2xl border border-dashed border-border bg-surface p-6 text-sm text-muted-foreground">Aucune publication.</p>
      <ul v-else class="space-y-4">
        <li v-for="post in posts" :key="post.id" class="rounded-2xl border border-border bg-surface p-5 shadow-card">
          <p class="text-sm text-muted-foreground">{{ post.locale.toUpperCase() }} · {{ statusLabels[post.status] }} · {{ formatDate(post.published_at) }}</p>
          <p class="mt-2 whitespace-pre-line">{{ post.body }}</p>
          <p v-if="post.link_url" class="mt-2 text-sm"><a :href="post.link_url" class="admin-link underline" rel="noopener noreferrer" target="_blank">{{ post.link_label || post.link_url }}</a></p>
          <div class="mt-4 flex gap-2">
            <button type="button" class="admin-link min-h-11 rounded-md border border-border px-3" @click="edit(post)">Modifier</button>
            <button type="button" class="min-h-11 rounded-md border border-destructive px-3 text-destructive" @click="remove(post)">Supprimer</button>
          </div>
        </li>
      </ul>
    </section>
  </AdminLayout>
</template>
