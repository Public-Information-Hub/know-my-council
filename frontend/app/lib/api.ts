type JsonValue = string | number | boolean | null | JsonValue[] | { [k: string]: JsonValue }

export class ApiError extends Error {
  constructor(
    message: string,
    public readonly status?: number,
    public readonly data?: unknown
  ) {
    super(message)
  }
}

type FetchErrorLike = {
  status?: number
  data?: unknown
  response?: {
    status?: number
    _data?: unknown
  }
}

function getApiBaseUrl(): string {
  const cfg = useRuntimeConfig()
  const base = String(process.server ? cfg.apiInternalBaseUrl : cfg.public.apiBaseUrl || '').replace(/\/+$/, '')
  if (!base) throw new Error('Missing API base URL runtime config')
  return base
}

export async function apiGet<T = JsonValue>(path: string, opts?: { signal?: AbortSignal }): Promise<T> {
  const base = getApiBaseUrl()
  const url = path.startsWith('http') ? path : `${base}${path.startsWith('/') ? '' : '/'}${path}`

  try {
    return await $fetch<T>(url, {
      method: 'GET',
      signal: opts?.signal
    })
  } catch (err: unknown) {
    // $fetch throws a structured error with status/data in most cases.
    const e = err as FetchErrorLike
    const status = e?.status ?? e?.response?.status
    const data = e?.data ?? e?.response?._data
    throw new ApiError(`API request failed: GET ${url}`, status, data)
  }
}
