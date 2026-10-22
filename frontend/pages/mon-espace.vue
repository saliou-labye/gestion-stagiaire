<script setup>
// Espace personnel en lecture seule : fiche du stagiaire, ou stagiaires attribués à l'encadreur.
const api = useApi()
const { user, role } = useAuth()

const data = ref(null)
const loading = ref(true)
const error = ref('')
const isEncadreur = computed(() => role.value === 'encadreur')

const load = async () => {
  loading.value = true
  error.value = ''
  try {
    data.value = await api.get(isEncadreur.value ? '/me/encadreur' : '/me/stagiaire')
  } catch (e) {
    const err = parseApiError(e)
    error.value = e?.response?.status === 404
      ? "Votre compte n'est lié à aucune fiche. Contactez l'administrateur."
      : err.message
  } finally {
    loading.value = false
  }
}
onMounted(load)

// ---- Vue encadreur : un stagiaire par ligne d'affectation ----
const affectations = computed(() => data.value?.affectations ?? [])
const moyenneStagiaire = (s) => {
  const ev = s?.evaluations ?? []
  if (!ev.length) return null
  return ev.reduce((t, e) => t + (Number(e.note_technique) + Number(e.note_comportement)) / 2, 0) / ev.length
}
const stagiairesUniques = computed(() => {
  const map = new Map()
  affectations.value.forEach((a) => a.stagiaire && map.set(a.stagiaire.id, a.stagiaire))
  return [...map.values()]
})
const enCours = computed(() => stagiairesUniques.value.filter((s) => s.statut === 'en_cours').length)
const moyenneGlobale = computed(() => {
  const notes = stagiairesUniques.value.map(moyenneStagiaire).filter((m) => m !== null)
  return notes.length ? notes.reduce((a, b) => a + b, 0) / notes.length : null
})
</script>

<template>
  <PageHeader :title="`Bonjour, ${user?.name ?? ''}`"
              :subtitle="isEncadreur ? 'Vos informations et les stagiaires qui vous sont attribués' : 'Vos informations de stage'" />

  <div class="readonly-banner" role="note">
    <AppIcon name="lock" :size="20" />
    Consultation uniquement : pour toute modification, contactez l'administrateur.
  </div>

  <StateBox :loading="loading" :error="error" @retry="load" />

  <template v-if="data && !loading">
    <!-- Stagiaire -->
    <StagiaireDetail v-if="!isEncadreur" :stagiaire="data" />

    <!-- Encadreur -->
    <template v-else>
      <section class="card card-pad" style="margin-bottom: 22px">
        <h2 class="section-title" style="margin-bottom: 18px">Informations personnelles</h2>
        <dl class="info-grid">
          <div><dt>Nom complet</dt><dd>{{ fullName(data) }}</dd></div>
          <div><dt>Email</dt><dd>{{ data.email }}</dd></div>
          <div><dt>Spécialité</dt><dd>{{ data.specialite }}</dd></div>
        </dl>
      </section>

      <section class="stats-grid" style="grid-template-columns: repeat(3, 1fr)">
        <StatCard label="Stagiaires encadrés" :value="stagiairesUniques.length" icon="users" tone="royal" />
        <StatCard label="Stages en cours" :value="enCours" icon="play" tone="sky" />
        <StatCard label="Moyenne de mes stagiaires" :value="moyenneGlobale === null ? '—' : `${note(moyenneGlobale)}/20`" icon="star" tone="gold" />
      </section>

      <section class="card">
        <h2 class="section-title pad">Mes stagiaires</h2>
        <StateBox :empty="!affectations.length" empty-title="Aucun stagiaire attribué pour le moment"
                  empty-text="Les stagiaires qui vous seront affectés apparaîtront ici." />
        <div v-if="affectations.length" class="table-wrap">
          <table class="data">
            <thead>
              <tr><th>Stagiaire</th><th>Email</th><th>Service</th><th>Statut</th><th>Période du stage</th><th>Affecté le</th><th>Moyenne</th></tr>
            </thead>
            <tbody>
              <tr v-for="a in affectations" :key="a.id">
                <td><strong>{{ fullName(a.stagiaire) }}</strong></td>
                <td>{{ a.stagiaire?.email }}</td>
                <td>{{ a.service?.nom ?? '—' }}</td>
                <td><StatusBadge v-if="a.stagiaire" :statut="a.stagiaire.statut" /></td>
                <td>{{ formatDate(a.stagiaire?.date_debut) }} → {{ formatDate(a.stagiaire?.date_fin) }}</td>
                <td>{{ formatDate(a.date_affectation) }}</td>
                <td class="mono"><strong>{{ moyenneStagiaire(a.stagiaire) === null ? '—' : `${note(moyenneStagiaire(a.stagiaire))}/20` }}</strong></td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </template>
  </template>
</template>

<style scoped>
.pad { padding: 18px 20px 12px; }
@media (max-width: 640px) { .stats-grid { grid-template-columns: 1fr !important; } }
</style>
