<script setup lang="ts">
import { apiPost, formatApiError } from '~/lib/api'

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
const email = ref('')
const password = ref('')
const remember = ref(true)
const challengeId = ref('')
const challengeMode = ref('off')
const challengeExpiresAt = ref('')
const challengeCode = ref('')
const busy = ref(false)
const challengeBusy = ref(false)
const notice = ref(route.query.signed_out ? 'You have been signed out.' : '')
const errorMessage = ref('')

function clearMessages(): void {
  notice.value = ''
  errorMessage.value = ''
}

async function submitLogin(): Promise<void> {
  clearMessages()
  busy.value = true

  try {
    const response = await apiPost<LoginResponse>('/auth/login', {
      email: email.value,
      password: password.value,
      remember: remember.value
    })

    if (response.requires_two_factor && response.challenge) {
      challengeId.value = response.challenge.id
      challengeMode.value = response.challenge.delivery_mode
      challengeExpiresAt.value = response.challenge.expires_at ?? ''
      challengeCode.value = ''
      notice.value = response.message
      return
    }

    await router.push('/profile?signed_in=1')
  } catch (error: unknown) {
    errorMessage.value = formatApiError(error, 'We could not sign you in right now.')
  } finally {
    busy.value = false
  }
}

async function confirmChallenge(): Promise<void> {
  if (!challengeId.value) return

  clearMessages()
  challengeBusy.value = true

  try {
    await apiPost('/auth/two-factor/confirm', {
      challenge_id: challengeId.value,
      code: challengeCode.value
    })
    await router.push('/profile?signed_in=1')
  } catch (error: unknown) {
    errorMessage.value = formatApiError(error, 'We could not confirm the sign-in check.')
  } finally {
    challengeBusy.value = false
  }
}

async function resendChallenge(): Promise<void> {
  if (!challengeId.value) return

  clearMessages()
  challengeBusy.value = true

  try {
    const response = await apiPost<{ message: string; challenge: Challenge }>('/auth/two-factor/resend', {
      challenge_id: challengeId.value
    })
    challengeMode.value = response.challenge.delivery_mode
    challengeExpiresAt.value = response.challenge.expires_at ?? ''
    notice.value = response.message
  } catch (error: unknown) {
    errorMessage.value = formatApiError(error, 'We could not resend the sign-in check.')
  } finally {
    challengeBusy.value = false
  }
}
</script>

<template>
  <div class="landing auth-page">
    <section class="panel auth-card">
      <div class="auth-card__intro">
        <p class="eyebrow">Sign in</p>
        <h1 class="hero__title">Welcome back</h1>
        <p class="hero__lede">
          Sign in to manage your profile, email verification, and account settings.
        </p>
      </div>

      <div v-if="notice" class="callout" role="status" aria-live="polite">{{ notice }}</div>
      <div v-if="errorMessage" class="callout auth-card__error" role="alert">{{ errorMessage }}</div>

      <form class="auth-form" @submit.prevent="submitLogin">
        <label class="field">
          <span class="field__label">Email address</span>
          <input v-model="email" class="field__input" type="email" autocomplete="email" required>
        </label>

        <label class="field">
          <span class="field__label">Password</span>
          <input v-model="password" class="field__input" type="password" autocomplete="current-password" required>
        </label>

        <label class="field field--inline">
          <input v-model="remember" type="checkbox">
          <span>Keep me signed in</span>
        </label>

        <div class="auth-actions">
          <button class="finder__button" type="submit" :disabled="busy">{{ busy ? 'Signing in…' : 'Sign in' }}</button>
          <NuxtLink class="pill" to="/forgot-password">Forgot password?</NuxtLink>
        </div>
      </form>
    </section>

    <section v-if="challengeId" class="panel auth-card">
      <div class="auth-card__intro">
        <p class="eyebrow">Extra check</p>
        <h2 class="section__heading">Complete the email check</h2>
        <p class="section__lead">
          {{ challengeMode === 'magic_link' ? 'We sent a magic link to your email address.' : 'Enter the code from your email to finish signing in.' }}
          <span v-if="challengeExpiresAt">This check expires at {{ new Intl.DateTimeFormat('en-GB', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(challengeExpiresAt)) }}.</span>
        </p>
      </div>

      <form class="auth-form" @submit.prevent="confirmChallenge">
        <label class="field">
          <span class="field__label">Verification code</span>
          <input v-model="challengeCode" class="field__input" type="text" inputmode="numeric" autocomplete="one-time-code" :disabled="challengeMode === 'magic_link'" :placeholder="challengeMode === 'magic_link' ? 'Use the magic link in your email' : 'Enter the 6-digit code'">
        </label>

        <div class="auth-actions">
          <button class="finder__button" type="submit" :disabled="challengeBusy || challengeMode === 'magic_link'">{{ challengeBusy ? 'Checking…' : 'Confirm sign in' }}</button>
          <button class="pill" type="button" :disabled="challengeBusy" @click="resendChallenge">Resend check</button>
        </div>
      </form>
    </section>

    <section class="panel">
      <p class="section__lead" style="margin-bottom: 0;">
        New here? <NuxtLink to="/register">Create an account</NuxtLink> to save your settings and access the profile area.
      </p>
    </section>
  </div>
</template>
