<script setup lang="ts">
import { lookupLocalAuthorityByPostcode } from '~/lib/govuk-local-authority'

type LocalAuthorityRecord = {
  name: string
  homepage_url?: string
  tier?: string
  slug: string
  parent?: LocalAuthorityRecord
}

const route = useRoute()
const router = useRouter()

const postcode = ref(typeof route.query.postcode === 'string' ? route.query.postcode : '')
const busy = ref(false)
const error = ref('')
const directAuthority = ref<LocalAuthorityRecord | null>(null)
const addressChoices = ref<Array<{ address: string; slug: string; name: string }>>([])

async function search() {
  busy.value = true
  error.value = ''
  directAuthority.value = null
  addressChoices.value = []

  const normalised = postcode.value.trim()
  if (!normalised) {
    error.value = 'Enter a postcode to find the relevant council.'
    busy.value = false
    return
  }

  try {
    const result = await lookupLocalAuthorityByPostcode(normalised)
    if ('local_authority' in result) {
      directAuthority.value = result.local_authority
    } else {
      addressChoices.value = result.addresses
    }
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'We could not look up that postcode.'
  } finally {
    busy.value = false
  }
}

async function goToCouncil(slug: string) {
  await router.push(`/councils/${slug}`)
}

onMounted(() => {
  if (postcode.value.trim()) {
    void search()
  }
})

useHead({
  title: 'Find your council'
})
</script>

<template>
  <div class="landing">
    <section class="panel">
      <h1 class="hero__title">Find your council</h1>
      <p class="hero__lede">
        Enter your postcode to find which council covers your area.
      </p>

      <form class="finder" role="search" aria-label="Council search" @submit.prevent="search">
        <label class="finder__label" for="postcode">Postcode</label>
        <div class="finder__row">
          <input
            id="postcode"
            v-model="postcode"
            class="finder__input"
            name="postcode"
            autocomplete="postal-code"
            inputmode="text"
            placeholder="e.g. SW1A 1AA"
          >
          <button class="finder__button" type="submit" :disabled="busy">
            {{ busy ? 'Searching...' : 'Search' }}
          </button>
        </div>
        <p class="finder__help">Uses the GOV.UK local authority register.</p>
      </form>
    </section>

    <section class="section" aria-live="polite">
      <div v-if="error" class="callout callout--error" role="alert">
        {{ error }}
      </div>

      <div v-else-if="directAuthority" class="panel">
        <h2 class="section__heading" style="margin-top: 0;">{{ directAuthority.name }}</h2>
        <p v-if="directAuthority.tier" class="subtle">{{ directAuthority.tier }}</p>

        <dl class="fact-grid" style="margin-top: 0.75rem;">
          <div v-if="directAuthority.homepage_url" class="fact-grid__item">
            <dt class="fact-grid__label">Website</dt>
            <dd class="fact-grid__value">
              <a :href="directAuthority.homepage_url" rel="noreferrer noopener" target="_blank">{{ directAuthority.homepage_url }}</a>
            </dd>
          </div>
        </dl>

        <div class="row" style="margin-top: 0.75rem;">
          <NuxtLink class="finder__button" :to="`/councils/${directAuthority.slug}`">View council page</NuxtLink>
        </div>
      </div>

      <div v-else-if="addressChoices.length" class="panel">
        <h2 class="section__heading" style="margin-top: 0;">Choose your address</h2>
        <p class="section__lead">
          This postcode covers more than one council area. Select the address that matches yours.
        </p>

        <div class="card-grid card-grid--two">
          <button
            v-for="choice in addressChoices"
            :key="choice.slug + choice.address"
            class="card card--button"
            type="button"
            @click="goToCouncil(choice.slug)"
          >
            <h3>{{ choice.name }}</h3>
            <p>{{ choice.address }}</p>
          </button>
        </div>
      </div>
    </section>
  </div>
</template>
