<script setup>
defineProps({
  label: String,
  value: [String, Number],
  icon: String,
  tone: { type: String, default: 'royal' },
  hint: String,
})
</script>

<template>
  <div class="card stat">
    <div class="stat-icon" :class="tone"><AppIcon :name="icon" :size="22" /></div>
    <div class="stat-body">
      <div class="stat-label">{{ label }}</div>
      <div class="stat-value">{{ value }}</div>
      <div v-if="hint" class="stat-hint">{{ hint }}</div>
    </div>
  </div>
</template>

<style scoped>
.stat { display: flex; gap: 14px; align-items: center; padding: 18px; }
.stat-icon { width: 46px; height: 46px; border-radius: 12px; display: grid; place-items: center; flex: none; }
.stat-icon.royal { background: var(--ice); color: var(--royal); }
.stat-icon.navy { background: var(--mist); color: var(--navy); }
.stat-icon.sky { background: var(--mist); color: #3d6bb3; }
.stat-icon.success { background: var(--success-bg); color: var(--success); }
.stat-icon.gold { background: var(--warn-bg); color: var(--warn); }
.stat-label { font-size: 13px; color: var(--text-soft); font-weight: 600; }
.stat-value { font-size: 28px; font-weight: 800; line-height: 1.1; margin-top: 2px; }
.stat-hint { font-size: 12.5px; color: var(--text-soft); margin-top: 2px; }
</style>
