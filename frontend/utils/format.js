// Utilitaires de formatage partagés par toutes les pages.

// Laravel sérialise les dates en ISO (2026-07-01T00:00:00.000000Z) : on garde AAAA-MM-JJ.
export const toInputDate = (value) => (value ? String(value).slice(0, 10) : '')

export const formatDate = (value) => {
  const d = toInputDate(value)
  if (!d) return '—'
  const [y, m, j] = d.split('-')
  return `${j}/${m}/${y}`
}

export const todayInput = () => new Date().toISOString().slice(0, 10)

export const fullName = (p) => (p ? `${p.prenom ?? ''} ${p.nom ?? ''}`.trim() : '—')

export const matricule = (id) => `STG-${String(id).padStart(4, '0')}`

export const initials = (name = '') =>
  name.split(/\s+/).filter(Boolean).slice(0, 2).map((s) => s[0].toUpperCase()).join('') || '?'

export const note = (v) => {
  const n = Number(v)
  return Number.isFinite(n) ? n.toFixed(1).replace('.0', '') : '—'
}

export const STATUTS = [
  { value: 'en_attente', label: 'En attente' },
  { value: 'en_cours', label: 'En cours' },
  { value: 'termine', label: 'Terminé' },
]

export const statutLabel = (v) => STATUTS.find((s) => s.value === v)?.label ?? v
