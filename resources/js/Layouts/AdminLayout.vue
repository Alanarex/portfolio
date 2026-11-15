<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

type NavItem = { href: string; label: string; icon: string; exact?: boolean };
type NavGroup = { label: string; items: NavItem[] };

const page = usePage<{ flash: { success?: string }; auth: { user: { name: string } | null } }>();

// Icônes 24×24 au trait, décoratives (aria-hidden) : le libellé porte le sens.
const icons: Record<string, string> = {
  home: 'M3 11.5 12 4l9 7.5M5.5 10v9.5h13V10',
  user: 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 8.5c0-3.6 3.1-6 7-6s7 2.4 7 6',
  route: 'M6 19a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm12-10a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM8 17h6.5a3.5 3.5 0 0 0 0-7h-5a3.5 3.5 0 0 1 0-7H16',
  skills: 'M4 20V10m6 10V4m6 16v-7m4 7H2',
  folder: 'M3 7.5A1.5 1.5 0 0 1 4.5 6H10l2 2.5h7.5A1.5 1.5 0 0 1 21 10v8a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 18Z',
  news: 'M5 4h11a2 2 0 0 1 2 2v13H7a2 2 0 0 1-2-2Zm13 3h2v10a2 2 0 0 1-2 2M8.5 8.5h6m-6 3h6m-6 3h4',
  settings: 'M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm7.4-2.3 1.6 1.2-1.8 3.1-1.9-.7a7 7 0 0 1-1.5.9L15.5 19.5h-3.6l-.3-1.8a7 7 0 0 1-1.5-.9l-1.9.7L6.4 14.4 8 13.2a7 7 0 0 1 0-1.8L6.4 10.2l1.8-3.1 1.9.7a7 7 0 0 1 1.5-.9l.3-1.8h3.6l.3 1.8a7 7 0 0 1 1.5.9l1.9-.7 1.8 3.1-1.6 1.2a7 7 0 0 1 0 1.8Z',
  cube: 'm12 3 8 4.5v9L12 21l-8-4.5v-9Zm0 9 8-4.5M12 12 4 7.5M12 12v9',
  external: 'M14 4h6v6m0-6-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5',
  logout: 'M15 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3M10 8l-4 4 4 4m-4-4h11',
  menu: 'M4 6.5h16M4 12h16M4 17.5h16',
  close: 'm6 6 12 12M18 6 6 18',
  sun: 'M12 16.5a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9ZM12 2.5v2m0 15v2M4.9 4.9l1.4 1.4m11.4 11.4 1.4 1.4M2.5 12h2m15 0h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4',
  moon: 'M20 14.5A8 8 0 0 1 9.5 4a8 8 0 1 0 10.5 10.5Z',
};

const groups: NavGroup[] = [
  { label: 'Aperçu', items: [{ href: '/dashboard', label: 'Accueil', icon: 'home', exact: true }] },
  {
    label: 'Contenu',
    items: [
      { href: '/dashboard/profile', label: 'Profil', icon: 'user' },
      { href: '/dashboard/career', label: 'Parcours', icon: 'route' },
      { href: '/dashboard/skills', label: 'Compétences', icon: 'skills' },
      { href: '/dashboard/projects', label: 'Projets', icon: 'folder' },
      { href: '/dashboard/posts', label: 'Publications', icon: 'news' },
    ],
  },
  {
    label: 'Site',
    items: [
      { href: '/dashboard/settings', label: 'Paramètres', icon: 'settings' },
      { href: '/dashboard/scene', label: 'Scène 3D', icon: 'cube' },
    ],
  },
];

const path = computed(() => page.url.split('?')[0] ?? '');

function isActive(item: NavItem): boolean {
  return item.exact === true ? path.value === item.href : path.value === item.href || path.value.startsWith(`${item.href}/`);
}

// Menu mobile : tiroir ouvert/fermé, focus rendu au bouton à la fermeture.
const menuOpen = ref(false);
const menuButton = ref<HTMLButtonElement | null>(null);
const sidebar = ref<HTMLElement | null>(null);

async function openMenu(): Promise<void> {
  menuOpen.value = true;
  await nextTick();
  sidebar.value?.querySelector<HTMLElement>('a[href]')?.focus();
}

function closeMenu(restoreFocus = true): void {
  if (!menuOpen.value) return;
  menuOpen.value = false;
  if (restoreFocus) void nextTick(() => menuButton.value?.focus());
}

function onKeydown(event: KeyboardEvent): void {
  if (event.key === 'Escape') closeMenu();
}

watch(path, () => closeMenu(false));

// Thème : même clé de stockage que le site public, pour un choix partagé.
type Theme = 'dark' | 'light';
const theme = ref<Theme>('dark');

function readTheme(): Theme {
  return document.documentElement.getAttribute('data-theme') === 'light' ? 'light' : 'dark';
}

function toggleTheme(): void {
  theme.value = theme.value === 'dark' ? 'light' : 'dark';
  document.documentElement.setAttribute('data-theme', theme.value);
  try {
    localStorage.setItem('portfolio-theme', theme.value);
  } catch {
    // Stockage indisponible : le thème reste valable pour la page courante.
  }
}

onMounted(() => {
  theme.value = readTheme();
  document.addEventListener('keydown', onKeydown);
});
onBeforeUnmount(() => document.removeEventListener('keydown', onKeydown));
</script>

<template>
  <div class="admin-shell min-h-screen">
    <a href="#admin-content" class="admin-link sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-md focus:bg-white focus:px-4 focus:py-3 focus:font-bold focus:text-[#0b090a]">
      Aller au contenu
    </a>

    <div v-if="menuOpen" class="fixed inset-0 z-30 bg-black/60 lg:hidden" aria-hidden="true" @click="closeMenu()" />

    <aside
      id="admin-sidebar"
      ref="sidebar"
      class="fixed inset-y-0 left-0 z-40 flex w-72 max-w-[85vw] flex-col border-r border-border bg-surface transition-transform duration-200 lg:translate-x-0"
      :class="menuOpen ? 'translate-x-0' : '-translate-x-full'"
    >
      <div class="flex h-16 shrink-0 items-center justify-between gap-3 px-5">
        <Link href="/dashboard" class="admin-link flex items-center gap-1 rounded-md py-2 font-display text-xl font-bold tracking-tight">
          Portfolio<span class="inline-block size-2 rounded-full bg-brand" aria-hidden="true" />
        </Link>
        <button type="button" class="admin-link inline-flex size-11 items-center justify-center rounded-full lg:hidden" aria-label="Fermer le menu" @click="closeMenu()">
          <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path :d="icons.close" /></svg>
        </button>
      </div>

      <nav aria-label="Administration" class="flex-1 space-y-6 overflow-y-auto px-3 py-4">
        <div v-for="group in groups" :key="group.label">
          <p class="admin-eyebrow mb-2 px-3">{{ group.label }}</p>
          <ul class="space-y-1">
            <li v-for="item in group.items" :key="item.href">
              <Link
                :href="item.href"
                class="admin-link flex min-h-11 items-center gap-3 rounded-lg px-3 text-sm font-medium"
                :class="isActive(item) ? 'bg-surface-strong text-foreground shadow-[inset_3px_0_0_var(--accent)]' : 'text-muted-foreground hover:text-foreground'"
                :aria-current="isActive(item) ? 'page' : undefined"
              >
                <svg viewBox="0 0 24 24" class="size-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path :d="icons[item.icon]" /></svg>
                {{ item.label }}
              </Link>
            </li>
          </ul>
        </div>
      </nav>

      <div class="shrink-0 space-y-1 border-t border-border p-3">
        <a href="/" class="admin-link flex min-h-11 items-center gap-3 rounded-lg px-3 text-sm font-medium text-muted-foreground hover:text-foreground">
          <svg viewBox="0 0 24 24" class="size-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path :d="icons.external" /></svg>
          Voir le portfolio
        </a>
      </div>
    </aside>

    <div class="lg:pl-72">
      <header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-border bg-background/85 px-4 backdrop-blur sm:px-6 lg:px-8">
        <button
          ref="menuButton"
          type="button"
          class="admin-link inline-flex size-11 items-center justify-center rounded-full border border-border-strong lg:hidden"
          aria-label="Ouvrir le menu"
          aria-controls="admin-sidebar"
          :aria-expanded="menuOpen"
          @click="openMenu"
        >
          <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path :d="icons.menu" /></svg>
        </button>
        <p class="admin-eyebrow hidden sm:block">Espace privé</p>

        <div class="ml-auto flex items-center gap-2">
          <button
            type="button"
            class="admin-link inline-flex size-11 items-center justify-center rounded-full border border-border-strong"
            :aria-label="theme === 'dark' ? 'Passer au thème clair' : 'Passer au thème sombre'"
            @click="toggleTheme"
          >
            <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path :d="theme === 'dark' ? icons.sun : icons.moon" /></svg>
          </button>
          <span v-if="page.props.auth?.user" class="hidden max-w-40 truncate text-sm text-muted-foreground md:inline">{{ page.props.auth.user.name }}</span>
          <Link href="/logout" method="post" as="button" class="admin-link inline-flex min-h-11 items-center gap-2 rounded-full border border-border-strong px-4 text-sm font-medium">
            <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path :d="icons.logout" /></svg>
            Déconnexion
          </Link>
        </div>
      </header>

      <div v-if="page.props.flash?.success" class="mx-auto mt-4 max-w-6xl px-4 sm:px-6 lg:px-8" role="status" aria-live="polite">
        <div class="rounded-xl border border-success/40 bg-success/10 px-4 py-3 text-sm">{{ page.props.flash.success }}</div>
      </div>

      <main id="admin-content" tabindex="-1" class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        <slot />
      </main>
    </div>
  </div>
</template>

<style>
.admin-shell {
  background-image: var(--shell-glow);
  background-repeat: no-repeat;
}

/* Sidebar masquée du clavier et des lecteurs d'écran quand le tiroir mobile est fermé. */
@media (width < 64rem) {
  #admin-sidebar.-translate-x-full {
    visibility: hidden;
    transition: transform 200ms, visibility 0s 200ms;
  }
}
</style>
