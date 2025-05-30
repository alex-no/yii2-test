import { createApp } from 'vue'
import App from './App.vue'

import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'

import router from './router'
import { createI18n } from 'vue-i18n'
import messages from './locales'
import { applyI18nEffects } from './i18n/i18n-utils'
//import { detectLanguage } from '@/utils/detect_language'

import './style.css'

const i18n = createI18n({
  legacy: false,
  //locale: detectLanguage(),
  locale: 'en',
  fallbackLocale: 'en',
  messages
})

createApp(App)
  .use(router)
  .use(i18n)
  .mount('#app')

applyI18nEffects(i18n)
