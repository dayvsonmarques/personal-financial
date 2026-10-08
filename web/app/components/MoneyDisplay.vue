<script setup lang="ts">
type MoneyType = 'income' | 'expense' | 'neutral'

const props = withDefaults(defineProps<{
  value: number | string
  type?: MoneyType
}>(), { type: 'neutral' })

const { money } = useFormat()

// Seção 13: cor sempre acompanhada de sinal, para não depender só da cor.
const sign = computed(() => ({ income: '+', expense: '−', neutral: '' })[props.type])
const label = computed(() => ({ income: 'Receita', expense: 'Despesa', neutral: '' })[props.type])
const colorClass = computed(() => ({ income: 'text-success', expense: 'text-error', neutral: '' })[props.type])
</script>

<template>
  <span
    class="tabular-nums whitespace-nowrap"
    :class="colorClass"
  >
    <span
      v-if="label"
      class="sr-only"
    >{{ label }}: </span>
    <span aria-hidden="true">{{ sign }}</span>{{ money(Math.abs(Number(value))) }}
  </span>
</template>
