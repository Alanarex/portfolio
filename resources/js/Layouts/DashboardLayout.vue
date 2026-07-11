<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
defineProps<{ userName?: string }>();
const loggingOut = ref(false);
const logout = () => { loggingOut.value = true; router.post('/logout', {}, { onFinish: () => { loggingOut.value = false; } }); };
</script>
<template>
  <a
    class="skip-link"
    href="#dashboard-content"
  >Aller au contenu</a>
  <div class="dashboard-shell">
    <header class="dashboard-header">
      <div class="dashboard-header__inner">
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
      </div>
    </header>
    <div class="dashboard-page">
      <aside
        class="dashboard-sidebar"
        aria-label="Navigation administration"
      >
        <a
          href="/dashboard"
          aria-current="page"
        >Vue d’ensemble</a>
        <span aria-disabled="true">Contenu</span>
        <span aria-disabled="true">Paramètres</span>
      </aside>
      <main id="dashboard-content">
        <p
          v-if="userName"
          class="dashboard-meta"
        >
          Connecté en tant que <strong>{{ userName }}</strong>.
        </p><slot />
      </main>
    </div>
  </div>
</template>
