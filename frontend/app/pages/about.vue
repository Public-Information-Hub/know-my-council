<script setup lang="ts">
useHead({
  title: 'About'
})
</script>

<template>
  <div class="landing">
    <section class="panel">
      <h1 class="hero__title">About KnowMyCouncil</h1>
      <p class="hero__lede">
        A public information service for UK councils and local authorities.
      </p>
    </section>

    <section class="section">
      <div class="card-grid card-grid--two">
        <article class="card">
          <h2 style="margin-top: 0; font-size: 1rem;">Source-led data</h2>
          <p>Every record links back to where it came from, when it was collected, and how it reached this site.</p>
        </article>

        <article class="card">
          <h2 style="margin-top: 0; font-size: 1rem;">UK-wide coverage</h2>
          <p>Covers councils across England, Scotland, Wales and Northern Ireland, with sources added in stages.</p>
        </article>

        <article class="card">
          <h2 style="margin-top: 0; font-size: 1rem;">Report errors</h2>
          <p>If you spot incorrect data, use the <NuxtLink to="/contact">contact page</NuxtLink> to let us know which record needs correcting.</p>
        </article>

        <article class="card">
          <h2 style="margin-top: 0; font-size: 1rem;">Open and free</h2>
          <p>This service is free to use. If you want to link to a council page or the homepage, go ahead.</p>
        </article>
      </div>
    </section>
  </div>
</template>
