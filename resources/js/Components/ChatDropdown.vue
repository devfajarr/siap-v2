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
  return ['Dosen', 'Kaprodi', 'Wadir', 'Direktur'].includes(role.value)
})

const fetchUnreadCount = async () => {
  if (!isSupportedRole.value) return
  try {
    const response = await axios.get('/presensi/pemberitahuan/unread-messages')
    unreadCount.value = response.data.unread_count
    unreadMessages.value = response.data.unread_get || []
  } catch (error) {
    console.error('Error fetching unread chat messages:', error)
  }
}

const handleItemClick = (msg) => {
  let targetUrl = ''
  
  if (role.value === 'Dosen') {
    targetUrl = route('v2.dosen.presensi.index') + `?open_chat=${msg.jadwal_id}`
  } else if (role.value === 'Kaprodi') {
    targetUrl = route('v2.kaprodi.monitoring.perkuliahan.detail', msg.kelas_id) + `?open_chat=${msg.jadwal_id}`
  } else if (role.value === 'Direktur' || role.value === 'Wadir') {
    targetUrl = route('v2.direktur.monitoring.perkuliahan.detail', msg.kelas_id) + `?open_chat=${msg.jadwal_id}`
  }
  
  if (targetUrl) {
    router.visit(targetUrl)
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
    pollingInterval = setInterval(fetchUnreadCount, 15000) // Poll every 15s for new messages
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
            <div class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center mb-3 text-slate-350">
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
              {{ msg.sender?.nama?.substring(0, 2) || 'PI' }}
            </div>
            
            <div class="flex-1 min-w-0">
              <div class="flex justify-between items-start mb-0.5">
                <span class="text-xs font-extrabold text-slate-700">
                  {{ msg.sender?.nama || 'Pimpinan' }}
                </span>
                <span class="text-[9px] text-slate-400 shrink-0 ml-2 font-medium flex items-center gap-1">
                  <Clock class="w-3 h-3" />
                  {{ formatTimeAgo(msg.sent_at) }}
                </span>
              </div>
              
              <div class="text-[10px] font-bold text-[#4B49AC] leading-tight mb-1">
                {{ msg.jadwal?.matkul?.nama_matkul }} - {{ msg.kelas?.nama_kelas }}
              </div>
              
              <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed bg-slate-50 p-2 rounded-lg mt-1 italic">
                "{{ msg.message }}"
              </p>
            </div>

            <!-- Read indicator -->
            <div class="absolute left-1 top-1/2 -translate-y-1/2 w-1.5 h-8 bg-[#4B49AC] rounded-full opacity-80"></div>
          </div>
        </div>

        <div class="p-3 bg-slate-50 rounded-b-2xl text-center border-t border-slate-100 flex items-center justify-center gap-1.5 hover:bg-slate-100 transition-colors cursor-pointer group" @click="router.visit(role === 'Dosen' ? route('v2.dosen.presensi.index') : route('v2.kaprodi.monitoring.perkuliahan.index'))">
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
