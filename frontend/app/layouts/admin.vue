<template>
  <div class="container">
    <header class="panel" style="margin-bottom: 16px;">
      <div class="row" style="justify-content: space-between;">
        <div class="row">
          <NuxtLink to="/" style="font-weight: 700;">KnowMyCouncil</NuxtLink>
          <span class="muted" style="font-size: 14px;">Admin area</span>
        </div>
        <NuxtLink to="/">Back to site</NuxtLink>
      </div>
    </header>

    <slot />
  </div>
</template>
