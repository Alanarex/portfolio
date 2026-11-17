<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';

// Langue résolue côté serveur (cookie de choix, puis Accept-Language) : seul cet écran est bilingue.
const messages = {
  fr: {
    title: 'Connexion administrateur', eyebrow: 'Espace privé', intro: 'Cette interface est réservée à l’administration du portfolio.',
    email: 'Adresse e-mail', password: 'Mot de passe', submit: 'Se connecter', busy: 'Connexion…', back: 'Retour au portfolio', forgot: 'Mot de passe oublié ?',
  },
  en: {
    title: 'Administrator sign-in', eyebrow: 'Private area', intro: 'This interface is reserved for managing the portfolio.',
    email: 'Email address', password: 'Password', submit: 'Sign in', busy: 'Signing in…', back: 'Back to the portfolio', forgot: 'Forgot your password?',
  },
} as const;
const page = usePage<{ locale?: string; flash?: { success?: string | null } }>();
const t = computed(() => (page.props.locale === 'en' ? messages.en : messages.fr));
onMounted(() => {
  document.documentElement.lang = page.props.locale === 'en' ? 'en' : 'fr';
});

const form = useForm({ email: '', password: '' });

function submit(): void {
  form.post('/login', { onFinish: () => form.reset('password') });
}
</script>

<template>
  <Head :title="t.title" />
  <main class="flex min-h-screen items-center justify-center bg-background px-4 py-12 text-foreground">
    <section class="w-full max-w-md rounded-2xl border border-border bg-surface p-6 shadow-card sm:p-8" aria-labelledby="login-title">
      <p class="text-xs font-semibold uppercase tracking-[0.16em] text-primary">{{ t.eyebrow }}</p>
      <h1 id="login-title" class="mt-2 text-3xl font-bold">{{ t.title }}</h1>
      <p class="mt-3 text-sm text-muted-foreground">{{ t.intro }}</p>
      <p v-if="page.props.flash?.success" class="mt-4 rounded-lg border border-border p-3 text-sm" role="status">{{ page.props.flash.success }}</p>
      <form class="mt-8 space-y-5" :aria-busy="form.processing" @submit.prevent="submit">
        <div>
          <label for="email" class="mb-2 block text-sm font-semibold">{{ t.email }}</label>
          <input id="email" v-model="form.email" class="admin-input" type="email" autocomplete="username" required autofocus :aria-invalid="Boolean(form.errors.email)" :aria-describedby="form.errors.email ? 'email-error' : undefined">
          <p v-if="form.errors.email" id="email-error" class="mt-2 text-sm text-destructive" role="alert">{{ form.errors.email }}</p>
        </div>
        <div>
          <label for="password" class="mb-2 block text-sm font-semibold">{{ t.password }}</label>
          <input id="password" v-model="form.password" class="admin-input" type="password" autocomplete="current-password" required :aria-invalid="Boolean(form.errors.password)" :aria-describedby="form.errors.password ? 'password-error' : undefined">
          <p v-if="form.errors.password" id="password-error" class="mt-2 text-sm text-destructive" role="alert">{{ form.errors.password }}</p>
        </div>
        <button class="admin-button w-full" type="submit" :disabled="form.processing">{{ form.processing ? t.busy : t.submit }}</button>
        <Link href="/forgot-password" class="admin-link inline-flex min-h-11 items-center text-sm text-primary hover:underline">{{ t.forgot }}</Link>
      </form>
      <Link href="/" class="admin-link mt-6 inline-flex min-h-11 items-center text-sm text-primary hover:underline">{{ t.back }}</Link>
    </section>
  </main>
</template>
