<script setup>
import { ref, watch, computed } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Badge } from '@/Components/ui/badge'
import {
  ClipboardList,
  BookOpen,
  Users,
  Calendar,
  CheckCircle2,
  AlertCircle,
  Clock,
  ChevronRight,
  GraduationCap,
  Sparkles,
  FileCheck,
  BarChart3,
} from 'lucide-vue-next'

const props = defineProps({
  jadwals: { type: Array, required: true },
})

const page = usePage()

// ── Toast ──────────────────────────────────────────────────────────────────
const showToast   = ref(false)
const toastMsg    = ref('')
const toastType   = ref('success')

watch(() => page.props.flash, (flash) => {
  if (flash?.success) { toastMsg.value = flash.success; toastType.value = 'success'; showToast.value = true }
  if (flash?.error)   { toastMsg.value = flash.error;   toastType.value = 'error';   showToast.value = true }
  if (showToast.value) setTimeout(() => { showToast.value = false }, 3500)
}, { deep: true, immediate: true })

// ── Statistics ─────────────────────────────────────────────────────────────
const totalKelas   = computed(() => props.jadwals.length)
const nilaiLengkap = computed(() => props.jadwals.filter(j => j.status_nilai === 'lengkap').length)
const sudahDiajukan = computed(() => props.jadwals.filter(j => ['diajukan', 'disetujui'].includes(j.status_nilai)).length)
const disetujui    = computed(() => props.jadwals.filter(j => j.status_nilai === 'disetujui').length)

// ── Helpers ────────────────────────────────────────────────────────────────
const statusConfig = {
  disetujui: { label: 'Disetujui',    cls: 'bg-emerald-50 text-emerald-700 border-emerald-200', icon: CheckCircle2 },
  diajukan:  { label: 'Menunggu',     cls: 'bg-amber-50 text-amber-700 border-amber-200',       icon: Clock },
  lengkap:   { label: 'Siap Diajukan', cls: 'bg-blue-50 text-blue-700 border-blue-200',          icon: FileCheck },
  sebagian:  { label: 'Sebagian',     cls: 'bg-orange-50 text-orange-700 border-orange-200',    icon: AlertCircle },
  belum:     { label: 'Belum Diisi',  cls: 'bg-slate-50 text-slate-500 border-slate-200',       icon: AlertCircle },
}

const komponen = ['tugas', 'uts', 'uas', 'etika', 'aktif']
const komponenLabel = { tugas: 'Tugas', uts: 'UTS', uas: 'UAS', etika: 'Etika', aktif: 'Aktif' }
</script>

<template>
  <AdminLayout>
    <Head title="Data Nilai Mahasiswa" />

    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
            <BarChart3 class="w-7 h-7 text-[#4B49AC]" />
            Data Nilai Mahasiswa
          </h1>
          <p class="text-slate-500 text-sm mt-1">
            Kelola nilai mahasiswa untuk setiap mata kuliah yang Anda ampu.
          </p>
        </div>
      </div>

      <!-- Statistics -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <Card class="border-none shadow-sm bg-gradient-to-br from-indigo-50/50 to-indigo-100/30">
          <CardContent class="p-4 flex items-center gap-3">
            <div class="p-2.5 bg-indigo-500 text-white rounded-xl shadow-sm">
              <GraduationCap class="w-5 h-5" />
            </div>
            <div>
              <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Kelas</p>
              <h3 class="text-xl font-extrabold text-slate-800 mt-0.5">{{ totalKelas }}</h3>
            </div>
          </CardContent>
        </Card>

        <Card class="border-none shadow-sm bg-gradient-to-br from-blue-50/50 to-blue-100/30">
          <CardContent class="p-4 flex items-center gap-3">
            <div class="p-2.5 bg-blue-500 text-white rounded-xl shadow-sm">
              <FileCheck class="w-5 h-5" />
            </div>
            <div>
              <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Nilai Lengkap</p>
              <h3 class="text-xl font-extrabold text-slate-800 mt-0.5">{{ nilaiLengkap }}</h3>
            </div>
          </CardContent>
        </Card>

        <Card class="border-none shadow-sm bg-gradient-to-br from-amber-50/50 to-amber-100/30">
          <CardContent class="p-4 flex items-center gap-3">
            <div class="p-2.5 bg-amber-500 text-white rounded-xl shadow-sm">
              <Clock class="w-5 h-5" />
            </div>
            <div>
              <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Menunggu / Diajukan</p>
              <h3 class="text-xl font-extrabold text-slate-800 mt-0.5">{{ sudahDiajukan }}</h3>
            </div>
          </CardContent>
        </Card>

        <Card class="border-none shadow-sm bg-gradient-to-br from-emerald-50/50 to-emerald-100/30">
          <CardContent class="p-4 flex items-center gap-3">
            <div class="p-2.5 bg-emerald-500 text-white rounded-xl shadow-sm">
              <CheckCircle2 class="w-5 h-5" />
            </div>
            <div>
              <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Disetujui</p>
              <h3 class="text-xl font-extrabold text-slate-800 mt-0.5">{{ disetujui }}</h3>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Jadwal Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <Card
          v-for="jadwal in jadwals"
          :key="jadwal.id"
          class="border-none shadow-md overflow-hidden hover:shadow-lg transition-all duration-300 flex flex-col group"
        >
          <!-- Card Header -->
          <CardHeader class="p-5 pb-3 bg-white border-b border-slate-50">
            <div class="flex items-start justify-between gap-3">
              <div class="relative pl-4 flex-1">
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-[#4B49AC] rounded-full group-hover:scale-y-110 transition-transform" />
                <CardTitle
                  class="text-base font-bold text-slate-800 leading-snug line-clamp-2"
                  :title="jadwal.matkul"
                >
                  {{ jadwal.matkul }}
                </CardTitle>
                <span class="text-xs font-mono text-[#4B49AC] font-bold tracking-wider block mt-1">
                  SKS: {{ jadwal.sks || '-' }}
                </span>
              </div>

              <!-- Status Badge -->
              <div class="flex-shrink-0">
                <Badge
                  v-if="statusConfig[jadwal.status_nilai]"
                  class="shadow-none font-semibold px-2 py-0.5 rounded-full flex items-center gap-1 text-[11px] border"
                  :class="statusConfig[jadwal.status_nilai].cls"
                >
                  <component :is="statusConfig[jadwal.status_nilai].icon" class="w-3.5 h-3.5" />
                  {{ statusConfig[jadwal.status_nilai].label }}
                </Badge>
              </div>
            </div>
          </CardHeader>

          <!-- Card Content -->
          <CardContent class="p-5 flex-1 flex flex-col justify-between space-y-4">
            <!-- Details -->
            <div class="space-y-2">
              <div class="flex items-center text-sm text-slate-600 gap-2">
                <Users class="w-4 h-4 text-slate-400 flex-shrink-0" />
                <span class="truncate">Kelas: <strong class="text-slate-800">{{ jadwal.kelas }}</strong></span>
              </div>
              <div class="flex items-center text-sm text-slate-600 gap-2">
                <BookOpen class="w-4 h-4 text-slate-400 flex-shrink-0" />
                <span class="truncate">Prodi: <strong class="text-slate-800">{{ jadwal.prodi }}</strong></span>
              </div>
              <div class="flex items-center text-sm text-slate-600 gap-2">
                <Calendar class="w-4 h-4 text-slate-400 flex-shrink-0" />
                <span>Hari: <strong class="text-slate-800">{{ jadwal.hari || '-' }}</strong></span>
              </div>
            </div>

            <!-- Komponen Progress -->
            <div class="space-y-2 pt-3 border-t border-slate-100">
              <div class="flex items-center justify-between text-xs font-semibold text-slate-500 mb-1">
                <span class="flex items-center gap-1">
                  <Sparkles class="w-3.5 h-3.5 text-amber-500" />
                  Kelengkapan Komponen
                </span>
                <span>{{ jadwal.completion.total }}/5</span>
              </div>
              <!-- Progress bar -->
              <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden mb-2">
                <div
                  class="h-full rounded-full transition-all duration-500"
                  :class="jadwal.completion.total === 5 ? 'bg-emerald-500' : jadwal.completion.total > 0 ? 'bg-[#4B49AC]' : 'bg-slate-300'"
                  :style="{ width: `${(jadwal.completion.total / 5) * 100}%` }"
                />
              </div>
              <!-- Komponen chips -->
              <div class="flex flex-wrap gap-1.5">
                <span
                  v-for="k in komponen"
                  :key="k"
                  class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-[10px] font-bold border transition-colors"
                  :class="jadwal.completion[k]
                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                    : 'bg-slate-50 text-slate-400 border-slate-200'"
                >
                  <CheckCircle2 v-if="jadwal.completion[k]" class="w-2.5 h-2.5" />
                  <AlertCircle v-else class="w-2.5 h-2.5" />
                  {{ komponenLabel[k] }}
                </span>
              </div>
            </div>

            <!-- Action -->
            <div class="pt-3 border-t border-slate-100">
              <Link :href="route('v2.dosen.nilai.show', jadwal.id)" class="w-full block">
                <Button
                  class="w-full flex items-center justify-center gap-1.5 transition-colors"
                  :class="jadwal.status_nilai === 'disetujui'
                    ? 'bg-emerald-600 hover:bg-emerald-700 text-white'
                    : 'bg-[#4B49AC] hover:bg-[#3f3e91] text-white'"
                >
                  <ClipboardList class="w-4 h-4" />
                  {{ jadwal.status_nilai === 'disetujui' ? 'Lihat Rekap' : 'Kelola Nilai' }}
                  <ChevronRight class="w-4 h-4 ml-auto" />
                </Button>
              </Link>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Empty State -->
      <Card v-if="jadwals.length === 0" class="border-dashed border-2 bg-transparent">
        <CardContent class="p-12 text-center">
          <div class="flex flex-col items-center gap-3">
            <BarChart3 class="w-12 h-12 text-slate-300" />
            <h3 class="text-lg font-medium text-slate-700">Belum Ada Jadwal Mengajar</h3>
            <p class="text-slate-400 text-sm max-w-sm">Anda tidak memiliki jadwal mengajar terdaftar pada semester ini.</p>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Toast -->
    <transition name="toast">
      <div v-if="showToast" class="fixed bottom-10 right-10 z-[100]">
        <div class="bg-[#1F2937] text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-3 border border-gray-700 backdrop-blur-sm">
          <div :class="toastType === 'error' ? 'bg-red-500' : 'bg-green-500'" class="p-1.5 rounded-full">
            <CheckCircle2 v-if="toastType === 'success'" class="w-4 h-4 text-white" />
            <AlertCircle v-else class="w-4 h-4 text-white" />
          </div>
          <span class="text-sm font-medium">{{ toastMsg }}</span>
        </div>
      </div>
    </transition>
  </AdminLayout>
</template>

<style scoped>
.toast-enter-active, .toast-leave-active { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateY(20px) scale(0.95); }
</style>
