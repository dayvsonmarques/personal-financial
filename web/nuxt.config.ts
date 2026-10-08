// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({

  modules: ['@nuxt/ui', '@pinia/nuxt', '@nuxt/eslint'],

  // D5: app atrás de login, sem SEO; SPA simplifica a sessão via cookie do Sanctum.
  ssr: false,

  devtools: { enabled: true },

  app: {
    head: {
      htmlAttrs: { lang: 'pt-BR' },
      title: 'Financeiro Pro',
    },
  },

  css: ['~/assets/css/main.css'],

  ui: {
    colorMode: true,
  },
  compatibilityDate: '2025-07-15',

  vite: {
    server: {
      // Em dev o navegador acessa via Nginx na porta 8080 (D21).
      ws: { clientPort: 8080 },
    },
  },

  eslint: {
    config: {
      stylistic: true,
    },
  },
})
