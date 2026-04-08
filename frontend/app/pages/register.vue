<script setup lang="ts">
import { apiPost, formatApiError } from '~/lib/api'
import { extractFieldErrors, firstFieldError, hasFieldErrors, isValidHandle, normaliseHandle, type FieldErrorMap } from '~/lib/form-errors'

type RegisterResponse = {
  message: string
  email_verification_required: boolean
}

useHead({
  title: 'Create account'
})

const router = useRouter()
const { refreshCurrentUser } = useCurrentUser()
const name = ref('')
const handle = ref('')
const email = ref('')
const publicBio = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const busy = ref(false)
const notice = ref('')
const errorMessage = ref('')
const fieldErrors = ref<FieldErrorMap>({})

function clearFormErrors(): void {
  fieldErrors.value = {}
}

function setFormErrors(error: unknown, fallback: string): boolean {
  const nextFieldErrors = extractFieldErrors(error)
  fieldErrors.value = nextFieldErrors

  if (hasFieldErrors(nextFieldErrors)) {
    errorMessage.value = 'Please correct the highlighted fields.'
    return true
  }

  errorMessage.value = formatApiError(error, fallback)
  return false
}

async function submitRegister(): Promise<void> {
  notice.value = ''
  errorMessage.value = ''
  clearFormErrors()
  busy.value = true

  handle.value = normaliseHandle(handle.value)

  if (handle.value && !isValidHandle(handle.value)) {
    fieldErrors.value = {
      handle: ['Use only letters, numbers, dots, hyphens, and underscores.']
    }
    errorMessage.value = 'Please correct the highlighted fields.'
    busy.value = false
    return
  }

  try {
    const response = await apiPost<RegisterResponse>('/auth/register', {
      name: name.value,
      handle: handle.value || null,
      email: email.value,
      public_bio: publicBio.value || null,
      password: password.value,
      password_confirmation: passwordConfirmation.value
    })

    notice.value = response.message
    await refreshCurrentUser()
    await router.push('/verify-email?registered=1')
  } catch (error: unknown) {
    setFormErrors(error, 'Could not create the account.')
  } finally {
    busy.value = false
  }
}
</script>

<template>
  <div class="landing auth-page">
    <section class="panel auth-card auth-card--wide">
      <div class="auth-card__intro">
        <h1 class="hero__title">Create an account</h1>
        <p class="hero__lede">
          Register to save your settings and access your profile.
        </p>
      </div>

      <div v-if="notice" class="callout" role="status" aria-live="polite">{{ notice }}</div>
      <div v-if="errorMessage" class="callout auth-card__error" role="alert">{{ errorMessage }}</div>

      <form class="auth-form auth-form--stacked" @submit.prevent="submitRegister">
        <label class="field">
          <span class="field__label">Full name</span>
          <input v-model="name" class="field__input" type="text" autocomplete="name" required>
          <span v-if="firstFieldError(fieldErrors, 'name')" class="field__error" role="alert">{{ firstFieldError(fieldErrors, 'name') }}</span>
        </label>

        <label class="field">
          <span class="field__label">Public handle <span class="muted" style="font-weight: 400;">(optional)</span></span>
          <input
            v-model="handle"
            class="field__input"
            type="text"
            autocomplete="username"
            inputmode="text"
            maxlength="32"
            placeholder="e.g. ada-lovelace"
            :aria-invalid="Boolean(firstFieldError(fieldErrors, 'handle'))"
            @blur="handle = handle ? normaliseHandle(handle) : ''"
          >
          <span v-if="firstFieldError(fieldErrors, 'handle')" class="field__error" role="alert">{{ firstFieldError(fieldErrors, 'handle') }}</span>
          <span class="field__hint">Letters, numbers, hyphens, underscores and dots. We recommend using a handle rather than your real name.</span>
        </label>

        <label class="field">
          <span class="field__label">Email address</span>
          <input v-model="email" class="field__input" type="email" autocomplete="email" required>
          <span v-if="firstFieldError(fieldErrors, 'email')" class="field__error" role="alert">{{ firstFieldError(fieldErrors, 'email') }}</span>
        </label>

        <label class="field">
          <span class="field__label">Public bio <span class="muted" style="font-weight: 400;">(optional)</span></span>
          <textarea v-model="publicBio" class="field__input field__input--textarea" rows="3" maxlength="280" placeholder="A short introduction, if you like."></textarea>
          <span v-if="firstFieldError(fieldErrors, 'public_bio')" class="field__error" role="alert">{{ firstFieldError(fieldErrors, 'public_bio') }}</span>
        </label>

        <label class="field">
          <span class="field__label">Password</span>
          <input v-model="password" class="field__input" type="password" autocomplete="new-password" required>
          <span v-if="firstFieldError(fieldErrors, 'password')" class="field__error" role="alert">{{ firstFieldError(fieldErrors, 'password') }}</span>
          <span class="field__hint">At least 12 characters with mixed case and numbers.</span>
        </label>

        <label class="field">
          <span class="field__label">Confirm password</span>
          <input v-model="passwordConfirmation" class="field__input" type="password" autocomplete="new-password" required>
          <span v-if="firstFieldError(fieldErrors, 'password_confirmation')" class="field__error" role="alert">{{ firstFieldError(fieldErrors, 'password_confirmation') }}</span>
        </label>

        <div class="auth-actions">
          <button class="finder__button" type="submit" :disabled="busy">{{ busy ? 'Creating...' : 'Create account' }}</button>
          <NuxtLink to="/login">Already have an account?</NuxtLink>
        </div>
      </form>
    </section>
  </div>
</template>
