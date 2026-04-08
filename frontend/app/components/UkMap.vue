<script setup lang="ts">
import { importLibrary, setOptions } from '@googlemaps/js-api-loader'

declare const google: any

type NationKey = 'england' | 'scotland' | 'wales' | 'northern-ireland'

type LatLngLiteral = {
  lat: number
  lng: number
}

type LatLngBoundsLiteral = {
  north: number
  south: number
  west: number
  east: number
}

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
  position: LatLngLiteral
  color: string
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
    position: { lat: 52.9, lng: -1.7 },
    color: '#0f766e'
  },
  {
    key: 'scotland',
    label: 'Scotland',
    shortLabel: 'SCT',
    href: '/councils?nation=scotland',
    description: 'Part of the UK-wide council map.',
    position: { lat: 56.7, lng: -4.2 },
    color: '#0c5670'
  },
  {
    key: 'wales',
    label: 'Wales',
    shortLabel: 'WLS',
    href: '/councils?nation=wales',
    description: 'Part of the UK-wide council map.',
    position: { lat: 52.2, lng: -3.8 },
    color: '#a16207'
  },
  {
    key: 'northern-ireland',
    label: 'Northern Ireland',
    shortLabel: 'NIR',
    href: '/councils?nation=northern-ireland',
    description: 'Part of the UK-wide council map.',
    position: { lat: 54.7, lng: -6.6 },
    color: '#9333ea'
  }
]

const ukBounds: LatLngBoundsLiteral = {
  north: 61.2,
  south: 49.4,
  west: -11.2,
  east: 3.6
}

const config = useRuntimeConfig()
const router = useRouter()
const mapElement = ref<HTMLDivElement | null>(null)
const mapState = ref<'fallback' | 'loading' | 'ready' | 'error'>('fallback')
const mapError = ref('')

const hasGoogleMaps = computed(() => Boolean(config.public.googleMapsApiKey))
const hasMapId = computed(() => Boolean(config.public.googleMapsMapId))

function openRoute(href: string): void {
  void router.push(href)
}

function renderStaticMap(): void {
  mapState.value = 'fallback'
}

onMounted(async () => {
  if (!mapElement.value || !hasGoogleMaps.value) {
    renderStaticMap()
    return
  }

  mapState.value = 'loading'

  try {
    setOptions({
      key: String(config.public.googleMapsApiKey),
      v: 'weekly',
      language: 'en-GB',
      region: 'GB',
      ...(hasMapId.value ? { mapIds: [String(config.public.googleMapsMapId)] } : {})
    })

    await importLibrary('maps')

    const map = new google.maps.Map(mapElement.value, {
      center: { lat: 54.5, lng: -3.3 },
      zoom: 5.4,
      minZoom: 4,
      maxZoom: 8,
      mapTypeId: 'roadmap',
      gestureHandling: 'cooperative',
      fullscreenControl: false,
      streetViewControl: false,
      mapTypeControl: false,
      rotateControl: false,
      clickableIcons: false,
      zoomControl: true,
      restriction: {
        latLngBounds: ukBounds,
        strictBounds: true
      },
      mapId: hasMapId.value ? String(config.public.googleMapsMapId) : undefined
    })

    if (hasMapId.value) {
      const boundaryStroke = '#0f766e'
      const boundaryFill = '#d9f0ec'
      ;[google.maps.FeatureType.COUNTRY, google.maps.FeatureType.ADMINISTRATIVE_AREA_LEVEL_1].forEach((featureType) => {
        const featureLayer = map.getFeatureLayer(featureType)
        if (featureLayer.isAvailable) {
          featureLayer.style = {
            strokeColor: boundaryStroke,
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: boundaryFill,
            fillOpacity: 0.08
          }
        }
      })
    }

    pins.forEach((pin) => {
      const marker = new google.maps.Marker({
        map,
        position: pin.position,
        title: pin.label,
        label: {
          text: pin.shortLabel,
          color: '#ffffff',
          fontWeight: '700'
        },
        icon: {
          path: google.maps.SymbolPath.CIRCLE,
          fillColor: pin.color,
          fillOpacity: 1,
          strokeColor: '#ffffff',
          strokeWeight: 2,
          scale: 8
        }
      })

      marker.addListener('click', () => openRoute(pin.href))
    })

    mapState.value = 'ready'
  } catch (error) {
    mapError.value = error instanceof Error ? error.message : 'Google Maps could not be loaded.'
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
      <div v-if="mapState === 'ready'" ref="mapElement" class="google-uk-map" aria-label="Interactive Google map of the United Kingdom" />

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

        <p v-if="mapState === 'loading'" class="map-card__status">Loading Google Maps…</p>
        <p v-else-if="mapState === 'error'" class="map-card__status">
          Google Maps could not be loaded, so we are showing the fallback map.
          <span v-if="mapError" class="map-card__status-detail"> {{ mapError }}</span>
        </p>
        <p v-else class="map-card__status">
          Google Maps will appear here when an API key is provided. The fallback map stays available for everyone.
        </p>

        <p v-if="hasGoogleMaps && !hasMapId" class="map-card__status-detail">
          Add a Google Maps map ID to show the UK boundary outlines and styled boundaries.
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
