export interface SelectOption {
  label: string
  value: string
}

/** Moedas ISO 4217 conhecidas pelo navegador; o backend valida contra a lista completa. */
export function currencyOptions(): SelectOption[] {
  const names = new Intl.DisplayNames('pt-BR', { type: 'currency' })
  return Intl.supportedValuesOf('currency').map((code) => {
    const name = names.of(code) ?? code
    return { value: code, label: `${code} — ${name.charAt(0).toUpperCase()}${name.slice(1)}` }
  })
}

/** Fusos IANA. Alguns ambientes omitem "UTC" da lista, então ele é garantido aqui. */
export function timezoneOptions(): SelectOption[] {
  const zones = new Set(Intl.supportedValuesOf('timeZone'))
  zones.add('UTC')
  return [...zones].sort().map(zone => ({ value: zone, label: zone.replaceAll('_', ' ') }))
}
