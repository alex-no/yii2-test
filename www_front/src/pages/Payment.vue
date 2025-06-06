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
      <input
        v-model="amount"
        type="number"
        step="0.01"
        min="0.01"
        class="border border-gray-300 p-2 rounded-md w-[150px]"
        required
      />
    </div>

    <div class="mb-4 flex justify-center items-center">
      <label class="me-3 text-sm text-right font-medium text-gray-700 w-[160px]">
        {{ $t('form.payment_system') }}
      </label>
      <select
        v-model="paySystem"
        class="border border-gray-300 p-2 rounded-md w-[150px]"
        readonly
      >
        <option value="liqpay">LiqPay</option>
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
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';

const amount = ref('1.00');
const paySystem = ref('liqpay');
const router = useRouter();

onMounted(() => {
  const token = localStorage.getItem('access_token');
  if (!token) {
    router.replace('/login?no-auth=1');
  }
});

const handleSubmit = async () => {
  //const orderId = `ORD-${new Date().toISOString().replace(/[-:.TZ]/g, '').slice(0, 14)}-${Math.random().toString(36).slice(2, 10)}`;
  const orderId = null;

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

  const result = await response.json();

  const payment = result?.payment;
  if (payment?.action && payment?.data && payment?.signature) {
    if (result?.orderId) {
      localStorage.setItem('order_id', result.orderId);
    }
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = payment.action;
    form.acceptCharset = 'utf-8';

    const dataInput = document.createElement('input');
    dataInput.type = 'hidden';
    dataInput.name = 'data';
    dataInput.value = payment.data;
    form.appendChild(dataInput);

    const signatureInput = document.createElement('input');
    signatureInput.type = 'hidden';
    signatureInput.name = 'signature';
    signatureInput.value = payment.signature;
    form.appendChild(signatureInput);

    document.body.appendChild(form);
    form.submit();
  } else {
    let msg = 'Payment initialization failed. ';
    if (result?.name) {
      msg += `\n${result.name}: `;
    }
    if (result?.message) {
      msg += result.message;
    }
    alert(msg);
  }
};
</script>
