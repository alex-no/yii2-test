<template>
  <div class="container py-5">
    <h1 class="mb-4">{{ $t('featureTitle') }}</h1>

    <div v-if="loading" class="text-center">
      <div class="spinner-border" role="status"></div>
      <span class="ms-2">{{ $t('pageLoading') }}...</span>
    </div>

    <div v-if="error" class="alert alert-danger">{{ error }}</div>

    <div v-if="!loading && !error">
      <table class="table table-striped table-bordered">
        <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>{{ $t('table.columnFeature') }}</th>
            <th>{{ $t('table.columnTechnology') }}</th>
            <th>{{ $t('table.columnResult') }}</th>
            <th>{{ $t('table.columnStatus') }}</th>
            <th>{{ $t('table.columnUpdated') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in items" :key="item.id">
            <td>{{ item.sort_order }}</td>
            <td>{{ item.feature }}</td>
            <td>{{ item.technology }}</td>
            <td v-html="item.result"></td>
            <td>{{ item.status_adv }}</td>
            <td>{{ item.updated }}</td>
          </tr>
        </tbody>
      </table>

      <Pagination :pagination="pagination" :meta="meta" @load="loadPage" />
    </div>
    <hr class="mt-5" />
    
    <div class="mb-2">
      {{ $t('goto_swagger') }}
      <a href="/api/documentation/#" target="_blank" class="link-primary ms-1">
        {{ $t('here') }}
      </a>.
    </div>
    <div class="mb-2">
      {{ $t('goto_github') }}
      <a href="https://github.com/alex-no/yii2-test" target="_blank" class="link-primary ms-1">
        {{ $t('here') }}
      </a>.
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import Pagination from '@/components/Pagination.vue'

const { locale, t } = useI18n()
const items = ref([])
const loading = ref(true)
const error = ref(null)
const pagination = ref({ next: null, prev: null })
const meta = ref({ links: [] })

function getQueryParam(name) {
  return new URLSearchParams(window.location.search).get(name)
}

const selectedLang = ref(getQueryParam('lang') || 'en')
const currentPage = ref(Number(getQueryParam('page')) || 1)

const baseUrl = '/api/development-plan'

onMounted(() => {
  fetchData(locale.value, currentPage.value)
})

watch(locale, (newLang) => {
  currentPage.value = 1 // When changing the language, you can reset to the 1st page or keep the current one — your choice
  updateUrl({ lang: newLang, page: currentPage.value })
  fetchData(newLang, currentPage.value)
})

function loadPage(page) {
  if (!page) return
  currentPage.value = page
  updateUrl({ lang: selectedLang.value, page })
  fetchData(selectedLang.value, page)
}

// Update the URL without reloading, so parameters are always in the address
function updateUrl({ lang, page }) {
  const url = new URL(window.location.href)
  url.searchParams.set('lang', lang)
  url.searchParams.set('page', page)
  window.history.replaceState(null, '', url.toString())
}

function fetchData(lang, page) {
  loading.value = true
  error.value = null

  fetch(`${baseUrl}?lang=${lang}&page=${page}`)
    .then((response) => {
      if (!response.ok) throw new Error('Network error')
      return response.json()
    })
    .then((data) => {
      items.value = data.items
      meta.value = data._meta
      const links = data._meta.links || {}
      pagination.value.next = links.next ? getPageFromUrl(links.next) : null
      pagination.value.prev = links.prev ? getPageFromUrl(links.prev) : null
    })
    .catch((err) => {
      console.error(err)
      error.value = t('pageLoadingError')
    })
    .finally(() => {
      loading.value = false
    })
}

// The API returns links with ?page=... — extract the page number from the link
function getPageFromUrl(url) {
  try {
    const u = new URL(url, window.location.origin)
    return Number(u.searchParams.get('page')) || 1
  } catch {
    return null
  }
}
</script>
