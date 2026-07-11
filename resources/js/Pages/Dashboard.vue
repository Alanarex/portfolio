<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import type { SharedProps } from '../types';

defineProps<{ projects: Array<{ id: number; title: string }> }>();
const page = usePage<SharedProps>();
const user = computed(() => page.props.auth.user);
const loggingOut = ref(false);
const logout = () => {
    loggingOut.value = true;
    router.post('/logout', {}, { onFinish: () => { loggingOut.value = false; } });
};
</script>

<template>
  <Head title="Dashboard" />
  <div class="dashboard-shell">
    <header class="dashboard-header">
      <div>
        <p class="eyebrow">
          Portfolio Platform
        </p><h1>Dashboard</h1>
      </div>
      <button
        type="button"
        :disabled="loggingOut"
        @click="logout"
      >
        {{ loggingOut ? 'Déconnexion…' : 'Se déconnecter' }}
      </button>
    </header>
    <main>
      <p v-if="user">
        Connecté en tant que <strong>{{ user.name }}</strong>.
      </p>
      <section
        class="panel"
        aria-labelledby="projects-title"
      >
        <h2 id="projects-title">
          Projets
        </h2>
        <ul v-if="projects.length">
          <li
            v-for="project in projects"
            :key="project.id"
          >
            {{ project.title }}
          </li>
        </ul>
        <div
          v-else
          class="empty-state"
        >
          <h3>Aucun projet</h3>
          <p>Les projets apparaîtront ici une fois le module de contenu disponible.</p>
        </div>
      </section>
    </main>
  </div>
</template>
