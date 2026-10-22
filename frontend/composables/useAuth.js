export const ROLE_LABELS = { admin: 'Administrateur', encadreur: 'Encadreur', stagiaire: 'Stagiaire' }

export const useAuth = () => {
  const api = useApi()
  const token = useAuthToken()
  const user = useState('auth_user', () => null)

  const role = computed(() => user.value?.role ?? 'admin')
  const isAdmin = computed(() => role.value === 'admin')
  const roleLabel = computed(() => ROLE_LABELS[role.value] ?? role.value)

  const login = async (email, password) => {
    const res = await api.post('/login', { email, password })
    token.value = res.token
    user.value = res.user
  }

  const fetchUser = async () => {
    user.value = await api.get('/user')
  }

  const logout = async () => {
    try { await api.post('/logout') } catch { /* le jeton est peut-être déjà invalide */ }
    token.value = null
    user.value = null
    await navigateTo('/login')
  }

  return { user, token, role, isAdmin, roleLabel, login, fetchUser, logout }
}
