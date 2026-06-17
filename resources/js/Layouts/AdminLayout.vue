<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import Navbar from '@/Components/Navbar.vue'
import Sidebar from '@/Components/Sidebar.vue'
import { AlertTriangle as AlertTriangleIcon, X as XIcon, RefreshCw as RefreshCwIcon, CheckCircle2 as CheckCircleIcon, XCircle as XCircleIcon } from 'lucide-vue-next'
import { useFeederSync } from '@/Composables/useFeederSync'

const page = usePage()
const user = computed(() => page.props.auth.user)

const isSidebarOpen = ref(false) // default tertutup, akan dibuka di desktop saat mount

const handleResize = () => {
  if (window.innerWidth >= 1024) {
    // Desktop: buka sidebar jika sebelumnya tertutup karena mobile
    isSidebarOpen.value = true
  } else {
    // Mobile: tutup sidebar
    isSidebarOpen.value = false
  }
}

const {
  isSyncing,
  progress,
  processed,
  total,
  message,
  status,
  type,
  stats,
  showWidget,
  initEchoListener,
  dismissWidget
} = useFeederSync()

const getSyncTitle = (syncType) => {
  switch (syncType) {
    case 'pull-prodis':
      return 'Tarik Program Studi'
    case 'pull-dosens':
      return 'Tarik Penugasan Dosen'
    case 'pull-matkuls':
      return 'Tarik Mata Kuliah'
    case 'pull-mahasiswas':
      return 'Tarik Data Mahasiswa'
    case 'push-mahasiswa':
      return 'Push Data Mahasiswa'
    case 'push-mahasiswa-kelas':
      return 'Push Kelas Rombel'
    default:
      return 'Sinkronisasi Data'
  }
}

onMounted(() => {
  // Set initial state berdasarkan ukuran layar saat pertama kali load
  isSidebarOpen.value = window.innerWidth >= 1024
  window.addEventListener('resize', handleResize)
  
  // Inisialisasi pendengar real-time WebSocket Echo untuk progres sinkronisasi
  initEchoListener()
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
})

const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value
}

const closeSidebar = () => {
  isSidebarOpen.value = false
}
</script>

<template>
  <div class="min-h-screen bg-[#F5F7FF] flex flex-col">
    <!-- Navbar -->
    <Navbar @toggle-sidebar="toggleSidebar" />

    <div class="flex flex-1 pt-[70px]">
      <!-- Sidebar -->
      <Sidebar :is-open="isSidebarOpen" @close="closeSidebar" />

      <!-- Main Content -->
      <main
        class="flex-1 transition-all duration-300 min-w-0"
        :class="[
          // Desktop: margin mengikuti lebar sidebar
          isSidebarOpen ? 'lg:ml-[260px]' : 'lg:ml-[70px]',
          // Mobile: tidak ada margin (sidebar overlay)
          'ml-0'
        ]"
      >
        <div class="p-4 sm:p-6">
          <!-- Warning Banner WhatsApp -->
          <div v-if="user && ['Mahasiswa', 'Orang Tua', 'Dosen', 'Pegawai', 'Administrator'].includes(user.role) && !user.whatsapp_verified_at" class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-lg flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shadow-sm">
            <div class="flex items-start gap-3">
              <div class="p-2 bg-amber-100 text-amber-700 rounded-lg mt-0.5 shrink-0">
                <AlertTriangleIcon class="w-5 h-5 animate-pulse text-amber-600" />
              </div>
              <div>
                <h4 class="font-bold text-amber-900 text-sm">Verifikasi WhatsApp Diperlukan</h4>
                <p class="text-amber-700 text-xs mt-0.5">
                  <span v-if="user.role === 'Mahasiswa'">Nomor WhatsApp Anda belum diverifikasi. Aksi penting (KRS, Unggah Pembayaran, Permohonan Surat) ditangguhkan sampai nomor terverifikasi.</span>
                  <span v-else-if="user.role === 'Orang Tua'">Nomor WhatsApp Anda belum diverifikasi. Silakan verifikasi nomor Anda agar dapat menerima notifikasi perkembangan akademik anak Anda secara langsung.</span>
                  <span v-else>Nomor WhatsApp Anda belum diverifikasi. Silakan verifikasi nomor Anda agar dapat menerima notifikasi penting dari sistem akademik.</span>
                </p>
              </div>
            </div>
            <Link :href="route('v2.profile.edit')" class="shrink-0 text-xs font-bold text-amber-950 bg-amber-200 hover:bg-amber-300 px-3.5 py-2.5 rounded-lg transition-colors text-center shadow-sm">
              Verifikasi Sekarang
            </Link>
          </div>

          <slot />
        </div>

        <!-- Footer -->
        <footer class="px-4 sm:px-6 pb-6 text-center text-[#6C7383] text-sm mt-auto">
          Copyright © 2026 Siap Polsa. All rights reserved.
        </footer>
      </main>
    </div>

    <!-- Floating Feeder Sync Progress Widget -->
    <transition name="widget-fade">
      <div
        v-if="showWidget"
        class="fixed bottom-6 right-6 z-[9999] w-80 sm:w-96 bg-white/95 backdrop-blur-md border border-gray-200 rounded-2xl shadow-2xl overflow-hidden font-sans"
      >
        <!-- Header -->
        <div class="bg-[#4B49AC] text-white px-4 py-3 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <RefreshCwIcon
              class="w-4 h-4"
              :class="{ 'animate-spin': isSyncing }"
            />
            <span class="font-bold text-xs uppercase tracking-wider">
              Sinkronisasi Feeder
            </span>
          </div>
          <button
            @click="dismissWidget"
            class="text-white/80 hover:text-white transition-colors"
          >
            <XIcon class="w-4 h-4" />
          </button>
        </div>

        <!-- Body -->
        <div class="p-4 space-y-3.5">
          <!-- Text status and message -->
          <div>
            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">
              {{ getSyncTitle(type) }}
            </div>
            <p class="text-xs font-semibold text-gray-700 line-clamp-2">
              {{ message }}
            </p>
          </div>

          <!-- Progress bar -->
          <div v-if="status === 'running'" class="space-y-1">
            <div class="flex justify-between items-center text-[10px] font-bold text-gray-500">
              <span>PROGRES</span>
              <span>{{ progress }}%</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
              <div
                class="bg-[#4B49AC] h-full rounded-full transition-all duration-300 ease-out"
                :style="{ width: `${progress}%` }"
              />
            </div>
          </div>

          <!-- Progress Stats -->
          <div class="grid grid-cols-3 gap-2 bg-gray-50 p-2.5 rounded-xl border border-gray-100">
            <div class="text-center">
              <div class="text-[10px] font-bold text-gray-400 uppercase">Cocok</div>
              <div class="text-sm font-bold text-indigo-600">{{ stats.matched }}</div>
            </div>
            <div class="text-center border-x border-gray-200">
              <div class="text-[10px] font-bold text-gray-400 uppercase">Baru</div>
              <div class="text-sm font-bold text-emerald-600">{{ stats.created }}</div>
            </div>
            <div class="text-center">
              <div class="text-[10px] font-bold text-gray-400 uppercase">Gagal</div>
              <div class="text-sm font-bold text-rose-600">{{ stats.failed }}</div>
            </div>
          </div>

          <!-- Status badge at bottom -->
          <div class="flex items-center justify-between text-xs pt-2 border-t border-gray-100">
            <span class="text-gray-400 font-medium">
              Terproses: <strong class="text-gray-700">{{ processed }} / {{ total || '?' }}</strong>
            </span>
            <div class="flex items-center gap-1.5 font-bold">
              <template v-if="status === 'running'">
                <span class="w-1.5 h-1.5 rounded-full bg-[#4B49AC] animate-ping" />
                <span class="text-[#4B49AC] uppercase text-[10px]">Memproses</span>
              </template>
              <template v-else-if="status === 'completed'">
                <CheckCircleIcon class="w-4 h-4 text-emerald-500" />
                <span class="text-emerald-600 uppercase text-[10px]">Selesai</span>
              </template>
              <template v-else-if="status === 'failed'">
                <XCircleIcon class="w-4 h-4 text-rose-500" />
                <span class="text-rose-600 uppercase text-[10px]">Gagal</span>
              </template>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<style scoped>
main {
  transition: margin-left 0.3s ease;
}

/* Widget transition animations */
.widget-fade-enter-active,
.widget-fade-leave-active {
  transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

.widget-fade-enter-from {
  opacity: 0;
  transform: translateY(20px) scale(0.95);
}

.widget-fade-leave-to {
  opacity: 0;
  transform: translateY(20px) scale(0.95);
}
</style>
