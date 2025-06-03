import { createApp } from 'vue'
import App from './App.vue'

import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'

import router from './router'
import { createI18n } from 'vue-i18n'
import messages from './locales'
import { applyI18nEffects } from './i18n/i18n-utils'
import { detectLanguage } from '@/utils/detect_language'

import './assets/tailwind.css';
import './style.css'

import './input.css'

const i18n = createI18n({
  legacy: false,
  locale: detectLanguage(),
  fallbackLocale: 'en',
  messages
})

const app = createApp(App)
app.use(router)
app.use(i18n)

applyI18nEffects(i18n, router)

app.mount('#app')
