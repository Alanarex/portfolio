<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';

const props = defineProps<{ token: string; email: string }>();

const messages = {
  fr: {
    title: 'Nouveau mot de passe', intro: '12 caractères minimum, avec majuscule, minuscule, chiffre et symbole. Les autres sessions seront déconnectées.',
    password: 'Nouveau mot de passe', confirm: 'Confirmer le mot de passe', submit: 'Modifier le mot de passe', busy: 'Enregistrement…', back: 'Retour à la connexion',
  },
  en: {
    title: 'New password', intro: 'At least 12 characters, with upper and lower case, a digit and a symbol. All other sessions will be signed out.',
    password: 'New password', confirm: 'Confirm password', submit: 'Change password', busy: 'Saving…', back: 'Back to sign-in',
  },
} as const;
const page = usePage<{ locale?: string }>();
const t = computed(() => (page.props.locale === 'en' ? messages.en : messages.fr));
onMounted(() => {
  document.documentElement.lang = page.props.locale === 'en' ? 'en' : 'fr';
});

const form = useForm({ token: props.token, email: props.email, password: '', password_confirmation: '' });
function submit(): void {
  form.post('/reset-password', { onFinish: () => form.reset('password', 'password_confirmation') });
}
</script>

<template>
  <Head :title="t.title" />
  <main class="flex min-h-screen items-center justify-center bg-background px-4 py-12 text-foreground">
    <section class="w-full max-w-md rounded-2xl border border-border bg-surface p-6 shadow-card sm:p-8" aria-labelledby="reset-title">
      <h1 id="reset-title" class="text-3xl font-bold">{{ t.title }}</h1>
      <p class="mt-3 text-sm text-muted-foreground">{{ t.intro }}</p>
      <form class="mt-8 space-y-5" :aria-busy="form.processing" @submit.prevent="submit">
        <p v-if="form.errors.email" class="text-sm text-destructive" role="alert">{{ form.errors.email }}</p>
        <div>
          <label for="password" class="mb-2 block text-sm font-semibold">{{ t.password }}</label>
          <input id="password" v-model="form.password" class="admin-input" type="password" autocomplete="new-password" minlength="12" required autofocus :aria-invalid="Boolean(form.errors.password)">
          <p v-if="form.errors.password" class="mt-2 text-sm text-destructive" role="alert">{{ form.errors.password }}</p>
        </div>
        <div>
          <label for="password_confirmation" class="mb-2 block text-sm font-semibold">{{ t.confirm }}</label>
          <input id="password_confirmation" v-model="form.password_confirmation" class="admin-input" type="password" autocomplete="new-password" required>
        </div>
        <button class="admin-button w-full" type="submit" :disabled="form.processing">{{ form.processing ? t.busy : t.submit }}</button>
      </form>
      <Link href="/login" class="admin-link mt-6 inline-flex min-h-11 items-center text-sm text-primary hover:underline">{{ t.back }}</Link>
    </section>
  </main>
</template>
