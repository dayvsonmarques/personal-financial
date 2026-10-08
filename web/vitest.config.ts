import { defineVitestConfig } from '@nuxt/test-utils/config'

export default defineVitestConfig({
  test: {
    environment: 'nuxt',
    include: ['tests/unit/**/*.spec.ts'],
    // Subir o ambiente Nuxt leva de 5 a 10 s; o padrão de 10 s estoura sob carga (CI, dev server rodando).
    hookTimeout: 30_000,
  },
})
