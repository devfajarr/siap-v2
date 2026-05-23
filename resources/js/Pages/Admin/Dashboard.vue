<script setup>
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Switch } from '@/Components/ui/switch'
import axios from 'axios'
import { ref } from 'vue'
import { 
  Users, 
  UserCheck, 
  School, 
  Calendar, 
  CheckCircle2, 
  XCircle,
  Bell
} from 'lucide-vue-next'

const props = defineProps({
  stats: {
    type: Object,
    required: true
  },
  rekapProdi: {
    type: Array,
    required: true
  },
  dailyScheduleStatus: {
    type: Number,
    required: true
  }
})

const isNotificationEnabled = ref(props.dailyScheduleStatus === 1)
const showToast = ref(false)
const toastMessage = ref('')

const toggleNotification = async (val) => {
  try {
    await axios.post('/presensi/toggle-daily-schedule', {
      status: val ? 1 : 0
    })
    
    toastMessage.value = val 
      ? 'Notifikasi jadwal harian berhasil diaktifkan!' 
      : 'Notifikasi jadwal harian dinonaktifkan!'
    showToast.value = true
    setTimeout(() => {
      showToast.value = false
    }, 3000)
  } catch (error) {
    console.error('Failed to update notification status:', error)
    isNotificationEnabled.value = !val
    
    toastMessage.value = 'Gagal memperbarui status notifikasi!'
    showToast.value = true
    setTimeout(() => {
      showToast.value = false
    }, 3000)
  }
}

const cards = [
  {
    title: 'Total Mahasiswa',
    value: props.stats.totalMahasiswa,
    icon: Users,
    color: 'bg-blue-500',
    description: 'Jumlah seluruh mahasiswa terdaftar'
  },
  {
    title: 'Total Dosen',
    value: props.stats.totalDosen,
    icon: UserCheck,
    color: 'bg-purple-500',
    description: 'Dosen dengan status aktif'
  },
  {
    title: 'Total Kelas',
    value: props.stats.totalKelas,
    icon: School,
    color: 'bg-indigo-500',
    description: 'Kelas pada semester aktif'
  }
]
</script>

<template>
  <AdminLayout>
    <Head title="Dashboard Admin" />

    <div class="space-y-6">
      <!-- Header -->
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-[#1F2937]">Dashboard Admin</h1>
        <p class="text-[#6B7280]">Statistik sistem informasi akademik saat ini.</p>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <Card v-for="card in cards" :key="card.title" class="border-none shadow-sm overflow-hidden group hover:shadow-md transition-all duration-300">
          <CardContent class="p-4 sm:p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-[#6B7280]">{{ card.title }}</p>
                <h3 class="text-3xl font-bold mt-1 text-[#1F2937]">{{ card.value }}</h3>
              </div>
              <div :class="[card.color, 'p-3 rounded-2xl text-white group-hover:scale-110 transition-transform duration-300']">
                <component :is="card.icon" class="w-6 h-6" />
              </div>
            </div>
            <p class="text-xs text-[#9CA3AF] mt-4">{{ card.description }}</p>
          </CardContent>
        </Card>

        <!-- Notification Toggle Card -->
        <Card class="border-none shadow-sm overflow-hidden group hover:shadow-md transition-all duration-300">
          <CardContent class="p-4 sm:p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-[#6B7280]">Notifikasi Harian</p>
                <div class="flex items-center gap-2 mt-2">
                  <span class="text-xs font-semibold" :class="isNotificationEnabled ? 'text-green-600' : 'text-gray-400'">
                    {{ isNotificationEnabled ? 'AKTIF' : 'NON-AKTIF' }}
                  </span>
                  <Switch 
                    v-model:checked="isNotificationEnabled" 
                    @update:checked="toggleNotification"
                  />
                </div>
              </div>
              <div class="bg-amber-500 p-3 rounded-2xl text-white group-hover:scale-110 transition-transform duration-300">
                <Bell class="w-6 h-6" />
              </div>
            </div>
            <p class="text-xs text-[#9CA3AF] mt-4">Kontrol notifikasi jadwal harian</p>
          </CardContent>
        </Card>
      </div>

      <!-- Attendance Today -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <Card class="border-none shadow-sm">
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-lg font-semibold text-[#1F2937]">Kehadiran Hari Ini (Hadir)</CardTitle>
            <CheckCircle2 class="h-5 w-5 text-green-500" />
          </CardHeader>
          <CardContent>
            <div class="text-3xl font-bold text-green-600">{{ stats.totalHadir }}</div>
            <p class="text-xs text-[#6B7280] mt-1">Mahasiswa hadir & telat hari ini</p>
            
            <div class="mt-4 h-2 w-full bg-gray-100 rounded-full overflow-hidden">
              <div 
                class="h-full bg-green-500 rounded-full" 
                :style="{ width: (stats.totalHadir / (stats.totalHadir + stats.totalTidakHadir) * 100) + '%' }"
              ></div>
            </div>
          </CardContent>
        </Card>

        <Card class="border-none shadow-sm">
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-lg font-semibold text-[#1F2937]">Kehadiran Hari Ini (Absen)</CardTitle>
            <XCircle class="h-5 w-5 text-red-500" />
          </CardHeader>
          <CardContent>
            <div class="text-3xl font-bold text-red-600">{{ stats.totalTidakHadir }}</div>
            <p class="text-xs text-[#6B7280] mt-1">Mahasiswa tidak hadir hari ini</p>

            <div class="mt-4 h-2 w-full bg-gray-100 rounded-full overflow-hidden">
              <div 
                class="h-full bg-red-500 rounded-full" 
                :style="{ width: (stats.totalTidakHadir / (stats.totalHadir + stats.totalTidakHadir) * 100) + '%' }"
              ></div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Rekap Per Prodi -->
      <Card class="border-none shadow-sm">
        <CardHeader>
          <CardTitle class="text-xl font-bold text-[#1F2937]">Rekap Kehadiran Per Program Studi</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="border-b border-gray-100">
                  <th class="py-4 px-4 font-semibold text-[#374151]">Program Studi</th>
                  <th class="py-4 px-4 font-semibold text-green-600">Hadir</th>
                  <th class="py-4 px-4 font-semibold text-red-600">Absen</th>
                  <th class="py-4 px-4 font-semibold text-[#374151]">Presentase</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="prodi in rekapProdi" :key="prodi.nama_prodi" class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                  <td class="py-4 px-4 font-medium text-[#1F2937]">{{ prodi.nama_prodi }}</td>
                  <td class="py-4 px-4 text-green-600 font-semibold">{{ prodi.total_hadir }}</td>
                  <td class="py-4 px-4 text-red-600 font-semibold">{{ prodi.total_tidak_hadir }}</td>
                  <td class="py-4 px-4">
                    <div class="flex items-center gap-3">
                      <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden min-w-[100px]">
                        <div 
                          class="h-full bg-blue-500" 
                          :style="{ width: (prodi.total_hadir / (prodi.total_hadir + prodi.total_tidak_hadir || 1) * 100) + '%' }"
                        ></div>
                      </div>
                      <span class="text-sm font-medium text-[#4B5563]">
                        {{ Math.round(prodi.total_hadir / (prodi.total_hadir + prodi.total_tidak_hadir || 1) * 100) }}%
                      </span>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Toast Notification -->
    <transition name="toast">
      <div v-if="showToast" class="fixed bottom-10 right-10 z-[100]">
        <div class="bg-[#1F2937] text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-3 border border-gray-700 backdrop-blur-sm bg-opacity-95">
          <div :class="toastMessage.includes('Gagal') ? 'bg-red-500' : 'bg-green-500'" class="p-1.5 rounded-full">
            <CheckCircle2 v-if="!toastMessage.includes('Gagal')" class="w-4 h-4 text-white" />
            <XCircle v-else class="w-4 h-4 text-white" />
          </div>
          <span class="text-sm font-medium">{{ toastMessage }}</span>
        </div>
      </div>
    </transition>
  </AdminLayout>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.toast-enter-from {
  opacity: 0;
  transform: translateY(20px) scale(0.95);
}

.toast-leave-to {
  opacity: 0;
  transform: translateY(20px) scale(0.95);
}
</style>
