<script setup>
const emit = defineEmits(['toggle'])
const { isAdmin, roleLabel, user } = useAuth()
const route = useRoute()
const search = ref('')

// Recherche rapide (administrateur) : redirige vers la liste des stagiaires filtrée
const submitSearch = () => {
  navigateTo({ path: '/stagiaires', query: search.value.trim() ? { recherche: search.value.trim() } : {} })
}
watch(() => route.path, () => { if (route.path !== '/stagiaires') search.value = '' })

const today = new Date().toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' })
</script>

<template>
  <header class="topbar">
    <button class="btn-icon menu" type="button" aria-label="Ouvrir le menu" @click="emit('toggle')"><AppIcon name="menu" :size="24" /></button>
    <form v-if="isAdmin" class="search" role="search" @submit.prevent="submitSearch">
      <AppIcon name="search" :size="18" />
      <input v-model="search" type="search" placeholder="Rechercher un stagiaire…" aria-label="Rechercher un stagiaire" />
    </form>
    <div class="date"><AppIcon name="calendar" :size="18" /> {{ today }}</div>
    <div class="user">
      <div class="who">
        <strong>{{ user?.name ?? '…' }}</strong>
        <small>{{ roleLabel }}</small>
      </div>
    </div>
  </header>
</template>

<style scoped>
.topbar { position: sticky; top: 0; z-index: 20; display: flex; align-items: center; gap: 16px; padding: 14px 32px; background: #fff; border-bottom: 1px solid var(--border); min-height: 72px; }
.menu { display: none; color: var(--navy); }
.search { flex: 1; max-width: 440px; display: flex; align-items: center; gap: 10px; padding: 0 14px; border: 1px solid var(--sky); border-radius: 10px; background: #fff; color: var(--text-soft); }
.search:focus-within { outline: 3px solid var(--ice); border-color: var(--royal); }
.search input { flex: 1; border: 0; outline: 0; padding: 12px 0; font: inherit; font-size: 15px; color: var(--navy); background: transparent; min-width: 0; }
.date { display: flex; align-items: center; gap: 8px; margin-left: auto; padding: 10px 14px; border: 1px solid var(--sky); border-radius: 10px; font-weight: 600; font-size: 15px; white-space: nowrap; }
.who { display: flex; flex-direction: column; line-height: 1.25; text-align: right; }
.who strong { font-size: 15px; }
.who small { color: var(--text-soft); font-size: 14px; }

@media (max-width: 900px) {
  .topbar { padding: 10px 14px; gap: 10px; min-height: 64px; }
  .menu { display: inline-flex; }
  .date { display: none; }
  .who { text-align: left; }
  .search { max-width: none; }
  .user { margin-left: auto; }
}
</style>
