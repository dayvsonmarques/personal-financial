<script setup lang="ts">
import type { FormError } from '@nuxt/ui'

definePageMeta({ layout: 'auth' })
useHead({ title: 'Esqueci a senha' })

const auth = useAuthStore()

const state = reactive({ email: '' })
const form = useTemplateRef('form')
const { error, run } = useFormSubmit(form, state)
const sent = ref<string | null>(null)

function validate(s: typeof state): FormError[] {
  return firstPerField([...requiredErrors(s, ['email']), ...emailErrors(s.email)])
}

async function onSubmit() {
  await run(async () => {
    sent.value = (await auth.forgotPassword(state.email)).message
  })
}
</script>

<template>
  <div class="space-y-6">
    <div class="space-y-1 text-center">
      <h1 class="text-xl font-semibold text-highlighted">
        Esqueci a senha
      </h1>
      <p class="text-sm text-muted">
        Informe seu e-mail e enviaremos um link para criar uma nova senha.
      </p>
    </div>

    <UAlert
      v-if="sent"
      color="success"
      variant="subtle"
      icon="i-lucide-mail"
      :title="sent"
    />

    <template v-else>
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

        <UButton
          type="submit"
          label="Enviar link"
          block
        />
      </UForm>
    </template>

    <p class="text-center text-sm">
      <ULink
        to="/entrar"
        class="font-medium text-primary"
      >
        Voltar para o login
      </ULink>
    </p>
  </div>
</template>
