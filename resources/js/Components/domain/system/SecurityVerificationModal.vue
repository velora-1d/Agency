<template>
  <Transition name="modal">
    <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center bg-stone-950/80 p-4 backdrop-blur-md">
      <div class="modal-panel w-full max-w-md overflow-hidden rounded-[2.5rem] border border-stone-800 bg-[#1c1917] p-8 shadow-2xl text-white">
        <div class="text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-amber-500/10 text-amber-500 mb-6">
                <ShieldCheck class="h-8 w-8" />
            </div>
            <h2 class="text-2xl font-bold tracking-tight">Verifikasi Keamanan</h2>
            <p class="mt-2 text-sm text-stone-400">Pindah ke workspace <strong>{{ targetWorkspaceName }}</strong> membutuhkan verifikasi ulang.</p>
        </div>

        <div class="mt-8 space-y-6">
            <!-- Mode Toggle (PIN / Password) -->
            <div class="flex p-1 bg-stone-900 rounded-2xl">
                <button 
                    @click="mode = 'pin'"
                    class="flex-1 py-2 text-xs font-bold uppercase tracking-widest rounded-xl transition-all"
                    :class="mode === 'pin' ? 'bg-stone-800 text-white shadow-sm' : 'text-stone-500 hover:text-stone-300'"
                >
                    PIN 6 Angka
                </button>
                <button 
                    @click="mode = 'password'"
                    class="flex-1 py-2 text-xs font-bold uppercase tracking-widest rounded-xl transition-all"
                    :class="mode === 'password' ? 'bg-stone-800 text-white shadow-sm' : 'text-stone-500 hover:text-stone-300'"
                >
                    Password Utama
                </button>
            </div>

            <!-- PIN Input -->
            <div v-if="mode === 'pin'" class="space-y-4">
                <div class="flex justify-center gap-3">
                    <input 
                        v-for="i in 6" 
                        :key="i"
                        :ref="el => pinRefs[i-1] = el"
                        v-model="pinArray[i-1]"
                        type="password"
                        maxlength="1"
                        class="w-12 h-14 text-center text-2xl font-bold bg-stone-900 border border-stone-800 rounded-2xl focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none transition-all"
                        @input="handlePinInput(i-1)"
                        @keydown.delete="handlePinDelete(i-1)"
                    />
                </div>
            </div>

            <!-- Password Input -->
            <div v-else class="space-y-2">
                <input 
                    v-model="passwordValue"
                    type="password"
                    placeholder="Masukkan password akun lu..."
                    class="w-full bg-stone-900 border border-stone-800 rounded-2xl px-5 py-4 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none transition-all"
                />
            </div>

            <p v-if="errorMessage" class="text-center text-xs text-rose-500 font-medium">{{ errorMessage }}</p>

            <div class="space-y-3">
                <button 
                    @click="verify"
                    :disabled="isProcessing"
                    class="w-full py-4 bg-amber-500 hover:bg-amber-600 disabled:opacity-50 disabled:cursor-not-allowed text-stone-950 font-bold rounded-2xl transition-all flex items-center justify-center gap-2"
                >
                    <span v-if="isProcessing" class="h-4 w-4 border-2 border-stone-950 border-t-transparent rounded-full animate-spin"></span>
                    <span>{{ isProcessing ? 'Memverifikasi...' : 'Konfirmasi Verifikasi' }}</span>
                </button>

                <button 
                    @click="verifyBiometric"
                    type="button"
                    class="w-full py-4 bg-stone-900 hover:bg-stone-800 text-white font-bold rounded-2xl transition-all flex items-center justify-center gap-2 border border-stone-800"
                >
                    <Fingerprint class="h-5 w-5 text-amber-500" />
                    <span>Gunakan Fingerprint</span>
                </button>

                <button 
                    @click="$emit('close')"
                    class="w-full py-3 text-stone-500 hover:text-stone-300 text-sm font-semibold transition-all"
                >
                    Batalkan
                </button>
            </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import { ShieldCheck, Fingerprint } from 'lucide-vue-next'

const props = defineProps({
  show: Boolean,
  targetWorkspaceSlug: String,
  targetWorkspaceName: String,
  workspaceSlug: String
})

const emit = defineEmits(['close', 'success'])

const mode = ref('pin')
const pinArray = ref(['', '', '', '', '', ''])
const pinRefs = ref([])
const passwordValue = ref('')
const isProcessing = ref(false)
const errorMessage = ref('')

function handlePinInput(index) {
    if (pinArray.value[index] && index < 5) {
        pinRefs.value[index + 1].focus()
    }
    if (pinArray.value.every(v => v !== '')) {
        verify()
    }
}

function handlePinDelete(index) {
    if (!pinArray.value[index] && index > 0) {
        pinRefs.value[index - 1].focus()
    }
}

async function verify() {
    isProcessing.value = true
    errorMessage.value = ''
    
    const value = mode.value === 'pin' ? pinArray.value.join('') : passwordValue.value
    
    // Ambil token CSRF dari cookie XSRF-TOKEN (Laravel standard)
    const xsrfToken = document.cookie
        .split('; ')
        .find(row => row.startsWith('XSRF-TOKEN='))
        ?.split('=')[1]

    try {
        const response = await fetch(`/app/verification/verify`, {
            method: 'POST',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(xsrfToken || ''),
            },
            body: JSON.stringify({
                type: mode.value,
                value: value
            })
        })
        
        const data = await response.json()
        
        if (response.ok && data.success) {
            emit('success')
        } else {
            errorMessage.value = data.message || 'Verifikasi gagal. Coba lagi.'
            if (mode.value === 'pin') pinArray.value = ['', '', '', '', '', '']
        }
    } catch (error) {
        errorMessage.value = 'Terjadi kesalahan sistem. Coba lagi.'
        if (mode.value === 'pin') pinArray.value = ['', '', '', '', '', '']
    } finally {
        isProcessing.value = false
    }
}

async function verifyBiometric() {
    // Simulasi Biometric
    // Di real apps, ini pake navigator.credentials.get()
    isProcessing.value = true
    setTimeout(() => {
        emit('success')
        isProcessing.value = false
    }, 1000)
}

onMounted(() => {
    nextTick(() => {
        if (pinRefs.value[0]) pinRefs.value[0].focus()
    })
})
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-panel {
  animation: modal-slide 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes modal-slide {
  from {
    transform: translateY(20px) scale(0.95);
    opacity: 0;
  }
  to {
    transform: translateY(0) scale(1);
    opacity: 1;
  }
}
</style>
