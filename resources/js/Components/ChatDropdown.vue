<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import { 
  PopoverRoot, 
  PopoverTrigger, 
  PopoverContent, 
  PopoverPortal,
  PopoverArrow
} from 'radix-vue'
import { MessageSquare, User, Clock, ArrowRight } from 'lucide-vue-next'
import axios from 'axios'

const page = usePage()

const unreadMessages = ref([])
const unreadCount = ref(0)
let pollingInterval = null

const user = computed(() => page.props.auth?.user)
const role = computed(() => user.value?.role)

const isSupportedRole = computed(() => {
  return ['Dosen', 'Kaprodi', 'Wadir', 'Direktur', 'Mahasiswa'].includes(role.value)
})

const lastNotifiedMessageId = ref(null)

const fetchUnreadCount = async () => {
  if (!isSupportedRole.value) return
  try {
    const response = await axios.get('/presensi/pemberitahuan/unread-messages')
    const oldUnreadCount = unreadCount.value
    unreadCount.value = response.data.unread_count
    unreadMessages.value = response.data.unread_get || []

    // Trigger notification if count increased or there's a new message
    if (unreadCount.value > oldUnreadCount && unreadMessages.value.length > 0) {
      const latestMsg = unreadMessages.value[0]
      if (latestMsg.id !== lastNotifiedMessageId.value) {
        sendBrowserNotification(latestMsg)
        lastNotifiedMessageId.value = latestMsg.id
      }
    }
  } catch (error) {
    console.error('Error fetching unread chat messages:', error)
  }
}

// Browser notification helper
const sendBrowserNotification = (msg) => {
  if (!('Notification' in window) || Notification.permission !== 'granted') return
  
  // Jangan kirim jika window sedang fokus (asumsi user sedang melihat layar)
  if (document.hasFocus()) return

  const senderName = msg.sender?.nama || 'Pimpinan'
  const notification = new Notification(`Pesan dari ${senderName}`, {
    body: msg.message,
    icon: '/favicon.ico'
  })

  notification.onclick = () => {
    window.focus()
    handleItemClick(msg)
  }
}

const handleItemClick = (msg) => {
  let targetUrl = ''
  
  if (msg.jadwal_id === null) {
    if (role.value === 'Dosen') {
      const studentId = msg.sender_type === 'App\\Models\\Mahasiswa' ? msg.sender_id : msg.receiver_id;
      const studentName = msg.sender?.nama_lengkap || msg.sender?.nama || '';
      targetUrl = route('v2.dosen.krs.index') + `?open_guidance=${studentId}&student_name=${encodeURIComponent(studentName)}`
    } else if (role.value === 'Mahasiswa') {
      targetUrl = '/v2/mahasiswa/dashboard?open_guidance=true'
    }
  } else {
    if (role.value === 'Dosen') {
      targetUrl = route('v2.dosen.presensi.index') + `?open_chat=${msg.jadwal_id}`
    } else if (role.value === 'Kaprodi') {
      targetUrl = route('v2.kaprodi.monitoring.perkuliahan.detail', msg.kelas_id) + `?open_chat=${msg.jadwal_id}`
    } else if (role.value === 'Direktur' || role.value === 'Wadir') {
      targetUrl = route('v2.direktur.monitoring.perkuliahan.detail', msg.kelas_id) + `?open_chat=${msg.jadwal_id}`
    }
  }
  
  if (targetUrl) {
    router.visit(targetUrl)
  }
}

const openMainPage = () => {
  if (role.value === 'Dosen') {
    router.visit(route('v2.dosen.presensi.index'))
  } else if (role.value === 'Kaprodi') {
    router.visit(route('v2.kaprodi.monitoring.perkuliahan.index'))
  } else if (role.value === 'Direktur' || role.value === 'Wadir') {
    router.visit(route('v2.direktur.monitoring.perkuliahan.index'))
  } else if (role.value === 'Mahasiswa') {
    router.visit('/v2/mahasiswa/dashboard')
  }
}

const formatTimeAgo = (dateString) => {
  const date = new Date(dateString)
  const now = new Date()
  const diffInSeconds = Math.floor((now - date) / 1000)

  if (diffInSeconds < 60) return 'Baru saja'
  if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} menit yang lalu`
  if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} jam yang lalu`
  return `${Math.floor(diffInSeconds / 86400)} hari yang lalu`
}

onMounted(() => {
  if (isSupportedRole.value) {
    fetchUnreadCount()
    
    // Request permission if not already set
    if ('Notification' in window && Notification.permission === 'default') {
      Notification.requestPermission()
    }

    // Listen to global MessageSent event if possible, or rely on polling for notifications
    // Since ChatDropdown is global, we can listen to private channels of the user
    if (window.Echo && user.value) {
      // Logic for global notification listening can be added here
      // For now, we enhance the polling to trigger notifications
    }

    pollingInterval = setInterval(fetchUnreadCount, 15000) 
  }
})

onUnmounted(() => {
  if (pollingInterval) clearInterval(pollingInterval)
})
</script>

<template>
  <PopoverRoot v-if="isSupportedRole">
    <PopoverTrigger
      class="relative cursor-pointer p-2 hover:bg-gray-100 rounded-full transition-colors outline-none flex items-center justify-center"
      aria-label="Messages"
    >
      <MessageSquare class="w-5 h-5 text-[#1F1F1F]" />
      <span 
        v-if="unreadCount > 0"
        class="absolute top-1 right-1 min-w-[16px] h-4 bg-[#FF4747] text-white text-[10px] font-bold rounded-full border-2 border-white flex items-center justify-center px-1"
      >
        {{ unreadCount > 99 ? '99+' : unreadCount }}
      </span>
    </PopoverTrigger>

    <PopoverPortal>
      <PopoverContent
        side="bottom"
        :side-offset="10"
        align="end"
        class="z-[100] w-[380px] rounded-2xl bg-white shadow-2xl border border-gray-100 outline-none animate-in fade-in zoom-in duration-200"
      >
        <div class="flex items-center justify-between p-4 border-b border-gray-50 bg-[#4B49AC]/5 rounded-t-2xl">
          <div class="flex items-center gap-2">
            <MessageSquare class="w-4 h-4 text-[#4B49AC]" />
            <h3 class="font-bold text-[#1F2937] text-sm">Diskusi Akademik</h3>
          </div>
          <span v-if="unreadCount > 0" class="text-xs font-semibold bg-[#FF4747] text-white px-2 py-0.5 rounded-full">
            {{ unreadCount }} Baru
          </span>
        </div>

        <div class="max-h-[350px] overflow-y-auto overflow-x-hidden py-2 custom-scrollbar bg-white">
          <div v-if="unreadMessages.length === 0" class="flex flex-col items-center justify-center py-12 px-4 text-center">
            <div class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center mb-3 text-slate-400">
              <MessageSquare class="w-5 h-5" />
            </div>
            <p class="text-xs font-bold text-slate-700">Tidak ada pesan baru</p>
            <p class="text-[11px] text-slate-400 max-w-[200px] mt-1">Pesan dari pimpinan atau dosen terkait presensi akan muncul di sini.</p>
          </div>

          <div 
            v-for="msg in unreadMessages" 
            :key="msg.id"
            @click="handleItemClick(msg)"
            class="flex items-start gap-3 p-4 hover:bg-slate-50 cursor-pointer transition-colors border-b border-slate-50 last:border-none relative group"
          >
            <!-- Avatar placeholder -->
            <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-semibold text-xs shrink-0 uppercase">
              {{ (msg.sender?.nama_lengkap || msg.sender?.nama || 'User').substring(0, 2) }}
            </div>
            
            <div class="flex-1 min-w-0">
              <div class="flex justify-between items-start mb-0.5">
                <span class="text-xs font-extrabold text-slate-700">
                  {{ msg.sender?.nama_lengkap || msg.sender?.nama || 'User' }}
                </span>
                <span class="text-[9px] text-slate-400 shrink-0 ml-2 font-medium flex items-center gap-1">
                  <Clock class="w-3 h-3" />
                  {{ formatTimeAgo(msg.sent_at) }}
                </span>
              </div>
              
              <div class="text-[10px] font-bold text-[#4B49AC] leading-tight mb-1">
                <template v-if="msg.jadwal_id">
                  {{ msg.jadwal?.matkul?.nama_matkul }} - {{ msg.kelas?.nama_kelas }}
                </template>
                <template v-else>
                  Konsultasi Akademik (Bimbingan DPA)
                </template>
              </div>
              
              <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed bg-slate-50 p-2 rounded-lg mt-1 italic">
                "{{ msg.message }}"
              </p>
            </div>

            <!-- Read indicator -->
            <div class="absolute left-1 top-1/2 -translate-y-1/2 w-1.5 h-8 bg-[#4B49AC] rounded-full opacity-80"></div>
          </div>
        </div>

        <div class="p-3 bg-slate-50 rounded-b-2xl text-center border-t border-slate-100 flex items-center justify-center gap-1.5 hover:bg-slate-100 transition-colors cursor-pointer group" @click="openMainPage()">
          <span class="text-[11px] text-[#4B49AC] font-bold">
            Buka Halaman Utama
          </span>
          <ArrowRight class="w-3.5 h-3.5 text-[#4B49AC] group-hover:translate-x-0.5 transition-transform" />
        </div>

        <PopoverArrow class="fill-white" />
      </PopoverContent>
    </PopoverPortal>
  </PopoverRoot>
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
</style>
