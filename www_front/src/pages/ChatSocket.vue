<template>
  <div class="chat-page py-4">
    <h2 class="text-center text-lg font-bold mb-1">{{ $t('title.chat_demo') }}</h2>
    <div class="text-center text-sm text-gray-600 mb-3">({{ $t('title.chat_demo_note') }})</div>

    <div class="text-sm text-gray-700 mb-2">
      <strong>{{ $t('label.users') }}: </strong>
      <span v-for="(user, index) in users" :key="user" class="mr-2">
        <span
          :class="{
            'text-red-600 font-bold': user === username,
            'text-black font-normal': user !== username
          }"
        >
          {{ user }}
        </span>
        <span v-if="index !== users.length - 1">, </span>
      </span>
    </div>

    <div class="border rounded-md p-3 h-64 overflow-y-auto bg-white shadow mb-3" style="min-height: 500px;" ref="chatContainer">
      <div v-for="(msg, index) in messages" :key="index" class="mb-2">
        <strong :class="{ 'text-red-600': msg.user === username }">{{ msg.user }}:</strong>
        <div class="ml-4 whitespace-pre-wrap">{{ msg.text }}</div>
      </div>
    </div>

    <textarea v-model="message" rows="2" class="w-full border rounded p-2 mb-2" :placeholder="$t('form.type_message') + '...'"></textarea>

    <button
      @click="sendMessage"
      class="text-white px-4 py-2 rounded-md border bg-blue-600 hover:bg-blue-700"
      style="background-color: #16a34a;"
    >
      {{ $t('form.send') }}
    </button>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import socketUrl from '@/config/socket.js';

const socket = ref(null)
const messages = ref([])
const message = ref('')
const users = ref([])
const username = ref(null)
const chatContainer = ref(null)

function scrollToBottom() {
  nextTick(() => {
    const container = chatContainer.value
    if (container) {
      container.scrollTop = container.scrollHeight
    }
  })
}

function sendMessage() {
  if (message.value.trim() && socket.value && socket.value.readyState === WebSocket.OPEN) {
    socket.value.send(message.value.trim())
    message.value = ''
  }
}

onMounted(() => {
  socket.value = new WebSocket(socketUrl)

  socket.value.addEventListener('open', () => {
    console.log('Connected to server')
  })

  socket.value.addEventListener('message', (event) => {
    try {
      const data = JSON.parse(event.data)

      switch (data.type) {
        case 'assign_name':
          username.value = data.name
          break
        case 'users_update':
          users.value = data.users
          break
        case 'message':
          messages.value.push({ user: data.user, text: data.text })
          scrollToBottom()
          break
      }
    } catch (e) {
      console.error('Invalid message format', e)
    }
  })

  socket.value.addEventListener('close', () => {
    console.log('Disconnected from server')
  })

  socket.value.addEventListener('error', (error) => {
    console.error('WebSocket error:', error)
  })
})

onUnmounted(() => {
  if (socket.value) {
    socket.value.close()
    socket.value = null
  }
})
</script>

<style scoped>
.chat-page {
  max-width: 600px;
  margin: 0 auto;
}
</style>
