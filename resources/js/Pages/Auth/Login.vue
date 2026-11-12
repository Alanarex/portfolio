<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import FloatingField from '../../Components/FloatingField.vue';
const form = useForm({ email: '', password: '' });
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
        Administration
      </p>
      <h1 id="login-title">
        Connexion administrateur
      </h1>
      <p class="auth-intro">
        Cette interface est réservée à l’administration du portfolio.
      </p>
      <form
        class="auth-form"
        :aria-busy="form.processing"
        @submit.prevent="submit"
      >
        <FloatingField
          id="email"
          v-model="form.email"
          label="Adresse e-mail"
          type="email"
          autocomplete="username"
          :error="form.errors.email"
          required
          autofocus
        />
        <FloatingField
          id="password"
          v-model="form.password"
          label="Mot de passe"
          type="password"
          autocomplete="current-password"
          :error="form.errors.password"
          required
        />
        <button
          class="button"
          type="submit"
          :disabled="form.processing"
        >
          <span
            v-if="form.processing"
            class="spinner"
          /><span>{{ form.processing ? 'Connexion…' : 'Se connecter' }}</span>
        </button>
      </form>
      <Link href="/">
        ← Retour au portfolio
      </Link>
    </section>
  </main>
</template>
