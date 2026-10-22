<script setup>
const api = useApi()
const toast = useToast()
const route = useRoute()

const stagiaires = ref([])
const services = ref([])
const loading = ref(true)
const loadError = ref('')

const filtres = reactive({
  recherche: String(route.query.recherche ?? ''),
  statut: '',
  service_id: '',
})

const load = async () => {
  loading.value = true
  loadError.value = ''
  try {
    const query = {}
    if (filtres.recherche.trim()) query.recherche = filtres.recherche.trim()
    if (filtres.statut) query.statut = filtres.statut
    if (filtres.service_id) query.service_id = filtres.service_id
    stagiaires.value = await api.get('/stagiaires', query)
  } catch (e) {
    loadError.value = parseApiError(e).message
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  try { services.value = await api.get('/services') } catch { /* le filtre service sera simplement vide */ }
  await load()
})

// Recherche avec temporisation pour ne pas appeler l'API à chaque frappe
let timer
watch(() => filtres.recherche, () => { clearTimeout(timer); timer = setTimeout(load, 350) })
watch(() => [filtres.statut, filtres.service_id], load)
// La recherche de la barre du haut met à jour l'URL
watch(() => route.query.recherche, (v) => { filtres.recherche = String(v ?? '') })

const resetFiltres = () => { filtres.recherche = ''; filtres.statut = ''; filtres.service_id = '' }
const hasFiltres = computed(() => filtres.recherche || filtres.statut || filtres.service_id)

// ---------- Formulaire ----------
const showForm = ref(false)
const editingId = ref(null)
const saving = ref(false)
const errors = ref({})
const formMessage = ref('')
const blank = () => ({ nom: '', prenom: '', email: '', telephone: '', date_debut: todayInput(), date_fin: '', statut: 'en_attente', password: '' })
const form = reactive(blank())

const openCreate = () => { Object.assign(form, blank()); editingId.value = null; errors.value = {}; formMessage.value = ''; showForm.value = true }
const openEdit = (s) => {
  Object.assign(form, {
    nom: s.nom, prenom: s.prenom, email: s.email, telephone: s.telephone ?? '',
    date_debut: toInputDate(s.date_debut), date_fin: toInputDate(s.date_fin), statut: s.statut, password: '',
  })
  editingId.value = s.id; errors.value = {}; formMessage.value = ''; showForm.value = true
}

const clientValidate = () => {
  const e = {}
  if (!form.nom.trim()) e.nom = ['Le nom est obligatoire.']
  if (!form.prenom.trim()) e.prenom = ['Le prénom est obligatoire.']
  if (!/^\S+@\S+\.\S+$/.test(form.email)) e.email = ["Une adresse email valide est obligatoire."]
  if (!form.date_debut) e.date_debut = ['La date de début est obligatoire.']
  if (!form.date_fin) e.date_fin = ['La date de fin est obligatoire.']
  else if (form.date_debut && form.date_fin < form.date_debut) e.date_fin = ['La date de fin doit être postérieure ou égale à la date de début.']
  errors.value = e
  return Object.keys(e).length === 0
}

const submit = async () => {
  formMessage.value = ''
  if (!clientValidate()) return
  saving.value = true
  try {
    const payload = { ...form, telephone: form.telephone || null }
    if (!payload.password) delete payload.password // laisser vide = compte inchangé
    if (editingId.value) await api.put(`/stagiaires/${editingId.value}`, payload)
    else await api.post('/stagiaires', payload)
    toast.success(editingId.value ? 'Stagiaire modifié avec succès.' : 'Stagiaire créé avec succès.')
    showForm.value = false
    await load()
  } catch (e) {
    const err = parseApiError(e)
    errors.value = err.errors
    formMessage.value = err.message
  } finally {
    saving.value = false
  }
}

// ---------- Suppression ----------
const toDelete = ref(null)
const deleting = ref(false)
const confirmDelete = async () => {
  deleting.value = true
  try {
    const res = await api.del(`/stagiaires/${toDelete.value.id}`)
    toast.success(res?.message ?? 'Stagiaire supprimé.')
    toDelete.value = null
    await load()
  } catch (e) {
    toast.error(parseApiError(e).message)
  } finally {
    deleting.value = false
  }
}
</script>

<template>
  <PageHeader title="Stagiaires" subtitle="Consultez, recherchez et gérez les stagiaires de l'entreprise">
    <button class="btn btn-primary" @click="openCreate"><AppIcon name="plus" :size="18" /> Nouveau stagiaire</button>
  </PageHeader>

  <section class="card">
    <div class="toolbar">
      <div class="grow">
        <input v-model="filtres.recherche" class="input" type="search" placeholder="Rechercher par nom, prénom ou email…" aria-label="Rechercher" />
      </div>
      <select v-model="filtres.statut" class="select" aria-label="Filtrer par statut">
        <option value="">Tous les statuts</option>
        <option v-for="s in STATUTS" :key="s.value" :value="s.value">{{ s.label }}</option>
      </select>
      <select v-model="filtres.service_id" class="select" aria-label="Filtrer par service">
        <option value="">Tous les services</option>
        <option v-for="s in services" :key="s.id" :value="s.id">{{ s.nom }}</option>
      </select>
      <button v-if="hasFiltres" class="btn btn-ghost" @click="resetFiltres">Réinitialiser</button>
    </div>

    <StateBox :loading="loading" :error="loadError" :empty="!stagiaires.length"
              :empty-title="hasFiltres ? 'Aucun stagiaire ne correspond' : 'Aucun stagiaire pour le moment'"
              :empty-text="hasFiltres ? 'Modifiez ou réinitialisez vos filtres.' : 'Ajoutez votre premier stagiaire pour commencer.'"
              @retry="load" />

    <div v-if="!loading && !loadError && stagiaires.length" class="table-wrap">
      <table class="data">
        <thead>
          <tr><th>ID</th><th>Nom complet</th><th>Email</th><th>Téléphone</th><th>Statut</th><th>Début</th><th>Fin</th><th /></tr>
        </thead>
        <tbody>
          <tr v-for="s in stagiaires" :key="s.id">
            <td class="mono">{{ matricule(s.id) }}</td>
            <td><NuxtLink :to="`/stagiaires/${s.id}`"><strong>{{ fullName(s) }}</strong></NuxtLink></td>
            <td>{{ s.email }}</td>
            <td>{{ s.telephone || '—' }}</td>
            <td><StatusBadge :statut="s.statut" /></td>
            <td>{{ formatDate(s.date_debut) }}</td>
            <td>{{ formatDate(s.date_fin) }}</td>
            <td>
              <div class="cell-actions">
                <NuxtLink :to="`/stagiaires/${s.id}`" class="btn-icon" title="Consulter" aria-label="Consulter"><AppIcon name="eye" :size="18" /></NuxtLink>
                <button class="btn-icon" title="Modifier" aria-label="Modifier" @click="openEdit(s)"><AppIcon name="edit" :size="18" /></button>
                <button class="btn-icon danger" title="Supprimer" aria-label="Supprimer" @click="toDelete = s"><AppIcon name="trash" :size="18" /></button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>

  <BaseModal :open="showForm" :title="editingId ? 'Modifier le stagiaire' : 'Nouveau stagiaire'" @close="showForm = false">
    <form id="stagiaire-form" novalidate @submit.prevent="submit">
      <div v-if="formMessage" class="alert alert-error" style="margin-bottom:16px" role="alert">{{ formMessage }}</div>
      <div class="form-grid">
        <FormField label="Prénom" required :error="errors.prenom?.[0]" for-id="prenom">
          <input id="prenom" v-model="form.prenom" class="input" :class="{ invalid: errors.prenom }" />
        </FormField>
        <FormField label="Nom" required :error="errors.nom?.[0]" for-id="nom">
          <input id="nom" v-model="form.nom" class="input" :class="{ invalid: errors.nom }" />
        </FormField>
        <FormField class="full" label="Email" required :error="errors.email?.[0]" for-id="email">
          <input id="email" v-model="form.email" class="input" :class="{ invalid: errors.email }" type="email" />
        </FormField>
        <FormField label="Téléphone" :error="errors.telephone?.[0]" for-id="telephone">
          <input id="telephone" v-model="form.telephone" class="input" :class="{ invalid: errors.telephone }" type="tel" />
        </FormField>
        <FormField label="Statut" required :error="errors.statut?.[0]" for-id="statut">
          <select id="statut" v-model="form.statut" class="select" :class="{ invalid: errors.statut }">
            <option v-for="s in STATUTS" :key="s.value" :value="s.value">{{ s.label }}</option>
          </select>
        </FormField>
        <FormField label="Date de début" required :error="errors.date_debut?.[0]" for-id="date_debut">
          <input id="date_debut" v-model="form.date_debut" class="input" :class="{ invalid: errors.date_debut }" type="date" />
        </FormField>
        <FormField label="Date de fin" required :error="errors.date_fin?.[0]" for-id="date_fin">
          <input id="date_fin" v-model="form.date_fin" class="input" :class="{ invalid: errors.date_fin }" type="date" :min="form.date_debut" />
        </FormField>
        <FormField class="full" label="Mot de passe du compte de connexion" :error="errors.password?.[0]" for-id="password"
                   :hint="editingId ? 'Laisser vide pour ne pas modifier le compte (8 caractères minimum).' : 'Facultatif : permet au stagiaire de se connecter (8 caractères minimum).'">
          <input id="password" v-model="form.password" class="input" :class="{ invalid: errors.password }" type="password" autocomplete="new-password" />
        </FormField>
      </div>
    </form>
    <template #footer>
      <button class="btn btn-secondary" type="button" @click="showForm = false">Annuler</button>
      <button class="btn btn-primary" type="submit" form="stagiaire-form" :disabled="saving">{{ saving ? 'Enregistrement…' : 'Enregistrer' }}</button>
    </template>
  </BaseModal>

  <ConfirmDialog :open="!!toDelete" :busy="deleting"
                 :message="`Voulez-vous vraiment supprimer ${fullName(toDelete)} ? Cette action est irréversible.`"
                 @cancel="toDelete = null" @confirm="confirmDelete" />
</template>
