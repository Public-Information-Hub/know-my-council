<script setup lang="ts">
import { apiGet, apiPost, formatApiError } from '~/lib/api'

type AuthUser = {
  id: number
  name: string
  handle: string | null
  email: string
  public_bio: string | null
  account_state: string
  verification_level: string
  trust_level: string
  two_factor_mode: string
  email_verified_at: string | null
  last_seen_at: string | null
  is_email_verified: boolean
}

type AuthResponse = {
  user: AuthUser
}

useHead({
  title: 'Verify email'
})

const route = useRoute()
const user = ref<AuthUser | null>(null)
const loading = ref(true)
const notice = ref('')
const errorMessage = ref('')
const sending = ref(false)

async function loadUser(): Promise<void> {
  loading.value = true
  try {
    const response = await apiGet<AuthResponse>('/auth/me')
    user.value = response.user
  } catch {
    user.value = null
  } finally {
    loading.value = false
  }
}

async function resendVerification(): Promise<void> {
  sending.value = true
  notice.value = ''
  errorMessage.value = ''

  try {
    const response = await apiPost<{ message: string }>('/auth/email-verification-notification')
    notice.value = response.message
    await loadUser()
  } catch (error: unknown) {
    errorMessage.value = formatApiError(error, 'Could not send the verification email.')
  } finally {
    sending.value = false
  }
}

onMounted(async () => {
  notice.value = route.query.verified ? 'Your email has been verified.' : route.query.registered ? 'Account created. Check your email to verify.' : ''
  await loadUser()
})
</script>

<template>
  <div class="landing auth-page">
    <section class="panel auth-card">
      <div class="auth-card__intro">
        <h1 class="hero__title">Verify your email</h1>
        <p class="hero__lede">We need to confirm your email address before you can use all features.</p>
      </div>

      <div v-if="notice" class="callout" role="status" aria-live="polite">{{ notice }}</div>
      <div v-if="errorMessage" class="callout auth-card__error" role="alert">{{ errorMessage }}</div>

      <div v-if="loading" class="callout">Loading...</div>
      <template v-else>
        <div v-if="user" class="stack">
          <dl class="fact-grid">
            <div class="fact-grid__item">
              <dt class="fact-grid__label">Email</dt>
              <dd class="fact-grid__value">{{ user.email }}</dd>
            </div>
            <div class="fact-grid__item">
              <dt class="fact-grid__label">Verified</dt>
              <dd class="fact-grid__value">{{ user.is_email_verified ? 'Yes' : 'Not yet' }}</dd>
            </div>
          </dl>

          <div class="auth-actions">
            <button class="finder__button" type="button" :disabled="sending || user.is_email_verified" @click="resendVerification">
              {{ sending ? 'Sending...' : 'Resend verification email' }}
            </button>
            <NuxtLink class="pill" to="/profile">Go to profile</NuxtLink>
          </div>
        </div>
        <div v-else class="stack">
          <p>Sign in or create an account to verify your email.</p>
          <div class="auth-actions">
            <NuxtLink class="finder__button" to="/login">Sign in</NuxtLink>
            <NuxtLink class="pill" to="/register">Create account</NuxtLink>
          </div>
        </div>
      </template>
    </section>
  </div>
</template>
