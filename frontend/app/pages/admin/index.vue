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
  title: 'Admin area'
})

const { data, pending, error } = await useAsyncData<AdminSummary>('admin-ingestion-summary', () => apiGet('/admin/ingestion-summary'))

const summary = computed(() => data.value ?? null)

function formatDate(value: string | null): string {
  if (!value) return 'Not set'
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
    <section class="hero panel">
      <div>
        <p class="eyebrow">Admin area</p>
        <h1 class="hero__title">Ingestion, registry and status control</h1>
        <p class="hero__lede">
          This surface is for the operational work that keeps councils, sources and import runs moving.
          It focuses on the current registry, recent refreshes, and what needs attention next.
        </p>

        <div class="row" style="margin-top: 1rem;">
          <NuxtLink class="pill" to="/status">View API status</NuxtLink>
          <a class="pill" href="#ingestion-status">Ingestion status</a>
          <a class="pill" href="#actions">Actions</a>
        </div>
      </div>

      <aside class="hero__panel">
        <div class="card-grid card-grid--two">
          <article class="card">
            <h3 style="margin-top: 0;">Councils</h3>
            <p class="hero__lede" style="margin-bottom: 0;">{{ summary?.counts.councils ?? '—' }}</p>
          </article>
          <article class="card">
            <h3 style="margin-top: 0;">Active sources</h3>
            <p class="hero__lede" style="margin-bottom: 0;">{{ summary?.counts.active_ingestion_sources ?? '—' }}</p>
          </article>
        </div>
      </aside>
    </section>

    <section id="ingestion-status" class="section">
      <div class="panel">
        <h2 class="section__heading" style="margin-top: 0;">Ingestion status</h2>
        <p class="section__lead">
          Live counts and recent activity for council registry work and source refreshes.
        </p>

        <div v-if="pending" class="callout">Loading admin summary…</div>
        <div v-else-if="error" class="callout">
          <strong>Could not load the admin summary.</strong>
          <span style="margin-left: 0.5rem;">{{ error instanceof Error ? error.message : 'Unknown error' }}</span>
        </div>
        <template v-else>
          <div class="card-grid card-grid--four">
            <article class="card">
              <h3>Councils</h3>
              <p>{{ summary?.counts.councils ?? 0 }}</p>
            </article>
            <article class="card">
              <h3>Registry versions</h3>
              <p>{{ summary?.counts.council_versions ?? 0 }}</p>
            </article>
            <article class="card">
              <h3>Import runs</h3>
              <p>{{ summary?.counts.import_runs ?? 0 }}</p>
            </article>
            <article class="card">
              <h3>Sources needing attention</h3>
              <p>{{ summary?.counts.failing_ingestion_sources ?? 0 }}</p>
            </article>
            <article class="card">
              <h3>Pending corrections</h3>
              <p>{{ summary?.counts.pending_correction_requests ?? 0 }}</p>
            </article>
          </div>

          <div class="card-grid card-grid--three" style="margin-top: 1rem;">
            <article class="card">
              <h3>Current council registry</h3>
              <p style="margin-bottom: 0.5rem;">Import key: <code>{{ summary?.council_registry.import_key }}</code></p>
              <p style="margin-bottom: 0;">Dataset key: <code>{{ summary?.council_registry.dataset_key ?? 'not linked yet' }}</code></p>
            </article>
            <article class="card">
              <h3>Latest registry run</h3>
              <p v-if="summary?.council_registry.latest_run">
                State: {{ summary.council_registry.latest_run.run_state }}<br>
                Started: {{ formatDate(summary.council_registry.latest_run.started_at) }}<br>
                Finished: {{ formatDate(summary.council_registry.latest_run.finished_at) }}
              </p>
              <p v-else>No registry run recorded yet.</p>
            </article>
            <article class="card">
              <h3>Running jobs</h3>
              <p style="margin-bottom: 0;">Import runs currently marked running: {{ summary?.counts.running_import_runs ?? 0 }}</p>
            </article>
          </div>
        </template>
      </div>
    </section>

    <section class="section">
      <div class="panel">
        <h2 class="section__heading" style="margin-top: 0;">Recent correction requests</h2>
        <div v-if="summary?.recent_correction_requests?.length" class="stack">
          <article v-for="request in summary.recent_correction_requests" :key="request.id" class="card">
            <div class="row" style="justify-content: space-between; align-items: flex-start;">
              <div>
                <h3 style="margin-top: 0; margin-bottom: 0.35rem;">{{ request.topic }}</h3>
                <p class="muted" style="margin: 0;">{{ request.name }} · {{ request.email }}</p>
              </div>
              <span class="pill">{{ request.status }}</span>
            </div>

            <div class="card-grid card-grid--two" style="margin-top: 0.85rem;">
              <div>
                <p class="muted" style="margin: 0 0 0.2rem;">Council</p>
                <p style="margin: 0;">{{ request.council_name ?? request.council_slug ?? 'Not set' }}</p>
              </div>
              <div>
                <p class="muted" style="margin: 0 0 0.2rem;">Submitted</p>
                <p style="margin: 0;">{{ formatDate(request.created_at) }}</p>
              </div>
              <div v-if="request.page_url">
                <p class="muted" style="margin: 0 0 0.2rem;">Page</p>
                <p style="margin: 0;">{{ request.page_url }}</p>
              </div>
              <div v-if="request.source_url">
                <p class="muted" style="margin: 0 0 0.2rem;">Source</p>
                <p style="margin: 0;">{{ request.source_url }}</p>
              </div>
            </div>

            <p style="margin: 0.85rem 0 0;">{{ request.details }}</p>
          </article>
        </div>
        <p v-else class="muted">No correction requests yet.</p>
      </div>
    </section>

    <section class="section">
      <div class="panel">
        <h2 class="section__heading" style="margin-top: 0;">Recent import runs</h2>
        <div v-if="summary?.recent_import_runs?.length" class="stack">
          <article v-for="run in summary.recent_import_runs" :key="run.id" class="card">
            <div class="row" style="justify-content: space-between; align-items: flex-start;">
              <div>
                <h3 style="margin-top: 0; margin-bottom: 0.35rem;">{{ run.import_key ?? 'Unknown import' }}</h3>
                <p class="muted" style="margin: 0;">{{ run.import_type ?? 'Import type not set' }} · {{ run.dataset_key ?? 'No dataset linked' }}</p>
              </div>
              <span class="pill">{{ run.run_state }}</span>
            </div>
            <div class="card-grid card-grid--two" style="margin-top: 0.85rem;">
              <div>
                <p class="muted" style="margin: 0 0 0.2rem;">Started</p>
                <p style="margin: 0;">{{ formatDate(run.started_at) }}</p>
              </div>
              <div>
                <p class="muted" style="margin: 0 0 0.2rem;">Finished</p>
                <p style="margin: 0;">{{ formatDate(run.finished_at) }}</p>
              </div>
              <div>
                <p class="muted" style="margin: 0 0 0.2rem;">Rows</p>
                <p style="margin: 0;">Seen {{ formatCount(run.rows_seen) }}, inserted {{ formatCount(run.rows_inserted) }}, updated {{ formatCount(run.rows_updated) }}</p>
              </div>
              <div>
                <p class="muted" style="margin: 0 0 0.2rem;">Warnings</p>
                <p style="margin: 0;">{{ formatCount(run.warning_count) }}</p>
              </div>
            </div>
            <p v-if="run.error_summary" class="callout" style="margin-bottom: 0;">
              <strong>Issue:</strong> {{ run.error_summary }}
            </p>
          </article>
        </div>
        <p v-else class="muted">No import runs yet.</p>
      </div>
    </section>

    <section class="section">
      <div class="panel">
        <h2 class="section__heading" style="margin-top: 0;">Recent ingestion sources</h2>
        <div v-if="summary?.recent_ingestion_sources?.length" class="stack">
          <article v-for="source in summary.recent_ingestion_sources" :key="source.id" class="card">
            <div class="row" style="justify-content: space-between; align-items: flex-start;">
              <div>
                <h3 style="margin-top: 0; margin-bottom: 0.35rem;">{{ source.source_name }}</h3>
                <p class="muted" style="margin: 0;">{{ source.source_key }} · {{ source.source_kind }} · {{ source.refresh_mode }}</p>
              </div>
              <span class="pill">{{ source.is_active ? 'Active' : 'Paused' }}</span>
            </div>

            <div class="card-grid card-grid--two" style="margin-top: 0.85rem;">
              <div>
                <p class="muted" style="margin: 0 0 0.2rem;">Council</p>
                <p style="margin: 0;">{{ source.council_slug ?? 'Unlinked' }}</p>
              </div>
              <div>
                <p class="muted" style="margin: 0 0 0.2rem;">Cadence</p>
                <p style="margin: 0;">{{ source.expected_refresh_cadence ?? 'Not set' }}</p>
              </div>
              <div>
                <p class="muted" style="margin: 0 0 0.2rem;">Last checked</p>
                <p style="margin: 0;">{{ formatDate(source.last_checked_at) }}</p>
              </div>
              <div>
                <p class="muted" style="margin: 0 0 0.2rem;">Last success</p>
                <p style="margin: 0;">{{ formatDate(source.last_success_at) }}</p>
              </div>
            </div>

            <p v-if="source.last_error_summary" class="callout" style="margin-bottom: 0;">
              <strong>Last error:</strong> {{ source.last_error_summary }}
            </p>
          </article>
        </div>
        <p v-else class="muted">No ingestion sources yet.</p>
      </div>
    </section>

    <section id="actions" class="section">
      <div class="panel">
        <h2 class="section__heading" style="margin-top: 0;">Useful commands</h2>
        <div class="card-grid card-grid--three">
          <article v-for="action in summary?.suggested_actions ?? []" :key="action.command" class="card">
            <h3>{{ action.label }}</h3>
            <p><code>{{ action.command }}</code></p>
            <p class="muted" style="margin-bottom: 0;">{{ action.purpose }}</p>
          </article>
        </div>
      </div>
    </section>
  </div>
</template>
