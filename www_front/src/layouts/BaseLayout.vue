<template>
  <div class="layout-wrapper">
    <header class="bg-light border-bottom d-flex justify-content-end px-3 py-1">
      <LanguageSwitcher v-model="selectedLang" />
    </header>

    <main class="container">
      <slot />
    </main>

    <footer class="bg-gray-100 text-center text-sm text-gray-600 px-3 py-1">
      &copy; {{ new Date().getFullYear() }} Oleksandr Nosov’s pet project. All rights reserved.
    </footer>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import LanguageSwitcher from '@/components/LanguageSwitcher.vue'

const { locale } = useI18n()

const urlLang = new URLSearchParams(window.location.search).get('lang')
const selectedLang = ref(urlLang || 'en')
locale.value = selectedLang.value

watch(selectedLang, (newLang) => {
  locale.value = newLang
  const url = new URL(window.location.href)
  url.searchParams.set('lang', newLang)
  window.history.replaceState(null, '', url.toString())
})
</script>