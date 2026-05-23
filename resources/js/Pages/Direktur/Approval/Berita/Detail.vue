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
  beritas: {
    type: Array,
    required: true
  },
  tahunAkademik: {
    type: Object,
    required: true
  },
  sem: {
    type: String,
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
  form.post(route('v2.direktur.rekap-berita.approve', props.params), {
    preserveScroll: true,
    onSuccess: () => {
      isConfirmModalOpen.value = false
    }
  })
}

const printDocument = () => {
  window.print()
}

const isAllApprovedByWadir = computed(() => {
  return props.beritas.length > 0 && props.beritas.every(b => !!b.setuju_wadir)
})

const pertemuanRange = props.params.pertemuan === '1-7' ? [1, 2, 3, 4, 5, 6, 7] : [8, 9, 10, 11, 12, 13, 14]

const getBeritaForPertemuan = (p) => {
  return props.beritas.find(b => b.pertemuan === p)
}

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  const date = new Date(dateStr)
  return date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

const formatTime = (timeStr) => {
  if (!timeStr) return '-'
  return timeStr.substring(0, 5)
}
</script>

<template>
  <AdminLayout>
    <Head title="Detail Persetujuan Rekap Berita Acara" />

    <div class="space-y-6">
      <!-- Breadcrumb / Header Actions (No Print) -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 no-print">
        <div class="flex items-center gap-2">
          <Link 
            :href="route('v2.direktur.rekap-berita.index')"
            class="p-1 hover:bg-gray-100 rounded-full transition-colors"
          >
            <ChevronLeft class="w-6 h-6 text-gray-500" />
          </Link>
          <div>
            <h1 class="text-2xl font-bold text-[#1F2937]">Detail Persetujuan Berita Acara</h1>
            <p class="text-[#6B7280] text-sm">
              Kelola persetujuan berita acara perkuliahan tingkat institusi (Wadir).
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
            v-if="!isAllApprovedByWadir"
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
          <div class="flex items-center justify-center gap-6 border-b-4 border-double border-black pb-4 mb-6">
            <img src="/images/file.png" alt="Logo POLSA" class="w-16 h-16 object-contain flex-shrink-0" />
            <div class="text-center">
              <h2 class="text-lg font-bold tracking-wide text-black uppercase font-serif">Politeknik Sawunggalih Aji</h2>
              <h1 class="text-xl font-extrabold text-black uppercase font-serif">Berita Acara Perkuliahan</h1>
              <h3 class="text-sm font-semibold text-black uppercase font-serif">
                Semester {{ sem }} Tahun Akademik {{ tahunAkademik?.tahun_akademik }}
              </h3>
            </div>
          </div>

          <!-- Informasi Kelas & Dosen -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm mb-6 text-black">
            <div class="space-y-1">
              <div class="flex">
                <span class="w-32 font-medium">Dosen Pengampu</span>
                <span class="mr-2">:</span>
                <span>{{ beritas[0]?.dosen?.nama }}</span>
              </div>
              <div class="flex">
                <span class="w-32 font-medium">Mata Kuliah</span>
                <span class="mr-2">:</span>
                <span>{{ beritas[0]?.matkul?.nama_matkul }}</span>
              </div>
            </div>
            <div class="space-y-1">
              <div class="flex">
                <span class="w-32 font-medium">Program Studi</span>
                <span class="mr-2">:</span>
                <span>{{ beritas[0]?.kelas?.prodi?.nama_prodi }}</span>
              </div>
              <div class="flex">
                <span class="w-32 font-medium">Semester / Kelas</span>
                <span class="mr-2">:</span>
                <span>Semester {{ beritas[0]?.kelas?.semester?.semester }} / {{ beritas[0]?.kelas?.nama_kelas }}</span>
              </div>
            </div>
          </div>

          <!-- Tabel BAP -->
          <div class="overflow-x-auto border-t-2 border-black">
            <table class="w-full text-sm text-black border-collapse font-serif">
              <thead>
                <tr class="border-b border-black">
                  <th class="border border-black px-3 py-2 text-center w-16">Pert. Ke</th>
                  <th class="border border-black px-3 py-2 text-center w-28">Tanggal</th>
                  <th class="border border-black px-3 py-2 text-center w-20">Waktu</th>
                  <th class="border border-black px-4 py-2 text-left">Ikhtisar Materi Kuliah</th>
                  <th class="border border-black px-3 py-2 text-center w-28">Jml Mhs Hadir</th>
                  <th class="border border-black px-3 py-2 text-center w-28">Jml Mhs Tdk Hadir</th>
                  <th class="border border-black px-3 py-2 text-center w-24">Ttd Dosen</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="p in pertemuanRange" :key="p" class="border-b border-black">
                  <td class="border border-black px-3 py-3 text-center">{{ p }}</td>
                  <td class="border border-black px-3 py-3 text-center">
                    {{ formatDate(getBeritaForPertemuan(p)?.tanggal) }}
                  </td>
                  <td class="border border-black px-3 py-3 text-center">
                    {{ formatTime(getBeritaForPertemuan(p)?.waktu) }}
                  </td>
                  <td class="border border-black px-4 py-3 text-left leading-relaxed">
                    {{ getBeritaForPertemuan(p)?.materi ?? '-' }}
                  </td>
                  <td class="border border-black px-3 py-3 text-center">
                    {{ getBeritaForPertemuan(p)?.hadir ?? '-' }}
                  </td>
                  <td class="border border-black px-3 py-3 text-center">
                    {{ getBeritaForPertemuan(p)?.tidak_hadir ?? '-' }}
                  </td>
                  <td class="border border-black px-3 py-3 text-center"></td>
                </tr>
                <tr class="border-b border-black font-semibold">
                  <td class="border border-black px-3 py-2 text-center">UTS/UAS</td>
                  <td class="border border-black px-3 py-2 text-center">-</td>
                  <td class="border border-black px-3 py-2 text-center">-</td>
                  <td class="border border-black px-4 py-2 text-left">Evaluasi Tengah/Akhir Semester</td>
                  <td class="border border-black px-3 py-2 text-center">-</td>
                  <td class="border border-black px-3 py-2 text-center">-</td>
                  <td class="border border-black px-3 py-2 text-center"></td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="mt-4 text-xs italic text-gray-700">
            Catatan : Daftar ini harus diisi setiap perkuliahan sebagai dasar perhitungan honor mengajar.
          </div>

          <!-- Tanda Tangan Verifikator -->
          <div class="mt-8 grid grid-cols-2 text-center text-sm font-serif">
            <div>
              <p class="font-medium">Mengetahui,</p>
              <p class="font-medium mb-16">Ketua Program Studi</p>
              <p class="underline font-bold">{{ beritas[0]?.kelas?.prodi?.kaprodi?.nama ?? '.....................................................' }}</p>
            </div>
            <div>
              <p class="font-medium">&nbsp;</p>
              <p class="font-medium mb-16">Wakil Direktur</p>
              <p class="underline font-bold">{{ page.props.auth?.user?.nama ?? '.....................................................' }}</p>
            </div>
          </div>

          <!-- Status Approval Widget (No Print) -->
          <div class="mt-8 p-4 bg-gray-50 border border-gray-200 rounded-lg no-print flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
              <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">Status Persetujuan</span>
              <div class="flex flex-wrap gap-3">
                <div class="flex items-center gap-1.5 text-sm">
                  <span class="font-medium">Kaprodi:</span>
                  <Badge :variant="beritas.every(b => !!b.setuju_kaprodi) ? 'success' : 'warning'">
                    {{ beritas.every(b => !!b.setuju_kaprodi) ? 'Disetujui' : 'Pending' }}
                  </Badge>
                </div>
                <div class="flex items-center gap-1.5 text-sm">
                  <span class="font-medium">Wakil Direktur:</span>
                  <Badge :variant="isAllApprovedByWadir ? 'success' : 'warning'">
                    {{ isAllApprovedByWadir ? 'Disetujui' : 'Pending' }}
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
              Apakah Anda yakin ingin {{ confirmStatus ? 'menyetujui seluruh' : 'membatalkan seluruh persetujuan' }} rekap berita acara <span class="font-bold text-gray-900">"{{ beritas[0]?.matkul?.nama_matkul }}"</span> kelas <span class="font-bold text-gray-900">"{{ beritas[0]?.kelas?.nama_kelas }}"</span> pertemuan <span class="font-bold text-gray-900">{{ params.pertemuan }}</span>?
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
