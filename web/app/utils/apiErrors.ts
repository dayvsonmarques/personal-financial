import type { FormError } from '@nuxt/ui'
import type { FetchError } from 'ofetch'
import type { ValidationErrorBody } from '~/types/api'

export function isFetchError(error: unknown): error is FetchError {
  return typeof error === 'object' && error !== null && 'statusCode' in error
}

export function statusOf(error: unknown): number | undefined {
  return isFetchError(error) ? error.statusCode : undefined
}

/** Converte um 422 do Laravel em erros por campo do UForm. */
export function toFormErrors(error: unknown): FormError[] {
  if (statusOf(error) !== 422) return []
  const body = (error as FetchError<ValidationErrorBody>).data
  return Object.entries(body?.errors ?? {}).map(([name, messages]) => ({ name, message: messages[0] ?? '' }))
}

export function errorMessage(error: unknown, fallback = 'Algo deu errado. Tente novamente.'): string {
  if (statusOf(error) === 429) return 'Muitas tentativas. Aguarde um pouco e tente de novo.'
  const data = isFetchError(error) ? (error.data as { message?: string } | undefined) : undefined
  return data?.message ?? fallback
}
