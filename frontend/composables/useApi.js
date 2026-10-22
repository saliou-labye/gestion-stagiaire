// Client HTTP vers l'API Laravel (jeton Sanctum envoyé en Bearer).
export const useAuthToken = () =>
  useCookie('auth_token', { maxAge: 60 * 60 * 24 * 7, sameSite: 'lax', default: () => null })

export const useApi = () => {
  const config = useRuntimeConfig()
  const token = useAuthToken()

  const request = (path, options = {}) =>
    $fetch(path, {
      baseURL: config.public.apiBase,
      ...options,
      headers: {
        Accept: 'application/json',
        ...(token.value ? { Authorization: `Bearer ${token.value}` } : {}),
        ...options.headers,
      },
      onResponseError({ response }) {
        // Jeton expiré ou révoqué : retour à la page de connexion
        if (response.status === 401 && path !== '/login') {
          token.value = null
          navigateTo('/login')
        }
      },
    })

  return {
    get: (path, query) => request(path, { query }),
    post: (path, body) => request(path, { method: 'POST', body }),
    put: (path, body) => request(path, { method: 'PUT', body }),
    del: (path) => request(path, { method: 'DELETE' }),
  }
}

// Transforme une erreur $fetch en { message, errors } exploitable dans les formulaires.
export const parseApiError = (e) => {
  const status = e?.response?.status ?? e?.statusCode
  const data = e?.data ?? e?.response?._data
  if (!status) return { message: "Impossible de joindre le serveur. Vérifiez que l'API Laravel est démarrée.", errors: {} }
  if (status === 422) {
    return { message: 'Certains champs sont invalides.', errors: data?.errors ?? {} }
  }
  if (status === 404) return { message: 'Ressource introuvable.', errors: {} }
  if (status === 401) return { message: 'Session expirée. Veuillez vous reconnecter.', errors: {} }
  // 500 & co : on n'affiche jamais de trace technique
  return { message: 'Une erreur est survenue. Cette opération est peut-être impossible (données liées existantes).', errors: {} }
}
