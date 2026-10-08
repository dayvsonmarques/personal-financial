<script setup lang="ts">
/** Moldura padrão das telas autenticadas. */
defineProps<{ title: string }>()
</script>

<template>
  <UDashboardPanel :id="title">
    <template #header>
      <UDashboardNavbar
        :title="title"
        :toggle="false"
      >
        <template #leading>
          <UDashboardSidebarCollapse class="hidden lg:inline-flex" />
        </template>
        <template #right>
          <slot name="actions" />
        </template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <div class="pb-20 lg:pb-0">
        <slot />
      </div>
    </template>
  </UDashboardPanel>
</template>
