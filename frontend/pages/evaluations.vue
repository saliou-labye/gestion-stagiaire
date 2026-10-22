<script setup>
const api = useApi()
const crud = useCrud('evaluations', { label: 'Évaluation' })
const { items, loading, loadError, saving, formErrors, formMessage } = crud

const stagiaires = ref([])
onMounted(async () => {
  crud.load()
  try { stagiaires.value = await api.get('/stagiaires') } catch { /* liste déroulante vide */ }
})

const showForm = ref(false)
const editingId = ref(null)
const clientErrors = ref({})
const blank = () => ({ stagiaire_id: '', note_technique: '', note_comportement: '', commentaire: '', date_evaluation: todayInput() })
const form = reactive(blank())
const err = (f) => clientErrors.value[f] ?? formErrors.value[f]?.[0]

const openCreate = () => { Object.assign(form, blank()); editingId.value = null; clientErrors.value = {}; crud.resetErrors(); showForm.value = true }
const openEdit = (x) => {
  Object.assign(form, {
    stagiaire_id: x.stagiaire_id, note_technique: Number(x.note_technique), note_comportement: Number(x.note_comportement),
    commentaire: x.commentaire ?? '', date_evaluation: toInputDate(x.date_evaluation),
  })
  editingId.value = x.id; clientErrors.value = {}; crud.resetErrors(); showForm.value = true
}

const noteError = (v, label) => {
  if (v === '' || v === null) return `${label} est obligatoire.`
  const n = Number(v)
  if (Number.isNaN(n) || n < 0 || n > 20) return 'La note doit être comprise entre 0 et 20.'
  return ''
}

const submit = async () => {
  crud.resetErrors()
  const e = {}
  if (!form.stagiaire_id) e.stagiaire_id = 'Choisissez un stagiaire.'
  const t = noteError(form.note_technique, 'La note technique'); if (t) e.note_technique = t
  const c = noteError(form.note_comportement, 'La note de comportement'); if (c) e.note_comportement = c
  if (!form.date_evaluation) e.date_evaluation = "La date d'évaluation est obligatoire."
  clientErrors.value = e
  if (Object.keys(e).length) return
  const payload = {
    ...form,
    note_technique: Number(form.note_technique),
    note_comportement: Number(form.note_comportement),
    commentaire: form.commentaire || null,
  }
  if (await crud.save(payload, editingId.value)) showForm.value = false
}

const moyenne = (x) => (Number(x.note_technique) + Number(x.note_comportement)) / 2

const toDelete = ref(null)
const deleting = ref(false)
const confirmDelete = async () => {
  deleting.value = true
  if (await crud.remove(toDelete.value.id)) toDelete.value = null
  deleting.value = false
}
</script>

<template>
  <PageHeader title="Évaluations" subtitle="Notes techniques et de comportement des stagiaires">
    <button class="btn btn-primary" @click="openCreate"><AppIcon name="plus" :size="18" /> Nouvelle évaluation</button>
  </PageHeader>

  <section class="card">
    <StateBox :loading="loading" :error="loadError" :empty="!items.length" empty-title="Aucune évaluation pour le moment"
              empty-text="Enregistrez une première évaluation pour alimenter la moyenne du tableau de bord." @retry="crud.load()" />
    <div v-if="!loading && !loadError && items.length" class="table-wrap">
      <table class="data">
        <thead><tr><th>Stagiaire</th><th>Date</th><th>Technique</th><th>Comportement</th><th>Moyenne</th><th>Commentaire</th><th /></tr></thead>
        <tbody>
          <tr v-for="x in items" :key="x.id">
            <td><NuxtLink :to="`/stagiaires/${x.stagiaire_id}`"><strong>{{ fullName(x.stagiaire) }}</strong></NuxtLink></td>
            <td>{{ formatDate(x.date_evaluation) }}</td>
            <td class="mono">{{ note(x.note_technique) }}/20</td>
            <td class="mono">{{ note(x.note_comportement) }}/20</td>
            <td class="mono"><strong>{{ note(moyenne(x)) }}/20</strong></td>
            <td class="muted">{{ x.commentaire || '—' }}</td>
            <td>
              <div class="cell-actions">
                <button class="btn-icon" title="Modifier" aria-label="Modifier" @click="openEdit(x)"><AppIcon name="edit" :size="18" /></button>
                <button class="btn-icon danger" title="Supprimer" aria-label="Supprimer" @click="toDelete = x"><AppIcon name="trash" :size="18" /></button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>

  <BaseModal :open="showForm" :title="editingId ? 'Modifier l\'évaluation' : 'Nouvelle évaluation'" @close="showForm = false">
    <form id="eval-form" novalidate @submit.prevent="submit">
      <div v-if="formMessage" class="alert alert-error" style="margin-bottom:16px" role="alert">{{ formMessage }}</div>
      <div class="form-grid">
        <FormField class="full" label="Stagiaire" required :error="err('stagiaire_id')" for-id="stagiaire_id">
          <select id="stagiaire_id" v-model="form.stagiaire_id" class="select" :class="{ invalid: err('stagiaire_id') }">
            <option value="" disabled>Sélectionner…</option>
            <option v-for="s in stagiaires" :key="s.id" :value="s.id">{{ fullName(s) }}</option>
          </select>
        </FormField>
        <FormField label="Note technique (0–20)" required :error="err('note_technique')" for-id="note_technique">
          <input id="note_technique" v-model="form.note_technique" type="number" min="0" max="20" step="0.5" class="input" :class="{ invalid: err('note_technique') }" />
        </FormField>
        <FormField label="Note de comportement (0–20)" required :error="err('note_comportement')" for-id="note_comportement">
          <input id="note_comportement" v-model="form.note_comportement" type="number" min="0" max="20" step="0.5" class="input" :class="{ invalid: err('note_comportement') }" />
        </FormField>
        <FormField class="full" label="Date d'évaluation" required :error="err('date_evaluation')" for-id="date_evaluation">
          <input id="date_evaluation" v-model="form.date_evaluation" type="date" class="input" :class="{ invalid: err('date_evaluation') }" />
        </FormField>
        <FormField class="full" label="Commentaire" :error="err('commentaire')" for-id="commentaire">
          <textarea id="commentaire" v-model="form.commentaire" class="textarea" />
        </FormField>
      </div>
    </form>
    <template #footer>
      <button class="btn btn-secondary" type="button" @click="showForm = false">Annuler</button>
      <button class="btn btn-primary" type="submit" form="eval-form" :disabled="saving">{{ saving ? 'Enregistrement…' : 'Enregistrer' }}</button>
    </template>
  </BaseModal>

  <ConfirmDialog :open="!!toDelete" :busy="deleting" message="Voulez-vous vraiment supprimer cette évaluation ?" @cancel="toDelete = null" @confirm="confirmDelete" />
</template>
