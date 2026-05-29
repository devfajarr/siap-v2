<script setup>
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { 
  CreditCard, 
  ChevronRight, 
  Clock, 
  CheckCircle2, 
  AlertCircle,
  FileImage,
  ExternalLink
} from 'lucide-vue-next'

const props = defineProps({
  mahasiswa: {
    type: Object,
    required: true
  },
  pembayarans: {
    type: Array,
    default: () => []
  }
})
</script>

<template>
  <Head title="Pemantauan Keuangan SPP Anak" />

  <AdminLayout>
    <div class="space-y-6">
      
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white p-5 rounded-xl border border-[#CDD1E1] shadow-sm">
        <div class="flex items-center gap-3.5">
          <div class="p-3 bg-[#4B49AC]/10 text-[#4B49AC] rounded-xl shrink-0">
            <CreditCard class="w-7 h-7" />
          </div>
          <div>
            <h1 class="text-lg sm:text-xl font-bold text-[#1F1F1F]">Keuangan & Pembayaran SPP/UKT</h1>
            <p class="text-xs sm:text-sm text-gray-500">Pantau verifikasi pelunasan uang kuliah anak Anda per semester</p>
          </div>
        </div>
        
        <div class="w-full sm:w-auto text-center sm:text-left text-xs sm:text-sm bg-gray-50 border border-gray-200 px-4 py-2.5 rounded-xl text-gray-700 font-medium">
          Mahasiswa: <strong class="text-[#4B49AC] font-bold">{{ mahasiswa.nama_lengkap }}</strong> <span class="mx-1 text-gray-300">|</span> NIM: <strong>{{ mahasiswa.nim }}</strong>
        </div>
      </div>

      <!-- Pembayaran History List -->
      <div class="bg-white rounded-xl border border-[#CDD1E1] shadow-sm overflow-hidden">
        <div class="p-5 border-b border-[#CDD1E1] bg-gray-50/50 flex items-center gap-2">
          <CreditCard class="w-5 h-5 text-[#4B49AC]" />
          <h2 class="text-base font-bold text-[#1F1F1F]">Riwayat Transaksi Keuangan</h2>
        </div>

        <div class="p-6">
          <div v-if="pembayarans.length === 0" class="py-12 text-center text-gray-500">
            Belum ada catatan pengajuan atau transaksi pembayaran untuk mahasiswa ini.
          </div>

          <div v-else class="space-y-4">
            <div 
              v-for="pembayaran in pembayarans" 
              :key="pembayaran.id"
              class="border border-[#CDD1E1] rounded-xl p-5 hover:border-[#4B49AC] transition-all bg-white shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4 md:gap-6"
            >
              <!-- Left side: Semester & upload info -->
              <div class="flex flex-col sm:flex-row sm:items-center gap-4 w-full md:w-auto">
                <div class="flex items-center justify-between sm:justify-start gap-3 w-full sm:w-auto">
                  <h3 class="text-lg font-bold text-[#1F1F1F]">{{ pembayaran.semester }}</h3>
                  <!-- Inline Status Pill for Mobile -->
                  <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-50 text-[#4B49AC] border border-indigo-100 sm:hidden">
                    Status: {{ pembayaran.keterangan }}
                  </span>
                </div>

                <!-- Desktop Status Box -->
                <div class="hidden sm:flex flex-col p-2.5 rounded-xl bg-indigo-50 text-[#4B49AC] shrink-0 font-bold text-center min-w-[80px]">
                  <span class="text-[10px] text-indigo-400 uppercase tracking-wider">Status</span>
                  <span class="text-xs font-extrabold">{{ pembayaran.keterangan }}</span>
                </div>

                <div class="space-y-1.5 w-full sm:w-auto">
                  <div class="text-xs text-gray-500 font-medium flex items-center gap-1.5">
                    <Clock class="w-3.5 h-3.5 text-gray-400" /> Tanggal Upload: {{ pembayaran.created_at }}
                  </div>
                  
                  <!-- Attachment file -->
                  <div v-if="pembayaran.bukti_pembayaran" class="pt-0.5">
                    <a 
                      :href="`/storage/${pembayaran.bukti_pembayaran}`" 
                      target="_blank"
                      class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-50 hover:bg-gray-100 border border-gray-200 text-xs font-bold text-[#4B49AC] transition-colors w-full sm:w-auto justify-center sm:justify-start"
                    >
                      <FileImage class="w-3.5 h-3.5 text-gray-400" />
                      <span>Lampiran Bukti Transfer</span>
                      <ExternalLink class="w-3 h-3 text-[#4B49AC]" />
                    </a>
                  </div>
                </div>
              </div>

              <!-- Right side: Verification Badge -->
              <div class="flex items-center gap-3 w-full md:w-auto justify-between md:justify-end pt-4 md:pt-0 border-t md:border-t-0 border-gray-100">
                <div class="text-xs font-bold text-gray-500 uppercase tracking-wider md:hidden">Verifikasi Keuangan</div>
                <div>
                  <span 
                    v-if="pembayaran.status_pembayaran === 1 && pembayaran.keterangan === 'Sudah'"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200"
                  >
                    <CheckCircle2 class="w-4 h-4 text-emerald-600" /> Lunas Terverifikasi
                  </span>
                  <span 
                    v-else-if="pembayaran.status_pembayaran === 0 && pembayaran.keterangan === 'Belum'"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-amber-50 text-amber-700 text-xs font-bold border border-amber-200"
                  >
                    <Clock class="w-4 h-4 animate-pulse text-amber-500" /> Menunggu Verifikasi
                  </span>
                  <span 
                    v-else
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-rose-50 text-rose-700 text-xs font-bold border border-rose-200"
                  >
                    <AlertCircle class="w-4 h-4 text-rose-600" /> Ditolak / Bermasalah
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
