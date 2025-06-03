<template>
  <div class="max-w-md mx-auto bg-white p-6 mt-3 rounded-xl shadow-md text-center">
    <h2 class="text-xl font-bold mb-4">{{ $t('form.payment_result') }}</h2>
    <div class="mb-4 attention-block">
      <b>{{ $t('attention') }}</b>
      {{ $t('site_description') }}
    </div>

    <div v-if="paymentInfo" class="text-left mt-4">
      <p><b>{{ $t('form.order_id') }}:</b> {{ paymentInfo.order.id }}</p>
      <p><b>{{ $t('form.amount') }}:</b> {{ paymentInfo.order.amount }} {{ paymentInfo.order.currency }}</p>
      <p><b>{{ $t('form.status') }}:</b> {{ $t(paymentInfo.order.status) }}</p>
    </div>

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
import { ref, onMounted } from 'vue';

const route = useRoute();
const router = useRouter();

const paymentInfo = ref(null);
const orderId = localStorage.getItem('order_id');

const goToPayment = () => {
  router.push('/payment');
};

onMounted(async () => {
  if (!orderId) return;

  try {
    const response = await fetch(`/api/payments/result?orderId=${orderId}`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('access_token')}`
      }
    });
    const result = await response.json();
console.log('Result:', result);
    paymentInfo.value = result;
console.log('Payment result:', paymentInfo);

    localStorage.removeItem('order_id');
  } catch (error) {
    console.error('Error fetching payment result:', error);
  }
});
</script>
