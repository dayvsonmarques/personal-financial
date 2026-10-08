import type { NavigationMenuItem } from '@nuxt/ui'

interface NavItem {
  label: string
  icon: string
  to: string
  /** Telas ainda não entregues ficam ocultas até o marco correspondente. */
  available: boolean
  /** Aparece na barra inferior do mobile. */
  mobile?: boolean
}

const ITEMS: NavItem[] = [
  { label: 'Início', icon: 'i-lucide-layout-dashboard', to: '/', available: true, mobile: true },
  { label: 'Lançamentos', icon: 'i-lucide-arrow-left-right', to: '/lancamentos', available: false, mobile: true },
  { label: 'Recorrências', icon: 'i-lucide-repeat', to: '/recorrencias', available: false },
  { label: 'Contas', icon: 'i-lucide-landmark', to: '/contas', available: false, mobile: true },
  { label: 'Cartões', icon: 'i-lucide-credit-card', to: '/cartoes', available: false, mobile: true },
  { label: 'Relatórios', icon: 'i-lucide-chart-column', to: '/relatorios', available: false },
  { label: 'Configurações', icon: 'i-lucide-settings', to: '/configuracoes', available: true, mobile: true },
]

export function useNavigation() {
  const items = ITEMS.filter(item => item.available)

  const sidebar = computed<NavigationMenuItem[]>(() =>
    items.map(({ label, icon, to }) => ({ label, icon, to, exact: to === '/' })))

  const mobile = computed(() => items.filter(item => item.mobile))

  return { sidebar, mobile }
}
