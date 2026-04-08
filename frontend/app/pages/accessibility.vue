<script setup lang="ts">
useHead({
  title: 'Accessibility'
})
</script>

<template>
  <div class="landing">
    <section class="panel">
      <h1 class="hero__title">Accessibility</h1>
      <p class="hero__lede">
        This site is designed to work with screen readers, keyboard navigation, and assistive technology.
      </p>
    </section>

    <section class="section">
      <div class="stack">
        <article class="card">
          <h2 style="margin-top: 0; font-size: 1rem;">What we support</h2>
          <ul class="list">
            <li>Keyboard navigation with a skip link and visible focus indicators.</li>
            <li>Three colour themes: light, dark and high contrast.</li>
            <li>Responsive layout for desktop, tablet and mobile screens.</li>
            <li>Semantic HTML and ARIA labels for screen readers.</li>
          </ul>
        </article>

        <article class="card">
          <h2 style="margin-top: 0; font-size: 1rem;">Known limitations</h2>
          <ul class="list">
            <li>The interactive map may not be fully accessible to all screen reader users. Text alternatives are provided.</li>
            <li>Some data tables may need additional navigation support at very large sizes.</li>
          </ul>
        </article>

        <article class="card">
          <h2 style="margin-top: 0; font-size: 1rem;">Report a problem</h2>
          <p>
            If you have difficulty using this site, please <NuxtLink to="/contact">contact us</NuxtLink> and describe what device, browser or assistive technology you were using.
          </p>
        </article>
      </div>
    </section>
  </div>
</template>
