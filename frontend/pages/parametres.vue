<script setup>
const { user, roleLabel, logout } = useAuth()
const config = useRuntimeConfig()
</script>

<template>
  <PageHeader title="Paramètres" subtitle="Compte connecté et configuration de l'application" />
  <div class="two-col">
    <section class="card card-pad">
      <h2 class="h">Mon compte</h2>
      <dl>
        <dt>Nom</dt><dd>{{ user?.name }}</dd>
        <dt>Email</dt><dd>{{ user?.email }}</dd>
        <dt>Rôle</dt><dd>{{ roleLabel }}</dd>
      </dl>
      <button class="btn btn-secondary" @click="logout"><AppIcon name="logout" :size="18" /> Se déconnecter</button>
    </section>
    <section class="card card-pad">
      <h2 class="h">Connexion à l'API</h2>
      <dl>
        <dt>URL de l'API Laravel</dt><dd class="mono">{{ config.public.apiBase }}</dd>
      </dl>
      <p class="muted" style="margin:0">Modifiable via la variable <code>NUXT_PUBLIC_API_BASE</code> du fichier <code>.env</code> du frontend.</p>
    </section>
  </div>
</template>

<style scoped>
.h { font-size: 18px; margin-bottom: 14px; }
dl { margin: 0 0 18px; }
dt { font-size: 13px; color: var(--text-soft); font-weight: 600; margin-top: 10px; }
dd { margin: 2px 0 0; font-weight: 600; word-break: break-all; }
</style>
