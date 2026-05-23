<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { 
  PopoverRoot, 
  PopoverTrigger, 
  PopoverContent, 
  PopoverPortal,
  PopoverArrow
} from 'radix-vue'
import { Bell, Info, AlertTriangle, CheckCircle, CreditCard, BookOpen, GraduationCap, Star, Trash2 } from 'lucide-vue-next'
import axios from 'axios'

const notifications = ref([])
const unreadCount = ref(0)
let pollingInterval = null

const fetchNotifications = async () => {
  try {
    const response = await axios.get('/presensi/get-notif')
    notifications.value = response.data.notifications
    unreadCount.value = response.data.unread_count
  } catch (error) {
    console.error('Error fetching notifications:', error)
  }
}

const markAsRead = async (id = null) => {
  try {
    const payload = id ? { notification_id: id } : {}
    await axios.post('/presensi/mark-notif-read', payload)
    await fetchNotifications()
  } catch (error) {
    console.error('Error marking notifications as read:', error)
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

const getNotificationConfig = (type) => {
  switch (type) {
    case 'pemberitahuan':
      return { icon: Info, color: 'bg-blue-500', label: 'Info' }
    case 'pembayaran':
      return { icon: CreditCard, color: 'bg-green-500', label: 'Pembayaran' }
    case 'krs':
      return { icon: GraduationCap, color: 'bg-indigo-500', label: 'KRS' }
    case 'presensi':
    case 'resume':
      return { icon: CheckCircle, color: 'bg-emerald-500', label: 'Kehadiran' }
    case 'kontrak':
      return { icon: BookOpen, color: 'bg-orange-500', label: 'Kontrak' }
    case 'nilai':
      return { icon: Star, color: 'bg-yellow-500', label: 'Nilai' }
    default:
      return { icon: Bell, color: 'bg-gray-500', label: 'Notifikasi' }
  }
}

onMounted(() => {
  fetchNotifications()
  pollingInterval = setInterval(fetchNotifications, 60000)
})

onUnmounted(() => {
  if (pollingInterval) clearInterval(pollingInterval)
})
</script>

<template>
  <PopoverRoot>
    <PopoverTrigger
      class="relative cursor-pointer p-2 hover:bg-gray-100 rounded-full transition-colors outline-none"
      aria-label="Notifications"
    >
      <Bell class="w-5 h-5 text-[#1F1F1F]" />
      <span 
        v-if="unreadCount > 0"
        class="absolute top-1 right-1 min-w-[16px] h-4 bg-red-500 text-white text-[10px] font-bold rounded-full border-2 border-white flex items-center justify-center px-1"
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
        <div class="flex items-center justify-between p-4 border-b border-gray-50">
          <h3 class="font-bold text-[#1F2937]">Notifikasi</h3>
          <button 
            @click="markAsRead()"
            class="text-xs text-blue-600 hover:underline font-medium"
          >
            Tandai semua dibaca
          </button>
        </div>

        <div class="max-h-[400px] overflow-y-auto overflow-x-hidden py-2 custom-scrollbar">
          <div v-if="notifications.length === 0" class="flex flex-col items-center justify-center py-12 px-4 text-center">
            <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mb-3">
              <Bell class="w-6 h-6 text-gray-300" />
            </div>
            <p class="text-sm text-gray-500">Tidak ada notifikasi baru</p>
          </div>

          <div 
            v-for="notif in notifications" 
            :key="notif.id"
            @click="markAsRead(notif.id)"
            class="flex items-start gap-3 p-4 hover:bg-gray-50 cursor-pointer transition-colors border-b border-gray-50 last:border-none relative group"
          >
            <div :class="[getNotificationConfig(notif.data.notification_type).color, 'p-2 rounded-xl text-white shrink-0']">
              <component :is="getNotificationConfig(notif.data.notification_type).icon" class="w-4 h-4" />
            </div>
            
            <div class="flex-1 min-w-0">
              <div class="flex justify-between items-start mb-1">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                  {{ getNotificationConfig(notif.data.notification_type).label }}
                </span>
                <span class="text-[10px] text-gray-400 shrink-0 ml-2">
                  {{ formatTimeAgo(notif.created_at) }}
                </span>
              </div>
              
              <h4 class="text-sm font-bold text-[#1F2937] leading-tight mb-1 truncate">
                {{ notif.data.title }}
              </h4>
              
              <p class="text-xs text-gray-600 line-clamp-2 leading-relaxed">
                {{ notif.data.message_content || notif.data.title }}
              </p>

              <!-- Meta info for academic types -->
              <div v-if="notif.data.matkul || notif.data.class" class="mt-2 flex flex-wrap gap-2">
                <span v-if="notif.data.matkul" class="text-[10px] bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded">
                  {{ notif.data.matkul }}
                </span>
                <span v-if="notif.data.class" class="text-[10px] bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded">
                  {{ notif.data.class }}
                </span>
              </div>
            </div>

            <!-- Read indicator -->
            <div v-if="!notif.read_at" class="absolute left-1 top-1/2 -translate-y-1/2 w-1 h-8 bg-blue-500 rounded-full"></div>
          </div>
        </div>

        <div class="p-3 bg-gray-50 rounded-b-2xl text-center border-t border-gray-100">
          <Link href="#" class="text-xs text-gray-500 hover:text-[#1F2937] font-medium">
            Lihat Semua Riwayat
          </Link>
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
  background: #E5E7EB;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #D1D5DB;
}
</style>
