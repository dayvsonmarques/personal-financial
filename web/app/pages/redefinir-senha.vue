<script setup lang="ts">
import type { FormError } from '@nuxt/ui'

definePageMeta({ layout: 'auth' })
useHead({ title: 'Nova senha' })

const auth = useAuthStore()
const route = useRoute()
const toast = useToast()

const token = typeof route.query.token === 'string' ? route.query.token : ''
const state = reactive({
  email: typeof route.query.email === 'string' ? route.query.email : '',
  password: '',
  password_confirmation: '',
})
const form = useTemplateRef('form')
const { error, run } = useFormSubmit(form)

function validate(s: typeof state): FormError[] {
  return firstPerField([
    ...requiredErrors(s, ['email', 'password', 'password_confirmation']),
    ...emailErrors(s.email),
    ...newPasswordErrors(s.password, s.password_confirmation),
  ])
}

async function onSubmit() {
  if (await run(() => auth.resetPassword({ token, ...state }))) {
    toast.add({ title: 'Senha redefinida', description: 'Entre com a nova senha.', color: 'success', icon: 'i-lucide-circle-check' })
    await navigateTo('/entrar')
  }
}
</script>

<template>
  <div class="space-y-6">
    <div class="space-y-1 text-center">
      <h1 class="text-xl font-semibold text-highlighted">
        Nova senha
      </h1>
      <p class="text-sm text-muted">
        Escolha uma nova senha para sua conta.
      </p>
    </div>

    <UAlert
      v-if="!token"
      color="error"
      variant="subtle"
      icon="i-lucide-circle-alert"
      title="Link inválido. Peça um novo link de redefinição."
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
        <!-- Visível porque o Laravel devolve o erro de token expirado/usado neste campo. -->
        <UFormField
          label="E-mail"
          name="email"
        >
          <UInput
            v-model="state.email"
            type="email"
            autocomplete="email"
            readonly
            class="w-full"
          />
        </UFormField>

        <UFormField
          label="Nova senha"
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
          label="Confirmar nova senha"
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
          label="Salvar nova senha"
          block
        />
      </UForm>
    </template>

    <p class="text-center text-sm">
      <ULink
        :to="token ? '/entrar' : '/esqueci-senha'"
        class="font-medium text-primary"
      >
        {{ token ? 'Voltar para o login' : 'Pedir novo link' }}
      </ULink>
    </p>
  </div>
</template>
