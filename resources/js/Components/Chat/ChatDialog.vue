<script setup>
import { ref, watch, onUnmounted, computed, nextTick } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
} from '@/Components/ui/dialog'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/Components/ui/select'
import { Badge } from '@/Components/ui/badge'
import { Send, MessageSquare, Loader2 } from 'lucide-vue-next'

const props = defineProps({
  open: {
    type: Boolean,
    default: false
  },
  jadwalId: {
    type: [Number, String],
    required: true
  },
  dosenName: {
    type: String,
    default: ''
  },
  matkulName: {
    type: String,
    default: ''
  },
  kelasName: {
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
const contacts = ref([])
const selectedContactKey = ref('') // Format: "type_id"
const messagesContainer = ref(null)
let pollingTimer = null

const isDosen = computed(() => page.props.auth?.user?.role === 'Dosen')
const currentUser = computed(() => page.props.auth?.user)

// Get formatted namespace from role
const getSenderTypeNamespace = (role) => {
  const map = {
    'Dosen': 'App\\Models\\Dosen',
    'Kaprodi': 'App\\Models\\Kaprodi',
    'Wadir': 'App\\Models\\Wadir',
    'Direktur': 'App\\Models\\Direktur'
  }
  return map[role] || 'App\\Models\\Dosen'
}

// Check who sent the message
const isSentByMe = (msg) => {
  if (isDosen.value) {
    return msg.sender_type === 'App\\Models\\Dosen'
  } else {
    return msg.sender_type !== 'App\\Models\\Dosen'
  }
}

// Find current active contact object
const activeContact = computed(() => {
  if (!selectedContactKey.value) return null
  const [type, id] = selectedContactKey.value.split('::')
  return contacts.value.find(c => c.type === type && c.id == id) || null
})

// Auto-scroll chat area
const scrollToBottom = async () => {
  await nextTick()
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

// Fetch available leaders contacts (for Dosen only)
const fetchContacts = async () => {
  try {
    isLoading.value = true
    const response = await axios.get('/presensi/pemberitahuan/contacts-dosen', {
      params: { jadwal_id: props.jadwalId }
    })
    contacts.value = response.data
    
    if (contacts.value.length > 0) {
      // Find one with messages, or Kaprodi, or default to first
      const kaprodiContact = contacts.value.find(c => c.role_label === 'Kaprodi')
      const defaultContact = kaprodiContact || contacts.value[0]
      selectedContactKey.value = `${defaultContact.type}::${defaultContact.id}`
    } else {
      selectedContactKey.value = ''
    }
  } catch (err) {
    console.error('Failed to fetch chat contacts:', err)
  } finally {
    isLoading.value = false
  }
}

// Load messages log
const fetchMessages = async (showLoading = false) => {
  if (showLoading) isLoading.value = true
  try {
    if (isDosen.value) {
      if (!activeContact.value) return
      const response = await axios.get('/presensi/pemberitahuan/showWhereDosen', {
        params: {
          jadwal_id: props.jadwalId,
          sender_id: activeContact.value.id,
          sender_type: activeContact.value.type
        }
      })
      messages.value = response.data
    } else {
      const response = await axios.get('/presensi/pemberitahuan/show', {
        params: { jadwal_id: props.jadwalId }
      })
      messages.value = response.data
    }
    scrollToBottom()
  } catch (err) {
    console.error('Failed to fetch messages:', err)
  } finally {
    if (showLoading) isLoading.value = false
  }
}

// Send a new message
const handleSendMessage = async () => {
  if (!newMessage.value.trim() || isSending.value) return
  
  isSending.value = true
  try {
    const payload = {
      message: newMessage.value.trim(),
      jadwal_id: props.jadwalId,
      sender_id: currentUser.value.id,
      sender_type: currentUser.value.role // The backend normalizes the type or we can send namespace
    }

    // Explicitly map sender_type for backend validator
    payload.sender_type = currentUser.value.role === 'Wadir' ? 'Wadir' : 
                          currentUser.value.role === 'Direktur' ? 'Direktur' : 
                          currentUser.value.role === 'Kaprodi' ? 'Kaprodi' : 'Dosen'

    if (isDosen.value) {
      if (!activeContact.value) return
      payload.receiver_id = activeContact.value.id
      payload.receiver_type = activeContact.value.type
    }

    const response = await axios.post('/presensi/pemberitahuan/send', payload)
    
    if (response.data) {
      newMessage.value = ''
      await fetchMessages(false)
      emit('messageSent')
    }
  } catch (err) {
    console.error('Failed to send message:', err)
  } finally {
    isSending.value = false
  }
}

// Polling setup
const startPolling = () => {
  stopPolling()
  pollingTimer = setInterval(() => {
    fetchMessages(false)
  }, 10000) // Poll every 10 seconds
}

const stopPolling = () => {
  if (pollingTimer) {
    clearInterval(pollingTimer)
    pollingTimer = null
  }
}

// Listen to modal opening
watch(() => props.open, (newOpen) => {
  if (newOpen) {
    if (isDosen.value) {
      fetchContacts().then(() => {
        if (selectedContactKey.value) {
          fetchMessages(true)
        }
      })
    } else {
      fetchMessages(true)
    }
    startPolling()
  } else {
    stopPolling()
    messages.value = []
    contacts.value = []
    selectedContactKey.value = ''
  }
})

// Listen to contact switch
watch(selectedContactKey, (newVal) => {
  if (newVal && isDosen.value) {
    fetchMessages(true)
  }
})

onUnmounted(() => {
  stopPolling()
})

const formatTime = (timeStr) => {
  if (!timeStr) return ''
  const date = new Date(timeStr)
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}
</script>

<template>
  <Dialog :open="open" @update:open="emit('update:open', $event)">
    <DialogContent class="sm:max-w-[500px] h-[80vh] flex flex-col p-0 overflow-hidden rounded-2xl border border-slate-100 shadow-2xl bg-white">
      
      <!-- Header -->
      <DialogHeader class="bg-[#4B49AC] text-white p-5 flex flex-col gap-1 flex-shrink-0">
        <div class="flex items-center gap-2">
          <MessageSquare class="w-5 h-5" />
          <DialogTitle class="text-white text-base font-bold">Diskusi Akademik</DialogTitle>
        </div>
        <div class="text-[11px] text-indigo-100 font-medium">
          Matkul: {{ matkulName || '-' }} <span class="mx-1">•</span> Kelas: {{ kelasName || '-' }}
          <span v-if="!isDosen"><span class="mx-1">•</span> Dosen: {{ dosenName || '-' }}</span>
        </div>
      </DialogHeader>

      <!-- Contact Selector (Dosen Role only) -->
      <div v-if="isDosen && contacts.length > 1" class="px-4 py-2 bg-slate-50 border-b border-slate-100 flex items-center justify-between gap-3 flex-shrink-0">
        <span class="text-xs font-semibold text-slate-500">Hubungi Pimpinan:</span>
        <Select v-model="selectedContactKey">
          <SelectTrigger class="w-[200px] h-8 text-xs font-medium border-slate-200 shadow-none bg-white rounded-lg">
            <SelectValue placeholder="Pilih Pimpinan" />
          </SelectTrigger>
          <SelectContent class="bg-white rounded-lg border border-slate-150 shadow-lg">
            <SelectItem 
              v-for="contact in contacts" 
              :key="contact.type + '_' + contact.id" 
              :value="`${contact.type}::${contact.id}`"
              class="text-xs focus:bg-indigo-50 focus:text-indigo-950"
            >
              {{ contact.nama }} ({{ contact.role_label }})
            </SelectItem>
          </SelectContent>
        </Select>
      </div>

      <!-- Messages Body -->
      <div 
        ref="messagesContainer"
        class="flex-1 overflow-y-auto p-5 space-y-4 bg-slate-50/50 flex flex-col"
      >
        <!-- Loading State -->
        <div v-if="isLoading && messages.length === 0" class="flex-1 flex flex-col items-center justify-center gap-2 text-slate-400">
          <Loader2 class="w-8 h-8 animate-spin text-[#4B49AC]" />
          <p class="text-xs font-medium">Memuat percakapan...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="messages.length === 0" class="flex-1 flex flex-col items-center justify-center text-center p-6 gap-3 text-slate-400">
          <div class="p-3 bg-slate-100 rounded-full text-slate-400">
            <MessageSquare class="w-6 h-6" />
          </div>
          <div>
            <h4 class="text-sm font-semibold text-slate-700">Belum Ada Percakapan</h4>
            <p class="text-xs text-slate-400 max-w-[280px] mt-1 leading-relaxed">
              {{ isDosen ? 'Kirim pesan pertama Anda ke pimpinan untuk mendiskusikan kelas ini.' : 'Kirim pesan teguran atau diskusi ke dosen mengenai kelas ini.' }}
            </p>
          </div>
        </div>

        <!-- Chat Bubbles -->
        <template v-else>
          <div 
            v-for="msg in messages" 
            :key="msg.id"
            :class="[
              'flex flex-col max-w-[75%]',
              isSentByMe(msg) ? 'self-end items-end' : 'self-start items-start'
            ]"
          >
            <!-- Sender Name if received -->
            <span v-if="!isSentByMe(msg)" class="text-[10px] font-bold text-slate-500 mb-1 ml-1">
              {{ msg.sender?.nama || 'Pimpinan' }}
            </span>
            
            <!-- Message Bubble -->
            <div 
              :class="[
                'p-3 text-sm shadow-sm transition-all duration-200',
                isSentByMe(msg) 
                  ? 'bg-[#4B49AC] text-white rounded-2xl rounded-tr-none' 
                  : 'bg-white border border-slate-100 text-slate-800 rounded-2xl rounded-tl-none'
              ]"
            >
              <p class="whitespace-pre-wrap leading-relaxed">{{ msg.message }}</p>
            </div>
            
            <!-- Timestamp -->
            <span class="text-[9px] text-slate-400 mt-1 mx-1 font-medium">
              {{ formatTime(msg.sent_at) }}
            </span>
          </div>
        </template>
      </div>

      <!-- Footer / Input Form -->
      <div class="p-4 bg-white border-t border-slate-100 flex items-center gap-2 flex-shrink-0">
        <Input 
          v-model="newMessage"
          placeholder="Tulis pesan akademik..."
          class="flex-1 h-10 border-slate-200 rounded-xl focus-visible:ring-[#4B49AC] shadow-none text-sm px-4"
          @keyup.enter="handleSendMessage"
          :disabled="isSending || (isDosen && contacts.length === 0)"
        />
        <Button 
          class="h-10 w-10 p-0 bg-[#4B49AC] hover:bg-[#3f3e91] text-white rounded-xl shadow-sm flex items-center justify-center"
          @click="handleSendMessage"
          :disabled="isSending || !newMessage.trim() || (isDosen && contacts.length === 0)"
        >
          <Loader2 v-if="isSending" class="w-4 h-4 animate-spin" />
          <Send v-else class="w-4 h-4" />
        </Button>
      </div>

    </DialogContent>
  </Dialog>
</template>
