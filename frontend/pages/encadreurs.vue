<script setup>
const crud = useCrud('encadreurs', { label: 'Encadreur' })
const { items, loading, loadError, saving, formErrors, formMessage } = crud

onMounted(() => crud.load())

const showForm = ref(false)
const editingId = ref(null)
const clientErrors = ref({})
const blank = () => ({ nom: '', prenom: '', email: '', specialite: '', password: '' })
const form = reactive(blank())
const err = (f) => clientErrors.value[f] ?? formErrors.value[f]?.[0]

const openCreate = () => { Object.assign(form, blank()); editingId.value = null; clientErrors.value = {}; crud.resetErrors(); showForm.value = true }
const openEdit = (x) => {
  Object.assign(form, { nom: x.nom, prenom: x.prenom, email: x.email, specialite: x.specialite, password: '' })
  editingId.value = x.id; clientErrors.value = {}; crud.resetErrors(); showForm.value = true
}

const submit = async () => {
  crud.resetErrors()
  const e = {}
  if (!form.prenom.trim()) e.prenom = 'Le prénom est obligatoire.'
  if (!form.nom.trim()) e.nom = 'Le nom est obligatoire.'
  if (!/^\S+@\S+\.\S+$/.test(form.email)) e.email = 'Une adresse email valide est obligatoire.'
  if (!form.specialite.trim()) e.specialite = 'La spécialité est obligatoire.'
  clientErrors.value = e
  if (Object.keys(e).length) return
  const payload = { ...form }
  if (!payload.password) delete payload.password // laisser vide = compte inchangé
  if (await crud.save(payload, editingId.value)) showForm.value = false
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
  <PageHeader title="Encadreurs" subtitle="Les référents qui supervisent les stagiaires">
    <button class="btn btn-primary" @click="openCreate"><AppIcon name="plus" :size="18" /> Nouvel encadreur</button>
  </PageHeader>

  <section class="card">
    <StateBox :loading="loading" :error="loadError" :empty="!items.length" empty-title="Aucun encadreur pour le moment"
              empty-text="Ajoutez un encadreur pour pouvoir créer des affectations." @retry="crud.load()" />
    <div v-if="!loading && !loadError && items.length" class="table-wrap">
      <table class="data">
        <thead><tr><th>Nom complet</th><th>Email</th><th>Spécialité</th><th /></tr></thead>
        <tbody>
          <tr v-for="x in items" :key="x.id">
            <td><strong>{{ fullName(x) }}</strong></td>
            <td>{{ x.email }}</td>
            <td>{{ x.specialite }}</td>
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

  <BaseModal :open="showForm" :title="editingId ? 'Modifier l\'encadreur' : 'Nouvel encadreur'" @close="showForm = false">
    <form id="enc-form" novalidate @submit.prevent="submit">
      <div v-if="formMessage" class="alert alert-error" style="margin-bottom:16px" role="alert">{{ formMessage }}</div>
      <div class="form-grid">
        <FormField label="Prénom" required :error="err('prenom')" for-id="prenom"><input id="prenom" v-model="form.prenom" class="input" :class="{ invalid: err('prenom') }" /></FormField>
        <FormField label="Nom" required :error="err('nom')" for-id="nom"><input id="nom" v-model="form.nom" class="input" :class="{ invalid: err('nom') }" /></FormField>
        <FormField class="full" label="Email" required :error="err('email')" for-id="email"><input id="email" v-model="form.email" type="email" class="input" :class="{ invalid: err('email') }" /></FormField>
        <FormField class="full" label="Spécialité" required :error="err('specialite')" for-id="specialite"><input id="specialite" v-model="form.specialite" class="input" :class="{ invalid: err('specialite') }" /></FormField>
        <FormField class="full" label="Mot de passe du compte de connexion" :error="err('password')" for-id="password"
                   :hint="editingId ? 'Laisser vide pour ne pas modifier le compte (8 caractères minimum).' : 'Facultatif : permet à l\'encadreur de se connecter (8 caractères minimum).'">
          <input id="password" v-model="form.password" class="input" :class="{ invalid: err('password') }" type="password" autocomplete="new-password" />
        </FormField>
      </div>
    </form>
    <template #footer>
      <button class="btn btn-secondary" type="button" @click="showForm = false">Annuler</button>
      <button class="btn btn-primary" type="submit" form="enc-form" :disabled="saving">{{ saving ? 'Enregistrement…' : 'Enregistrer' }}</button>
    </template>
  </BaseModal>

  <ConfirmDialog :open="!!toDelete" :busy="deleting" :message="`Voulez-vous vraiment supprimer ${fullName(toDelete)} ?`" @cancel="toDelete = null" @confirm="confirmDelete" />
</template>
