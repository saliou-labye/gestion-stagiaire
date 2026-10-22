<script setup>
defineProps({ title: String, subtitle: String })
</script>

<template>
  <div class="page-head">
    <div>
      <h1>{{ title }}</h1>
      <p v-if="subtitle">{{ subtitle }}</p>
    </div>
    <div><slot /></div>
  </div>
</template>
