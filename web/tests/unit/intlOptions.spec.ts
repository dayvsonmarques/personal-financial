import { describe, expect, it } from 'vitest'
import { currencyOptions, timezoneOptions } from '~/utils/intlOptions'

describe('opções de moeda e fuso', () => {
  it('lista moedas ISO 4217 com nome em português', () => {
    const brl = currencyOptions().find(o => o.value === 'BRL')
    expect(brl?.label).toBe('BRL — Real brasileiro')
  })

  it('lista fusos IANA, incluindo UTC', () => {
    const values = timezoneOptions().map(o => o.value)
    expect(values).toContain('America/Sao_Paulo')
    expect(values).toContain('UTC')
  })
})
