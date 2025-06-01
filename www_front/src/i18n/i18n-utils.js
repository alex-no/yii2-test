import { watch } from 'vue'

export function applyI18nEffects(i18n, router) {
  // Set HTML language
  document.documentElement.lang = i18n.global.locale.value

  // Function to update title based on route
  const updateTitle = () => {
    const titleKey = router.currentRoute.value.meta.title
    if (titleKey) {
      document.title = i18n.global.t(titleKey)
    } else {
      document.title = i18n.global.t('htmlTitle') // fallback
    }
  }

  // Watch for language changes
  watch(
    () => i18n.global.locale.value,
    (newLocale) => {
      document.documentElement.lang = newLocale
      updateTitle()
    }
  )

  // Watch for route changes
  router.afterEach(() => {
    updateTitle()
  })

  // Initialize on load
  updateTitle()
}
