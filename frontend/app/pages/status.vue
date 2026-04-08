<script setup lang="ts">
import { apiGet } from '~/lib/api'

useHead({
  title: 'Service status'
})

const health = await apiGet('/health')
const version = await apiGet('/version')
</script>

<template>
  <div class="landing">
    <section class="panel">
      <h1 class="hero__title">Service status</h1>
      <p class="hero__lede">Current health and version of the KnowMyCouncil API.</p>
    </section>

    <section class="section">
      <div class="stack">
        <article class="card">
          <h2 style="margin-top: 0; font-size: 1rem;">API health</h2>
          <pre>{{ health }}</pre>
        </article>

        <article class="card">
          <h2 style="margin-top: 0; font-size: 1rem;">API version</h2>
          <pre>{{ version }}</pre>
        </article>
      </div>
    </section>
  </div>
</template>
