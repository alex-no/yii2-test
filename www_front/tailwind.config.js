/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    // '../www_app/views/**/*.php',
    // '../www_app/widgets/**/*.php',
    // '../www_app/app/modules/**/views/**/*.php',
    // '../www_app/web/public/js/**/*.js',
    // "./src/**/*.{html,js}",
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  safelist: [
    'bg-green-600',
    'hover:bg-green-700',
    'border-green-700',
    'bg-red-100',
    'border-red-400',
    'text-red-700'
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}
