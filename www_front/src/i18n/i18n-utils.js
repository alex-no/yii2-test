import { watch } from 'vue'

export function applyI18nEffects(i18n) {
  document.documentElement.lang = i18n.global.locale.value
  document.title = i18n.global.t('pageTitle')

  watch(
    () => i18n.global.locale.value,
    (newLocale) => {
      document.documentElement.lang = newLocale
      document.title = i18n.global.t('pageTitle')
    }
  )
}
