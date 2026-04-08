<script setup lang="ts">
useHead({
  title: 'Cookie policy'
})
</script>

<template>
  <div class="landing">
    <section class="panel">
      <h1 class="hero__title">Cookies</h1>
      <p class="hero__lede">
        This site uses only essential cookies for sign-in and security.
      </p>
    </section>

    <section class="section">
      <div class="stack">
        <article class="card">
          <h2 style="margin-top: 0; font-size: 1rem;">Essential cookies</h2>
          <p>Session cookies keep you signed in and protect authenticated actions such as profile updates and account recovery.</p>
        </article>

        <article class="card">
          <h2 style="margin-top: 0; font-size: 1rem;">Security cookies</h2>
          <p>A CSRF token cookie verifies that requests to the API come from this site.</p>
        </article>

        <article class="card">
          <h2 style="margin-top: 0; font-size: 1rem;">No advertising cookies</h2>
          <p>We do not use advertising or analytics cookies. If that changes, we will update this page first.</p>
        </article>
      </div>
    </section>
  </div>
</template>
