<script setup lang="ts">
defineProps<{ id: string; label: string; hint?: string; disabled?: boolean }>();
const model = defineModel<boolean>({ default: false });
</script>
<template>
  <label
    class="switch"
    :for="id"
  >
    <input
      :id="id"
      v-model="model"
      class="switch__input"
      type="checkbox"
      role="switch"
      :disabled="disabled"
    >
    <span class="switch__track" />
    <span>{{ label }}<small v-if="hint">{{ hint }}</small></span>
  </label>
</template>
