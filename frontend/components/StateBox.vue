<script setup>
// Affiche chargement, erreur ou liste vide à la place d'un tableau.
defineProps({ loading: Boolean, error: String, empty: Boolean, emptyTitle: { type: String, default: 'Aucun résultat' }, emptyText: String })
const emit = defineEmits(['retry'])
</script>

<template>
  <div v-if="loading" class="state-box"><div class="spinner" /> Chargement…</div>
  <div v-else-if="error" class="state-box">
    <strong>Oups, un problème est survenu</strong>
    {{ error }}
    <div style="margin-top:14px"><button class="btn btn-secondary" @click="emit('retry')">Réessayer</button></div>
  </div>
  <div v-else-if="empty" class="state-box"><strong>{{ emptyTitle }}</strong>{{ emptyText }}<slot /></div>
</template>
