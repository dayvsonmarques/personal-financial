<script setup lang="ts">
import type { DropdownMenuItem } from '@nuxt/ui'

defineProps<{ collapsed?: boolean }>()

const auth = useAuthStore()

async function logout() {
  await auth.logout()
  await navigateTo('/entrar')
}

const items = computed<DropdownMenuItem[][]>(() => [
  [{ label: auth.user?.email ?? '', type: 'label' }],
  [
    { label: 'Perfil', icon: 'i-lucide-user', to: '/configuracoes/perfil' },
    { label: 'Organização', icon: 'i-lucide-building-2', to: '/configuracoes/organizacao' },
  ],
  [{ label: 'Sair', icon: 'i-lucide-log-out', onSelect: logout }],
])
</script>

<template>
  <UDropdownMenu
    :items="items"
    :content="{ align: 'center', collisionPadding: 12 }"
    :ui="{ content: collapsed ? 'w-48' : 'w-(--reka-dropdown-menu-trigger-width)' }"
  >
    <UButton
      :label="collapsed ? undefined : auth.user?.name"
      :avatar="{ alt: auth.user?.name }"
      color="neutral"
      variant="ghost"
      block
      :square="collapsed"
      class="data-[state=open]:bg-elevated"
      :trailing-icon="collapsed ? undefined : 'i-lucide-chevrons-up-down'"
      aria-label="Menu do usuário"
    />
  </UDropdownMenu>
</template>
