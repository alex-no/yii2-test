import { createRouter, createWebHistory } from 'vue-router';
import Payment from '@/pages/Payment.vue';
import PaymentResult from '@/pages/PaymentResult.vue';
import Login from '@/pages/Login.vue';

const routes = [
  { path: '/html/payment', component: Payment },
  { path: '/html/payment/result', component: PaymentResult },
  { path: '/html/login', component: Login }
];

export const router = createRouter({
  history: createWebHistory(),
  routes
});
