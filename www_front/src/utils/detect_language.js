import messages from '../locales'

export function detectLanguage() {
  if (typeof window === 'undefined') return 'en'
  if (typeof navigator === 'undefined') return 'en'
  if (typeof document === 'undefined') return 'en'

  const candidates = [
    () => new URLSearchParams(window.location.search).get('lang'),
    () => localStorage.getItem('selected_language'),
    () => {
      const match = document.cookie.match(/(?:^| )selected_language=([^;]+)/)
      return match ? decodeURIComponent(match[1]) : null
    },
    () => navigator.language?.split('-')[0],
    () => 'en',
  ]

  for (const getLang of candidates) {
    const lang = getLang()
    if (lang && Object.keys(messages).includes(lang)) return lang
  }

  return 'en'
}
