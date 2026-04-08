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
    <section class="hero panel">
      <div>
        <p class="eyebrow">Council page</p>
        <h1 class="hero__title">{{ authority?.name || slug }}</h1>
        <p class="hero__lede">
          This page is the starting point for a council record: identity, source links, later data views, and the provenance needed to trust it.
        </p>
      </div>

      <aside class="hero__panel">
        <div class="callout">
          <p class="eyebrow" style="margin-bottom: 0.35rem;">Source record</p>
          <p style="margin: 0;">This page is powered by the GOV.UK local authority lookup while the fuller council registry is still being built.</p>
        </div>
      </aside>
    </section>

    <section class="section">
      <div v-if="loading" class="panel">
        Loading council record…
      </div>

      <div v-else-if="error" class="callout" role="alert">
        <strong>Could not load this council.</strong>
        <span style="margin-left: 0.5rem;">{{ error?.message || 'We could not load that council.' }}</span>
      </div>

      <div v-else-if="authority" class="card-grid card-grid--two">
        <article class="card">
          <h2 style="margin-top: 0;">Overview</h2>
          <p class="subtle">Public metadata returned by the GOV.UK lookup endpoint.</p>
          <dl class="fact-grid">
            <div class="fact-grid__item">
              <dt class="fact-grid__label">Name</dt>
              <dd class="fact-grid__value">{{ authority.name }}</dd>
            </div>
            <div class="fact-grid__item">
              <dt class="fact-grid__label">Slug</dt>
              <dd class="fact-grid__value">{{ authority.slug }}</dd>
            </div>
            <div class="fact-grid__item">
              <dt class="fact-grid__label">Tier</dt>
              <dd class="fact-grid__value">{{ authority.tier || 'Not supplied' }}</dd>
            </div>
          </dl>
        </article>

        <article class="card">
          <h2 style="margin-top: 0;">Links</h2>
          <p v-if="authority.homepage_url">
            <a :href="authority.homepage_url" rel="noreferrer noopener" target="_blank">{{ authority.homepage_url }}</a>
          </p>
          <p v-else class="muted">Homepage URL not supplied by the source.</p>

          <div v-if="authority.parent" class="callout" style="margin-top: 1rem;">
            <p class="eyebrow" style="margin-bottom: 0.35rem;">Parent authority</p>
            <p style="margin: 0;">
              {{ authority.parent.name }}
              <span v-if="authority.parent.tier">({{ authority.parent.tier }})</span>
            </p>
          </div>
        </article>
      </div>

      <div v-else class="panel">
        No council record is available yet.
      </div>
    </section>
  </div>
</template>
