// @ts-check
import withNuxt from './.nuxt/eslint.config.mjs'

export default withNuxt({
  rules: {
    // seção 15: proibido v-html com dados do usuário
    'vue/no-v-html': 'error',
  },
})
