<script setup lang="ts">
import type { FormError } from '@nuxt/ui'
import type { Organization, Resource } from '~/types/api'

useHead({ title: 'Organização' })

const api = useApi()
const auth = useAuthStore()
const toast = useToast()

const currencies = currencyOptions()
const timezones = timezoneOptions()
const locales: SelectOption[] = [{ value: 'pt-BR', label: 'Português (Brasil)' }]

const state = reactive({ name: '', currency: '', timezone: '', locale: '' })
const loading = ref(true)
const loadError = ref<string | null>(null)

async function load() {
  loading.value = true
  loadError.value = null
  try {
    const { data } = await api.get<Resource<Organization>>('/organization')
    Object.assign(state, { name: data.name, currency: data.currency, timezone: data.timezone, locale: data.locale })
  }
  catch (e) {
    loadError.value = errorMessage(e, 'Não foi possível carregar a organização.')
  }
  finally {
    loading.value = false
  }
}

onMounted(load)

const form = useTemplateRef('form')
const { error, run } = useFormSubmit(form, state)

function validate(s: typeof state): FormError[] {
  return requiredErrors(s, ['name', 'currency', 'timezone', 'locale'])
}

async function onSubmit() {
  if (await run(() => api.put('/organization', { ...state }))) {
    // O /me traz a organização; mantém o restante do app com moeda e fuso novos.
    await auth.fetchUser()
    toast.add({ title: 'Organização atualizada', color: 'success', icon: 'i-lucide-circle-check' })
  }
}
</script>

<template>
  <AppPage title="Configurações">
    <SettingsNav />

    <div class="mx-auto max-w-2xl">
      <UCard>
        <template #header>
          <h2 class="font-semibold text-highlighted">
            Organização
          </h2>
          <p class="text-sm text-muted">
            Moeda e fuso usados nos lançamentos, faturas e relatórios.
          </p>
        </template>

        <div
          v-if="loading"
          class="space-y-4"
        >
          <USkeleton
            v-for="i in 4"
            :key="i"
            class="h-10 w-full"
          />
        </div>

        <ErrorState
          v-else-if="loadError"
          :message="loadError"
          @retry="load"
        />

        <UForm
          v-else
          ref="form"
          :state="state"
          :validate="validate"
          class="space-y-4"
          @submit="onSubmit"
        >
          <UAlert
            v-if="error"
            color="error"
            variant="subtle"
            icon="i-lucide-circle-alert"
            :title="error"
          />

          <UFormField
            label="Nome"
            name="name"
          >
            <UInput
              v-model="state.name"
              class="w-full"
            />
          </UFormField>

          <UFormField
            label="Moeda"
            name="currency"
          >
            <USelectMenu
              v-model="state.currency"
              :items="currencies"
              value-key="value"
              :search-input="{ placeholder: 'Buscar moeda…' }"
              class="w-full"
            />
          </UFormField>

          <UFormField
            label="Fuso horário"
            name="timezone"
          >
            <USelectMenu
              v-model="state.timezone"
              :items="timezones"
              value-key="value"
              :search-input="{ placeholder: 'Buscar fuso…' }"
              virtualize
              class="w-full"
            />
          </UFormField>

          <UFormField
            label="Idioma"
            name="locale"
          >
            <USelect
              v-model="state.locale"
              :items="locales"
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
    </div>
  </AppPage>
</template>
