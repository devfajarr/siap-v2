<script setup>
import { ref, computed } from 'vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { 
  CreditCard, 
  FileText, 
  UploadCloud, 
  CheckCircle2, 
  Clock, 
  AlertTriangle, 
  Printer, 
  ChevronRight, 
  GraduationCap, 
  ShieldCheck, 
  Send, 
  PenTool,
  BookOpen,
  Info
} from 'lucide-vue-next'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/Components/ui/dialog'

const props = defineProps({
  mahasiswa: {
    type: Object,
    required: true
  },
  matkuls: {
    type: Array,
    default: () => []
  },
  pembayaran: {
    type: Object,
    default: null
  },
  krs: {
    type: Object,
    default: null
  },
  cekStatusKrs: {
    type: Number,
    default: 0
  }
})

const page = usePage()

// Form Unggah Pembayaran
const uploadForm = useForm({
  file: null
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
  uploadForm.post('/v2/mahasiswa/krs_pembayaran/upload', {
    onSuccess: () => {
      uploadForm.reset()
      previewUrl.value = null
    }
  })
}

// Form Pengajuan KRS Awal
const pengajuanForm = useForm({})
const ajukanKRS = () => {
  pengajuanForm.post('/v2/mahasiswa/krs_pembayaran/pengajuan')
}

// Form Persetujuan / Penandatanganan KRS
const persetujuanForm = useForm({})
const showConfirmModal = ref(false)

const tandatanganiKRS = () => {
  persetujuanForm.put(`/v2/mahasiswa/krs_pembayaran/persetujuan/${props.krs.id}`, {
    onSuccess: () => {
      showConfirmModal.value = false
    }
  })
}

// Total SKS
const totalSks = computed(() => props.matkuls.reduce((sum, item) => sum + item.sks, 0))
</script>

<template>
  <Head title="KRS & Pembayaran" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Header Title & Breadcrumb -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
            <Link href="/v2/mahasiswa/dashboard" class="hover:text-[#4B49AC] transition-colors">Dashboard</Link>
            <ChevronRight class="w-4 h-4" />
            <span class="text-gray-700 font-semibold">KRS & Pembayaran</span>
          </div>
          <h1 class="text-xl sm:text-2xl font-bold text-[#1F1F1F]">
            Kartu Rencana Studi & Pembayaran
          </h1>
          <p class="text-xs sm:text-sm text-gray-500">
            Unggah bukti pembayaran semester dan verifikasi pengajuan rencana studi (KRS)
          </p>
        </div>
      </div>

      <!-- Banner Notifikasi Sukses / Galat dari Sesi -->
      <div v-if="page.props.flash?.success || page.props.flash?.error" class="mb-4">
        <div v-if="page.props.flash?.success" class="p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-3 shadow-xs">
          <CheckCircle2 class="w-5 h-5 text-emerald-600 flex-shrink-0" />
          <span class="text-sm font-medium">{{ page.props.flash.success }}</span>
        </div>
        <div v-if="page.props.flash?.error" class="p-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 flex items-center gap-3 shadow-xs">
          <AlertTriangle class="w-5 h-5 text-rose-600 flex-shrink-0" />
          <span class="text-sm font-medium">{{ page.props.flash.error }}</span>
        </div>
      </div>

      <!-- Student Info Banner -->
      <div class="bg-gradient-to-br from-[#4B49AC] to-[#5957c2] p-4 sm:p-6 rounded-lg text-white shadow-md flex flex-col md:flex-row md:items-center justify-between gap-6 relative overflow-hidden">
        <div class="absolute right-0 top-0 opacity-10 translate-x-4 -translate-y-4 pointer-events-none">
          <GraduationCap class="w-64 h-64" />
        </div>
        <div class="space-y-1 relative z-10">
          <div class="text-xs uppercase font-semibold text-indigo-200 tracking-wider">Identitas Mahasiswa</div>
          <h2 class="text-lg sm:text-xl font-bold">{{ mahasiswa.nama_lengkap }}</h2>
          <div class="flex flex-wrap gap-x-4 sm:gap-x-6 gap-y-1 text-xs sm:text-sm text-indigo-100 pt-2 border-t border-white/20">
            <div><span class="font-semibold text-white">NIM:</span> {{ mahasiswa.nim }}</div>
            <div><span class="font-semibold text-white">Prodi:</span> {{ mahasiswa.prodi }}</div>
            <div><span class="font-semibold text-white">Semester:</span> {{ mahasiswa.semester }}</div>
          </div>
        </div>
        <div class="relative z-10 bg-white/10 backdrop-blur-md p-4 rounded-lg border border-white/20 w-full md:max-w-sm">
          <div class="text-xs text-indigo-200">Dosen Pembimbing Akademik:</div>
          <div class="text-sm font-bold text-white mt-0.5 flex items-center gap-1.5">
            <ShieldCheck class="w-4 h-4 text-emerald-400" />
            {{ mahasiswa.pembimbing_akademik }}
          </div>
        </div>
      </div>

      <!-- Grid Status Pembayaran & Pengajuan KRS -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Kolom Kiri: Status Pembayaran & Upload Bukti -->
        <div class="bg-white p-4 sm:p-6 rounded-lg border border-[#CDD1E1] shadow-sm flex flex-col justify-between space-y-6">
          <div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-4 mb-4">
              <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-lg bg-indigo-50 text-[#4B49AC] shrink-0">
                  <CreditCard class="w-5 h-5" />
                </div>
                <div>
                  <h2 class="font-bold text-base sm:text-lg text-[#1F1F1F]">Status Pembayaran</h2>
                  <p class="text-xs text-gray-500">Bukti pembayaran semester berjalan</p>
                </div>
              </div>

              <!-- Badge Status Pembayaran -->
              <div class="shrink-0 self-start sm:self-auto">
                <div v-if="!pembayaran" class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 text-xs font-bold flex items-center gap-1.5">
                  <AlertTriangle class="w-3.5 h-3.5" /> Belum Diunggah
                </div>
                <div v-else-if="pembayaran.status_pembayaran === 0" class="px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 border border-amber-200 text-xs font-bold flex items-center gap-1.5">
                  <Clock class="w-3.5 h-3.5 animate-spin" /> Sedang Diproses / Pending
                </div>
                <div v-else-if="pembayaran.status_pembayaran === 1 && pembayaran.keterangan === 'Sudah'" class="px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold flex items-center gap-1.5">
                  <CheckCircle2 class="w-3.5 h-3.5" /> Lunas Terverifikasi
                </div>
                <div v-else class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 text-xs font-bold flex items-center gap-1.5">
                  <AlertTriangle class="w-3.5 h-3.5" /> Pengecekan Akademik
                </div>
              </div>
            </div>

            <!-- Pesan Peringatan Berdasarkan Status -->
            <div v-if="pembayaran">
              <div v-if="pembayaran.status_pembayaran === 0 && pembayaran.keterangan === 'Belum'" class="p-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-sm font-medium mb-4 flex items-start gap-3">
                <AlertTriangle class="w-5 h-5 text-rose-600 flex-shrink-0 mt-0.5" />
                <div>
                  <div class="font-bold text-rose-900">Pembayaran Belum Lunas</div>
                  <p class="text-xs mt-0.5">Bukti pembayaran Anda belum terverifikasi lunas. Segera hubungi Bagian Administrasi / Akademik.</p>
                </div>
              </div>
              <div v-else-if="pembayaran.status_pembayaran === 0" class="p-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-sm font-medium mb-4 flex items-start gap-3">
                <Clock class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" />
                <div>
                  <div class="font-bold text-amber-900">Menunggu Verifikasi Admin</div>
                  <p class="text-xs mt-0.5">Bukti pembayaran yang Anda unggah pada {{ pembayaran.created_at }} sedang diverifikasi oleh tim administrasi.</p>
                </div>
              </div>
              <div v-else-if="pembayaran.status_pembayaran === 1 && pembayaran.keterangan === 'Sudah'" class="p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium mb-4 flex items-start gap-3">
                <CheckCircle2 class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" />
                <div>
                  <div class="font-bold text-emerald-900">Pembayaran Berhasil Diverifikasi</div>
                  <p class="text-xs mt-0.5">Pembayaran Anda telah dinyatakan lunas. Anda dapat melanjutkan ke proses verifikasi & pengajuan Kartu Rencana Studi (KRS).</p>
                </div>
              </div>
            </div>

            <!-- Upload Zone -->
            <div v-if="!pembayaran || pembayaran.status_pembayaran === 0">
              <form @submit.prevent="submitUpload" class="space-y-4">
                <label class="block text-sm font-semibold text-gray-700">Unggah Bukti Pembayaran Baru</label>
                
                <input 
                  ref="fileInputRef"
                  type="file" 
                  accept=".jpg,.png,.jpeg"
                  class="hidden"
                  @change="handleFileChange"
                />

                <div 
                  @click="triggerFileInput"
                  class="border-2 border-dashed border-gray-300 hover:border-[#4B49AC] bg-gray-50 hover:bg-[#F5F7FF] rounded-lg p-6 text-center cursor-pointer transition-all group"
                >
                  <div v-if="!previewUrl" class="space-y-2">
                    <div class="w-12 h-12 bg-white rounded-lg flex items-center justify-center mx-auto shadow-xs text-[#4B49AC] group-hover:scale-110 transition-transform">
                      <UploadCloud class="w-6 h-6" />
                    </div>
                    <div class="text-sm font-bold text-gray-700">Klik atau Pilih File Bukti Pembayaran</div>
                    <p class="text-xs text-gray-400">Maksimal 5MB. Format: JPG, PNG, atau JPEG</p>
                  </div>

                  <div v-else class="space-y-3">
                    <img :src="previewUrl" class="max-h-36 mx-auto rounded-lg shadow-sm object-contain" />
                    <div class="text-xs font-semibold text-[#4B49AC]">File Terpilih: {{ uploadForm.file?.name }}</div>
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
                    class="flex-1 px-3 sm:px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 font-bold text-xs sm:text-sm transition-all"
                  >
                    Batal Pilih
                  </button>
                  <button 
                    type="submit"
                    :disabled="!uploadForm.file || uploadForm.processing"
                    class="flex-1 px-3 sm:px-4 py-2.5 rounded-lg bg-[#4B49AC] hover:bg-[#3a3888] disabled:bg-gray-300 text-white font-bold text-xs sm:text-sm transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-1.5 sm:gap-2"
                  >
                    <span v-if="uploadForm.processing" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <UploadCloud class="w-4 h-4 shrink-0" /> Unggah Pembayaran
                  </button>
                </div>
              </form>
            </div>
            
            <div v-else-if="pembayaran?.bukti_pembayaran" class="space-y-3">
              <label class="block text-xs font-bold uppercase tracking-wider text-gray-500">File Bukti Pembayaran Terverifikasi</label>
              <div class="p-4 rounded-lg border bg-gray-50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                  <div class="p-2 rounded-lg bg-indigo-100 text-[#4B49AC] shrink-0">
                    <FileText class="w-5 h-5" />
                  </div>
                  <div class="min-w-0">
                    <div class="text-sm font-semibold text-gray-800 truncate">Bukti_Pembayaran_Semester.jpg</div>
                    <div class="text-xs text-gray-500">Diunggah: {{ pembayaran.created_at }}</div>
                  </div>
                </div>
                <a 
                  :href="`/storage/${pembayaran.bukti_pembayaran}`" 
                  target="_blank"
                  class="text-xs font-bold text-[#4B49AC] hover:underline shrink-0 self-start sm:self-auto pl-11 sm:pl-0"
                >
                  Lihat File
                </a>
              </div>
            </div>

          </div>
        </div>

        <!-- Kolom Kanan: Status KRS & Persetujuan -->
        <div class="bg-white p-4 sm:p-6 rounded-lg border border-[#CDD1E1] shadow-sm flex flex-col justify-between space-y-6">
          <div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-4 mb-4">
              <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-lg bg-emerald-50 text-emerald-600 shrink-0">
                  <FileText class="w-5 h-5" />
                </div>
                <div>
                  <h2 class="font-bold text-base sm:text-lg text-[#1F1F1F]">Persetujuan & Verifikasi KRS</h2>
                  <p class="text-xs text-gray-500">Rencana studi untuk semester aktif saat ini</p>
                </div>
              </div>

              <!-- Badge Status KRS -->
              <div class="shrink-0 self-start sm:self-auto">
                <div v-if="!krs" class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-600 border border-gray-200 text-xs font-bold flex items-center gap-1.5">
                  <Clock class="w-3.5 h-3.5" /> Belum Diproses
                </div>
                <div v-else-if="krs.setuju_mahasiswa === 1 && krs.setuju_pa === 0" class="px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 border border-amber-200 text-xs font-bold flex items-center gap-1.5">
                  <Clock class="w-3.5 h-3.5 animate-spin" /> Verifikasi Dosen PA
                </div>
                <div v-else-if="krs.status_krs === 1 && krs.setuju_mahasiswa === 1 && krs.setuju_pa === 1" class="px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold flex items-center gap-1.5">
                  <CheckCircle2 class="w-3.5 h-3.5" /> Selesai & Terverifikasi
                </div>
                <div v-else class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-600 border border-gray-200 text-xs font-bold flex items-center gap-1.5">
                  <Clock class="w-3.5 h-3.5" /> Belum Diproses
                </div>
              </div>
            </div>

            <!-- Tahapan / Alur KRS -->
            <div class="space-y-6 my-6">
              <div class="flex items-start gap-4">
                <div :class="pembayaran?.status_pembayaran === 1 && pembayaran?.keterangan === 'Sudah' ? 'bg-[#4B49AC] text-white' : 'bg-gray-200 text-gray-500'" class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-sm flex-shrink-0 shadow-xs">
                  1
                </div>
                <div>
                  <h3 class="text-sm font-bold text-gray-800">Verifikasi Pembayaran Lunas</h3>
                  <p class="text-xs text-gray-500 mt-0.5">Bagian administrasi menyatakan pembayaran untuk semester ini telah disetujui.</p>
                </div>
              </div>

              <div class="flex items-start gap-4">
                <div :class="krs?.setuju_mahasiswa === 1 ? 'bg-[#4B49AC] text-white' : 'bg-gray-200 text-gray-500'" class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-sm flex-shrink-0 shadow-xs">
                  2
                </div>
                <div>
                  <h3 class="text-sm font-bold text-gray-800">Penandatanganan KRS Mahasiswa</h3>
                  <p class="text-xs text-gray-500 mt-0.5">Mahasiswa memeriksa daftar matakuliah dan memverifikasi pengajuan rencana studi.</p>
                </div>
              </div>

              <div class="flex items-start gap-4">
                <div :class="krs?.setuju_pa === 1 ? 'bg-emerald-600 text-white' : 'bg-gray-200 text-gray-500'" class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-sm flex-shrink-0 shadow-xs">
                  3
                </div>
                <div>
                  <h3 class="text-sm font-bold text-gray-800">Persetujuan Dosen Pembimbing Akademik</h3>
                  <p class="text-xs text-gray-500 mt-0.5">Dosen PA memvalidasi dan menyetujui KRS di sistem portal akademik.</p>
                </div>
              </div>
            </div>

            <!-- Tombol Aksi Berdasarkan Kondisi KRS & Pembayaran -->
            <div class="border-t pt-6 mt-6 space-y-4">
              <!-- Jika Pembayaran Terverifikasi tapi KRS belum diajukan (Bug Fix Legacy) -->
              <div v-if="pembayaran?.status_pembayaran === 1 && pembayaran?.keterangan === 'Sudah'">
                
                <div v-if="!krs">
                  <div class="p-4 rounded-lg bg-indigo-50 border border-indigo-200 text-indigo-900 mb-4 text-xs flex items-center gap-3">
                    <Info class="w-5 h-5 text-indigo-600 flex-shrink-0" />
                    <span>Pembayaran Anda sudah diverifikasi lunas. Silakan klik tombol di bawah untuk membuat dan mengajukan dokumen KRS semester ini.</span>
                  </div>
                  <button 
                    @click="ajukanKRS"
                    :disabled="pengajuanForm.processing"
                    class="w-full py-3.5 rounded-lg bg-[#4B49AC] hover:bg-[#3a3888] text-white font-bold text-sm shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2"
                  >
                    <span v-if="pengajuanForm.processing" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <Send class="w-4 h-4" /> Ajukan Dokumen KRS Sekarang
                  </button>
                </div>

                <!-- Jika KRS sudah ada tapi belum disetujui mahasiswa -->
                <div v-else-if="krs.setuju_mahasiswa === 0">
                  <div class="p-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-900 mb-4 text-xs flex items-center gap-3">
                    <AlertTriangle class="w-5 h-5 text-amber-600 flex-shrink-0" />
                    <span>KRS Anda telah dibuat. Silakan tandatangani persetujuan agar dapat diteruskan ke Dosen Pembimbing Akademik.</span>
                  </div>
                  <button 
                    @click="showConfirmModal = true"
                    class="w-full py-3.5 rounded-lg bg-[#4B49AC] hover:bg-[#3a3888] text-white font-bold text-sm shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2"
                  >
                    <PenTool class="w-4 h-4" /> Tandatangani & Serahkan KRS ke PA
                  </button>
                </div>

                <!-- Jika sudah disetujui mahasiswa tapi belum disetujui PA -->
                <div v-else-if="krs.setuju_mahasiswa === 1 && krs.setuju_pa === 0">
                  <div class="p-4 rounded-lg bg-indigo-50 border border-indigo-200 text-indigo-900 text-xs flex items-center gap-3">
                    <Clock class="w-5 h-5 text-indigo-600 flex-shrink-0 animate-spin" />
                    <span>KRS berhasil diajukan dan sedang menunggu verifikasi serta persetujuan dari Dosen Pembimbing Akademik ({{ mahasiswa.pembimbing_akademik }}).</span>
                  </div>
                </div>

                <!-- Jika sudah selesai (Disetujui Mahasiswa & PA) -->
                <div v-else-if="krs.status_krs === 1 && krs.setuju_mahasiswa === 1 && krs.setuju_pa === 1">
                  <div class="p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs flex items-center gap-3 mb-4">
                    <CheckCircle2 class="w-5 h-5 text-emerald-600 flex-shrink-0" />
                    <span>KRS Anda telah tuntas diverifikasi oleh Dosen PA. Anda dapat mencetak dokumen resmi KRS untuk keperluan akademik.</span>
                  </div>
                  <a 
                    :href="`/v2/mahasiswa/krs_pembayaran/cetak/${krs.id}`" 
                    target="_blank"
                    class="w-full py-3.5 rounded-lg bg-[#4B49AC] hover:bg-[#3a3888] text-white font-bold text-sm shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 text-center inline-block"
                  >
                    <Printer class="w-4 h-4" /> Cetak Dokumen KRS Resmi
                  </a>
                </div>

              </div>
              
              <!-- Jika pembayaran belum lunas -->
              <div v-else class="p-4 rounded-lg bg-gray-100 text-gray-500 text-xs text-center font-medium">
                Pengajuan dan pencetakan KRS akan terbuka secara otomatis setelah pembayaran Anda terverifikasi lunas.
              </div>

            </div>

          </div>
        </div>

      </div>

      <!-- Main Table Card: Daftar Mata Kuliah Paket -->
      <div class="bg-white rounded-lg border border-[#CDD1E1] shadow-sm overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-[#CDD1E1] flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gray-50/50">
          <div class="flex items-center gap-3">
            <div class="p-2 rounded-lg bg-[#4B49AC] text-white shrink-0">
              <BookOpen class="w-5 h-5" />
            </div>
            <div>
              <h2 class="font-bold text-base sm:text-lg text-[#1F1F1F]">Mata Kuliah Paket Semester</h2>
              <p class="text-xs text-gray-500">Daftar matakuliah yang terdaftar untuk semester berjalan</p>
            </div>
          </div>
          <div class="bg-indigo-50 px-4 py-2 rounded-lg border border-indigo-100 text-sm font-bold text-[#4B49AC] self-start sm:self-auto">
            Total Beban: {{ totalSks }} SKS
          </div>
        </div>

        <div class="p-4 sm:p-6">
          <div v-if="matkuls.length === 0" class="py-12 text-center space-y-3">
            <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center mx-auto text-gray-400">
              <FileText class="w-8 h-8" />
            </div>
            <h3 class="font-bold text-gray-700 text-lg">Belum Ada Daftar Mata Kuliah</h3>
            <p class="text-gray-500 text-sm max-w-sm mx-auto">
              Tidak ditemukan daftar matakuliah untuk semester ini. Silakan hubungi bagian administrasi akademik.
            </p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full border-collapse">
              <thead>
                <tr class="border-b border-gray-200 bg-gray-50/75 text-left text-xs font-bold uppercase tracking-wider text-gray-500">
                  <th class="py-3 px-2 sm:py-3.5 sm:px-4 rounded-l-lg w-16">No</th>
                  <th class="py-3 px-2 sm:py-3.5 sm:px-4 w-36">Kode</th>
                  <th class="py-3 px-2 sm:py-3.5 sm:px-4">Nama Mata Kuliah</th>
                  <th class="py-3 px-2 sm:py-3.5 sm:px-4 text-center w-28">Teori (T)</th>
                  <th class="py-3 px-2 sm:py-3.5 sm:px-4 text-center w-28">Praktek (P)</th>
                  <th class="py-3 px-2 sm:py-3.5 sm:px-4 text-center rounded-r-lg w-28">Jumlah SKS</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr 
                  v-for="(item, index) in matkuls" 
                  :key="item.id"
                  class="hover:bg-[#F5F7FF]/50 transition-colors"
                >
                  <td class="py-3 px-2 sm:py-4 sm:px-4 text-sm text-gray-600">{{ index + 1 }}</td>
                  <td class="py-3 px-2 sm:py-4 sm:px-4 text-sm font-bold text-[#4B49AC] font-mono">{{ item.kode }}</td>
                  <td class="py-3 px-2 sm:py-4 sm:px-4 text-sm font-semibold text-[#1F1F1F]">{{ item.nama_matkul }}</td>
                  <td class="py-3 px-2 sm:py-4 sm:px-4 text-sm text-center text-gray-600">{{ item.teori }} SKS</td>
                  <td class="py-3 px-2 sm:py-4 sm:px-4 text-sm text-center text-gray-600">{{ item.praktek }} SKS</td>
                  <td class="py-3 px-2 sm:py-4 sm:px-4 text-sm text-center font-bold text-gray-800 bg-gray-50/50">{{ item.sks }} SKS</td>
                </tr>
              </tbody>
              <tfoot>
                <tr class="bg-gray-50/75 font-bold border-t-2 border-gray-200 text-gray-800">
                  <td colspan="3" class="py-3 px-2 sm:py-4 sm:px-4 text-right">Total Keseluruhan SKS:</td>
                  <td class="py-3 px-2 sm:py-4 sm:px-4 text-center">{{ matkuls.reduce((sum, item) => sum + item.teori, 0) }} SKS</td>
                  <td class="py-3 px-2 sm:py-4 sm:px-4 text-center">{{ matkuls.reduce((sum, item) => sum + item.praktek, 0) }} SKS</td>
                  <td class="py-3 px-2 sm:py-4 sm:px-4 text-center text-[#4B49AC] text-base">{{ totalSks }} SKS</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Dialog Konfirmasi Penandatanganan KRS -->
    <Dialog v-model:open="showConfirmModal">
      <DialogContent class="sm:max-w-md bg-white p-0 overflow-hidden sm:rounded-lg border border-gray-200 shadow-xl [&>button]:text-white [&>button:hover]:bg-white/20 [&>button]:rounded-lg [&>button]:p-1 [&>button]:transition-colors">
        <DialogHeader class="bg-[#4B49AC] text-white p-6">
          <DialogTitle class="text-white text-xl font-bold flex items-center gap-2">
            <ShieldCheck class="w-6 h-6" /> Konfirmasi Penandatanganan KRS
          </DialogTitle>
          <DialogDescription class="text-indigo-100 text-xs">
            Persetujuan Kartu Rencana Studi (KRS) Semester Aktif
          </DialogDescription>
        </DialogHeader>

        <div class="p-6 space-y-4">
          <div class="p-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-900 text-xs leading-relaxed flex items-start gap-3 shadow-xs">
            <AlertTriangle class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" />
            <div>
              <span class="font-bold">Apakah Anda yakin?</span> Dengan menekan tombol setuju di bawah ini, Anda menyatakan bahwa rencana matakuliah yang terdaftar telah benar, dan dokumen ini akan diserahkan kepada Dosen Pembimbing Akademik untuk diverifikasi.
            </div>
          </div>

          <div class="text-xs space-y-2 p-4 rounded-lg bg-gray-50 border border-gray-200 text-gray-700 shadow-xs">
            <div class="flex justify-between border-b pb-1.5"><span class="font-semibold text-gray-500">Nama Lengkap:</span> <span class="font-bold text-gray-900">{{ mahasiswa.nama_lengkap }}</span></div>
            <div class="flex justify-between border-b pb-1.5"><span class="font-semibold text-gray-500">Nomor Induk Mahasiswa (NIM):</span> <span class="font-mono font-bold text-[#4B49AC]">{{ mahasiswa.nim }}</span></div>
            <div class="flex justify-between"><span class="font-semibold text-gray-500">Dosen Pembimbing Akademik:</span> <span class="font-bold text-gray-900">{{ mahasiswa.pembimbing_akademik }}</span></div>
          </div>
        </div>

        <DialogFooter class="p-6 pt-0 gap-3 flex flex-col-reverse sm:flex-row">
          <button 
            type="button" 
            @click="showConfirmModal = false"
            class="w-full sm:w-auto px-6 py-2.5 rounded-lg border border-gray-300 font-bold text-sm text-gray-700 hover:bg-gray-100 transition-all text-center cursor-pointer"
          >
            Batal
          </button>
          <button 
            type="button" 
            @click="tandatanganiKRS"
            :disabled="persetujuanForm.processing"
            class="w-full sm:flex-1 px-6 py-2.5 rounded-lg bg-[#4B49AC] hover:bg-[#3a3888] disabled:bg-gray-300 text-white font-bold text-sm shadow-md transition-all flex items-center justify-center gap-2 text-center cursor-pointer whitespace-nowrap"
          >
            <span v-if="persetujuanForm.processing" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
            <ShieldCheck class="w-4 h-4 flex-shrink-0" /> Ya, Tandatangani & Serahkan
          </button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

  </AdminLayout>
</template>
