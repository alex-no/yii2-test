<template>
  <form
    @submit.prevent="handleSubmit"
    class="w-full max-w-md mx-auto bg-white p-6 mt-3 rounded-xl shadow-md"
  >
    <h2 class="text-xl font-bold mb-4 text-center">{{ $t('form.make_payment') }}</h2>

    <div class="mb-4 attention-block text-sm text-gray-700">
      <b>{{ $t('attention') }}</b>
      {{ $t('site_description') }}
    </div>

    <div class="mb-4 flex justify-center items-center">
      <label class="me-3 text-sm text-right font-medium text-gray-700 w-[160px]">
        {{ $t('form.amount') }}
      </label>
      <div class="flex items-center gap-2">
        <input
          v-model="amount"
          type="number"
          step="0.01"
          min="0.01"
          class="border border-gray-300 p-2 rounded-md w-[150px]"
          required
        />
        <input
          type="text"
          :value="currency"
          name="currency"
          readonly
          class="border border-gray-300 p-2 rounded-md w-[70px] text-center bg-gray-100"
        />
      </div>
    </div>

    <div class="mb-4 flex justify-center items-center">
      <label class="me-3 text-sm text-right font-medium text-gray-700 w-[160px]">
        {{ $t('form.payment_system') }}
      </label>
      <select
        v-model="paySystem"
        class="border border-gray-300 p-2 rounded-md w-[220px]"
      >
        <option
          v-for="driver in drivers"
          :key="driver"
          :value="driver"
        >
          {{ $t(`form.${driver}`) }}
        </option>
      </select>
    </div>

    <div class="mb-4 text-center">
      <button
        type="submit"
        class="text-white px-4 py-2 rounded-md border border-green-700"
        style="background-color: #16a34a;"
      >
        {{ $t('form.pay_now') }}
      </button>
    </div>

    <div class="text-center mt-4">
      <a
        href="/html/login"
        class="text-blue-600 hover:text-blue-800 text-sm"
      >
        {{ $t('form.to_authorize') }}
      </a>
    </div>
  </form>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { useRouter } from 'vue-router';

const amount = ref('1.00');
const paySystem = ref('');
const currency = ref('USD');
const drivers = ref([]);
const router = useRouter();

onMounted(async () => {
  const token = localStorage.getItem('access_token');
  if (!token) {
    router.replace('/login?no-auth=1');
    return;
  }

  try {
    const res = await fetch('/api/payments');
    const data = await res.json();
    drivers.value = data.drivers || [];
    paySystem.value = data.default || '';
  } catch (e) {
    console.error('Failed to load payment drivers', e);
  }
});

watch(paySystem, (val) => {
  currency.value = (val === 'liqpay') ? 'UAH' : 'USD';
}, { immediate: true });

const handleSubmit = async () => {
  //const orderId = `ORD-${new Date().toISOString().replace(/[-:.TZ]/g, '').slice(0, 14)}-${Math.random().toString(36).slice(2, 10)}`;
  const orderId = null;

  const response = await fetch('/api/payments/create', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      Authorization: `Bearer ${localStorage.getItem('access_token')}`
    },
    body: JSON.stringify({
      amount: amount.value,
      pay_system: paySystem.value,
      currency: currency.value,
      order_id: orderId
    })
  });

  const result = await response.json();

  const payment = result?.payment;
  if (payment?.action && payment?.method && typeof payment?.data === 'object') {
    if (result?.orderId) {
      localStorage.setItem('order_id', result.orderId);
    }

    const form = document.createElement('form');
    form.method = payment.method || 'POST';
    form.action = payment.action;
    form.acceptCharset = 'utf-8';

    for (const [key, value] of Object.entries(payment.data)) {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = key;
      input.value = value;
      form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
  } else {
    let msg = 'Payment initialization failed.';
    if (result?.name) {
      msg += `\n${result.name}:`;
    }
    if (result?.message) {
      msg += ` ${result.message}`;
    }
    alert(msg);
  }
};
</script>

