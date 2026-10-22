// Logique CRUD générique réutilisée par les pages encadreurs, services, affectations, évaluations.
export const useCrud = (resource, { label = 'élément' } = {}) => {
  const api = useApi()
  const toast = useToast()

  const items = ref([])
  const loading = ref(true)
  const loadError = ref('')
  const saving = ref(false)
  const formErrors = ref({})
  const formMessage = ref('')

  const load = async (query) => {
    loading.value = true
    loadError.value = ''
    try {
      const res = await api.get(`/${resource}`, query)
      items.value = Array.isArray(res) ? res : (res.data ?? [])
    } catch (e) {
      loadError.value = parseApiError(e).message
    } finally {
      loading.value = false
    }
  }

  const resetErrors = () => { formErrors.value = {}; formMessage.value = '' }

  // Retourne true si l'enregistrement a réussi
  const save = async (payload, id = null) => {
    saving.value = true
    resetErrors()
    try {
      const res = id ? await api.put(`/${resource}/${id}`, payload) : await api.post(`/${resource}`, payload)
      toast.success(res?.message ?? (id ? 'Modifications enregistrées.' : 'Créé avec succès.'))
      await load()
      return true
    } catch (e) {
      const err = parseApiError(e)
      formErrors.value = err.errors
      formMessage.value = err.message
      return false
    } finally {
      saving.value = false
    }
  }

  const remove = async (id) => {
    try {
      const res = await api.del(`/${resource}/${id}`)
      toast.success(res?.message ?? `${label} supprimé.`)
      await load()
      return true
    } catch (e) {
      toast.error(parseApiError(e).message)
      return false
    }
  }

  return { items, loading, loadError, saving, formErrors, formMessage, load, save, remove, resetErrors }
}
