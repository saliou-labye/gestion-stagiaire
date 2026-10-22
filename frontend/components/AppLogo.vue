<script setup>
// light : version claire pour fond sombre ; compact : pictogramme seul
defineProps({ light: Boolean, compact: Boolean })
</script>

<template>
  <span class="logo">
    <svg width="38" height="30" viewBox="0 0 38 30" aria-hidden="true">
      <circle cx="19" cy="9" r="6" :fill="light ? '#FFFFFF' : '#334DAF'" />
      <path d="M7 28c0-7 5-11 12-11s12 4 12 11z" :fill="light ? '#FFFFFF' : '#334DAF'" />
      <circle cx="7" cy="13" r="4" fill="#7096D1" />
      <path d="M0 27c0-5 3-8 8-8 1.5 0 3 .3 4 .9-2 2-3 4.5-3 7.1z" fill="#7096D1" />
      <circle cx="31" cy="13" r="4" :fill="light ? '#D0E4FE' : '#091F5C'" />
      <path d="M38 27c0-5-3-8-8-8-1.5 0-3 .3-4 .9 2 2 3 4.5 3 7.1z" :fill="light ? '#D0E4FE' : '#091F5C'" />
    </svg>
    <span v-if="!compact" class="logo-text" :class="{ light }"><b>Intern</b>central</span>
  </span>
</template>

<style scoped>
.logo { display: inline-flex; align-items: center; gap: 10px; }
.logo-text { font-size: 24px; letter-spacing: -0.3px; color: var(--navy); font-weight: 500; }
.logo-text.light { color: #fff; }
.logo-text b { font-weight: 800; }
</style>
