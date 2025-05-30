<template>
  <div class="min-h-screen flex flex-col">
    <header class="bg-gray-800 text-white p-4 text-xl font-semibold">
      <div class="container mx-auto">Vue Payment</div>
    </header>

    <main class="flex-grow container mx-auto p-4">
      <slot />
    </main>

    <footer class="bg-gray-100 text-center p-4 text-sm text-gray-600">
      &copy; {{ new Date().getFullYear() }} Vue Payment System
    </footer>
  </div>
</template>

<script setup>
</script>

<style scoped>
</style>