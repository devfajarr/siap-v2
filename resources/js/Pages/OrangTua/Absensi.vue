<script setup>
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { 
  ClipboardCheck, 
  User, 
  Calendar, 
  AlertTriangle,
  CheckCircle,
  XCircle,
  Clock,
  BookOpen
} from 'lucide-vue-next'

const props = defineProps({
  mahasiswa: {
    type: Object,
    required: true
  },
  rekapAbsensi: {
    type: Array,
    default: () => []
  },
  riwayatAbsensi: {
    type: Array,
    default: () => []
  }
})
</script>

<template>
  <Head title="Pemantauan Absensi Anak" />

  <AdminLayout>
    <div class="space-y-6">
      
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white p-5 rounded-xl border border-[#CDD1E1] shadow-sm">
        <div class="flex items-center gap-3.5">
          <div class="p-3 bg-[#4B49AC]/10 text-[#4B49AC] rounded-xl">
            <ClipboardCheck class="w-7 h-7" />
          </div>
          <div>
            <h1 class="text-xl font-bold text-[#1F1F1F]">Pemantauan Absensi Mahasiswa</h1>
            <p class="text-sm text-gray-500">Monitor rekapitulasi dan riwayat kehadiran kuliah anak Anda</p>
          </div>
        </div>
        
        <div class="text-xs sm:text-sm bg-gray-50 border border-gray-200 px-4 py-2 rounded-xl text-gray-700 font-medium">
          Mahasiswa: <strong class="text-[#4B49AC] font-bold">{{ mahasiswa.nama_lengkap }}</strong> | NIM: <strong>{{ mahasiswa.nim }}</strong>
        </div>
      </div>

      <!-- Warning Alert if attendance rate is low -->
      <div 
        v-if="rekapAbsensi.some(r => r.persentase_kehadiran < 75)" 
        class="bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-xl flex items-start gap-3"
      >
        <AlertTriangle class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" />
        <div>
          <h4 class="text-sm font-bold text-rose-800">Peringatan: Kehadiran di Bawah Batas Minimum 75%</h4>
          <p class="text-xs text-rose-700 mt-1">
            Beberapa mata kuliah di bawah ini memiliki tingkat kehadiran kurang dari 75%. Mahasiswa terancam tidak diperbolehkan mengikuti ujian akhir (UAS) pada mata kuliah terkait jika persentase ini tidak diperbaiki.
          </p>
        </div>
      </div>

      <!-- Rekap Per Mata Kuliah -->
      <div class="bg-white rounded-xl border border-[#CDD1E1] shadow-sm overflow-hidden">
        <div class="p-5 border-b border-[#CDD1E1] bg-gray-50/50 flex items-center gap-2">
          <BookOpen class="w-5 h-5 text-[#4B49AC]" />
          <h2 class="text-base font-bold text-[#1F1F1F]">Rekapitulasi Kehadiran per Mata Kuliah</h2>
        </div>
        
        <div class="p-6">
          <div v-if="rekapAbsensi.length === 0" class="py-12 text-center text-gray-500">
            Belum ada rekapitulasi absensi untuk semester ini.
          </div>
          
          <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div 
              v-for="rekap in rekapAbsensi" 
              :key="rekap.matkul_id"
              class="border border-[#CDD1E1] rounded-xl p-5 bg-white space-y-4 hover:border-[#4B49AC] transition-all hover:shadow-sm"
            >
              <div class="space-y-1">
                <div class="flex items-start justify-between gap-4">
                  <h3 class="font-bold text-[#1F1F1F] leading-snug line-clamp-1">{{ rekap.nama_matkul }}</h3>
                  <span 
                    :class="rekap.persentase_kehadiran >= 75 ? 'bg-emerald-55 text-emerald-700' : 'bg-rose-100 text-rose-700'"
                    class="px-2.5 py-1 rounded-lg text-xs font-extrabold border shrink-0"
                  >
                    {{ rekap.persentase_kehadiran }}%
                  </span>
                </div>
                <p class="text-xs text-gray-500 font-medium">Dosen: {{ rekap.dosen }}</p>
              </div>

              <!-- Progress Bar -->
              <div class="space-y-1">
                <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden border border-gray-200">
                  <div 
                    :class="rekap.persentase_kehadiran >= 75 ? 'bg-emerald-500' : 'bg-rose-500'"
                    class="h-full rounded-full transition-all duration-500" 
                    :style="{ width: rekap.persentase_kehadiran + '%' }"
                  />
                </div>
              </div>

              <!-- Stats Breakdown Grid -->
              <div class="grid grid-cols-5 divide-x divide-gray-100 bg-gray-50 rounded-lg p-2.5 text-center text-xs font-semibold">
                <div>
                  <p class="text-[10px] text-emerald-600">Hadir</p>
                  <p class="font-bold text-[#1F1F1F] mt-0.5 text-sm">{{ rekap.hadir }}</p>
                </div>
                <div>
                  <p class="text-[10px] text-amber-600">Late</p>
                  <p class="font-bold text-[#1F1F1F] mt-0.5 text-sm">{{ rekap.terlambat }}</p>
                </div>
                <div>
                  <p class="text-[10px] text-blue-600">Sakit</p>
                  <p class="font-bold text-[#1F1F1F] mt-0.5 text-sm">{{ rekap.sakit }}</p>
                </div>
                <div>
                  <p class="text-[10px] text-indigo-600">Izin</p>
                  <p class="font-bold text-[#1F1F1F] mt-0.5 text-sm">{{ rekap.izin }}</p>
                </div>
                <div>
                  <p class="text-[10px] text-rose-600">Alpa</p>
                  <p class="font-bold text-rose-700 mt-0.5 text-sm">{{ rekap.alfa }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Riwayat Absensi Detail -->
      <div class="bg-white rounded-xl border border-[#CDD1E1] shadow-sm overflow-hidden">
        <div class="p-5 border-b border-[#CDD1E1] bg-gray-50/50 flex items-center justify-between gap-4">
          <div class="flex items-center gap-2">
            <Calendar class="w-5 h-5 text-[#4B49AC]" />
            <h2 class="text-base font-bold text-[#1F1F1F]">Riwayat Kehadiran Kuliah Terbaru</h2>
          </div>
        </div>

        <div class="p-6">
          <div v-if="riwayatAbsensi.length === 0" class="py-12 text-center text-gray-500">
            Belum ada catatan riwayat kehadiran kuliah terbaru.
          </div>
          
          <div v-else class="overflow-x-auto">
            <table class="w-full text-sm text-left border-collapse">
              <thead>
                <tr class="border-b border-[#CDD1E1] text-gray-500 font-bold">
                  <th class="py-3.5 px-4">Pertemuan</th>
                  <th class="py-3.5 px-4">Tanggal</th>
                  <th class="py-3.5 px-4">Mata Kuliah</th>
                  <th class="py-3.5 px-4">Dosen</th>
                  <th class="py-3.5 px-4 text-center">Status Kehadiran</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr 
                  v-for="riwayat in riwayatAbsensi" 
                  :key="riwayat.id"
                  class="hover:bg-gray-50/50 transition-colors"
                >
                  <td class="py-4 px-4 font-bold text-[#4B49AC]">Ke-{{ riwayat.pertemuan }}</td>
                  <td class="py-4 px-4 text-gray-600 font-medium">{{ riwayat.tanggal }}</td>
                  <td class="py-4 px-4 font-semibold text-[#1F1F1F]">{{ riwayat.nama_matkul }}</td>
                  <td class="py-4 px-4 text-gray-600">{{ riwayat.dosen }}</td>
                  <td class="py-4 px-4 text-center">
                    <span 
                      v-if="riwayat.status_code === 'H'"
                      class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold border border-emerald-300"
                    >
                      <CheckCircle class="w-3.5 h-3.5" /> Hadir
                    </span>
                    <span 
                      v-else-if="riwayat.status_code === 'T'"
                      class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-bold border border-amber-300"
                    >
                      <Clock class="w-3.5 h-3.5" /> Terlambat
                    </span>
                    <span 
                      v-else-if="riwayat.status_code === 'A'"
                      class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-rose-100 text-rose-700 text-xs font-bold border border-rose-300"
                    >
                      <XCircle class="w-3.5 h-3.5" /> Alpa
                    </span>
                    <span 
                      v-else-if="riwayat.status_code === 'I' || riwayat.status_code === 'S'"
                      class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-bold border border-blue-300"
                    >
                      <Clock class="w-3.5 h-3.5" /> {{ riwayat.status_code === 'I' ? 'Izin' : 'Sakit' }}
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
