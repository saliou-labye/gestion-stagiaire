<script setup>
definePageMeta({ layout: 'auth' })

const { login } = useAuth()
const form = reactive({ email: '', password: '' })
const errors = reactive({ email: '', password: '' })
const globalError = ref('')
const info = ref('')
const loading = ref(false)

// Validation côté interface (la validation Laravel reste la référence)
const validate = () => {
  errors.email = !form.email ? "L'adresse email est obligatoire." : (/^\S+@\S+\.\S+$/.test(form.email) ? '' : "Format d'email invalide.")
  errors.password = form.password ? '' : 'Le mot de passe est obligatoire.'
  return !errors.email && !errors.password
}

const submit = async () => {
  globalError.value = ''
  info.value = ''
  if (!validate()) return
  loading.value = true
  try {
    await login(form.email, form.password)
    await navigateTo('/')
  } catch (e) {
    const err = parseApiError(e)
    if (err.errors.email) errors.email = err.errors.email[0]
    else globalError.value = err.message
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <form class="login card" novalidate @submit.prevent="submit">
    <div class="logo-row"><AppLogo /></div>
    <h1>Salut ! Veuillez vous connecter</h1>

    <div v-if="globalError" class="alert alert-error" role="alert">{{ globalError }}</div>
    <div v-if="info" class="alert alert-success">{{ info }}</div>

    <FormField label="Adresse Email" :error="errors.email" for-id="email">
      <input id="email" v-model="form.email" class="input" :class="{ invalid: errors.email }" type="email" autocomplete="username" autofocus />
    </FormField>
    <FormField label="Mot de passe" :error="errors.password" for-id="password">
      <input id="password" v-model="form.password" class="input" :class="{ invalid: errors.password }" type="password" autocomplete="current-password" />
    </FormField>

    <button class="btn btn-primary submit" type="submit" :disabled="loading">
      {{ loading ? 'CONNEXION…' : 'SE CONNECTER' }}
    </button>
    <button class="link" type="button" @click="info = 'Contactez votre administrateur pour réinitialiser votre mot de passe.'">Mot de passe oublié ?</button>
  </form>
</template>

<style scoped>
.login { width: 100%; max-width: 420px; padding: 32px; display: flex; flex-direction: column; gap: 16px;
  background: rgba(249, 251, 255, .88); backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px);
  border-color: rgba(255, 255, 255, .7); }
.logo-row { display: flex; justify-content: center; margin-bottom: 4px; }
h1 { font-size: 22px; font-weight: 600; text-align: center; margin-bottom: 4px; }
.submit { width: 100%; padding: 12px; letter-spacing: .04em; margin-top: 4px; }
.link { background: none; border: 0; color: var(--navy); text-decoration: underline; font-size: 14px; align-self: center; padding: 4px; }
.link:hover { color: var(--royal); }
</style>
