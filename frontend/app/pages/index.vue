<script setup lang="ts">
const ingestionSources = [
  {
    mark: 'ONS',
    name: 'ONS Open Geography Portal',
    description: 'Council names, codes and geography products for the core registry.',
    href: 'https://geoportal.statistics.gov.uk/'
  },
  {
    mark: 'GOV.UK',
    name: 'GOV.UK Local Authorities API',
    description: 'Postcode lookup and basic local authority metadata.',
    href: 'https://docs.publishing.service.gov.uk/repos/frontend/local-authorities-api.html'
  },
  {
    mark: 'CKAN',
    name: 'data.gov.uk catalogue',
    description: 'Discovery path for official local government datasets and releases.',
    href: 'https://www.data.gov.uk/'
  },
  {
    mark: 'CSV',
    name: 'Council publication pages',
    description: 'Spend and contract exports published directly by councils.',
    href: '/councils'
  }
] as const
</script>

<template>
  <div class="landing">
    <section class="hero panel">
      <div>
        <p class="eyebrow">Public information site</p>
        <h1 class="hero__title">Clear, source-led information about UK councils</h1>
        <p class="hero__lede">
          KnowMyCouncil is being built to help people find council spend, suppliers, contracts and source material across the UK in one place, with accessibility and provenance at the centre.
        </p>

        <div class="row" style="margin-top: 1rem;">
          <NuxtLink class="pill" to="/status">View status</NuxtLink>
          <a class="pill" href="#what-this-is">What this is</a>
          <a class="pill" href="#accessibility">Accessibility</a>
        </div>
      </div>

      <aside class="hero__panel" aria-label="At a glance">
        <UkMap />

        <dl class="fact-grid" aria-label="Project facts">
          <div class="fact-grid__item">
            <dt class="fact-grid__label">Primary purpose</dt>
            <dd class="fact-grid__value">Make council information easier to inspect, compare and reuse.</dd>
          </div>
          <div class="fact-grid__item">
            <dt class="fact-grid__label">Current focus</dt>
            <dd class="fact-grid__value">Build the UK-wide data model, ingestion routes and public views together.</dd>
          </div>
          <div class="fact-grid__item">
            <dt class="fact-grid__label">Theme support</dt>
            <dd class="fact-grid__value">Light, dark and high contrast are available in the header.</dd>
          </div>
        </dl>
      </aside>
    </section>

    <section class="section">
      <div class="callout">
        <h2 class="section__heading" style="margin-top: 0;">Start with a postcode</h2>
        <p class="section__lead" style="margin-bottom: 0;">
          The map is the fastest route in, but postcode lookup will take people directly to the relevant council page once they are ready to search.
        </p>
        <div class="row" style="margin-top: 0.9rem;">
          <NuxtLink class="pill" to="/councils">Find your council</NuxtLink>
          <a class="pill" href="#accessibility">Accessibility first</a>
        </div>
      </div>
    </section>

    <nav class="section" aria-label="On this page">
      <div class="row">
        <a class="pill" href="#what-this-is">What this is</a>
        <a class="pill" href="#what-you-will-find">What you’ll find</a>
        <a class="pill" href="#accessibility">Accessibility</a>
        <a class="pill" href="#status">Status</a>
      </div>
    </nav>

    <section id="what-this-is" class="section">
      <h2 class="section__heading">What this site is for</h2>
      <p class="section__lead">
        The site is being shaped as a public information service first: calm, fast to scan, and structured so the important details are obvious.
      </p>

      <div class="card-grid card-grid--three">
        <article class="card">
          <h3>Public record, not a brochure</h3>
          <p>We want a practical site that shows where the information came from and what it means.</p>
        </article>
        <article class="card">
          <h3>Readable at a glance</h3>
          <p>Key context should be visible immediately, with deeper detail only when someone needs it.</p>
        </article>
        <article class="card">
          <h3>Built for councils and citizens</h3>
          <p>Every page should work for people checking a single line item or exploring a broader UK pattern.</p>
        </article>
      </div>
    </section>

    <section id="what-you-will-find" class="section">
      <h2 class="section__heading">What you’ll find here</h2>
      <p class="section__lead">
        The initial public pages are being designed around the data we can source and verify, not around decorative content.
      </p>

      <div class="card-grid card-grid--two">
        <article class="card">
          <h3>Core public views</h3>
          <ul class="list">
            <li>Council summaries and source links</li>
            <li>Spend, suppliers and contract records</li>
            <li>Import history and provenance trails</li>
          </ul>
        </article>
        <article class="card">
          <h3>Operational pages</h3>
          <ul class="list">
            <li>Build and ingestion status</li>
            <li>Source registration and refresh status</li>
            <li>Admin paths for maintenance work</li>
          </ul>
        </article>
      </div>
    </section>

    <section id="accessibility" class="section">
      <div class="panel">
        <h2 class="section__heading">Accessibility comes first</h2>
        <p class="section__lead">
          The interface is kept small, contrast-aware and keyboard-friendly. Theme choices are available right in the header.
        </p>

        <div class="card-grid card-grid--three">
          <article class="card">
            <h3>Colour modes</h3>
            <p>Light, dark and high contrast are supported without changing the layout.</p>
          </article>
          <article class="card">
            <h3>Keyboard support</h3>
            <p>Skip links, visible focus states and simple navigation are built in.</p>
          </article>
          <article class="card">
            <h3>Responsive by default</h3>
            <p>The layout expands on desktop and progressively simplifies on smaller screens.</p>
          </article>
        </div>
      </div>
    </section>

    <section id="status" class="section">
      <div class="callout">
        <h2 class="section__heading" style="margin-top: 0;">Current status</h2>
        <p class="section__lead">
          The site is protected while it is being built. The backend, ingestion work and public pages are still evolving.
        </p>
        <div class="row">
          <NuxtLink class="pill" to="/status">Open build status</NuxtLink>
          <NuxtLink class="pill" to="/admin">Admin area</NuxtLink>
        </div>
      </div>
    </section>

    <section id="ingestion-sources" class="section">
      <div class="panel">
        <h2 class="section__heading" style="margin-top: 0;">Ingestion points</h2>
        <p class="section__lead">
          These are the public sources we can already use or are wiring up first. The marks below are simple source badges so the section stays readable in all themes and screen sizes.
        </p>

        <div class="source-strip" aria-label="Ingestion sources">
          <article v-for="source in ingestionSources" :key="source.name" class="source-card">
            <div class="source-card__mark" aria-hidden="true">{{ source.mark }}</div>
            <div class="source-card__body">
              <h3 class="source-card__title">
                <a :href="source.href" :target="source.href.startsWith('http') ? '_blank' : undefined" :rel="source.href.startsWith('http') ? 'noreferrer noopener' : undefined">
                  {{ source.name }}
                </a>
              </h3>
              <p class="source-card__text">{{ source.description }}</p>
            </div>
          </article>
        </div>
      </div>
    </section>
  </div>
</template>
