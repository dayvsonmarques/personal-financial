type Numeric = number | string

const NBSP = new RegExp('[\\u00A0\\u202F]', 'g')

/**
 * Formatação pt-BR de valores e datas.
 * Valores chegam da API como string decimal ("1234.56") — seção 11.
 */
export function useFormat(locale = 'pt-BR', currency = 'BRL') {
  const moneyFormatter = new Intl.NumberFormat(locale, { style: 'currency', currency })
  const dateFormatter = new Intl.DateTimeFormat(locale, { timeZone: 'UTC' })

  function money(value: Numeric): string {
    return moneyFormatter.format(Number(value)).replace(NBSP, ' ')
  }

  /** Datas de competência são `date` sem hora (RN-51): formatar em UTC evita voltar um dia. */
  function date(iso: string): string {
    return dateFormatter.format(new Date(`${iso.slice(0, 10)}T00:00:00Z`))
  }

  function parseMoney(text: string): number | null {
    const cleaned = text.replace(/[^\d,-]/g, '').replace(',', '.')
    if (cleaned === '' || cleaned === '-') return null
    const value = Number(cleaned)
    return Number.isFinite(value) ? value : null
  }

  return { money, date, parseMoney }
}
