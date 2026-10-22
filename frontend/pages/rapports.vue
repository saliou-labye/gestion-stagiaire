<script setup>
const api = useApi()
const toast = useToast()
const busy = ref('')

// Génère et télécharge un fichier CSV (séparateur « ; » pour une ouverture directe dans Excel en français)
const downloadCsv = (filename, headers, rows) => {
  const esc = (v) => `"${String(v ?? '').replace(/"/g, '""')}"`
  const lines = [headers.map(esc).join(';'), ...rows.map((r) => r.map(esc).join(';'))]
  const blob = new Blob(['\uFEFF' + lines.join('\r\n')], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = filename
  a.click()
  URL.revokeObjectURL(url)
}

const reports = [
  {
    key: 'stagiaires', title: 'Liste des stagiaires', text: 'Identité, contact, statut et dates de stage.',
    run: async () => {
      const data = await api.get('/stagiaires')
      downloadCsv('stagiaires.csv', ['ID', 'Nom', 'Prénom', 'Email', 'Téléphone', 'Statut', 'Début', 'Fin'],
        data.map((s) => [matricule(s.id), s.nom, s.prenom, s.email, s.telephone, statutLabel(s.statut), formatDate(s.date_debut), formatDate(s.date_fin)]))
    },
  },
  {
    key: 'affectations', title: 'Affectations', text: 'Stagiaire, service, encadreur et date d\'affectation.',
    run: async () => {
      const data = await api.get('/affectations')
      downloadCsv('affectations.csv', ['Stagiaire', 'Service', 'Encadreur', 'Date', 'Observations'],
        data.map((a) => [fullName(a.stagiaire), a.service?.nom, fullName(a.encadreur), formatDate(a.date_affectation), a.observations]))
    },
  },
  {
    key: 'evaluations', title: 'Évaluations', text: 'Notes technique et comportement avec commentaires.',
    run: async () => {
      const data = await api.get('/evaluations')
      downloadCsv('evaluations.csv', ['Stagiaire', 'Date', 'Note technique', 'Note comportement', 'Commentaire'],
        data.map((e) => [fullName(e.stagiaire), formatDate(e.date_evaluation), note(e.note_technique), note(e.note_comportement), e.commentaire]))
    },
  },
]

const generate = async (r) => {
  busy.value = r.key
  try {
    await r.run()
    toast.success('Rapport téléchargé.')
  } catch (e) {
    toast.error(parseApiError(e).message)
  } finally {
    busy.value = ''
  }
}
</script>

<template>
  <PageHeader title="Rapports" subtitle="Exportez vos données au format CSV (compatible Excel)" />
  <div class="grid">
    <section v-for="r in reports" :key="r.key" class="card card-pad item">
      <h2>{{ r.title }}</h2>
      <p class="muted">{{ r.text }}</p>
      <button class="btn btn-primary" :disabled="busy === r.key" @click="generate(r)">
        <AppIcon name="download" :size="18" /> {{ busy === r.key ? 'Génération…' : 'Exporter en CSV' }}
      </button>
    </section>
  </div>
</template>

<style scoped>
.grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; }
.item { display: flex; flex-direction: column; gap: 10px; align-items: flex-start; }
.item h2 { font-size: 18px; }
.item p { margin: 0 0 6px; flex: 1; }
</style>
