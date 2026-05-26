<script setup>
import { ref, computed } from 'vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
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
  TrendingUp,
  UploadCloud,
  AlertTriangle
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

const page = usePage()

// Form Unggah Pembayaran KHS
const uploadForm = useForm({
  file: null,
  semester_id: props.summary.semester_id
})

const fileInputRef = ref(null)
const previewUrl = ref(null)

const handleFileChange = (e) => {
  const file = e.target.files[0]
  if (file) {
    uploadForm.file = file
    previewUrl.value = URL.createObjectURL(file)
  }
}

const triggerFileInput = () => {
  fileInputRef.value?.click()
}

const clearFile = () => {
  uploadForm.file = null
  previewUrl.value = null
  if (fileInputRef.value) fileInputRef.value.value = ''
}

const submitUpload = () => {
  uploadForm.post('/v2/mahasiswa/nilai/upload-pembayaran', {
    onSuccess: () => {
      uploadForm.reset()
      previewUrl.value = null
    }
  })
}

// Form Pengajuan Cetak KHS
const ajukanForm = useForm({
  semester_id: props.summary.semester_id
})

const submitAjukanCetak = () => {
  ajukanForm.post('/v2/mahasiswa/nilai/ajukan-cetak')
}

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
          <h1 class="text-xl sm:text-2xl font-bold text-[#1F1F1F]">
            {{ isRiwayat ? 'Riwayat Hasil Studi' : 'Kartu Hasil Studi (KHS)' }}
          </h1>
          <p class="text-sm text-gray-500">
            Daftar perolehan nilai matakuliah dan Indeks Prestasi Semester (IPS)
          </p>
        </div>

        <!-- Action Button -->
        <div class="w-full md:w-auto">
          <!-- Jika KHS Belum Dinilai sama sekali -->
          <div v-if="!summary.can_print && summary.can_view" class="flex items-center justify-center gap-2 w-full md:w-auto px-5 py-2.5 rounded-xl bg-gray-200 text-gray-400 text-sm font-bold cursor-not-allowed">
            <Printer class="w-4 h-4" /> Cetak KHS (Belum Tersedia)
          </div>

          <!-- Jika Sudah Lunas dan Ada Nilai -->
          <div v-else-if="summary.can_view" class="flex flex-col sm:flex-row items-center gap-3">
            <!-- Belum Mengajukan -->
            <button
              v-if="!summary.pengajuan"
              @click="submitAjukanCetak"
              :disabled="ajukanForm.processing"
              class="flex items-center justify-center gap-2 w-full md:w-auto px-5 py-2.5 rounded-xl bg-[#4B49AC] hover:bg-[#3a3888] text-white text-sm font-bold shadow-lg hover:shadow-xl transition-all cursor-pointer"
            >
              <span v-if="ajukanForm.processing" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
              <Printer class="w-4 h-4" /> Ajukan Cetak KHS Fisik
            </button>

            <!-- Pending (Status 0) -->
            <div
              v-else-if="summary.pengajuan.status === 0"
              class="flex items-center gap-2 px-4 py-2 rounded-xl bg-amber-50 text-amber-700 border border-amber-200 text-sm font-bold"
            >
              <Clock class="w-4 h-4 animate-pulse text-amber-500" /> Menunggu Cetak Akademik
            </div>

            <!-- Selesai (Status 1) -->
            <div
              v-else-if="summary.pengajuan.status === 1"
              class="flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 text-sm font-bold"
            >
              <CheckCircle2 class="w-4 h-4" /> KHS Fisik Siap Diambil di Loket
            </div>

            <!-- Ditolak (Status 2) -->
            <div v-else-if="summary.pengajuan.status === 2" class="flex flex-col items-end gap-1">
              <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-rose-50 text-rose-700 border border-rose-200 text-sm font-bold">
                <AlertCircle class="w-4 h-4" /> Pengajuan Cetak Ditolak
              </div>
              <span class="text-xs text-rose-600 font-medium">Alasan: {{ summary.pengajuan.keterangan || '-' }}</span>
              <button
                @click="submitAjukanCetak"
                :disabled="ajukanForm.processing"
                class="mt-1 text-xs text-[#4B49AC] hover:underline font-bold"
              >
                Ajukan Ulang
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Banner Notifikasi Sukses / Galat dari Sesi -->
      <div v-if="page.props.flash?.success || page.props.flash?.error" class="mb-4">
        <div v-if="page.props.flash?.success" class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-3 shadow-xs">
          <CheckCircle2 class="w-5 h-5 text-emerald-600 flex-shrink-0" />
          <span class="text-sm font-medium">{{ page.props.flash.success }}</span>
        </div>
        <div v-if="page.props.flash?.error" class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center gap-3 shadow-xs">
          <AlertTriangle class="w-5 h-5 text-rose-600 flex-shrink-0" />
          <span class="text-sm font-medium">{{ page.props.flash.error }}</span>
        </div>
      </div>

      <!-- Student Info Banner -->
      <div class="bg-gradient-to-br from-[#4B49AC] to-[#5957c2] p-6 rounded-2xl text-white shadow-md flex items-center gap-6 relative overflow-hidden">
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

      <!-- Jika Pembayaran Belum Lunas (Tampilkan form konfirmasi pembayaran) -->
      <div v-if="!summary.can_view" class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2 space-y-6">
          <div class="bg-white p-6 rounded-2xl border border-[#CDD1E1] shadow-sm space-y-4">
            <div class="flex items-start gap-4">
              <div class="p-3 rounded-xl bg-rose-50 text-rose-600 shrink-0">
                <AlertTriangle class="w-6 h-6" />
              </div>
              <div>
                <h2 class="text-lg font-bold text-gray-900">Akses KHS Ditangguhkan</h2>
                <p class="text-sm text-gray-500 mt-1">
                  Detail Kartu Hasil Studi (KHS) untuk semester ini belum dapat ditampilkan secara online karena administrasi keuangan Anda belum diverifikasi lunas.
                </p>
              </div>
            </div>

            <!-- Alert Status Pembayaran -->
            <div v-if="pembayaran" class="p-4 rounded-xl border bg-gray-50 flex items-start gap-3">
              <Clock v-if="pembayaran.status_pembayaran === 0 && pembayaran.keterangan !== 'Sudah'" class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5 animate-spin" />
              <AlertTriangle v-else class="w-5 h-5 text-rose-600 flex-shrink-0 mt-0.5" />
              <div>
                <div class="font-bold text-sm" :class="pembayaran.status_pembayaran === 0 && pembayaran.keterangan !== 'Sudah' ? 'text-amber-900' : 'text-rose-900'">
                  {{ pembayaran.status_pembayaran === 0 && pembayaran.keterangan !== 'Sudah' ? 'Menunggu Verifikasi Pembayaran' : 'Pembayaran Belum Lunas' }}
                </div>
                <p class="text-xs text-gray-500 mt-0.5">
                  {{ pembayaran.status_pembayaran === 0 && pembayaran.keterangan !== 'Sudah' 
                    ? `Bukti pembayaran yang diunggah pada ${pembayaran.created_at} sedang diperiksa oleh bagian administrasi.`
                    : 'Bukti pembayaran Anda belum disetujui atau belum bernilai lunas. Silakan lakukan pengunggahan ulang bukti pembayaran SPP/UKT semester berjalan.'
                  }}
                </p>
              </div>
            </div>
          </div>

          <!-- Upload Form Box -->
          <div v-if="!pembayaran || pembayaran.status_pembayaran === 0" class="bg-white p-6 rounded-2xl border border-[#CDD1E1] shadow-sm">
            <form @submit.prevent="submitUpload" class="space-y-4">
              <h3 class="font-bold text-[#1F1F1F] text-base">Konfirmasi & Unggah Bukti Pembayaran</h3>
              <p class="text-xs text-gray-500">Unggah bukti transfer atau kwitansi pembayaran resmi dari bank/kampus.</p>
              
              <input 
                ref="fileInputRef"
                type="file" 
                accept=".jpg,.png,.jpeg"
                class="hidden"
                @change="handleFileChange"
              />

              <div 
                @click="triggerFileInput"
                class="border-2 border-dashed border-gray-300 hover:border-[#4B49AC] bg-gray-50 hover:bg-[#F5F7FF] rounded-xl p-6 text-center cursor-pointer transition-all group"
              >
                <div v-if="!previewUrl" class="space-y-2">
                  <div class="w-12 h-12 bg-white rounded-lg flex items-center justify-center mx-auto shadow-xs text-[#4B49AC] group-hover:scale-110 transition-transform">
                    <UploadCloud class="w-6 h-6" />
                  </div>
                  <div class="text-sm font-bold text-gray-700">Pilih File Bukti Pembayaran</div>
                  <p class="text-xs text-gray-400">Maksimal 5MB. Format: JPG, PNG, atau JPEG</p>
                </div>

                <div v-else class="space-y-3">
                  <img :src="previewUrl" class="max-h-36 mx-auto rounded-lg shadow-sm object-contain" />
                  <div class="text-xs font-semibold text-[#4B49AC]">File: {{ uploadForm.file?.name }}</div>
                </div>
              </div>

              <div v-if="uploadForm.errors?.file" class="text-xs text-rose-600 font-medium">
                {{ uploadForm.errors.file }}
              </div>

              <div class="flex gap-3">
                <button 
                  v-if="previewUrl"
                  type="button"
                  @click="clearFile"
                  class="flex-1 px-4 py-2.5 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-100 font-bold text-sm transition-all"
                >
                  Batal
                </button>
                <button 
                  type="submit"
                  :disabled="!uploadForm.file || uploadForm.processing"
                  class="flex-1 px-4 py-2.5 rounded-xl bg-[#4B49AC] hover:bg-[#3a3888] disabled:bg-gray-300 text-white font-bold text-sm transition-all shadow-md flex items-center justify-center gap-2 cursor-pointer"
                >
                  <span v-if="uploadForm.processing" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                  <UploadCloud class="w-4 h-4 shrink-0" /> Unggah Pembayaran
                </button>
              </div>
            </form>
          </div>

          <div v-else-if="pembayaran?.bukti_pembayaran" class="p-4 rounded-xl border bg-gray-50 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <FileText class="w-5 h-5 text-gray-400" />
              <span class="text-sm font-medium text-gray-700">Bukti_Pembayaran_Semester.jpg</span>
            </div>
            <a :href="`/storage/${pembayaran.bukti_pembayaran}`" target="_blank" class="text-xs font-bold text-[#4B49AC] hover:underline">
              Lihat File
            </a>
          </div>
        </div>

        <!-- Sidebar Bantuan Keuangan/Akademik -->
        <div class="bg-white p-6 rounded-2xl border border-[#CDD1E1] shadow-sm space-y-4 self-start">
          <h3 class="font-bold text-[#1F1F1F] text-base">Butuh Bantuan?</h3>
          <p class="text-xs text-gray-500 leading-relaxed">
            Jika Anda sudah menyelesaikan pembayaran namun KHS belum terbuka, silakan serahkan bukti transfer fisik ke bagian loket Keuangan Kampus untuk segera diverifikasi secara manual.
          </p>
          <div class="pt-2">
            <Link href="/v2/mahasiswa/krs_pembayaran" class="text-xs font-bold text-[#4B49AC] hover:underline flex items-center gap-1">
              Periksa Menu KRS & Pembayaran <ChevronRight class="w-3.5 h-3.5" />
            </Link>
          </div>
        </div>
      </div>

      <!-- Jika Pembayaran Lunas (Tampilkan Data Nilai secara Normal) -->
      <div v-else class="space-y-6">
        <!-- Info & Metrik Card -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
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
          <div class="px-4 sm:px-6 py-4 border-b border-[#CDD1E1] flex items-center justify-between gap-3 bg-gray-50/50">
            <div class="flex items-center gap-2 sm:gap-3 min-w-0">
              <div class="p-2 rounded-lg bg-[#4B49AC] text-white shrink-0">
                <Award class="w-5 h-5" />
              </div>
              <h2 class="font-bold text-sm sm:text-lg text-[#1F1F1F] truncate">Rincian Nilai Matakuliah</h2>
            </div>
            <div class="shrink-0">
              <span
                v-if="summary.matkul_dinilai === summary.total_matkul && summary.total_matkul > 0"
                class="inline-flex items-center gap-1 px-2 sm:px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold border border-emerald-300"
              >
                <CheckCircle2 class="w-3.5 h-3.5 shrink-0" />
                <span class="hidden sm:inline">Penilaian Lengkap</span>
              </span>
              <span
                v-else
                class="inline-flex items-center gap-1 px-2 sm:px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-bold border border-amber-300"
              >
                <Clock class="w-3.5 h-3.5 shrink-0" />
                <span class="hidden sm:inline">Dalam Proses</span>
              </span>
            </div>
          </div>

          <div class="p-4 sm:p-6">
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
    </div>
  </AdminLayout>
</template>
