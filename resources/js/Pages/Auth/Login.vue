<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({ email: '', password: '', remember: false });
const submit = () => form.post('/login', { onFinish: () => form.reset('password') });
</script>

<template>
  <Head title="Connexion" />
  <main class="auth-shell">
    <section
      class="panel"
      aria-labelledby="login-title"
    >
      <p class="eyebrow">
        Espace privé
      </p>
      <h1 id="login-title">
        Connexion administrateur
      </h1>
      <p>Cette interface est réservée à l’administration du portfolio.</p>
      <form @submit.prevent="submit">
        <label for="email">Adresse e-mail</label>
        <input
          id="email"
          v-model="form.email"
          type="email"
          autocomplete="username"
          required
          autofocus
        >
        <p
          v-if="form.errors.email"
          class="error"
          role="alert"
        >
          {{ form.errors.email }}
        </p>
        <label for="password">Mot de passe</label>
        <input
          id="password"
          v-model="form.password"
          type="password"
          autocomplete="current-password"
          required
        >
        <p
          v-if="form.errors.password"
          class="error"
          role="alert"
        >
          {{ form.errors.password }}
        </p>
        <label class="check"><input
          v-model="form.remember"
          type="checkbox"
        > Rester connecté</label>
        <button
          type="submit"
          :disabled="form.processing"
        >
          {{ form.processing ? 'Connexion…' : 'Se connecter' }}
        </button>
      </form>
      <Link href="/">
        Retour au portfolio
      </Link>
    </section>
  </main>
</template>
