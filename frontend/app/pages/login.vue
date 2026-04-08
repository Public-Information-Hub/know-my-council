<script setup lang="ts">
import { apiPost, formatApiError } from '~/lib/api'
import { extractFieldErrors, firstFieldError, hasFieldErrors, type FieldErrorMap } from '~/lib/form-errors'

type Challenge = {
  id: string
  challenge_type: string
  delivery_mode: string
  expires_at: string | null
  last_sent_at: string | null
}

type LoginResponse = {
  message: string
  requires_two_factor: boolean
  challenge?: Challenge
}

useHead({
  title: 'Sign in'
})

const router = useRouter()
const route = useRoute()
const { refreshCurrentUser } = useCurrentUser()
const redirectTarget = computed(() => {
  const value = route.query.redirect
  if (typeof value !== 'string' || value.length === 0) {
    return '/profile?signed_in=1'
  }

  if (!value.startsWith('/') || value.startsWith('//')) {
    return '/profile?signed_in=1'
  }

  return value
})
const email = ref('')
const password = ref('')
const remember = ref(true)
const challengeId = ref('')
const challengeMode = ref<'email_code' | 'magic_link'>('email_code')
const challengeExpiresAt = ref('')
const challengeCode = ref('')
const busy = ref(false)
const challengeBusy = ref(false)
const notice = ref(route.query.signed_out ? 'You have been signed out.' : '')
const errorMessage = ref('')
const fieldErrors = ref<FieldErrorMap>({})

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

function clearMessages(): void {
  notice.value = ''
  errorMessage.value = ''
}

async function submitLogin(): Promise<void> {
  clearMessages()
  clearFormErrors()
  busy.value = true

  try {
    const response = await apiPost<LoginResponse>('/auth/login', {
      email: email.value,
      password: password.value,
      remember: remember.value
    })

    if (response.requires_two_factor && response.challenge) {
      challengeId.value = response.challenge.id
      challengeMode.value = response.challenge.delivery_mode === 'magic_link' ? 'magic_link' : 'email_code'
      challengeExpiresAt.value = response.challenge.expires_at ?? ''
      challengeCode.value = ''
      notice.value = 'Check your email for a verification code or magic link.'
      return
    }

    await refreshCurrentUser()
    await router.push(redirectTarget.value)
  } catch (error: unknown) {
    setFormErrors(error, 'Could not sign you in.')
  } finally {
    busy.value = false
  }
}

async function confirmChallenge(): Promise<void> {
  if (!challengeId.value) return

  clearMessages()
  clearFormErrors()
  challengeBusy.value = true

  try {
    await apiPost('/auth/two-factor/confirm', {
      challenge_id: challengeId.value,
      code: challengeCode.value
    })
    await refreshCurrentUser()
    await router.push(redirectTarget.value)
  } catch (error: unknown) {
    setFormErrors(error, 'Could not verify the code.')
  } finally {
    challengeBusy.value = false
  }
}

async function resendChallenge(): Promise<void> {
  if (!challengeId.value) return

  clearMessages()
  clearFormErrors()
  challengeBusy.value = true

  try {
    const response = await apiPost<{ message: string; challenge: Challenge }>('/auth/two-factor/resend', {
      challenge_id: challengeId.value,
      delivery_mode: challengeMode.value
    })
    challengeMode.value = response.challenge.delivery_mode === 'magic_link' ? 'magic_link' : 'email_code'
    challengeExpiresAt.value = response.challenge.expires_at ?? ''
    notice.value = response.message
  } catch (error: unknown) {
    setFormErrors(error, 'Could not resend the code.')
  } finally {
    challengeBusy.value = false
  }
}

async function switchChallengeMode(nextMode: 'email_code' | 'magic_link'): Promise<void> {
  if (!challengeId.value) return

  challengeMode.value = nextMode
  await resendChallenge()
}
</script>

<template>
  <div class="landing auth-page">
    <section class="panel auth-card">
      <div class="auth-card__intro">
        <h1 class="hero__title">Sign in</h1>
        <p class="hero__lede">
          Sign in to manage your profile and account settings.
        </p>
      </div>

      <div v-if="notice" class="callout" role="status" aria-live="polite">{{ notice }}</div>
      <div v-if="errorMessage" class="callout auth-card__error" role="alert">{{ errorMessage }}</div>

      <form class="auth-form" @submit.prevent="submitLogin">
        <label class="field">
          <span class="field__label">Email address</span>
          <input v-model="email" class="field__input" type="email" autocomplete="email" required>
          <span v-if="firstFieldError(fieldErrors, 'email')" class="field__error" role="alert">{{ firstFieldError(fieldErrors, 'email') }}</span>
        </label>

        <label class="field">
          <span class="field__label">Password</span>
          <input v-model="password" class="field__input" type="password" autocomplete="current-password" required>
          <span v-if="firstFieldError(fieldErrors, 'password')" class="field__error" role="alert">{{ firstFieldError(fieldErrors, 'password') }}</span>
        </label>

        <label class="field field--inline">
          <input v-model="remember" type="checkbox">
          <span>Keep me signed in</span>
        </label>

        <div class="auth-actions">
          <button class="finder__button" type="submit" :disabled="busy">{{ busy ? 'Signing in...' : 'Sign in' }}</button>
          <NuxtLink to="/forgot-password">Forgot password?</NuxtLink>
        </div>
      </form>
    </section>

    <section v-if="challengeId" class="panel auth-card" aria-live="polite">
      <div class="auth-card__intro">
        <h2 class="section__heading">Verify your sign-in</h2>
        <p class="section__lead">
          {{ challengeMode === 'magic_link' ? 'We sent a magic link to your email. You can also switch to a code.' : 'Enter the 6-digit code from your email.' }}
          <span v-if="challengeExpiresAt"> Expires {{ new Intl.DateTimeFormat('en-GB', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(challengeExpiresAt)) }}.</span>
        </p>
      </div>

      <form class="auth-form" @submit.prevent="confirmChallenge">
        <label class="field">
          <span class="field__label">Verification code</span>
          <input
            v-model="challengeCode"
            class="field__input"
            type="text"
            inputmode="numeric"
            autocomplete="one-time-code"
            maxlength="6"
            pattern="[0-9]{6}"
            :disabled="challengeMode === 'magic_link'"
            :placeholder="challengeMode === 'magic_link' ? 'Using magic link' : '6-digit code'"
          >
          <span v-if="firstFieldError(fieldErrors, 'code')" class="field__error" role="alert">{{ firstFieldError(fieldErrors, 'code') }}</span>
        </label>

        <div class="auth-actions">
          <button class="finder__button" type="submit" :disabled="challengeBusy || challengeMode === 'magic_link'">{{ challengeBusy ? 'Checking...' : 'Confirm' }}</button>
          <button class="pill" type="button" :disabled="challengeBusy" @click="resendChallenge">Resend</button>
          <button class="pill" type="button" :disabled="challengeBusy" @click="switchChallengeMode(challengeMode === 'magic_link' ? 'email_code' : 'magic_link')">
            {{ challengeMode === 'magic_link' ? 'Use code' : 'Use magic link' }}
          </button>
        </div>
      </form>
    </section>

    <p class="subtle" style="text-align: center;">
      No account? <NuxtLink to="/register">Create one</NuxtLink>
    </p>
  </div>
</template>
