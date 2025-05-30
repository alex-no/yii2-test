<template>
  <BaseLayout>
    <div class="max-w-md mx-auto bg-white p-6 rounded-xl shadow-md">
      <h2 class="text-xl font-bold mb-4">Payment Result</h2>
      <p class="text-green-600 font-semibold">Your payment was processed successfully.</p>
    </div>
  </BaseLayout>
</template>

<script setup>
import BaseLayout from '@/layouts/BaseLayout.vue';
</script>


// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router';
import Payment from '@/pages/Payment.vue';
import PaymentResult from '@/pages/PaymentResult.vue';

const routes = [
  { path: '/html/payment', component: Payment },
  { path: '/html/payment/result', component: PaymentResult }
];

export const router = createRouter({
  history: createWebHistory(),
  routes
});
