<script setup lang="ts">
import 'leaflet/dist/leaflet.css'

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

type PinEntry = {
  key: NationKey
  label: string
  shortLabel: string
  href: string
  description: string
  position: [number, number]
  color: string
}

type OutlineEntry = {
  key: NationKey
  label: string
  href: string
  description: string
  points: [number, number][]
}

const regions: NationMapEntry[] = [
  {
    key: 'scotland',
    label: 'Scotland',
    href: '/councils?nation=scotland',
    description: 'Part of the UK-wide council map.',
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
    description: 'Part of the UK-wide council map.',
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
    description: 'Part of the UK-wide council map.',
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
    description: 'Part of the UK-wide council map.',
    x: 174,
    y: 150,
    width: 150,
    height: 188,
    accent: 'var(--kmc-accent-soft)'
  }
]

const pins: PinEntry[] = [
  {
    key: 'england',
    label: 'England',
    shortLabel: 'ENG',
    href: '/councils',
    description: 'Part of the UK-wide council map.',
    position: [52.9, -1.7],
    color: '#0f766e'
  },
  {
    key: 'scotland',
    label: 'Scotland',
    shortLabel: 'SCT',
    href: '/councils?nation=scotland',
    description: 'Part of the UK-wide council map.',
    position: [56.7, -4.2],
    color: '#0c5670'
  },
  {
    key: 'wales',
    label: 'Wales',
    shortLabel: 'WLS',
    href: '/councils?nation=wales',
    description: 'Part of the UK-wide council map.',
    position: [52.2, -3.8],
    color: '#a16207'
  },
  {
    key: 'northern-ireland',
    label: 'Northern Ireland',
    shortLabel: 'NIR',
    href: '/councils?nation=northern-ireland',
    description: 'Part of the UK-wide council map.',
    position: [54.7, -6.6],
    color: '#9333ea'
  }
]

const outlines: OutlineEntry[] = [
  {
    key: 'scotland',
    label: 'Scotland',
    href: '/councils?nation=scotland',
    description: 'Part of the UK-wide council map.',
    points: [
      [55.0, -6.2],
      [56.2, -6.0],
      [57.5, -5.1],
      [58.6, -3.8],
      [59.2, -2.0],
      [59.3, -0.8],
      [58.0, 0.0],
      [56.7, -0.5],
      [55.8, -1.8],
      [55.0, -3.8],
      [55.0, -6.2]
    ]
  },
  {
    key: 'wales',
    label: 'Wales',
    href: '/councils?nation=wales',
    description: 'Part of the UK-wide council map.',
    points: [
      [53.5, -5.5],
      [52.8, -5.1],
      [52.1, -4.6],
      [51.7, -4.9],
      [51.1, -5.3],
      [51.0, -4.0],
      [51.5, -3.1],
      [52.4, -3.4],
      [53.1, -4.0],
      [53.5, -5.5]
    ]
  },
  {
    key: 'northern-ireland',
    label: 'Northern Ireland',
    href: '/councils?nation=northern-ireland',
    description: 'Part of the UK-wide council map.',
    points: [
      [55.3, -8.3],
      [55.1, -7.0],
      [54.4, -6.4],
      [54.0, -7.5],
      [54.2, -8.7],
      [55.3, -8.3]
    ]
  },
  {
    key: 'england',
    label: 'England',
    href: '/councils',
    description: 'Part of the UK-wide council map.',
    points: [
      [55.8, -5.7],
      [55.2, -4.2],
      [54.7, -3.1],
      [54.2, -2.0],
      [53.7, -1.1],
      [52.8, -0.2],
      [52.0, 0.5],
      [50.7, 0.0],
      [50.0, -1.7],
      [50.2, -3.8],
      [50.9, -5.3],
      [51.7, -5.6],
      [52.7, -5.1],
      [53.7, -4.8],
      [54.7, -4.8],
      [55.8, -5.7]
    ]
  }
]

const ukBounds: [number, number][] = [
  [61.2, -11.2],
  [49.4, 3.6]
]

const router = useRouter()
const mapElement = ref<HTMLDivElement | null>(null)
const mapState = ref<'fallback' | 'loading' | 'ready' | 'error'>('fallback')
const mapError = ref('')

function openRoute(href: string): void {
  void router.push(href)
}

function resolveColor(name: string, fallback: string): string {
  if (typeof window === 'undefined') {
    return fallback
  }

  const value = getComputedStyle(document.documentElement).getPropertyValue(name).trim()
  return value || fallback
}

onMounted(async () => {
  mapState.value = 'loading'
  await nextTick()

  if (!mapElement.value) {
    mapState.value = 'fallback'
    return
  }

  try {
    const leaflet = await import('leaflet')
    const map = leaflet.map(mapElement.value, {
      center: [54.5, -3.3],
      zoom: 5.4,
      minZoom: 4,
      maxZoom: 8,
      zoomControl: true,
      attributionControl: true,
      scrollWheelZoom: false,
      dragging: true,
      touchZoom: true,
      doubleClickZoom: true,
      keyboard: true,
      boxZoom: true,
      maxBounds: ukBounds,
      maxBoundsViscosity: 0.9
    })

    leaflet
      .tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        minZoom: 4,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
      })
      .addTo(map)

    const borderColor = resolveColor('--kmc-border', '#cbd5e1')
    const accentColor = resolveColor('--kmc-accent-soft', '#bfe8df')
    const panelColor = resolveColor('--kmc-panel', '#ffffff')

    outlines.forEach((nation) => {
      const polygon = leaflet
        .polygon(nation.points, {
          color: borderColor,
          weight: 2,
          opacity: 0.95,
          fillColor: accentColor,
          fillOpacity: 0.2
        })
        .addTo(map)

      polygon.bindTooltip(nation.label, {
        permanent: true,
        direction: 'center',
        className: 'leaflet-tooltip--nation',
        opacity: 0.95
      })

      polygon.on('click', () => openRoute(nation.href))
      polygon.on('mouseover', () => polygon.setStyle({ weight: 3, fillOpacity: 0.28 }))
      polygon.on('mouseout', () => polygon.setStyle({ weight: 2, fillOpacity: 0.2 }))
    })

    pins.forEach((pin) => {
      const marker = leaflet.circleMarker(pin.position, {
        radius: 8,
        color: panelColor,
        weight: 2,
        fillColor: pin.color,
        fillOpacity: 1,
        opacity: 1
      }).addTo(map)

      marker.bindTooltip(pin.label, {
        direction: 'top',
        offset: [0, -6],
        opacity: 0.96
      })

      marker.on('click', () => openRoute(pin.href))
    })

    map.fitBounds(ukBounds, { padding: [22, 22] })
    map.invalidateSize()

    mapState.value = 'ready'
  } catch (error) {
    mapError.value = error instanceof Error ? error.message : 'The open-source map could not be loaded.'
    mapState.value = 'error'
  }
})
</script>

<template>
  <section class="map-card" aria-labelledby="uk-map-title">
    <div class="map-card__header">
      <div>
        <p class="eyebrow" style="margin-bottom: 0.35rem;">UK map</p>
        <h2 id="uk-map-title" class="section__heading" style="margin: 0;">Find your council</h2>
      </div>
      <p class="subtle" style="margin: 0;">
        The map is set up for UK-wide council navigation from the start.
      </p>
    </div>

    <div class="map-card__canvas">
      <div v-if="mapState === 'loading' || mapState === 'ready'" ref="mapElement" class="leaflet-uk-map" aria-label="Interactive map of the United Kingdom" />

      <div v-else class="map-card__fallback">
        <svg class="uk-map" viewBox="0 0 420 420" role="img" aria-labelledby="uk-map-svg-title uk-map-svg-desc">
          <title id="uk-map-svg-title">Interactive map of the United Kingdom</title>
          <desc id="uk-map-svg-desc">
            Select a nation to continue to the council finder or the council pages.
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

        <p v-if="mapState === 'error'" class="map-card__status">
          The open-source map could not be loaded, so we are showing the fallback map.
          <span v-if="mapError" class="map-card__status-detail"> {{ mapError }}</span>
        </p>
        <p v-else class="map-card__status">
          The map uses open-source tiles and works without a paid API key. The fallback map stays available for everyone.
        </p>
      </div>
    </div>

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
