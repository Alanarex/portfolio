<script setup lang="ts">
defineProps<{ title: string; description?: string; eyebrow?: string }>();
</script>

<template>
  <header class="flex flex-wrap items-start justify-between gap-4">
    <div>
      <p class="admin-eyebrow">{{ eyebrow ?? 'Administration' }}</p>
      <h1 class="mt-3 text-3xl font-semibold sm:text-4xl">{{ title }}</h1>
      <p v-if="description" class="mt-2 max-w-3xl text-sm text-muted-foreground">{{ description }}</p>
    </div>
    <div v-if="$slots.default" class="flex flex-wrap gap-2"><slot /></div>
  </header>
</template>
