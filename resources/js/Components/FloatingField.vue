<script setup lang="ts">
import { computed, ref } from 'vue';

const props = defineProps<{
  id: string;
  label: string;
  type?: string;
  autocomplete?: string;
  error?: string;
  required?: boolean;
  autofocus?: boolean;
  multiline?: boolean;
  rows?: number;
  maxlength?: number;
}>();
const model = defineModel<string>({ default: '' });
const revealed = ref(false);
const isPassword = computed(() => props.type === 'password');
const inputType = computed(() => (isPassword.value && revealed.value ? 'text' : (props.type ?? 'text')));
</script>
<template>
  <div
    class="field"
    :class="{ 'field--error': error, 'field--with-action': isPassword }"
  >
    <textarea
      v-if="multiline"
      :id="id"
      v-model="model"
      class="field__input field__input--multiline"
      placeholder=" "
      :rows="rows ?? 5"
      :maxlength="maxlength"
      :required="required"
      :aria-invalid="Boolean(error)"
      :aria-describedby="error ? `${id}-error` : undefined"
    />
    <input
      v-else
      :id="id"
      v-model="model"
      class="field__input"
      :type="inputType"
      placeholder=" "
      :autocomplete="autocomplete"
      :required="required"
      :autofocus="autofocus"
      :aria-invalid="Boolean(error)"
      :aria-describedby="error ? `${id}-error` : undefined"
    >
    <label
      class="field__label"
      :for="id"
    >{{ label }}</label>
    <button
      v-if="isPassword"
      type="button"
      class="field__reveal"
      :aria-label="revealed ? 'Masquer le mot de passe' : 'Afficher le mot de passe'"
      :aria-pressed="revealed"
      @click="revealed = !revealed"
    >
      <svg
        width="20"
        height="20"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.8"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
      ><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z" /><circle
        cx="12"
        cy="12"
        r="3"
      /></svg>
    </button>
    <p
      v-if="error"
      :id="`${id}-error`"
      class="error"
      role="alert"
    >
      {{ error }}
    </p>
  </div>
</template>
