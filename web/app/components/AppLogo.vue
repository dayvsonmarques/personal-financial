<script setup lang="ts">
defineProps<{ collapsed?: boolean }>()
</script>

<template>
  <NuxtLink
    to="/"
    class="flex items-center gap-2 font-semibold text-highlighted"
  >
    <UIcon
      name="i-lucide-wallet"
      class="size-6 text-primary"
    />
    <span v-if="!collapsed">Financeiro Pro</span>
  </NuxtLink>
</template>
