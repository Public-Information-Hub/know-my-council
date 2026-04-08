import { ApiError } from '~/lib/api'

type ValidationPayload = {
  errors?: Record<string, unknown>
}

export type FieldErrorMap = Record<string, string[]>

function collectMessages(value: unknown): string[] {
  if (typeof value === 'string') return [value]
  if (!Array.isArray(value)) return []

  return value.flatMap((item) => collectMessages(item))
}

export function extractFieldErrors(error: unknown): FieldErrorMap {
  if (!(error instanceof ApiError)) return {}

  const data = error.data as ValidationPayload | undefined
  if (!data?.errors) return {}

  const fieldErrors: FieldErrorMap = {}

  for (const [field, value] of Object.entries(data.errors)) {
    const messages = collectMessages(value).filter((message) => message.length > 0)
    if (messages.length > 0) {
      fieldErrors[field] = messages
    }
  }

  return fieldErrors
}

export function firstFieldError(errors: FieldErrorMap, field: string): string {
  return errors[field]?.[0] ?? ''
}

export function hasFieldErrors(errors: FieldErrorMap): boolean {
  return Object.keys(errors).length > 0
}

export function normaliseHandle(value: string): string {
  return value.trim().toLowerCase()
}

export function isValidHandle(value: string): boolean {
  if (!value.trim()) return false
  return /^[a-z0-9_.-]+$/.test(normaliseHandle(value))
}

