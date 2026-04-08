type JsonValue = string | number | boolean | null | JsonValue[] | { [k: string]: JsonValue }

type ApiMethod = 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE'

export class ApiError extends Error {
  constructor(
    message: string,
    public readonly status?: number,
    public readonly data?: unknown
  ) {
    super(message)
  }
}

function collectErrorMessages(value: unknown): string[] {
  if (typeof value === 'string') return [value]
  if (!Array.isArray(value)) return []

  return value.flatMap((item) => collectErrorMessages(item))
}

type FetchErrorLike = {
  status?: number
  data?: unknown
  response?: {
    status?: number
    _data?: unknown
  }
}

type ApiRequestOptions = {
  body?: unknown
  headers?: Record<string, string>
  signal?: AbortSignal
}

function getApiBaseUrl(): string {
  const cfg = useRuntimeConfig()
  const base = String(process.server ? cfg.apiInternalBaseUrl : cfg.public.apiBaseUrl || '').replace(/\/+$/, '')
  if (!base) throw new Error('Missing API base URL runtime config')
  return base
}

function getCookie(name: string): string | null {
  if (process.server || typeof document === 'undefined') return null

  const cookie = document.cookie
    .split('; ')
    .find((part) => part.startsWith(`${name}=`))

  return cookie ? decodeURIComponent(cookie.split('=').slice(1).join('=')) : null
}

async function ensureCsrfCookie(): Promise<void> {
  if (process.server) return

  if (getCookie('XSRF-TOKEN')) return

  const base = getApiBaseUrl()
  await $fetch(`${base}/auth/csrf-cookie`, {
    method: 'GET',
    credentials: 'include'
  })
}

async function apiRequest<T = JsonValue>(method: ApiMethod, path: string, opts: ApiRequestOptions = {}): Promise<T> {
  const base = getApiBaseUrl()
  const url = path.startsWith('http') ? path : `${base}${path.startsWith('/') ? '' : '/'}${path}`

  if (method !== 'GET' && process.client) {
    await ensureCsrfCookie()
  }

  const headers: Record<string, string> = {
    Accept: 'application/json',
    ...opts.headers
  }

  const xsrf = getCookie('XSRF-TOKEN')
  if (xsrf && method !== 'GET') {
    headers['X-XSRF-TOKEN'] = xsrf
  }

  try {
    return await $fetch<T>(url, {
      method,
      body: opts.body as BodyInit | Record<string, any> | null | undefined,
      headers,
      signal: opts.signal,
      credentials: 'include'
    })
  } catch (err: unknown) {
    const e = err as FetchErrorLike
    const status = e?.status ?? e?.response?.status
    const data = e?.data ?? e?.response?._data
    throw new ApiError(`API request failed: ${method} ${url}`, status, data)
  }
}

export async function apiGet<T = JsonValue>(path: string, opts?: { signal?: AbortSignal }): Promise<T> {
  return apiRequest<T>('GET', path, opts)
}

export async function apiPost<T = JsonValue>(path: string, body?: unknown, opts?: ApiRequestOptions): Promise<T> {
  return apiRequest<T>('POST', path, { ...opts, body })
}

export async function apiPatch<T = JsonValue>(path: string, body?: unknown, opts?: ApiRequestOptions): Promise<T> {
  return apiRequest<T>('PATCH', path, { ...opts, body })
}

export async function apiDelete<T = JsonValue>(path: string, opts?: ApiRequestOptions): Promise<T> {
  return apiRequest<T>('DELETE', path, opts)
}

export function formatApiError(error: unknown, fallback = 'Something went wrong.'): string {
  if (!(error instanceof ApiError)) {
    return fallback
  }

  const data = error.data as { message?: unknown; errors?: Record<string, unknown> } | undefined
  const directMessage = typeof data?.message === 'string' ? data.message : null
  if (directMessage) {
    return directMessage
  }

  if (data?.errors) {
    for (const value of Object.values(data.errors)) {
      const messages = collectErrorMessages(value)
      if (messages.length > 0) {
        return messages[0] ?? fallback
      }
    }
  }

  return error.message || fallback
}
