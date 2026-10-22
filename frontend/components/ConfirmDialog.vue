<script setup>
defineProps({ open: Boolean, title: { type: String, default: 'Confirmer la suppression' }, message: String, busy: Boolean })
const emit = defineEmits(['cancel', 'confirm'])
</script>

<template>
  <BaseModal :open="open" :title="title" width="440px" @close="emit('cancel')">
    <p style="margin:0">{{ message }}</p>
    <template #footer>
      <button class="btn btn-secondary" type="button" @click="emit('cancel')">Annuler</button>
      <button class="btn btn-danger" type="button" :disabled="busy" @click="emit('confirm')">
        {{ busy ? 'Suppression…' : 'Supprimer' }}
      </button>
    </template>
  </BaseModal>
</template>
