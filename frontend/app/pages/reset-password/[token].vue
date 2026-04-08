<script setup lang="ts">
import { apiPost, formatApiError } from '~/lib/api'

useHead({
  title: 'Reset password'
})

const route = useRoute()
const router = useRouter()
const token = computed(() => String(route.params.token || ''))
const email = ref(String(route.query.email || ''))
const password = ref('')
const passwordConfirmation = ref('')
const busy = ref(false)
const notice = ref('')
const errorMessage = ref('')

async function submitResetPassword(): Promise<void> {
  notice.value = ''
  errorMessage.value = ''
  busy.value = true

  try {
    const response = await apiPost<{ message: string }>('/auth/reset-password', {
      token: token.value,
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value
    })
    notice.value = response.message
    await router.push('/profile?password_reset=1')
  } catch (error: unknown) {
    errorMessage.value = formatApiError(error, 'We could not reset your password right now.')
  } finally {
    busy.value = false
  }
}
</script>

<template>
  <div class="landing auth-page">
    <section class="panel auth-card">
      <div class="auth-card__intro">
        <p class="eyebrow">Reset password</p>
        <h1 class="hero__title">Choose a new password</h1>
        <p class="hero__lede">Use the token from the email and set a new password for your account.</p>
      </div>

      <div v-if="notice" class="callout" role="status" aria-live="polite">{{ notice }}</div>
      <div v-if="errorMessage" class="callout auth-card__error" role="alert">{{ errorMessage }}</div>

      <form class="auth-form" @submit.prevent="submitResetPassword">
        <label class="field">
          <span class="field__label">Email address</span>
          <input v-model="email" class="field__input" type="email" autocomplete="email" required>
        </label>

        <label class="field">
          <span class="field__label">New password</span>
          <input v-model="password" class="field__input" type="password" autocomplete="new-password" required>
        </label>

        <label class="field">
          <span class="field__label">Confirm new password</span>
          <input v-model="passwordConfirmation" class="field__input" type="password" autocomplete="new-password" required>
        </label>

        <div class="auth-actions">
          <button class="finder__button" type="submit" :disabled="busy">{{ busy ? 'Resetting…' : 'Reset password' }}</button>
          <NuxtLink class="pill" to="/login">Back to sign in</NuxtLink>
        </div>
      </form>
    </section>
  </div>
</template>
