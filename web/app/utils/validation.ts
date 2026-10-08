import type { FormError } from '@nuxt/ui'

/** Espelha as regras do backend para dar retorno antes do envio; o 422 continua sendo a fonte da verdade. */
export const PASSWORD_MIN = 8

const EMAIL = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

export function requiredErrors<T extends object>(state: T, fields: (keyof T & string)[]): FormError[] {
  return fields
    .filter(name => !String(state[name] ?? '').trim())
    .map(name => ({ name, message: 'Campo obrigatório.' }))
}

export function emailErrors(email: string, name = 'email'): FormError[] {
  return email && !EMAIL.test(email) ? [{ name, message: 'Informe um e-mail válido.' }] : []
}

export function newPasswordErrors(password: string, confirmation: string): FormError[] {
  if (password && password.length < PASSWORD_MIN) {
    return [{ name: 'password', message: `A senha deve ter pelo menos ${PASSWORD_MIN} caracteres.` }]
  }
  if (password && confirmation && password !== confirmation) {
    return [{ name: 'password_confirmation', message: 'As senhas não conferem.' }]
  }
  return []
}

/** Mantém só o primeiro erro de cada campo (ex.: "obrigatório" antes de "e-mail inválido"). */
export function firstPerField(errors: FormError[]): FormError[] {
  return errors.filter((error, index) => errors.findIndex(e => e.name === error.name) === index)
}
