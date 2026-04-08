<script setup lang="ts">
import { apiPost, formatApiError } from '~/lib/api'

useHead({
  title: 'Forgot password'
})

const email = ref('')
const busy = ref(false)
const notice = ref('')
const errorMessage = ref('')

async function submitForgotPassword(): Promise<void> {
  notice.value = ''
  errorMessage.value = ''
  busy.value = true

  try {
    const response = await apiPost<{ message: string }>('/auth/forgot-password', {
      email: email.value
    })
    notice.value = response.message
  } catch (error: unknown) {
    errorMessage.value = formatApiError(error, 'We could not request a password reset right now.')
  } finally {
    busy.value = false
  }
}
</script>

<template>
  <div class="landing auth-page">
    <section class="panel auth-card">
      <div class="auth-card__intro">
        <p class="eyebrow">Password reset</p>
        <h1 class="hero__title">Send a reset link</h1>
        <p class="hero__lede">We will email a link to reset your password if the address exists in the system.</p>
      </div>

      <div v-if="notice" class="callout" role="status" aria-live="polite">{{ notice }}</div>
      <div v-if="errorMessage" class="callout auth-card__error" role="alert">{{ errorMessage }}</div>

      <form class="auth-form" @submit.prevent="submitForgotPassword">
        <label class="field">
          <span class="field__label">Email address</span>
          <input v-model="email" class="field__input" type="email" autocomplete="email" required>
        </label>

        <div class="auth-actions">
          <button class="finder__button" type="submit" :disabled="busy">{{ busy ? 'Sending…' : 'Send reset link' }}</button>
          <NuxtLink class="pill" to="/login">Back to sign in</NuxtLink>
        </div>
      </form>
    </section>
  </div>
</template>
