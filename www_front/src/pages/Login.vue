<template>
  <form @submit.prevent="handleLogin" class="max-w-md mx-auto bg-white p-6 mt-3 rounded-xl shadow-md">
    <h2 class="text-xl font-bold mb-4">{{ $t('form.login') }}</h2>
    <p v-if="showAuthMessage" class="text-sm text-red-600 mb-4" style="color: darkred;">
      {{ $t('form.auth_required') }}
    </p>
    <div class="mb-4">
      <label class="block text-sm text-end font-medium text-gray-700 mb-2" style="width: 140px; margin-right: 10px;">{{ $t('form.username') }}</label>
      <input v-model="username" type="text" class="w-full border border-gray-300 p-2 rounded-md" required />
    </div>

    <div class="mb-4">
      <label class="block text-sm text-end font-medium text-gray-700 mb-2" style="width: 140px; margin-right: 10px;">{{ $t('form.password') }}</label>
      <input v-model="password" type="password" class="w-full border border-gray-300 p-2 rounded-md" required />
    </div>

    <button
      type="submit"
      class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md w-full border border-green-700"
      style="background-color: #16a34a;"
    >
      {{ $t('form.sign_in') }}
    </button>
  </form>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';

const username = ref('');
const password = ref('');
const router = useRouter();
const route = useRoute();

const showAuthMessage = computed(() => route.query['no-auth'] === '1');

const handleLogin = async () => {
  const response = await fetch('/api/auth/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      username: username.value,
      password: password.value
    })
  });

  const data = await response.json();
  if (data.access_token) {
    localStorage.setItem('access_token', data.access_token);
    router.push('/payment');
  } else {
    alert('Login failed');
  }
};
</script>
