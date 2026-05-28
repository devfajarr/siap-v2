<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { Head, useForm, router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { 
  CheckCircle2, 
  XCircle, 
  Clock, 
  AlertCircle, 
  Search,
  ChevronRight,
  FileText,
  Eye,
  Calendar,
  AlertTriangle
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
    type: Object,
    required: true
  },
  filters: {
    type: Object,
    default: () => ({})
  }
})

const page = usePage()

// Search & Filter State
const searchQuery = ref(props.filters.search || '')
const statusFilter = ref(props.filters.status || 'all') // 'all', '0', '1', '2'

// Dialog State
const showDetailModal = ref(false)
const selectedRequest = ref(null)

const showRejectModal = ref(false)
const rejectReason = ref('')

const showConfirmCompleteModal = ref(false)

const showFlashModal = ref(false)
const flashMessage = ref('')
const flashType = ref('success')

// Forms
const statusForm = useForm({
  status: 1,
  keterangan: ''
})

const openDetailModal = (req) => {
  selectedRequest.value = req
  showDetailModal.value = true
}

const openRejectModal = () => {
  rejectReason.value = ''
  showRejectModal.value = false
  // We keep details modal open but open a reject confirmation
}

const submitStatus = (statusValue, reason = '') => {
  statusForm.status = statusValue
  statusForm.keterangan = reason
  statusForm.put(`/v2/admin/pengajuan-kartu-ujian/${selectedRequest.value.id}/status`, {
    onSuccess: () => {
      showDetailModal.value = false
      showRejectModal.value = false
      showConfirmCompleteModal.value = false
      selectedRequest.value = null
    }
  })
}

// Watchers for filtering (Server-side sorting/filtering via router.get)
watch([searchQuery, statusFilter], () => {
  router.get('/v2/admin/pengajuan-kartu-ujian', {
    search: searchQuery.value,
    status: statusFilter.value === 'all' ? '' : statusFilter.value
  }, {
    preserveState: true,
    replace: true,
    only: ['pengajuans']
  })
})

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
</script>

<template>
  <Head title="Pengelolaan Kartu Ujian Mahasiswa" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900">Pengajuan Kartu Ujian</h1>
          <p class="text-sm text-gray-500 mt-1">
            Daftar permohonan cetak Kartu Ujian (UTS/UAS) yang diajukan oleh mahasiswa beserta bukti administrasi keuangan.
          </p>
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
            placeholder="Cari NIM, nama mahasiswa, prodi..."
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
            Pending
          </button>
          <button
            @click="statusFilter = '1'"
            :class="statusFilter === '1' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700 border border-emerald-100 hover:bg-emerald-100'"
            class="px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5"
          >
            Selesai
          </button>
          <button
            @click="statusFilter = '2'"
            :class="statusFilter === '2' ? 'bg-rose-600 text-white' : 'bg-rose-50 text-rose-700 border border-rose-100 hover:bg-rose-100'"
            class="px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5"
          >
            Ditolak
          </button>
        </div>
      </div>

      <!-- Main Table Card -->
      <div class="bg-white rounded-2xl border border-[#CDD1E1] shadow-xs overflow-hidden">
        <div class="p-6">
          <div v-if="pengajuans.data.length === 0" class="py-16 text-center space-y-4">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto text-gray-400">
              <FileText class="w-8 h-8" />
            </div>
            <h3 class="font-bold text-gray-700 text-lg">Tidak Ada Data Pengajuan</h3>
            <p class="text-gray-500 text-sm max-w-sm mx-auto">
              Tidak ditemukan data pengajuan kartu ujian yang sesuai dengan kriteria pencarian atau penyaringan Anda.
            </p>
          </div>

          <div v-else class="space-y-4">
            <div class="overflow-x-auto">
              <table class="w-full border-collapse">
                <thead>
                  <tr class="border-b border-gray-200 bg-gray-50/75 text-left text-xs font-bold uppercase tracking-wider text-gray-500">
                    <th class="py-3.5 px-4 rounded-l-xl w-12">No</th>
                    <th class="py-3.5 px-4 w-32">NIM</th>
                    <th class="py-3.5 px-4">Nama Mahasiswa</th>
                    <th class="py-3.5 px-4">Prodi & Kelas</th>
                    <th class="py-3.5 px-4 text-center">Jenis Ujian</th>
                    <th class="py-3.5 px-4 text-center">Semester</th>
                    <th class="py-3.5 px-4 text-center">Periode SPP</th>
                    <th class="py-3.5 px-4 text-center">Diajukan Pada</th>
                    <th class="py-3.5 px-4 text-center w-28">Status</th>
                    <th class="py-3.5 px-4 text-right rounded-r-xl w-32">Aksi</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <tr 
                    v-for="(item, index) in pengajuans.data" 
                    :key="item.id"
                    class="hover:bg-[#F5F7FF]/50 transition-colors"
                  >
                    <td class="py-4 px-4 text-sm text-gray-600">
                      {{ (pengajuans.current_page - 1) * pengajuans.per_page + index + 1 }}
                    </td>
                    <td class="py-4 px-4 text-sm font-bold text-[#4B49AC] font-mono">{{ item.nim }}</td>
                    <td class="py-4 px-4 text-sm font-semibold text-gray-800">{{ item.nama_lengkap }}</td>
                    <td class="py-4 px-4 text-sm text-gray-600">
                      <div>{{ item.prodi }}</div>
                      <div class="text-xs text-gray-400 font-medium">Kelas: {{ item.kelas }}</div>
                    </td>
                    <td class="py-4 px-4 text-center">
                      <span 
                        class="inline-block px-2.5 py-1 rounded-lg text-xs font-bold border"
                        :class="item.jenis_ujian === 'UTS' ? 'bg-purple-50 text-purple-700 border-purple-200' : 'bg-blue-50 text-blue-700 border-blue-200'"
                      >
                        {{ item.jenis_ujian }}
                      </span>
                    </td>
                    <td class="py-4 px-4 text-sm text-center font-bold text-gray-700">{{ item.semester }}</td>
                    <td class="py-4 px-4 text-sm text-center font-semibold text-gray-700">
                      {{ item.bulan_spp }} {{ item.tahun_spp }}
                    </td>
                    <td class="py-4 px-4 text-sm text-center text-gray-500 font-medium">{{ item.created_at }}</td>
                    <td class="py-4 px-4 text-center">
                      <div class="flex justify-center">
                        <span 
                          v-if="item.status === 0"
                          class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 border border-amber-200 text-amber-700 text-xs font-bold whitespace-nowrap"
                        >
                          <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                          Pending
                        </span>
                        <span 
                          v-else-if="item.status === 1"
                          class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold whitespace-nowrap"
                        >
                          <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                          Selesai
                        </span>
                        <span 
                          v-else-if="item.status === 2"
                          class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-50 border border-rose-200 text-rose-700 text-xs font-bold whitespace-nowrap"
                          :title="`Alasan: ${item.keterangan || '-'}`"
                        >
                          <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                          Ditolak
                        </span>
                      </div>
                    </td>
                    <td class="py-4 px-4 text-right">
                      <button 
                        @click="openDetailModal(item)"
                        class="px-3 py-1.5 rounded-lg bg-indigo-50 border border-indigo-100 hover:bg-[#4B49AC] text-[#4B49AC] hover:text-white text-xs font-bold transition-all flex items-center gap-1 cursor-pointer"
                        title="Verifikasi Bukti Pembayaran"
                      >
                        <Eye class="w-3.5 h-3.5" /> Verifikasi
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div v-if="pengajuans.links.length > 3" class="flex justify-between items-center pt-4 border-t border-gray-100">
              <div class="text-xs text-gray-500">
                Menampilkan {{ pengajuans.from }} sampai {{ pengajuans.to }} dari {{ pengajuans.total }} pengajuan
              </div>
              <div class="flex items-center gap-1">
                <Link
                  v-for="link in pengajuans.links"
                  :key="link.label"
                  :href="link.url || '#'"
                  :disabled="!link.url"
                  v-html="link.label"
                  class="px-3 py-1.5 rounded-lg text-xs transition-all border shadow-xs"
                  :class="[
                    link.active ? 'bg-[#4B49AC] text-white border-[#4B49AC] font-bold' : 'bg-white text-gray-600 hover:bg-gray-100',
                    !link.url ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
                  ]"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Dialog Verifikasi Detail & Bukti -->
    <Dialog v-model:open="showDetailModal">
      <DialogContent class="sm:max-w-4xl bg-white p-0 overflow-hidden sm:rounded-2xl border border-gray-200 shadow-xl [&>button]:text-white [&>button:hover]:bg-white/20 [&>button]:rounded-lg [&>button]:p-1">
        <DialogHeader class="bg-[#4B49AC] text-white p-6">
          <DialogTitle class="text-white text-xl font-bold flex items-center gap-2">
            <Eye class="w-6 h-6" /> Verifikasi Bukti Pembayaran
          </DialogTitle>
          <DialogDescription class="text-indigo-100 text-xs">
            Tinjau berkas bukti SPP bulanan dan pembayaran ujian yang dikirimkan oleh mahasiswa.
          </DialogDescription>
        </DialogHeader>

        <div v-if="selectedRequest" class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
          <!-- Student info summary -->
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 bg-gray-50 rounded-xl border border-gray-200 text-xs">
            <div>
              <span class="font-bold text-gray-400 uppercase tracking-wider block mb-0.5">Nama Lengkap</span>
              <span class="font-bold text-gray-900 text-sm">{{ selectedRequest.nama_lengkap }}</span>
            </div>
            <div>
              <span class="font-bold text-gray-400 uppercase tracking-wider block mb-0.5">NIM / Kelas</span>
              <span class="font-mono font-bold text-[#4B49AC] text-sm">{{ selectedRequest.nim }}</span>
              <span class="text-gray-500 block">Kelas: {{ selectedRequest.kelas }}</span>
            </div>
            <div>
              <span class="font-bold text-gray-400 uppercase tracking-wider block mb-0.5">Semester / Prodi</span>
              <span class="font-bold text-gray-900 text-sm">{{ selectedRequest.semester }}</span>
              <span class="text-gray-500 block">{{ selectedRequest.prodi }}</span>
            </div>
            <div>
              <span class="font-bold text-gray-400 uppercase tracking-wider block mb-0.5">Ujian / Bulan SPP</span>
              <span class="font-black text-indigo-700 text-sm block">KARTU {{ selectedRequest.jenis_ujian }}</span>
              <span class="text-gray-800 font-semibold block">SPP: {{ selectedRequest.bulan_spp }} {{ selectedRequest.tahun_spp }}</span>
            </div>
          </div>

          <!-- Document Images Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Bukti SPP -->
            <div class="space-y-2">
              <div class="flex items-center justify-between">
                <span class="text-sm font-bold text-gray-700 flex items-center gap-1">
                  <FileText class="w-4 h-4 text-indigo-500" /> Bukti SPP Bulan Berjalan
                </span>
                <a :href="`/storage/${selectedRequest.bukti_spp}`" target="_blank" class="text-xs font-bold text-[#4B49AC] hover:underline">
                  Buka Gambar Penuh
                </a>
              </div>
              <div class="border rounded-xl bg-gray-100/50 p-2 flex items-center justify-center min-h-[300px]">
                <img 
                  :src="`/storage/${selectedRequest.bukti_spp}`" 
                  class="max-h-[350px] object-contain rounded-lg shadow-xs" 
                />
              </div>
            </div>

            <!-- Bukti Ujian -->
            <div class="space-y-2">
              <div class="flex items-center justify-between">
                <span class="text-sm font-bold text-gray-700 flex items-center gap-1">
                  <FileText class="w-4 h-4 text-indigo-500" /> Bukti Pembayaran Ujian
                </span>
                <a :href="`/storage/${selectedRequest.bukti_pembayaran_ujian}`" target="_blank" class="text-xs font-bold text-[#4B49AC] hover:underline">
                  Buka Gambar Penuh
                </a>
              </div>
              <div class="border rounded-xl bg-gray-100/50 p-2 flex items-center justify-center min-h-[300px]">
                <img 
                  :src="`/storage/${selectedRequest.bukti_pembayaran_ujian}`" 
                  class="max-h-[350px] object-contain rounded-lg shadow-xs" 
                />
              </div>
            </div>
          </div>

          <!-- Rejection Message if already rejected -->
          <div v-if="selectedRequest.status === 2" class="p-4 rounded-xl border border-rose-200 bg-rose-50/50">
            <span class="text-xs font-bold text-rose-800 uppercase block mb-1">Catatan Penolakan Sebelumnya</span>
            <p class="text-sm text-rose-950 font-semibold">{{ selectedRequest.keterangan || '-' }}</p>
          </div>
        </div>

        <DialogFooter class="p-6 pt-0 gap-3 border-t pt-4 flex flex-col-reverse sm:flex-row justify-end bg-gray-50">
          <button 
            type="button" 
            @click="showDetailModal = false"
            class="px-5 py-2.5 rounded-xl border border-gray-300 font-bold text-sm text-gray-700 bg-white hover:bg-gray-100 transition-all text-center cursor-pointer"
          >
            Tutup
          </button>
          
          <template v-if="selectedRequest && selectedRequest.status === 0">
            <button 
              type="button" 
              @click="showRejectModal = true"
              class="px-5 py-2.5 rounded-xl bg-rose-50 border border-rose-200 hover:bg-rose-600 text-rose-700 hover:text-white font-bold text-sm shadow-xs transition-all text-center cursor-pointer"
            >
              Tolak Berkas
            </button>
            <button 
              type="button" 
              @click="showConfirmCompleteModal = true"
              class="px-6 py-2.5 rounded-xl bg-[#4B49AC] hover:bg-[#3a3888] text-white font-bold text-sm shadow-md transition-all flex items-center justify-center gap-1.5 text-center cursor-pointer"
            >
              <CheckCircle2 class="w-4 h-4" /> Setujui & Tandai Selesai
            </button>
          </template>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Dialog Konfirmasi Reject -->
    <Dialog v-model:open="showRejectModal">
      <DialogContent class="sm:max-w-md bg-white p-0 overflow-hidden sm:rounded-2xl border border-gray-200 shadow-xl [&>button]:text-white [&>button:hover]:bg-white/20 [&>button]:rounded-lg [&>button]:p-1 z-55">
        <DialogHeader class="bg-[#4B49AC] text-white p-6">
          <DialogTitle class="text-white text-xl font-bold flex items-center gap-2">
            <XCircle class="w-6 h-6" /> Alasan Penolakan Berkas
          </DialogTitle>
          <DialogDescription class="text-indigo-100 text-xs">
            Masukkan alasan penolakan agar mahasiswa tahu berkas mana yang salah.
          </DialogDescription>
        </DialogHeader>

        <div class="p-6 space-y-4">
          <div class="space-y-2">
            <label class="text-sm font-semibold text-gray-700">Catatan Alasan</label>
            <textarea
              v-model="rejectReason"
              placeholder="Contoh: Gambar bukti SPP buram, atau Kwitansi Ujian salah tahun..."
              rows="4"
              class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:border-[#4B49AC] text-sm text-gray-700 bg-gray-50/50 resize-none"
            ></textarea>
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
            @click="submitStatus(2, rejectReason)"
            :disabled="!rejectReason.trim() || statusForm.processing"
            class="w-full sm:flex-1 px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 disabled:bg-gray-300 text-white font-bold text-sm shadow-md transition-all flex items-center justify-center gap-2 text-center cursor-pointer"
          >
            <span v-if="statusForm.processing" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
            Tolak Pengajuan
          </button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Dialog Konfirmasi Selesai / Approval -->
    <Dialog v-model:open="showConfirmCompleteModal">
      <DialogContent class="sm:max-w-md bg-white p-0 overflow-hidden sm:rounded-2xl border border-gray-200 shadow-xl [&>button]:text-white [&>button:hover]:bg-white/20 [&>button]:rounded-lg [&>button]:p-1 z-55">
        <DialogHeader class="bg-[#4B49AC] text-white p-6">
          <DialogTitle class="text-white text-xl font-bold flex items-center gap-2">
            <CheckCircle2 class="w-6 h-6" /> Setujui & Selesaikan Pengajuan
          </DialogTitle>
          <DialogDescription class="text-indigo-100 text-xs">
            Konfirmasi menyetujui berkas dan menandai pencetakan kartu selesai.
          </DialogDescription>
        </DialogHeader>

        <div class="p-6 space-y-4">
          <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs leading-relaxed flex items-start gap-3 shadow-xs">
            <AlertTriangle class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" />
            <div>
              <span class="font-bold">Apakah Anda yakin?</span> Pastikan Anda sudah mencetak kartu ujian fisik mahasiswa secara manual di luar sistem. Setelah disetujui, mahasiswa akan menerima notifikasi bahwa kartu ujian fisiknya siap diambil di Bagian Akademik.
            </div>
          </div>
        </div>

        <DialogFooter class="p-6 pt-0 gap-3 flex flex-col-reverse sm:flex-row">
          <button 
            type="button" 
            @click="showConfirmCompleteModal = false"
            class="w-full sm:w-auto px-6 py-2.5 rounded-xl border border-gray-300 font-bold text-sm text-gray-700 hover:bg-gray-100 transition-all text-center cursor-pointer"
          >
            Batal
          </button>
          <button 
            type="button" 
            @click="submitStatus(1)"
            :disabled="statusForm.processing"
            class="w-full sm:flex-1 px-6 py-2.5 rounded-xl bg-[#4B49AC] hover:bg-[#3a3888] disabled:bg-gray-300 text-white font-bold text-sm shadow-md transition-all flex items-center justify-center gap-2 text-center cursor-pointer whitespace-nowrap"
          >
            <span v-if="statusForm.processing" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
            <CheckCircle2 class="w-4 h-4 flex-shrink-0" /> Ya, Setujui & Selesaikan
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
