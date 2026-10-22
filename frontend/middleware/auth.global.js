// Authentification + contrôle d'accès par rôle.
// Admin : tout. Encadreur / stagiaire : uniquement leur espace et leurs paramètres.
const ALLOWED_FOR_OTHERS = ['/mon-espace', '/parametres']

export default defineNuxtRouteMiddleware(async (to) => {
  const token = useAuthToken()

  if (to.path === '/login') {
    if (token.value) return navigateTo('/')
    return
  }
  if (!token.value) return navigateTo('/login')

  const { user, fetchUser } = useAuth()
  if (!user.value) {
    try { await fetchUser() } catch { token.value = null; return navigateTo('/login') }
  }

  const role = user.value?.role ?? 'admin'
  if (role === 'admin') {
    if (to.path === '/mon-espace') return navigateTo('/')
  } else if (!ALLOWED_FOR_OTHERS.includes(to.path)) {
    return navigateTo('/mon-espace')
  }
})
