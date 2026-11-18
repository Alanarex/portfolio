<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';

const messages = {
  fr: {
    title: 'Mot de passe oublié', intro: 'Saisissez l’adresse e-mail de l’administrateur : si elle correspond à un compte, vous recevrez un lien de réinitialisation valable peu de temps.',
    email: 'Adresse e-mail', submit: 'Envoyer le lien', busy: 'Envoi…', back: 'Retour à la connexion',
  },
  en: {
    title: 'Forgot your password', intro: 'Enter the administrator email address: if it matches an account, you will receive a short-lived reset link.',
    email: 'Email address', submit: 'Send the link', busy: 'Sending…', back: 'Back to sign-in',
  },
} as const;
const page = usePage<{ locale?: string; flash?: { success?: string | null } }>();
const t = computed(() => (page.props.locale === 'en' ? messages.en : messages.fr));
onMounted(() => {
  document.documentElement.lang = page.props.locale === 'en' ? 'en' : 'fr';
});

const form = useForm({ email: '' });
function submit(): void {
  form.post('/forgot-password', { onSuccess: () => form.reset('email') });
}
</script>

<template>
  <Head :title="t.title" />
  <main class="flex min-h-screen items-center justify-center bg-background px-4 py-12 text-foreground">
    <section class="w-full max-w-md rounded-2xl border border-border bg-surface p-6 shadow-card sm:p-8" aria-labelledby="forgot-title">
      <h1 id="forgot-title" class="text-3xl font-bold">{{ t.title }}</h1>
      <p class="mt-3 text-sm text-muted-foreground">{{ t.intro }}</p>
      <p v-if="page.props.flash?.success" class="mt-4 rounded-lg border border-border p-3 text-sm" role="status">{{ page.props.flash.success }}</p>
      <form class="mt-8 space-y-5" :aria-busy="form.processing" @submit.prevent="submit">
        <div>
          <label for="email" class="mb-2 block text-sm font-semibold">{{ t.email }}</label>
          <input id="email" v-model="form.email" class="admin-input" type="email" autocomplete="username" required autofocus :aria-invalid="Boolean(form.errors.email)">
          <p v-if="form.errors.email" class="mt-2 text-sm text-destructive" role="alert">{{ form.errors.email }}</p>
        </div>
        <button class="admin-button w-full" type="submit" :disabled="form.processing">{{ form.processing ? t.busy : t.submit }}</button>
      </form>
      <Link href="/login" class="admin-link mt-6 inline-flex min-h-11 items-center text-sm text-primary hover:underline">{{ t.back }}</Link>
    </section>
  </main>
</template>
