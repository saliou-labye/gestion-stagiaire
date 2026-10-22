<script setup>
// Fiche complète d'un stagiaire (lecture seule) : utilisée par l'administrateur et par « Mon espace ».
const props = defineProps({ stagiaire: { type: Object, required: true } })

const moyenne = computed(() => {
  const ev = props.stagiaire.evaluations ?? []
  if (!ev.length) return null
  const total = ev.reduce((s, e) => s + (Number(e.note_technique) + Number(e.note_comportement)) / 2, 0)
  return total / ev.length
})
</script>

<template>
  <section class="card card-pad" style="margin-bottom: 22px">
    <div class="head">
      <h2 class="section-title">Informations personnelles</h2>
      <StatusBadge :statut="stagiaire.statut" />
    </div>
    <dl class="info-grid">
      <div><dt>Nom complet</dt><dd>{{ fullName(stagiaire) }}</dd></div>
      <div><dt>Identifiant</dt><dd class="mono">{{ matricule(stagiaire.id) }}</dd></div>
      <div><dt>Email</dt><dd>{{ stagiaire.email }}</dd></div>
      <div><dt>Téléphone</dt><dd>{{ stagiaire.telephone || '—' }}</dd></div>
      <div><dt>Début du stage</dt><dd>{{ formatDate(stagiaire.date_debut) }}</dd></div>
      <div><dt>Fin du stage</dt><dd>{{ formatDate(stagiaire.date_fin) }}</dd></div>
    </dl>
  </section>

  <section class="stats-grid" style="grid-template-columns: repeat(3, 1fr)">
    <StatCard label="Moyenne générale" :value="moyenne === null ? '—' : `${note(moyenne)}/20`" icon="star" tone="gold" />
    <StatCard label="Affectations" :value="stagiaire.affectations?.length ?? 0" icon="link" tone="royal" />
    <StatCard label="Évaluations" :value="stagiaire.evaluations?.length ?? 0" icon="list" tone="sky" />
  </section>

  <div class="two-col">
    <section class="card">
      <h2 class="section-title pad">Affectations</h2>
      <StateBox :empty="!stagiaire.affectations?.length" empty-title="Aucune affectation" />
      <div v-if="stagiaire.affectations?.length" class="table-wrap">
        <table class="data" style="min-width: 420px">
          <thead><tr><th>Service</th><th>Encadreur</th><th>Date</th></tr></thead>
          <tbody>
            <tr v-for="a in stagiaire.affectations" :key="a.id">
              <td>{{ a.service?.nom ?? '—' }}</td>
              <td>{{ fullName(a.encadreur) }}</td>
              <td>{{ formatDate(a.date_affectation) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <section class="card">
      <h2 class="section-title pad">Évaluations</h2>
      <StateBox :empty="!stagiaire.evaluations?.length" empty-title="Aucune évaluation" />
      <div v-if="stagiaire.evaluations?.length" class="table-wrap">
        <table class="data" style="min-width: 420px">
          <thead><tr><th>Date</th><th>Technique</th><th>Comport.</th><th>Commentaire</th></tr></thead>
          <tbody>
            <tr v-for="e in stagiaire.evaluations" :key="e.id">
              <td>{{ formatDate(e.date_evaluation) }}</td>
              <td class="mono">{{ note(e.note_technique) }}/20</td>
              <td class="mono">{{ note(e.note_comportement) }}/20</td>
              <td class="muted">{{ e.commentaire || '—' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </div>
</template>

<style scoped>
.head { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 18px; }
.pad { padding: 18px 20px 12px; }
@media (max-width: 640px) { .stats-grid { grid-template-columns: 1fr !important; } }
</style>
