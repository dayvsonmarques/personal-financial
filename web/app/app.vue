<script setup lang="ts">
const APP_NAME = 'Financeiro Pro'

useHead({ titleTemplate: title => (title && title !== APP_NAME ? `${title} · ${APP_NAME}` : APP_NAME) })
</script>

<template>
  <UApp>
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
  </UApp>
</template>
