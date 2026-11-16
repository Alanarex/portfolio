<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { cloneProps } from '@admin/lib/clone';
import ErrorSummary from '@admin/Components/ErrorSummary.vue';
import CollapsibleControls from '@admin/Components/CollapsibleControls.vue';
import CollapsibleSection from '@admin/Components/CollapsibleSection.vue';
import PageHeader from '@admin/Components/PageHeader.vue';
import ToggleSwitch from '@admin/Components/ToggleSwitch.vue';
import AdminLayout from '@admin/Layouts/AdminLayout.vue';
import { hasErrorUnder, provideSectionGroup } from '@admin/lib/collapsible';

type Locale = 'fr' | 'en';
type Status = 'draft' | 'published' | 'archived';
type Skill = { id?: number; key: string; status: Status; is_visible: boolean; sort_order: number; translations: Record<Locale, { name: string; description: string }> };
type Category = { id?: number; key: string; status: Status; is_visible: boolean; sort_order: number; translations: Record<Locale, { name: string }>; skills: Skill[] };

const props = defineProps<{ categories: Category[] }>();
const form = useForm({ categories: cloneProps(props.categories) });
const group = provideSectionGroup('skills');
const categoryId = (category: Category, index: number): string => `category-${category.id ?? `new-${index}`}`;
const skillId = (skill: Skill, index: number, category: Category, categoryIndex: number): string => `skill-${skill.id ?? `${categoryId(category, categoryIndex)}-new-${index}`}`;
const skillCount = (category: Category): string => (category.skills.length === 1 ? '1 compétence' : `${category.skills.length} compétences`);
const locales: Locale[] = ['fr', 'en'];
const statusLabels: Record<Status, string> = { draft: 'Brouillon', published: 'Publié', archived: 'Archivé' };

function addCategory(): void {
  group.set(`category-new-${form.categories.length}`, true);
  form.categories.push({
    key: '', status: 'draft', is_visible: false, sort_order: (form.categories.length + 1) * 10,
    translations: { fr: { name: '' }, en: { name: '' } }, skills: [],
  });
}

function addSkill(category: Category, categoryIndex: number): void {
  group.set(skillId({ key: '' } as Skill, category.skills.length, category, categoryIndex), true);
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
    <PageHeader class="mb-8" title="Compétences" description="Classez la taxonomie, contrôlez sa visibilité et publiez uniquement les libellés français et anglais vérifiés." />
    <ErrorSummary :errors="form.errors" class="mb-6" />
    <form class="space-y-6" :aria-busy="form.processing" @submit.prevent="save">
      <div class="flex flex-wrap items-center justify-end gap-3"><CollapsibleControls /><button type="button" class="admin-button" @click="addCategory">Ajouter une catégorie</button></div>
      <div v-if="form.categories.length === 0" class="admin-empty">Aucune catégorie.</div>
      <CollapsibleSection
        v-for="(category, categoryIndex) in form.categories" :id="categoryId(category, categoryIndex)" :key="category.id ?? `new-category-${categoryIndex}`"
        :default-open="!category.id || categoryIndex === 0" :title="`Catégorie ${categoryIndex + 1}${category.translations.fr.name ? ` · ${category.translations.fr.name}` : ''}`"
        :summary="`${skillCount(category)} · ${statusLabels[category.status]}`" :invalid="hasErrorUnder(form.errors, `categories.${categoryIndex}`)"
      >
        <template #actions>
          <button type="button" class="admin-button admin-button-secondary admin-button-icon" :disabled="categoryIndex === 0" :aria-label="`Monter la catégorie ${categoryIndex + 1}`" @click="move(form.categories, categoryIndex, -1)">↑</button><button type="button" class="admin-button admin-button-secondary admin-button-icon" :disabled="categoryIndex === form.categories.length - 1" :aria-label="`Descendre la catégorie ${categoryIndex + 1}`" @click="move(form.categories, categoryIndex, 1)">↓</button><button type="button" class="admin-button admin-button-danger admin-button-compact" @click="form.categories.splice(categoryIndex, 1)">Supprimer</button>
        </template>
        <div class="grid gap-4 md:grid-cols-4">
          <label class="admin-field">Clé stable<input :id="`category-key-${categoryIndex}`" v-model="category.key" class="admin-input" required :aria-invalid="Boolean(form.errors[`categories.${categoryIndex}.key`])" :aria-describedby="form.errors[`categories.${categoryIndex}.key`] ? `category-key-${categoryIndex}-error` : undefined"><span v-if="form.errors[`categories.${categoryIndex}.key`]" :id="`category-key-${categoryIndex}-error`" class="admin-error" role="alert">{{ form.errors[`categories.${categoryIndex}.key`] }}</span></label>
          <label v-for="locale in locales" :key="locale" class="admin-field">Nom {{ locale.toUpperCase() }}<input :id="`category-name-${categoryIndex}-${locale}`" v-model="category.translations[locale].name" class="admin-input" :aria-invalid="Boolean(form.errors[`categories.${categoryIndex}.translations.${locale}.name`])" :aria-describedby="form.errors[`categories.${categoryIndex}.translations.${locale}.name`] ? `category-name-${categoryIndex}-${locale}-error` : undefined"><span v-if="form.errors[`categories.${categoryIndex}.translations.${locale}.name`]" :id="`category-name-${categoryIndex}-${locale}-error`" class="admin-error" role="alert">{{ form.errors[`categories.${categoryIndex}.translations.${locale}.name`] }}</span></label>
          <label class="admin-field">Statut<select v-model="category.status" class="admin-input"><option v-for="(label, value) in statusLabels" :key="value" :value="value">{{ label }}</option></select></label>
          <ToggleSwitch v-model="category.is_visible">Visible</ToggleSwitch>
        </div>
        <div class="mt-6 flex items-center justify-between gap-3 border-t border-border pt-5"><h3 class="font-semibold">Compétences</h3><button type="button" class="admin-button admin-button-secondary admin-button-compact" @click="addSkill(category, categoryIndex)">Ajouter une compétence</button></div>
        <CollapsibleSection
          v-for="(skill, skillIndex) in category.skills" :id="skillId(skill, skillIndex, category, categoryIndex)" :key="skill.id ?? `new-skill-${skillIndex}`" class="mt-4" variant="sub" :level="4"
          :default-open="!skill.id" :title="`Compétence ${skillIndex + 1}${skill.translations.fr.name ? ` · ${skill.translations.fr.name}` : ''}`" :summary="`${statusLabels[skill.status]}${skill.is_visible ? '' : ' · masquée'}`"
          :invalid="hasErrorUnder(form.errors, `categories.${categoryIndex}.skills.${skillIndex}`)"
        >
          <div class="grid gap-4 md:grid-cols-4">
            <label class="admin-field">Clé<input :id="`skill-key-${categoryIndex}-${skillIndex}`" v-model="skill.key" class="admin-input" required :aria-invalid="Boolean(form.errors[`categories.${categoryIndex}.skills.${skillIndex}.key`])" :aria-describedby="form.errors[`categories.${categoryIndex}.skills.${skillIndex}.key`] ? `skill-key-${categoryIndex}-${skillIndex}-error` : undefined"><span v-if="form.errors[`categories.${categoryIndex}.skills.${skillIndex}.key`]" :id="`skill-key-${categoryIndex}-${skillIndex}-error`" class="admin-error" role="alert">{{ form.errors[`categories.${categoryIndex}.skills.${skillIndex}.key`] }}</span></label>
            <label class="admin-field">Statut<select v-model="skill.status" class="admin-input"><option v-for="(label, value) in statusLabels" :key="value" :value="value">{{ label }}</option></select></label>
            <ToggleSwitch v-model="skill.is_visible" class="self-end">Visible</ToggleSwitch>
            <div class="flex gap-2 self-end"><button type="button" class="admin-button admin-button-secondary admin-button-icon" :disabled="skillIndex === 0" :aria-label="`Monter la compétence ${skillIndex + 1}`" @click="move(category.skills, skillIndex, -1)">↑</button><button type="button" class="admin-button admin-button-secondary admin-button-icon" :disabled="skillIndex === category.skills.length - 1" :aria-label="`Descendre la compétence ${skillIndex + 1}`" @click="move(category.skills, skillIndex, 1)">↓</button><button type="button" class="admin-button admin-button-danger admin-button-compact" @click="category.skills.splice(skillIndex, 1)">Supprimer</button></div>
          </div>
          <div class="mt-4 grid gap-4 md:grid-cols-2"><fieldset v-for="locale in locales" :key="locale" class="admin-fieldset"><legend class="px-1 text-sm font-semibold">{{ locale.toUpperCase() }}</legend><label class="admin-field">Nom<input :id="`skill-name-${categoryIndex}-${skillIndex}-${locale}`" v-model="skill.translations[locale].name" class="admin-input" :aria-invalid="Boolean(form.errors[`categories.${categoryIndex}.skills.${skillIndex}.translations.${locale}.name`])" :aria-describedby="form.errors[`categories.${categoryIndex}.skills.${skillIndex}.translations.${locale}.name`] ? `skill-name-${categoryIndex}-${skillIndex}-${locale}-error` : undefined"><span v-if="form.errors[`categories.${categoryIndex}.skills.${skillIndex}.translations.${locale}.name`]" :id="`skill-name-${categoryIndex}-${skillIndex}-${locale}-error`" class="admin-error" role="alert">{{ form.errors[`categories.${categoryIndex}.skills.${skillIndex}.translations.${locale}.name`] }}</span></label><label class="admin-field mt-3">Description<textarea v-model="skill.translations[locale].description" class="admin-input min-h-20" maxlength="2000" /></label></fieldset></div>
        </CollapsibleSection>
      </CollapsibleSection>
      <div class="admin-savebar"><button type="submit" class="admin-button shadow-glow" :disabled="form.processing">{{ form.processing ? 'Enregistrement…' : 'Enregistrer les compétences' }}</button></div>
    </form>
  </AdminLayout>
</template>
