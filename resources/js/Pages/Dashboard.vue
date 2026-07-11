<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import DashboardLayout from '../Layouts/DashboardLayout.vue';
import type { SharedProps } from '../types';
defineProps<{ projects: Array<{ id: number; title: string }> }>();
const page = usePage<SharedProps>();
const user = computed(() => page.props.auth.user);
</script>
<template>
  <Head title="Dashboard" />
  <DashboardLayout :user-name="user?.name">
    <section
      class="panel"
      aria-labelledby="projects-title"
    >
      <div class="section-heading">
        <div>
          <p class="eyebrow">
            Contenu
          </p><h2 id="projects-title">
            Projets
          </h2>
        </div><span class="badge">Brouillon</span>
      </div>
      <div
        class="alert"
        role="status"
      >
        <strong>Socle prêt.</strong> Les prochains modules réutiliseront ces composants et états.
      </div>
      <div
        v-if="projects.length"
        class="card-grid"
      >
        <article
          v-for="project in projects"
          :key="project.id"
          class="card"
        >
          <h3>{{ project.title }}</h3>
        </article>
      </div>
      <div
        v-else
        class="state"
        role="status"
      >
        <h3>Aucun projet</h3><p>Les projets apparaîtront ici une fois le module de contenu disponible.</p>
      </div>
      <div class="table-wrap">
        <table>
          <caption>Exemple de tableau accessible</caption><thead>
            <tr>
              <th scope="col">
                Élément
              </th><th scope="col">
                État
              </th>
            </tr>
          </thead><tbody><tr><td>Design system</td><td><span class="badge badge--success">Actif</span></td></tr></tbody>
        </table>
      </div>
    </section>
  </DashboardLayout>
</template>
