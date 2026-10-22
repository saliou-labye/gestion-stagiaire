<script setup>
const api = useApi()
const route = useRoute()

const stagiaire = ref(null)
const loading = ref(true)
const error = ref('')

const load = async () => {
  loading.value = true
  error.value = ''
  try {
    stagiaire.value = await api.get(`/stagiaires/${route.params.id}`)
  } catch (e) {
    error.value = parseApiError(e).message
  } finally {
    loading.value = false
  }
}
onMounted(load)
</script>

<template>
  <NuxtLink to="/stagiaires" class="back"><AppIcon name="back" :size="18" /> Retour aux stagiaires</NuxtLink>

  <StateBox :loading="loading" :error="error" @retry="load" />

  <template v-if="stagiaire && !loading">
    <PageHeader :title="fullName(stagiaire)" :subtitle="`Fiche de ${matricule(stagiaire.id)}`" />
    <StagiaireDetail :stagiaire="stagiaire" />
  </template>
</template>

<style scoped>
.back { display: inline-flex; align-items: center; gap: 6px; margin-bottom: 14px; font-weight: 600; font-size: 15px; }
</style>
