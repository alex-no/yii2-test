import { createApp } from 'vue'
import App from './App.vue'

import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'

import { createI18n } from 'vue-i18n'
import messages from './locales'
import { detectLanguage } from './utils/detect_language'
import { applyI18nEffects } from './i18n/i18n-utils'

const i18n = createI18n({
  legacy: false,
  locale: detectLanguage(),
  fallbackLocale: 'en',
  messages
})

const app = createApp(App)
app.use(i18n)
app.mount('#app')

applyI18nEffects(i18n)
