<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({ email: '', password: '' });

function submit(): void {
  form.post('/login', { onFinish: () => form.reset('password') });
}
</script>

<template>
  <Head title="Connexion administrateur" />
  <main class="flex min-h-screen items-center justify-center bg-background px-4 py-12 text-foreground">
    <section class="w-full max-w-md rounded-2xl border border-border bg-surface p-6 shadow-card sm:p-8" aria-labelledby="login-title">
      <p class="text-xs font-semibold uppercase tracking-[0.16em] text-primary">Espace privé</p>
      <h1 id="login-title" class="mt-2 text-3xl font-bold">Connexion administrateur</h1>
      <p class="mt-3 text-sm text-muted-foreground">Cette interface est réservée à l’administration du portfolio.</p>
      <form class="mt-8 space-y-5" :aria-busy="form.processing" @submit.prevent="submit">
        <div>
          <label for="email" class="mb-2 block text-sm font-semibold">Adresse e-mail</label>
          <input id="email" v-model="form.email" class="admin-input" type="email" autocomplete="username" required autofocus :aria-invalid="Boolean(form.errors.email)" :aria-describedby="form.errors.email ? 'email-error' : undefined">
          <p v-if="form.errors.email" id="email-error" class="mt-2 text-sm text-destructive" role="alert">{{ form.errors.email }}</p>
        </div>
        <div>
          <label for="password" class="mb-2 block text-sm font-semibold">Mot de passe</label>
          <input id="password" v-model="form.password" class="admin-input" type="password" autocomplete="current-password" required :aria-invalid="Boolean(form.errors.password)" :aria-describedby="form.errors.password ? 'password-error' : undefined">
          <p v-if="form.errors.password" id="password-error" class="mt-2 text-sm text-destructive" role="alert">{{ form.errors.password }}</p>
        </div>
        <button class="admin-button w-full" type="submit" :disabled="form.processing">{{ form.processing ? 'Connexion…' : 'Se connecter' }}</button>
      </form>
      <Link href="/" class="admin-link mt-6 inline-flex min-h-11 items-center text-sm text-primary hover:underline">Retour au portfolio</Link>
    </section>
  </main>
</template>
