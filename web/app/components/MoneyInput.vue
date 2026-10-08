<script setup lang="ts">
/**
 * Campo de valor com máscara pt-BR (seção 13). Os dígitos digitados são centavos:
 * "123456" vira "1.234,56". Emite string decimal ("1234.56"), formato da API.
 */
const props = defineProps<{
  modelValue: string | null
}>()

const emit = defineEmits<{
  'update:modelValue': [value: string | null]
}>()

const formatter = new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })

function toDisplay(value: string | null): string {
  return value === null || value === '' ? '' : formatter.format(Number(value))
}

const display = ref(toDisplay(props.modelValue))
let lastEmitted = props.modelValue

watch(() => props.modelValue, (value) => {
  if (value !== lastEmitted) display.value = toDisplay(value)
})

function onInput(raw: string | number | null | undefined) {
  const digits = String(raw ?? '').replace(/\D/g, '').replace(/^0+(?=\d)/, '')
  const value = digits === '' ? null : (Number(digits) / 100).toFixed(2)

  display.value = toDisplay(value)
  lastEmitted = value
  emit('update:modelValue', value)
}
</script>

<template>
  <UInput
    :model-value="display"
    inputmode="numeric"
    autocomplete="off"
    placeholder="0,00"
    @update:model-value="onInput"
  >
    <template #leading>
      <span class="text-muted text-sm">R$</span>
    </template>
  </UInput>
</template>
