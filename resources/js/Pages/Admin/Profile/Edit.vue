<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@admin/Layouts/AdminLayout.vue';

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
};

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
const translations = structuredClone(props.profile.translations);
for (const locale of ['fr', 'en'] as const) {
  if (translations[locale].professional_titles.length === 0) translations[locale].professional_titles = [''];
}

const form = useForm({ ...props.profile, translations });
const newCv = useForm({
  locale: 'fr', label: '', version_label: '', original_filename: '', mime_type: '', size_bytes: null as number | null,
  checksum_sha256: '', is_verified: false, published: false, archived: false,
});
const cvForms = props.cvVersions.map((version) => useForm({ ...version }));

function saveProfile(): void {
  form.put('/dashboard/profile', { preserveScroll: true });
}

function addCv(): void {
  newCv.post('/dashboard/cv-versions', { preserveScroll: true, onSuccess: () => newCv.reset() });
}

function updateCv(index: number): void {
  cvForms[index].put(`/dashboard/cv-versions/${cvForms[index].id}`, { preserveScroll: true });
}

function deleteCv(id: number): void {
  if (window.confirm('Supprimer cette version de CV ?')) {
    router.delete(`/dashboard/cv-versions/${id}`, { preserveScroll: true });
  }
}
</script>

<template>
  <Head title="Profil — Administration" />
  <AdminLayout>
    <header class="mb-8">
      <p class="text-sm font-semibold text-primary">PORT-005</p>
      <h1 class="mt-1 text-3xl font-bold">Profil professionnel</h1>
      <p class="mt-2 max-w-3xl text-sm text-muted-foreground">Gérez l’identité publique et ses contenus français et anglais. L’arabe reste un état de préparation non public.</p>
    </header>

    <form class="space-y-8" :aria-busy="form.processing" @submit.prevent="saveProfile">
      <section class="rounded-2xl border border-border bg-surface p-5 shadow-card sm:p-6" aria-labelledby="identity-heading">
        <h2 id="identity-heading" class="text-xl font-bold">Identité et publication</h2>
        <div class="mt-5 grid gap-5 md:grid-cols-2">
          <div>
            <label for="display-name" class="mb-2 block text-sm font-semibold">Nom affiché</label>
            <input id="display-name" v-model="form.display_name" class="admin-input" required maxlength="120" :aria-invalid="Boolean(form.errors.display_name)" :aria-describedby="form.errors.display_name ? 'display-name-error' : undefined">
            <p v-if="form.errors.display_name" id="display-name-error" class="mt-2 text-sm text-destructive" role="alert">{{ form.errors.display_name }}</p>
          </div>
          <fieldset class="grid gap-3 rounded-xl border border-border p-4">
            <legend class="px-1 text-sm font-semibold">Visibilité</legend>
            <label class="flex min-h-11 items-center gap-3"><input v-model="form.is_published" type="checkbox"> Publier le profil</label>
            <label class="flex min-h-11 items-center gap-3"><input v-model="form.show_location" type="checkbox"> Afficher la localisation</label>
            <label class="flex min-h-11 items-center gap-3"><input v-model="form.show_availability" type="checkbox"> Afficher la disponibilité</label>
          </fieldset>
        </div>
      </section>

      <section v-for="locale in (['fr', 'en', 'ar'] as const)" :key="locale" class="rounded-2xl border border-border bg-surface p-5 shadow-card sm:p-6" :aria-labelledby="`translation-${locale}`">
        <h2 :id="`translation-${locale}`" class="text-xl font-bold">{{ localeLabels[locale] }}</h2>
        <p v-if="locale === 'ar'" class="mt-1 text-sm text-warning">Préparation uniquement : ce contenu n’est jamais exposé par le lecteur public.</p>
        <div class="mt-5 grid gap-5">
          <div>
            <label :for="`title-${locale}`" class="mb-2 block text-sm font-semibold">Titre professionnel principal</label>
            <input :id="`title-${locale}`" v-model="form.translations[locale].professional_titles[0]" class="admin-input" :required="locale !== 'ar'" maxlength="120" :aria-invalid="Boolean(form.errors[`translations.${locale}.professional_titles.0`])" :aria-describedby="form.errors[`translations.${locale}.professional_titles.0`] ? `title-${locale}-error` : undefined">
            <p v-if="form.errors[`translations.${locale}.professional_titles.0`]" :id="`title-${locale}-error`" class="mt-2 text-sm text-destructive" role="alert">{{ form.errors[`translations.${locale}.professional_titles.0`] }}</p>
          </div>
          <div>
            <label :for="`summary-${locale}`" class="mb-2 block text-sm font-semibold">Résumé</label>
            <textarea :id="`summary-${locale}`" v-model="form.translations[locale].summary" class="admin-input min-h-28" :required="locale !== 'ar'" maxlength="1000" :aria-invalid="Boolean(form.errors[`translations.${locale}.summary`])" :aria-describedby="form.errors[`translations.${locale}.summary`] ? `summary-${locale}-error` : undefined" />
            <p v-if="form.errors[`translations.${locale}.summary`]" :id="`summary-${locale}-error`" class="mt-2 text-sm text-destructive" role="alert">{{ form.errors[`translations.${locale}.summary`] }}</p>
          </div>
          <div class="grid gap-5 md:grid-cols-2">
            <div>
              <label :for="`location-${locale}`" class="mb-2 block text-sm font-semibold">Localisation publique</label>
              <input :id="`location-${locale}`" v-model="form.translations[locale].location_label" class="admin-input" maxlength="160">
            </div>
            <div>
              <label :for="`availability-${locale}`" class="mb-2 block text-sm font-semibold">Disponibilité</label>
              <input :id="`availability-${locale}`" v-model="form.translations[locale].availability" class="admin-input" maxlength="240">
            </div>
          </div>
          <div>
            <label :for="`biography-${locale}`" class="mb-2 block text-sm font-semibold">Biographie</label>
            <textarea :id="`biography-${locale}`" v-model="form.translations[locale].biography" class="admin-input min-h-44" :required="locale !== 'ar'" maxlength="10000" :aria-invalid="Boolean(form.errors[`translations.${locale}.biography`])" :aria-describedby="form.errors[`translations.${locale}.biography`] ? `biography-${locale}-error` : undefined" />
            <p v-if="form.errors[`translations.${locale}.biography`]" :id="`biography-${locale}-error`" class="mt-2 text-sm text-destructive" role="alert">{{ form.errors[`translations.${locale}.biography`] }}</p>
          </div>
        </div>
      </section>

      <div class="sticky bottom-4 flex justify-end">
        <button type="submit" class="admin-button shadow-glow" :disabled="form.processing">{{ form.processing ? 'Enregistrement…' : 'Enregistrer le profil' }}</button>
      </div>
    </form>

    <section class="mt-12" aria-labelledby="cv-heading">
      <div>
        <h2 id="cv-heading" class="text-2xl font-bold">Versions du CV</h2>
        <p class="mt-2 text-sm text-muted-foreground">Métadonnées uniquement. Aucun fichier n’est téléversé ou téléchargeable dans cette fonctionnalité.</p>
      </div>

      <div v-if="cvForms.length === 0" class="mt-5 rounded-2xl border border-dashed border-border bg-surface p-6 text-sm text-muted-foreground">Aucune version enregistrée.</div>
      <form v-for="(cv, index) in cvForms" :key="cv.id" class="mt-5 grid gap-4 rounded-2xl border border-border bg-surface p-5 md:grid-cols-3" :aria-busy="cv.processing" @submit.prevent="updateCv(index)">
        <div v-if="Object.keys(cv.errors).length" class="rounded-lg border border-destructive/50 bg-destructive/10 p-3 text-sm text-destructive md:col-span-3" role="alert">Vérifiez les métadonnées de cette version : {{ Object.values(cv.errors).join(' ') }}</div>
        <div><label :for="`cv-label-${cv.id}`" class="mb-2 block text-sm font-semibold">Libellé</label><input :id="`cv-label-${cv.id}`" v-model="cv.label" class="admin-input" required maxlength="120"></div>
        <div><label :for="`cv-version-${cv.id}`" class="mb-2 block text-sm font-semibold">Version</label><input :id="`cv-version-${cv.id}`" v-model="cv.version_label" class="admin-input" required maxlength="50"></div>
        <div><label :for="`cv-locale-${cv.id}`" class="mb-2 block text-sm font-semibold">Langue</label><select :id="`cv-locale-${cv.id}`" v-model="cv.locale" class="admin-input"><option value="fr">FR</option><option value="en">EN</option><option value="ar">AR</option></select></div>
        <label class="flex min-h-11 items-center gap-3"><input v-model="cv.is_verified" type="checkbox"> Vérifié</label>
        <label class="flex min-h-11 items-center gap-3"><input v-model="cv.published" type="checkbox"> Marqué publié</label>
        <label class="flex min-h-11 items-center gap-3"><input v-model="cv.archived" type="checkbox"> Archivé</label>
        <div class="flex flex-wrap gap-3 md:col-span-3"><button type="submit" class="admin-button" :disabled="cv.processing">Enregistrer</button><button type="button" class="min-h-11 rounded-md border border-destructive px-4 text-destructive" @click="deleteCv(cv.id)">Supprimer</button></div>
      </form>

      <form class="mt-5 grid gap-4 rounded-2xl border border-border bg-surface p-5 md:grid-cols-3" :aria-busy="newCv.processing" @submit.prevent="addCv">
        <h3 class="text-lg font-bold md:col-span-3">Ajouter une version</h3>
        <div v-if="Object.keys(newCv.errors).length" class="rounded-lg border border-destructive/50 bg-destructive/10 p-3 text-sm text-destructive md:col-span-3" role="alert">Vérifiez les métadonnées : {{ Object.values(newCv.errors).join(' ') }}</div>
        <div><label for="new-cv-label" class="mb-2 block text-sm font-semibold">Libellé</label><input id="new-cv-label" v-model="newCv.label" class="admin-input" required maxlength="120"></div>
        <div><label for="new-cv-version" class="mb-2 block text-sm font-semibold">Version</label><input id="new-cv-version" v-model="newCv.version_label" class="admin-input" required maxlength="50"></div>
        <div><label for="new-cv-locale" class="mb-2 block text-sm font-semibold">Langue</label><select id="new-cv-locale" v-model="newCv.locale" class="admin-input"><option value="fr">FR</option><option value="en">EN</option><option value="ar">AR</option></select></div>
        <div><label for="new-cv-filename" class="mb-2 block text-sm font-semibold">Nom du fichier (métadonnée)</label><input id="new-cv-filename" v-model="newCv.original_filename" class="admin-input" maxlength="255"></div>
        <div><label for="new-cv-mime" class="mb-2 block text-sm font-semibold">Type MIME</label><select id="new-cv-mime" v-model="newCv.mime_type" class="admin-input"><option value="">Non défini</option><option value="application/pdf">application/pdf</option></select></div>
        <div><label for="new-cv-size" class="mb-2 block text-sm font-semibold">Taille en octets</label><input id="new-cv-size" v-model="newCv.size_bytes" class="admin-input" type="number" min="1" max="20971520"></div>
        <label class="flex min-h-11 items-center gap-3"><input v-model="newCv.is_verified" type="checkbox"> Vérifié</label>
        <label class="flex min-h-11 items-center gap-3"><input v-model="newCv.published" type="checkbox"> Marqué publié</label>
        <label class="flex min-h-11 items-center gap-3"><input v-model="newCv.archived" type="checkbox"> Archivé</label>
        <div class="md:col-span-3"><button type="submit" class="admin-button" :disabled="newCv.processing">{{ newCv.processing ? 'Ajout…' : 'Ajouter la version' }}</button></div>
      </form>
    </section>
  </AdminLayout>
</template>
