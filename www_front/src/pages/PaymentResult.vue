<template>
  <div class="max-w-md mx-auto bg-white p-6 rounded-xl shadow-md text-center">
    <h2 class="text-xl font-bold mb-4">{{ $t('form.payment_result') }}</h2>
    <p v-if="status === 'success'" class="text-green-600 font-semibold">{{ $t('form.payment_successfull') }}</p>
    <p v-else-if="status === 'failure'" class="text-red-600 font-semibold">{{ $t('form.payment_failed') }}</p>
    <p v-else class="text-yellow-600 font-semibold">{{ $t('form.unknown_status') }}</p>

    <button
      type="submit"
      class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md w-full border border-green-700"
      style="background-color: #16a34a;"
    >
      {{ $t('form.another_payment') }}
    </button>
  </div>
</template>

<script setup>
import { useRoute, useRouter } from 'vue-router';
import { computed } from 'vue';

const route = useRoute();
const router = useRouter();

const status = computed(() => route.query.status || 'unknown');

const goToPayment = () => {
  router.push('/payment');
};
</script>
