<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@admin/Layouts/AdminLayout.vue';

type SocialLink = { platform: string; url: string; is_enabled: boolean; is_public: boolean; sort_order: number };
type Settings = {
  site_name: string;
  default_locale: string;
  contact_email: string;
  contact_phone: string;
  show_email: boolean;
  show_phone: boolean;
  social_links: SocialLink[];
  feature_flags: Record<'projects' | 'contact' | 'cv' | 'three_d', boolean>;
};

const props = defineProps<{
  settings: Settings;
  locales: { code: string; label: string; direction: string; status: string }[];
}>();

const form = useForm(structuredClone(props.settings));

function socialError(platform: string, field: string): string | undefined {
  const submitted = form.social_links.filter((link) => link.url.trim() !== '');
  const index = submitted.findIndex((link) => link.platform === platform);
  return index < 0 ? undefined : form.errors[`social_links.${index}.${field}`];
}

function save(): void {
  form.transform((data) => ({
    ...data,
    social_links: data.social_links.filter((link) => link.url.trim() !== ''),
  })).put('/dashboard/settings', { preserveScroll: true });
}
</script>

<template>
  <Head title="Paramètres — Administration" />
  <AdminLayout>
    <header class="mb-8">
      <p class="text-sm font-semibold text-primary">PORT-005</p>
      <h1 class="mt-1 text-3xl font-bold">Paramètres globaux</h1>
      <p class="mt-2 max-w-3xl text-sm text-muted-foreground">Les coordonnées et liens restent privés tant que leur visibilité publique n’est pas activée explicitement.</p>
    </header>

    <form class="space-y-8" :aria-busy="form.processing" @submit.prevent="save">
      <section class="rounded-2xl border border-border bg-surface p-5 shadow-card sm:p-6" aria-labelledby="site-heading">
        <h2 id="site-heading" class="text-xl font-bold">Identité du site</h2>
        <div class="mt-5 grid gap-5 md:grid-cols-2">
          <div>
            <label for="site-name" class="mb-2 block text-sm font-semibold">Nom du site</label>
            <input id="site-name" v-model="form.site_name" class="admin-input" required maxlength="120" :aria-invalid="Boolean(form.errors.site_name)" :aria-describedby="form.errors.site_name ? 'site-name-error' : undefined">
            <p v-if="form.errors.site_name" id="site-name-error" class="mt-2 text-sm text-destructive" role="alert">{{ form.errors.site_name }}</p>
          </div>
          <div>
            <label for="default-locale" class="mb-2 block text-sm font-semibold">Langue par défaut</label>
            <select id="default-locale" v-model="form.default_locale" class="admin-input" disabled><option value="fr">Français</option></select>
            <p class="mt-2 text-xs text-muted-foreground">Le français est fixé comme langue publique par défaut pour V3.</p>
          </div>
        </div>
        <div class="mt-6 grid gap-3 sm:grid-cols-3" aria-label="État des langues">
          <div v-for="locale in locales" :key="locale.code" class="rounded-xl border border-border p-4">
            <div class="flex items-center justify-between gap-2"><span class="font-semibold">{{ locale.label }}</span><span class="rounded-full bg-muted px-2 py-1 text-xs">{{ locale.status }}</span></div>
            <p class="mt-2 text-xs text-muted-foreground">{{ locale.code.toUpperCase() }} · {{ locale.direction.toUpperCase() }}</p>
          </div>
        </div>
      </section>

      <section class="rounded-2xl border border-border bg-surface p-5 shadow-card sm:p-6" aria-labelledby="contact-heading">
        <h2 id="contact-heading" class="text-xl font-bold">Coordonnées</h2>
        <p class="mt-2 text-sm text-muted-foreground">Valeurs chiffrées au repos. La visibilité est désactivée par défaut.</p>
        <div class="mt-5 grid gap-5 md:grid-cols-2">
          <div><label for="contact-email" class="mb-2 block text-sm font-semibold">E-mail</label><input id="contact-email" v-model="form.contact_email" class="admin-input" type="email" maxlength="255" autocomplete="email" :aria-invalid="Boolean(form.errors.contact_email)" :aria-describedby="form.errors.contact_email ? 'contact-email-error' : undefined"><p v-if="form.errors.contact_email" id="contact-email-error" class="mt-2 text-sm text-destructive" role="alert">{{ form.errors.contact_email }}</p></div>
          <div><label for="contact-phone" class="mb-2 block text-sm font-semibold">Téléphone</label><input id="contact-phone" v-model="form.contact_phone" class="admin-input" type="tel" maxlength="40" autocomplete="tel" :aria-invalid="Boolean(form.errors.contact_phone)" :aria-describedby="form.errors.contact_phone ? 'contact-phone-error' : undefined"><p v-if="form.errors.contact_phone" id="contact-phone-error" class="mt-2 text-sm text-destructive" role="alert">{{ form.errors.contact_phone }}</p></div>
        </div>
        <fieldset class="mt-5 grid gap-3 sm:grid-cols-2">
          <legend class="text-sm font-semibold">Visibilité publique</legend>
          <label class="flex min-h-11 items-center gap-3"><input v-model="form.show_email" type="checkbox"> Afficher l’e-mail public</label>
          <label class="flex min-h-11 items-center gap-3"><input v-model="form.show_phone" type="checkbox"> Afficher le téléphone public</label>
        </fieldset>
      </section>

      <section class="rounded-2xl border border-border bg-surface p-5 shadow-card sm:p-6" aria-labelledby="social-heading">
        <h2 id="social-heading" class="text-xl font-bold">Liens sociaux</h2>
        <div class="mt-5 space-y-4">
          <fieldset v-for="link in form.social_links" :key="link.platform" class="grid gap-4 rounded-xl border border-border p-4 md:grid-cols-[10rem_1fr_auto_auto] md:items-end">
            <legend class="px-1 text-sm font-semibold capitalize">{{ link.platform }}</legend>
            <div class="md:col-span-2"><label :for="`social-url-${link.platform}`" class="mb-2 block text-sm font-semibold">URL HTTPS</label><input :id="`social-url-${link.platform}`" v-model="link.url" class="admin-input" type="url" maxlength="2048" :aria-invalid="Boolean(socialError(link.platform, 'url'))" :aria-describedby="socialError(link.platform, 'url') ? `social-url-${link.platform}-error` : undefined"><p v-if="socialError(link.platform, 'url')" :id="`social-url-${link.platform}-error`" class="mt-2 text-sm text-destructive" role="alert">{{ socialError(link.platform, 'url') }}</p></div>
            <label class="flex min-h-11 items-center gap-2"><input v-model="link.is_enabled" type="checkbox" :disabled="!link.url"> Activé</label>
            <label class="flex min-h-11 items-center gap-2"><input v-model="link.is_public" type="checkbox" :disabled="!link.url"> Public</label>
          </fieldset>
        </div>
      </section>

      <section class="rounded-2xl border border-border bg-surface p-5 shadow-card sm:p-6" aria-labelledby="features-heading">
        <h2 id="features-heading" class="text-xl font-bold">Fonctionnalités publiques</h2>
        <fieldset class="mt-5 grid gap-3 sm:grid-cols-2">
          <legend class="sr-only">Activation des fonctionnalités</legend>
          <label class="flex min-h-11 items-center gap-3"><input v-model="form.feature_flags.projects" type="checkbox"> Projets</label>
          <label class="flex min-h-11 items-center gap-3"><input v-model="form.feature_flags.contact" type="checkbox"> Contact</label>
          <label class="flex min-h-11 items-center gap-3"><input v-model="form.feature_flags.cv" type="checkbox"> CV</label>
          <label class="flex min-h-11 items-center gap-3"><input v-model="form.feature_flags.three_d" type="checkbox"> Expérience 3D</label>
        </fieldset>
      </section>

      <div class="sticky bottom-4 flex justify-end"><button type="submit" class="admin-button shadow-glow" :disabled="form.processing">{{ form.processing ? 'Enregistrement…' : 'Enregistrer les paramètres' }}</button></div>
    </form>
  </AdminLayout>
</template>
