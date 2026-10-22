// État du menu latéral : réduit (icônes seules) ou déployé, mémorisé dans un cookie.
export const useSidebar = () => {
  const collapsed = useCookie('sidebar_collapsed', { maxAge: 60 * 60 * 24 * 365, default: () => false })
  const toggle = () => { collapsed.value = !collapsed.value }
  // Pastilles de notification par route (ex. { '/stagiaires': 3 })
  const badges = useState('sidebar_badges', () => ({}))
  return { collapsed, toggle, badges }
}
