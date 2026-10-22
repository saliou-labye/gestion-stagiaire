<script setup>
const toasts = useToasts()
</script>

<template>
  <div class="toasts" aria-live="polite">
    <div v-for="t in toasts" :key="t.id" class="toast" :class="t.type">{{ t.message }}</div>
  </div>
</template>

<style scoped>
.toasts { position: fixed; right: 16px; bottom: 16px; z-index: 100; display: flex; flex-direction: column; gap: 8px; max-width: calc(100vw - 32px); }
.toast { padding: 12px 16px; border-radius: 10px; color: #fff; font-size: 14px; font-weight: 600; box-shadow: 0 6px 20px rgba(9, 31, 92, .25); }
.toast.success { background: var(--navy); border-left: 5px solid var(--sky); }
.toast.error { background: #a32121; border-left: 5px solid #fda4a4; }
</style>
