<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@admin/Layouts/AdminLayout.vue';

type Locale = 'fr' | 'en';
type Status = 'draft' | 'published' | 'archived';
type Skill = { id?: number; key: string; status: Status; is_visible: boolean; sort_order: number; translations: Record<Locale, { name: string; description: string }> };
type Category = { id?: number; key: string; status: Status; is_visible: boolean; sort_order: number; translations: Record<Locale, { name: string }>; skills: Skill[] };

const props = defineProps<{ categories: Category[] }>();
const form = useForm({ categories: structuredClone(props.categories) });
const locales: Locale[] = ['fr', 'en'];
const statusLabels: Record<Status, string> = { draft: 'Brouillon', published: 'Publié', archived: 'Archivé' };

function addCategory(): void {
  form.categories.push({
    key: '', status: 'draft', is_visible: false, sort_order: (form.categories.length + 1) * 10,
    translations: { fr: { name: '' }, en: { name: '' } }, skills: [],
  });
}

function addSkill(category: Category): void {
  category.skills.push({
    key: '', status: 'draft', is_visible: false, sort_order: (category.skills.length + 1) * 10,
    translations: { fr: { name: '', description: '' }, en: { name: '', description: '' } },
  });
}

function move<T extends { sort_order: number }>(items: T[], index: number, direction: -1 | 1): void {
  const target = index + direction;
  if (target < 0 || target >= items.length) return;
  [items[index], items[target]] = [items[target], items[index]];
  items.forEach((item, position) => { item.sort_order = (position + 1) * 10; });
}

function save(): void {
  form.put('/dashboard/skills', { preserveScroll: true });
}
</script>

<template>
  <Head title="Compétences — Administration" />
  <AdminLayout>
    <header class="mb-8"><p class="text-sm font-semibold text-primary">PORT-006</p><h1 class="mt-1 text-3xl font-bold">Compétences</h1><p class="mt-2 max-w-3xl text-sm text-muted-foreground">Classez la taxonomie, contrôlez sa visibilité et publiez uniquement les libellés français et anglais vérifiés.</p></header>
    <div v-if="Object.keys(form.errors).length" class="mb-6 rounded-xl border border-destructive/50 bg-destructive/10 p-4 text-sm text-destructive" role="alert"><p class="font-semibold">Certains champs sont invalides.</p><ul class="mt-2 list-disc space-y-1 pl-5"><li v-for="(message, path) in form.errors" :key="path"><span class="font-mono">{{ path }}</span> : {{ message }}</li></ul></div>
    <form class="space-y-6" :aria-busy="form.processing" @submit.prevent="save">
      <div class="flex justify-end"><button type="button" class="admin-button" @click="addCategory">Ajouter une catégorie</button></div>
      <div v-if="form.categories.length === 0" class="rounded-2xl border border-dashed border-border bg-surface p-6 text-sm text-muted-foreground">Aucune catégorie.</div>
      <section v-for="(category, categoryIndex) in form.categories" :key="category.id ?? `new-category-${categoryIndex}`" class="rounded-2xl border border-border bg-surface p-5 shadow-card sm:p-6" :aria-labelledby="`category-${categoryIndex}`">
        <div class="flex flex-wrap justify-between gap-3">
          <h2 :id="`category-${categoryIndex}`" class="text-xl font-bold">Catégorie {{ categoryIndex + 1 }} <span class="text-sm font-normal text-muted-foreground">— {{ statusLabels[category.status] }}</span></h2>
          <div class="flex gap-2"><button type="button" class="admin-link min-h-11 rounded-md border border-border px-3" :disabled="categoryIndex === 0" :aria-label="`Monter la catégorie ${categoryIndex + 1}`" @click="move(form.categories, categoryIndex, -1)">↑</button><button type="button" class="admin-link min-h-11 rounded-md border border-border px-3" :disabled="categoryIndex === form.categories.length - 1" :aria-label="`Descendre la catégorie ${categoryIndex + 1}`" @click="move(form.categories, categoryIndex, 1)">↓</button><button type="button" class="min-h-11 rounded-md border border-destructive px-3 text-destructive" @click="form.categories.splice(categoryIndex, 1)">Supprimer</button></div>
        </div>
        <div class="mt-5 grid gap-4 md:grid-cols-4">
          <label class="text-sm font-semibold">Clé stable<input :id="`category-key-${categoryIndex}`" v-model="category.key" class="admin-input mt-2" required :aria-invalid="Boolean(form.errors[`categories.${categoryIndex}.key`])" :aria-describedby="form.errors[`categories.${categoryIndex}.key`] ? `category-key-${categoryIndex}-error` : undefined"><span v-if="form.errors[`categories.${categoryIndex}.key`]" :id="`category-key-${categoryIndex}-error`" class="mt-2 block text-destructive" role="alert">{{ form.errors[`categories.${categoryIndex}.key`] }}</span></label>
          <label v-for="locale in locales" :key="locale" class="text-sm font-semibold">Nom {{ locale.toUpperCase() }}<input :id="`category-name-${categoryIndex}-${locale}`" v-model="category.translations[locale].name" class="admin-input mt-2" :aria-invalid="Boolean(form.errors[`categories.${categoryIndex}.translations.${locale}.name`])" :aria-describedby="form.errors[`categories.${categoryIndex}.translations.${locale}.name`] ? `category-name-${categoryIndex}-${locale}-error` : undefined"><span v-if="form.errors[`categories.${categoryIndex}.translations.${locale}.name`]" :id="`category-name-${categoryIndex}-${locale}-error`" class="mt-2 block text-destructive" role="alert">{{ form.errors[`categories.${categoryIndex}.translations.${locale}.name`] }}</span></label>
          <label class="text-sm font-semibold">Statut<select v-model="category.status" class="admin-input mt-2"><option v-for="(label, value) in statusLabels" :key="value" :value="value">{{ label }}</option></select></label>
          <label class="flex min-h-11 items-center gap-3"><input v-model="category.is_visible" type="checkbox"> Visible</label>
        </div>
        <div class="mt-6 flex items-center justify-between gap-3 border-t border-border pt-5"><h3 class="font-bold">Compétences</h3><button type="button" class="admin-link min-h-11 rounded-md border border-border px-3" @click="addSkill(category)">Ajouter une compétence</button></div>
        <article v-for="(skill, skillIndex) in category.skills" :key="skill.id ?? `new-skill-${skillIndex}`" class="mt-4 rounded-xl border border-border p-4">
          <div class="grid gap-4 md:grid-cols-4">
            <label class="text-sm font-semibold">Clé<input :id="`skill-key-${categoryIndex}-${skillIndex}`" v-model="skill.key" class="admin-input mt-2" required :aria-invalid="Boolean(form.errors[`categories.${categoryIndex}.skills.${skillIndex}.key`])" :aria-describedby="form.errors[`categories.${categoryIndex}.skills.${skillIndex}.key`] ? `skill-key-${categoryIndex}-${skillIndex}-error` : undefined"><span v-if="form.errors[`categories.${categoryIndex}.skills.${skillIndex}.key`]" :id="`skill-key-${categoryIndex}-${skillIndex}-error`" class="mt-2 block text-destructive" role="alert">{{ form.errors[`categories.${categoryIndex}.skills.${skillIndex}.key`] }}</span></label>
            <label class="text-sm font-semibold">Statut<select v-model="skill.status" class="admin-input mt-2"><option v-for="(label, value) in statusLabels" :key="value" :value="value">{{ label }}</option></select></label>
            <label class="flex min-h-11 items-center gap-3 self-end"><input v-model="skill.is_visible" type="checkbox"> Visible</label>
            <div class="flex gap-2 self-end"><button type="button" class="admin-link min-h-11 rounded-md border border-border px-3" :disabled="skillIndex === 0" :aria-label="`Monter la compétence ${skillIndex + 1}`" @click="move(category.skills, skillIndex, -1)">↑</button><button type="button" class="admin-link min-h-11 rounded-md border border-border px-3" :disabled="skillIndex === category.skills.length - 1" :aria-label="`Descendre la compétence ${skillIndex + 1}`" @click="move(category.skills, skillIndex, 1)">↓</button><button type="button" class="min-h-11 rounded-md border border-destructive px-3 text-destructive" @click="category.skills.splice(skillIndex, 1)">Supprimer</button></div>
          </div>
          <div class="mt-4 grid gap-4 md:grid-cols-2"><fieldset v-for="locale in locales" :key="locale" class="rounded-lg border border-border p-3"><legend class="px-1 text-sm font-bold">{{ locale.toUpperCase() }}</legend><label class="text-sm font-semibold">Nom<input :id="`skill-name-${categoryIndex}-${skillIndex}-${locale}`" v-model="skill.translations[locale].name" class="admin-input mt-2" :aria-invalid="Boolean(form.errors[`categories.${categoryIndex}.skills.${skillIndex}.translations.${locale}.name`])" :aria-describedby="form.errors[`categories.${categoryIndex}.skills.${skillIndex}.translations.${locale}.name`] ? `skill-name-${categoryIndex}-${skillIndex}-${locale}-error` : undefined"><span v-if="form.errors[`categories.${categoryIndex}.skills.${skillIndex}.translations.${locale}.name`]" :id="`skill-name-${categoryIndex}-${skillIndex}-${locale}-error`" class="mt-2 block text-destructive" role="alert">{{ form.errors[`categories.${categoryIndex}.skills.${skillIndex}.translations.${locale}.name`] }}</span></label><label class="mt-3 block text-sm font-semibold">Description<textarea v-model="skill.translations[locale].description" class="admin-input mt-2 min-h-20" maxlength="2000" /></label></fieldset></div>
        </article>
      </section>
      <div class="sticky bottom-4 flex justify-end"><button type="submit" class="admin-button shadow-glow" :disabled="form.processing">{{ form.processing ? 'Enregistrement…' : 'Enregistrer les compétences' }}</button></div>
    </form>
  </AdminLayout>
</template>
