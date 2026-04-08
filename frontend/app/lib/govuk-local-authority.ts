import { ApiError, apiGet } from '~/lib/api'

type LocalAuthorityRecord = {
  name: string
  homepage_url?: string
  tier?: string
  slug: string
  parent?: LocalAuthorityRecord
}

type PostcodeResult =
  | { local_authority: LocalAuthorityRecord }
  | { addresses: Array<{ address: string; slug: string; name: string }> }

const GOVUK_LOCAL_AUTHORITY_API = 'https://www.gov.uk/api/local-authority'

export async function lookupLocalAuthorityByPostcode(postcode: string): Promise<PostcodeResult> {
  return await $fetch<PostcodeResult>(`${GOVUK_LOCAL_AUTHORITY_API}?postcode=${encodeURIComponent(postcode)}`)
}

export async function lookupLocalAuthorityBySlug(slug: string): Promise<LocalAuthorityRecord> {
  const encoded = encodeURIComponent(slug)
  try {
    const response = await apiGet<{ local_authority: LocalAuthorityRecord }>(`/api/councils/${encoded}`)
    return response.local_authority
  } catch (error) {
    const status = error instanceof ApiError ? error.status : undefined
    if (status !== 404) {
      throw error
    }
  }

  const attempts = [
    `${GOVUK_LOCAL_AUTHORITY_API}/${encoded}`,
    `${GOVUK_LOCAL_AUTHORITY_API}/?slug=${encoded}`
  ]

  let lastError: unknown

  for (const url of attempts) {
    try {
      const response = await $fetch<{ local_authority: LocalAuthorityRecord } | LocalAuthorityRecord>(url)
      if ('local_authority' in response) {
        return response.local_authority
      }
      return response
    } catch (error) {
      lastError = error
    }
  }

  throw lastError instanceof Error
    ? lastError
    : new Error(`Could not load local authority ${slug}`)
}
