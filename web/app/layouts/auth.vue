<template>
  <main class="flex min-h-dvh items-center justify-center bg-muted px-4 py-10">
    <div class="w-full max-w-sm space-y-6">
      <div class="flex justify-center">
        <AppLogo />
      </div>
      <UCard>
        <slot />
      </UCard>
    </div>
  </main>
</template>
