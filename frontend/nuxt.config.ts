export default defineNuxtConfig({
  devtools: { enabled: true },
  modules: ['@nuxt/eslint'],
  typescript: {
    strict: true,
    typeCheck: true
  },
  runtimeConfig: {
    public: {
      // Base URL for the Laravel API (should include the `/api` prefix).
      apiBaseUrl: process.env.NUXT_PUBLIC_API_BASE_URL || 'http://127.0.0.1:8000/api'
    }
  },
  app: {
    head: {
      title: 'KnowMyCouncil',
      meta: [
        { name: 'description', content: 'KnowMyCouncil: a public transparency platform for English councils.' }
      ]
    }
  },
  css: ['~/assets/base.css']
})
