<template>
  <form @submit.prevent="handleLogin" class="max-w-md mx-auto bg-white p-6 rounded-xl shadow-md">
    <h2 class="text-xl font-bold mb-4">Login</h2>
    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
      <input v-model="username" type="text" class="w-full border border-gray-300 p-2 rounded-md" required />
    </div>

    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
      <input v-model="password" type="password" class="w-full border border-gray-300 p-2 rounded-md" required />
    </div>

    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md w-full">
      Sign In
    </button>
  </form>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const username = ref('');
const password = ref('');
const router = useRouter();

const handleLogin = async () => {
  const response = await fetch('/api/login', {
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
    router.push('/html/payment');
  } else {
    alert('Login failed');
  }
};
</script>
