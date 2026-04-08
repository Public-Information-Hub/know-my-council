<script setup lang="ts">
useHead({
  title: 'Privacy'
})
</script>

<template>
  <div class="landing">
    <section class="panel">
      <p class="eyebrow">Policy</p>
      <h1 class="hero__title">Privacy policy</h1>
      <p class="hero__lede">
        This page explains the personal data we process, why we process it, and how long we keep it.
      </p>
    </section>

    <section class="panel">
      <div class="stack">
        <article class="card">
          <h2 class="section__heading" style="margin-top: 0;">What we collect</h2>
          <ul class="list">
            <li>Account details such as name, handle, email address, and public bio.</li>
            <li>Session data needed to keep you signed in and protect the site.</li>
            <li>Operational logs that help us run the service and investigate faults.</li>
          </ul>
        </article>

        <article class="card">
          <h2 class="section__heading" style="margin-top: 0;">Why we collect it</h2>
          <ul class="list">
            <li>To create and manage accounts.</li>
            <li>To send verification, login, and password reset emails.</li>
            <li>To protect the admin area and keep the platform working.</li>
          </ul>
        </article>

        <article class="card">
          <h2 class="section__heading" style="margin-top: 0;">How we use it</h2>
          <ul class="list">
            <li>We use the minimum data needed for the service to operate.</li>
            <li>We do not use unnecessary advertising tracking on the site.</li>
            <li>We may keep operational records for debugging, security, and audit purposes.</li>
          </ul>
        </article>

        <article class="card">
          <h2 class="section__heading" style="margin-top: 0;">Third-party services</h2>
          <p>
            Some parts of the site may load external services such as map tiles or email delivery tools. When that happens, those providers may see limited technical data such as your IP address or email address needed to deliver the service.
          </p>
        </article>

        <article class="card">
          <h2 class="section__heading" style="margin-top: 0;">Contact</h2>
          <p>If you have a privacy question or want to request a correction, email <a href="mailto:hello@knowmycouncil.uk?subject=KnowMyCouncil%20privacy%20query">hello@knowmycouncil.uk</a>.</p>
        </article>
      </div>
    </section>
  </div>
</template>
