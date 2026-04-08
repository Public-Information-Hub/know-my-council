<script setup lang="ts">
useHead({
  title: 'Contact'
})

const contactTopics = [
  {
    title: 'Corrections',
    email: 'hello@knowmycouncil.uk',
    subject: 'KnowMyCouncil correction request',
    description: 'Use this for council page errors, source mismatches, or missing records.'
  },
  {
    title: 'Accessibility',
    email: 'hello@knowmycouncil.uk',
    subject: 'KnowMyCouncil accessibility feedback',
    description: 'Use this for keyboard, contrast, screen reader, or layout issues.'
  },
  {
    title: 'Data sources',
    email: 'hello@knowmycouncil.uk',
    subject: 'KnowMyCouncil source question',
    description: 'Use this for questions about how a council record was sourced or updated.'
  }
]
</script>

<template>
  <div class="landing">
    <section class="panel">
      <p class="eyebrow">Contact</p>
      <h1 class="hero__title">Get in touch</h1>
      <p class="hero__lede">
        The quickest route is email. Use the topic that best matches your request, and we will keep replies focused and traceable.
      </p>
    </section>

    <section class="panel">
      <div class="card-grid card-grid--three">
        <article v-for="topic in contactTopics" :key="topic.title" class="card">
          <h2 class="section__heading" style="margin-top: 0;">{{ topic.title }}</h2>
          <p style="margin-bottom: 0.75rem;">{{ topic.description }}</p>
          <a class="pill" :href="`mailto:${topic.email}?subject=${encodeURIComponent(topic.subject)}`">Email us</a>
        </article>
      </div>
    </section>

    <section class="panel">
      <h2 class="section__heading" style="margin-top: 0;">General contact</h2>
      <p>
        Email <a href="mailto:hello@knowmycouncil.uk">hello@knowmycouncil.uk</a> if you are not sure which route to use.
      </p>
      <p class="subtle" style="margin-bottom: 0;">
        Please include the council name, page link, and a short note about what needs to change if you are reporting a correction.
      </p>
    </section>
  </div>
</template>
