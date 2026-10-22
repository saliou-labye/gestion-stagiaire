<script setup>
const props = defineProps({ open: Boolean, title: String, width: { type: String, default: '560px' } })
const emit = defineEmits(['close'])

const onKey = (e) => { if (e.key === 'Escape' && props.open) emit('close') }
onMounted(() => window.addEventListener('keydown', onKey))
onBeforeUnmount(() => window.removeEventListener('keydown', onKey))
</script>

<template>
  <Teleport to="body">
    <div v-if="open" class="overlay" @mousedown.self="emit('close')">
      <div class="modal card" role="dialog" aria-modal="true" :aria-label="title" :style="{ maxWidth: width }">
        <header class="modal-head">
          <h2>{{ title }}</h2>
          <button class="btn-icon" type="button" aria-label="Fermer" @click="emit('close')"><AppIcon name="close" /></button>
        </header>
        <div class="modal-body"><slot /></div>
        <footer v-if="$slots.footer" class="modal-foot"><slot name="footer" /></footer>
      </div>
    </div>
  </Teleport>
</template>

<style scoped>
.overlay { position: fixed; inset: 0; z-index: 50; background: rgba(9, 31, 92, .45); display: flex; align-items: center; justify-content: center; padding: 16px; overflow-y: auto; }
.modal { width: 100%; max-height: calc(100vh - 32px); display: flex; flex-direction: column; }
.modal-head { display: flex; align-items: center; justify-content: space-between; padding: 18px 22px; border-bottom: 1px solid var(--border); }
.modal-head h2 { font-size: 19px; }
.modal-body { padding: 22px; overflow-y: auto; }
.modal-foot { padding: 14px 22px; border-top: 1px solid var(--border); background: var(--snow); display: flex; justify-content: flex-end; gap: 10px; border-radius: 0 0 var(--radius) var(--radius); }
</style>
