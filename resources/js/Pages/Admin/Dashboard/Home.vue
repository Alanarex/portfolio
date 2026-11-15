<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AdminLayout from '@admin/Layouts/AdminLayout.vue';

type Counter = { published: number; draft: number };
type Activity = { id: number; action: string; subject: string; fields: number; created_at: string | null };

const props = defineProps<{
  stats: { projects: Counter; posts: Counter; experiences: Counter; skills: Counter };
  activity: Activity[];
}>();

const page = usePage<{ auth: { user: { name: string } | null } }>();
const firstName = computed(() => page.props.auth?.user?.name?.split(' ')[0] ?? '');

const cards = computed(() => [
  { key: 'projects', label: 'Projets', href: '/dashboard/projects', draftLabel: 'brouillon(s) ou archivé(s)', counter: props.stats.projects },
  { key: 'experiences', label: 'Expériences', href: '/dashboard/career', draftLabel: 'non publiée(s)', counter: props.stats.experiences },
  { key: 'skills', label: 'Compétences', href: '/dashboard/skills', draftLabel: 'non publiée(s)', counter: props.stats.skills },
  { key: 'posts', label: 'Publications', href: '/dashboard/posts', draftLabel: 'brouillon(s)', counter: props.stats.posts },
]);

const shortcuts = [
  { href: '/dashboard/projects/create', title: 'Nouveau projet', text: 'Ajouter un projet et son étude de cas.' },
  { href: '/dashboard/posts', title: 'Nouvelle publication', text: 'Écrire une courte actualité.' },
  { href: '/dashboard/profile', title: 'Mettre à jour le profil', text: 'Présentation, contact et versions du CV.' },
  { href: '/dashboard/settings', title: 'Régler le site', text: 'Liens publics, langues et options.' },
];

function formatDate(value: string | null): string {
  return value === null ? '' : new Date(value).toLocaleString('fr-FR', { dateStyle: 'medium', timeStyle: 'short' });
}
</script>

<template>
  <Head title="Accueil — Administration" />
  <AdminLayout>
    <header>
      <p class="admin-eyebrow">Tableau de bord</p>
      <h1 class="mt-3 text-3xl font-semibold sm:text-4xl">{{ firstName ? `Bonjour ${firstName}` : 'Bonjour' }}</h1>
      <p class="mt-2 max-w-2xl text-sm text-muted-foreground">Voici l’état du contenu publié sur le portfolio. Choisissez une section dans le menu ou partez d’un raccourci.</p>
    </header>

    <section class="mt-8" aria-labelledby="stats-title">
      <h2 id="stats-title" class="sr-only">Contenu du site</h2>
      <ul class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <li v-for="card in cards" :key="card.key">
          <Link :href="card.href" class="admin-card admin-link group block h-full p-5 transition hover:border-border-strong">
            <p class="text-sm font-medium text-muted-foreground">{{ card.label }}</p>
            <p class="mt-3 font-display text-4xl font-semibold" :aria-label="`${card.counter.published} publié(s)`">{{ card.counter.published }}</p>
            <p class="mt-1 text-sm text-muted-foreground">publié(s)</p>
            <p class="mt-4 border-t border-border pt-3 text-xs text-muted-foreground">
              <span :class="card.counter.draft > 0 ? 'font-semibold text-warning' : ''">{{ card.counter.draft }}</span> {{ card.draftLabel }}
            </p>
          </Link>
        </li>
      </ul>
    </section>

    <div class="mt-10 grid gap-8 lg:grid-cols-5">
      <section class="lg:col-span-3" aria-labelledby="shortcuts-title">
        <h2 id="shortcuts-title" class="text-xl font-semibold">Raccourcis</h2>
        <ul class="mt-4 grid gap-4 sm:grid-cols-2">
          <li v-for="shortcut in shortcuts" :key="shortcut.href">
            <Link :href="shortcut.href" class="admin-card admin-link block h-full p-5 hover:border-border-strong">
              <p class="font-display text-lg font-semibold">{{ shortcut.title }}</p>
              <p class="mt-1 text-sm text-muted-foreground">{{ shortcut.text }}</p>
            </Link>
          </li>
        </ul>
      </section>

      <section class="lg:col-span-2" aria-labelledby="activity-title">
        <h2 id="activity-title" class="text-xl font-semibold">Activité récente</h2>
        <div v-if="activity.length === 0" class="mt-4 rounded-xl border border-dashed border-border-strong p-5 text-sm text-muted-foreground">Aucune modification enregistrée pour le moment.</div>
        <ol v-else class="admin-card mt-4 divide-y divide-border">
          <li v-for="event in activity" :key="event.id" class="px-5 py-3">
            <p class="font-mono text-sm break-words">{{ event.action }}</p>
            <p class="mt-1 text-xs text-muted-foreground">{{ event.subject }} · {{ event.fields }} champ(s) · <time :datetime="event.created_at ?? undefined">{{ formatDate(event.created_at) }}</time></p>
          </li>
        </ol>
      </section>
    </div>
  </AdminLayout>
</template>
