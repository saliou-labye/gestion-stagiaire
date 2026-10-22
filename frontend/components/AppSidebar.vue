<script setup>
// Menu latéral : sections « Général » / « Outils », pastilles, mode réduit (icônes seules).
defineProps({ open: Boolean })
const emit = defineEmits(['navigate'])

const route = useRoute()
const api = useApi()
const { user, role, roleLabel, isAdmin, logout } = useAuth()
const { collapsed, toggle, badges } = useSidebar()

const sections = computed(() => {
  if (isAdmin.value) {
    return [
      { title: 'Général', links: [
        { to: '/', label: 'Tableau de Bord', icon: 'home' },
        { to: '/stagiaires', label: 'Stagiaires', icon: 'users' },
        { to: '/encadreurs', label: 'Encadreurs', icon: 'mentor' },
        { to: '/services', label: 'Services', icon: 'briefcase' },
      ] },
      { title: 'Outils', links: [
        { to: '/affectations', label: 'Affectations', icon: 'link' },
        { to: '/evaluations', label: 'Évaluations', icon: 'list' },
        { to: '/rapports', label: 'Rapports', icon: 'chart' },
        { to: '/parametres', label: 'Paramètres', icon: 'settings' },
      ] },
    ]
  }
  return [
    { title: 'Général', links: [
      { to: '/mon-espace', label: role.value === 'encadreur' ? 'Mes stagiaires' : 'Mon espace', icon: role.value === 'encadreur' ? 'users' : 'user' },
    ] },
    { title: 'Outils', links: [{ to: '/parametres', label: 'Paramètres', icon: 'settings' }] },
  ]
})

const isActive = (to) => (to === '/' ? route.path === '/' : route.path.startsWith(to))
const initialsOf = computed(() => initials(user.value?.name))

// Pastille « stagiaires en attente » (administrateur uniquement)
const loadBadges = async () => {
  if (!isAdmin.value) return
  try {
    const d = await api.get('/dashboard')
    const attente = Math.max(d.total_stagiaires - d.stagiaires_en_cours - d.stagiaires_termines, 0)
    badges.value = { '/stagiaires': attente }
  } catch { /* la pastille est facultative */ }
}
onMounted(loadBadges)
watch(() => route.path, loadBadges)
</script>

<template>
  <aside class="sidebar" :class="{ open, collapsed }" aria-label="Navigation principale">
    <div class="head">
      <AppLogo light class="logo-full" />
      <AppLogo light compact class="logo-mini" />
      <button class="toggle" type="button" :title="collapsed ? 'Déployer le menu' : 'Réduire le menu'"
              :aria-label="collapsed ? 'Déployer le menu' : 'Réduire le menu'" @click="toggle">
        <AppIcon :name="collapsed ? 'expand' : 'collapse'" :size="20" />
      </button>
    </div>

    <div class="group">
      <div class="group-title label">Compte</div>
      <div class="account" :title="`${user?.name ?? ''} — ${roleLabel}`">
        <span class="avatar">{{ initialsOf }}</span>
        <span class="who label">
          <strong>{{ user?.name }}</strong>
          <small>{{ roleLabel }}</small>
        </span>
      </div>
    </div>

    <nav class="scroll">
      <div v-for="sec in sections" :key="sec.title" class="group">
        <div class="group-title label">{{ sec.title }}</div>
        <NuxtLink v-for="l in sec.links" :key="l.to" :to="l.to" class="item" :class="{ active: isActive(l.to) }"
                  :title="l.label" @click="emit('navigate')">
          <AppIcon :name="l.icon" :size="22" />
          <span class="label text">{{ l.label }}</span>
          <span v-if="badges[l.to]" class="badge label">{{ badges[l.to] }}</span>
          <span v-if="badges[l.to]" class="dot" aria-hidden="true" />
        </NuxtLink>
      </div>
    </nav>

    <div class="foot">
      <button class="item logout" type="button" title="Se déconnecter" @click="logout">
        <AppIcon name="logout" :size="22" />
        <span class="label text">Se déconnecter</span>
      </button>
    </div>
  </aside>
</template>

<style scoped>
.sidebar {
  position: fixed; inset: 0 auto 0 0; width: var(--sidebar-w); z-index: 30;
  background: var(--navy); color: #fff;
  display: flex; flex-direction: column; transition: width .2s ease;
}
.head { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 22px 18px 18px; }
.logo-mini { display: none; }
.toggle {
  width: 38px; height: 38px; flex: none; border-radius: 10px; border: 0; color: #fff; display: grid; place-items: center;
  background: rgba(255, 255, 255, .12);
}
.toggle:hover { background: rgba(255, 255, 255, .22); }

.group { padding: 14px 14px 10px; border-top: 1px solid rgba(255, 255, 255, .14); }
.group-title { color: #b9c9ee; font-size: 14px; font-weight: 600; padding: 0 6px 10px; letter-spacing: .01em; }

.account { display: flex; align-items: center; gap: 12px; padding: 10px; border-radius: 12px; background: rgba(255, 255, 255, .09); }
.avatar {
  width: 42px; height: 42px; flex: none; border-radius: 12px; display: grid; place-items: center;
  font-weight: 800; font-size: 16px; color: #fff; background: linear-gradient(135deg, #334daf, #7096d1);
}
.who { display: flex; flex-direction: column; min-width: 0; line-height: 1.25; }
.who strong { font-size: 16px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.who small { font-size: 14px; color: #c9d7f5; }

.scroll { flex: 1; overflow-y: auto; }
.item {
  position: relative; display: flex; align-items: center; gap: 14px; width: 100%;
  padding: 12px 10px; margin-bottom: 4px; border-radius: 10px; border: 0; background: none;
  color: #e8f2fe; font-size: 16px; font-weight: 500; text-align: left;
}
.item:hover { background: rgba(255, 255, 255, .1); text-decoration: none; }
.item.active { background: rgba(208, 228, 254, .16); color: #fff; font-weight: 700; }
.item.active::before { content: ''; position: absolute; left: -14px; top: 8px; bottom: 8px; width: 4px; border-radius: 0 4px 4px 0; background: #7096d1; }
.text { flex: 1; white-space: nowrap; }
.badge { min-width: 28px; padding: 3px 8px; border-radius: 8px; background: #7096d1; color: var(--navy); font-size: 14px; font-weight: 800; text-align: center; }
.dot { display: none; position: absolute; top: 9px; right: 12px; width: 9px; height: 9px; border-radius: 50%; background: #7096d1; box-shadow: 0 0 0 2px var(--navy); }

.foot { padding: 10px 14px 18px; border-top: 1px solid rgba(255, 255, 255, .14); }
.logout:hover { background: rgba(253, 224, 224, .16); }

/* ---- Mode réduit (bureau uniquement) ---- */
@media (min-width: 901px) {
  .sidebar.collapsed .label { display: none; }
  .sidebar.collapsed .head { flex-direction: column; padding: 18px 0 12px; gap: 14px; }
  .sidebar.collapsed .logo-full { display: none; }
  .sidebar.collapsed .logo-mini { display: inline-flex; }
  .sidebar.collapsed .group { padding-left: 10px; padding-right: 10px; }
  .sidebar.collapsed .item { justify-content: center; padding: 12px 0; }
  .sidebar.collapsed .item.active::before { left: -10px; }
  .sidebar.collapsed .account { justify-content: center; padding: 8px 0; background: none; }
  .sidebar.collapsed .dot { display: block; }
  .sidebar.collapsed .group-title { display: none; }
}

/* ---- Mobile : tiroir ---- */
@media (max-width: 900px) {
  .sidebar { width: 280px; transform: translateX(-100%); transition: transform .2s; box-shadow: 0 0 40px rgba(9, 31, 92, .35); }
  .sidebar.open { transform: translateX(0); }
  .toggle { display: none; }
}
</style>
