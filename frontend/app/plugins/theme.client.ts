import type { ThemeName } from '~/composables/useTheme'

function applyTheme(theme: ThemeName) {
  const root = document.documentElement
  root.dataset.theme = theme
  root.style.colorScheme = theme === 'dark' || theme === 'contrast' ? 'dark' : 'light'
  localStorage.setItem('kmc-theme', theme)
}

export default defineNuxtPlugin(() => {
  const { theme } = useTheme()

  if (!import.meta.client) {
    return
  }

  if (!localStorage.getItem('kmc-theme')) {
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches
    theme.value = prefersDark ? 'dark' : 'light'
  } else {
    theme.value = (localStorage.getItem('kmc-theme') as ThemeName) || 'light'
  }

  watch(theme, (next) => {
    applyTheme(next)
  }, { immediate: true })
})
