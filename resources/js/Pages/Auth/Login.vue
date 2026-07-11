<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
const form = useForm({ email: '', password: '' });
const submit = () => form.post('/login', { onFinish: () => form.reset('password') });
</script>
<template>
  <Head title="Connexion" /><main class="auth-shell">
    <section
      class="panel"
      aria-labelledby="login-title"
    >
      <p class="eyebrow">
        Espace privé
      </p><h1 id="login-title">
        Connexion administrateur
      </h1><p class="auth-intro">
        Cette interface est réservée à l’administration du portfolio.
      </p>
      <form
        :aria-busy="form.processing"
        @submit.prevent="submit"
      >
        <label for="email">Adresse e-mail</label><input
          id="email"
          v-model="form.email"
          type="email"
          autocomplete="username"
          required
          autofocus
          :aria-invalid="Boolean(form.errors.email)"
          :aria-describedby="form.errors.email ? 'email-error' : undefined"
        >
        <p
          v-if="form.errors.email"
          id="email-error"
          class="error"
          role="alert"
        >
          {{ form.errors.email }}
        </p>
        <label for="password">Mot de passe</label><input
          id="password"
          v-model="form.password"
          type="password"
          autocomplete="current-password"
          required
          :aria-invalid="Boolean(form.errors.password)"
          :aria-describedby="form.errors.password ? 'password-error' : undefined"
        >
        <p
          v-if="form.errors.password"
          id="password-error"
          class="error"
          role="alert"
        >
          {{ form.errors.password }}
        </p>
        <button
          type="submit"
          :disabled="form.processing"
        >
          {{ form.processing ? 'Connexion…' : 'Se connecter' }}
        </button>
      </form><Link href="/">
        Retour au portfolio
      </Link>
    </section>
  </main>
</template>
