<script setup>
const props = defineProps({ statut: { type: String, required: true } })
const label = computed(() => statutLabel(props.statut).toUpperCase())
</script>

<template>
  <span class="badge" :class="`badge-${statut}`">{{ label }}</span>
</template>

<style scoped>
.badge { display: inline-block; padding: 4px 10px; border-radius: 8px; font-size: 12px; font-weight: 700; letter-spacing: .02em; white-space: nowrap; }
.badge-en_attente { background: var(--neutral-bg); color: var(--neutral); }
.badge-en_cours { background: var(--ice); color: var(--royal); }
.badge-termine { background: var(--success-bg); color: var(--success); }
</style>
