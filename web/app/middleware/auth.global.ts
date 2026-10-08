const GUEST_ONLY = ['/entrar', '/cadastro', '/esqueci-senha']
const PUBLIC = [...GUEST_ONLY, '/redefinir-senha']

export default defineNuxtRouteMiddleware(async (to) => {
  const auth = useAuthStore()
  if (!auth.loaded) await auth.fetchUser()

  if (!auth.user) {
    if (PUBLIC.includes(to.path)) return
    return navigateTo({ path: '/entrar', query: to.fullPath === '/' ? {} : { redirect: to.fullPath } })
  }

  if (GUEST_ONLY.includes(to.path)) return navigateTo('/')

  // RF-02: sem e-mail verificado, só a tela de verificação.
  if (!auth.isVerified && to.path !== '/verificar-email') return navigateTo('/verificar-email')
  if (auth.isVerified && to.path === '/verificar-email') return navigateTo('/')
})
