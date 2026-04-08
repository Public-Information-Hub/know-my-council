<script setup lang="ts">
import { apiPost, formatApiError } from '~/lib/api'
import { extractFieldErrors, firstFieldError, hasFieldErrors, type FieldErrorMap } from '~/lib/form-errors'

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

async function submitResetPassword(): Promise<void> {
  notice.value = ''
  errorMessage.value = ''
  clearFormErrors()
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
    setFormErrors(error, 'Could not reset your password.')
  } finally {
    busy.value = false
  }
}
</script>

<template>
  <div class="landing auth-page">
    <section class="panel auth-card">
      <div class="auth-card__intro">
        <h1 class="hero__title">Set a new password</h1>
      </div>

      <div v-if="notice" class="callout callout--success" role="status" aria-live="polite">{{ notice }}</div>
      <div v-if="errorMessage" class="callout auth-card__error" role="alert">{{ errorMessage }}</div>

      <form class="auth-form" @submit.prevent="submitResetPassword">
        <label class="field">
          <span class="field__label">Email address</span>
          <input v-model="email" class="field__input" type="email" autocomplete="email" required>
          <span v-if="firstFieldError(fieldErrors, 'email')" class="field__error" role="alert">{{ firstFieldError(fieldErrors, 'email') }}</span>
        </label>

        <label class="field">
          <span class="field__label">New password</span>
          <input v-model="password" class="field__input" type="password" autocomplete="new-password" required>
          <span v-if="firstFieldError(fieldErrors, 'password')" class="field__error" role="alert">{{ firstFieldError(fieldErrors, 'password') }}</span>
        </label>

        <label class="field">
          <span class="field__label">Confirm new password</span>
          <input v-model="passwordConfirmation" class="field__input" type="password" autocomplete="new-password" required>
          <span v-if="firstFieldError(fieldErrors, 'password_confirmation')" class="field__error" role="alert">{{ firstFieldError(fieldErrors, 'password_confirmation') }}</span>
        </label>

        <div class="auth-actions">
          <button class="finder__button" type="submit" :disabled="busy">{{ busy ? 'Resetting...' : 'Reset password' }}</button>
          <NuxtLink to="/login">Back to sign in</NuxtLink>
        </div>
      </form>
    </section>
  </div>
</template>
