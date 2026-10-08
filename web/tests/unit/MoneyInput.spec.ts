import { describe, expect, it } from 'vitest'
import { mountSuspended } from '@nuxt/test-utils/runtime'
import MoneyInput from '~/components/MoneyInput.vue'

describe('MoneyInput', () => {
  it('trata os dígitos digitados como centavos e emite string decimal', async () => {
    const wrapper = await mountSuspended(MoneyInput, { props: { modelValue: null } })
    const input = wrapper.find('input')

    await input.setValue('123456')

    expect(wrapper.emitted('update:modelValue')!.at(-1)).toEqual(['1234.56'])
    expect(input.element.value).toBe('1.234,56')
  })

  it('exibe o valor inicial formatado', async () => {
    const wrapper = await mountSuspended(MoneyInput, { props: { modelValue: '99.9' } })

    expect(wrapper.find('input').element.value).toBe('99,90')
  })

  it('emite null quando o campo é apagado', async () => {
    const wrapper = await mountSuspended(MoneyInput, { props: { modelValue: '10.00' } })

    await wrapper.find('input').setValue('')

    expect(wrapper.emitted('update:modelValue')!.at(-1)).toEqual([null])
  })
})
