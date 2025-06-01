<template>
  <form @submit.prevent="handleSubmit" class="max-w-md mx-auto bg-white p-6 rounded-xl shadow-md">
    <h2 class="text-xl font-bold mb-4">{{ $t('form.make_payment') }}</h2>
    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-2" style="width: 140px; margin-right: 10px;">{{ $t('form.amount') }}</label>
      <input v-model="amount" type="number" step="0.01" min="0.01" class="w-full border border-gray-300 p-2 rounded-md" required />
    </div>

    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-2" style="width: 140px; margin-right: 10px;">{{ $t('form.payment_system') }}</label>
      <select v-model="paySystem" class="w-full border border-gray-300 p-2 rounded-md" readonly>
        <option value="liqpay">LiqPay</option>
      </select>
    </div>

    <button
      type="submit"
      class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md w-full border border-green-700"
      style="background-color: #16a34a;"
    >
      {{ $t('form.pay_now') }}
    </button>
  </form>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';

const amount = ref('100.00');
const paySystem = ref('liqpay');
const router = useRouter();

onMounted(() => {
  const token = localStorage.getItem('access_token');
console.log('Access Token:', token);  
  if (!token) {
    //router.replace('/html/login');
  }
});

const handleSubmit = async () => {
  const orderId = `ORD-${new Date().toISOString().replace(/[-:.TZ]/g, '').slice(0, 14)}-${Math.random().toString(36).slice(2, 10)}`;

  const response = await fetch('/api/payments', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      Authorization: `Bearer ${localStorage.getItem('access_token')}`
    },
    body: JSON.stringify({
      amount: amount.value,
      pay_system: paySystem.value,
      order_id: orderId
    })
  });

  const data = await response.json();
  if (data && data.redirect_url && data.fields) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = data.redirect_url;

    for (const key in data.fields) {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = key;
      input.value = data.fields[key];
      form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
  } else {
    alert('Payment initialization failed');
  }
};
</script>
