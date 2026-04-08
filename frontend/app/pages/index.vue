<script setup lang="ts">
useHead({
  title: 'Find your council'
})
</script>

<template>
  <div class="landing">
    <section class="hero panel">
      <div>
        <h1 class="hero__title">Find information about your council</h1>
        <p class="hero__lede">
          Search by postcode to find spending, supplier and contract records for your local authority.
        </p>

        <div class="row" style="margin-top: 1rem;">
          <NuxtLink class="pill" to="/councils">Find your council</NuxtLink>
          <NuxtLink class="pill" to="/about">About this service</NuxtLink>
        </div>
      </div>

      <aside class="hero__panel" aria-label="UK map">
        <UkMap />
      </aside>
    </section>

    <section class="section">
      <h2 class="section__heading">What you can do</h2>

      <div class="card-grid card-grid--three">
        <article class="card">
          <h3>Look up a council</h3>
          <p>Enter a postcode to find which council covers your area and see their public record.</p>
        </article>
        <article class="card">
          <h3>Check spending data</h3>
          <p>See council spend, suppliers and contracts sourced from official publications.</p>
        </article>
        <article class="card">
          <h3>Trace the source</h3>
          <p>Every record links back to the data source so you can verify it yourself.</p>
        </article>
      </div>
    </section>

    <section id="accessibility" class="section">
      <div class="callout">
        <h2 class="section__heading" style="margin-top: 0;">Accessibility</h2>
        <p style="margin: 0;">
          This site supports keyboard navigation, screen readers, and three colour themes (light, dark and high contrast). Use the theme switcher above the page content.
        </p>
      </div>
    </section>

    <section class="section">
      <h2 class="section__heading">Where the data comes from</h2>
      <p class="section__lead">
        Records are sourced from official public bodies and linked back to the original publication.
      </p>

      <div class="source-strip" role="list" aria-label="Data sources">
        <article class="source-card" role="listitem">
          <div class="source-card__mark" aria-hidden="true">ONS</div>
          <div class="source-card__body">
            <h3 class="source-card__title">ONS Open Geography</h3>
            <p class="source-card__text">Council names, codes and boundaries.</p>
          </div>
        </article>
        <article class="source-card" role="listitem">
          <div class="source-card__mark" aria-hidden="true">GOV</div>
          <div class="source-card__body">
            <h3 class="source-card__title">GOV.UK API</h3>
            <p class="source-card__text">Postcode lookup and authority metadata.</p>
          </div>
        </article>
        <article class="source-card" role="listitem">
          <div class="source-card__mark" aria-hidden="true">DATA</div>
          <div class="source-card__body">
            <h3 class="source-card__title">data.gov.uk</h3>
            <p class="source-card__text">Official local government datasets.</p>
          </div>
        </article>
        <article class="source-card" role="listitem">
          <div class="source-card__mark" aria-hidden="true">CSV</div>
          <div class="source-card__body">
            <h3 class="source-card__title">Council publications</h3>
            <p class="source-card__text">Spend and contract exports from councils.</p>
          </div>
        </article>
      </div>
    </section>
  </div>
</template>
