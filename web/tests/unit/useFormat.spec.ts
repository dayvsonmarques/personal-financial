import { describe, expect, it } from 'vitest'
import { useFormat } from '~/composables/useFormat'

describe('useFormat', () => {
  const { money, date, parseMoney } = useFormat()

  it('formata valores em reais no padrão pt-BR', () => {
    expect(money(1234.5)).toBe('R$ 1.234,50')
    expect(money('0.1')).toBe('R$ 0,10')
    expect(money(-10)).toBe('-R$ 10,00')
  })

  it('formata datas ISO sem deslocamento de fuso', () => {
    expect(date('2026-03-05')).toBe('05/03/2026')
    expect(date('2026-01-01')).toBe('01/01/2026')
  })

  it('converte texto pt-BR em número', () => {
    expect(parseMoney('1.234,56')).toBe(1234.56)
    expect(parseMoney('R$ 10,00')).toBe(10)
    expect(parseMoney('')).toBeNull()
    expect(parseMoney('abc')).toBeNull()
  })
})
