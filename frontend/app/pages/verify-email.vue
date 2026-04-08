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
    errorMessage.value = formatApiError(error, 'We could not send another verification email.')
  } finally {
    sending.value = false
  }
}

onMounted(async () => {
  notice.value = route.query.verified ? 'Your email address has been verified.' : route.query.registered ? 'Your account has been created. Please check your email.' : ''
  await loadUser()
})
</script>

<template>
  <div class="landing auth-page">
    <section class="panel auth-card">
      <div class="auth-card__intro">
        <p class="eyebrow">Email verification</p>
        <h1 class="hero__title">Confirm your email address</h1>
        <p class="hero__lede">Email verification helps us keep accounts accountable and reduces spam and abuse.</p>
      </div>

      <div v-if="notice" class="callout" role="status" aria-live="polite">{{ notice }}</div>
      <div v-if="errorMessage" class="callout auth-card__error" role="alert">{{ errorMessage }}</div>

      <div v-if="loading" class="callout">Loading your account status…</div>
      <template v-else>
        <div v-if="user" class="stack">
          <article class="card">
            <h2 class="section__heading" style="margin-top: 0;">{{ user.name }}</h2>
            <p class="subtle" style="margin-bottom: 0;">{{ user.email }}</p>
          </article>

          <article class="card">
            <h3 style="margin-top: 0;">Verification status</h3>
            <p>Email verified: {{ user.is_email_verified ? 'Yes' : 'Not yet' }}</p>
            <p>Account state: {{ user.account_state }}</p>
            <p>Verification level: {{ user.verification_level }}</p>
            <p style="margin-bottom: 0;">Trust level: {{ user.trust_level }}</p>
          </article>

          <div class="auth-actions">
            <button class="finder__button" type="button" :disabled="sending || user.is_email_verified" @click="resendVerification">
              {{ sending ? 'Sending…' : 'Send verification email again' }}
            </button>
            <NuxtLink class="pill" to="/profile">Open profile</NuxtLink>
          </div>
        </div>
        <div v-else class="stack">
          <p class="section__lead">Sign in or create an account first so we can show your verification status.</p>
          <div class="auth-actions">
            <NuxtLink class="pill" to="/login">Sign in</NuxtLink>
            <NuxtLink class="pill" to="/register">Create account</NuxtLink>
          </div>
        </div>
      </template>
    </section>
  </div>
</template>
