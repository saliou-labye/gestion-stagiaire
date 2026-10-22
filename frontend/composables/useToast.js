export const useToasts = () => useState('toasts', () => [])

export const useToast = () => {
  const toasts = useToasts()
  const push = (type, message) => {
    const id = Date.now() + Math.random()
    toasts.value.push({ id, type, message })
    setTimeout(() => { toasts.value = toasts.value.filter((t) => t.id !== id) }, 4500)
  }
  return {
    success: (m) => push('success', m),
    error: (m) => push('error', m),
  }
}
