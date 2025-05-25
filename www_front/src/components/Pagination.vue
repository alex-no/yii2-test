<template>
  <nav>
    <ul class="pagination justify-content-center">
      <li class="page-item" :class="{ disabled: !pagination.prev }">
        <button class="page-link" @click="$emit('load', pagination.prev)" :disabled="!pagination.prev">
          &laquo;
        </button>
      </li>

      <li
        v-for="link in meta.pageLinks"
        :key="link.label"
        class="page-item"
        :class="{ active: link.active, disabled: !link.url || link.label.includes('pagination') }"
      >
        <button
          class="page-link text-nowrap"
          @click="$emit('load', getPageFromUrl(link.url))"
          :disabled="!link.url || link.label.includes('pagination')"
        >
          {{ formatLabel(link.label) }}
        </button>
      </li>

      <li class="page-item" :class="{ disabled: !pagination.next }">
        <button class="page-link" @click="$emit('load', pagination.next)" :disabled="!pagination.next">
          &raquo;
        </button>
      </li>
    </ul>
  </nav>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

defineProps({
  pagination: Object,
  meta: Object
})

function getPageFromUrl(url) {
  if (!url) return null
  const match = url.match(/[\?&]page=(\d+)/)
  return match ? Number(match[1]) : null
}

function formatLabel(label) {
  const match =  String(label).toLowerCase().match(/previous|next/)
  const key = match ? match[0] : null

  if (key === 'previous') return `← ${t('pagination.previous')}`
  if (key === 'next') return `${t('pagination.next')} →`

  return label
}
</script>

