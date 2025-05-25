<template>
  <div class="d-flex justify-content-end align-items-center mb-3">
    <div class="btn-group">
      <button
        type="button"
        class="btn btn-outline-secondary dropdown-toggle"
        data-bs-toggle="dropdown"
        aria-expanded="false"
      >
        {{ currentLanguage?.short_name || '🌐 Language' }}
      </button>
      <ul class="dropdown-menu dropdown-menu-end">
        <li
          v-for="lang in languages"
          :key="lang.code"
        >
          <a
            class="dropdown-item"
            href="#"
            @click.prevent="changeLanguage(lang.code)"
          >
            {{ lang.full_name }}
          </a>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { detectLanguage } from '@/utils/detect_language'

const selectedLang = ref(detectLanguage())
const props = defineProps({ modelValue: String })
const emit = defineEmits(['update:modelValue'])

const languages = ref([])
const currentLanguage = ref(null)

function saveLanguage(code, days = 365) {
  const name = 'selected_language'
  currentLanguage.value = languages.value.find((l) => l.code === code)

  // Save to localStorage
  localStorage.setItem('selected_language', code)

  // Save to cookie
  const expires = new Date(Date.now() + days * 86400 * 1000).toUTCString()
  document.cookie = `${name}=${encodeURIComponent(code)}; expires=${expires}; path=/`
}

function changeLanguage(code) {
  saveLanguage(code)
  emit('update:modelValue', code)
}

watch(
  () => props.modelValue,
  (newCode) => {
    if (newCode) {
      saveLanguage(newCode)
    }
  }
)

async function fetchLanguages() {
  try {
    const res = await fetch('/api/languages')
    const data = await res.json()
    languages.value = data.data

    const detectedLang = detectLanguage()
    currentLanguage.value = languages.value.find((l) => l.code === detectedLang)
    emit('update:modelValue', detectedLang)
  } catch (e) {
    console.error('Error loading languages:', e)
  }
}

onMounted(fetchLanguages)
</script>

<style scoped>
.dropdown-toggle::after {
  margin-left: 0.5rem;
}
</style>
