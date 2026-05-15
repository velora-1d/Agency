<template>
  <div class="fixed bottom-6 right-6 z-[100]">
    <!-- Chat Toggle Button -->
    <button
      @click="toggleChat"
      class="flex h-14 w-14 items-center justify-center rounded-full bg-stone-950 text-white shadow-2xl transition-transform hover:scale-110 active:scale-95"
      :class="{ 'rotate-90': isOpen }"
    >
      <MessageSquare v-if="!isOpen" class="h-6 w-6" />
      <X v-else class="h-6 w-6" />
    </button>

    <!-- Chat Window -->
    <Transition name="slide-up">
      <div
        v-if="isOpen"
        class="absolute bottom-20 right-0 w-[22rem] overflow-hidden rounded-[2rem] border border-stone-200 bg-white shadow-[0_20px_60px_rgba(28,25,23,0.15)] md:w-[26rem]"
      >
        <!-- Header -->
        <div class="bg-stone-950 p-6 text-white">
          <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 backdrop-blur-sm">
              <Bot class="h-6 w-6" />
            </div>
            <div>
              <h3 class="text-sm font-bold tracking-tight">AI Assistant</h3>
              <p class="text-[10px] font-medium text-stone-400 uppercase tracking-widest">Kantor Digital Helper</p>
            </div>
          </div>
        </div>

        <!-- Messages Area -->
        <div ref="messageContainer" class="h-96 overflow-y-auto p-6 space-y-4 bg-stone-50/50">
          <div v-if="messages.length === 0" class="text-center py-10 px-4">
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-stone-100 text-stone-400 mb-4">
              <Sparkles class="h-6 w-6" />
            </div>
            <p class="text-sm font-medium text-stone-950">Halo! Saya asisten AI Kantor Digital.</p>
            <p class="text-xs text-stone-500 mt-2 leading-relaxed">Tanyakan apa saja seputar fitur, menu, atau cara penggunaan sistem ini.</p>
          </div>

          <div
            v-for="(msg, index) in messages"
            :key="index"
            :class="['flex', msg.role === 'user' ? 'justify-end' : 'justify-start']"
          >
            <div
              :class="[
                'max-w-[85%] rounded-[1.4rem] px-4 py-3 text-sm leading-relaxed shadow-sm',
                msg.role === 'user' 
                  ? 'bg-stone-950 text-white rounded-br-none' 
                  : 'bg-white border border-stone-100 text-stone-800 rounded-bl-none'
              ]"
            >
              <div class="whitespace-pre-wrap">{{ msg.content }}</div>
            </div>
          </div>

          <div v-if="isLoading" class="flex justify-start">
            <div class="bg-white border border-stone-100 rounded-[1.4rem] rounded-bl-none px-4 py-3 shadow-sm">
              <div class="flex gap-1">
                <span class="h-1.5 w-1.5 rounded-full bg-stone-300 animate-bounce"></span>
                <span class="h-1.5 w-1.5 rounded-full bg-stone-300 animate-bounce [animation-delay:0.2s]"></span>
                <span class="h-1.5 w-1.5 rounded-full bg-stone-300 animate-bounce [animation-delay:0.4s]"></span>
              </div>
            </div>
          </div>
        </div>

        <!-- Input Area -->
        <div class="p-4 bg-white border-t border-stone-100">
          <div class="relative flex items-center">
            <input
              v-model="input"
              @keydown.enter="sendMessage"
              placeholder="Tanya sesuatu..."
              class="w-full rounded-2xl border border-stone-200 bg-stone-50 py-3 pl-4 pr-12 text-sm outline-none transition-all focus:border-stone-400 focus:bg-white focus:ring-4 focus:ring-stone-100"
              :disabled="isLoading"
            />
            <button
              @click="sendMessage"
              :disabled="!input.trim() || isLoading"
              class="absolute right-2 h-9 w-9 flex items-center justify-center rounded-xl bg-stone-950 text-white transition hover:bg-stone-800 disabled:opacity-40"
            >
              <ArrowUp class="h-4 w-4" />
            </button>
          </div>
          <p class="text-[9px] text-center text-stone-400 mt-3 uppercase font-bold tracking-widest">Powered by 9Router & Claude</p>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, nextTick, watch } from 'vue'
import { MessageSquare, X, Bot, Sparkles, ArrowUp } from 'lucide-vue-next'
import axios from 'axios'
import { usePage } from '@inertiajs/vue3'

const isOpen = ref(false)
const input = ref('')
const isLoading = ref(false)
const messages = ref([])
const messageContainer = ref(null)

const page = usePage()
const workspace = page.props.workspace

function toggleChat() {
  isOpen.value = !isOpen.value
}

async function sendMessage() {
  if (!input.value.trim() || isLoading.value) return

  const userMessage = input.value
  messages.value.push({ role: 'user', content: userMessage })
  input.value = ''
  isLoading.value = true

  await scrollToBottom()

  try {
    const response = await axios.post(`/api/v1/w/${workspace.slug}/ai-assistant/chat`, {
      prompt: userMessage
    })

    if (response.data.success) {
      messages.value.push({ role: 'assistant', content: response.data.answer })
    }
  } catch (error) {
    messages.value.push({ 
      role: 'assistant', 
      content: 'Maaf, terjadi kesalahan saat menghubungi asisten AI. Pastikan 9Router kamu menyala.' 
    })
  } finally {
    isLoading.value = false
    await scrollToBottom()
  }
}

async function scrollToBottom() {
  await nextTick()
  if (messageContainer.value) {
    messageContainer.value.scrollTop = messageContainer.value.scrollHeight
  }
}

watch(isOpen, (val) => {
  if (val) scrollToBottom()
})
</script>

<style scoped>
.slide-up-enter-active,
.slide-up-leave-active {
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.slide-up-enter-from,
.slide-up-leave-to {
  opacity: 0;
  transform: translateY(20px) scale(0.95);
}

::-webkit-scrollbar {
  width: 4px;
}
::-webkit-scrollbar-thumb {
  background: #e7e5e4;
  border-radius: 10px;
}
</style>
