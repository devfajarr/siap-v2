<script setup>
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { 
  BookOpen, 
  ChevronRight, 
  Clock, 
  CheckCircle2, 
  AlertCircle,
  FileText,
  UserCheck
} from 'lucide-vue-next'

const props = defineProps({
  mahasiswa: {
    type: Object,
    required: true
  },
  matkuls: {
    type: Array,
    default: () => []
  },
  krs: {
    type: Object,
    default: null
  }
})

// Sum total SKS
const totalSks = computed(() => {
  return props.matkuls.reduce((sum, item) => sum + (item.sks || 0), 0)
})
</script>

<template>
  <Head title="Pemantauan KRS Anak" />

  <AdminLayout>
    <div class="space-y-6">
      
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white p-5 rounded-xl border border-[#CDD1E1] shadow-sm">
        <div class="flex items-center gap-3.5">
          <div class="p-3 bg-[#4B49AC]/10 text-[#4B49AC] rounded-xl">
            <FileText class="w-7 h-7" />
          </div>
          <div>
            <h1 class="text-xl font-bold text-[#1F1F1F]">Rencana Studi Kuliah (KRS)</h1>
            <p class="text-sm text-gray-500">Monitor mata kuliah pilihan anak Anda di semester aktif ini</p>
          </div>
        </div>
        
        <div class="text-xs sm:text-sm bg-gray-50 border border-gray-200 px-4 py-2 rounded-xl text-gray-700 font-medium">
          DPA Anak: <strong class="text-[#4B49AC] font-bold">{{ mahasiswa.pembimbing_akademik }}</strong>
        </div>
      </div>

      <!-- KRS Status Checklist Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Status KRS Card -->
        <div class="bg-white p-6 rounded-xl border border-[#CDD1E1] shadow-sm flex flex-col justify-between">
          <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Status Dokumen KRS</span>
          <div class="mt-4">
            <span 
              v-if="krs && krs.status_krs === 1"
              class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-emerald-100 text-emerald-700 text-sm font-bold border border-emerald-300"
            >
              <CheckCircle2 class="w-4 h-4" /> Disetujui Akademik
            </span>
            <span 
              v-else-if="krs"
              class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-amber-100 text-amber-700 text-sm font-bold border border-amber-300"
            >
              <Clock class="w-4 h-4 animate-pulse text-amber-500" /> Sedang Diproses
            </span>
            <span 
              v-else
              class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-rose-100 text-rose-700 text-sm font-bold border border-rose-300"
            >
              <AlertCircle class="w-4 h-4" /> Belum Diisi
            </span>
          </div>
        </div>

        <!-- Student Signature -->
        <div class="bg-white p-6 rounded-xl border border-[#CDD1E1] shadow-sm flex flex-col justify-between">
          <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Persetujuan Mahasiswa</span>
          <div class="mt-4 flex items-center gap-2">
            <CheckCircle2 v-if="krs && krs.setuju_mahasiswa === 1" class="w-5 h-5 text-emerald-600" />
            <AlertCircle v-else class="w-5 h-5 text-gray-400" />
            <span class="text-sm font-semibold" :class="krs && krs.setuju_mahasiswa === 1 ? 'text-emerald-700 font-bold' : 'text-gray-500'">
              {{ krs && krs.setuju_mahasiswa === 1 ? 'Sudah Ditandatangani' : 'Belum Ditandatangani' }}
            </span>
          </div>
        </div>

        <!-- DPA Signature -->
        <div class="bg-white p-6 rounded-xl border border-[#CDD1E1] shadow-sm flex flex-col justify-between">
          <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Verifikasi Dosen Wali (DPA)</span>
          <div class="mt-4 flex items-center gap-2">
            <CheckCircle2 v-if="krs && krs.setuju_pa === 1" class="w-5 h-5 text-emerald-600" />
            <AlertCircle v-else class="w-5 h-5 text-gray-400" />
            <span class="text-sm font-semibold" :class="krs && krs.setuju_pa === 1 ? 'text-emerald-700 font-bold' : 'text-gray-500'">
              {{ krs && krs.setuju_pa === 1 ? 'Sudah Diverifikasi' : 'Belum Diverifikasi' }}
            </span>
          </div>
        </div>
      </div>

      <!-- KRS Subject List Table -->
      <div class="bg-white rounded-xl border border-[#CDD1E1] shadow-sm overflow-hidden">
        <div class="p-5 border-b border-[#CDD1E1] bg-gray-50/50 flex items-center justify-between gap-4">
          <div class="flex items-center gap-2">
            <BookOpen class="w-5 h-5 text-[#4B49AC]" />
            <h2 class="text-base font-bold text-[#1F1F1F]">Daftar Mata Kuliah Semester {{ mahasiswa.semester }}</h2>
          </div>
          <span class="text-sm font-bold text-[#4B49AC] bg-indigo-50 border border-indigo-100 px-3 py-1 rounded-lg">
            Total SKS: {{ totalSks }} SKS
          </span>
        </div>

        <div class="p-6">
          <div v-if="matkuls.length === 0" class="py-12 text-center text-gray-500">
            Belum ada rencana studi matakuliah yang terbit untuk kelas mahasiswa ini.
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full text-sm text-left border-collapse">
              <thead>
                <tr class="border-b border-[#CDD1E1] text-gray-500 font-bold uppercase tracking-wider text-xs">
                  <th class="py-3.5 px-4 w-12 text-center">No</th>
                  <th class="py-3.5 px-4 w-28">Kode</th>
                  <th class="py-3.5 px-4">Mata Kuliah</th>
                  <th class="py-3.5 px-4 text-center w-24">Teori</th>
                  <th class="py-3.5 px-4 text-center w-24">Praktek</th>
                  <th class="py-3.5 px-4 text-center w-28">Jumlah SKS</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr 
                  v-for="(item, index) in matkuls" 
                  :key="item.id"
                  class="hover:bg-gray-50/50 transition-colors"
                >
                  <td class="py-4 px-4 text-gray-500 text-center">{{ index + 1 }}</td>
                  <td class="py-4 px-4 font-bold text-[#4B49AC] font-mono">{{ item.kode }}</td>
                  <td class="py-4 px-4 font-semibold text-[#1F1F1F]">{{ item.nama_matkul }}</td>
                  <td class="py-4 px-4 text-gray-650 text-center">{{ item.teori }} Jam</td>
                  <td class="py-4 px-4 text-gray-650 text-center">{{ item.praktek }} Jam</td>
                  <td class="py-4 px-4 font-bold text-gray-700 text-center">{{ item.sks }} SKS</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
