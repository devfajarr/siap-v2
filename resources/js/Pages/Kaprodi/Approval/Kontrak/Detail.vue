<script setup>
import { ref, computed, watch } from 'vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardContent } from '@/Components/ui/card'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/Components/ui/dialog'
import { Badge } from '@/Components/ui/badge'
import { Button } from '@/Components/ui/button'
import { ChevronLeft, CheckCircle2, XCircle, AlertCircle, Loader2, Printer } from 'lucide-vue-next'

const props = defineProps({
  kontraks: {
    type: Array,
    required: true
  },
  kaprodi: {
    type: Object,
    required: true
  },
  wadir: {
    type: Object,
    required: true
  },
  params: {
    type: Object,
    required: true
  }
})

// Toast state
const showToast = ref(false)
const toastMessage = ref('')
const toastType = ref('success')

const page = usePage()

watch(() => page.props.flash, (flash) => {
  if (flash?.success) {
    toastMessage.value = flash.success
    toastType.value = 'success'
    showToast.value = true
    setTimeout(() => { showToast.value = false }, 3000)
  } else if (flash?.error) {
    toastMessage.value = flash.error
    toastType.value = 'error'
    showToast.value = true
    setTimeout(() => { showToast.value = false }, 3000)
  }
}, { deep: true, immediate: true })

const form = useForm({
  approve: true
})

const isConfirmModalOpen = ref(false)
const confirmStatus = ref(true)

const openConfirmModal = (status) => {
  confirmStatus.value = status
  isConfirmModalOpen.value = true
}

const submitApprove = () => {
  form.approve = confirmStatus.value
  form.post(route('v2.kaprodi.rekap-kontrak.approve', props.params), {
    preserveScroll: true,
    onSuccess: () => {
      isConfirmModalOpen.value = false
    }
  })
}

const printDocument = () => {
  window.print()
}

const isAllApprovedByKaprodi = computed(() => {
  return props.kontraks.length > 0 && props.kontraks.every(k => !!k.setuju_kaprodi)
})

const getKontrakForMeeting = (i) => {
  if (i === 8 || i === 16) return null
  const adjustedIndex = i > 8 ? i - 2 : i - 1
  if (adjustedIndex < props.kontraks.length) {
    return props.kontraks[adjustedIndex]
  }
  return null
}
</script>

<template>
  <AdminLayout>
    <Head title="Detail Persetujuan Kontrak Kuliah" />

    <div class="space-y-6">
      <!-- Breadcrumb / Header Actions (No Print) -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 no-print">
        <div class="flex items-center gap-2">
          <Link 
            :href="route('v2.kaprodi.rekap-kontrak.index')"
            class="p-1 hover:bg-gray-100 rounded-full transition-colors"
          >
            <ChevronLeft class="w-6 h-6 text-gray-500" />
          </Link>
          <div>
            <h1 class="text-2xl font-bold text-[#1F2937]">Detail Persetujuan Kontrak Kuliah</h1>
            <p class="text-[#6B7280] text-sm">
              Kelola persetujuan kontrak kuliah tingkat program studi.
            </p>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <Button 
            @click="printDocument" 
            variant="outline"
            class="gap-2 border-[#CDD1E1]"
          >
            <Printer class="w-4 h-4" /> Cetak Dokumen
          </Button>

          <Button 
            v-if="!isAllApprovedByKaprodi"
            @click="openConfirmModal(true)" 
            :disabled="form.processing"
            class="bg-green-600 hover:bg-green-700 gap-2 shadow-sm text-white"
          >
            <CheckCircle2 class="w-4 h-4" /> Setujui Semua
          </Button>
          <Button 
            v-else
            @click="openConfirmModal(false)" 
            :disabled="form.processing"
            variant="destructive"
            class="gap-2 shadow-sm"
          >
            <XCircle class="w-4 h-4" /> Batalkan Persetujuan
          </Button>
        </div>
      </div>

      <!-- Document Sheet (Print Area) -->
      <Card class="border-none shadow-sm print-container bg-white">
        <CardContent class="p-8 sm:p-12 print-area">
          
          <!-- Kop Surat Kampus -->
          <div class="flex items-center gap-4 border-b-2 border-black pb-4 mb-6">
            <img src="/images/logomini2.png" alt="Logo POLSA" class="w-16 h-16 object-contain flex-shrink-0" />
            <div class="leading-tight">
              <h3 class="text-xs font-bold text-gray-900 font-sans tracking-widest uppercase">Politeknik</h3>
              <h2 class="text-xl font-black text-black font-sans leading-none uppercase">Sawunggalih Aji</h2>
              <h6 class="text-[8px] text-gray-700 font-sans mt-0.5">
                Jl. Wismoaji No. 8 Kutoarjo, Purworejo, Jawa Tengah, Indonesia Kode Pos 54212
              </h6>
              <h6 class="text-[8px] text-gray-700 font-sans">
                Telp. (0275)642466 (HUNTING) (0275) 3410444 Fax (0275) 642467 | Http://www.polsa.ac.id | info@polsa.ac.id
              </h6>
            </div>
          </div>

          <h3 class="font-bold text-center text-sm mb-6 text-black tracking-wider uppercase font-serif">KONTRAK PERKULIAHAN</h3>

          <!-- Informasi Mata Kuliah -->
          <div class="space-y-1 text-sm mb-6 text-black font-serif">
            <div class="flex">
              <span class="w-48 font-medium">Mata Kuliah / Bobot SKS</span>
              <span class="mr-2">:</span>
              <span>
                {{ kontraks[0]?.matkul?.nama_matkul }} / 
                {{ (kontraks[0]?.matkul?.teori || 0) + (kontraks[0]?.matkul?.praktek || 0) }} SKS
              </span>
            </div>
            <div class="flex">
              <span class="w-48 font-medium">Prodi / Semester</span>
              <span class="mr-2">:</span>
              <span>{{ kontraks[0]?.kelas?.prodi?.nama_prodi }} / Semester {{ kontraks[0]?.kelas?.semester?.semester }}</span>
            </div>
            <div class="flex">
              <span class="w-48 font-medium">Materi Perkuliahan</span>
              <span class="mr-2">:</span>
              <span>Satu Semester</span>
            </div>
          </div>

          <!-- Tabel Materi Perkuliahan -->
          <div class="overflow-x-auto">
            <table class="w-full text-xs text-black border border-black border-collapse font-serif">
              <thead>
                <tr class="bg-gray-100">
                  <th class="border border-black px-3 py-2 text-center w-24">Pertemuan</th>
                  <th class="border border-black px-4 py-2 text-left">Materi Perkuliahan</th>
                  <th class="border border-black px-4 py-2 text-left w-64">Daftar Pustaka</th>
                </tr>
              </thead>
              <tbody>
                <template v-for="i in 16" :key="i">
                  <tr v-if="i === 8" class="bg-gray-50/50">
                    <td class="border border-black px-3 py-2.5 text-center font-bold">8</td>
                    <td colspan="2" class="border border-black px-4 py-2.5 font-bold uppercase tracking-wider text-center">UTS</td>
                  </tr>
                  <tr v-else-if="i === 16" class="bg-gray-50/50">
                    <td class="border border-black px-3 py-2.5 text-center font-bold">16</td>
                    <td colspan="2" class="border border-black px-4 py-2.5 font-bold uppercase tracking-wider text-center">UAS</td>
                  </tr>
                  <tr v-else>
                    <td class="border border-black px-3 py-2 text-center font-medium">{{ i }}</td>
                    <td class="border border-black px-4 py-2 text-left leading-relaxed">
                      {{ getKontrakForMeeting(i)?.materi ?? '-' }}
                    </td>
                    <td class="border border-black px-4 py-2 text-left leading-relaxed">
                      {{ getKontrakForMeeting(i)?.pustaka ?? '-' }}
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>

          <!-- Presentasi Penilaian -->
          <div class="mt-6 text-xs text-black font-serif">
            <h4 class="font-bold mb-2">Persentase Evaluasi/Penilaian Perkuliahan:</h4>
            <ol class="space-y-1 pl-4">
              <li class="flex items-center">
                <span class="w-48">1. Presensi / Kehadiran</span>
                <span class="mr-2">:</span>
                <span class="font-semibold">15%</span>
              </li>
              <li class="flex items-center">
                <span class="w-48">2. Tugas Mandiri / Terstruktur</span>
                <span class="mr-2">:</span>
                <span class="font-semibold">20%</span>
              </li>
              <li class="flex items-center">
                <span class="w-48">3. Sikap dan Keaktifan</span>
                <span class="mr-2">:</span>
                <span class="font-semibold">15%</span>
              </li>
              <li class="flex items-center">
                <span class="w-48">4. Ujian Tengah Semester (UTS)</span>
                <span class="mr-2">:</span>
                <span class="font-semibold">25%</span>
              </li>
              <li class="flex items-center">
                <span class="w-48" style="border-bottom: 1px solid black;">5. Ujian Akhir Semester (UAS)</span>
                <span class="mr-2">:</span>
                <span class="font-semibold" style="border-bottom: 1px solid black;">25%</span>
              </li>
              <li class="flex items-center font-bold text-sm pt-1">
                <span class="w-48">Total Persentase</span>
                <span class="mr-2">:</span>
                <span>100%</span>
              </li>
            </ol>
          </div>

          <!-- Tanda Tangan Section -->
          <div class="mt-10 grid grid-cols-2 gap-y-8 text-center text-xs font-serif leading-loose">
            <div>
              <p class="font-medium">Dosen Pengampu,</p>
              <p class="mt-16 underline font-bold">{{ kontraks[0]?.jadwal?.dosen?.nama ?? '.....................................................' }}</p>
            </div>
            <div>
              <p class="font-medium">Komting Mahasiswa,</p>
              <p class="mt-16 underline font-bold">.....................................................</p>
            </div>
            
            <div class="col-span-2 text-center py-2 font-bold uppercase tracking-wider">Mengetahui,</div>

            <div>
              <p class="font-medium">Ketua Program Studi (Kaprodi),</p>
              <p class="mt-16 underline font-bold">{{ kaprodi?.nama ?? '.....................................................' }}</p>
            </div>
            <div>
              <p class="font-medium">Wakil Direktur,</p>
              <p class="mt-16 underline font-bold">{{ wadir?.nama ?? '.....................................................' }}</p>
            </div>
          </div>

          <!-- Status Approval Widget (No Print) -->
          <div class="mt-8 p-4 bg-gray-50 border border-gray-200 rounded-lg no-print flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
              <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">Status Persetujuan</span>
              <div class="flex flex-wrap gap-3">
                <div class="flex items-center gap-1.5 text-sm">
                  <span class="font-medium">Kaprodi:</span>
                  <Badge :variant="isAllApprovedByKaprodi ? 'success' : 'warning'">
                    {{ isAllApprovedByKaprodi ? 'Disetujui' : 'Pending' }}
                  </Badge>
                </div>
                <div class="flex items-center gap-1.5 text-sm">
                  <span class="font-medium">Wakil Direktur:</span>
                  <Badge :variant="kontraks.every(k => !!k.setuju_wadir) ? 'success' : 'warning'">
                    {{ kontraks.every(k => !!k.setuju_wadir) ? 'Disetujui' : 'Pending' }}
                  </Badge>
                </div>
              </div>
            </div>
          </div>

        </CardContent>
      </Card>
    </div>

    <!-- Confirm Modal -->
    <Dialog :open="isConfirmModalOpen" @update:open="isConfirmModalOpen = $event">
      <DialogContent class="sm:max-w-[450px] p-0 overflow-hidden border-none shadow-2xl bg-white rounded-2xl">
        <div class="p-8 text-center">
          <div :class="[confirmStatus ? 'bg-green-50' : 'bg-red-50']" class="size-20 rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm">
            <AlertCircle :class="[confirmStatus ? 'text-green-600' : 'text-red-600']" class="size-10" />
          </div>
          <DialogHeader>
            <DialogTitle class="text-2xl font-bold text-gray-800 text-center">
              {{ confirmStatus ? 'Konfirmasi Persetujuan' : 'Batalkan Persetujuan' }}
            </DialogTitle>
            <DialogDescription class="text-gray-500 text-center mt-3 px-2 text-base">
              Apakah Anda yakin ingin {{ confirmStatus ? 'menyetujui seluruh' : 'membatalkan seluruh persetujuan' }} kontrak kuliah <span class="font-bold text-gray-900">"{{ kontraks[0]?.matkul?.nama_matkul }}"</span> kelas <span class="font-bold text-gray-900">"{{ kontraks[0]?.kelas?.nama_kelas }}"</span>?
            </DialogDescription>
          </DialogHeader>

          <div class="flex items-center justify-center gap-4 mt-10">
            <Button 
              type="button" 
              variant="outline" 
              @click="isConfirmModalOpen = false" 
              class="h-12 px-8 rounded-xl text-gray-500 hover:bg-gray-100 transition-all font-semibold"
            >
              Batal
            </Button>
            <Button 
              @click="submitApprove" 
              :disabled="form.processing"
              :class="[confirmStatus ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700']"
              class="h-12 px-10 text-white rounded-xl shadow-lg transition-all font-semibold"
            >
              <Loader2 v-if="form.processing" class="size-4 mr-2 animate-spin" />
              {{ confirmStatus ? 'Ya, Setujui' : 'Ya, Batalkan' }}
            </Button>
          </div>
        </div>
      </DialogContent>
    </Dialog>

    <!-- Toast Notification -->
    <transition name="toast">
      <div v-if="showToast" class="fixed bottom-10 right-10 z-[100]">
        <div class="bg-white px-4 py-3 rounded-lg shadow-xl border border-gray-100 flex items-center gap-3">
          <div :class="toastType === 'error' ? 'bg-red-500' : 'bg-green-500'" class="p-1.5 rounded-full">
            <CheckCircle2 v-if="toastType === 'success'" class="size-4 text-white" />
            <XCircle v-else class="size-4 text-white" />
          </div>
          <span class="text-sm font-medium">{{ toastMessage }}</span>
        </div>
      </div>
    </transition>

  </AdminLayout>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.toast-enter-from {
  opacity: 0;
  transform: translateY(20px) scale(0.95);
}

.toast-leave-to {
  opacity: 0;
  transform: translateY(20px) scale(0.95);
}

@media print {
  /* Hide unnecessary elements on print */
  nav, aside, footer, .no-print, button, a {
    display: none !important;
  }
  
  body, main {
    margin: 0 !important;
    padding: 0 !important;
    background: white !important;
  }
  
  main {
    margin-left: 0 !important;
  }
  
  .print-container {
    border: none !important;
    box-shadow: none !important;
    padding: 0 !important;
    margin: 0 !important;
    width: 100% !important;
  }

  table {
    page-break-inside: auto;
  }

  tr {
    page-break-inside: avoid;
    page-break-after: auto;
  }
}
</style>
