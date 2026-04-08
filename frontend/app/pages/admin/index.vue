<script setup lang="ts">
import { apiGet } from '~/lib/api'

type AdminSummary = {
  generated_at: string
  counts: {
    councils: number
    council_versions: number
    datasets: number
    imports: number
    import_runs: number
    ingestion_sources: number
    active_ingestion_sources: number
    failing_ingestion_sources: number
    running_import_runs: number
    correction_requests: number
    pending_correction_requests: number
  }
  council_registry: {
    import_key: string
    dataset_key: string | null
    latest_run: {
      id: string
      import_key: string | null
      import_type: string | null
      dataset_key: string | null
      run_state: string
      started_at: string | null
      finished_at: string | null
      rows_seen: number | null
      rows_inserted: number | null
      rows_updated: number | null
      warning_count: number | null
      error_summary: string | null
    } | null
  }
  recent_import_runs: Array<{
    id: string
    import_key: string | null
    import_type: string | null
    dataset_key: string | null
    run_state: string
    started_at: string | null
    finished_at: string | null
    rows_seen: number | null
    rows_inserted: number | null
    rows_updated: number | null
    warning_count: number | null
    error_summary: string | null
  }>
  recent_ingestion_sources: Array<{
    id: string
    source_key: string
    source_name: string
    source_kind: string
    refresh_mode: string
    expected_refresh_cadence: string | null
    is_active: boolean
    dataset_key: string | null
    council_slug: string | null
    last_checked_at: string | null
    last_success_at: string | null
    last_failure_at: string | null
    last_error_summary: string | null
  }>
  recent_correction_requests: Array<{
    id: string
    topic: string
    name: string
    email: string
    council_name: string | null
    council_slug: string | null
    page_url: string | null
    source_url: string | null
    details: string
    status: string
    admin_notes: string | null
    reviewed_at: string | null
    created_at: string | null
  }>
  suggested_actions: Array<{
    label: string
    command: string
    purpose: string
  }>
}

definePageMeta({
  middleware: 'admin'
})

useHead({
  title: 'Admin'
})

const { data, pending, error } = await useAsyncData<AdminSummary>('admin-ingestion-summary', () => apiGet('/admin/ingestion-summary'))

const summary = computed(() => data.value ?? null)

function formatDate(value: string | null): string {
  if (!value) return '—'
  return new Intl.DateTimeFormat('en-GB', {
    dateStyle: 'medium',
    timeStyle: 'short'
  }).format(new Date(value))
}

function formatCount(value: number | null | undefined): string {
  return typeof value === 'number' ? value.toString() : '0'
}
</script>

<template>
  <div class="landing">
    <section class="panel">
      <h1 class="hero__title">Admin</h1>
      <p class="hero__lede">Ingestion status, registry health and correction requests.</p>
      <div class="row" style="margin-top: 0.75rem;">
        <NuxtLink class="pill" to="/status">API status</NuxtLink>
        <a class="pill" href="#corrections">Corrections</a>
        <a class="pill" href="#actions">Commands</a>
      </div>
    </section>

    <section id="ingestion-status" class="section">
      <div v-if="pending" class="callout">Loading...</div>
      <div v-else-if="error" class="callout callout--error" role="alert">
        Could not load admin summary. {{ error instanceof Error ? error.message : '' }}
      </div>
      <template v-else>
        <div class="card-grid card-grid--four">
          <article class="card">
            <h3>Councils</h3>
            <p style="font-size: 1.25rem; font-weight: 700; color: var(--kmc-text);">{{ summary?.counts.councils ?? 0 }}</p>
          </article>
          <article class="card">
            <h3>Import runs</h3>
            <p style="font-size: 1.25rem; font-weight: 700; color: var(--kmc-text);">{{ summary?.counts.import_runs ?? 0 }}</p>
          </article>
          <article class="card">
            <h3>Failing sources</h3>
            <p style="font-size: 1.25rem; font-weight: 700; color: var(--kmc-text);">{{ summary?.counts.failing_ingestion_sources ?? 0 }}</p>
          </article>
          <article class="card">
            <h3>Pending corrections</h3>
            <p style="font-size: 1.25rem; font-weight: 700; color: var(--kmc-text);">{{ summary?.counts.pending_correction_requests ?? 0 }}</p>
          </article>
        </div>

        <div class="card-grid card-grid--three" style="margin-top: 0.75rem;">
          <article class="card">
            <h3>Registry import</h3>
            <p><code>{{ summary?.council_registry.import_key }}</code></p>
          </article>
          <article class="card">
            <h3>Latest run</h3>
            <template v-if="summary?.council_registry.latest_run">
              <p>{{ summary.council_registry.latest_run.run_state }}</p>
              <p class="muted" style="font-size: 0.8rem;">Started {{ formatDate(summary.council_registry.latest_run.started_at) }}</p>
            </template>
            <p v-else class="muted">No run yet.</p>
          </article>
          <article class="card">
            <h3>Running jobs</h3>
            <p style="font-size: 1.25rem; font-weight: 700; color: var(--kmc-text);">{{ summary?.counts.running_import_runs ?? 0 }}</p>
          </article>
        </div>
      </template>
    </section>

    <section id="corrections" class="section">
      <div class="panel">
        <h2 class="section__heading" style="margin-top: 0;">Recent corrections</h2>
        <div v-if="summary?.recent_correction_requests?.length" class="stack">
          <article v-for="request in summary.recent_correction_requests" :key="request.id" class="card">
            <div class="row" style="justify-content: space-between; align-items: flex-start;">
              <div>
                <h3 style="margin-top: 0; margin-bottom: 0.2rem;">{{ request.topic }}</h3>
                <p class="muted" style="margin: 0; font-size: 0.85rem;">{{ request.name }} &middot; {{ request.email }}</p>
              </div>
              <span class="pill" style="font-size: 0.75rem;">{{ request.status }}</span>
            </div>
            <div class="card-grid card-grid--two" style="margin-top: 0.5rem;">
              <div>
                <p class="muted" style="margin: 0; font-size: 0.8rem;">Council</p>
                <p style="margin: 0; font-size: 0.9rem;">{{ request.council_name ?? request.council_slug ?? '—' }}</p>
              </div>
              <div>
                <p class="muted" style="margin: 0; font-size: 0.8rem;">Submitted</p>
                <p style="margin: 0; font-size: 0.9rem;">{{ formatDate(request.created_at) }}</p>
              </div>
            </div>
            <p style="margin: 0.5rem 0 0; font-size: 0.9rem;">{{ request.details }}</p>
          </article>
        </div>
        <p v-else class="muted">No correction requests.</p>
      </div>
    </section>

    <section class="section">
      <div class="panel">
        <h2 class="section__heading" style="margin-top: 0;">Recent imports</h2>
        <div v-if="summary?.recent_import_runs?.length" class="stack">
          <article v-for="run in summary.recent_import_runs" :key="run.id" class="card">
            <div class="row" style="justify-content: space-between; align-items: flex-start;">
              <div>
                <h3 style="margin-top: 0; margin-bottom: 0.2rem;">{{ run.import_key ?? 'Unknown' }}</h3>
                <p class="muted" style="margin: 0; font-size: 0.85rem;">{{ run.import_type ?? '—' }} &middot; {{ run.dataset_key ?? '—' }}</p>
              </div>
              <span class="pill" style="font-size: 0.75rem;">{{ run.run_state }}</span>
            </div>
            <div class="card-grid card-grid--two" style="margin-top: 0.5rem;">
              <div>
                <p class="muted" style="margin: 0; font-size: 0.8rem;">Started</p>
                <p style="margin: 0; font-size: 0.9rem;">{{ formatDate(run.started_at) }}</p>
              </div>
              <div>
                <p class="muted" style="margin: 0; font-size: 0.8rem;">Rows</p>
                <p style="margin: 0; font-size: 0.9rem;">{{ formatCount(run.rows_seen) }} seen, {{ formatCount(run.rows_inserted) }} inserted, {{ formatCount(run.rows_updated) }} updated</p>
              </div>
            </div>
            <div v-if="run.error_summary" class="callout callout--error" style="margin-top: 0.5rem;">
              {{ run.error_summary }}
            </div>
          </article>
        </div>
        <p v-else class="muted">No import runs.</p>
      </div>
    </section>

    <section class="section">
      <div class="panel">
        <h2 class="section__heading" style="margin-top: 0;">Ingestion sources</h2>
        <div v-if="summary?.recent_ingestion_sources?.length" class="stack">
          <article v-for="source in summary.recent_ingestion_sources" :key="source.id" class="card">
            <div class="row" style="justify-content: space-between; align-items: flex-start;">
              <div>
                <h3 style="margin-top: 0; margin-bottom: 0.2rem;">{{ source.source_name }}</h3>
                <p class="muted" style="margin: 0; font-size: 0.85rem;">{{ source.source_key }} &middot; {{ source.source_kind }}</p>
              </div>
              <span class="pill" style="font-size: 0.75rem;">{{ source.is_active ? 'Active' : 'Paused' }}</span>
            </div>
            <div class="card-grid card-grid--two" style="margin-top: 0.5rem;">
              <div>
                <p class="muted" style="margin: 0; font-size: 0.8rem;">Last checked</p>
                <p style="margin: 0; font-size: 0.9rem;">{{ formatDate(source.last_checked_at) }}</p>
              </div>
              <div>
                <p class="muted" style="margin: 0; font-size: 0.8rem;">Last success</p>
                <p style="margin: 0; font-size: 0.9rem;">{{ formatDate(source.last_success_at) }}</p>
              </div>
            </div>
            <div v-if="source.last_error_summary" class="callout callout--error" style="margin-top: 0.5rem;">
              {{ source.last_error_summary }}
            </div>
          </article>
        </div>
        <p v-else class="muted">No ingestion sources.</p>
      </div>
    </section>

    <section id="actions" class="section">
      <div class="panel">
        <h2 class="section__heading" style="margin-top: 0;">Useful commands</h2>
        <div class="card-grid card-grid--three">
          <article v-for="action in summary?.suggested_actions ?? []" :key="action.command" class="card">
            <h3>{{ action.label }}</h3>
            <p><code>{{ action.command }}</code></p>
            <p>{{ action.purpose }}</p>
          </article>
        </div>
      </div>
    </section>
  </div>
</template>
