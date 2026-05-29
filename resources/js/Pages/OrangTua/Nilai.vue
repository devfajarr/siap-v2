<script setup>
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { 
  GraduationCap, 
  BookOpen, 
  Award, 
  AlertCircle, 
  Clock, 
  ChevronRight,
  ChevronDown,
  ChevronUp,
  TrendingUp,
  AlertTriangle,
  Lock
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
  },
  pembayaran: {
    type: Object,
    default: null
  }
})

// Expanded rows tracker for detailed component grades
const expandedRows = ref([])

const toggleRow = (id) => {
  if (expandedRows.value.includes(id)) {
    expandedRows.value = expandedRows.value.filter(rowId => rowId !== id)
  } else {
    expandedRows.value.push(id)
  }
}

// Grade badge color helper
const getGradeBadgeClass = (grade) => {
  if (!grade || grade === 'Belum Dinilai') {
    return 'bg-gray-150 text-gray-650 border-gray-250 font-medium'
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
  <Head title="Pemantauan Nilai Anak" />

  <AdminLayout>
    <div class="space-y-6">
      
      <!-- Header Title & Breadcrumb -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
            <Link href="/v2/orang-tua/dashboard" class="hover:text-[#4B49AC] transition-colors">Dashboard</Link>
            <ChevronRight class="w-4 h-4" />
            <span class="text-gray-700 font-semibold">{{ isRiwayat ? 'Riwayat Nilai' : 'Nilai KHS Aktif' }}</span>
          </div>
          <h1 class="text-xl sm:text-2xl font-bold text-[#1F1F1F]">
            {{ isRiwayat ? 'Riwayat Hasil Studi Anak' : 'Kartu Hasil Studi (KHS) Anak' }}
          </h1>
          <p class="text-sm text-gray-500">
            Pemantauan perolehan nilai mata kuliah dan Indeks Prestasi Semester (IPS)
          </p>
        </div>
      </div>

      <!-- Student Profile Card -->
      <div class="bg-gradient-to-br from-[#4B49AC] to-[#5957c2] p-6 rounded-2xl text-white shadow-md flex items-center gap-6 relative overflow-hidden">
        <div class="absolute right-0 top-0 opacity-10 translate-x-4 -translate-y-4 pointer-events-none">
          <GraduationCap class="w-48 h-48" />
        </div>
        <div class="space-y-1 relative z-10 flex-1">
          <div class="text-xs uppercase font-semibold text-indigo-200 tracking-wider">Mahasiswa Terpantau</div>
          <h2 class="text-xl font-bold">{{ mahasiswa.nama_lengkap }}</h2>
          <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-indigo-100 pt-2 border-t border-white/20">
            <div><span class="font-semibold text-white">NIM:</span> {{ mahasiswa.nim }}</div>
            <div><span class="font-semibold text-white">Prodi:</span> {{ mahasiswa.prodi }}</div>
            <div><span class="font-semibold text-white">Semester KHS:</span> {{ mahasiswa.semester }}</div>
          </div>
        </div>
      </div>

      <!-- IF ACCESS IS SUSPENDED / TUITION IS NOT PAID -->
      <div v-if="!summary.ips && matkuls.length === 0" class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2 space-y-6">
          <div class="bg-white p-6 rounded-2xl border border-[#CDD1E1] shadow-sm space-y-4">
            <div class="flex items-start gap-4">
              <div class="p-3 rounded-xl bg-rose-50 text-[#FF4747] shrink-0">
                <Lock class="w-6 h-6" />
              </div>
              <div>
                <h2 class="text-lg font-bold text-gray-900">Akses KHS Ditangguhkan</h2>
                <p class="text-sm text-gray-500 mt-1">
                  Detail Kartu Hasil Studi (KHS) mahasiswa untuk semester ini belum dapat ditampilkan secara online karena administrasi keuangan/SPP belum diverifikasi lunas.
                </p>
              </div>
            </div>

            <!-- Alert Payment Status -->
            <div v-if="pembayaran" class="p-4 rounded-xl border bg-gray-50 flex items-start gap-3">
              <Clock v-if="pembayaran.status_pembayaran === 0" class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5 animate-spin" />
              <AlertTriangle v-else class="w-5 h-5 text-rose-600 flex-shrink-0 mt-0.5" />
              <div>
                <div class="font-bold text-sm" :class="pembayaran.status_pembayaran === 0 ? 'text-amber-900' : 'text-rose-900'">
                  {{ pembayaran.status_pembayaran === 0 ? 'Bukti Pembayaran Sedang Diverifikasi' : 'Administrasi Keuangan Belum Lunas' }}
                </div>
                <p class="text-xs text-gray-500 mt-0.5">
                  {{ pembayaran.status_pembayaran === 0 
                    ? `Bukti pembayaran mahasiswa yang diunggah pada ${pembayaran.created_at} sedang diperiksa oleh bagian administrasi keuangan.`
                    : 'Bukti pembayaran mahasiswa belum disetujui atau belum bernilai lunas. Silakan ingatkan mahasiswa untuk mengunggah ulang bukti pembayaran kuliah yang valid.'
                  }}
                </p>
              </div>
            </div>
            <div v-else class="p-4 rounded-xl border bg-gray-50 flex items-start gap-3">
              <AlertTriangle class="w-5 h-5 text-rose-600 flex-shrink-0 mt-0.5" />
              <div>
                <div class="font-bold text-sm text-rose-900">Belum Ada Pengajuan Pembayaran</div>
                <p class="text-xs text-gray-500 mt-0.5">
                  Mahasiswa belum mengunggah bukti pembayaran SPP/UKT untuk semester ini.
                </p>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-[#CDD1E1] shadow-sm space-y-4 self-start">
          <h3 class="font-bold text-[#1F1F1F] text-base">Informasi Keuangan</h3>
          <p class="text-xs text-gray-500 leading-relaxed">
            Sistem akademik membatasi visualisasi nilai KHS sebelum administrasi keuangan mahasiswa diselesaikan. Harap hubungi bagian keuangan kampus jika ada kendala pembayaran UKT/SPP.
          </p>
          <div class="pt-2">
            <Link href="/v2/orang-tua/keuangan" class="text-xs font-bold text-[#4B49AC] hover:underline flex items-center gap-1">
              Periksa Riwayat Keuangan Anak <ChevronRight class="w-3.5 h-3.5" />
            </Link>
          </div>
        </div>
      </div>

      <!-- IF PAID / GRAD DATA AVAILABLE -->
      <div v-else class="space-y-6">
        <!-- Info & Metrik Card -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6">
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
              <p class="text-xs text-gray-500 mt-1">Total beban sks yang ditempuh semester ini</p>
            </div>
          </div>

          <!-- IPS -->
          <div class="bg-white p-6 rounded-2xl border border-[#CDD1E1] shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Indeks Prestasi Semester (IPS)</span>
              <div class="p-2 rounded-lg bg-emerald-50 text-emerald-600">
                <TrendingUp class="w-5 h-5" />
              </div>
            </div>
            <div>
              <div class="text-3xl font-extrabold text-emerald-600 mt-2">{{ summary.ips }}</div>
              <div class="flex items-center gap-1.5 mt-1">
                <span class="text-xs text-gray-500">Status Penilaian:</span>
                <span class="text-xs font-bold text-[#4B49AC]">{{ summary.matkul_dinilai }} / {{ summary.total_matkul }} Matkul Dinilai</span>
              </div>
            </div>
          </div>

          <!-- Keuangan Status -->
          <div class="bg-white p-6 rounded-2xl border border-[#CDD1E1] shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Administrasi Keuangan</span>
              <div class="p-2 rounded-lg bg-purple-50 text-purple-600">
                <Award class="w-5 h-5" />
              </div>
            </div>
            <div>
              <div class="text-3xl font-extrabold text-emerald-600 mt-2">LUNAS</div>
              <p class="text-xs text-gray-500 mt-1">Pembayaran SPP terverifikasi lunas</p>
            </div>
          </div>
        </div>

        <!-- Detail Table Card -->
        <div class="bg-white rounded-2xl border border-[#CDD1E1] shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-[#CDD1E1] flex items-center justify-between gap-3 bg-gray-50/50">
            <div class="flex items-center gap-2">
              <div class="p-2 rounded-lg bg-[#4B49AC] text-white">
                <Award class="w-5 h-5" />
              </div>
              <h2 class="font-bold text-lg text-[#1F1F1F]">Rincian Transkrip Nilai</h2>
            </div>
            <span class="text-xs text-gray-500 font-bold hidden sm:inline">
              * Klik baris mata kuliah untuk melihat detail rincian komponen nilai (Tugas, UTS, UAS, dll.)
            </span>
          </div>

          <div class="p-6">
            <div class="overflow-x-auto">
              <table class="w-full border-collapse">
                <thead>
                  <tr class="border-b border-gray-200 bg-gray-50/75 text-left text-xs font-bold uppercase tracking-wider text-gray-500">
                    <th class="py-3.5 px-4 rounded-l-xl w-12"></th>
                    <th class="py-3.5 px-4 w-12 text-center">No</th>
                    <th class="py-3.5 px-4 w-28">Kode</th>
                    <th class="py-3.5 px-4">Mata Kuliah</th>
                    <th class="py-3.5 px-4 text-center w-28">Bobot SKS</th>
                    <th class="py-3.5 px-4 text-center w-28">Nilai Angka</th>
                    <th class="py-3.5 px-4 text-center w-36 rounded-r-xl">Predikat Nilai</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <template v-for="(item, index) in matkuls" :key="item.id">
                    <!-- Main row -->
                    <tr 
                      @click="toggleRow(item.id)"
                      class="hover:bg-[#F5F7FF]/50 transition-colors cursor-pointer"
                      :class="{ 'bg-[#F5F7FF]/30': expandedRows.includes(item.id) }"
                    >
                      <td class="py-4 px-4 text-center">
                        <ChevronDown v-if="!expandedRows.includes(item.id)" class="w-4 h-4 text-gray-400 mx-auto" />
                        <ChevronUp v-else class="w-4 h-4 text-[#4B49AC] mx-auto" />
                      </td>
                      <td class="py-4 px-4 text-sm text-gray-600 text-center">{{ index + 1 }}</td>
                      <td class="py-4 px-4 text-sm font-bold text-[#4B49AC] font-mono">{{ item.kode }}</td>
                      <td class="py-4 px-4 text-sm font-semibold text-[#1F1F1F]">{{ item.nama_matkul }}</td>
                      <td class="py-4 px-4 text-sm text-center font-bold text-gray-700">{{ item.sks }} SKS</td>
                      <td class="py-4 px-4 text-sm text-center font-semibold text-gray-700">
                        {{ item.nilai_total !== null ? item.nilai_total : '-' }}
                      </td>
                      <td class="py-4 px-4 text-center">
                        <span 
                          :class="getGradeBadgeClass(item.nilai_huruf)"
                          class="inline-block px-3.5 py-1 rounded-lg text-xs border text-center min-w-[60px]"
                        >
                          {{ item.nilai_huruf }}
                        </span>
                      </td>
                    </tr>

                    <!-- Collapsible grade details row -->
                    <tr v-if="expandedRows.includes(item.id)">
                      <td colspan="7" class="bg-[#F5F7FF]/20 px-8 py-4 border-t border-gray-100">
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 bg-white p-4 rounded-xl border border-[#CDD1E1]/60 shadow-xs max-w-4xl">
                          <div class="text-center p-2 rounded-lg bg-gray-50 border border-gray-100">
                            <span class="block text-[10px] uppercase font-bold text-gray-400">Rata-Rata Tugas</span>
                            <strong class="block text-base text-[#1F1F1F] mt-1">{{ item.detail.tugas }}</strong>
                          </div>
                          <div class="text-center p-2 rounded-lg bg-gray-50 border border-gray-100">
                            <span class="block text-[10px] uppercase font-bold text-gray-400">Nilai UTS</span>
                            <strong class="block text-base text-[#1F1F1F] mt-1">{{ item.detail.uts }}</strong>
                          </div>
                          <div class="text-center p-2 rounded-lg bg-gray-50 border border-gray-100">
                            <span class="block text-[10px] uppercase font-bold text-gray-400">Nilai UAS</span>
                            <strong class="block text-base text-[#1F1F1F] mt-1">{{ item.detail.uas }}</strong>
                          </div>
                          <div class="text-center p-2 rounded-lg bg-gray-50 border border-gray-100">
                            <span class="block text-[10px] uppercase font-bold text-gray-400">Nilai Etika</span>
                            <strong class="block text-base text-[#1F1F1F] mt-1">{{ item.detail.etika }}</strong>
                          </div>
                          <div class="text-center p-2 rounded-lg bg-gray-50 border border-gray-100 col-span-2 sm:col-span-1">
                            <span class="block text-[10px] uppercase font-bold text-gray-400">Keaktifan</span>
                            <strong class="block text-base text-[#1F1F1F] mt-1">{{ item.detail.aktif }}</strong>
                          </div>
                        </div>
                      </td>
                    </tr>
                  </template>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
