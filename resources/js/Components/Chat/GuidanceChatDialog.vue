<script setup>
import { ref, watch, onUnmounted, computed, nextTick, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'
import {
  Sheet,
  SheetContent,
  SheetHeader,
  SheetTitle,
} from '@/Components/ui/sheet'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Send, MessageSquare, Loader2, Check, CheckCheck, CheckCircle2, AlertCircle } from 'lucide-vue-next'

const props = defineProps({
  open: {
    type: Boolean,
    default: false
  },
  studentId: {
    type: [Number, String],
    required: true
  },
  studentName: {
    type: String,
    default: ''
  },
  dpaName: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:open', 'messageSent'])

const page = usePage()
const messages = ref([])
const newMessage = ref('')
const isLoading = ref(false)
const isSending = ref(false)
const messagesContainer = ref(null)
const isWindowFocused = ref(true)
const onlineUsers = ref([])
let channel = null

// Toast Notification State
const showToast = ref(false)
const toastMessage = ref('')
const toastType = ref('success')

const triggerToast = (message, type = 'success') => {
  toastMessage.value = message
  toastType.value = type
  showToast.value = true
  setTimeout(() => {
    showToast.value = false
  }, 3000)
}

const isDosen = computed(() => page.props.auth?.user?.role === 'Dosen')
const currentUser = computed(() => page.props.auth?.user)

const activeChatPartnerName = computed(() => {
  return isDosen.value ? props.studentName : (props.dpaName || 'Dosen Pembimbing Akademik')
})

const isSentByMe = (msg) => {
  if (!msg || !msg.sender_type) return false
  const senderType = msg.sender_type
  if (isDosen.value) {
    return senderType.includes('Dosen')
  } else {
    return senderType.includes('Mahasiswa')
  }
}

const scrollToBottom = () => {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
    }
  })
}

const fetchMessages = async (shouldScroll = false) => {
  if (!props.studentId) return
  isLoading.value = true
  try {
    const response = await axios.get('/presensi/pemberitahuan/show-guidance', {
      params: { student_id: props.studentId }
    })
    messages.value = response.data
    if (shouldScroll) {
      scrollToBottom()
    }
  } catch (err) {
    console.error('Failed to fetch guidance messages:', err)
  } finally {
    isLoading.value = false
  }
}

const markAsRead = async (messageIds) => {
  if (!messageIds.length) return
  try {
    await axios.post('/presensi/pemberitahuan/mark-guidance-read', {
      message_ids: messageIds,
      student_id: props.studentId
    })
  } catch (err) {
    console.error('Failed to mark guidance messages as read:', err)
  }
}

const sendMessage = async () => {
  if (!newMessage.value.trim() || isSending.value) return

  const messageText = newMessage.value.trim()
  newMessage.value = ''
  isSending.value = true

  try {
    const payload = {
      message: messageText,
      student_id: props.studentId
    }

    const response = await axios.post('/presensi/pemberitahuan/send-guidance', payload)

    const exists = messages.value.some(m => m.id === response.data.data.id)
    if (!exists) {
      messages.value.push(response.data.data)
      scrollToBottom()
      emit('messageSent')
    }
    triggerToast('Pesan berhasil dikirim.', 'success')
  } catch (err) {
    console.error('Failed to send guidance message:', err)
    newMessage.value = messageText
    const errorMsg = err.response?.data?.message || 'Gagal mengirim pesan. Silakan coba lagi.'
    triggerToast(errorMsg, 'error')
  } finally {
    isSending.value = false
  }
}

const startListening = () => {
  if (!props.studentId) return
  stopListening()

  console.log('--- JOINING GUIDANCE CHANNEL: guidance.' + props.studentId + ' ---')

  channel = window.Echo.join(`guidance.${props.studentId}`)
    .here((users) => {
      onlineUsers.value = users
    })
    .joining((user) => {
      const exists = onlineUsers.value.some(u => u.id === user.id && u.role === user.role)
      if (!exists) {
        onlineUsers.value.push(user)
      }
    })
    .leaving((user) => {
      onlineUsers.value = onlineUsers.value.filter(u => !(u.id === user.id && u.role === user.role))
    })
    .listen('.MessageSent', (e) => {
      const messageData = e.message || e
      
      const exists = messages.value.some(m => m.id === messageData.id)
      if (!exists) {
        messages.value.push(messageData)
        scrollToBottom()
        emit('messageSent')

        if (props.open && isWindowFocused.value) {
          markAsRead([messageData.id])
          messageData.read = true
        }
      }
    })
    .listen('.MessageRead', (e) => {
      const { message_ids } = e
      const idSet = new Set(message_ids.map(id => Number(id)))
      
      messages.value.forEach(m => {
        if (idSet.has(Number(m.id))) {
          m.read = true
        }
      })
    })
}

const stopListening = () => {
  if (channel) {
    window.Echo.leave(`guidance.${props.studentId}`)
    channel = null
    onlineUsers.value = []
  }
}

const handleFocus = () => {
  isWindowFocused.value = true
  if (props.open && messages.value.length > 0) {
    const unreadIds = messages.value
      .filter(m => !m.read && !isSentByMe(m))
      .map(m => m.id)
    if (unreadIds.length > 0) {
      markAsRead(unreadIds)
      messages.value.forEach(m => {
        if (unreadIds.includes(m.id)) {
          m.read = true
        }
      })
    }
  }
}

const handleBlur = () => {
  isWindowFocused.value = false
}

watch(() => props.open, (newOpen) => {
  if (newOpen) {
    fetchMessages(true).then(() => {
      const unreadIds = messages.value
        .filter(m => !m.read && !isSentByMe(m))
        .map(m => m.id)
      if (unreadIds.length > 0) {
        markAsRead(unreadIds)
        messages.value.forEach(m => {
          if (unreadIds.includes(m.id)) {
            m.read = true
          }
        })
      }
    })
    startListening()
  } else {
    stopListening()
  }
})

watch(() => props.studentId, () => {
  if (props.open) {
    fetchMessages(true)
    startListening()
  }
})

onMounted(() => {
  window.addEventListener('focus', handleFocus)
  window.addEventListener('blur', handleBlur)
  if (props.open) {
    fetchMessages(true)
    startListening()
  }
})

onUnmounted(() => {
  window.removeEventListener('focus', handleFocus)
  window.removeEventListener('blur', handleBlur)
  stopListening()
})
</script>

<template>
  <Sheet :open="open" @update:open="emit('update:open', $event)">
    <SheetContent class="w-full sm:max-w-[480px] p-0 flex flex-col h-full border-l border-[#CDD1E1]">
      <SheetHeader class="bg-[#4B49AC] text-white p-5 flex flex-col shrink-0 gap-1.5 shadow-md">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center font-bold text-lg uppercase tracking-wide border border-white/20">
            {{ activeChatPartnerName.substring(0, 2) }}
          </div>
          <div class="flex-1 min-w-0">
            <SheetTitle class="text-white text-base font-bold truncate leading-tight flex items-center gap-2">
              {{ activeChatPartnerName }}
            </SheetTitle>
            <span class="text-[11px] text-white/80 font-medium">
              {{ isDosen ? 'Mahasiswa Bimbingan' : 'Dosen Pembimbing Akademik' }}
            </span>
          </div>
        </div>
      </SheetHeader>

      <div 
        ref="messagesContainer"
        class="flex-1 overflow-y-auto p-5 space-y-4 bg-slate-50/50 custom-scrollbar"
      >
        <div v-if="isLoading" class="flex flex-col items-center justify-center h-full space-y-3">
          <Loader2 class="w-8 h-8 text-[#4B49AC] animate-spin" />
          <span class="text-xs text-slate-400 font-semibold tracking-wider uppercase">Memuat percakapan...</span>
        </div>

        <div v-else-if="messages.length === 0" class="flex flex-col items-center justify-center h-full text-center p-6">
          <div class="w-14 h-14 bg-white shadow-sm rounded-2xl flex items-center justify-center mb-4 text-[#4B49AC]/40 border border-[#4B49AC]/5">
            <MessageSquare class="w-6 h-6" />
          </div>
          <h4 class="text-sm font-bold text-slate-700">Mulai Percakapan</h4>
          <p class="text-xs text-slate-400 max-w-[240px] mt-1 leading-relaxed">
            Kirimkan pesan pertama Anda untuk memulai konsultasi bimbingan akademik.
          </p>
        </div>

        <div 
          v-else 
          v-for="msg in messages" 
          :key="msg.id"
          :class="['flex w-full', isSentByMe(msg) ? 'justify-end' : 'justify-start']"
        >
          <div class="max-w-[80%] flex flex-col">
            <div :class="[
              'p-3.5 rounded-2xl shadow-sm leading-relaxed text-sm',
              isSentByMe(msg) 
                ? 'bg-[#4B49AC] text-white rounded-tr-none' 
                : 'bg-white text-slate-800 border border-slate-100 rounded-tl-none'
            ]">
              <p class="whitespace-pre-wrap text-left">{{ msg.message }}</p>
            </div>
            
            <div :class="[
              'flex items-center gap-1 mt-1 text-[10px] text-slate-400 px-1',
              isSentByMe(msg) ? 'justify-end' : 'justify-start'
            ]">
              <span>{{ new Date(msg.sent_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}</span>
              <template v-if="isSentByMe(msg)">
                <CheckCheck v-if="msg.read" class="w-3.5 h-3.5 text-[#4B49AC]" />
                <Check v-else class="w-3.5 h-3.5 text-slate-400" />
              </template>
            </div>
          </div>
        </div>
      </div>

      <form
        @submit.prevent="sendMessage"
        class="p-4 border-t border-slate-100 bg-white shrink-0 flex items-center gap-3"
      >
        <Input
          v-model="newMessage"
          type="text"
          placeholder="Tulis pesan konsultasi..."
          class="flex-1 h-11 border-slate-200 focus-visible:ring-[#4B49AC] rounded-xl text-sm"
          :disabled="isSending"
        />
        <Button
          type="submit"
          class="h-11 w-11 rounded-xl bg-[#4B49AC] hover:bg-[#4b49ac]/90 text-white shrink-0 p-0 shadow-md transition-all active:scale-95"
          :disabled="isSending || !newMessage.trim()"
        >
          <Loader2 v-if="isSending" class="w-4 h-4 animate-spin" />
          <Send v-else class="w-4 h-4" />
        </Button>
      </form>

      <!-- Self-contained Toast Notification inside Sheet -->
      <transition name="toast">
        <div v-if="showToast" class="absolute bottom-20 left-1/2 -translate-x-1/2 z-[150] w-[90%] max-w-[320px]">
          <div class="bg-slate-900/95 text-white px-4 py-3 rounded-xl shadow-2xl flex items-center gap-2.5 border border-slate-800 backdrop-blur-sm">
            <div :class="toastType === 'error' ? 'bg-red-500' : 'bg-green-500'" class="p-1 rounded-full shrink-0 flex items-center justify-center animate-pulse">
              <CheckCircle2 v-if="toastType === 'success'" class="w-3 h-3 text-white" />
              <AlertCircle v-else class="w-3 h-3 text-white" />
            </div>
            <span class="text-xs font-semibold leading-tight">{{ toastMessage }}</span>
          </div>
        </div>
      </transition>
    </SheetContent>
  </Sheet>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #E2E8F0;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #CBD5E1;
}

.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translate(-50%, 10px) scale(0.95);
}
</style>
