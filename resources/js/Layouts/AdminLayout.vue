<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage<{ flash: { success?: string } }>();
</script>

<template>
  <div class="min-h-screen bg-background text-foreground">
    <a href="#admin-content" class="admin-link sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-md focus:bg-primary focus:px-4 focus:py-3 focus:text-primary-foreground">
      Aller au contenu
    </a>
    <header class="border-b border-border bg-surface">
      <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.16em] text-primary">Espace privé</p>
          <p class="font-display text-lg font-bold">Administration du portfolio</p>
        </div>
        <nav aria-label="Administration" class="flex flex-wrap items-center gap-2 text-sm">
          <Link href="/dashboard/profile" class="admin-link rounded-md px-3 py-2 hover:bg-accent">Profil</Link>
          <Link href="/dashboard/settings" class="admin-link rounded-md px-3 py-2 hover:bg-accent">Paramètres</Link>
          <Link href="/" class="admin-link rounded-md px-3 py-2 hover:bg-accent">Voir le portfolio</Link>
          <Link href="/logout" method="post" as="button" class="admin-link rounded-md border border-border px-3 py-2 hover:bg-accent">Déconnexion</Link>
        </nav>
      </div>
    </header>
    <div v-if="page.props.flash?.success" class="mx-auto mt-4 max-w-6xl px-4 sm:px-6" role="status" aria-live="polite">
      <div class="rounded-lg border border-success/40 bg-success/10 px-4 py-3 text-sm">{{ page.props.flash.success }}</div>
    </div>
    <main id="admin-content" class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
      <slot />
    </main>
  </div>
</template>
