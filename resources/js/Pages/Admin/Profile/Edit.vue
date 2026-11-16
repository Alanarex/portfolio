<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { cloneProps } from '@admin/lib/clone';
import CollapsibleControls from '@admin/Components/CollapsibleControls.vue';
import CollapsibleSection from '@admin/Components/CollapsibleSection.vue';
import PageHeader from '@admin/Components/PageHeader.vue';
import ToggleSwitch from '@admin/Components/ToggleSwitch.vue';
import ConfirmDialog from '@admin/Components/ConfirmDialog.vue';
import { useConfirm } from '@admin/lib/confirm';
import AdminLayout from '@admin/Layouts/AdminLayout.vue';
import { hasErrorUnder, provideSectionGroup } from '@admin/lib/collapsible';

type Translation = {
  professional_titles: string[];
  summary: string;
  location_label: string;
  availability: string;
  biography: string;
};

type CvVersion = {
  id: number;
  locale: string;
  label: string;
  version_label: string;
  original_filename: string | null;
  mime_type: string | null;
  size_bytes: number | null;
  checksum_sha256: string | null;
  is_verified: boolean;
  published: boolean;
  archived: boolean;
  has_document: boolean;
};

const confirm = useConfirm();

const props = defineProps<{
  profile: {
    display_name: string;
    is_published: boolean;
    show_location: boolean;
    show_availability: boolean;
    translations: Record<'fr' | 'en' | 'ar', Translation>;
  };
  cvVersions: CvVersion[];
}>();

const localeLabels = { fr: 'Français', en: 'English', ar: 'العربية (préparation)' } as const;
const translations = cloneProps(props.profile.translations);
for (const locale of ['fr', 'en'] as const) {
  if (translations[locale].professional_titles.length === 0) translations[locale].professional_titles = [''];
}

const form = useForm({ ...props.profile, translations });
provideSectionGroup('profile');
const cvHasErrors = (): boolean => newCv.hasErrors || cvForms.some((cv) => cv.hasErrors);
const newCv = useForm({
  locale: 'fr', label: '', version_label: '', document: null as File | null,
  is_verified: false, published: false, archived: false,
});
const cvForms = props.cvVersions.map((version) => useForm({ ...version, document: null as File | null }));

function saveProfile(): void {
  form.put('/dashboard/profile', { preserveScroll: true });
}

function addCv(): void {
  newCv.post('/dashboard/cv-versions', {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => newCv.reset(),
  });
}

function updateCv(index: number): void {
  cvForms[index]
    .transform((data) => ({ ...data, _method: 'put' }))
    .post(`/dashboard/cv-versions/${cvForms[index].id}`, { forceFormData: true, preserveScroll: true });
}

function selectDocument(event: Event, target: { document: File | null }): void {
  const input = event.target as HTMLInputElement;
  target.document = input.files?.[0] ?? null;
}

function deleteCv(id: number): void {
  confirm.ask({
    title: 'Supprimer cette version de CV ?',
    message: 'Le PDF privé associé sera supprimé. Cette action est définitive.',
    action: () => router.delete(`/dashboard/cv-versions/${id}`, { preserveScroll: true }),
  });
}
</script>

<template>
  <Head title="Profil — Administration" />
  <AdminLayout>
    <PageHeader class="mb-8" title="Profil professionnel" description="Gérez l’identité publique et ses contenus français et anglais. L’arabe reste un état de préparation non public." />

    <div class="mb-4 flex justify-end"><CollapsibleControls /></div>
    <form class="space-y-6" :aria-busy="form.processing" @submit.prevent="saveProfile">
      <CollapsibleSection id="identity" title="Identité et publication" default-open :invalid="hasErrorUnder(form.errors, 'display_name', 'is_published', 'show_location', 'show_availability')">
        <div class="grid gap-5 md:grid-cols-2">
          <div>
            <label for="display-name" class="admin-label">Nom affiché</label>
            <input id="display-name" v-model="form.display_name" class="admin-input" required maxlength="120" :aria-invalid="Boolean(form.errors.display_name)" :aria-describedby="form.errors.display_name ? 'display-name-error' : undefined">
            <p v-if="form.errors.display_name" id="display-name-error" class="admin-error" role="alert">{{ form.errors.display_name }}</p>
          </div>
          <fieldset class="grid gap-3 admin-fieldset">
            <legend class="px-1 text-sm font-semibold">Visibilité</legend>
            <ToggleSwitch v-model="form.is_published">Publier le profil</ToggleSwitch>
            <ToggleSwitch v-model="form.show_location">Afficher la localisation</ToggleSwitch>
            <ToggleSwitch v-model="form.show_availability">Afficher la disponibilité</ToggleSwitch>
          </fieldset>
        </div>
      </CollapsibleSection>

      <CollapsibleSection
        v-for="locale in (['fr', 'en', 'ar'] as const)" :id="`translation-${locale}`" :key="locale" :default-open="locale === 'fr'" :title="localeLabels[locale]"
        :description="locale === 'ar' ? 'Préparation uniquement : ce contenu n’est jamais exposé par le lecteur public.' : undefined"
        :summary="form.translations[locale].professional_titles[0] || 'Vide'" :invalid="hasErrorUnder(form.errors, `translations.${locale}`)"
      >
        <div class="grid gap-5">
          <div>
            <label :for="`title-${locale}`" class="admin-label">Titre professionnel principal</label>
            <input :id="`title-${locale}`" v-model="form.translations[locale].professional_titles[0]" class="admin-input" :required="locale !== 'ar'" maxlength="120" :aria-invalid="Boolean(form.errors[`translations.${locale}.professional_titles.0`])" :aria-describedby="form.errors[`translations.${locale}.professional_titles.0`] ? `title-${locale}-error` : undefined">
            <p v-if="form.errors[`translations.${locale}.professional_titles.0`]" :id="`title-${locale}-error`" class="admin-error" role="alert">{{ form.errors[`translations.${locale}.professional_titles.0`] }}</p>
          </div>
          <div>
            <label :for="`summary-${locale}`" class="admin-label">Résumé</label>
            <textarea :id="`summary-${locale}`" v-model="form.translations[locale].summary" class="admin-input min-h-28" :required="locale !== 'ar'" maxlength="1000" :aria-invalid="Boolean(form.errors[`translations.${locale}.summary`])" :aria-describedby="form.errors[`translations.${locale}.summary`] ? `summary-${locale}-error` : undefined" />
            <p v-if="form.errors[`translations.${locale}.summary`]" :id="`summary-${locale}-error`" class="admin-error" role="alert">{{ form.errors[`translations.${locale}.summary`] }}</p>
          </div>
          <div class="grid gap-5 md:grid-cols-2">
            <div>
              <label :for="`location-${locale}`" class="admin-label">Localisation publique</label>
              <input :id="`location-${locale}`" v-model="form.translations[locale].location_label" class="admin-input" maxlength="160">
            </div>
            <div>
              <label :for="`availability-${locale}`" class="admin-label">Disponibilité</label>
              <input :id="`availability-${locale}`" v-model="form.translations[locale].availability" class="admin-input" maxlength="240">
            </div>
          </div>
          <div>
            <label :for="`biography-${locale}`" class="admin-label">Biographie</label>
            <textarea :id="`biography-${locale}`" v-model="form.translations[locale].biography" class="admin-input min-h-44" :required="locale !== 'ar'" maxlength="10000" :aria-invalid="Boolean(form.errors[`translations.${locale}.biography`])" :aria-describedby="form.errors[`translations.${locale}.biography`] ? `biography-${locale}-error` : undefined" />
            <p v-if="form.errors[`translations.${locale}.biography`]" :id="`biography-${locale}-error`" class="admin-error" role="alert">{{ form.errors[`translations.${locale}.biography`] }}</p>
          </div>
        </div>
      </CollapsibleSection>

      <div class="admin-savebar">
        <button type="submit" class="admin-button shadow-glow" :disabled="form.processing">{{ form.processing ? 'Enregistrement…' : 'Enregistrer le profil' }}</button>
      </div>
    </form>

    <CollapsibleSection id="cv" class="mt-8" title="Versions du CV" description="Les PDF restent privés. Seule une version vérifiée, publiée et activée dans les paramètres peut être téléchargée publiquement." :summary="cvForms.length === 1 ? '1 version' : `${cvForms.length} versions`" :invalid="cvHasErrors()">
      <div v-if="cvForms.length === 0" class="admin-empty">Aucune version enregistrée.</div>
      <form v-for="(cv, index) in cvForms" :key="cv.id" class="mt-5 grid gap-4 admin-card admin-panel md:grid-cols-3" :aria-busy="cv.processing" @submit.prevent="updateCv(index)">
        <div v-if="Object.keys(cv.errors).length" class="admin-banner md:col-span-3" role="alert">Vérifiez les métadonnées de cette version : {{ Object.values(cv.errors).join(' ') }}</div>
        <div><label :for="`cv-label-${cv.id}`" class="admin-label">Libellé</label><input :id="`cv-label-${cv.id}`" v-model="cv.label" class="admin-input" required maxlength="120"></div>
        <div><label :for="`cv-version-${cv.id}`" class="admin-label">Version</label><input :id="`cv-version-${cv.id}`" v-model="cv.version_label" class="admin-input" required maxlength="50"></div>
        <div><label :for="`cv-locale-${cv.id}`" class="admin-label">Langue</label><select :id="`cv-locale-${cv.id}`" v-model="cv.locale" class="admin-input"><option value="fr">FR</option><option value="en">EN</option><option value="ar">AR</option></select></div>
        <ToggleSwitch v-model="cv.is_verified">Vérifié</ToggleSwitch>
        <ToggleSwitch v-model="cv.published">Marqué publié</ToggleSwitch>
        <ToggleSwitch v-model="cv.archived">Archivé</ToggleSwitch>
        <div class="md:col-span-3">
          <label :for="`cv-document-${cv.id}`" class="admin-label">Remplacer le PDF</label>
          <input :id="`cv-document-${cv.id}`" class="admin-input" type="file" accept="application/pdf,.pdf" :aria-invalid="Boolean(cv.errors.document)" @change="selectDocument($event, cv)">
          <p class="mt-2 text-xs text-muted-foreground">{{ cv.has_document ? 'Un PDF privé est enregistré.' : 'Aucun PDF enregistré.' }}</p>
          <p v-if="cv.errors.document" class="admin-error" role="alert">{{ cv.errors.document }}</p>
        </div>
        <div class="flex flex-wrap gap-3 md:col-span-3"><button type="submit" class="admin-button" :disabled="cv.processing">Enregistrer</button><button type="button" class="admin-button admin-button-danger admin-button-compact" @click="deleteCv(cv.id)">Supprimer</button></div>
      </form>

      <form class="mt-5 grid gap-4 admin-card admin-panel md:grid-cols-3" :aria-busy="newCv.processing" @submit.prevent="addCv">
        <h3 class="text-lg font-semibold md:col-span-3">Ajouter une version</h3>
        <div v-if="Object.keys(newCv.errors).length" class="admin-banner md:col-span-3" role="alert">Vérifiez les métadonnées : {{ Object.values(newCv.errors).join(' ') }}</div>
        <div><label for="new-cv-label" class="admin-label">Libellé</label><input id="new-cv-label" v-model="newCv.label" class="admin-input" required maxlength="120"></div>
        <div><label for="new-cv-version" class="admin-label">Version</label><input id="new-cv-version" v-model="newCv.version_label" class="admin-input" required maxlength="50"></div>
        <div><label for="new-cv-locale" class="admin-label">Langue</label><select id="new-cv-locale" v-model="newCv.locale" class="admin-input"><option value="fr">FR</option><option value="en">EN</option><option value="ar">AR</option></select></div>
        <div class="md:col-span-3">
          <label for="new-cv-document" class="admin-label">Fichier PDF privé</label>
          <input id="new-cv-document" class="admin-input" type="file" accept="application/pdf,.pdf" :aria-invalid="Boolean(newCv.errors.document)" @change="selectDocument($event, newCv)">
          <p class="mt-2 text-xs text-muted-foreground">PDF uniquement, 20 Mo maximum. Le nom, la taille et le SHA-256 sont calculés par le serveur.</p>
          <p v-if="newCv.errors.document" class="admin-error" role="alert">{{ newCv.errors.document }}</p>
        </div>
        <ToggleSwitch v-model="newCv.is_verified">Vérifié</ToggleSwitch>
        <ToggleSwitch v-model="newCv.published">Marqué publié</ToggleSwitch>
        <ToggleSwitch v-model="newCv.archived">Archivé</ToggleSwitch>
        <div class="md:col-span-3"><button type="submit" class="admin-button" :disabled="newCv.processing">{{ newCv.processing ? 'Ajout…' : 'Ajouter la version' }}</button></div>
      </form>
    </CollapsibleSection>

    <ConfirmDialog :open="confirm.request.value !== null" :title="confirm.request.value?.title ?? ''" :message="confirm.request.value?.message" :confirm-label="confirm.request.value?.confirmLabel" @confirm="confirm.accept" @cancel="confirm.dismiss" />
  </AdminLayout>
</template>
