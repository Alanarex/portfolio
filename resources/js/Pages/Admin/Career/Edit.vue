<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@admin/Layouts/AdminLayout.vue';

type Locale = 'fr' | 'en';
type Status = 'draft' | 'published' | 'archived';
type Translation = Record<string, string | string[]>;
type Translations = Record<Locale, Translation>;
type Achievement = { id?: number; key: string; status: Status; is_quantified: boolean; is_verified: boolean; sort_order: number; translations: Translations };
type Experience = {
  id?: number; key: string; organization: string; start_year: number; start_month: number | null; end_year: number | null;
  end_month: number | null; is_current: boolean; status: Status; sort_order: number; translations: Translations; achievements: Achievement[];
};
type Education = { id?: number; key: string; institution: string; start_year: number; start_month: number | null; end_year: number | null; end_month: number | null; status: Status; sort_order: number; translations: Translations };
type Certification = { id?: number; key: string; issuer: string; credential_id: string; verification_url: string; issue_year: number; issue_month: number | null; result: string; is_verified: boolean; status: Status; sort_order: number; translations: Translations };
type Language = { id?: number; key: string; language_code: string; proficiency_kind: 'native' | 'bilingual' | 'cefr'; cefr_level: string; status: Status; sort_order: number; translations: Translations };

const props = defineProps<{ career: { experiences: Experience[]; education: Education[]; certifications: Certification[]; languages: Language[] } }>();
const form = useForm(structuredClone(props.career));
const locales: Locale[] = ['fr', 'en'];
const statusLabels: Record<Status, string> = { draft: 'Brouillon', published: 'Publié', archived: 'Archivé' };

function emptyTranslations(fields: string[]): Translations {
  return Object.fromEntries(locales.map((locale) => [locale, Object.fromEntries(fields.map((field) => [field, field === 'highlights' ? [] : '']))])) as Translations;
}

function addExperience(): void {
  form.experiences.push({
    key: '', organization: '', start_year: new Date().getFullYear(), start_month: null, end_year: null, end_month: null,
    is_current: false, status: 'draft', sort_order: (form.experiences.length + 1) * 10,
    translations: emptyTranslations(['role', 'employment_type', 'location', 'summary', 'highlights']), achievements: [],
  });
}

function addAchievement(experience: Experience): void {
  experience.achievements.push({
    key: '', status: 'draft', is_quantified: false, is_verified: false, sort_order: (experience.achievements.length + 1) * 10,
    translations: emptyTranslations(['statement']),
  });
}

function addEducation(): void {
  form.education.push({
    key: '', institution: '', start_year: new Date().getFullYear(), start_month: null, end_year: null, end_month: null,
    status: 'draft', sort_order: (form.education.length + 1) * 10, translations: emptyTranslations(['program', 'level', 'location', 'summary']),
  });
}

function addCertification(): void {
  form.certifications.push({
    key: '', issuer: '', credential_id: '', verification_url: '', issue_year: new Date().getFullYear(), issue_month: null,
    result: '', is_verified: false, status: 'draft', sort_order: (form.certifications.length + 1) * 10,
    translations: emptyTranslations(['name', 'skill_label']),
  });
}

function addLanguage(): void {
  form.languages.push({
    key: '', language_code: '', proficiency_kind: 'cefr', cefr_level: '', status: 'draft',
    sort_order: (form.languages.length + 1) * 10, translations: emptyTranslations(['name', 'proficiency_label', 'evidence']),
  });
}

function move<T extends { sort_order: number }>(items: T[], index: number, direction: -1 | 1): void {
  const target = index + direction;
  if (target < 0 || target >= items.length) return;
  [items[index], items[target]] = [items[target], items[index]];
  items.forEach((item, position) => { item.sort_order = (position + 1) * 10; });
}

function save(): void {
  form.put('/dashboard/career', { preserveScroll: true });
}
</script>

<template>
  <Head title="Parcours — Administration" />
  <AdminLayout>
    <header class="mb-8">
      <p class="text-sm font-semibold text-primary">PORT-006</p>
      <h1 class="mt-1 text-3xl font-bold">Parcours professionnel</h1>
      <p class="mt-2 max-w-3xl text-sm text-muted-foreground">Gérez les expériences, formations, certifications et langues. Une fiche doit disposer d’un contenu français et anglais vérifié avant publication.</p>
    </header>

    <div v-if="Object.keys(form.errors).length" class="mb-6 rounded-xl border border-destructive/50 bg-destructive/10 p-4 text-sm text-destructive" role="alert">
      <p class="font-semibold">Certains champs sont invalides.</p>
      <ul class="mt-2 list-disc space-y-1 pl-5">
        <li v-for="(message, path) in form.errors" :key="path"><span class="font-mono">{{ path }}</span> : {{ message }}</li>
      </ul>
    </div>

    <form class="space-y-12" :aria-busy="form.processing" @submit.prevent="save">
      <section aria-labelledby="experiences-heading">
        <div class="flex flex-wrap items-end justify-between gap-3">
          <div><h2 id="experiences-heading" class="text-2xl font-bold">Expériences</h2><p class="mt-1 text-sm text-muted-foreground">Les résultats chiffrés publics doivent être vérifiés.</p></div>
          <button type="button" class="admin-button" @click="addExperience">Ajouter une expérience</button>
        </div>
        <div v-if="form.experiences.length === 0" class="mt-5 rounded-2xl border border-dashed border-border bg-surface p-6 text-sm text-muted-foreground">Aucune expérience.</div>
        <article v-for="(experience, index) in form.experiences" :key="experience.id ?? `new-${index}`" class="mt-5 rounded-2xl border border-border bg-surface p-5 shadow-card sm:p-6">
          <div class="flex flex-wrap justify-between gap-3">
            <h3 class="text-lg font-bold">Expérience {{ index + 1 }} <span class="text-sm font-normal text-muted-foreground">— {{ statusLabels[experience.status] }}</span></h3>
            <div class="flex gap-2">
              <button type="button" class="admin-link min-h-11 rounded-md border border-border px-3" :disabled="index === 0" :aria-label="`Monter l’expérience ${index + 1}`" @click="move(form.experiences, index, -1)">↑</button>
              <button type="button" class="admin-link min-h-11 rounded-md border border-border px-3" :disabled="index === form.experiences.length - 1" :aria-label="`Descendre l’expérience ${index + 1}`" @click="move(form.experiences, index, 1)">↓</button>
              <button type="button" class="min-h-11 rounded-md border border-destructive px-3 text-destructive" @click="form.experiences.splice(index, 1)">Supprimer</button>
            </div>
          </div>
          <div class="mt-5 grid gap-4 md:grid-cols-5">
            <label class="text-sm font-semibold">Clé stable<input :id="`experience-key-${index}`" v-model="experience.key" class="admin-input mt-2" required pattern="[a-z0-9]+(?:-[a-z0-9]+)*" :aria-invalid="Boolean(form.errors[`experiences.${index}.key`])" :aria-describedby="form.errors[`experiences.${index}.key`] ? `experience-key-${index}-error` : undefined"><span v-if="form.errors[`experiences.${index}.key`]" :id="`experience-key-${index}-error`" class="mt-2 block text-destructive" role="alert">{{ form.errors[`experiences.${index}.key`] }}</span></label>
            <label class="text-sm font-semibold md:col-span-2">Organisation<input v-model="experience.organization" class="admin-input mt-2" required maxlength="160"></label>
            <label class="text-sm font-semibold">Statut<select v-model="experience.status" class="admin-input mt-2"><option v-for="(label, value) in statusLabels" :key="value" :value="value">{{ label }}</option></select></label>
            <label class="text-sm font-semibold">Année de début<input v-model.number="experience.start_year" class="admin-input mt-2" type="number" min="1950" max="2100" required></label>
            <label class="text-sm font-semibold">Mois de début<input v-model.number="experience.start_month" class="admin-input mt-2" type="number" min="1" max="12"></label>
            <label class="text-sm font-semibold">Année de fin<input v-model.number="experience.end_year" class="admin-input mt-2" type="number" min="1950" max="2100" :disabled="experience.is_current"></label>
            <label class="text-sm font-semibold">Mois de fin<input v-model.number="experience.end_month" class="admin-input mt-2" type="number" min="1" max="12" :disabled="experience.is_current"></label>
            <label class="flex min-h-11 items-center gap-3 self-end"><input v-model="experience.is_current" type="checkbox"> Poste actuel</label>
          </div>
          <div class="mt-6 grid gap-5 lg:grid-cols-2">
            <fieldset v-for="locale in locales" :key="locale" class="rounded-xl border border-border p-4">
              <legend class="px-1 text-sm font-bold">{{ locale.toUpperCase() }}</legend>
              <div class="grid gap-4">
                <label class="text-sm font-semibold">Rôle<input :id="`experience-role-${index}-${locale}`" v-model="experience.translations[locale].role" class="admin-input mt-2" maxlength="180" :aria-invalid="Boolean(form.errors[`experiences.${index}.translations.${locale}`] || form.errors[`experiences.${index}.translations.${locale}.role`])" :aria-describedby="form.errors[`experiences.${index}.translations.${locale}`] || form.errors[`experiences.${index}.translations.${locale}.role`] ? `experience-role-${index}-${locale}-error` : undefined"><span v-if="form.errors[`experiences.${index}.translations.${locale}`] || form.errors[`experiences.${index}.translations.${locale}.role`]" :id="`experience-role-${index}-${locale}-error`" class="mt-2 block text-destructive" role="alert">{{ form.errors[`experiences.${index}.translations.${locale}`] || form.errors[`experiences.${index}.translations.${locale}.role`] }}</span></label>
                <label class="text-sm font-semibold">Type d’emploi<input v-model="experience.translations[locale].employment_type" class="admin-input mt-2" maxlength="100"></label>
                <label class="text-sm font-semibold">Localisation<input v-model="experience.translations[locale].location" class="admin-input mt-2" maxlength="160"></label>
                <label class="text-sm font-semibold">Résumé<textarea v-model="experience.translations[locale].summary" class="admin-input mt-2 min-h-24" maxlength="5000" /></label>
                <div>
                  <p class="text-sm font-semibold">Points clés</p>
                  <div v-for="(_highlight, highlightIndex) in experience.translations[locale].highlights as string[]" :key="highlightIndex" class="mt-2 flex gap-2">
                    <input v-model="(experience.translations[locale].highlights as string[])[highlightIndex]" class="admin-input" maxlength="500" :aria-label="`Point clé ${highlightIndex + 1} ${locale}`">
                    <button type="button" class="min-h-11 rounded-md border border-destructive px-3 text-destructive" @click="(experience.translations[locale].highlights as string[]).splice(highlightIndex, 1)">Retirer</button>
                  </div>
                  <button type="button" class="admin-link mt-2 min-h-11 rounded-md border border-border px-3" @click="(experience.translations[locale].highlights as string[]).push('')">Ajouter un point</button>
                </div>
              </div>
            </fieldset>
          </div>
          <section class="mt-6 border-t border-border pt-5" :aria-labelledby="`achievements-${index}`">
            <div class="flex items-center justify-between gap-3"><h4 :id="`achievements-${index}`" class="font-bold">Résultats</h4><button type="button" class="admin-link min-h-11 rounded-md border border-border px-3" @click="addAchievement(experience)">Ajouter un résultat</button></div>
            <div v-for="(achievement, achievementIndex) in experience.achievements" :key="achievement.id ?? `new-achievement-${achievementIndex}`" class="mt-4 rounded-xl border border-border p-4">
              <div class="grid gap-4 md:grid-cols-4">
                <label class="text-sm font-semibold">Clé<input v-model="achievement.key" class="admin-input mt-2" required></label>
                <label class="text-sm font-semibold">Statut<select v-model="achievement.status" class="admin-input mt-2"><option v-for="(label, value) in statusLabels" :key="value" :value="value">{{ label }}</option></select></label>
                <label class="flex min-h-11 items-center gap-3 self-end"><input v-model="achievement.is_quantified" type="checkbox"> Chiffré</label>
                <label class="flex min-h-11 items-center gap-3 self-end"><input v-model="achievement.is_verified" type="checkbox"> Vérifié</label>
              </div>
              <div class="mt-4 grid gap-4 md:grid-cols-2">
                <label v-for="locale in locales" :key="locale" class="text-sm font-semibold">Énoncé {{ locale.toUpperCase() }}<textarea :id="`achievement-statement-${index}-${achievementIndex}-${locale}`" v-model="achievement.translations[locale].statement" class="admin-input mt-2 min-h-24" maxlength="2000" :aria-invalid="Boolean(form.errors[`experiences.${index}.achievements.${achievementIndex}.translations.${locale}.statement`])" :aria-describedby="form.errors[`experiences.${index}.achievements.${achievementIndex}.translations.${locale}.statement`] ? `achievement-statement-${index}-${achievementIndex}-${locale}-error` : undefined" /><span v-if="form.errors[`experiences.${index}.achievements.${achievementIndex}.translations.${locale}.statement`]" :id="`achievement-statement-${index}-${achievementIndex}-${locale}-error`" class="mt-2 block text-destructive" role="alert">{{ form.errors[`experiences.${index}.achievements.${achievementIndex}.translations.${locale}.statement`] }}</span></label>
              </div>
              <div class="mt-3 flex gap-2">
                <button type="button" class="admin-link min-h-11 rounded-md border border-border px-3" :disabled="achievementIndex === 0" :aria-label="`Monter le résultat ${achievementIndex + 1}`" @click="move(experience.achievements, achievementIndex, -1)">↑</button>
                <button type="button" class="admin-link min-h-11 rounded-md border border-border px-3" :disabled="achievementIndex === experience.achievements.length - 1" :aria-label="`Descendre le résultat ${achievementIndex + 1}`" @click="move(experience.achievements, achievementIndex, 1)">↓</button>
                <button type="button" class="min-h-11 rounded-md border border-destructive px-3 text-destructive" @click="experience.achievements.splice(achievementIndex, 1)">Supprimer le résultat</button>
              </div>
            </div>
          </section>
        </article>
      </section>

      <section aria-labelledby="education-heading">
        <div class="flex flex-wrap items-end justify-between gap-3"><h2 id="education-heading" class="text-2xl font-bold">Formation</h2><button type="button" class="admin-button" @click="addEducation">Ajouter une formation</button></div>
        <article v-for="(record, index) in form.education" :key="record.id ?? `new-education-${index}`" class="mt-5 rounded-2xl border border-border bg-surface p-5">
          <div class="flex justify-between gap-3"><h3 class="font-bold">Formation {{ index + 1 }}</h3><div class="flex gap-2"><button type="button" class="admin-link min-h-11 rounded-md border border-border px-3" :disabled="index === 0" :aria-label="`Monter la formation ${index + 1}`" @click="move(form.education, index, -1)">↑</button><button type="button" class="admin-link min-h-11 rounded-md border border-border px-3" :disabled="index === form.education.length - 1" :aria-label="`Descendre la formation ${index + 1}`" @click="move(form.education, index, 1)">↓</button><button type="button" class="min-h-11 rounded-md border border-destructive px-3 text-destructive" :aria-label="`Supprimer la formation ${index + 1}`" @click="form.education.splice(index, 1)">Supprimer</button></div></div>
          <div class="mt-4 grid gap-4 md:grid-cols-6">
            <label class="text-sm font-semibold">Clé<input v-model="record.key" class="admin-input mt-2" required></label><label class="text-sm font-semibold">Établissement<input v-model="record.institution" class="admin-input mt-2" required></label>
            <label class="text-sm font-semibold">Année de début<input v-model.number="record.start_year" class="admin-input mt-2" type="number" min="1950" max="2100" required></label><label class="text-sm font-semibold">Mois de début<input v-model.number="record.start_month" class="admin-input mt-2" type="number" min="1" max="12"></label><label class="text-sm font-semibold">Année de fin<input :id="`education-end-year-${index}`" v-model.number="record.end_year" class="admin-input mt-2" type="number" min="1950" max="2100" :aria-invalid="Boolean(form.errors[`education.${index}.end_year`])" :aria-describedby="form.errors[`education.${index}.end_year`] ? `education-end-year-${index}-error` : undefined"><span v-if="form.errors[`education.${index}.end_year`]" :id="`education-end-year-${index}-error`" class="mt-2 block text-destructive" role="alert">{{ form.errors[`education.${index}.end_year`] }}</span></label><label class="text-sm font-semibold">Mois de fin<input v-model.number="record.end_month" class="admin-input mt-2" type="number" min="1" max="12"></label>
            <label class="text-sm font-semibold">Statut<select v-model="record.status" class="admin-input mt-2"><option v-for="(label, value) in statusLabels" :key="value" :value="value">{{ label }}</option></select></label>
          </div>
          <div class="mt-4 grid gap-4 md:grid-cols-2"><fieldset v-for="locale in locales" :key="locale" class="rounded-xl border border-border p-4"><legend class="px-1 text-sm font-bold">{{ locale.toUpperCase() }}</legend><div class="grid gap-3"><label class="text-sm font-semibold">Programme<input :id="`education-program-${index}-${locale}`" v-model="record.translations[locale].program" class="admin-input mt-2" :aria-invalid="Boolean(form.errors[`education.${index}.translations.${locale}`] || form.errors[`education.${index}.translations.${locale}.program`])" :aria-describedby="form.errors[`education.${index}.translations.${locale}`] || form.errors[`education.${index}.translations.${locale}.program`] ? `education-program-${index}-${locale}-error` : undefined"><span v-if="form.errors[`education.${index}.translations.${locale}`] || form.errors[`education.${index}.translations.${locale}.program`]" :id="`education-program-${index}-${locale}-error`" class="mt-2 block text-destructive" role="alert">{{ form.errors[`education.${index}.translations.${locale}`] || form.errors[`education.${index}.translations.${locale}.program`] }}</span></label><label class="text-sm font-semibold">Niveau<input v-model="record.translations[locale].level" class="admin-input mt-2"></label><label class="text-sm font-semibold">Ville<input v-model="record.translations[locale].location" class="admin-input mt-2"></label><label class="text-sm font-semibold">Résumé<textarea v-model="record.translations[locale].summary" class="admin-input mt-2 min-h-24" maxlength="5000" /></label></div></fieldset></div>
        </article>
      </section>

      <section aria-labelledby="certifications-heading">
        <div class="flex flex-wrap items-end justify-between gap-3"><h2 id="certifications-heading" class="text-2xl font-bold">Certifications</h2><button type="button" class="admin-button" @click="addCertification">Ajouter une certification</button></div>
        <article v-for="(record, index) in form.certifications" :key="record.id ?? `new-certification-${index}`" class="mt-5 rounded-2xl border border-border bg-surface p-5">
          <div class="flex justify-between gap-3"><h3 class="font-bold">Certification {{ index + 1 }}</h3><div class="flex gap-2"><button type="button" class="admin-link min-h-11 rounded-md border border-border px-3" :disabled="index === 0" :aria-label="`Monter la certification ${index + 1}`" @click="move(form.certifications, index, -1)">↑</button><button type="button" class="admin-link min-h-11 rounded-md border border-border px-3" :disabled="index === form.certifications.length - 1" :aria-label="`Descendre la certification ${index + 1}`" @click="move(form.certifications, index, 1)">↓</button><button type="button" class="min-h-11 rounded-md border border-destructive px-3 text-destructive" @click="form.certifications.splice(index, 1)">Supprimer</button></div></div>
          <div class="mt-4 grid gap-4 md:grid-cols-4">
            <label class="text-sm font-semibold">Clé<input v-model="record.key" class="admin-input mt-2" required></label><label class="text-sm font-semibold">Organisme<input v-model="record.issuer" class="admin-input mt-2"></label>
            <label class="text-sm font-semibold">Année<input v-model.number="record.issue_year" class="admin-input mt-2" type="number" min="1950" max="2100" required></label><label class="text-sm font-semibold">Mois<input v-model.number="record.issue_month" class="admin-input mt-2" type="number" min="1" max="12"></label>
            <label class="text-sm font-semibold">Résultat<input v-model="record.result" class="admin-input mt-2"></label><label class="text-sm font-semibold">Identifiant<input v-model="record.credential_id" class="admin-input mt-2"></label>
            <label class="text-sm font-semibold">Lien HTTPS<input :id="`certification-url-${index}`" v-model="record.verification_url" class="admin-input mt-2" type="url" :aria-invalid="Boolean(form.errors[`certifications.${index}.verification_url`])" :aria-describedby="form.errors[`certifications.${index}.verification_url`] ? `certification-url-${index}-error` : undefined"><span v-if="form.errors[`certifications.${index}.verification_url`]" :id="`certification-url-${index}-error`" class="mt-2 block text-destructive" role="alert">{{ form.errors[`certifications.${index}.verification_url`] }}</span></label><label class="text-sm font-semibold">Statut<select v-model="record.status" class="admin-input mt-2"><option v-for="(label, value) in statusLabels" :key="value" :value="value">{{ label }}</option></select></label>
            <label class="flex min-h-11 items-center gap-3"><input v-model="record.is_verified" type="checkbox"> Vérifiée</label>
          </div>
          <div class="mt-4 grid gap-4 md:grid-cols-2"><fieldset v-for="locale in locales" :key="locale" class="rounded-xl border border-border p-4"><legend class="px-1 text-sm font-bold">{{ locale.toUpperCase() }}</legend><label class="text-sm font-semibold">Nom<input v-model="record.translations[locale].name" class="admin-input mt-2"></label><label class="mt-3 block text-sm font-semibold">Compétence associée<input v-model="record.translations[locale].skill_label" class="admin-input mt-2"></label></fieldset></div>
        </article>
      </section>

      <section aria-labelledby="languages-heading">
        <div class="flex flex-wrap items-end justify-between gap-3"><h2 id="languages-heading" class="text-2xl font-bold">Langues parlées</h2><button type="button" class="admin-button" @click="addLanguage">Ajouter une langue</button></div>
        <article v-for="(record, index) in form.languages" :key="record.id ?? `new-language-${index}`" class="mt-5 rounded-2xl border border-border bg-surface p-5">
          <div class="flex justify-between gap-3"><h3 class="font-bold">Langue {{ index + 1 }}</h3><div class="flex gap-2"><button type="button" class="admin-link min-h-11 rounded-md border border-border px-3" :disabled="index === 0" :aria-label="`Monter la langue ${index + 1}`" @click="move(form.languages, index, -1)">↑</button><button type="button" class="admin-link min-h-11 rounded-md border border-border px-3" :disabled="index === form.languages.length - 1" :aria-label="`Descendre la langue ${index + 1}`" @click="move(form.languages, index, 1)">↓</button><button type="button" class="min-h-11 rounded-md border border-destructive px-3 text-destructive" @click="form.languages.splice(index, 1)">Supprimer</button></div></div>
          <div class="mt-4 grid gap-4 md:grid-cols-5">
            <label class="text-sm font-semibold">Clé<input v-model="record.key" class="admin-input mt-2" required></label><label class="text-sm font-semibold">Code langue<input v-model="record.language_code" class="admin-input mt-2" required></label>
            <label class="text-sm font-semibold">Type<select v-model="record.proficiency_kind" class="admin-input mt-2"><option value="native">Maternelle</option><option value="bilingual">Bilingue</option><option value="cefr">CECRL</option></select></label>
            <label class="text-sm font-semibold">Niveau CECRL<select v-model="record.cefr_level" class="admin-input mt-2"><option value="">Non défini</option><option v-for="level in ['A1','A2','B1','B2','C1','C2']" :key="level" :value="level">{{ level }}</option></select></label>
            <label class="text-sm font-semibold">Statut<select v-model="record.status" class="admin-input mt-2"><option v-for="(label, value) in statusLabels" :key="value" :value="value">{{ label }}</option></select></label>
          </div>
          <div class="mt-4 grid gap-4 md:grid-cols-2"><fieldset v-for="locale in locales" :key="locale" class="rounded-xl border border-border p-4"><legend class="px-1 text-sm font-bold">{{ locale.toUpperCase() }}</legend><label class="text-sm font-semibold">Nom<input v-model="record.translations[locale].name" class="admin-input mt-2"></label><label class="mt-3 block text-sm font-semibold">Niveau affiché<input v-model="record.translations[locale].proficiency_label" class="admin-input mt-2"></label><label class="mt-3 block text-sm font-semibold">Preuve<input v-model="record.translations[locale].evidence" class="admin-input mt-2"></label></fieldset></div>
        </article>
      </section>

      <div class="sticky bottom-4 flex justify-end"><button type="submit" class="admin-button shadow-glow" :disabled="form.processing">{{ form.processing ? 'Enregistrement…' : 'Enregistrer le parcours' }}</button></div>
    </form>
  </AdminLayout>
</template>
