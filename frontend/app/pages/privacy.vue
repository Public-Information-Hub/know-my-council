<script setup lang="ts">
useHead({
  title: 'Privacy policy'
})
</script>

<template>
  <div class="landing">
    <section class="panel">
      <h1 class="hero__title">Privacy policy</h1>
      <p class="hero__lede">
        How we handle personal data on this site.
      </p>
    </section>

    <section class="section">
      <div class="stack">
        <article class="card">
          <h2 style="margin-top: 0; font-size: 1rem;">What we collect</h2>
          <ul class="list">
            <li>Account details: name, handle, email address and public bio.</li>
            <li>Session data needed to keep you signed in.</li>
            <li>Operational logs for running the service and investigating faults.</li>
          </ul>
        </article>

        <article class="card">
          <h2 style="margin-top: 0; font-size: 1rem;">Why we collect it</h2>
          <ul class="list">
            <li>To create and manage your account.</li>
            <li>To send verification, sign-in and password reset emails.</li>
            <li>To keep the service running and secure.</li>
          </ul>
        </article>

        <article class="card">
          <h2 style="margin-top: 0; font-size: 1rem;">How we use it</h2>
          <ul class="list">
            <li>We collect the minimum data needed for the service.</li>
            <li>We do not use advertising trackers.</li>
            <li>We may keep operational records for security and audit purposes.</li>
          </ul>
        </article>

        <article class="card">
          <h2 style="margin-top: 0; font-size: 1rem;">Third-party services</h2>
          <p>
            The site may load external services such as map tiles or email delivery. Those providers may see limited technical data such as your IP address.
          </p>
        </article>

        <article class="card">
          <h2 style="margin-top: 0; font-size: 1rem;">Contact</h2>
          <p>For privacy queries, email <a href="mailto:hello@knowmycouncil.uk?subject=Privacy%20query">hello@knowmycouncil.uk</a>.</p>
        </article>
      </div>
    </section>
  </div>
</template>
