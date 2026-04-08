<script setup lang="ts">
import { apiGet, apiPatch, apiPost, formatApiError } from '~/lib/api'
import { extractFieldErrors, firstFieldError, hasFieldErrors, isValidHandle, normaliseHandle, type FieldErrorMap } from '~/lib/form-errors'

type AuthUser = {
  id: number
  name: string
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

type AuthResponse = {
  user: AuthUser
}

useHead({
  title: 'Profile'
})

const router = useRouter()
const route = useRoute()
const { clearCurrentUser } = useCurrentUser()
const user = ref<AuthUser | null>(null)
const loading = ref(true)
const notice = ref(route.query.signed_in ? 'You are signed in.' : route.query.password_reset ? 'Password updated.' : '')
const errorMessage = ref('')
const savingProfile = ref(false)
const savingPassword = ref(false)
const sendingVerification = ref(false)
const fieldErrors = ref<FieldErrorMap>({})

const profileName = ref('')
const profileHandle = ref('')
const profileBio = ref('')
const currentPassword = ref('')
const newPassword = ref('')
const newPasswordConfirmation = ref('')

function clearFormErrors(): void {
  fieldErrors.value = {}
}

function setFormErrors(error: unknown, fallback: string): void {
  const nextFieldErrors = extractFieldErrors(error)
  fieldErrors.value = nextFieldErrors

  if (hasFieldErrors(nextFieldErrors)) {
    errorMessage.value = 'Please correct the highlighted fields.'
    return
  }

  errorMessage.value = formatApiError(error, fallback)
}

async function loadUser(): Promise<void> {
  loading.value = true
  try {
    const response = await apiGet<AuthResponse>('/auth/me')
    user.value = response.user
    profileName.value = response.user.name
    profileHandle.value = response.user.handle ?? ''
    profileBio.value = response.user.public_bio ?? ''
  } catch {
    user.value = null
  } finally {
    loading.value = false
  }
}

async function saveProfile(): Promise<void> {
  if (!user.value) return
  savingProfile.value = true
  notice.value = ''
  errorMessage.value = ''
  clearFormErrors()

  try {
    profileHandle.value = normaliseHandle(profileHandle.value)

    if (!isValidHandle(profileHandle.value)) {
      fieldErrors.value = {
        handle: ['Use only letters, numbers, dots, hyphens, and underscores.']
      }
      errorMessage.value = 'Please correct the highlighted fields.'
      savingProfile.value = false
      return
    }

    const response = await apiPatch<AuthResponse>('/auth/profile', {
      name: profileName.value,
      handle: profileHandle.value,
      public_bio: profileBio.value || null
    })
    user.value = response.user
    notice.value = 'Profile updated.'
  } catch (error: unknown) {
    setFormErrors(error, 'Could not update your profile.')
  } finally {
    savingProfile.value = false
  }
}

async function savePassword(): Promise<void> {
  if (!user.value) return
  savingPassword.value = true
  notice.value = ''
  errorMessage.value = ''
  clearFormErrors()

  try {
    await apiPatch('/auth/password', {
      current_password: currentPassword.value,
      password: newPassword.value,
      password_confirmation: newPasswordConfirmation.value
    })
    currentPassword.value = ''
    newPassword.value = ''
    newPasswordConfirmation.value = ''
    notice.value = 'Password updated.'
  } catch (error: unknown) {
    setFormErrors(error, 'Could not update your password.')
  } finally {
    savingPassword.value = false
  }
}

async function resendVerification(): Promise<void> {
  sendingVerification.value = true
  notice.value = ''
  errorMessage.value = ''
  clearFormErrors()

  try {
    const response = await apiPost<{ message: string; user: AuthUser }>('/auth/email-verification-notification')
    user.value = response.user
    notice.value = response.message
  } catch (error: unknown) {
    errorMessage.value = formatApiError(error, 'Could not send verification email.')
  } finally {
    sendingVerification.value = false
  }
}

async function signOut(): Promise<void> {
  await apiPost('/auth/logout')
  user.value = null
  clearCurrentUser()
  await router.push('/login?signed_out=1')
}

onMounted(async () => {
  await loadUser()
})
</script>

<template>
  <div class="landing auth-page">
    <section class="panel auth-card auth-card--wide">
      <div class="auth-card__intro">
        <h1 class="hero__title">Your profile</h1>
      </div>

      <div v-if="notice" class="callout" role="status" aria-live="polite">{{ notice }}</div>
      <div v-if="errorMessage" class="callout auth-card__error" role="alert">{{ errorMessage }}</div>

      <div v-if="loading" class="callout">Loading...</div>
      <template v-else>
        <div v-if="user" class="stack">
          <div class="card-grid card-grid--two">
            <article class="card">
              <h2 style="margin-top: 0; font-size: 1rem;">Account</h2>
              <dl class="fact-grid">
                <div class="fact-grid__item">
                  <dt class="fact-grid__label">Name</dt>
                  <dd class="fact-grid__value">{{ user.name }}</dd>
                </div>
                <div class="fact-grid__item">
                  <dt class="fact-grid__label">Handle</dt>
                  <dd class="fact-grid__value">{{ user.handle ?? 'Not set' }}</dd>
                </div>
                <div class="fact-grid__item">
                  <dt class="fact-grid__label">Email</dt>
                  <dd class="fact-grid__value">{{ user.email }}</dd>
                </div>
              </dl>
            </article>
            <article class="card">
              <h2 style="margin-top: 0; font-size: 1rem;">Status</h2>
              <dl class="fact-grid">
                <div class="fact-grid__item">
                  <dt class="fact-grid__label">Email verified</dt>
                  <dd class="fact-grid__value">{{ user.is_email_verified ? 'Yes' : 'Not yet' }}</dd>
                </div>
                <div class="fact-grid__item">
                  <dt class="fact-grid__label">Account state</dt>
                  <dd class="fact-grid__value">{{ user.account_state }}</dd>
                </div>
                <div v-if="user.is_super_admin" class="fact-grid__item">
                  <dt class="fact-grid__label">Role</dt>
                  <dd class="fact-grid__value">Administrator</dd>
                </div>
              </dl>
            </article>
          </div>

          <article class="card">
            <h2 style="margin-top: 0; font-size: 1rem;">Edit profile</h2>
            <form class="auth-form auth-form--grid" @submit.prevent="saveProfile">
              <label class="field">
                <span class="field__label">Name</span>
                <input v-model="profileName" class="field__input" type="text" required>
                <span v-if="firstFieldError(fieldErrors, 'name')" class="field__error" role="alert">{{ firstFieldError(fieldErrors, 'name') }}</span>
              </label>

              <label class="field">
                <span class="field__label">Handle</span>
                <input
                  v-model="profileHandle"
                  class="field__input"
                  type="text"
                  inputmode="text"
                  maxlength="32"
                  required
                  :aria-invalid="Boolean(firstFieldError(fieldErrors, 'handle'))"
                  @blur="profileHandle = normaliseHandle(profileHandle)"
                >
                <span v-if="firstFieldError(fieldErrors, 'handle')" class="field__error" role="alert">{{ firstFieldError(fieldErrors, 'handle') }}</span>
              </label>

              <label class="field field--full">
                <span class="field__label">Public bio</span>
                <textarea v-model="profileBio" class="field__input field__input--textarea" rows="3" maxlength="280"></textarea>
                <span v-if="firstFieldError(fieldErrors, 'public_bio')" class="field__error" role="alert">{{ firstFieldError(fieldErrors, 'public_bio') }}</span>
              </label>

              <div class="auth-actions field--full">
                <button class="finder__button" type="submit" :disabled="savingProfile">{{ savingProfile ? 'Saving...' : 'Save profile' }}</button>
                <button v-if="!user.is_email_verified" class="pill" type="button" :disabled="sendingVerification" @click="resendVerification">
                  {{ sendingVerification ? 'Sending...' : 'Resend verification email' }}
                </button>
                <button class="pill" type="button" @click="signOut">Sign out</button>
              </div>
            </form>
          </article>

          <article class="card">
            <h2 style="margin-top: 0; font-size: 1rem;">Change password</h2>
            <form class="auth-form auth-form--grid" @submit.prevent="savePassword">
              <label class="field">
                <span class="field__label">Current password</span>
                <input v-model="currentPassword" class="field__input" type="password" autocomplete="current-password" required>
                <span v-if="firstFieldError(fieldErrors, 'current_password')" class="field__error" role="alert">{{ firstFieldError(fieldErrors, 'current_password') }}</span>
              </label>

              <label class="field">
                <span class="field__label">New password</span>
                <input v-model="newPassword" class="field__input" type="password" autocomplete="new-password" required>
                <span v-if="firstFieldError(fieldErrors, 'password')" class="field__error" role="alert">{{ firstFieldError(fieldErrors, 'password') }}</span>
              </label>

              <label class="field field--full">
                <span class="field__label">Confirm new password</span>
                <input v-model="newPasswordConfirmation" class="field__input" type="password" autocomplete="new-password" required>
                <span v-if="firstFieldError(fieldErrors, 'password_confirmation')" class="field__error" role="alert">{{ firstFieldError(fieldErrors, 'password_confirmation') }}</span>
              </label>

              <div class="auth-actions field--full">
                <button class="finder__button" type="submit" :disabled="savingPassword">{{ savingPassword ? 'Updating...' : 'Update password' }}</button>
              </div>
            </form>
          </article>
        </div>
        <div v-else class="stack">
          <p>You are not signed in.</p>
          <div class="auth-actions">
            <NuxtLink class="finder__button" to="/login">Sign in</NuxtLink>
            <NuxtLink class="pill" to="/register">Create account</NuxtLink>
          </div>
        </div>
      </template>
    </section>
  </div>
</template>
