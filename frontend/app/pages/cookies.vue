<script setup lang="ts">
useHead({
  title: 'Cookies'
})
</script>

<template>
  <div class="landing">
    <section class="panel">
      <p class="eyebrow">Policy</p>
      <h1 class="hero__title">Cookie policy</h1>
      <p class="hero__lede">
        We keep this simple: the site uses essential cookies and session storage needed for sign-in and security.
      </p>
    </section>

    <section class="panel">
      <div class="stack">
        <article class="card">
          <h2 class="section__heading" style="margin-top: 0;">Essential cookies</h2>
          <p>
            We use session cookies to keep you signed in and to protect authenticated actions such as profile updates, admin access, and account recovery.
          </p>
        </article>

        <article class="card">
          <h2 class="section__heading" style="margin-top: 0;">Security cookies</h2>
          <p>
            We use a CSRF token cookie so browser requests can be checked safely before they reach the API.
          </p>
        </article>

        <article class="card">
          <h2 class="section__heading" style="margin-top: 0;">Other cookies</h2>
          <p>
            We do not currently use advertising cookies. If that changes, we will document the change here before enabling it.
          </p>
        </article>
      </div>
    </section>
  </div>
</template>
