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
    <section class="hero panel">
      <div>
        <p class="eyebrow">Council finder</p>
        <h1 class="hero__title">Find the right council quickly</h1>
        <p class="hero__lede">
          Search by postcode to get to the relevant council page. This is the first public-entry step for a much deeper council record and source trail.
        </p>

        <form class="finder" @submit.prevent="search">
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
              {{ busy ? 'Looking up…' : 'Find council' }}
            </button>
          </div>
          <p class="finder__help">We use the official GOV.UK local authority lookup for the initial council resolve.</p>
        </form>
      </div>

      <aside class="hero__panel">
        <UkMap />
      </aside>
    </section>

    <section class="section">
      <div v-if="error" class="callout" role="alert">
        <strong>Lookup issue:</strong>
        <span style="margin-left: 0.5rem;">{{ error }}</span>
      </div>

      <div v-else-if="directAuthority" class="panel">
        <p class="eyebrow" style="margin-bottom: 0.3rem;">Council found</p>
        <h2 class="section__heading" style="margin-top: 0;">{{ directAuthority.name }}</h2>
        <p class="section__lead">
          {{ directAuthority.tier ? `Tier: ${directAuthority.tier}.` : 'Council record returned by GOV.UK.' }}
        </p>

        <div class="card-grid card-grid--two">
          <div class="card">
            <h3>Slug</h3>
            <p>{{ directAuthority.slug }}</p>
          </div>
          <div class="card">
            <h3>Homepage</h3>
            <p v-if="directAuthority.homepage_url">
              <a :href="directAuthority.homepage_url" rel="noreferrer noopener" target="_blank">{{ directAuthority.homepage_url }}</a>
            </p>
            <p v-else>Not supplied by the source record.</p>
          </div>
        </div>

        <div class="row" style="margin-top: 1rem;">
          <NuxtLink class="pill" :to="`/councils/${directAuthority.slug}`">Open council page</NuxtLink>
          <NuxtLink class="pill" to="/">Back to home</NuxtLink>
        </div>
      </div>

      <div v-else-if="addressChoices.length" class="panel">
        <p class="eyebrow" style="margin-bottom: 0.3rem;">Multiple authorities</p>
        <h2 class="section__heading" style="margin-top: 0;">Choose the address that matches you</h2>
        <p class="section__lead">
          This postcode spans more than one authority, so pick the address that looks right.
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

      <div v-else class="card-grid card-grid--three">
        <article class="card">
          <h3>Why this page exists</h3>
          <p>People should be able to get from postcode or map to the relevant council quickly.</p>
        </article>
        <article class="card">
          <h3>What comes next</h3>
          <p>We will join this to the council registry, source imports and profile pages.</p>
        </article>
        <article class="card">
          <h3>How it will evolve</h3>
          <p>The finder will become richer as official council metadata is ingested and refreshed.</p>
        </article>
      </div>
    </section>
  </div>
</template>
