<script setup lang="ts">
import { apiPost, formatApiError } from '~/lib/api'
import { extractFieldErrors, firstFieldError, hasFieldErrors, type FieldErrorMap } from '~/lib/form-errors'

useHead({
  title: 'Forgot password'
})

const email = ref('')
const busy = ref(false)
const notice = ref('')
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

async function submitForgotPassword(): Promise<void> {
  notice.value = ''
  errorMessage.value = ''
  clearFormErrors()
  busy.value = true

  try {
    const response = await apiPost<{ message: string }>('/auth/forgot-password', {
      email: email.value
    })
    notice.value = response.message
  } catch (error: unknown) {
    setFormErrors(error, 'Could not send a reset link.')
  } finally {
    busy.value = false
  }
}
</script>

<template>
  <div class="landing auth-page">
    <section class="panel auth-card">
      <div class="auth-card__intro">
        <h1 class="hero__title">Reset your password</h1>
        <p class="hero__lede">Enter your email and we will send a reset link.</p>
      </div>

      <div v-if="notice" class="callout callout--success" role="status" aria-live="polite">{{ notice }}</div>
      <div v-if="errorMessage" class="callout auth-card__error" role="alert">{{ errorMessage }}</div>

      <form class="auth-form" @submit.prevent="submitForgotPassword">
        <label class="field">
          <span class="field__label">Email address</span>
          <input v-model="email" class="field__input" type="email" autocomplete="email" required>
          <span v-if="firstFieldError(fieldErrors, 'email')" class="field__error" role="alert">{{ firstFieldError(fieldErrors, 'email') }}</span>
        </label>

        <div class="auth-actions">
          <button class="finder__button" type="submit" :disabled="busy">{{ busy ? 'Sending...' : 'Send reset link' }}</button>
          <NuxtLink to="/login">Back to sign in</NuxtLink>
        </div>
      </form>
    </section>
  </div>
</template>
