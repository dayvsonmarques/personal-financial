<script setup lang="ts">
import type { FormError } from '@nuxt/ui'

useHead({ title: 'Perfil' })

const auth = useAuthStore()
const toast = useToast()

const profile = reactive({ name: auth.user?.name ?? '', email: auth.user?.email ?? '' })
const profileForm = useTemplateRef('profileForm')
const { error: profileError, run: runProfile } = useFormSubmit(profileForm, profile)

function validateProfile(s: typeof profile): FormError[] {
  return firstPerField([...requiredErrors(s, ['name', 'email']), ...emailErrors(s.email)])
}

async function saveProfile() {
  const emailChanged = profile.email.trim().toLowerCase() !== auth.user?.email
  if (!await runProfile(() => auth.updateProfile({ ...profile }))) return

  if (emailChanged) {
    // US-105: e-mail novo precisa ser confirmado antes de continuar.
    toast.add({ title: 'Confirme o novo e-mail', description: 'Enviamos um link para o endereço informado.', color: 'info', icon: 'i-lucide-mail' })
    await navigateTo('/verificar-email')
  }
  else {
    toast.add({ title: 'Perfil atualizado', color: 'success', icon: 'i-lucide-circle-check' })
  }
}

const password = reactive({ current_password: '', password: '', password_confirmation: '' })
const passwordForm = useTemplateRef('passwordForm')
const { error: passwordError, run: runPassword } = useFormSubmit(passwordForm, password)

function validatePassword(s: typeof password): FormError[] {
  return firstPerField([
    ...requiredErrors(s, ['current_password', 'password', 'password_confirmation']),
    ...newPasswordErrors(s.password, s.password_confirmation),
  ])
}

async function savePassword() {
  if (!await runPassword(() => auth.updatePassword({ ...password }))) return

  Object.assign(password, { current_password: '', password: '', password_confirmation: '' })
  toast.add({
    title: 'Senha alterada',
    description: 'Sessões abertas em outros dispositivos foram encerradas.',
    color: 'success',
    icon: 'i-lucide-circle-check',
  })
}
</script>

<template>
  <AppPage title="Configurações">
    <SettingsNav />

    <div class="mx-auto max-w-2xl space-y-6">
      <UCard>
        <template #header>
          <h2 class="font-semibold text-highlighted">
            Dados pessoais
          </h2>
        </template>

        <UForm
          ref="profileForm"
          :state="profile"
          :validate="validateProfile"
          class="space-y-4"
          @submit="saveProfile"
        >
          <UAlert
            v-if="profileError"
            color="error"
            variant="subtle"
            icon="i-lucide-circle-alert"
            :title="profileError"
          />

          <UFormField
            label="Nome"
            name="name"
          >
            <UInput
              v-model="profile.name"
              autocomplete="name"
              class="w-full"
            />
          </UFormField>

          <UFormField
            label="E-mail"
            name="email"
            help="Ao trocar o e-mail, você precisará confirmá-lo novamente."
          >
            <UInput
              v-model="profile.email"
              type="email"
              autocomplete="email"
              class="w-full"
            />
          </UFormField>

          <div class="flex justify-end">
            <UButton
              type="submit"
              label="Salvar"
            />
          </div>
        </UForm>
      </UCard>

      <UCard>
        <template #header>
          <h2 class="font-semibold text-highlighted">
            Senha
          </h2>
        </template>

        <UForm
          ref="passwordForm"
          :state="password"
          :validate="validatePassword"
          class="space-y-4"
          @submit="savePassword"
        >
          <UAlert
            v-if="passwordError"
            color="error"
            variant="subtle"
            icon="i-lucide-circle-alert"
            :title="passwordError"
          />

          <UFormField
            label="Senha atual"
            name="current_password"
          >
            <UInput
              v-model="password.current_password"
              type="password"
              autocomplete="current-password"
              class="w-full"
            />
          </UFormField>

          <UFormField
            label="Nova senha"
            name="password"
            :help="`Mínimo de ${PASSWORD_MIN} caracteres. Outros dispositivos serão desconectados.`"
          >
            <UInput
              v-model="password.password"
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
              v-model="password.password_confirmation"
              type="password"
              autocomplete="new-password"
              class="w-full"
            />
          </UFormField>

          <div class="flex justify-end">
            <UButton
              type="submit"
              label="Alterar senha"
            />
          </div>
        </UForm>
      </UCard>
    </div>
  </AppPage>
</template>
