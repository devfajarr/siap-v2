<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { 
  GraduationCap, 
  BookOpen, 
  Award, 
  Printer, 
  AlertCircle, 
  CheckCircle2, 
  Clock, 
  FileText,
  ChevronRight,
  TrendingUp
} from 'lucide-vue-next'

const props = defineProps({
  mahasiswa: {
    type: Object,
    required: true
  },
  semesterRiwayat: {
    type: Object,
    default: null
  },
  isRiwayat: {
    type: Boolean,
    default: false
  },
  matkuls: {
    type: Array,
    default: () => []
  },
  summary: {
    type: Object,
    required: true
  }
})

// Helper untuk warna badge nilai huruf
const getGradeBadgeClass = (grade) => {
  if (!grade || grade === 'Belum Dinilai') {
    return 'bg-gray-100 text-gray-600 border-gray-200 font-medium'
  }
  if (grade.startsWith('A')) {
    return 'bg-emerald-100 text-emerald-700 border-emerald-300 font-extrabold shadow-xs'
  }
  if (grade.startsWith('B')) {
    return 'bg-blue-100 text-blue-700 border-blue-300 font-bold'
  }
  if (grade.startsWith('C')) {
    return 'bg-amber-100 text-amber-700 border-amber-300 font-bold'
  }
  return 'bg-rose-100 text-rose-700 border-rose-300 font-bold'
}
</script>

<template>
  <Head title="Rekap Nilai KHS" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Header Title & Breadcrumb -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
            <Link href="/v2/mahasiswa/dashboard" class="hover:text-[#4B49AC] transition-colors">Dashboard</Link>
            <ChevronRight class="w-4 h-4" />
            <span class="text-gray-700 font-semibold">{{ isRiwayat ? 'Riwayat Nilai' : 'Nilai KHS Aktif' }}</span>
          </div>
          <h1 class="text-2xl font-bold text-[#1F1F1F]">
            {{ isRiwayat ? 'Riwayat Hasil Studi' : 'Kartu Hasil Studi (KHS)' }}
          </h1>
          <p class="text-sm text-gray-500">
            Daftar perolehan nilai matakuliah dan Indeks Prestasi Semester (IPS)
          </p>
        </div>

        <!-- Action Button -->
        <div>
          <a 
            v-if="summary.can_print && summary.semester_id" 
            :href="`/v2/mahasiswa/khs/${summary.semester_id}`" 
            target="_blank"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#4B49AC] hover:bg-[#3a3888] text-white text-sm font-bold shadow-lg hover:shadow-xl transition-all"
          >
            <Printer class="w-4 h-4" /> Cetak Dokumen KHS
          </a>
          <button 
            v-else
            disabled
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-200 text-gray-400 text-sm font-bold cursor-not-allowed"
            title="Belum ada matakuliah yang dinilai pada semester ini"
          >
            <Printer class="w-4 h-4" /> Cetak KHS (Belum Tersedia)
          </button>
        </div>
      </div>

      <!-- Info & Metrik Card -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Student Info Banner -->
        <div class="md:col-span-2 bg-gradient-to-br from-[#4B49AC] to-[#5957c2] p-6 rounded-2xl text-white shadow-md flex items-center gap-6 relative overflow-hidden">
          <div class="absolute right-0 top-0 opacity-10 translate-x-4 -translate-y-4 pointer-events-none">
            <GraduationCap class="w-48 h-48" />
          </div>
          <div class="space-y-1 relative z-10 flex-1">
            <div class="text-xs uppercase font-semibold text-indigo-200 tracking-wider">Identitas Mahasiswa</div>
            <h2 class="text-xl font-bold">{{ mahasiswa.nama_lengkap }}</h2>
            <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-indigo-100 pt-2 border-t border-white/20">
              <div><span class="font-semibold text-white">NIM:</span> {{ mahasiswa.nim }}</div>
              <div><span class="font-semibold text-white">Prodi:</span> {{ mahasiswa.prodi }}</div>
              <div><span class="font-semibold text-white">Semester:</span> {{ mahasiswa.semester }}</div>
            </div>
          </div>
        </div>

        <!-- Total SKS -->
        <div class="bg-white p-6 rounded-2xl border border-[#CDD1E1] shadow-sm flex flex-col justify-between">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Beban SKS Semester</span>
            <div class="p-2 rounded-lg bg-indigo-50 text-[#4B49AC]">
              <BookOpen class="w-5 h-5" />
            </div>
          </div>
          <div>
            <div class="text-3xl font-extrabold text-[#1F1F1F] mt-2">{{ summary.total_sks }} <span class="text-sm font-normal text-gray-500">SKS</span></div>
            <p class="text-xs text-gray-500 mt-1">Total sks yang diambil semester ini</p>
          </div>
        </div>

        <!-- IPS Sementara -->
        <div class="bg-white p-6 rounded-2xl border border-[#CDD1E1] shadow-sm flex flex-col justify-between">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Indeks Prestasi (IPS)</span>
            <div class="p-2 rounded-lg bg-emerald-50 text-emerald-600">
              <TrendingUp class="w-5 h-5" />
            </div>
          </div>
          <div>
            <div class="text-3xl font-extrabold text-emerald-600 mt-2">{{ summary.ips }}</div>
            <div class="flex items-center gap-1.5 mt-1">
              <span class="text-xs text-gray-500">Status Penilaian:</span>
              <span class="text-xs font-bold text-[#4B49AC]">{{ summary.matkul_dinilai }} / {{ summary.total_matkul }} Matkul</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Table Card -->
      <div class="bg-white rounded-2xl border border-[#CDD1E1] shadow-sm overflow-hidden">
        <div class="p-6 border-b border-[#CDD1E1] flex items-center justify-between bg-gray-50/50">
          <div class="flex items-center gap-3">
            <div class="p-2 rounded-lg bg-[#4B49AC] text-white">
              <Award class="w-5 h-5" />
            </div>
            <h2 class="font-bold text-lg text-[#1F1F1F]">Rincian Nilai Matakuliah</h2>
          </div>
          <div>
            <span 
              v-if="summary.matkul_dinilai === summary.total_matkul && summary.total_matkul > 0"
              class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold border border-emerald-300"
            >
              <CheckCircle2 class="w-3.5 h-3.5" /> Penilaian Lengkap
            </span>
            <span 
              v-else
              class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-bold border border-amber-300"
            >
              <Clock class="w-3.5 h-3.5" /> Penilaian Dalam Proses
            </span>
          </div>
        </div>

        <div class="p-6">
          <div v-if="matkuls.length === 0" class="py-12 text-center space-y-3">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto text-gray-400">
              <FileText class="w-8 h-8" />
            </div>
            <h3 class="font-bold text-gray-700 text-lg">Belum Ada Data Matakuliah</h3>
            <p class="text-gray-500 text-sm max-w-sm mx-auto">
              Tidak ada matakuliah yang terdaftar pada semester ini. Pastikan Anda telah mengisi KRS atau hubungi admin akademik.
            </p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full border-collapse">
              <thead>
                <tr class="border-b border-gray-200 bg-gray-50/75 text-left text-xs font-bold uppercase tracking-wider text-gray-500">
                  <th class="py-3.5 px-4 rounded-l-xl">No</th>
                  <th class="py-3.5 px-4">Kode</th>
                  <th class="py-3.5 px-4">Mata Kuliah</th>
                  <th class="py-3.5 px-4 text-center">Bobot SKS</th>
                  <th class="py-3.5 px-4 text-center">Nilai Angka</th>
                  <th class="py-3.5 px-4 text-center rounded-r-xl">Predikat Nilai</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr 
                  v-for="(item, index) in matkuls" 
                  :key="item.id"
                  class="hover:bg-[#F5F7FF]/50 transition-colors"
                >
                  <td class="py-4 px-4 text-sm text-gray-600">{{ index + 1 }}</td>
                  <td class="py-4 px-4 text-sm font-bold text-[#4B49AC] font-mono">{{ item.kode }}</td>
                  <td class="py-4 px-4 text-sm font-semibold text-[#1F1F1F] max-w-md">{{ item.nama_matkul }}</td>
                  <td class="py-4 px-4 text-sm text-center font-bold text-gray-700">{{ item.sks }} SKS</td>
                  <td class="py-4 px-4 text-sm text-center font-medium text-gray-600">
                    {{ item.nilai_total !== null ? item.nilai_total : '-' }}
                  </td>
                  <td class="py-4 px-4 text-center">
                    <span 
                      :class="getGradeBadgeClass(item.nilai_huruf)"
                      class="inline-block px-3.5 py-1 rounded-lg text-xs border text-center min-w-[50px]"
                    >
                      {{ item.nilai_huruf }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
