<script setup>
import { ref, watch } from 'vue'
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
import { ChevronLeft, CheckCircle2, XCircle, AlertCircle, Loader2 } from 'lucide-vue-next'

const props = defineProps({
  absens: {
    type: Array,
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
  form.post(route('v2.kaprodi.rekap-presensi.approve', props.params), {
    preserveScroll: true,
    onSuccess: () => {
        isConfirmModalOpen.value = false
    }
  })
}

const pertemuanRange = props.params.pertemuan === '1-7' ? [1, 2, 3, 4, 5, 6, 7] : [8, 9, 10, 11, 12, 13, 14]

// Group absens by mahasiswa
const groupedPresensi = props.absens.reduce((acc, curr) => {
  if (!acc[curr.mahasiswas_id]) {
    acc[curr.mahasiswas_id] = {
      nama: curr.mahasiswa?.nama_lengkap,
      nim: curr.mahasiswa?.nim,
      records: {}
    }
  }
  acc[curr.mahasiswas_id].records[curr.pertemuan] = curr
  return acc
}, {})

const getStatus = (mhsRecords, pertemuan) => {
  const p = mhsRecords[pertemuan]
  if (!p) return '-'
  return p.status
}

const getStatusVariant = (status) => {
  switch (status) {
    case 'Hadir': return 'success'
    case 'Sakit': return 'warning'
    case 'Izin': return 'indigo'
    case 'Alpa': return 'destructive'
    default: return 'outline'
  }
}

const isAllApprovedByKaprodi = props.absens.every(a => a.setuju_kaprodi === 1)
</script>

<template>
  <AdminLayout>
    <Head title="Detail Persetujuan Rekap Presensi" />

    <div class="space-y-6">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-2">
          <Link 
            :href="route('v2.kaprodi.rekap-presensi.diajukan')"
            class="p-1 hover:bg-gray-100 rounded-full transition-colors"
          >
            <ChevronLeft class="w-6 h-6 text-gray-500" />
          </Link>
          <div>
            <h1 class="text-2xl font-bold text-[#1F2937]">Detail Persetujuan Presensi</h1>
            <p class="text-[#6B7280] text-sm">
              {{ absens[0]?.matkul?.nama_matkul }} - {{ absens[0]?.kelas?.nama_kelas }} (Pertemuan {{ params.pertemuan }})
            </p>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <Button 
            v-if="!isAllApprovedByKaprodi"
            @click="openConfirmModal(true)" 
            :disabled="form.processing"
            class="bg-green-600 hover:bg-green-700 gap-2 shadow-sm"
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

      <Card class="border-none shadow-sm overflow-hidden">
        <CardContent class="p-0">
          <div class="rounded-md border overflow-x-auto">
            <Table>
              <TableHeader class="bg-gray-50/50 text-[10px] uppercase tracking-wider">
                <TableRow>
                  <TableHead class="w-12 text-center">#</TableHead>
                  <TableHead class="w-48">Nama Mahasiswa</TableHead>
                  <TableHead v-for="p in pertemuanRange" :key="p" class="text-center font-bold">
                    P{{ p }}
                  </TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="(mhs, mhsId, index) in groupedPresensi" :key="mhsId">
                  <TableCell class="text-center text-xs text-gray-500">{{ index + 1 }}</TableCell>
                  <TableCell class="font-medium text-xs whitespace-nowrap">
                    {{ mhs.nama }}
                    <div class="text-[10px] text-gray-400 font-normal">{{ mhs.nim }}</div>
                  </TableCell>
                  <TableCell v-for="p in pertemuanRange" :key="p" class="text-center">
                    <Badge 
                      v-if="getStatus(mhs.records, p) !== '-'"
                      :variant="getStatusVariant(getStatus(mhs.records, p))"
                      class="text-[9px] px-1.5 py-0.5"
                    >
                      {{ getStatus(mhs.records, p).charAt(0) }}
                    </Badge>
                    <span v-else class="text-gray-300">-</span>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </CardContent>
      </Card>

      <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500 bg-white p-4 rounded-lg shadow-sm">
        <span class="font-bold">Keterangan Status:</span>
        <div class="flex items-center gap-1">
          <Badge variant="success" class="text-[9px] px-1.5 py-0.5">H</Badge> Hadir
        </div>
        <div class="flex items-center gap-1">
          <Badge variant="warning" class="text-[9px] px-1.5 py-0.5">S</Badge> Sakit
        </div>
        <div class="flex items-center gap-1">
          <Badge variant="indigo" class="text-[9px] px-1.5 py-0.5">I</Badge> Izin
        </div>
        <div class="flex items-center gap-1">
          <Badge variant="destructive" class="text-[9px] px-1.5 py-0.5">A</Badge> Alpa
        </div>
      </div>
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
                Apakah Anda yakin ingin {{ confirmStatus ? 'menyetujui seluruh' : 'membatalkan seluruh persetujuan' }} data presensi <span class="font-bold text-gray-900">"{{ absens[0]?.matkul?.nama_matkul }}"</span> kelas <span class="font-bold text-gray-900">"{{ absens[0]?.kelas?.nama_kelas }}"</span> pertemuan <span class="font-bold text-gray-900">{{ params.pertemuan }}</span>?
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
</style>
