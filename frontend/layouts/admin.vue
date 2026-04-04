<template>
  <div>
    <header class="container">
      <div class="row" style="justify-content: space-between;">
        <div class="row">
          <NuxtLink to="/" style="font-weight: 700;">KnowMyCouncil</NuxtLink>
          <span class="pill">Admin area (placeholder)</span>
        </div>
        <nav class="row" style="gap: 14px;">
          <NuxtLink to="/admin">Dashboard</NuxtLink>
          <NuxtLink to="/status">Status</NuxtLink>
        </nav>
      </div>
    </header>

    <main class="container">
      <div class="panel">
        <slot />
      </div>
    </main>
  </div>
</template>

