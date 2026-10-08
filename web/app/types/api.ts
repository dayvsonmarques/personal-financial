export interface Organization {
  id: number
  name: string
  currency: string
  timezone: string
  locale: string
  settings: Record<string, unknown>
}

export interface User {
  id: number
  name: string
  email: string
  email_verified_at: string | null
  organization?: Organization
}

export interface Resource<T> {
  data: T
}

export interface ValidationErrorBody {
  message: string
  errors: Record<string, string[]>
}
