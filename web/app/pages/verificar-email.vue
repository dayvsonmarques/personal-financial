<script setup lang="ts">
definePageMeta({ layout: 'auth' })
useHead({ title: 'Verifique seu e-mail' })

const auth = useAuthStore()

const sending = ref(false)
const checking = ref(false)
const feedback = ref<{ color: 'success' | 'error' | 'warning', text: string } | null>(null)

async function resend() {
  sending.value = true
  feedback.value = null
  try {
    await auth.resendVerification()
    feedback.value = { color: 'success', text: 'Enviamos um novo link. Confira sua caixa de entrada.' }
  }
  catch (e) {
    feedback.value = { color: 'error', text: errorMessage(e) }
  }
  finally {
    sending.value = false
  }
}

async function alreadyVerified() {
  checking.value = true
  feedback.value = null
  try {
    await auth.fetchUser()
    if (auth.isVerified) await navigateTo('/')
    else feedback.value = { color: 'warning', text: 'Ainda não recebemos a confirmação. Abra o link enviado por e-mail.' }
  }
  catch (e) {
    feedback.value = { color: 'error', text: errorMessage(e) }
  }
  finally {
    checking.value = false
  }
}

async function logout() {
  await auth.logout()
  await navigateTo('/entrar')
}
</script>

<template>
  <div class="space-y-6 text-center">
    <UIcon
      name="i-lucide-mail-check"
      class="mx-auto size-10 text-primary"
    />

    <div class="space-y-1">
      <h1 class="text-xl font-semibold text-highlighted">
        Verifique seu e-mail
      </h1>
      <p class="text-sm text-muted">
        Enviamos um link de confirmação para
        <strong class="text-default">{{ auth.user?.email }}</strong>.
        Abra-o para liberar o acesso.
      </p>
    </div>

    <UAlert
      v-if="feedback"
      :color="feedback.color"
      variant="subtle"
      :title="feedback.text"
      class="text-left"
    />

    <div class="space-y-3">
      <UButton
        label="Já verifiquei"
        block
        :loading="checking"
        @click="alreadyVerified"
      />
      <UButton
        label="Reenviar e-mail"
        color="neutral"
        variant="outline"
        block
        :loading="sending"
        @click="resend"
      />
    </div>

    <UButton
      label="Sair"
      color="neutral"
      variant="link"
      @click="logout"
    />
  </div>
</template>
