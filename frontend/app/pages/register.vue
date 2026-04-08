<script setup lang="ts">
import { apiPost, formatApiError } from '~/lib/api'

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

async function submitRegister(): Promise<void> {
  notice.value = ''
  errorMessage.value = ''
  busy.value = true

  try {
    const response = await apiPost<RegisterResponse>('/auth/register', {
      name: name.value,
      handle: handle.value,
      email: email.value,
      public_bio: publicBio.value || null,
      password: password.value,
      password_confirmation: passwordConfirmation.value
    })

    notice.value = response.message
    await refreshCurrentUser()
    await router.push('/verify-email?registered=1')
  } catch (error: unknown) {
    errorMessage.value = formatApiError(error, 'We could not create the account right now.')
  } finally {
    busy.value = false
  }
}
</script>

<template>
  <div class="landing auth-page">
    <section class="panel auth-card auth-card--wide">
      <div class="auth-card__intro">
        <p class="eyebrow">Create account</p>
        <h1 class="hero__title">Join KnowMyCouncil</h1>
        <p class="hero__lede">
          Register with a public handle, verify your email, and use the default email-based sign-in checks.
        </p>
      </div>

      <div v-if="notice" class="callout" role="status" aria-live="polite">{{ notice }}</div>
      <div v-if="errorMessage" class="callout auth-card__error" role="alert">{{ errorMessage }}</div>

      <form class="auth-form auth-form--stacked" @submit.prevent="submitRegister">
        <label class="field">
          <span class="field__label">Public name</span>
          <input v-model="name" class="field__input" type="text" autocomplete="name" required>
          <span class="field__hint">This is the name shown on your profile.</span>
        </label>

        <label class="field">
          <span class="field__label">Public handle</span>
          <input v-model="handle" class="field__input" type="text" autocomplete="username" required placeholder="example-user">
          <span class="field__hint">Letters, numbers, hyphens, underscores and dots are allowed.</span>
        </label>

        <label class="field field--full">
          <span class="field__label">Email address</span>
          <input v-model="email" class="field__input" type="email" autocomplete="email" required>
        </label>

        <label class="field field--full">
          <span class="field__label">Public bio</span>
          <textarea v-model="publicBio" class="field__input field__input--textarea" rows="4" maxlength="280" placeholder="A short public introduction, if you want one."></textarea>
          <span class="field__hint">Optional. Keep it short.</span>
        </label>

        <label class="field">
          <span class="field__label">Password</span>
          <input v-model="password" class="field__input" type="password" autocomplete="new-password" required>
          <span class="field__hint">Use at least 12 characters with mixed case and numbers.</span>
        </label>

        <label class="field">
          <span class="field__label">Confirm password</span>
          <input v-model="passwordConfirmation" class="field__input" type="password" autocomplete="new-password" required>
        </label>

        <div class="callout field--full">
          <strong>Email sign-in checks are on by default.</strong>
          <span style="display: block; margin-top: 0.25rem;">
            We will use an emailed code for sign-in checks, with magic-link login available when you need it.
          </span>
        </div>

        <div class="auth-actions field--full">
          <button class="finder__button" type="submit" :disabled="busy">{{ busy ? 'Creating…' : 'Create account' }}</button>
          <NuxtLink class="pill" to="/login">Already have an account?</NuxtLink>
        </div>
      </form>
    </section>
  </div>
</template>
