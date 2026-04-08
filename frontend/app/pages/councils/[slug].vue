<script setup lang="ts">
import { lookupLocalAuthorityBySlug } from '~/lib/govuk-local-authority'

type LocalAuthorityRecord = {
  name: string
  homepage_url?: string
  tier?: string
  slug: string
  parent?: LocalAuthorityRecord
}

const route = useRoute()
const slug = computed(() => String(route.params.slug || '').trim())

const { data: authority, pending: loading, error } = await useAsyncData(
  () => `council:${slug.value}`,
  () => lookupLocalAuthorityBySlug(slug.value),
  { watch: [slug] }
)

useHead({
  title: () => (authority.value ? authority.value.name : slug.value || 'Council')
})
</script>

<template>
  <div class="landing">
    <section class="panel">
      <p class="eyebrow">Council</p>
      <h1 class="hero__title">{{ authority?.name || slug }}</h1>
      <p v-if="authority?.tier" class="hero__lede">{{ authority.tier }}</p>
    </section>

    <section class="section" aria-live="polite">
      <div v-if="loading" class="callout">
        Loading council record...
      </div>

      <div v-else-if="error" class="callout callout--error" role="alert">
        Could not load this council. {{ error?.message || '' }}
      </div>

      <template v-else-if="authority">
        <div class="card-grid card-grid--two">
          <article class="card">
            <h2 style="margin-top: 0; font-size: 1rem;">Details</h2>
            <dl class="fact-grid">
              <div class="fact-grid__item">
                <dt class="fact-grid__label">Name</dt>
                <dd class="fact-grid__value">{{ authority.name }}</dd>
              </div>
              <div v-if="authority.tier" class="fact-grid__item">
                <dt class="fact-grid__label">Type</dt>
                <dd class="fact-grid__value">{{ authority.tier }}</dd>
              </div>
              <div v-if="authority.homepage_url" class="fact-grid__item">
                <dt class="fact-grid__label">Website</dt>
                <dd class="fact-grid__value">
                  <a :href="authority.homepage_url" rel="noreferrer noopener" target="_blank">{{ authority.homepage_url }}</a>
                </dd>
              </div>
            </dl>
          </article>

          <article v-if="authority.parent" class="card">
            <h2 style="margin-top: 0; font-size: 1rem;">Parent authority</h2>
            <p>
              <NuxtLink :to="`/councils/${authority.parent.slug}`">{{ authority.parent.name }}</NuxtLink>
              <span v-if="authority.parent.tier" class="muted"> &middot; {{ authority.parent.tier }}</span>
            </p>
          </article>
        </div>

        <div class="row" style="margin-top: 1rem;">
          <NuxtLink class="pill" to="/councils">Search another postcode</NuxtLink>
          <NuxtLink class="pill" to="/contact">Report an error</NuxtLink>
        </div>
      </template>

      <div v-else class="callout">
        No council record available for this page yet.
      </div>
    </section>
  </div>
</template>
