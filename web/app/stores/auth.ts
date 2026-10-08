import { defineStore } from 'pinia'
import type { Resource, User } from '~/types/api'

export interface RegisterPayload {
  name: string
  email: string
  password: string
  password_confirmation: string
}

export const useAuthStore = defineStore('auth', () => {
  const api = useApi()

  const user = ref<User | null>(null)
  const loaded = ref(false)

  const isVerified = computed(() => !!user.value?.email_verified_at)

  async function fetchUser(): Promise<User | null> {
    try {
      user.value = (await api.get<Resource<User>>('/me')).data
    }
    catch (error) {
      if (statusOf(error) !== 401) throw error
      user.value = null
    }
    finally {
      loaded.value = true
    }
    return user.value
  }

  async function login(email: string, password: string, remember = false) {
    await csrf()
    await api.post('/auth/login', { email, password, remember })
    await fetchUser()
  }

  async function register(payload: RegisterPayload) {
    await csrf()
    await api.post('/auth/register', { ...payload })
    await fetchUser()
  }

  async function logout() {
    await api.post('/auth/logout')
    user.value = null
  }

  async function resendVerification() {
    await api.post('/auth/email/verification-notification')
  }

  async function forgotPassword(email: string) {
    await csrf()
    return api.post<{ message: string }>('/auth/forgot-password', { email })
  }

  async function resetPassword(payload: { token: string, email: string, password: string, password_confirmation: string }) {
    await csrf()
    await api.post('/auth/reset-password', payload)
  }

  async function updateProfile(payload: { name: string, email: string }) {
    await api.put('/auth/user/profile-information', payload)
    await fetchUser()
  }

  async function updatePassword(payload: { current_password: string, password: string, password_confirmation: string }) {
    await api.put('/auth/user/password', payload)
  }

  return {
    user, loaded, isVerified,
    fetchUser, login, register, logout, resendVerification,
    forgotPassword, resetPassword, updateProfile, updatePassword,
  }
})
