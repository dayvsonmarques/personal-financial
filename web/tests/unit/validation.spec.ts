import { describe, expect, it } from 'vitest'
import { emailErrors, newPasswordErrors, requiredErrors } from '~/utils/validation'

describe('validação client-side', () => {
  it('exige campos preenchidos, ignorando espaços', () => {
    expect(requiredErrors({ name: '  ', email: 'a@b.co' }, ['name', 'email']))
      .toEqual([{ name: 'name', message: 'Campo obrigatório.' }])
  })

  it('rejeita e-mail mal formado', () => {
    expect(emailErrors('fulano@')).toEqual([{ name: 'email', message: 'Informe um e-mail válido.' }])
    expect(emailErrors('fulano@exemplo.com')).toEqual([])
  })

  it('exige senha com 8 caracteres e confirmação igual (RF-01)', () => {
    expect(newPasswordErrors('1234567', '1234567'))
      .toEqual([{ name: 'password', message: 'A senha deve ter pelo menos 8 caracteres.' }])
    expect(newPasswordErrors('12345678', '12345679'))
      .toEqual([{ name: 'password_confirmation', message: 'As senhas não conferem.' }])
    expect(newPasswordErrors('12345678', '12345678')).toEqual([])
  })
})
