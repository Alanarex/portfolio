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
const form = useForm(cloneProps(props.career));
const group = provideSectionGroup('career');
const count = (items: unknown[], one: string, many: string): string => (items.length === 1 ? `1 ${one}` : `${items.length} ${many}`);
const recordId = (kind: string, record: { id?: number }, index: number): string => `${kind}-${record.id ?? `new-${index}`}`;
const openNew = (kind: string, length: number): void => group.set(`${kind}-new-${length}`, true);
const locales: Locale[] = ['fr', 'en'];
const statusLabels: Record<Status, string> = { draft: 'Brouillon', published: 'Publié', archived: 'Archivé' };

function emptyTranslations(fields: string[]): Translations {
  return Object.fromEntries(locales.map((locale) => [locale, Object.fromEntries(fields.map((field) => [field, field === 'highlights' ? [] : '']))])) as Translations;
}

function addExperience(): void {
  openNew('experience', form.experiences.length);
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
  openNew('education', form.education.length);
  form.education.push({
    key: '', institution: '', start_year: new Date().getFullYear(), start_month: null, end_year: null, end_month: null,
    status: 'draft', sort_order: (form.education.length + 1) * 10, translations: emptyTranslations(['program', 'level', 'location', 'summary']),
  });
}

function addCertification(): void {
  openNew('certification', form.certifications.length);
  form.certifications.push({
    key: '', issuer: '', credential_id: '', verification_url: '', issue_year: new Date().getFullYear(), issue_month: null,
    result: '', is_verified: false, status: 'draft', sort_order: (form.certifications.length + 1) * 10,
    translations: emptyTranslations(['name', 'skill_label']),
  });
}

function addLanguage(): void {
  openNew('language', form.languages.length);
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
    <PageHeader class="mb-8" title="Parcours professionnel" description="Gérez les expériences, formations, certifications et langues. Une fiche doit disposer d’un contenu français et anglais vérifié avant publication." />

    <ErrorSummary :errors="form.errors" class="mb-6" />

    <form class="space-y-6" :aria-busy="form.processing" @submit.prevent="save">
      <div class="flex justify-end"><CollapsibleControls /></div>
      <CollapsibleSection id="experiences" title="Expériences" description="Les résultats chiffrés publics doivent être vérifiés." default-open :summary="count(form.experiences, 'expérience', 'expériences')" :invalid="hasErrorUnder(form.errors, 'experiences')">
        <template #actions><button type="button" class="admin-button" @click="addExperience">Ajouter une expérience</button></template>
        <div v-if="form.experiences.length === 0" class="admin-empty">Aucune expérience.</div>
        <CollapsibleSection
          v-for="(experience, index) in form.experiences" :id="recordId('experience', experience, index)" :key="experience.id ?? `new-${index}`" class="mt-4" variant="sub" :level="3"
          :default-open="!experience.id || index === 0" :title="`Expérience ${index + 1}${experience.organization ? ` · ${experience.organization}` : ''}`" :summary="statusLabels[experience.status]"
          :invalid="hasErrorUnder(form.errors, `experiences.${index}`)"
        >
          <template #actions>
            <div class="flex gap-2">
              <button type="button" class="admin-button admin-button-secondary admin-button-icon" :disabled="index === 0" :aria-label="`Monter l’expérience ${index + 1}`" @click="move(form.experiences, index, -1)">↑</button>
              <button type="button" class="admin-button admin-button-secondary admin-button-icon" :disabled="index === form.experiences.length - 1" :aria-label="`Descendre l’expérience ${index + 1}`" @click="move(form.experiences, index, 1)">↓</button>
              <button type="button" class="admin-button admin-button-danger admin-button-compact" @click="form.experiences.splice(index, 1)">Supprimer</button>
            </div>
          </template>
          <div class="grid gap-4 md:grid-cols-5">
            <label class="admin-field">Clé stable<input :id="`experience-key-${index}`" v-model="experience.key" class="admin-input" required pattern="[a-z0-9]+(?:-[a-z0-9]+)*" :aria-invalid="Boolean(form.errors[`experiences.${index}.key`])" :aria-describedby="form.errors[`experiences.${index}.key`] ? `experience-key-${index}-error` : undefined"><span v-if="form.errors[`experiences.${index}.key`]" :id="`experience-key-${index}-error`" class="admin-error" role="alert">{{ form.errors[`experiences.${index}.key`] }}</span></label>
            <label class="admin-field md:col-span-2">Organisation<input v-model="experience.organization" class="admin-input" required maxlength="160"></label>
            <label class="admin-field">Statut<select v-model="experience.status" class="admin-input"><option v-for="(label, value) in statusLabels" :key="value" :value="value">{{ label }}</option></select></label>
            <label class="admin-field">Année de début<input v-model.number="experience.start_year" class="admin-input" type="number" min="1950" max="2100" required></label>
            <label class="admin-field">Mois de début<input v-model.number="experience.start_month" class="admin-input" type="number" min="1" max="12"></label>
            <label class="admin-field">Année de fin<input v-model.number="experience.end_year" class="admin-input" type="number" min="1950" max="2100" :disabled="experience.is_current"></label>
            <label class="admin-field">Mois de fin<input v-model.number="experience.end_month" class="admin-input" type="number" min="1" max="12" :disabled="experience.is_current"></label>
            <ToggleSwitch v-model="experience.is_current" class="self-end">Poste actuel</ToggleSwitch>
          </div>
          <div class="mt-6 grid gap-5 lg:grid-cols-2">
            <fieldset v-for="locale in locales" :key="locale" class="admin-fieldset">
              <legend class="px-1 text-sm font-semibold">{{ locale.toUpperCase() }}</legend>
              <div class="grid gap-4">
                <label class="admin-field">Rôle<input :id="`experience-role-${index}-${locale}`" v-model="experience.translations[locale].role" class="admin-input" maxlength="180" :aria-invalid="Boolean(form.errors[`experiences.${index}.translations.${locale}`] || form.errors[`experiences.${index}.translations.${locale}.role`])" :aria-describedby="form.errors[`experiences.${index}.translations.${locale}`] || form.errors[`experiences.${index}.translations.${locale}.role`] ? `experience-role-${index}-${locale}-error` : undefined"><span v-if="form.errors[`experiences.${index}.translations.${locale}`] || form.errors[`experiences.${index}.translations.${locale}.role`]" :id="`experience-role-${index}-${locale}-error`" class="admin-error" role="alert">{{ form.errors[`experiences.${index}.translations.${locale}`] || form.errors[`experiences.${index}.translations.${locale}.role`] }}</span></label>
                <label class="admin-field">Type d’emploi<input v-model="experience.translations[locale].employment_type" class="admin-input" maxlength="100"></label>
                <label class="admin-field">Localisation<input v-model="experience.translations[locale].location" class="admin-input" maxlength="160"></label>
                <label class="admin-field">Résumé<textarea v-model="experience.translations[locale].summary" class="admin-input min-h-24" maxlength="5000" /></label>
                <div>
                  <p class="admin-field">Points clés</p>
                  <div v-for="(_highlight, highlightIndex) in experience.translations[locale].highlights as string[]" :key="highlightIndex" class="mt-2 flex gap-2">
                    <input v-model="(experience.translations[locale].highlights as string[])[highlightIndex]" class="admin-input" maxlength="500" :aria-label="`Point clé ${highlightIndex + 1} ${locale}`">
                    <button type="button" class="admin-button admin-button-danger admin-button-compact" @click="(experience.translations[locale].highlights as string[]).splice(highlightIndex, 1)">Retirer</button>
                  </div>
                  <button type="button" class="admin-button admin-button-secondary admin-button-compact mt-2" @click="(experience.translations[locale].highlights as string[]).push('')">Ajouter un point</button>
                </div>
              </div>
            </fieldset>
          </div>
          <section class="mt-6 border-t border-border pt-5" :aria-labelledby="`achievements-${index}`">
            <div class="flex items-center justify-between gap-3"><h4 :id="`achievements-${index}`" class="font-semibold">Résultats</h4><button type="button" class="admin-button admin-button-secondary admin-button-compact" @click="addAchievement(experience)">Ajouter un résultat</button></div>
            <div v-for="(achievement, achievementIndex) in experience.achievements" :key="achievement.id ?? `new-achievement-${achievementIndex}`" class="admin-subcard mt-4">
              <div class="grid gap-4 md:grid-cols-4">
                <label class="admin-field">Clé<input v-model="achievement.key" class="admin-input" required></label>
                <label class="admin-field">Statut<select v-model="achievement.status" class="admin-input"><option v-for="(label, value) in statusLabels" :key="value" :value="value">{{ label }}</option></select></label>
                <ToggleSwitch v-model="achievement.is_quantified" class="self-end">Chiffré</ToggleSwitch>
                <ToggleSwitch v-model="achievement.is_verified" class="self-end">Vérifié</ToggleSwitch>
              </div>
              <div class="mt-4 grid gap-4 md:grid-cols-2">
                <label v-for="locale in locales" :key="locale" class="admin-field">Énoncé {{ locale.toUpperCase() }}<textarea :id="`achievement-statement-${index}-${achievementIndex}-${locale}`" v-model="achievement.translations[locale].statement" class="admin-input min-h-24" maxlength="2000" :aria-invalid="Boolean(form.errors[`experiences.${index}.achievements.${achievementIndex}.translations.${locale}.statement`])" :aria-describedby="form.errors[`experiences.${index}.achievements.${achievementIndex}.translations.${locale}.statement`] ? `achievement-statement-${index}-${achievementIndex}-${locale}-error` : undefined" /><span v-if="form.errors[`experiences.${index}.achievements.${achievementIndex}.translations.${locale}.statement`]" :id="`achievement-statement-${index}-${achievementIndex}-${locale}-error`" class="admin-error" role="alert">{{ form.errors[`experiences.${index}.achievements.${achievementIndex}.translations.${locale}.statement`] }}</span></label>
              </div>
              <div class="mt-3 flex gap-2">
                <button type="button" class="admin-button admin-button-secondary admin-button-icon" :disabled="achievementIndex === 0" :aria-label="`Monter le résultat ${achievementIndex + 1}`" @click="move(experience.achievements, achievementIndex, -1)">↑</button>
                <button type="button" class="admin-button admin-button-secondary admin-button-icon" :disabled="achievementIndex === experience.achievements.length - 1" :aria-label="`Descendre le résultat ${achievementIndex + 1}`" @click="move(experience.achievements, achievementIndex, 1)">↓</button>
                <button type="button" class="admin-button admin-button-danger admin-button-compact" @click="experience.achievements.splice(achievementIndex, 1)">Supprimer le résultat</button>
              </div>
            </div>
          </section>
        </CollapsibleSection>
      </CollapsibleSection>

      <CollapsibleSection id="education" title="Formation" :summary="count(form.education, 'formation', 'formations')" :invalid="hasErrorUnder(form.errors, 'education')">
        <template #actions><button type="button" class="admin-button" @click="addEducation">Ajouter une formation</button></template>
        <div v-if="form.education.length === 0" class="admin-empty">Aucune formation.</div>
        <CollapsibleSection
          v-for="(record, index) in form.education" :id="recordId('education', record, index)" :key="record.id ?? `new-education-${index}`" class="mt-4" variant="sub" :level="3"
          :default-open="!record.id" :title="`Formation ${index + 1}${record.institution ? ` · ${record.institution}` : ''}`" :summary="statusLabels[record.status]" :invalid="hasErrorUnder(form.errors, `education.${index}`)"
        >
          <template #actions><div class="flex gap-2"><button type="button" class="admin-button admin-button-secondary admin-button-icon" :disabled="index === 0" :aria-label="`Monter la formation ${index + 1}`" @click="move(form.education, index, -1)">↑</button><button type="button" class="admin-button admin-button-secondary admin-button-icon" :disabled="index === form.education.length - 1" :aria-label="`Descendre la formation ${index + 1}`" @click="move(form.education, index, 1)">↓</button><button type="button" class="admin-button admin-button-danger admin-button-compact" :aria-label="`Supprimer la formation ${index + 1}`" @click="form.education.splice(index, 1)">Supprimer</button></div></template>
          <div class="grid gap-4 md:grid-cols-6">
            <label class="admin-field">Clé<input v-model="record.key" class="admin-input" required></label><label class="admin-field">Établissement<input v-model="record.institution" class="admin-input" required></label>
            <label class="admin-field">Année de début<input v-model.number="record.start_year" class="admin-input" type="number" min="1950" max="2100" required></label><label class="admin-field">Mois de début<input v-model.number="record.start_month" class="admin-input" type="number" min="1" max="12"></label><label class="admin-field">Année de fin<input :id="`education-end-year-${index}`" v-model.number="record.end_year" class="admin-input" type="number" min="1950" max="2100" :aria-invalid="Boolean(form.errors[`education.${index}.end_year`])" :aria-describedby="form.errors[`education.${index}.end_year`] ? `education-end-year-${index}-error` : undefined"><span v-if="form.errors[`education.${index}.end_year`]" :id="`education-end-year-${index}-error`" class="admin-error" role="alert">{{ form.errors[`education.${index}.end_year`] }}</span></label><label class="admin-field">Mois de fin<input v-model.number="record.end_month" class="admin-input" type="number" min="1" max="12"></label>
            <label class="admin-field">Statut<select v-model="record.status" class="admin-input"><option v-for="(label, value) in statusLabels" :key="value" :value="value">{{ label }}</option></select></label>
          </div>
          <div class="mt-4 grid gap-4 md:grid-cols-2"><fieldset v-for="locale in locales" :key="locale" class="admin-fieldset"><legend class="px-1 text-sm font-semibold">{{ locale.toUpperCase() }}</legend><div class="grid gap-3"><label class="admin-field">Programme<input :id="`education-program-${index}-${locale}`" v-model="record.translations[locale].program" class="admin-input" :aria-invalid="Boolean(form.errors[`education.${index}.translations.${locale}`] || form.errors[`education.${index}.translations.${locale}.program`])" :aria-describedby="form.errors[`education.${index}.translations.${locale}`] || form.errors[`education.${index}.translations.${locale}.program`] ? `education-program-${index}-${locale}-error` : undefined"><span v-if="form.errors[`education.${index}.translations.${locale}`] || form.errors[`education.${index}.translations.${locale}.program`]" :id="`education-program-${index}-${locale}-error`" class="admin-error" role="alert">{{ form.errors[`education.${index}.translations.${locale}`] || form.errors[`education.${index}.translations.${locale}.program`] }}</span></label><label class="admin-field">Niveau<input v-model="record.translations[locale].level" class="admin-input"></label><label class="admin-field">Ville<input v-model="record.translations[locale].location" class="admin-input"></label><label class="admin-field">Résumé<textarea v-model="record.translations[locale].summary" class="admin-input min-h-24" maxlength="5000" /></label></div></fieldset></div>
        </CollapsibleSection>
      </CollapsibleSection>

      <CollapsibleSection id="certifications" title="Certifications" :summary="count(form.certifications, 'certification', 'certifications')" :invalid="hasErrorUnder(form.errors, 'certifications')">
        <template #actions><button type="button" class="admin-button" @click="addCertification">Ajouter une certification</button></template>
        <div v-if="form.certifications.length === 0" class="admin-empty">Aucune certification.</div>
        <CollapsibleSection
          v-for="(record, index) in form.certifications" :id="recordId('certification', record, index)" :key="record.id ?? `new-certification-${index}`" class="mt-4" variant="sub" :level="3"
          :default-open="!record.id" :title="`Certification ${index + 1}${record.issuer ? ` · ${record.issuer}` : ''}`" :summary="statusLabels[record.status]" :invalid="hasErrorUnder(form.errors, `certifications.${index}`)"
        >
          <template #actions><div class="flex gap-2"><button type="button" class="admin-button admin-button-secondary admin-button-icon" :disabled="index === 0" :aria-label="`Monter la certification ${index + 1}`" @click="move(form.certifications, index, -1)">↑</button><button type="button" class="admin-button admin-button-secondary admin-button-icon" :disabled="index === form.certifications.length - 1" :aria-label="`Descendre la certification ${index + 1}`" @click="move(form.certifications, index, 1)">↓</button><button type="button" class="admin-button admin-button-danger admin-button-compact" @click="form.certifications.splice(index, 1)">Supprimer</button></div></template>
          <div class="grid gap-4 md:grid-cols-4">
            <label class="admin-field">Clé<input v-model="record.key" class="admin-input" required></label><label class="admin-field">Organisme<input v-model="record.issuer" class="admin-input"></label>
            <label class="admin-field">Année<input v-model.number="record.issue_year" class="admin-input" type="number" min="1950" max="2100" required></label><label class="admin-field">Mois<input v-model.number="record.issue_month" class="admin-input" type="number" min="1" max="12"></label>
            <label class="admin-field">Résultat<input v-model="record.result" class="admin-input"></label><label class="admin-field">Identifiant<input v-model="record.credential_id" class="admin-input"></label>
            <label class="admin-field">Lien HTTPS<input :id="`certification-url-${index}`" v-model="record.verification_url" class="admin-input" type="url" :aria-invalid="Boolean(form.errors[`certifications.${index}.verification_url`])" :aria-describedby="form.errors[`certifications.${index}.verification_url`] ? `certification-url-${index}-error` : undefined"><span v-if="form.errors[`certifications.${index}.verification_url`]" :id="`certification-url-${index}-error`" class="admin-error" role="alert">{{ form.errors[`certifications.${index}.verification_url`] }}</span></label><label class="admin-field">Statut<select v-model="record.status" class="admin-input"><option v-for="(label, value) in statusLabels" :key="value" :value="value">{{ label }}</option></select></label>
            <ToggleSwitch v-model="record.is_verified">Vérifiée</ToggleSwitch>
          </div>
          <div class="mt-4 grid gap-4 md:grid-cols-2"><fieldset v-for="locale in locales" :key="locale" class="admin-fieldset"><legend class="px-1 text-sm font-semibold">{{ locale.toUpperCase() }}</legend><label class="admin-field">Nom<input v-model="record.translations[locale].name" class="admin-input"></label><label class="admin-field mt-3">Compétence associée<input v-model="record.translations[locale].skill_label" class="admin-input"></label></fieldset></div>
        </CollapsibleSection>
      </CollapsibleSection>

      <CollapsibleSection id="languages" title="Langues parlées" :summary="count(form.languages, 'langue', 'langues')" :invalid="hasErrorUnder(form.errors, 'languages')">
        <template #actions><button type="button" class="admin-button" @click="addLanguage">Ajouter une langue</button></template>
        <div v-if="form.languages.length === 0" class="admin-empty">Aucune langue.</div>
        <CollapsibleSection
          v-for="(record, index) in form.languages" :id="recordId('language', record, index)" :key="record.id ?? `new-language-${index}`" class="mt-4" variant="sub" :level="3"
          :default-open="!record.id" :title="`Langue ${index + 1}${record.language_code ? ` · ${record.language_code}` : ''}`" :summary="statusLabels[record.status]" :invalid="hasErrorUnder(form.errors, `languages.${index}`)"
        >
          <template #actions><div class="flex gap-2"><button type="button" class="admin-button admin-button-secondary admin-button-icon" :disabled="index === 0" :aria-label="`Monter la langue ${index + 1}`" @click="move(form.languages, index, -1)">↑</button><button type="button" class="admin-button admin-button-secondary admin-button-icon" :disabled="index === form.languages.length - 1" :aria-label="`Descendre la langue ${index + 1}`" @click="move(form.languages, index, 1)">↓</button><button type="button" class="admin-button admin-button-danger admin-button-compact" @click="form.languages.splice(index, 1)">Supprimer</button></div></template>
          <div class="grid gap-4 md:grid-cols-5">
            <label class="admin-field">Clé<input v-model="record.key" class="admin-input" required></label><label class="admin-field">Code langue<input v-model="record.language_code" class="admin-input" required></label>
            <label class="admin-field">Type<select v-model="record.proficiency_kind" class="admin-input"><option value="native">Maternelle</option><option value="bilingual">Bilingue</option><option value="cefr">CECRL</option></select></label>
            <label class="admin-field">Niveau CECRL<select v-model="record.cefr_level" class="admin-input"><option value="">Non défini</option><option v-for="level in ['A1','A2','B1','B2','C1','C2']" :key="level" :value="level">{{ level }}</option></select></label>
            <label class="admin-field">Statut<select v-model="record.status" class="admin-input"><option v-for="(label, value) in statusLabels" :key="value" :value="value">{{ label }}</option></select></label>
          </div>
          <div class="mt-4 grid gap-4 md:grid-cols-2"><fieldset v-for="locale in locales" :key="locale" class="admin-fieldset"><legend class="px-1 text-sm font-semibold">{{ locale.toUpperCase() }}</legend><label class="admin-field">Nom<input v-model="record.translations[locale].name" class="admin-input"></label><label class="admin-field mt-3">Niveau affiché<input v-model="record.translations[locale].proficiency_label" class="admin-input"></label><label class="admin-field mt-3">Preuve<input v-model="record.translations[locale].evidence" class="admin-input"></label></fieldset></div>
        </CollapsibleSection>
      </CollapsibleSection>

      <div class="admin-savebar"><button type="submit" class="admin-button shadow-glow" :disabled="form.processing">{{ form.processing ? 'Enregistrement…' : 'Enregistrer le parcours' }}</button></div>
    </form>
  </AdminLayout>
</template>
