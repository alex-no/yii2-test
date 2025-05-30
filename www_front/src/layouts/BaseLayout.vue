<template>
  <div>
    <header class="p-3 bg-light border-bottom d-flex justify-content-end">
      <LanguageSwitcher v-model="selectedLang" />
    </header>

    <main class="flex-grow container mx-auto p-4">
      <slot />
    </main>

    <footer class="bg-gray-100 text-center p-4 text-sm text-gray-600">
      &copy; {{ new Date().getFullYear() }} Vue Payment System
    </footer>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import LanguageSwitcher from '@/components/LanguageSwitcher.vue'

const { locale } = useI18n()

const selectedLang = ref(new URLSearchParams(window.location.search).get('lang') || 'en')
locale.value = selectedLang.value

watch(selectedLang, (newLang) => {
  locale.value = newLang
  const url = new URL(window.location.href)
  url.searchParams.set('lang', newLang)
  window.history.replaceState(null, '', url.toString())
})
</script>