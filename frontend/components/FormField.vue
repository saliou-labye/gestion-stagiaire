<script setup>
// Champ de formulaire : label, contrôle (slot) et message d'erreur (client ou serveur Laravel).
defineProps({ label: String, error: String, required: Boolean, hint: String, forId: String })
</script>

<template>
  <div class="field">
    <label :for="forId">{{ label }} <span v-if="required" class="req">*</span></label>
    <slot />
    <span v-if="hint && !error" class="field-hint">{{ hint }}</span>
    <span v-if="error" class="field-error" role="alert">{{ error }}</span>
  </div>
</template>
