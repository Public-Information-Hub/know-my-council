export type ThemeName = 'light' | 'dark' | 'contrast'

const THEME_KEY = 'kmc-theme'
const THEMES: readonly ThemeName[] = ['light', 'dark', 'contrast']

export function useTheme() {
  const theme = useState<ThemeName>('kmc-theme', () => 'light')

  function setTheme(next: ThemeName) {
    theme.value = next
  }

  return {
    theme,
    themes: THEMES,
    setTheme,
    themeKey: THEME_KEY
  }
}
