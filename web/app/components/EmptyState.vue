<script setup lang="ts">
withDefaults(defineProps<{
  title: string
  description?: string
  icon?: string
}>(), { icon: 'i-lucide-inbox', description: undefined })
</script>

<template>
  <div class="flex flex-col items-center justify-center gap-3 py-12 text-center">
    <UIcon
      :name="icon"
      class="size-10 text-muted"
    />
    <p class="font-medium text-highlighted">
      {{ title }}
    </p>
    <p
      v-if="description"
      class="max-w-sm text-sm text-muted"
    >
      {{ description }}
    </p>
    <slot />
  </div>
</template>
