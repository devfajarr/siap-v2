<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { 
  Calendar, 
  ChevronRight, 
  CheckCircle2, 
  AlertCircle, 
  Clock, 
  UploadCloud, 
  FileText, 
  Info,
  ExternalLink,
  Lock,
  ArrowRight
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
  tahun_akademik: {
    type: String,
    default: ''
  },
  uts: {
    type: Object,
    required: true
  },
  uas: {
    type: Object,
    required: true
  }
})

const page = usePage()

// Tab Active State ('uts' atau 'uas')
const activeTab = ref('uts')

// Bulan list
const months = [
  'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
]

// Determine default current month and year
const today = new Date()
const currentMonthName = months[today.getMonth()]
const currentYear = today.getFullYear()

// Forms for UTS & UAS
const uploadForm = useForm({
  jenis_ujian: 'uts',
  bulan_spp: currentMonthName,
  tahun_spp: currentYear,
  bukti_spp: null,
  bukti_pembayaran_ujian: null
})

// File Inputs ref
const fileInputSpp = ref(null)
const fileInputUjian = ref(null)

const previewSppUrl = ref(null)
const previewUjianUrl = ref(null)

// Modal states
const showConfirmModal = ref(false)
const showFlashModal = ref(false)
const flashMessage = ref('')
const flashType = ref('success')

// Handle Tab Switch
const setTab = (tab) => {
  activeTab.value = tab
  uploadForm.jenis_ujian = tab
  clearFiles()
}

// File uploads helpers
const handleSppFileChange = (e) => {
  const file = e.target.files[0]
  if (file) {
    uploadForm.bukti_spp = file
    previewSppUrl.value = URL.createObjectURL(file)
  }
}

const handleUjianFileChange = (e) => {
  const file = e.target.files[0]
  if (file) {
    uploadForm.bukti_pembayaran_ujian = file
    previewUjianUrl.value = URL.createObjectURL(file)
  }
}

const triggerSppInput = () => {
  fileInputSpp.value?.click()
}

const triggerUjianInput = () => {
  fileInputUjian.value?.click()
}

const clearFiles = () => {
  uploadForm.bukti_spp = null
  uploadForm.bukti_pembayaran_ujian = null
  previewSppUrl.value = null
  previewUjianUrl.value = null
  if (fileInputSpp.value) fileInputSpp.value.value = ''
  if (fileInputUjian.value) fileInputUjian.value.value = ''
}

// Submit Request
const submitRequest = () => {
  uploadForm.post('/v2/mahasiswa/jadwal-ujian/ajukan', {
    onSuccess: () => {
      clearFiles()
      showConfirmModal.value = false
    },
    onError: () => {
      showConfirmModal.value = false
    }
  })
}

// Watch session flash messages
watch(() => page.props.flash, (newFlash) => {
  if (newFlash?.success) {
    flashMessage.value = newFlash.success
    flashType.value = 'success'
    showFlashModal.value = true
  } else if (newFlash?.error) {
    flashMessage.value = newFlash.error
    flashType.value = 'error'
    showFlashModal.value = true
  }
}, { deep: true })

onMounted(() => {
  if (page.props.flash?.success) {
    flashMessage.value = page.props.flash.success
    flashType.value = 'success'
    showFlashModal.value = true
  } else if (page.props.flash?.error) {
    flashMessage.value = page.props.flash.error
    flashType.value = 'error'
    showFlashModal.value = true
  }
})

// Current data depending on Tab
const currentData = computed(() => {
  return activeTab.value === 'uts' ? props.uts : props.uas
})

// Format Date Helper
const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  const d = new Date(dateStr)
  return d.toLocaleDateString('id-ID', { 
    weekday: 'long', 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  })
}
</script>

<template>
  <Head title="Jadwal & Kartu Ujian" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Header Title & Breadcrumb -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
            <Link href="/v2/mahasiswa/dashboard" class="hover:text-[#4B49AC] transition-colors">Dashboard</Link>
            <ChevronRight class="w-4 h-4" />
            <span class="text-gray-700 font-semibold">Jadwal & Kartu Ujian</span>
          </div>
          <h1 class="text-xl sm:text-2xl font-bold text-[#1F1F1F]">Jadwal & Kartu Ujian</h1>
          <p class="text-sm text-gray-500">
            Lihat jadwal ujian dan ajukan pencetakan kartu ujian fisik untuk UTS dan UAS
          </p>
        </div>

        <div class="flex items-center bg-gray-100 p-1 rounded-xl">
          <button 
            @click="setTab('uts')"
            class="px-4 py-2 text-sm font-bold rounded-lg transition-all"
            :class="activeTab === 'uts' ? 'bg-[#4B49AC] text-white shadow-xs' : 'text-gray-600 hover:text-gray-900'"
          >
            Ujian Tengah Semester (UTS)
          </button>
          <button 
            @click="setTab('uas')"
            class="px-4 py-2 text-sm font-bold rounded-lg transition-all"
            :class="activeTab === 'uas' ? 'bg-[#4B49AC] text-white shadow-xs' : 'text-gray-600 hover:text-gray-900'"
          >
            Ujian Akhir Semester (UAS)
          </button>
        </div>
      </div>

      <!-- Student Banner -->
      <div class="bg-gradient-to-br from-[#4B49AC] to-[#5957c2] p-6 rounded-2xl text-white shadow-md flex items-center gap-6 relative overflow-hidden">
        <div class="absolute right-0 top-0 opacity-10 translate-x-4 -translate-y-4 pointer-events-none">
          <Calendar class="w-48 h-48" />
        </div>
        <div class="space-y-1 relative z-10 flex-1">
          <div class="text-xs uppercase font-semibold text-indigo-200 tracking-wider">Identitas Mahasiswa & Periode Ujian</div>
          <h2 class="text-xl font-bold">{{ mahasiswa.nama_lengkap }}</h2>
          <div class="flex flex-wrap gap-x-6 gap-y-1 text-sm text-indigo-100 pt-2 border-t border-white/20">
            <div><span class="font-semibold text-white">NIM:</span> {{ mahasiswa.nim }}</div>
            <div><span class="font-semibold text-white">Program Studi:</span> {{ mahasiswa.prodi }}</div>
            <div><span class="font-semibold text-white">Semester:</span> {{ mahasiswa.semester }}</div>
            <div><span class="font-semibold text-white">Tahun Akademik:</span> {{ tahun_akademik }}</div>
          </div>
        </div>
      </div>

      <!-- Main Layout Checklist & Form -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Checklist & Request Form (Left Column) -->
        <div class="lg:col-span-2 space-y-6">
          <!-- 1. Questionnaire Completion Section -->
          <div class="bg-white p-6 rounded-2xl border border-[#CDD1E1] shadow-sm space-y-4">
            <div class="flex items-center gap-3">
              <div 
                class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shadow-xs"
                :class="currentData.questionnaire_completed ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'"
              >
                1
              </div>
              <h2 class="text-lg font-bold text-gray-900">
                Penyelesaian Kuisioner {{ activeTab === 'uts' ? 'Pelayanan' : 'Kinerja Pengajar' }}
              </h2>
            </div>
            
            <p class="text-sm text-gray-500">
              Sebelum melakukan pengajuan kartu ujian, Anda wajib menyelesaikan kuesioner yang telah disediakan.
            </p>

            <!-- Complete Alert -->
            <div 
              v-if="currentData.questionnaire_completed"
              class="p-4 rounded-xl border border-emerald-200 bg-emerald-50/75 flex items-center gap-3"
            >
              <CheckCircle2 class="w-5 h-5 text-emerald-600 flex-shrink-0" />
              <div class="text-sm font-bold text-emerald-950">
                Semua kuesioner wajib telah diselesaikan.
              </div>
            </div>

            <!-- Incomplete Alert -->
            <div v-else class="space-y-3">
              <div class="p-4 rounded-xl border border-amber-200 bg-amber-50/75 flex items-start gap-3">
                <AlertCircle class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" />
                <div class="text-sm text-amber-900 leading-relaxed">
                  <span class="font-bold">Kuesioner belum lengkap.</span> Silakan isi kuisioner berikut untuk membuka form pengajuan kartu:
                </div>
              </div>

              <!-- List UTS Questionnaires (Pelayanan) -->
              <div v-if="activeTab === 'uts' && currentData.pelayanan_list.length > 0" class="divide-y divide-gray-100 border rounded-xl overflow-hidden bg-white">
                <div 
                  v-for="q in currentData.pelayanan_list" 
                  :key="q.id"
                  class="flex items-center justify-between p-3.5 hover:bg-gray-50 transition-colors"
                >
                  <div class="flex items-center gap-2">
                    <CheckCircle2 v-if="q.is_submitted" class="w-4 h-4 text-emerald-500" />
                    <Clock v-else class="w-4 h-4 text-amber-500" />
                    <span class="text-sm font-medium text-gray-700">{{ q.title }}</span>
                  </div>
                  <Link 
                    v-if="!q.is_submitted"
                    :href="`/v2/isi-kuisioner/${q.id}`"
                    class="inline-flex items-center gap-1 text-xs font-bold text-[#4B49AC] hover:underline"
                  >
                    Isi Kuesioner <ExternalLink class="w-3 h-3" />
                  </Link>
                  <span v-else class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-lg border border-emerald-200">Selesai</span>
                </div>
              </div>

              <!-- List UAS Questionnaires (Kinerja Pengajar) -->
              <div v-if="activeTab === 'uas' && currentData.kinerja_list.length > 0" class="divide-y divide-gray-100 border rounded-xl overflow-hidden bg-white max-h-60 overflow-y-auto">
                <div 
                  v-for="k in currentData.kinerja_list" 
                  :key="k.jadwal_id"
                  class="flex items-center justify-between p-3.5 hover:bg-gray-50 transition-colors"
                >
                  <div class="min-w-0 pr-4">
                    <div class="flex items-center gap-2">
                      <CheckCircle2 v-if="k.is_submitted" class="w-4 h-4 text-emerald-500 shrink-0" />
                      <Clock v-else class="w-4 h-4 text-amber-500 shrink-0" />
                      <span class="text-sm font-bold text-gray-800 truncate">{{ k.nama_dosen }}</span>
                    </div>
                    <span class="text-xs text-gray-500 ml-6 block">{{ k.nama_matkul }}</span>
                  </div>
                  <Link 
                    v-if="!k.is_submitted"
                    :href="`/v2/isi-kuisioner/${k.questionnaire_id}?dosen_id=${k.dosen_id}&jadwal_id=${k.jadwal_id}`"
                    class="inline-flex items-center gap-1 text-xs font-bold text-[#4B49AC] hover:underline shrink-0"
                  >
                    Evaluasi <ExternalLink class="w-3 h-3" />
                  </Link>
                  <span v-else class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-lg border border-emerald-200 shrink-0">Selesai</span>
                </div>
              </div>
            </div>
          </div>

          <!-- 2. Payment Proof Verification & Upload Form Section -->
          <div class="bg-white p-6 rounded-2xl border border-[#CDD1E1] shadow-sm space-y-4">
            <div class="flex items-center gap-3">
              <div 
                class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shadow-xs"
                :class="currentData.pengajuan && currentData.pengajuan.status === 1 ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600'"
              >
                2
              </div>
              <h2 class="text-lg font-bold text-gray-900">Upload Bukti Pembayaran & Status</h2>
            </div>

            <!-- Lock Message if Questionnaire Incomplete -->
            <div 
              v-if="!currentData.questionnaire_completed"
              class="p-6 text-center border-2 border-dashed border-gray-200 bg-gray-50 rounded-xl space-y-2"
            >
              <Lock class="w-8 h-8 text-gray-400 mx-auto" />
              <div class="text-sm font-bold text-gray-700">Form Terkunci</div>
              <p class="text-xs text-gray-400 max-w-xs mx-auto">
                Silakan selesaikan seluruh kuesioner wajib pada bagian 1 di atas terlebih dahulu untuk membuka formulir pengajuan pembayaran.
              </p>
            </div>

            <!-- Form & Status Tracking when Questionnaires completed -->
            <div v-else class="space-y-4">
              <!-- STATUS ALERTS -->
              <div v-if="currentData.pengajuan">
                <!-- Status 0: Pending -->
                <div 
                  v-if="currentData.pengajuan.status === 0"
                  class="p-4 rounded-xl border border-amber-200 bg-amber-50/75 flex items-start gap-3"
                >
                  <Clock class="w-5 h-5 text-amber-500 mt-0.5 animate-pulse shrink-0" />
                  <div>
                    <h4 class="font-bold text-sm text-amber-950">Menunggu Verifikasi Administrasi</h4>
                    <p class="text-xs text-gray-500 mt-1">
                      Dokumen bukti pembayaran SPP bulan <b>{{ currentData.pengajuan.bulan_spp }} {{ currentData.pengajuan.tahun_spp }}</b> dan bukti pembayaran ujian Anda sedang diperiksa oleh bagian administrasi akademik. Kartu ujian fisik akan dicetak oleh admin setelah berkas Anda disetujui.
                    </p>
                  </div>
                </div>

                <!-- Status 1: Selesai / Siap Diambil -->
                <div 
                  v-else-if="currentData.pengajuan.status === 1"
                  class="p-5 rounded-xl border border-emerald-200 bg-emerald-50 flex items-start gap-4"
                >
                  <CheckCircle2 class="w-6 h-6 text-emerald-600 shrink-0 mt-0.5" />
                  <div>
                    <h4 class="font-bold text-base text-emerald-950">Kartu Ujian Siap Diambil!</h4>
                    <p class="text-sm text-gray-700 mt-1 leading-relaxed">
                      Pengajuan pembayaran Anda sudah <b>Disetujui dan Diverifikasi</b> oleh Akademik. Kartu ujian fisik Anda telah selesai dicetak. 
                    </p>
                    <div class="mt-3 flex items-center gap-2 text-xs font-bold text-emerald-800 bg-emerald-100/50 border border-emerald-300/50 px-3 py-1.5 rounded-lg w-fit">
                      <Info class="w-3.5 h-3.5" /> Silakan datangi Loket Bagian Akademik Kampus untuk mengambil kartu ujian fisik Anda.
                    </div>
                  </div>
                </div>

                <!-- Status 2: Ditolak -->
                <div 
                  v-else-if="currentData.pengajuan.status === 2"
                  class="p-4 rounded-xl border border-rose-200 bg-rose-50/75 flex items-start gap-3"
                >
                  <AlertCircle class="w-5 h-5 text-rose-500 mt-0.5 shrink-0" />
                  <div>
                    <h4 class="font-bold text-sm text-rose-950">Pengajuan Pembayaran Ditolak</h4>
                    <p class="text-xs text-gray-600 mt-1 leading-relaxed">
                      Alasan Penolakan: <b class="text-rose-700">{{ currentData.pengajuan.keterangan || '-' }}</b>.
                      Silakan periksa kembali berkas bukti transfer Anda dan lakukan unggah ulang dokumen yang valid di bawah ini.
                    </p>
                  </div>
                </div>
              </div>

              <!-- UPLOAD FORM (Tampil jika belum mengajukan OR ditolak) -->
              <div v-if="!currentData.pengajuan || currentData.pengajuan.status === 2" class="space-y-4 pt-2 border-t border-gray-100">
                <h3 class="font-bold text-[#1F1F1F] text-base">Unggah Bukti Pembayaran</h3>
                <p class="text-xs text-gray-500">Silakan unggah bukti SPP bulan berjalan beserta bukti pembayaran ujian (UTS/UAS).</p>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <!-- SPP Month Dropdown -->
                  <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">SPP Bulan Berjalan</label>
                    <select 
                      v-model="uploadForm.bulan_spp"
                      class="w-full text-sm rounded-lg border border-[#CDD1E1] p-2 focus:ring-1 focus:ring-[#4B49AC] outline-none"
                    >
                      <option v-for="m in months" :key="m" :value="m">{{ m }}</option>
                    </select>
                  </div>

                  <!-- SPP Year Input -->
                  <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Tahun SPP</label>
                    <input 
                      type="number"
                      v-model="uploadForm.tahun_spp"
                      class="w-full text-sm rounded-lg border border-[#CDD1E1] p-2 focus:ring-1 focus:ring-[#4B49AC] outline-none"
                    />
                  </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-2">
                  <!-- SPP Upload Area -->
                  <div class="space-y-2">
                    <label class="block text-xs font-bold text-gray-700">Bukti Pembayaran SPP</label>
                    <input 
                      ref="fileInputSpp"
                      type="file" 
                      accept=".jpg,.png,.jpeg"
                      class="hidden"
                      @change="handleSppFileChange"
                    />
                    <div 
                      @click="triggerSppInput"
                      class="border border-dashed border-gray-300 hover:border-[#4B49AC] bg-gray-50 hover:bg-[#F5F7FF] rounded-xl p-4 text-center cursor-pointer transition-all min-h-32 flex flex-col items-center justify-center gap-2 group"
                    >
                      <div v-if="!previewSppUrl" class="space-y-1">
                        <UploadCloud class="w-6 h-6 text-gray-400 mx-auto group-hover:scale-110 transition-transform" />
                        <div class="text-xs font-bold text-gray-600">Pilih Bukti SPP</div>
                        <p class="text-[10px] text-gray-400">Max 5MB (JPG/PNG)</p>
                      </div>
                      <div v-else class="space-y-2 w-full">
                        <img :src="previewSppUrl" class="max-h-24 mx-auto rounded object-contain shadow-xs" />
                        <div class="text-[10px] text-gray-500 truncate">{{ uploadForm.bukti_spp?.name }}</div>
                      </div>
                    </div>
                    <div v-if="uploadForm.errors?.bukti_spp" class="text-xs text-rose-600 font-medium mt-1">
                      {{ uploadForm.errors.bukti_spp }}
                    </div>
                  </div>

                  <!-- Ujian Upload Area -->
                  <div class="space-y-2">
                    <label class="block text-xs font-bold text-gray-700">Bukti Pembayaran Ujian</label>
                    <input 
                      ref="fileInputUjian"
                      type="file" 
                      accept=".jpg,.png,.jpeg"
                      class="hidden"
                      @change="handleUjianFileChange"
                    />
                    <div 
                      @click="triggerUjianInput"
                      class="border border-dashed border-gray-300 hover:border-[#4B49AC] bg-gray-50 hover:bg-[#F5F7FF] rounded-xl p-4 text-center cursor-pointer transition-all min-h-32 flex flex-col items-center justify-center gap-2 group"
                    >
                      <div v-if="!previewUjianUrl" class="space-y-1">
                        <UploadCloud class="w-6 h-6 text-gray-400 mx-auto group-hover:scale-110 transition-transform" />
                        <div class="text-xs font-bold text-gray-600">Pilih Bukti Ujian</div>
                        <p class="text-[10px] text-gray-400">Max 5MB (JPG/PNG)</p>
                      </div>
                      <div v-else class="space-y-2 w-full">
                        <img :src="previewUjianUrl" class="max-h-24 mx-auto rounded object-contain shadow-xs" />
                        <div class="text-[10px] text-gray-500 truncate">{{ uploadForm.bukti_pembayaran_ujian?.name }}</div>
                      </div>
                    </div>
                    <div v-if="uploadForm.errors?.bukti_pembayaran_ujian" class="text-xs text-rose-600 font-medium mt-1">
                      {{ uploadForm.errors.bukti_pembayaran_ujian }}
                    </div>
                  </div>
                </div>

                <div class="flex gap-3 pt-3">
                  <button 
                    v-if="previewSppUrl || previewUjianUrl"
                    type="button"
                    @click="clearFiles"
                    class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 font-bold text-xs transition-all"
                  >
                    Bersihkan
                  </button>
                  <button 
                    type="button"
                    @click="showConfirmModal = true"
                    :disabled="!uploadForm.bukti_spp || !uploadForm.bukti_pembayaran_ujian || uploadForm.processing"
                    class="flex-1 px-5 py-2.5 rounded-lg bg-[#4B49AC] hover:bg-[#3a3888] disabled:bg-gray-300 text-white font-bold text-xs transition-all shadow-md flex items-center justify-center gap-2 cursor-pointer"
                  >
                    <span v-if="uploadForm.processing" class="inline-block w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <UploadCloud class="w-4 h-4 shrink-0" /> Kirim Berkas Pengajuan
                  </button>
                </div>
              </div>

              <!-- VIEW UPLOADED FILES (Tampil jika sudah diajukan & status pending/selesai) -->
              <div v-if="currentData.pengajuan && currentData.pengajuan.status !== 2" class="p-4 rounded-xl border bg-gray-50 space-y-3">
                <h4 class="text-xs font-bold text-gray-700">Berkas yang Telah Diunggah:</h4>
                <div class="flex flex-col sm:flex-row gap-4">
                  <div class="flex items-center gap-2 text-xs flex-1 bg-white p-2.5 rounded-lg border">
                    <FileText class="w-4 h-4 text-gray-400 shrink-0" />
                    <span class="text-gray-700 font-medium truncate flex-1">Bukti SPP - {{ currentData.pengajuan.bulan_spp }}.jpg</span>
                    <a :href="`/storage/${currentData.pengajuan.bukti_spp}`" target="_blank" class="text-indigo-600 font-bold hover:underline shrink-0">Buka</a>
                  </div>
                  <div class="flex items-center gap-2 text-xs flex-1 bg-white p-2.5 rounded-lg border">
                    <FileText class="w-4 h-4 text-gray-400 shrink-0" />
                    <span class="text-gray-700 font-medium truncate flex-1">Bukti Pembayaran Ujian.jpg</span>
                    <a :href="`/storage/${currentData.pengajuan.bukti_pembayaran_ujian}`" target="_blank" class="text-indigo-600 font-bold hover:underline shrink-0">Buka</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar Info Bantuan (Right Column) -->
        <div class="bg-white p-6 rounded-2xl border border-[#CDD1E1] shadow-sm space-y-4 self-start">
          <h3 class="font-bold text-[#1F1F1F] text-base flex items-center gap-1.5">
            <Info class="w-5 h-5 text-[#4B49AC]" /> Alur Pengambilan Kartu
          </h3>
          <ol class="text-xs text-gray-500 space-y-3 leading-relaxed list-decimal pl-4">
            <li>Pastikan seluruh kuisioner wajib UTS/UAS di bagian 1 sudah selesai diisi.</li>
            <li>Unggah berkas bukti transfer SPP bulan berjalan dan bukti pembayaran ujian di bagian 2.</li>
            <li>Status permohonan akan ditinjau secara manual oleh loket administrasi keuangan & akademik.</li>
            <li>Jika disetujui, status akan berubah menjadi <b>Selesai (Siap Diambil)</b>.</li>
            <li>Bawa kartu identitas mahasiswa dan bukti online ke Loket Akademik untuk serah terima kartu fisik asli.</li>
          </ol>
        </div>
      </div>

      <!-- Exam Schedule Table Section -->
      <div class="bg-white rounded-2xl border border-[#CDD1E1] shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-[#CDD1E1] flex items-center gap-3 bg-gray-50/50">
          <div class="p-2 rounded-lg bg-[#4B49AC] text-white">
            <Calendar class="w-5 h-5" />
          </div>
          <div>
            <h2 class="font-bold text-base sm:text-lg text-[#1F1F1F]">Jadwal Pelaksanaan Ujian ({{ activeTab.toUpperCase() }})</h2>
            <p class="text-xs text-gray-400">Jadwal resmi pelaksanaan ujian sesuai mata kuliah semester berjalan</p>
          </div>
        </div>

        <div class="p-6">
          <div v-if="currentData.schedules.length === 0" class="py-12 text-center space-y-3">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto text-gray-400">
              <Calendar class="w-8 h-8" />
            </div>
            <h3 class="font-bold text-gray-700 text-lg">Belum Ada Jadwal Ujian</h3>
            <p class="text-gray-500 text-sm max-w-sm mx-auto">
              Jadwal pelaksanaan ujian untuk periode ini belum diterbitkan oleh bagian akademik. Silakan cek kembali secara berkala.
            </p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full border-collapse">
              <thead>
                <tr class="border-b border-gray-200 bg-gray-50/75 text-left text-xs font-bold uppercase tracking-wider text-gray-500">
                  <th class="py-3.5 px-4 rounded-l-xl">No</th>
                  <th class="py-3.5 px-4">Hari, Tanggal</th>
                  <th class="py-3.5 px-4 text-center">Waktu</th>
                  <th class="py-3.5 px-4">Mata Kuliah</th>
                  <th class="py-3.5 px-4 text-center">Ruangan</th>
                  <th class="py-3.5 px-4 rounded-r-xl">Pengawas</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr 
                  v-for="(item, index) in currentData.schedules" 
                  :key="item.id"
                  class="hover:bg-[#F5F7FF]/50 transition-colors"
                >
                  <td class="py-4 px-4 text-sm text-gray-600">{{ index + 1 }}</td>
                  <td class="py-4 px-4 text-sm font-semibold text-gray-800">{{ formatDate(item.tanggal) }}</td>
                  <td class="py-4 px-4 text-sm text-center font-bold text-gray-700">{{ item.waktu }} WIB</td>
                  <td class="py-4 px-4">
                    <div class="text-sm font-bold text-[#4B49AC] font-mono">{{ item.kode_matkul }}</div>
                    <div class="text-sm font-medium text-gray-800 max-w-sm">{{ item.matkul }}</div>
                  </td>
                  <td class="py-4 px-4 text-sm text-center font-bold text-gray-700">{{ item.ruangan }}</td>
                  <td class="py-4 px-4 text-sm text-gray-700 font-medium">{{ item.pengawas }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Dialog Konfirmasi Kirim Pengajuan -->
    <Dialog v-model:open="showConfirmModal">
      <DialogContent class="sm:max-w-md bg-white p-0 overflow-hidden sm:rounded-2xl border border-gray-200 shadow-xl [&>button]:text-white [&>button:hover]:bg-white/20 [&>button]:rounded-lg [&>button]:p-1">
        <DialogHeader class="bg-[#4B49AC] text-white p-6">
          <DialogTitle class="text-white text-xl font-bold flex items-center gap-2">
            <UploadCloud class="w-6 h-6" /> Konfirmasi Kirim Berkas
          </DialogTitle>
          <DialogDescription class="text-indigo-100 text-xs">
            Kirim berkas bukti pembayaran untuk kartu ujian {{ activeTab.toUpperCase() }}
          </DialogDescription>
        </DialogHeader>

        <div class="p-6 space-y-4">
          <div class="p-4 rounded-xl bg-gray-50 border text-gray-700 text-xs leading-relaxed flex items-start gap-3">
            <Info class="w-5 h-5 text-[#4B49AC] flex-shrink-0 mt-0.5" />
            <div>
              Dengan menekan tombol kirim, Anda menyatakan bahwa bukti pembayaran SPP bulan <b>{{ uploadForm.bulan_spp }} {{ uploadForm.tahun_spp }}</b> dan bukti pembayaran ujian yang Anda unggah adalah asli dan sah.
            </div>
          </div>
        </div>

        <DialogFooter class="p-6 pt-0 gap-3 flex flex-col-reverse sm:flex-row">
          <button 
            type="button" 
            @click="showConfirmModal = false"
            class="w-full sm:w-auto px-6 py-2.5 rounded-xl border border-gray-300 font-bold text-sm text-gray-700 hover:bg-gray-100 transition-all text-center cursor-pointer"
          >
            Batal
          </button>
          <button 
            type="button" 
            @click="submitRequest"
            :disabled="uploadForm.processing"
            class="w-full sm:flex-1 px-6 py-2.5 rounded-xl bg-[#4B49AC] hover:bg-[#3a3888] disabled:bg-gray-300 text-white font-bold text-sm shadow-md transition-all flex items-center justify-center gap-2 text-center cursor-pointer whitespace-nowrap"
          >
            <span v-if="uploadForm.processing" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
            <ArrowRight class="w-4 h-4 flex-shrink-0" /> Kirim Pengajuan
          </button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Dialog Notifikasi Status Flash (Sukses/Gagal) -->
    <Dialog v-model:open="showFlashModal">
      <DialogContent class="sm:max-w-md bg-white p-0 overflow-hidden sm:rounded-2xl border border-gray-200 shadow-xl [&>button]:text-white [&>button:hover]:bg-white/20 [&>button]:rounded-lg [&>button]:p-1">
        <div class="p-6 text-center">
          <div :class="flashType === 'success' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'" class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 shadow-xs">
            <CheckCircle2 v-if="flashType === 'success'" class="w-8 h-8" />
            <AlertCircle v-else class="w-8 h-8" />
          </div>
          <DialogTitle class="text-lg font-bold text-gray-900 mb-2">
            {{ flashType === 'success' ? 'Berhasil' : 'Gagal' }}
          </DialogTitle>
          <DialogDescription class="text-sm text-gray-500 mb-6">
            {{ flashMessage }}
          </DialogDescription>
          <button 
            type="button" 
            @click="showFlashModal = false"
            class="w-full py-2.5 rounded-xl bg-[#4B49AC] hover:bg-[#3a3888] text-white font-bold text-sm shadow-md transition-all text-center cursor-pointer"
          >
            Tutup
          </button>
        </div>
      </DialogContent>
    </Dialog>
  </AdminLayout>
</template>
