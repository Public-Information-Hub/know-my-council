import { apiGet } from '~/lib/api'

export type CurrentUser = {
  id: number
  name: string
  display_name: string | null
  handle: string | null
  email: string
  public_bio: string | null
  account_state: string
  verification_level: string
  trust_level: string
  is_super_admin: boolean
  two_factor_mode: string
  email_verified_at: string | null
  last_seen_at: string | null
  is_email_verified: boolean
}

type CurrentUserResponse = {
  user: CurrentUser
}

export function useCurrentUser() {
  const user = useState<CurrentUser | null>('kmc-current-user', () => null)
  const loaded = useState<boolean>('kmc-current-user-loaded', () => false)
  const loading = useState<boolean>('kmc-current-user-loading', () => false)

  async function refreshCurrentUser(): Promise<CurrentUser | null> {
    loading.value = true

    try {
      const response = await apiGet<CurrentUserResponse>('/auth/me')
      user.value = response.user
      loaded.value = true
      return user.value
    } catch {
      user.value = null
      loaded.value = true
      return null
    } finally {
      loading.value = false
    }
  }

  async function ensureCurrentUserLoaded(): Promise<CurrentUser | null> {
    if (!loaded.value) {
      return await refreshCurrentUser()
    }

    return user.value
  }

  function clearCurrentUser(): void {
    user.value = null
    loaded.value = true
  }

  return {
    user,
    loaded,
    loading,
    refreshCurrentUser,
    ensureCurrentUserLoaded,
    clearCurrentUser
  }
}
