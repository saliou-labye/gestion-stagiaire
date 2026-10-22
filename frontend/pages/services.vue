<script setup>
const crud = useCrud('services', { label: 'Service' })
const { items, loading, loadError, saving, formErrors, formMessage } = crud

onMounted(() => crud.load())

const showForm = ref(false)
const editingId = ref(null)
const clientErrors = ref({})
const blank = () => ({ nom: '', description: '' })
const form = reactive(blank())
const err = (f) => clientErrors.value[f] ?? formErrors.value[f]?.[0]

const openCreate = () => { Object.assign(form, blank()); editingId.value = null; clientErrors.value = {}; crud.resetErrors(); showForm.value = true }
const openEdit = (x) => {
  Object.assign(form, { nom: x.nom, description: x.description ?? '' })
  editingId.value = x.id; clientErrors.value = {}; crud.resetErrors(); showForm.value = true
}

const submit = async () => {
  crud.resetErrors()
  const e = {}
  if (!form.nom.trim()) e.nom = 'Le nom du service est obligatoire.'
  clientErrors.value = e
  if (Object.keys(e).length) return
  if (await crud.save({ nom: form.nom, description: form.description || null }, editingId.value)) showForm.value = false
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
  <PageHeader title="Services" subtitle="Les services d'accueil des stagiaires">
    <button class="btn btn-primary" @click="openCreate"><AppIcon name="plus" :size="18" /> Nouveau service</button>
  </PageHeader>

  <section class="card">
    <StateBox :loading="loading" :error="loadError" :empty="!items.length" empty-title="Aucun service pour le moment"
              empty-text="Créez un service pour pouvoir y affecter des stagiaires." @retry="crud.load()" />
    <div v-if="!loading && !loadError && items.length" class="table-wrap">
      <table class="data">
        <thead><tr><th>Nom</th><th>Description</th><th>Affectations</th><th /></tr></thead>
        <tbody>
          <tr v-for="x in items" :key="x.id">
            <td><strong>{{ x.nom }}</strong></td>
            <td class="muted">{{ x.description || '—' }}</td>
            <td class="mono">{{ x.affectations_count ?? 0 }}</td>
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

  <BaseModal :open="showForm" :title="editingId ? 'Modifier le service' : 'Nouveau service'" @close="showForm = false">
    <form id="srv-form" novalidate @submit.prevent="submit">
      <div v-if="formMessage" class="alert alert-error" style="margin-bottom:16px" role="alert">{{ formMessage }}</div>
      <div class="form-grid">
        <FormField class="full" label="Nom" required :error="err('nom')" for-id="nom"><input id="nom" v-model="form.nom" class="input" :class="{ invalid: err('nom') }" /></FormField>
        <FormField class="full" label="Description" :error="err('description')" for-id="description"><textarea id="description" v-model="form.description" class="textarea" /></FormField>
      </div>
    </form>
    <template #footer>
      <button class="btn btn-secondary" type="button" @click="showForm = false">Annuler</button>
      <button class="btn btn-primary" type="submit" form="srv-form" :disabled="saving">{{ saving ? 'Enregistrement…' : 'Enregistrer' }}</button>
    </template>
  </BaseModal>

  <ConfirmDialog :open="!!toDelete" :busy="deleting" :message="`Voulez-vous vraiment supprimer le service « ${toDelete?.nom} » ?`" @cancel="toDelete = null" @confirm="confirmDelete" />
</template>
