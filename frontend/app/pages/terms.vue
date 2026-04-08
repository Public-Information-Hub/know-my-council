<script setup lang="ts">
useHead({
  title: 'Terms'
})
</script>

<template>
  <div class="landing">
    <section class="panel">
      <p class="eyebrow">Policy</p>
      <h1 class="hero__title">Terms and conditions</h1>
      <p class="hero__lede">
        These terms set out how the site should be used and what we do and do not promise about the data.
      </p>
    </section>

    <section class="panel">
      <div class="stack">
        <article class="card">
          <h2 class="section__heading" style="margin-top: 0;">Use of the site</h2>
          <ul class="list">
            <li>Do not attempt to disrupt the service, scrape it aggressively, or use it unlawfully.</li>
            <li>Keep account details accurate and update them if they change.</li>
            <li>Use the public information respectfully and with proper context.</li>
          </ul>
        </article>

        <article class="card">
          <h2 class="section__heading" style="margin-top: 0;">Data and accuracy</h2>
          <ul class="list">
            <li>We work from source material and make the provenance visible where we can.</li>
            <li>We cannot guarantee every source is complete, current, or error free.</li>
            <li>Where data is unclear or conflicting, we will flag that rather than hide it.</li>
          </ul>
        </article>

        <article class="card">
          <h2 class="section__heading" style="margin-top: 0;">No legal advice</h2>
          <p>
            The site provides public information. It is not legal advice, and it should not be treated as a substitute for checking the underlying source documents.
          </p>
        </article>
      </div>
    </section>
  </div>
</template>
