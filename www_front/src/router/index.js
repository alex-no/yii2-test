import { createRouter, createWebHistory } from 'vue-router'

import FeaturesList from '@/pages/FeaturesList.vue'
import Login from '@/pages/Login.vue'
import Payment from '@/pages/Payment.vue'
import PaymentResult from '@/pages/PaymentResult.vue'

const routes = [
  { 
    path: '/features', 
    component: FeaturesList, 
    meta: { title: 'title.features' } 
  },
  { 
    path: '/login', 
    component: Login, 
    meta: { title: 'title.login' } 
  },
  { 
    path: '/payment', 
    component: Payment, 
    meta: { title: 'title.payment' } 
  },
  { 
    path: '/payment-result', 
    component: PaymentResult, 
    meta: { title: 'title.payment_result' } 
  },
  { 
    path: '/', 
    redirect: '/features' 
  },
]

const router = createRouter({
  history: createWebHistory('/html/'),
  routes,
})

export default router