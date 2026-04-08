<script setup lang="ts">
useHead({
  title: 'About'
})
</script>

<template>
  <div class="landing">
    <section class="panel">
      <p class="eyebrow">About</p>
      <h1 class="hero__title">How KnowMyCouncil works</h1>
      <p class="hero__lede">
        The site is built around source-led council data, clear provenance, and a simple public interface.
      </p>
    </section>

    <section class="panel">
      <div class="card-grid card-grid--two">
        <article class="card">
          <h2 class="section__heading" style="margin-top: 0;">Source-led</h2>
          <p>
            We aim to show where information comes from, when it was pulled in, and how it was transformed into the public site.
          </p>
        </article>

        <article class="card">
          <h2 class="section__heading" style="margin-top: 0;">UK-wide</h2>
          <p>
            The site is framed for UK councils and local authorities from the start, even if individual sources are added in stages.
          </p>
        </article>

        <article class="card">
          <h2 class="section__heading" style="margin-top: 0;">Corrections</h2>
          <p>
            If you spot a source issue or want to correct a council page, use the contact page and tell us which record needs attention.
          </p>
        </article>

        <article class="card">
          <h2 class="section__heading" style="margin-top: 0;">Promotion</h2>
          <p>
            If you want to share the project, linking to the council pages or the homepage is the simplest option. If you need a badge, partnership note, or a clearer attribution line, contact us.
          </p>
        </article>
      </div>
    </section>
  </div>
</template>
