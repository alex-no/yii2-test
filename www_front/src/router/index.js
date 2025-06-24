import { createRouter, createWebHistory } from 'vue-router'

import FeaturesList from '@/pages/FeaturesList.vue'
import Login from '@/pages/Login.vue'
import PaymentCreate from '@/pages/PaymentCreate.vue'
import PaymentResult from '@/pages/PaymentResult.vue'
import ChatSocket from '@/pages/ChatSocket.vue'

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
    path: '/payment-create', 
    component: PaymentCreate, 
    meta: { title: 'title.payment' } 
  },
  { 
    path: '/payment-result', 
    component: PaymentResult, 
    meta: { title: 'title.payment_result' } 
  },
  { 
    path: '/payment-success', 
    component: PaymentResult, 
    meta: { title: 'title.payment_result' } 
  },
  { 
    path: '/payment-cancel', 
    component: PaymentResult, 
    meta: { title: 'title.payment_result' } 
  },
  {
    path: '/chat-socket',
    component: ChatSocket,
    meta: { title: 'Chat Socket Test' }
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