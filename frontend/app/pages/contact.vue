<script setup lang="ts">
import { apiPost, formatApiError } from '~/lib/api'

type ContactRequestResponse = {
  message: string
  request: {
    id: string
    status: string
  }
}

useHead({
  title: 'Contact'
})

const topics = [
  { value: 'correction', title: 'Data correction' },
  { value: 'accessibility', title: 'Accessibility issue' },
  { value: 'data_source', title: 'Data source query' },
  { value: 'general', title: 'General enquiry' }
] as const

const topic = ref<(typeof topics)[number]['value']>('correction')
const name = ref('')
const email = ref('')
const councilName = ref('')
const pageUrl = ref('')
const details = ref('')
const busy = ref(false)
const notice = ref('')
const errorMessage = ref('')

async function submitContact(): Promise<void> {
  notice.value = ''
  errorMessage.value = ''
  busy.value = true

  try {
    const response = await apiPost<ContactRequestResponse>('/contact/correction-request', {
      topic: topic.value,
      name: name.value,
      email: email.value,
      council_name: councilName.value || null,
      council_slug: null,
      page_url: pageUrl.value || null,
      source_url: null,
      details: details.value
    })

    notice.value = response.message
    name.value = ''
    email.value = ''
    councilName.value = ''
    pageUrl.value = ''
    details.value = ''
  } catch (error: unknown) {
    errorMessage.value = formatApiError(error, 'Could not send your request.')
  } finally {
    busy.value = false
  }
}
</script>

<template>
  <div class="landing">
    <section class="panel">
      <h1 class="hero__title">Contact us</h1>
      <p class="hero__lede">
        Report a data error, ask about a council record, or raise an accessibility issue.
      </p>
    </section>

    <section class="panel">
      <div v-if="notice" class="callout callout--success" role="status" aria-live="polite">{{ notice }}</div>
      <div v-if="errorMessage" class="callout auth-card__error" role="alert">{{ errorMessage }}</div>

      <form class="auth-form auth-form--stacked" @submit.prevent="submitContact">
        <label class="field">
          <span class="field__label">What is this about?</span>
          <select v-model="topic" class="field__input" required>
            <option v-for="item in topics" :key="item.value" :value="item.value">{{ item.title }}</option>
          </select>
        </label>

        <label class="field">
          <span class="field__label">Your name</span>
          <input v-model="name" class="field__input" type="text" autocomplete="name" required>
        </label>

        <label class="field">
          <span class="field__label">Your email</span>
          <input v-model="email" class="field__input" type="email" autocomplete="email" required>
        </label>

        <label class="field">
          <span class="field__label">Council name <span class="muted" style="font-weight: 400;">(optional)</span></span>
          <input v-model="councilName" class="field__input" type="text" placeholder="e.g. Manchester City Council">
        </label>

        <label class="field">
          <span class="field__label">Page URL <span class="muted" style="font-weight: 400;">(optional)</span></span>
          <input v-model="pageUrl" class="field__input" type="url" placeholder="Link to the page with the issue">
          <span class="field__hint">If you noticed an error on a specific page, paste the address here.</span>
        </label>

        <label class="field">
          <span class="field__label">Details</span>
          <textarea v-model="details" class="field__input field__input--textarea" rows="5" required placeholder="Describe what you found and what should be different."></textarea>
        </label>

        <div class="auth-actions">
          <button class="finder__button" type="submit" :disabled="busy">{{ busy ? 'Sending...' : 'Send' }}</button>
          <a href="mailto:hello@knowmycouncil.uk">Or email us directly</a>
        </div>
      </form>
    </section>
  </div>
</template>
