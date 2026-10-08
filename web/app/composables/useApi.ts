import type { NitroFetchOptions, NitroFetchRequest } from 'nitropack'

type ApiOptions = NitroFetchOptions<NitroFetchRequest>

function readCookie(name: string): string | null {
  const match = document.cookie.match(new RegExp(`(?:^|; )${name}=([^;]*)`))
  return match ? decodeURIComponent(match[1]!) : null
}

export async function csrf(): Promise<void> {
  await $fetch('/sanctum/csrf-cookie', { credentials: 'include' })
}

/**
 * Cliente da API (D21: mesma origem). Envia o token CSRF do Sanctum,
 * renova-o uma vez em caso de 419 e trata sessão expirada (401).
 */
export function useApi() {
  async function request<T>(path: string, options: ApiOptions = {}, retried = false): Promise<T> {
    const method = (options.method ?? 'GET').toUpperCase()
    if (method !== 'GET' && !readCookie('XSRF-TOKEN')) await csrf()

    try {
      return await $fetch<T>(`/api/v1${path}`, {
        ...options,
        credentials: 'include',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-XSRF-TOKEN': readCookie('XSRF-TOKEN') ?? '',
          ...(options.headers as Record<string, string> | undefined),
        },
      }) as T
    }
    catch (error) {
      if (statusOf(error) === 419 && !retried) {
        await csrf()
        return request<T>(path, options, true)
      }
      // Sessão expirada. O /me trata o próprio 401 (usado para descobrir se há sessão).
      if (statusOf(error) === 401 && path !== '/me') {
        useAuthStore().user = null
        await navigateTo('/entrar')
      }
      throw error
    }
  }

  return {
    get: <T>(path: string, query?: Record<string, unknown>) => request<T>(path, { query }),
    post: <T>(path: string, body?: Record<string, unknown>) => request<T>(path, { method: 'POST', body }),
    put: <T>(path: string, body?: Record<string, unknown>) => request<T>(path, { method: 'PUT', body }),
    del: <T>(path: string) => request<T>(path, { method: 'DELETE' }),
  }
}
