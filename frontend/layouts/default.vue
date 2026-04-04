<template>
  <div>
    <header class="container">
      <div class="row" style="justify-content: space-between;">
        <div class="row">
          <NuxtLink to="/" style="font-weight: 700;">KnowMyCouncil</NuxtLink>
          <span class="muted" style="font-size: 14px;">Bootstrap scaffold</span>
        </div>
        <nav class="row" style="gap: 14px;">
          <NuxtLink to="/status">Status</NuxtLink>
          <NuxtLink to="/admin">Admin</NuxtLink>
        </nav>
      </div>
    </header>

    <main class="container">
      <slot />
    </main>

    <footer class="container muted" style="font-size: 14px;">
      Local scaffold for a public transparency platform for English councils.
    </footer>
  </div>
</template>

