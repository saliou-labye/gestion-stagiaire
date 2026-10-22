<script setup>
const menuOpen = ref(false)
const route = useRoute()
const { collapsed } = useSidebar()
watch(() => route.fullPath, () => { menuOpen.value = false })
</script>

<template>
  <div class="shell" :style="{ '--sidebar-w': collapsed ? '88px' : '280px' }">
    <AppSidebar :open="menuOpen" @navigate="menuOpen = false" />
    <div v-if="menuOpen" class="backdrop" @click="menuOpen = false" />
    <div class="main">
      <AppTopbar @toggle="menuOpen = !menuOpen" />
      <main class="content"><slot /></main>
    </div>
  </div>
</template>

<style scoped>
.main { margin-left: var(--sidebar-w); min-height: 100vh; transition: margin-left .2s ease; }
.content { padding: 32px; max-width: 1440px; }
.backdrop { position: fixed; inset: 0; background: rgba(9, 31, 92, .5); z-index: 25; }
@media (max-width: 900px) {
  .main { margin-left: 0; }
  .content { padding: 20px 14px; }
}
</style>
