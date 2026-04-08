<script setup lang="ts">
type NationKey = 'england' | 'scotland' | 'wales' | 'northern-ireland'

type NationMapEntry = {
  key: NationKey
  label: string
  href: string
  description: string
  x: number
  y: number
  width: number
  height: number
  accent: string
}

const regions: NationMapEntry[] = [
  {
    key: 'scotland',
    label: 'Scotland',
    href: '/councils?nation=scotland',
    description: 'Scotland is not in the first release scope.',
    x: 140,
    y: 28,
    width: 120,
    height: 92,
    accent: 'var(--kmc-panel-soft)'
  },
  {
    key: 'northern-ireland',
    label: 'Northern Ireland',
    href: '/councils?nation=northern-ireland',
    description: 'Northern Ireland is not in the first release scope.',
    x: 34,
    y: 182,
    width: 84,
    height: 64,
    accent: 'color-mix(in srgb, var(--kmc-accent-soft) 58%, var(--kmc-panel))'
  },
  {
    key: 'wales',
    label: 'Wales',
    href: '/councils?nation=wales',
    description: 'Wales is not in the first release scope.',
    x: 96,
    y: 190,
    width: 72,
    height: 120,
    accent: 'color-mix(in srgb, var(--kmc-accent-soft) 42%, var(--kmc-panel))'
  },
  {
    key: 'england',
    label: 'England',
    href: '/councils',
    description: 'England councils are the first release focus.',
    x: 174,
    y: 150,
    width: 150,
    height: 188,
    accent: 'var(--kmc-accent-soft)'
  }
]
</script>

<template>
  <section class="map-card" aria-labelledby="uk-map-title">
    <div class="map-card__header">
      <div>
        <p class="eyebrow" style="margin-bottom: 0.35rem;">UK map</p>
        <h2 id="uk-map-title" class="section__heading" style="margin: 0;">Find your council</h2>
      </div>
      <p class="subtle" style="margin: 0;">
        England is the current focus; the map remains set up for UK-wide expansion.
      </p>
    </div>

    <svg class="uk-map" viewBox="0 0 420 420" role="img" aria-labelledby="uk-map-svg-title uk-map-svg-desc">
      <title id="uk-map-svg-title">Interactive map of the United Kingdom</title>
      <desc id="uk-map-svg-desc">
        Select a nation to continue to the council finder or the future council pages.
      </desc>

      <rect x="12" y="12" width="396" height="396" rx="28" fill="var(--kmc-panel)" stroke="var(--kmc-border)" />

      <g v-for="region in regions" :key="region.key">
        <a
          class="uk-map__link"
          :href="region.href"
          :aria-label="`${region.label}. ${region.description}`"
        >
          <rect
            :x="region.x"
            :y="region.y"
            :width="region.width"
            :height="region.height"
            rx="22"
            :fill="region.accent"
            stroke="var(--kmc-border)"
            stroke-width="2"
          />
          <text
            :x="region.x + region.width / 2"
            :y="region.y + region.height / 2 - 4"
            text-anchor="middle"
            fill="var(--kmc-text)"
            font-size="20"
            font-weight="700"
          >
            {{ region.label }}
          </text>
          <text
            :x="region.x + region.width / 2"
            :y="region.y + region.height / 2 + 18"
            text-anchor="middle"
            fill="var(--kmc-muted)"
            font-size="11"
          >
            Tap to open
          </text>
        </a>
      </g>

      <circle cx="255" cy="102" r="9" fill="var(--kmc-accent)" opacity="0.9" />
      <circle cx="252" cy="100" r="3" fill="var(--kmc-panel)" />
    </svg>

    <div class="map-card__links" aria-label="Map links">
      <a
        v-for="region in regions"
        :key="region.key"
        class="pill"
        :href="region.href"
      >
        {{ region.label }}
      </a>
    </div>
  </section>
</template>
