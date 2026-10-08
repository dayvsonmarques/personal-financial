<script setup lang="ts">
const auth = useAuthStore()
const route = useRoute()
const toast = useToast()

onMounted(() => {
  if (route.query.verified === '1') {
    toast.add({ title: 'E-mail confirmado', color: 'success', icon: 'i-lucide-circle-check' })
  }
})
</script>

<template>
  <AppPage title="Início">
    <EmptyState
      icon="i-lucide-layout-dashboard"
      :title="`Olá, ${auth.user?.name}`"
      description="Seu painel com saldos, faturas e gráficos chega em breve."
    />
  </AppPage>
</template>
