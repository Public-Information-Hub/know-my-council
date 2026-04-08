<script setup lang="ts">
const { theme, themes, setTheme } = useTheme()
</script>

<template>
  <div class="site-shell">
    <a class="skip-link" href="#content">Skip to content</a>

    <header class="site-banner" role="status" aria-live="polite">
      <div class="site-banner__inner">
        <strong>Site in progress.</strong>
        <span>KnowMyCouncil is being built and is not live yet.</span>
      </div>
    </header>

    <header class="site-header">
      <div class="site-brand">
        <NuxtLink to="/" class="site-brand__mark" aria-label="KnowMyCouncil home">KMC</NuxtLink>
        <div>
          <NuxtLink to="/" class="site-brand__name">KnowMyCouncil</NuxtLink>
          <p class="site-brand__tag">Public information for English councils</p>
        </div>
      </div>

      <nav class="site-nav" aria-label="Primary">
        <NuxtLink to="/" class="site-nav__link">Home</NuxtLink>
        <NuxtLink to="/status" class="site-nav__link">Status</NuxtLink>
        <NuxtLink to="/admin" class="site-nav__link">Admin</NuxtLink>
      </nav>

      <div class="theme-switcher" role="group" aria-label="Colour theme">
        <button
          v-for="option in themes"
          :key="option"
          :class="['theme-switcher__button', { 'theme-switcher__button--active': theme === option }]"
          type="button"
          :aria-pressed="theme === option"
          @click="setTheme(option)"
        >
          {{ option === 'contrast' ? 'Contrast' : option.charAt(0).toUpperCase() + option.slice(1) }}
        </button>
      </div>
    </header>

    <main id="content" class="site-main">
      <slot />
    </main>

    <footer class="site-footer">
      <p>
        KnowMyCouncil is a source-led civic information project focused on English councils.
      </p>
      <p class="site-footer__meta">
        Built for clear reading, keyboard use, and accessible presentation from the start.
      </p>
    </footer>
  </div>
</template>
