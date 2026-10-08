<script setup lang="ts">
import type { FormError } from '@nuxt/ui'

definePageMeta({ layout: 'auth' })
useHead({ title: 'Entrar' })

const auth = useAuthStore()
const route = useRoute()

const state = reactive({ email: '', password: '', remember: false })
const form = useTemplateRef('form')
const { error, run } = useFormSubmit(form, state)

function validate(s: typeof state): FormError[] {
  return firstPerField([
    ...requiredErrors(s, ['email', 'password']),
    ...emailErrors(s.email),
  ])
}

/** Só aceita caminhos internos, para não virar redirecionamento aberto. */
function redirectTarget(): string {
  const target = route.query.redirect
  return typeof target === 'string' && target.startsWith('/') && !target.startsWith('//') ? target : '/'
}

async function onSubmit() {
  if (await run(() => auth.login(state.email, state.password, state.remember))) {
    await navigateTo(auth.isVerified ? redirectTarget() : '/verificar-email')
  }
}
</script>

<template>
  <div class="space-y-6">
    <div class="space-y-1 text-center">
      <h1 class="text-xl font-semibold text-highlighted">
        Entrar
      </h1>
      <p class="text-sm text-muted">
        Acesse sua conta para continuar.
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
      >
        <template #hint>
          <ULink
            to="/esqueci-senha"
            class="text-sm"
          >
            Esqueci a senha
          </ULink>
        </template>
        <UInput
          v-model="state.password"
          type="password"
          autocomplete="current-password"
          class="w-full"
        />
      </UFormField>

      <UCheckbox
        v-model="state.remember"
        label="Manter conectado"
      />

      <UButton
        type="submit"
        label="Entrar"
        block
      />
    </UForm>

    <p class="text-center text-sm text-muted">
      Não tem conta?
      <ULink
        to="/cadastro"
        class="font-medium text-primary"
      >
        Cadastre-se
      </ULink>
    </p>
  </div>
</template>
