<script setup lang="ts">
withDefaults(defineProps<{
  message?: string
}>(), { message: 'Não foi possível carregar os dados.' })

defineEmits<{ retry: [] }>()
</script>

<template>
  <div
    class="flex flex-col items-center justify-center gap-3 py-12 text-center"
    role="alert"
  >
    <UIcon
      name="i-lucide-triangle-alert"
      class="size-10 text-error"
    />
    <p class="text-sm text-muted">
      {{ message }}
    </p>
    <UButton
      label="Tentar novamente"
      color="neutral"
      variant="outline"
      icon="i-lucide-refresh-cw"
      @click="$emit('retry')"
    />
  </div>
</template>
