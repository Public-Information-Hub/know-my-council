<script setup lang="ts">
import { apiPost } from '~/lib/api'

const router = useRouter()
const { theme, themes, setTheme } = useTheme()
const { user, ensureCurrentUserLoaded, clearCurrentUser } = useCurrentUser()

await ensureCurrentUserLoaded()

async function signOut(): Promise<void> {
  try {
    await apiPost('/auth/logout')
  } finally {
    clearCurrentUser()
    await router.push('/login?signed_out=1')
  }
}
</script>

<template>
  <div class="site-shell">
    <a class="skip-link" href="#content">Skip to main content</a>

    <header class="site-header" role="banner">
      <div class="site-header__inner">
        <NuxtLink to="/" class="site-brand" aria-label="KnowMyCouncil home">
          <span class="site-brand__mark" aria-hidden="true">KMC</span>
          <span class="site-brand__name">KnowMyCouncil</span>
        </NuxtLink>

        <nav class="site-nav" aria-label="Main navigation">
          <NuxtLink to="/" class="site-nav__link">Home</NuxtLink>
          <NuxtLink to="/councils" class="site-nav__link">Councils</NuxtLink>
          <NuxtLink to="/about" class="site-nav__link">About</NuxtLink>
          <NuxtLink v-if="user?.is_super_admin" to="/admin" class="site-nav__link">Admin</NuxtLink>
        </nav>

        <div class="site-auth">
          <template v-if="user">
            <NuxtLink class="site-nav__link" to="/profile">Profile</NuxtLink>
            <button class="site-nav__link site-nav__link--button" type="button" @click="signOut">Sign out</button>
          </template>
          <template v-else>
            <NuxtLink class="site-nav__link" to="/login">Sign in</NuxtLink>
          </template>
        </div>
      </div>
    </header>

    <div class="site-bar">
      <div class="site-bar__inner">
        <div class="theme-switcher" role="group" aria-label="Colour theme">
          <button
            v-for="option in themes"
            :key="option"
            :class="['theme-switcher__button', { 'theme-switcher__button--active': theme === option }]"
            type="button"
            :aria-pressed="theme === option"
            @click="setTheme(option)"
          >
            {{ option === 'contrast' ? 'High contrast' : option.charAt(0).toUpperCase() + option.slice(1) }}
          </button>
        </div>
      </div>
    </div>

    <main id="content" class="site-main" role="main">
      <slot />
    </main>

    <footer class="site-footer" role="contentinfo">
      <div class="site-footer__inner">
        <div class="site-footer__grid">
          <div>
            <p class="site-footer__title">KnowMyCouncil</p>
            <p>Public information about UK councils and local authorities.</p>
          </div>

          <div class="site-footer__links">
            <div class="site-footer__group">
              <p class="site-footer__group-title">Explore</p>
              <NuxtLink to="/">Home</NuxtLink>
              <NuxtLink to="/councils">Councils</NuxtLink>
              <NuxtLink to="/about">About</NuxtLink>
              <NuxtLink to="/contact">Contact</NuxtLink>
            </div>

            <div class="site-footer__group">
              <p class="site-footer__group-title">Help</p>
              <NuxtLink to="/accessibility">Accessibility</NuxtLink>
              <NuxtLink to="/status">Service status</NuxtLink>
            </div>

            <div class="site-footer__group">
              <p class="site-footer__group-title">Legal</p>
              <NuxtLink to="/privacy">Privacy</NuxtLink>
              <NuxtLink to="/cookies">Cookies</NuxtLink>
              <NuxtLink to="/terms">Terms</NuxtLink>
            </div>
          </div>
        </div>

        <p class="site-footer__meta">
          KnowMyCouncil is not affiliated with or endorsed by HM Government.
        </p>
      </div>
    </footer>
  </div>
</template>
