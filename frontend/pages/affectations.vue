<script setup>
const api = useApi()
const crud = useCrud('affectations', { label: 'Affectation' })
const { items, loading, loadError, saving, formErrors, formMessage } = crud

const stagiaires = ref([])
const encadreurs = ref([])
const services = ref([])
const optionsError = ref('')

onMounted(async () => {
  crud.load()
  try {
    ;[stagiaires.value, encadreurs.value, services.value] = await Promise.all([
      api.get('/stagiaires'), api.get('/encadreurs'), api.get('/services'),
    ])
  } catch (e) {
    optionsError.value = parseApiError(e).message
  }
})

const showForm = ref(false)
const editingId = ref(null)
const clientErrors = ref({})
const blank = () => ({ stagiaire_id: '', encadreur_id: '', service_id: '', date_affectation: todayInput(), observations: '' })
const form = reactive(blank())
const err = (f) => clientErrors.value[f] ?? formErrors.value[f]?.[0]

const openCreate = () => { Object.assign(form, blank()); editingId.value = null; clientErrors.value = {}; crud.resetErrors(); showForm.value = true }
const openEdit = (x) => {
  Object.assign(form, {
    stagiaire_id: x.stagiaire_id, encadreur_id: x.encadreur_id, service_id: x.service_id,
    date_affectation: toInputDate(x.date_affectation), observations: x.observations ?? '',
  })
  editingId.value = x.id; clientErrors.value = {}; crud.resetErrors(); showForm.value = true
}

const submit = async () => {
  crud.resetErrors()
  const e = {}
  if (!form.stagiaire_id) e.stagiaire_id = 'Choisissez un stagiaire.'
  if (!form.encadreur_id) e.encadreur_id = 'Choisissez un encadreur.'
  if (!form.service_id) e.service_id = 'Choisissez un service.'
  if (!form.date_affectation) e.date_affectation = "La date d'affectation est obligatoire."
  clientErrors.value = e
  if (Object.keys(e).length) return
  if (await crud.save({ ...form, observations: form.observations || null }, editingId.value)) showForm.value = false
}

const toDelete = ref(null)
const deleting = ref(false)
const confirmDelete = async () => {
  deleting.value = true
  if (await crud.remove(toDelete.value.id)) toDelete.value = null
  deleting.value = false
}
</script>

<template>
  <PageHeader title="Affectations" subtitle="Affectez les stagiaires à un encadreur et à un service">
    <button class="btn btn-primary" @click="openCreate"><AppIcon name="plus" :size="18" /> Nouvelle affectation</button>
  </PageHeader>

  <section class="card">
    <StateBox :loading="loading" :error="loadError" :empty="!items.length" empty-title="Aucune affectation pour le moment"
              empty-text="Créez une affectation pour lier un stagiaire à un encadreur et à un service." @retry="crud.load()" />
    <div v-if="!loading && !loadError && items.length" class="table-wrap">
      <table class="data">
        <thead><tr><th>Stagiaire</th><th>Service</th><th>Encadreur</th><th>Statut</th><th>Date d'affectation</th><th>Observations</th><th /></tr></thead>
        <tbody>
          <tr v-for="x in items" :key="x.id">
            <td><NuxtLink :to="`/stagiaires/${x.stagiaire_id}`"><strong>{{ fullName(x.stagiaire) }}</strong></NuxtLink></td>
            <td>{{ x.service?.nom ?? '—' }}</td>
            <td>{{ fullName(x.encadreur) }}</td>
            <td><StatusBadge v-if="x.stagiaire" :statut="x.stagiaire.statut" /></td>
            <td>{{ formatDate(x.date_affectation) }}</td>
            <td class="muted">{{ x.observations || '—' }}</td>
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

  <BaseModal :open="showForm" :title="editingId ? 'Modifier l\'affectation' : 'Nouvelle affectation'" @close="showForm = false">
    <form id="aff-form" novalidate @submit.prevent="submit">
      <div v-if="optionsError" class="alert alert-error" style="margin-bottom:16px">{{ optionsError }}</div>
      <div v-if="formMessage" class="alert alert-error" style="margin-bottom:16px" role="alert">{{ formMessage }}</div>
      <div class="form-grid">
        <FormField class="full" label="Stagiaire" required :error="err('stagiaire_id')" for-id="stagiaire_id">
          <select id="stagiaire_id" v-model="form.stagiaire_id" class="select" :class="{ invalid: err('stagiaire_id') }">
            <option value="" disabled>Sélectionner…</option>
            <option v-for="s in stagiaires" :key="s.id" :value="s.id">{{ fullName(s) }}</option>
          </select>
        </FormField>
        <FormField label="Encadreur" required :error="err('encadreur_id')" for-id="encadreur_id">
          <select id="encadreur_id" v-model="form.encadreur_id" class="select" :class="{ invalid: err('encadreur_id') }">
            <option value="" disabled>Sélectionner…</option>
            <option v-for="x in encadreurs" :key="x.id" :value="x.id">{{ fullName(x) }}</option>
          </select>
        </FormField>
        <FormField label="Service" required :error="err('service_id')" for-id="service_id">
          <select id="service_id" v-model="form.service_id" class="select" :class="{ invalid: err('service_id') }">
            <option value="" disabled>Sélectionner…</option>
            <option v-for="x in services" :key="x.id" :value="x.id">{{ x.nom }}</option>
          </select>
        </FormField>
        <FormField class="full" label="Date d'affectation" required :error="err('date_affectation')" for-id="date_affectation">
          <input id="date_affectation" v-model="form.date_affectation" type="date" class="input" :class="{ invalid: err('date_affectation') }" />
        </FormField>
        <FormField class="full" label="Observations" :error="err('observations')" for-id="observations">
          <textarea id="observations" v-model="form.observations" class="textarea" />
        </FormField>
      </div>
    </form>
    <template #footer>
      <button class="btn btn-secondary" type="button" @click="showForm = false">Annuler</button>
      <button class="btn btn-primary" type="submit" form="aff-form" :disabled="saving">{{ saving ? 'Enregistrement…' : 'Enregistrer' }}</button>
    </template>
  </BaseModal>

  <ConfirmDialog :open="!!toDelete" :busy="deleting" message="Voulez-vous vraiment supprimer cette affectation ?" @cancel="toDelete = null" @confirm="confirmDelete" />
</template>
