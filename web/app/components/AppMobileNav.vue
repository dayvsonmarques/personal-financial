<script setup lang="ts">
const { mobile } = useNavigation()
const route = useRoute()

function isActive(to: string) {
  return to === '/' ? route.path === '/' : route.path.startsWith(to)
}
</script>

<template>
  <nav
    aria-label="Navegação principal"
    class="fixed inset-x-0 bottom-0 z-40 border-t border-default bg-default/95 backdrop-blur lg:hidden"
  >
    <ul class="flex justify-around pb-[env(safe-area-inset-bottom)]">
      <li
        v-for="item in mobile"
        :key="item.to"
        class="flex-1"
      >
        <NuxtLink
          :to="item.to"
          class="flex flex-col items-center gap-1 py-2 text-xs"
          :class="isActive(item.to) ? 'text-primary' : 'text-muted'"
          :aria-current="isActive(item.to) ? 'page' : undefined"
        >
          <UIcon
            :name="item.icon"
            class="size-5"
          />
          {{ item.label }}
        </NuxtLink>
      </li>
    </ul>
  </nav>
</template>
