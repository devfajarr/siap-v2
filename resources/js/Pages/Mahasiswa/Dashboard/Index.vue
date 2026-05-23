<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { 
  GraduationCap, 
  CheckCircle2, 
  BookOpen, 
  Calendar, 
  Clock, 
  MapPin, 
  User, 
  AlertCircle, 
  Check,
  XCircle,
  Sparkles
} from 'lucide-vue-next'

defineProps({
  mahasiswa: {
    type: Object,
    required: true
  },
  totalKehadiran: {
    type: Number,
    required: true
  },
  totalMatakuliah: {
    type: Number,
    required: true
  },
  statusKrs: {
    type: String,
    required: true
  },
  jadwals: {
    type: Array,
    default: () => []
  }
})
</script>

<template>
  <Head title="Dashboard Mahasiswa" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Welcome Banner -->
      <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#4B49AC] to-[#6b69d6] p-5 sm:p-8 text-white shadow-xl">
        <div class="absolute right-0 top-0 opacity-10 translate-x-8 -translate-y-8 pointer-events-none hidden md:block">
          <GraduationCap class="w-72 h-72" />
        </div>

        <div class="relative z-10 flex flex-col gap-5 sm:gap-6">
          <!-- Text Content -->
          <div class="space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-xs font-semibold uppercase tracking-wider">
              <Sparkles class="w-3.5 h-3.5" /> Portal Mahasiswa V2
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight leading-snug">Selamat Datang, {{ mahasiswa.nama_lengkap }}</h1>
            <p class="text-indigo-100 text-sm sm:text-base leading-relaxed">
              Pantau informasi akademik, kehadiran perkuliahan, dan jadwal kuliah harian Anda dalam satu dashboard terpadu.
            </p>
          </div>

          <!-- Student Info — Grid 3 kolom dengan divider vertikal -->
          <div class="grid grid-cols-3 divide-x divide-white/20 bg-white/10 backdrop-blur-md rounded-xl border border-white/20 overflow-hidden">
            <div class="px-3 sm:px-5 py-3">
              <p class="text-[10px] sm:text-xs text-indigo-200 uppercase tracking-wider font-medium">NIM</p>
              <p class="font-bold text-sm sm:text-base mt-0.5 truncate">{{ mahasiswa.nim }}</p>
            </div>
            <div class="px-3 sm:px-5 py-3">
              <p class="text-[10px] sm:text-xs text-indigo-200 uppercase tracking-wider font-medium">Prodi / Kelas</p>
              <p class="font-bold text-sm sm:text-base mt-0.5 truncate">{{ mahasiswa.prodi }} - {{ mahasiswa.nama_kelas }}</p>
            </div>
            <div class="px-3 sm:px-5 py-3">
              <p class="text-[10px] sm:text-xs text-indigo-200 uppercase tracking-wider font-medium">Semester</p>
              <p class="font-bold text-sm sm:text-base mt-0.5">{{ mahasiswa.semester }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Metrics Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6">
        <!-- Kehadiran Card -->
        <div class="bg-white p-6 rounded-xl border border-[#CDD1E1] shadow-sm flex items-center justify-between">
          <div class="space-y-1">
            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Kehadiran</p>
            <h2 class="text-3xl font-bold text-[#1F1F1F]">{{ totalKehadiran }} <span class="text-sm font-normal text-gray-500">Pertemuan</span></h2>
          </div>
          <div class="p-4 rounded-xl bg-indigo-50 text-[#4B49AC]">
            <CheckCircle2 class="w-8 h-8" />
          </div>
        </div>

        <!-- Matakuliah Card -->
        <div class="bg-white p-6 rounded-xl border border-[#CDD1E1] shadow-sm flex items-center justify-between">
          <div class="space-y-1">
            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Mata Kuliah Diambil</p>
            <h2 class="text-3xl font-bold text-[#1F1F1F]">{{ totalMatakuliah }} <span class="text-sm font-normal text-gray-500">Matkul</span></h2>
          </div>
          <div class="p-4 rounded-xl bg-emerald-50 text-emerald-600">
            <BookOpen class="w-8 h-8" />
          </div>
        </div>

        <!-- Status KRS Card -->
        <div class="bg-white p-6 rounded-xl border border-[#CDD1E1] shadow-sm flex items-center justify-between">
          <div class="space-y-1">
            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Status Pengisian KRS</p>
            <div class="flex items-center gap-2 mt-1">
              <span 
                :class="statusKrs === 'Sudah' ? 'bg-emerald-100 text-emerald-700 border-emerald-300' : 'bg-amber-100 text-amber-700 border-amber-300'"
                class="px-3 py-1 rounded-full text-sm font-bold border flex items-center gap-1.5"
              >
                <span :class="statusKrs === 'Sudah' ? 'bg-emerald-500' : 'bg-amber-500'" class="w-2 h-2 rounded-full animate-pulse"></span>
                {{ statusKrs === 'Sudah' ? 'Terverifikasi' : 'Belum Diisi' }}
              </span>
            </div>
          </div>
          <div class="p-4 rounded-xl bg-purple-50 text-purple-600">
            <GraduationCap class="w-8 h-8" />
          </div>
        </div>
      </div>

      <!-- Jadwal Hari Ini Section -->
      <div class="bg-white rounded-xl border border-[#CDD1E1] shadow-sm overflow-hidden">
        <div class="p-6 border-b border-[#CDD1E1] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-gray-50/50">
          <div class="flex items-center gap-3">
            <div class="p-2.5 rounded-lg bg-[#4B49AC] text-white">
              <Calendar class="w-6 h-6" />
            </div>
            <div>
              <h2 class="text-lg font-bold text-[#1F1F1F]">Jadwal Perkuliahan Hari Ini</h2>
              <p class="text-sm text-gray-500">Jadwal tatap muka atau ujian yang berlangsung pada hari ini</p>
            </div>
          </div>
          <div class="text-sm font-semibold text-[#4B49AC] bg-indigo-50 px-3.5 py-1.5 rounded-lg border border-indigo-100">
            {{ new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) }}
          </div>
        </div>

        <div class="p-6">
          <div v-if="jadwals.length === 0" class="py-12 px-4 text-center space-y-3">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto text-gray-400">
              <Calendar class="w-8 h-8" />
            </div>
            <h3 class="text-lg font-bold text-gray-700">Tidak Ada Jadwal Hari Ini</h3>
            <p class="text-gray-500 text-sm max-w-sm mx-auto">
              Saat ini tidak ada sesi perkuliahan yang terjadwal untuk kelas Anda pada hari ini. Waktunya untuk belajar mandiri atau mengerjakan tugas!
            </p>
          </div>

          <div v-else class="grid grid-cols-1 gap-4">
            <div 
              v-for="jadwal in jadwals" 
              :key="jadwal.id"
              class="border border-[#CDD1E1] rounded-xl p-5 hover:border-[#4B49AC] transition-all bg-white shadow-sm hover:shadow-md flex flex-col md:flex-row items-start md:items-center justify-between gap-6"
            >
              <!-- Info Matkul -->
              <div class="flex items-start gap-4 flex-1">
                <div class="p-3 bg-indigo-50 text-[#4B49AC] rounded-xl font-bold text-center min-w-[70px]">
                  <div class="text-xs uppercase text-indigo-400">Jam</div>
                  <div class="text-sm font-extrabold">{{ jadwal.waktu_mulai.substring(0, 5) }}</div>
                  <div class="text-xs text-gray-500">{{ jadwal.waktu_selesai.substring(0, 5) }}</div>
                </div>

                <div class="space-y-1.5">
                  <h3 class="text-lg font-bold text-[#1F1F1F]">{{ jadwal.matkul }}</h3>
                  <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 text-sm text-gray-600">
                    <div class="flex items-center gap-1.5 font-medium">
                      <User class="w-4 h-4 text-gray-400" /> {{ jadwal.dosen }}
                    </div>
                    <div class="flex items-center gap-1.5 font-medium">
                      <MapPin class="w-4 h-4 text-gray-400" /> Ruang {{ jadwal.ruangan }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- Status Kehadiran Tag -->
              <div class="flex items-center gap-3 w-full md:w-auto justify-between md:justify-end pt-4 md:pt-0 border-t md:border-t-0 border-gray-100">
                <div class="text-sm font-semibold text-gray-500 md:hidden">Status Presensi:</div>
                <div v-if="jadwal.sudah_presensi">
                  <span 
                    v-if="jadwal.status_kehadiran === 'H'"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-100 text-emerald-700 text-sm font-bold border border-emerald-300"
                  >
                    <Check class="w-4 h-4" /> Hadir Tepat Waktu
                  </span>
                  <span 
                    v-else-if="jadwal.status_kehadiran === 'T'"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-amber-100 text-amber-700 text-sm font-bold border border-amber-300"
                  >
                    <Clock class="w-4 h-4" /> Hadir Terlambat
                  </span>
                  <span 
                    v-else-if="jadwal.status_kehadiran === 'A'"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-rose-100 text-rose-700 text-sm font-bold border border-rose-300"
                  >
                    <XCircle class="w-4 h-4" /> Alpa / Tidak Hadir
                  </span>
                  <span 
                    v-else-if="jadwal.status_kehadiran === 'I' || jadwal.status_kehadiran === 'S'"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-blue-100 text-blue-700 text-sm font-bold border border-blue-300"
                  >
                    <AlertCircle class="w-4 h-4" /> {{ jadwal.status_kehadiran === 'I' ? 'Izin' : 'Sakit' }}
                  </span>
                </div>
                <div v-else>
                  <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gray-100 text-gray-600 text-sm font-medium border border-gray-200">
                    <Clock class="w-4 h-4" /> Belum Dipresensi
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
