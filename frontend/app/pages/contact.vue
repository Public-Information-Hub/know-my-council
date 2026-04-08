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
  {
    value: 'correction',
    title: 'Corrections',
    description: 'Use this for council page errors, source mismatches, or missing records.'
  },
  {
    value: 'accessibility',
    title: 'Accessibility',
    description: 'Use this for keyboard, contrast, screen reader, or layout issues.'
  },
  {
    value: 'data_source',
    title: 'Data sources',
    description: 'Use this for questions about how a council record was sourced or updated.'
  },
  {
    value: 'general',
    title: 'General',
    description: 'Use this when you are not sure which route is best.'
  }
] as const

const topic = ref<(typeof topics)[number]['value']>('correction')
const name = ref('')
const email = ref('')
const councilName = ref('')
const councilSlug = ref('')
const pageUrl = ref('')
const sourceUrl = ref('')
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
      council_slug: councilSlug.value || null,
      page_url: pageUrl.value || null,
      source_url: sourceUrl.value || null,
      details: details.value
    })

    notice.value = `${response.message} We have logged it as ${response.request.status}.`
    name.value = ''
    email.value = ''
    councilName.value = ''
    councilSlug.value = ''
    pageUrl.value = ''
    sourceUrl.value = ''
    details.value = ''
  } catch (error: unknown) {
    errorMessage.value = formatApiError(error, 'We could not send your request right now.')
  } finally {
    busy.value = false
  }
}
</script>

<template>
  <div class="landing">
    <section class="panel">
      <p class="eyebrow">Contact</p>
      <h1 class="hero__title">Send a correction or question</h1>
      <p class="hero__lede">
        The public form below logs requests so we can track them in admin, follow up on corrections, and keep a record of what changed.
      </p>
    </section>

    <section class="panel">
      <div class="card-grid card-grid--three">
        <article v-for="item in topics" :key="item.value" class="card">
          <h2 class="section__heading" style="margin-top: 0;">{{ item.title }}</h2>
          <p style="margin-bottom: 0;">{{ item.description }}</p>
        </article>
      </div>
    </section>

    <section class="panel">
      <div v-if="notice" class="callout" role="status" aria-live="polite">{{ notice }}</div>
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
          <span class="field__label">Council name</span>
          <input v-model="councilName" class="field__input" type="text" placeholder="Optional">
        </label>

        <label class="field">
          <span class="field__label">Council slug</span>
          <input v-model="councilSlug" class="field__input" type="text" placeholder="Optional">
        </label>

        <label class="field">
          <span class="field__label">Page URL</span>
          <input v-model="pageUrl" class="field__input" type="url" placeholder="Optional">
        </label>

        <label class="field">
          <span class="field__label">Source URL</span>
          <input v-model="sourceUrl" class="field__input" type="url" placeholder="Optional">
        </label>

        <label class="field field--full">
          <span class="field__label">What needs to change?</span>
          <textarea v-model="details" class="field__input field__input--textarea" rows="6" required placeholder="Tell us what you found and what should be corrected."></textarea>
          <span class="field__hint">Please include the page or council name if you can.</span>
        </label>

        <div class="auth-actions field--full">
          <button class="finder__button" type="submit" :disabled="busy">{{ busy ? 'Sending…' : 'Send request' }}</button>
          <a class="pill" href="mailto:hello@knowmycouncil.uk">Email instead</a>
        </div>
      </form>
    </section>
  </div>
</template>
