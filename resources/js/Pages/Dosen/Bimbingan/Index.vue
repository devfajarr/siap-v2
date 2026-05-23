<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import axios from 'axios'
import { Card } from '@/Components/ui/card'
import { Input } from '@/Components/ui/input'
import { Button } from '@/Components/ui/button'
import {
  MessageSquare,
  Search,
  Send,
  Loader2,
  Check,
  CheckCheck,
  CheckCircle2,
  AlertCircle,
  Users,
  UserCircle2,
} from 'lucide-vue-next'

const page = usePage()

// ─── Contact List State ───────────────────────────────────────
const contacts = ref([])
const isLoadingContacts = ref(true)
const searchQuery = ref('')
const selectedStudentId = ref(null)

const filteredContacts = computed(() => {
  const q = searchQuery.value.toLowerCase().trim()
  if (!q) return contacts.value
  return contacts.value.filter(
    c =>
      c.nama.toLowerCase().includes(q) ||
      c.nim.toLowerCase().includes(q) ||
      c.kelas.toLowerCase().includes(q),
  )
})

const totalUnread = computed(() =>
  contacts.value.reduce((sum, c) => sum + (c.unread_count || 0), 0),
)

const fetchContacts = async () => {
  isLoadingContacts.value = true
  try {
    const res = await axios.get('/presensi/pemberitahuan/contacts-guidance')
    contacts.value = res.data
    // Auto-select first contact with unread, or first in list
    if (!selectedStudentId.value && contacts.value.length > 0) {
      const withUnread = contacts.value.find(c => c.unread_count > 0)
      selectedStudentId.value = (withUnread || contacts.value[0]).id
    }
  } catch (err) {
    console.error('Failed to fetch contacts:', err)
  } finally {
    isLoadingContacts.value = false
  }
}

const selectedContact = computed(() =>
  contacts.value.find(c => c.id === selectedStudentId.value) ?? null,
)

const selectContact = (id) => {
  selectedStudentId.value = id
}

// ─── Chat State ───────────────────────────────────────────────
const messages = ref([])
const newMessage = ref('')
const isLoadingMessages = ref(false)
const isSending = ref(false)
const messagesContainer = ref(null)
const onlineUsers = ref([])
let channel = null

// Toast
const showToast = ref(false)
const toastMessage = ref('')
const toastType = ref('success')

const triggerToast = (message, type = 'success') => {
  toastMessage.value = message
  toastType.value = type
  showToast.value = true
  setTimeout(() => { showToast.value = false }, 3000)
}

const scrollToBottom = () => {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
    }
  })
}

const isSentByMe = (msg) => {
  return msg?.sender_type?.includes('Dosen')
}

const isStudentOnline = computed(() => {
  if (!selectedStudentId.value) return false
  return onlineUsers.value.some(u => u.role === 'mahasiswa')
})

// ─── Fetch Messages ───────────────────────────────────────────
const fetchMessages = async () => {
  if (!selectedStudentId.value) return
  isLoadingMessages.value = true
  try {
    const res = await axios.get('/presensi/pemberitahuan/show-guidance', {
      params: { student_id: selectedStudentId.value },
    })
    messages.value = res.data
    // Mark contact as read locally
    const contact = contacts.value.find(c => c.id === selectedStudentId.value)
    if (contact) { contact.unread_count = 0 }
    scrollToBottom()
  } catch (err) {
    console.error('Failed to fetch messages:', err)
  } finally {
    isLoadingMessages.value = false
  }
}

// ─── Send Message ─────────────────────────────────────────────
const sendMessage = async () => {
  if (!newMessage.value.trim() || isSending.value) return
  const text = newMessage.value.trim()
  newMessage.value = ''
  isSending.value = true
  try {
    const res = await axios.post('/presensi/pemberitahuan/send-guidance', {
      message: text,
      student_id: selectedStudentId.value,
    })
    const sent = res.data.data
    if (!messages.value.some(m => m.id === sent.id)) {
      messages.value.push(sent)
      scrollToBottom()
    }
    triggerToast('Pesan berhasil dikirim.', 'success')
  } catch (err) {
    console.error('Failed to send message:', err)
    newMessage.value = text
    const errMsg = err.response?.data?.message || 'Gagal mengirim pesan. Silakan coba lagi.'
    triggerToast(errMsg, 'error')
  } finally {
    isSending.value = false
  }
}

// ─── Echo / Presence Channel ──────────────────────────────────
const stopListening = () => {
  if (channel && selectedStudentId.value) {
    window.Echo.leave(`guidance.${selectedStudentId.value}`)
    channel = null
    onlineUsers.value = []
  }
}

const startListening = () => {
  if (!selectedStudentId.value) return
  stopListening()

  channel = window.Echo.join(`guidance.${selectedStudentId.value}`)
    .here((users) => { onlineUsers.value = users })
    .joining((user) => {
      if (!onlineUsers.value.some(u => u.id === user.id && u.role === user.role)) {
        onlineUsers.value.push(user)
      }
    })
    .leaving((user) => {
      onlineUsers.value = onlineUsers.value.filter(
        u => !(u.id === user.id && u.role === user.role),
      )
    })
    .listen('.MessageSent', (e) => {
      const msg = e.message || e
      if (!messages.value.some(m => m.id === msg.id)) {
        messages.value.push(msg)
        scrollToBottom()
        // Update unread locally only if not the currently selected contact
        if (msg.sender_id !== selectedStudentId.value) {
          const contact = contacts.value.find(c => c.id === msg.sender_id)
          if (contact) { contact.unread_count = (contact.unread_count || 0) + 1 }
        } else {
          // Mark as read immediately via API (we're looking at this chat)
          axios.post('/presensi/pemberitahuan/mark-guidance-read', {
            message_ids: [msg.id],
            student_id: selectedStudentId.value,
          }).catch(() => {})
          msg.read = true
        }
      }
    })
    .listen('.MessageRead', (e) => {
      const ids = new Set(e.message_ids.map(Number))
      messages.value.forEach(m => {
        if (ids.has(Number(m.id))) { m.read = true }
      })
    })
}

// ─── Watchers ─────────────────────────────────────────────────
watch(selectedStudentId, async (newId) => {
  if (!newId) return
  messages.value = []
  await fetchMessages()
  startListening()
})

// ─── Lifecycle ────────────────────────────────────────────────
onMounted(async () => {
  await fetchContacts()
})

onUnmounted(() => {
  stopListening()
})
</script>

<template>
  <AdminLayout>
    <Head title="Bimbingan Mahasiswa" />

    <div class="flex flex-col h-[calc(100vh-80px)]">
      <!-- Page Header -->
      <div class="flex items-center justify-between mb-4 shrink-0">
        <div>
          <h1 class="text-xl font-bold text-slate-800">Bimbingan Mahasiswa</h1>
          <p class="text-sm text-slate-500 mt-0.5">
            Ruang konsultasi bimbingan akademik dengan mahasiswa perwalian Anda.
          </p>
        </div>
        <div
          v-if="totalUnread > 0"
          class="flex items-center gap-2 bg-red-50 text-red-600 border border-red-200 px-3 py-1.5 rounded-full text-xs font-bold"
        >
          <span class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
          {{ totalUnread }} pesan belum dibaca
        </div>
      </div>

      <!-- Split Panel -->
      <div class="flex flex-1 gap-4 min-h-0">

        <!-- LEFT: Contact List -->
        <Card class="w-72 xl:w-80 shrink-0 flex flex-col border border-slate-200 shadow-sm overflow-hidden">
          <!-- Search -->
          <div class="p-3 border-b border-slate-100 shrink-0">
            <div class="relative">
              <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" />
              <Input
                v-model="searchQuery"
                placeholder="Cari nama / NIM..."
                class="pl-8 h-9 text-sm border-slate-200 focus-visible:ring-[#4B49AC]"
              />
            </div>
          </div>

          <!-- Loading skeleton -->
          <div v-if="isLoadingContacts" class="flex-1 overflow-y-auto p-2 space-y-2">
            <div
              v-for="i in 5"
              :key="i"
              class="h-16 rounded-lg bg-slate-100 animate-pulse"
            />
          </div>

          <!-- Empty state -->
          <div
            v-else-if="filteredContacts.length === 0"
            class="flex-1 flex flex-col items-center justify-center text-center p-6 text-slate-400"
          >
            <Users class="w-10 h-10 mb-3 text-slate-300" />
            <p class="text-sm font-semibold">Belum ada mahasiswa</p>
            <p class="text-xs mt-1">
              {{ searchQuery ? 'Tidak ditemukan hasil pencarian.' : 'Anda belum memiliki mahasiswa bimbingan.' }}
            </p>
          </div>

          <!-- Contact Items -->
          <div v-else class="flex-1 overflow-y-auto p-2 space-y-1 custom-scrollbar">
            <button
              v-for="contact in filteredContacts"
              :key="contact.id"
              class="w-full text-left px-3 py-2.5 rounded-xl transition-all duration-150 flex items-center gap-3 group"
              :class="selectedStudentId === contact.id
                ? 'bg-[#4B49AC] text-white shadow-md'
                : 'hover:bg-slate-50 text-slate-800'"
              @click="selectContact(contact.id)"
            >
              <!-- Avatar -->
              <div
                class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm shrink-0 uppercase"
                :class="selectedStudentId === contact.id ? 'bg-white/20 text-white' : 'bg-[#4B49AC]/10 text-[#4B49AC]'"
              >
                {{ contact.nama.substring(0, 2) }}
              </div>

              <!-- Info -->
              <div class="flex-1 min-w-0">
                <p
                  class="text-sm font-semibold truncate"
                  :class="selectedStudentId === contact.id ? 'text-white' : 'text-slate-800'"
                >
                  {{ contact.nama }}
                </p>
                <p
                  class="text-xs truncate mt-0.5"
                  :class="selectedStudentId === contact.id ? 'text-white/70' : 'text-slate-400'"
                >
                  {{ contact.nim }} · {{ contact.kelas }}
                </p>
              </div>

              <!-- Unread Badge -->
              <span
                v-if="contact.unread_count > 0"
                class="shrink-0 min-w-[20px] h-5 px-1.5 rounded-full text-[11px] font-bold flex items-center justify-center"
                :class="selectedStudentId === contact.id ? 'bg-white text-[#4B49AC]' : 'bg-red-500 text-white'"
              >
                {{ contact.unread_count }}
              </span>
            </button>
          </div>
        </Card>

        <!-- RIGHT: Chat Area -->
        <Card class="flex-1 flex flex-col border border-slate-200 shadow-sm overflow-hidden min-w-0">
          <!-- Empty state (no contact selected) -->
          <div
            v-if="!selectedContact"
            class="flex-1 flex flex-col items-center justify-center text-center p-10 text-slate-400"
          >
            <div class="w-16 h-16 rounded-2xl bg-[#4B49AC]/10 flex items-center justify-center mb-4">
              <MessageSquare class="w-8 h-8 text-[#4B49AC]/40" />
            </div>
            <h4 class="font-bold text-slate-600">Pilih Mahasiswa</h4>
            <p class="text-sm mt-1 max-w-xs leading-relaxed">
              Pilih salah satu mahasiswa bimbingan di sebelah kiri untuk memulai percakapan.
            </p>
          </div>

          <!-- Chat Panel -->
          <template v-else>
            <!-- Chat Header -->
            <div class="px-5 py-4 border-b border-slate-100 shrink-0 flex items-center gap-3 bg-white">
              <div class="w-10 h-10 rounded-full bg-[#4B49AC]/10 text-[#4B49AC] flex items-center justify-center font-bold uppercase text-sm shrink-0">
                {{ selectedContact.nama.substring(0, 2) }}
              </div>
              <div class="flex-1 min-w-0">
                <h3 class="font-bold text-slate-800 text-sm leading-tight truncate">
                  {{ selectedContact.nama }}
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">
                  {{ selectedContact.nim }} · {{ selectedContact.kelas }}
                  <span
                    v-if="isStudentOnline"
                    class="ml-2 inline-flex items-center gap-1 text-green-500 font-semibold"
                  >
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>
                    Online
                  </span>
                </p>
              </div>
              <UserCircle2 class="w-5 h-5 text-slate-300" />
            </div>

            <!-- Messages Area -->
            <div
              ref="messagesContainer"
              class="flex-1 overflow-y-auto p-5 space-y-4 bg-slate-50/50 custom-scrollbar"
            >
              <!-- Loading -->
              <div v-if="isLoadingMessages" class="flex flex-col items-center justify-center h-full space-y-3">
                <Loader2 class="w-8 h-8 text-[#4B49AC] animate-spin" />
                <span class="text-xs text-slate-400 font-semibold tracking-wider uppercase">Memuat percakapan...</span>
              </div>

              <!-- Empty chat -->
              <div
                v-else-if="messages.length === 0"
                class="flex flex-col items-center justify-center h-full text-center"
              >
                <div class="w-14 h-14 bg-white shadow-sm rounded-2xl flex items-center justify-center mb-4 text-[#4B49AC]/40 border border-[#4B49AC]/5">
                  <MessageSquare class="w-6 h-6" />
                </div>
                <h4 class="text-sm font-bold text-slate-700">Mulai Percakapan</h4>
                <p class="text-xs text-slate-400 max-w-[240px] mt-1 leading-relaxed">
                  Kirim pesan pertama untuk memulai sesi bimbingan dengan {{ selectedContact.nama }}.
                </p>
              </div>

              <!-- Message Bubbles -->
              <template v-else>
                <div
                  v-for="msg in messages"
                  :key="msg.id"
                  :class="['flex w-full', isSentByMe(msg) ? 'justify-end' : 'justify-start']"
                >
                  <div class="max-w-[75%] flex flex-col">
                    <div
                      :class="[
                        'p-3.5 rounded-2xl shadow-sm leading-relaxed text-sm',
                        isSentByMe(msg)
                          ? 'bg-[#4B49AC] text-white rounded-tr-none'
                          : 'bg-white text-slate-800 border border-slate-100 rounded-tl-none',
                      ]"
                    >
                      <p class="whitespace-pre-wrap text-left">{{ msg.message }}</p>
                    </div>

                    <div
                      :class="[
                        'flex items-center gap-1 mt-1 text-[10px] text-slate-400 px-1',
                        isSentByMe(msg) ? 'justify-end' : 'justify-start',
                      ]"
                    >
                      <span>{{ new Date(msg.sent_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}</span>
                      <template v-if="isSentByMe(msg)">
                        <CheckCheck v-if="msg.read" class="w-3.5 h-3.5 text-[#4B49AC]" />
                        <Check v-else class="w-3.5 h-3.5 text-slate-400" />
                      </template>
                    </div>
                  </div>
                </div>
              </template>
            </div>

            <!-- Input Bar -->
            <form
              @submit.prevent="sendMessage"
              class="p-4 border-t border-slate-100 bg-white shrink-0 flex items-center gap-3"
            >
              <Input
                v-model="newMessage"
                type="text"
                :placeholder="`Tulis pesan untuk ${selectedContact.nama}...`"
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

            <!-- Toast -->
            <transition name="toast">
              <div
                v-if="showToast"
                class="absolute bottom-6 left-1/2 -translate-x-1/2 z-[150] w-[90%] max-w-[360px]"
              >
                <div class="bg-slate-900/95 text-white px-4 py-3 rounded-xl shadow-2xl flex items-center gap-2.5 border border-slate-800 backdrop-blur-sm">
                  <div
                    :class="toastType === 'error' ? 'bg-red-500' : 'bg-green-500'"
                    class="p-1 rounded-full shrink-0 flex items-center justify-center animate-pulse"
                  >
                    <CheckCircle2 v-if="toastType === 'success'" class="w-3 h-3 text-white" />
                    <AlertCircle v-else class="w-3 h-3 text-white" />
                  </div>
                  <span class="text-xs font-semibold leading-tight">{{ toastMessage }}</span>
                </div>
              </div>
            </transition>
          </template>
        </Card>
      </div>
    </div>
  </AdminLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #CBD5E1; }

.toast-enter-active, .toast-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.toast-enter-from, .toast-leave-to {
  opacity: 0;
  transform: translate(-50%, 10px) scale(0.95);
}
</style>
