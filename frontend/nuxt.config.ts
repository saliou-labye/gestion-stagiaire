// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2024-11-01',
  // Application monopage : le frontend consomme uniquement l'API Laravel
  ssr: false,
  devtools: { enabled: false },
  css: ['~/assets/css/main.css'],
  runtimeConfig: {
    public: {
      // Surchargé par NUXT_PUBLIC_API_BASE dans le fichier .env
      apiBase: 'http://localhost:8000/api',
    },
  },
  app: {
    head: {
      htmlAttrs: { lang: 'fr' },
      title: 'Interncentral — Gestion des stagiaires',
      meta: [
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { name: 'theme-color', content: '#091F5C' },
      ],
      link: [
        { rel: 'icon', type: 'image/svg+xml', href: '/favicon.svg' },
        { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
        { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' },
        { rel: 'stylesheet', href: 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap' },
      ],
    },
  },
})
