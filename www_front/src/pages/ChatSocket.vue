<template>
  <div class="chat-page py-4">
    <h2 class="text-center text-lg font-bold mb-3">{{ $t('title.chat_demo') }}</h2>
    <small class="text-gray-600">({{ $t('title.chat_demo_note') }})</small>

    <div class="text-sm text-gray-700 mb-2">
      <span v-for="(user, index) in users" :key="user" class="mr-2">
        <span
          :class="{
            'text-red-600 font-bold': user === username,
            'text-black': user !== username
          }"
        >
          {{ user }}
        </span>
        <span v-if="index !== users.length - 1">,</span>
      </span>
    </div>

    <div class="border rounded-md p-3 h-64 overflow-y-auto bg-white shadow mb-3" style="min-height: 500px;" ref="chatContainer">
      <div v-for="(msg, index) in messages" :key="index" class="mb-1">
        <strong :class="{ 'text-red-600': msg.user === username }">{{ msg.user }}:</strong>
        {{ msg.text }}
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
import { io } from 'socket.io-client'

const socket = io('http://localhost:3000') // заменим позже, если нужно

const messages = ref([])
const message = ref('')
const users = ref([])
const username = ref(null)
const chatContainer = ref(null)

onMounted(() => {
  socket.on('connect', () => {
    console.log('Connected to server')
  })

  socket.on('assign_name', (name) => {
    username.value = name
  })

  socket.on('users_update', (names) => {
    users.value = names
  })

  socket.on('message', (msg) => {
    messages.value.push(msg)
    scrollToBottom()
  })
})

onUnmounted(() => {
  socket.disconnect()
})

function sendMessage() {
  if (message.value.trim()) {
    socket.emit('message', message.value.trim())
    message.value = ''
  }
}

function scrollToBottom() {
  nextTick(() => {
    const container = chatContainer.value
    if (container) {
      container.scrollTop = container.scrollHeight
    }
  })
}
</script>

<style scoped>
.chat-page {
  max-width: 600px;
  margin: 0 auto;
}
</style>
