<script setup lang="ts">
defineProps<{ errors: Record<string, string>; title?: string }>();
</script>

<template>
  <div v-if="Object.keys(errors).length" class="admin-banner" role="alert">
    <p class="font-semibold">{{ title ?? 'Certains champs sont invalides.' }}</p>
    <ul class="mt-2 list-disc space-y-1 pl-5">
      <li v-for="(message, path) in errors" :key="path"><span class="font-mono">{{ path }}</span> : {{ message }}</li>
    </ul>
  </div>
</template>
