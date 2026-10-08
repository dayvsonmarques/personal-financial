<script setup lang="ts">
import type { NavigationMenuItem } from '@nuxt/ui'

const items: NavigationMenuItem[] = [
  { label: 'Perfil', icon: 'i-lucide-user', to: '/configuracoes/perfil' },
  { label: 'Organização', icon: 'i-lucide-building-2', to: '/configuracoes/organizacao' },
]
</script>

<template>
  <UNavigationMenu
    :items="items"
    highlight
    class="-mx-1 mb-6 border-b border-default"
  />
</template>
