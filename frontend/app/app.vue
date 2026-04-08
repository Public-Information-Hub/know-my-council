<script setup lang="ts">
useHead({
  script: [
    {
      children:
        `(function(){try{var key='kmc-theme';var theme=localStorage.getItem(key);if(!theme){theme=window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light'}document.documentElement.dataset.theme=theme;document.documentElement.style.colorScheme=(theme==='dark'||theme==='contrast')?'dark':'light'}catch(e){}})();`
    }
  ]
})
</script>

<template>
  <NuxtRouteAnnouncer />
  <NuxtLayout>
    <NuxtPage />
  </NuxtLayout>
</template>
