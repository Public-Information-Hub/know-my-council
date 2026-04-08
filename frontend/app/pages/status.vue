<script setup lang="ts">
import { apiGet } from '~/lib/api'

const health = await apiGet('/health')
const version = await apiGet('/version')
</script>

<template>
  <div class="panel">
    <h1 style="margin-top: 0;">Status</h1>
    <p class="muted">Quick checks against the Laravel API configured in <code>NUXT_PUBLIC_API_BASE_URL</code>.</p>
    <p class="muted">
      Ingestion controls, import runs and source refresh status are available to superadmins in the admin area.
    </p>

    <h2 style="margin-bottom: 8px;">API health</h2>
    <pre class="panel" style="margin-top: 0; overflow: auto;">{{ health }}</pre>

    <h2 style="margin-bottom: 8px;">API version</h2>
    <pre class="panel" style="margin-top: 0; overflow: auto;">{{ version }}</pre>
  </div>
</template>
