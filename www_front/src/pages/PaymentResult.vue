<template>
  <div class="max-w-md mx-auto bg-white p-6 mt-3 rounded-xl shadow-md text-center">
    <h2 class="text-xl font-bold mb-4">{{ $t('form.payment_result') }}</h2>
    <div class="mb-4 attention-block">
      <b>{{ $t('attention') }}</b>
      {{ $t('site_description') }}
    </div>

    <div v-if="paymentInfo" class="w-[400px] mx-auto text-left mt-4 mb-4">
      <p><b>{{ $t('form.order_id') }}:</b> {{ paymentInfo.order_id }}</p>
      <p><b>{{ $t('form.amount') }}:</b> {{ paymentInfo.amount }} {{ paymentInfo.currency }}</p>
      <p><b>{{ $t('form.status') }}:</b> {{ $t(`payment_status.${paymentInfo.status}`) }}</p>
    </div>

    <div v-if="errorMessage" class="w-[800px] mx-auto mb-4">
      <div class="border text-left px-4 py-3 ps-4 rounded" style="color: #b91c1c; background-color: #fee2e2; border-color: #b91c1c;">
        {{ errorMessage }}
      </div>
    </div>

    <button
      type="submit"
      class="text-white px-4 py-2 rounded-md border"
      style="background-color: #16a34a;" 
      @click="goToPayment"
    >
      {{ $t('form.another_payment') }}
    </button>
  </div>
</template>

<script setup>
import { useRoute, useRouter } from 'vue-router';
import { ref, onMounted } from 'vue';

const route = useRoute();
const router = useRouter();

const paymentInfo = ref(null);
const errorMessage = ref(null);
const orderId = localStorage.getItem('order_id');
// const orderId = 'ORD-20250604-015012-xxCOhE';

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

    if (!response.ok) {
      // Server returned an error
      errorMessage.value = result.message || t('unexpected_error');
      return;
    }

    paymentInfo.value = result.order;
    localStorage.removeItem('order_id');

  } catch (error) {
    console.error('Error fetching payment result:', error);
    errorMessage.value = t('network_error');
  }
});
</script>

