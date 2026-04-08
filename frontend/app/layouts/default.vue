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
    <a class="skip-link" href="#content">Skip to content</a>

    <header class="site-header app-bar">
      <div class="site-brand">
        <NuxtLink to="/" class="site-brand__mark" aria-label="KnowMyCouncil home">KMC</NuxtLink>
        <div>
          <NuxtLink to="/" class="site-brand__name">KnowMyCouncil</NuxtLink>
          <p class="site-brand__tag">UK councils and local authorities</p>
        </div>
      </div>

      <nav class="site-nav" aria-label="Primary">
        <NuxtLink to="/" class="site-nav__link">Home</NuxtLink>
        <NuxtLink to="/councils" class="site-nav__link">Councils</NuxtLink>
        <NuxtLink to="/status" class="site-nav__link">Status</NuxtLink>
        <NuxtLink v-if="user?.is_super_admin" to="/admin" class="site-nav__link">Admin</NuxtLink>
      </nav>

      <div class="site-actions">
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

        <div class="site-auth">
          <template v-if="user">
            <span class="site-auth__status">Signed in</span>
            <NuxtLink class="site-nav__link" to="/profile">Profile</NuxtLink>
            <button class="site-nav__link site-nav__link--button" type="button" @click="signOut">Sign out</button>
          </template>
          <template v-else>
            <NuxtLink class="site-nav__link" to="/login">Sign in</NuxtLink>
            <NuxtLink class="site-nav__link" to="/register">Create account</NuxtLink>
          </template>
        </div>
      </div>
    </header>

    <main id="content" class="site-main">
      <slot />
    </main>

    <footer class="site-footer">
      <p>
        KnowMyCouncil is a source-led civic information project focused on UK councils and local authorities.
      </p>
      <p class="site-footer__meta">
        Built for clear reading, keyboard use, and accessible presentation from the start.
      </p>
    </footer>
  </div>
</template>
