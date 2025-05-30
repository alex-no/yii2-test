import { createRouter, createWebHistory } from 'vue-router'

import FeaturesList from '@/pages/FeaturesList.vue'
import Login from '@/pages/Login.vue'
import Payment from '@/pages/Payment.vue'
import PaymentResult from '@/pages/PaymentResult.vue'

const routes = [
  { path: '/features', component: FeaturesList },
  { path: '/login', component: Login },
  { path: '/payment', component: Payment },
  { path: '/payment-result', component: PaymentResult },
  { path: '/', redirect: '/features' },
]

const router = createRouter({
  history: createWebHistory('/html/'),
  routes,
})

export default router