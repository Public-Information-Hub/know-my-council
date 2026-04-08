<script setup lang="ts">
useHead({
  title: 'Accessibility'
})
</script>

<template>
  <div class="landing">
    <section class="panel">
      <p class="eyebrow">Accessibility</p>
      <h1 class="hero__title">Accessibility statement</h1>
      <p class="hero__lede">
        We are building the site to work well with keyboard navigation, screen readers, larger text, and high-contrast presentation.
      </p>
    </section>

    <section class="panel">
      <div class="stack">
        <article class="card">
          <h2 class="section__heading" style="margin-top: 0;">What we already support</h2>
          <ul class="list">
            <li>Keyboard navigation with a skip link and visible focus states.</li>
            <li>Light, dark, and high-contrast themes.</li>
            <li>Responsive layouts for desktop, tablet, and mobile.</li>
          </ul>
        </article>

        <article class="card">
          <h2 class="section__heading" style="margin-top: 0;">What we are watching</h2>
          <ul class="list">
            <li>Whether embedded maps and charts remain readable at all sizes.</li>
            <li>Whether forms stay clear when browser zoom is increased.</li>
            <li>Whether source tables need more table-specific navigation support.</li>
          </ul>
        </article>

        <article class="card">
          <h2 class="section__heading" style="margin-top: 0;">Tell us about a problem</h2>
          <p>
            If you find an accessibility issue, please use the contact page and tell us what device, browser, or assistive technology you were using.
          </p>
        </article>
      </div>
    </section>
  </div>
</template>
