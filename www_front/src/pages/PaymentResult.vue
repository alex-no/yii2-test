<template>
  <BaseLayout>
    <div class="max-w-md mx-auto bg-white p-6 rounded-xl shadow-md text-center">
      <h2 class="text-xl font-bold mb-4">Payment Result</h2>
      <p v-if="status === 'success'" class="text-green-600 font-semibold">Your payment was processed successfully.</p>
      <p v-else-if="status === 'failure'" class="text-red-600 font-semibold">Payment failed. Please try again.</p>
      <p v-else class="text-yellow-600 font-semibold">Unknown payment status.</p>

      <button @click="goToPayment" class="mt-6 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
        Make another payment
      </button>
    </div>
  </BaseLayout>
</template>

<script setup>
import { useRoute, useRouter } from 'vue-router';
import { computed } from 'vue';
import BaseLayout from '@/layouts/BaseLayout.vue';

const route = useRoute();
const router = useRouter();

const status = computed(() => route.query.status || 'unknown');

const goToPayment = () => {
  router.push('/html/payment');
};
</script>
