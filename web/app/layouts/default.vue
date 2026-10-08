<script setup lang="ts">
const { sidebar } = useNavigation()
</script>

<template>
  <UDashboardGroup unit="rem">
    <UDashboardSidebar
      id="principal"
      collapsible
      class="hidden bg-elevated/25 lg:flex"
      :ui="{ footer: 'border-t border-default' }"
    >
      <template #header="{ collapsed }">
        <AppLogo :collapsed="collapsed" />
      </template>

      <template #default="{ collapsed }">
        <UNavigationMenu
          :collapsed="collapsed"
          :items="sidebar"
          orientation="vertical"
          tooltip
        />
      </template>

      <template #footer="{ collapsed }">
        <AppUserMenu :collapsed="collapsed" />
      </template>
    </UDashboardSidebar>

    <slot />

    <AppMobileNav />
  </UDashboardGroup>
</template>
