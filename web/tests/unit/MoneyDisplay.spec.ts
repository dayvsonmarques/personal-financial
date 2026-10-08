import { describe, expect, it } from 'vitest'
import { mountSuspended } from '@nuxt/test-utils/runtime'
import MoneyDisplay from '~/components/MoneyDisplay.vue'

describe('MoneyDisplay', () => {
  it('mostra despesa com sinal de menos e cor de erro (seção 13)', async () => {
    const wrapper = await mountSuspended(MoneyDisplay, { props: { value: '150.5', type: 'expense' } })

    expect(wrapper.text()).toContain('−')
    expect(wrapper.text()).toContain('R$ 150,50')
    expect(wrapper.classes()).toContain('text-error')
  })

  it('mostra receita com sinal de mais e cor de sucesso', async () => {
    const wrapper = await mountSuspended(MoneyDisplay, { props: { value: 10, type: 'income' } })

    expect(wrapper.text()).toContain('+')
    expect(wrapper.classes()).toContain('text-success')
  })

  it('mostra valor neutro sem sinal', async () => {
    const wrapper = await mountSuspended(MoneyDisplay, { props: { value: 10 } })

    expect(wrapper.text().trim()).toBe('R$ 10,00')
  })
})
