<script setup>
const api = useApi()

const stats = ref(null)
const recent = ref([])
const loading = ref(true)
const error = ref('')

const load = async () => {
  loading.value = true
  error.value = ''
  try {
    const [dash, affs] = await Promise.all([api.get('/dashboard'), api.get('/affectations')])
    stats.value = dash
    recent.value = (Array.isArray(affs) ? affs : []).slice(0, 5)
  } catch (e) {
    error.value = parseApiError(e).message
  } finally {
    loading.value = false
  }
}
onMounted(load)

const enAttente = computed(() =>
  stats.value ? Math.max(stats.value.total_stagiaires - stats.value.stagiaires_en_cours - stats.value.stagiaires_termines, 0) : 0)
const pct = (n) => (stats.value?.total_stagiaires ? (n / stats.value.total_stagiaires) * 100 : 0)
</script>

<template>
  <PageHeader title="Tableau de Bord" subtitle="Gestion centralisée des stagiaires, affectations et évaluations" />

  <StateBox :loading="loading" :error="error" @retry="load" />

  <template v-if="stats && !loading">
    <section class="stats-grid">
      <StatCard label="Total Stagiaires" :value="stats.total_stagiaires" icon="user" tone="royal" />
      <StatCard label="Stages en Cours" :value="stats.stagiaires_en_cours" icon="play" tone="sky" />
      <StatCard label="Stages Terminés" :value="stats.stagiaires_termines" icon="check" tone="success" />
      <StatCard label="Moyenne des Notes" :value="`${note(stats.moyenne_generale)}/20`" icon="star" tone="gold"
                :hint="`Tech. ${note(stats.moyenne_technique)} · Comport. ${note(stats.moyenne_comportement)}`" />
    </section>

    <section class="card card-pad repartition">
      <h2>Répartition des statuts</h2>
      <div class="bar" role="img" :aria-label="`${stats.stagiaires_en_cours} en cours, ${stats.stagiaires_termines} terminés, ${enAttente} en attente`">
        <span class="seg s-cours" :style="{ width: pct(stats.stagiaires_en_cours) + '%' }" />
        <span class="seg s-fini" :style="{ width: pct(stats.stagiaires_termines) + '%' }" />
        <span class="seg s-att" :style="{ width: pct(enAttente) + '%' }" />
      </div>
      <ul class="legend">
        <li><i class="dot s-cours" /> En cours ({{ stats.stagiaires_en_cours }})</li>
        <li><i class="dot s-fini" /> Terminés ({{ stats.stagiaires_termines }})</li>
        <li><i class="dot s-att" /> En attente ({{ enAttente }})</li>
      </ul>
    </section>

    <section class="card recent">
      <div class="recent-head">
        <h2><AppIcon name="list" :size="20" /> Affectations Récentes</h2>
        <NuxtLink to="/affectations" class="btn btn-secondary">Tout voir</NuxtLink>
      </div>
      <StateBox :empty="!recent.length" empty-title="Aucune affectation" empty-text="Les affectations créées apparaîtront ici." />
      <div v-if="recent.length" class="table-wrap">
        <table class="data">
          <thead>
            <tr><th>ID Stagiaire</th><th>Nom complet</th><th>Service</th><th>Encadreur</th><th>Statut</th><th>Date Début</th><th>Date Fin</th></tr>
          </thead>
          <tbody>
            <tr v-for="a in recent" :key="a.id">
              <td class="mono">{{ matricule(a.stagiaire_id) }}</td>
              <td><NuxtLink :to="`/stagiaires/${a.stagiaire_id}`">{{ fullName(a.stagiaire) }}</NuxtLink></td>
              <td>{{ a.service?.nom ?? '—' }}</td>
              <td>{{ fullName(a.encadreur) }}</td>
              <td><StatusBadge v-if="a.stagiaire" :statut="a.stagiaire.statut" /></td>
              <td>{{ formatDate(a.stagiaire?.date_debut) }}</td>
              <td>{{ formatDate(a.stagiaire?.date_fin) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </template>
</template>

<style scoped>
.repartition { margin-bottom: 22px; }
.repartition h2 { font-size: 17px; margin-bottom: 14px; }
.bar { display: flex; height: 14px; border-radius: 99px; overflow: hidden; background: var(--mist); }
.seg { display: block; height: 100%; transition: width .4s; }
.s-cours { background: var(--royal); }
.s-fini { background: var(--navy); }
.s-att { background: var(--sky); }
.legend { list-style: none; display: flex; flex-wrap: wrap; gap: 8px 22px; padding: 0; margin: 14px 0 0; font-size: 14px; }
.dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 6px; }
.recent-head { display: flex; align-items: center; justify-content: space-between; padding: 18px 20px; }
.recent-head h2 { font-size: 18px; display: flex; align-items: center; gap: 10px; }
.recent-head .btn:hover { text-decoration: none; }
</style>
