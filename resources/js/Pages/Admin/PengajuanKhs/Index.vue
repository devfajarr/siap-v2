<script setup>
import { ref, computed } from 'vue'
import { Head, useForm, router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { 
  Printer, 
  CheckCircle2, 
  XCircle, 
  Clock, 
  AlertCircle, 
  Search,
  ChevronRight,
  FileText,
  Filter
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
  pengajuans: {
    type: Array,
    default: () => []
  }
})

const page = usePage()

// Search & Filter State
const searchQuery = ref('')
const statusFilter = ref('all') // 'all', '0', '1', '2'

// Dialog State
const showRejectModal = ref(false)
const selectedPengajuanId = ref(null)
const rejectReason = ref('')

const rejectForm = useForm({
  status: 2,
  keterangan: ''
})

const statusUpdateForm = useForm({
  status: 1
})

const openRejectModal = (id) => {
  selectedPengajuanId.value = id
  rejectReason.value = ''
  rejectForm.keterangan = ''
  showRejectModal.value = true
}

const submitReject = () => {
  rejectForm.keterangan = rejectReason.value
  rejectForm.put(`/v2/admin/pengajuan-khs/${selectedPengajuanId.value}/status`, {
    onSuccess: () => {
      showRejectModal.value = false
      selectedPengajuanId.value = null
      rejectReason.value = ''
    }
  })
}

const markAsCompleted = (id) => {
  statusUpdateForm.status = 1
  statusUpdateForm.put(`/v2/admin/pengajuan-khs/${id}/status`)
}

// Handler Cetak KHS (Buka tab baru dan refresh halaman setelah beberapa detik untuk memuat status terbaru)
const handleCetak = (mahasiswaId, semesterId) => {
  window.open(`/v2/admin/pengajuan-khs/cetak/${mahasiswaId}/${semesterId}`, '_blank')
  // Berikan sedikit jeda sebelum me-reload data dari server agar update status cetak ter-direfresh
  setTimeout(() => {
    router.reload({ only: ['pengajuans'] })
  }, 1000)
}

// Filter Logic
const filteredPengajuans = computed(() => {
  return props.pengajuans.filter(p => {
    const matchesSearch = 
      p.nama_lengkap.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      p.nim.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      p.kelas.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      p.prodi.toLowerCase().includes(searchQuery.value.toLowerCase())

    const matchesStatus = statusFilter.value === 'all' || p.status.toString() === statusFilter.value

    return matchesSearch && matchesStatus
  })
})

const countByStatus = (status) => {
  return props.pengajuans.filter(p => p.status === status).length
}
</script>

<template>
  <Head title="Pengelolaan Cetak KHS Mahasiswa" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900">Pengajuan Cetak KHS</h1>
          <p class="text-sm text-gray-500 mt-1">
            Daftar permohonan cetak Kartu Hasil Studi (KHS) resmi yang diajukan oleh mahasiswa.
          </p>
        </div>
      </div>

      <!-- Flash Message Banner -->
      <div v-if="page.props.flash?.success || page.props.flash?.error" class="mb-4">
        <div v-if="page.props.flash?.success" class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-3 shadow-xs">
          <CheckCircle2 class="w-5 h-5 text-emerald-600 flex-shrink-0" />
          <span class="text-sm font-medium">{{ page.props.flash.success }}</span>
        </div>
        <div v-if="page.props.flash?.error" class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center gap-3 shadow-xs">
          <AlertCircle class="w-5 h-5 text-rose-600 flex-shrink-0" />
          <span class="text-sm font-medium">{{ page.props.flash.error }}</span>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-[#CDD1E1] shadow-xs flex items-center gap-4">
          <div class="p-3 bg-indigo-50 text-[#4B49AC] rounded-xl">
            <FileText class="w-6 h-6" />
          </div>
          <div>
            <div class="text-xs text-gray-500 font-bold uppercase tracking-wider">Total Pengajuan</div>
            <div class="text-2xl font-black text-gray-900 mt-0.5">{{ pengajuans.length }}</div>
          </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-[#CDD1E1] shadow-xs flex items-center gap-4">
          <div class="p-3 bg-amber-50 text-amber-600 rounded-xl">
            <Clock class="w-6 h-6" />
          </div>
          <div>
            <div class="text-xs text-gray-500 font-bold uppercase tracking-wider">Menunggu (Pending)</div>
            <div class="text-2xl font-black text-amber-600 mt-0.5">{{ countByStatus(0) }}</div>
          </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-[#CDD1E1] shadow-xs flex items-center gap-4">
          <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
            <CheckCircle2 class="w-6 h-6" />
          </div>
          <div>
            <div class="text-xs text-gray-500 font-bold uppercase tracking-wider">Selesai/Dicetak</div>
            <div class="text-2xl font-black text-emerald-600 mt-0.5">{{ countByStatus(1) }}</div>
          </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-[#CDD1E1] shadow-xs flex items-center gap-4">
          <div class="p-3 bg-rose-50 text-rose-600 rounded-xl">
            <XCircle class="w-6 h-6" />
          </div>
          <div>
            <div class="text-xs text-gray-500 font-bold uppercase tracking-wider">Ditolak</div>
            <div class="text-2xl font-black text-rose-600 mt-0.5">{{ countByStatus(2) }}</div>
          </div>
        </div>
      </div>

      <!-- Controls Block -->
      <div class="bg-white p-4 rounded-2xl border border-[#CDD1E1] shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Search bar -->
        <div class="relative flex-1 max-w-md">
          <Search class="absolute left-3.5 top-3 w-4 h-4 text-gray-400" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari NIM, nama mahasiswa, kelas..."
            class="pl-10 pr-4 py-2 w-full rounded-xl border border-gray-200 focus:outline-none focus:border-[#4B49AC] text-sm text-gray-700 bg-gray-50/50"
          />
        </div>

        <!-- Filter Tabs -->
        <div class="flex flex-wrap items-center gap-2">
          <button
            @click="statusFilter = 'all'"
            :class="statusFilter === 'all' ? 'bg-[#4B49AC] text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
            class="px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer"
          >
            Semua
          </button>
          <button
            @click="statusFilter = '0'"
            :class="statusFilter === '0' ? 'bg-amber-500 text-white' : 'bg-amber-50 text-amber-700 border border-amber-100 hover:bg-amber-100'"
            class="px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5"
          >
            Pending ({{ countByStatus(0) }})
          </button>
          <button
            @click="statusFilter = '1'"
            :class="statusFilter === '1' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700 border border-emerald-100 hover:bg-emerald-100'"
            class="px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5"
          >
            Selesai ({{ countByStatus(1) }})
          </button>
          <button
            @click="statusFilter = '2'"
            :class="statusFilter === '2' ? 'bg-rose-600 text-white' : 'bg-rose-50 text-rose-700 border border-rose-100 hover:bg-rose-100'"
            class="px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5"
          >
            Ditolak ({{ countByStatus(2) }})
          </button>
        </div>
      </div>

      <!-- Main Table Card -->
      <div class="bg-white rounded-2xl border border-[#CDD1E1] shadow-xs overflow-hidden">
        <div class="p-6">
          <div v-if="filteredPengajuans.length === 0" class="py-16 text-center space-y-4">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto text-gray-400">
              <FileText class="w-8 h-8" />
            </div>
            <h3 class="font-bold text-gray-700 text-lg">Tidak Ada Data Pengajuan</h3>
            <p class="text-gray-500 text-sm max-w-sm mx-auto">
              Tidak ditemukan data pengajuan cetak KHS yang sesuai dengan kriteria penyaringan Anda saat ini.
            </p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full border-collapse">
              <thead>
                <tr class="border-b border-gray-200 bg-gray-50/75 text-left text-xs font-bold uppercase tracking-wider text-gray-500">
                  <th class="py-3.5 px-4 rounded-l-xl w-12">No</th>
                  <th class="py-3.5 px-4 w-32">NIM</th>
                  <th class="py-3.5 px-4">Nama Mahasiswa</th>
                  <th class="py-3.5 px-4">Prodi & Kelas</th>
                  <th class="py-3.5 px-4 text-center w-28">Semester</th>
                  <th class="py-3.5 px-4 text-center w-40">Diajukan Pada</th>
                  <th class="py-3.5 px-4 text-center w-36">Status</th>
                  <th class="py-3.5 px-4 text-right rounded-r-xl w-72">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr 
                  v-for="(item, index) in filteredPengajuans" 
                  :key="item.id"
                  class="hover:bg-[#F5F7FF]/50 transition-colors"
                >
                  <td class="py-4 px-4 text-sm text-gray-600">{{ index + 1 }}</td>
                  <td class="py-4 px-4 text-sm font-bold text-[#4B49AC] font-mono">{{ item.nim }}</td>
                  <td class="py-4 px-4 text-sm font-semibold text-gray-800">{{ item.nama_lengkap }}</td>
                  <td class="py-4 px-4 text-sm text-gray-600">
                    <div>{{ item.prodi }}</div>
                    <div class="text-xs text-gray-400 font-medium">Kelas: {{ item.kelas }}</div>
                  </td>
                  <td class="py-4 px-4 text-sm text-center font-bold text-gray-700">Smtr {{ item.semester }}</td>
                  <td class="py-4 px-4 text-sm text-center text-gray-500 font-medium">{{ item.created_at }}</td>
                  <td class="py-4 px-4 text-center">
                    <span 
                      v-if="item.status === 0"
                      class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 border border-amber-200 text-amber-700 text-xs font-bold"
                    >
                      <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                      Pending
                    </span>
                    <span 
                      v-else-if="item.status === 1"
                      class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold"
                    >
                      <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                      Telah Dicetak
                    </span>
                    <span 
                      v-else-if="item.status === 2"
                      class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-50 border border-rose-200 text-rose-700 text-xs font-bold"
                      :title="`Alasan: ${item.keterangan || '-'}`"
                    >
                      <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                      Ditolak
                    </span>
                  </td>
                  <td class="py-4 px-4 text-right">
                    <div class="flex items-center justify-end gap-2">
                      <!-- Tombol Cetak (Tab Baru) -->
                      <button 
                        @click="handleCetak(item.mahasiswa_id, item.semester_id)"
                        class="px-3 py-1.5 rounded-lg bg-indigo-50 border border-indigo-100 hover:bg-[#4B49AC] text-[#4B49AC] hover:text-white text-xs font-bold transition-all flex items-center gap-1 cursor-pointer"
                        title="Buka pratinjau cetak KHS"
                      >
                        <Printer class="w-3.5 h-3.5" /> Cetak KHS
                      </button>

                      <!-- Tombol Selesaikan Manual (jika status pending/tolak) -->
                      <button 
                        v-if="item.status !== 1"
                        @click="markAsCompleted(item.id)"
                        :disabled="statusUpdateForm.processing"
                        class="px-3 py-1.5 rounded-lg bg-emerald-50 border border-emerald-200 hover:bg-emerald-600 text-emerald-700 hover:text-white text-xs font-bold transition-all disabled:opacity-50 cursor-pointer"
                        title="Tandai KHS telah dicetak & siap diambil"
                      >
                        Selesai
                      </button>

                      <!-- Tombol Tolak (jika status pending) -->
                      <button 
                        v-if="item.status === 0"
                        @click="openRejectModal(item.id)"
                        class="px-3 py-1.5 rounded-lg bg-rose-50 border border-rose-200 hover:bg-rose-600 text-rose-700 hover:text-white text-xs font-bold transition-all cursor-pointer"
                        title="Tolak pengajuan cetak"
                      >
                        Tolak
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Dialog Input Alasan Penolakan -->
    <Dialog v-model:open="showRejectModal">
      <DialogContent class="sm:max-w-md bg-white p-0 overflow-hidden sm:rounded-2xl border border-gray-200 shadow-xl [&>button]:text-white [&>button:hover]:bg-white/20 [&>button]:rounded-lg [&>button]:p-1">
        <DialogHeader class="bg-[#4B49AC] text-white p-6">
          <DialogTitle class="text-white text-xl font-bold flex items-center gap-2">
            <XCircle class="w-6 h-6" /> Tolak Pengajuan Cetak KHS
          </DialogTitle>
          <DialogDescription class="text-indigo-100 text-xs">
            Formulir konfirmasi penolakan permohonan cetak dokumen KHS mahasiswa.
          </DialogDescription>
        </DialogHeader>

        <div class="p-6 space-y-4">
          <div class="space-y-2">
            <label class="text-sm font-semibold text-gray-700">Alasan Penolakan</label>
            <textarea
              v-model="rejectReason"
              placeholder="Masukkan alasan penolakan agar dapat dibaca oleh mahasiswa di dashboard mereka..."
              rows="4"
              class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:border-[#4B49AC] text-sm text-gray-700 bg-gray-50/50 resize-none"
            ></textarea>
            <div v-if="rejectForm.errors?.keterangan" class="text-xs text-rose-600 font-semibold mt-1">
              {{ rejectForm.errors.keterangan }}
            </div>
          </div>
        </div>

        <DialogFooter class="p-6 pt-0 gap-3 flex flex-col-reverse sm:flex-row">
          <button 
            type="button" 
            @click="showRejectModal = false"
            class="w-full sm:w-auto px-6 py-2.5 rounded-xl border border-gray-300 font-bold text-sm text-gray-700 hover:bg-gray-100 transition-all text-center cursor-pointer"
          >
            Batal
          </button>
          <button 
            type="button" 
            @click="submitReject"
            :disabled="!rejectReason.trim() || rejectForm.processing"
            class="w-full sm:flex-1 px-6 py-2.5 rounded-xl bg-[#4B49AC] hover:bg-[#3a3888] disabled:bg-gray-300 text-white font-bold text-sm shadow-md transition-all flex items-center justify-center gap-2 text-center cursor-pointer"
          >
            <span v-if="rejectForm.processing" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
            Tolak Pengajuan
          </button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AdminLayout>
</template>
