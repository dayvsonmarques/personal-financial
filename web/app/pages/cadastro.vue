<script setup lang="ts">
import type { FormError } from '@nuxt/ui'

definePageMeta({ layout: 'auth' })
useHead({ title: 'Criar conta' })

const auth = useAuthStore()

const state = reactive({ name: '', email: '', password: '', password_confirmation: '' })
const form = useTemplateRef('form')
const { error, run } = useFormSubmit(form)

function validate(s: typeof state): FormError[] {
  return firstPerField([
    ...requiredErrors(s, ['name', 'email', 'password', 'password_confirmation']),
    ...emailErrors(s.email),
    ...newPasswordErrors(s.password, s.password_confirmation),
  ])
}

async function onSubmit() {
  if (await run(() => auth.register({ ...state }))) {
    await navigateTo('/verificar-email')
  }
}
</script>

<template>
  <div class="space-y-6">
    <div class="space-y-1 text-center">
      <h1 class="text-xl font-semibold text-highlighted">
        Criar conta
      </h1>
      <p class="text-sm text-muted">
        Comece a organizar suas finanças.
      </p>
    </div>

    <UAlert
      v-if="error"
      color="error"
      variant="subtle"
      icon="i-lucide-circle-alert"
      :title="error"
    />

    <UForm
      ref="form"
      :state="state"
      :validate="validate"
      class="space-y-4"
      @submit="onSubmit"
    >
      <UFormField
        label="Nome"
        name="name"
      >
        <UInput
          v-model="state.name"
          autocomplete="name"
          class="w-full"
        />
      </UFormField>

      <UFormField
        label="E-mail"
        name="email"
      >
        <UInput
          v-model="state.email"
          type="email"
          autocomplete="email"
          class="w-full"
        />
      </UFormField>

      <UFormField
        label="Senha"
        name="password"
        :help="`Mínimo de ${PASSWORD_MIN} caracteres.`"
      >
        <UInput
          v-model="state.password"
          type="password"
          autocomplete="new-password"
          class="w-full"
        />
      </UFormField>

      <UFormField
        label="Confirmar senha"
        name="password_confirmation"
      >
        <UInput
          v-model="state.password_confirmation"
          type="password"
          autocomplete="new-password"
          class="w-full"
        />
      </UFormField>

      <UButton
        type="submit"
        label="Criar conta"
        block
      />
    </UForm>

    <p class="text-center text-sm text-muted">
      Já tem conta?
      <ULink
        to="/entrar"
        class="font-medium text-primary"
      >
        Entrar
      </ULink>
    </p>
  </div>
</template>
