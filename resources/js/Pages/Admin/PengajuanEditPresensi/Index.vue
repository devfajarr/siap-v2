<script setup>
import { ref, watch } from 'vue'
import { Head, router, useForm, usePage } from '@inertiajs/vue3'

import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Button } from '@/Components/ui/button'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table'
import { Card, CardContent } from '@/Components/ui/card'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/Components/ui/dialog'
import { 
  ChevronLeft,
  ChevronRight,
  History,
  CheckCircle2,
  XCircle,
  AlertCircle,
  Check,
  Loader2
} from 'lucide-vue-next'

const props = defineProps({
  pengajuans: Object,
})

const page = usePage()

// Toast state
const showToast = ref(false)
const toastMessage = ref('')
const toastType = ref('success')

// Watch for flash messages
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

// Modal State
const isConfirmModalOpen = ref(false)
const selectedPengajuan = ref(null)

const confirmForm = useForm({
  id: null
})

const openConfirmModal = (pengajuan) => {
  selectedPengajuan.value = pengajuan
  confirmForm.id = pengajuan.id
  isConfirmModalOpen.value = true
}

const submitVerify = () => {
  confirmForm.post('/v2/admin/pengajuan-edit-presensi/verify', {
    onSuccess: () => {
      isConfirmModalOpen.value = false
    }
  })
}

</script>

<template>
  <AdminLayout>
    <Head title="Pengajuan Edit Presensi" />

    <div class="space-y-6">
      <!-- Header Section -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-[#1F2937]">Pengajuan Edit Presensi</h1>
          <p class="text-[#6B7280]">Daftar pengajuan dosen untuk membuka akses edit presensi per pertemuan.</p>
        </div>
      </div>

      <!-- Table Section -->
      <Card class="border-none shadow-sm overflow-hidden">
        <Table>
          <TableHeader class="bg-[#F9FAFB]">
            <TableRow>
              <TableHead class="font-bold text-[#374151]">Nama Dosen</TableHead>
              <TableHead class="font-bold text-[#374151]">Mata Kuliah</TableHead>
              <TableHead class="font-bold text-[#374151] text-center">Pertemuan</TableHead>
              <TableHead class="text-right font-bold text-[#374151]">Aksi</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="pengajuan in pengajuans.data" :key="pengajuan.id" class="hover:bg-gray-50/50 transition-colors">
              <TableCell class="font-semibold text-[#1F2937]">
                {{ pengajuan.nama_dosen }}
              </TableCell>
              <TableCell class="text-[#4B5563]">
                {{ pengajuan.nama_matkul }}
              </TableCell>
              <TableCell class="text-center text-[#4B5563]">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-[#4B49AC]">
                  Pertemuan {{ pengajuan.pertemuan }}
                </span>
              </TableCell>
              <TableCell class="text-right">
                <Button 
                  size="sm" 
                  @click="openConfirmModal(pengajuan)" 
                  class="bg-[#4B49AC] hover:bg-[#3f3d91] text-white"
                >
                  <Check class="w-4 h-4 mr-2" />
                  Verifikasi
                </Button>
              </TableCell>
            </TableRow>
            <TableRow v-if="pengajuans.data.length === 0">
              <TableCell colspan="4" class="h-32 text-center text-[#9CA3AF]">
                <div class="flex flex-col items-center justify-center space-y-2">
                  <History class="w-8 h-8 opacity-20" />
                  <p>Tidak ada pengajuan edit presensi saat ini.</p>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>

        <!-- Pagination -->
        <div v-if="pengajuans.total > 0" class="p-4 border-t border-gray-100 flex items-center justify-between bg-white">
          <p class="text-sm text-[#6B7280]">
            Menampilkan {{ pengajuans.from || 0 }} sampai {{ pengajuans.to || 0 }} dari {{ pengajuans.total }} data
          </p>
          <div class="flex items-center gap-2">
            <Button 
              variant="outline" 
              size="sm" 
              :disabled="!pengajuans.prev_page_url"
              @click="router.get(pengajuans.prev_page_url, {}, { preserveState: true })"
              class="rounded-lg h-8 w-8 p-0"
            >
              <ChevronLeft class="w-4 h-4" />
            </Button>
            <div class="flex items-center gap-1">
              <Button 
                v-for="p in pengajuans.links.slice(1, -1)" 
                :key="p.label"
                variant="ghost"
                size="sm"
                @click="router.get(p.url, {}, { preserveState: true })"
                :class="[
                  'h-8 w-8 p-0 rounded-lg font-medium',
                  p.active ? 'bg-[#4B49AC] text-white hover:bg-[#3f3d91]' : 'text-[#4B5563] hover:bg-gray-100'
                ]"
              >
                {{ p.label }}
              </Button>
            </div>
            <Button 
              variant="outline" 
              size="sm" 
              :disabled="!pengajuans.next_page_url"
              @click="router.get(pengajuans.next_page_url, {}, { preserveState: true })"
              class="rounded-lg h-8 w-8 p-0"
            >
              <ChevronRight class="w-4 h-4" />
            </Button>
          </div>
        </div>
      </Card>
    </div>

    <!-- Confirm Modal -->
    <Dialog :open="isConfirmModalOpen" @update:open="isConfirmModalOpen = $event">
      <DialogContent class="sm:max-w-[450px] p-0 overflow-hidden border-none shadow-2xl bg-white rounded-lg">
        <div class="p-8 text-center">
          <div class="w-20 h-20 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm">
            <AlertCircle class="w-10 h-10 text-[#4B49AC]" />
          </div>
          <DialogHeader>
            <DialogTitle class="text-2xl font-bold text-gray-800 text-center">Konfirmasi Verifikasi</DialogTitle>
            <DialogDescription class="text-gray-500 text-center mt-3 px-2 text-base">
              Apakah Anda yakin ingin memberikan akses pengeditan presensi untuk Dosen <span class="font-bold text-[#1F2937]">"{{ selectedPengajuan?.nama_dosen }}"</span> pada Mata Kuliah <span class="font-bold text-[#1F2937]">"{{ selectedPengajuan?.nama_matkul }}"</span> (Pertemuan {{ selectedPengajuan?.pertemuan }})?
            </DialogDescription>
          </DialogHeader>

          <div class="flex items-center justify-center gap-4 mt-10">
            <Button 
              type="button" 
              variant="ghost" 
              @click="isConfirmModalOpen = false" 
              class="h-12 px-8 rounded-lg text-gray-500 hover:bg-gray-100 transition-all font-semibold"
            >
              Batal
            </Button>
            <Button 
              @click="submitVerify" 
              :disabled="confirmForm.processing"
              class="h-12 px-10 bg-[#4B49AC] hover:bg-[#3f3d91] text-white rounded-lg shadow-lg shadow-indigo-500/20 transition-all font-semibold"
            >
              <Loader2 v-if="confirmForm.processing" class="w-4 h-4 mr-2 animate-spin" />
              Ya, Verifikasi
            </Button>
          </div>
        </div>
      </DialogContent>
    </Dialog>

    <!-- Toast Notification -->
    <transition name="toast">
      <div v-if="showToast" class="fixed bottom-10 right-10 z-[100]">
        <div class="bg-[#1F2937] text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-3 border border-gray-700 backdrop-blur-sm bg-opacity-95">
          <div :class="toastType === 'error' ? 'bg-red-500' : 'bg-green-500'" class="p-1.5 rounded-full">
            <CheckCircle2 v-if="toastType === 'success'" class="w-4 h-4 text-white" />
            <XCircle v-else class="w-4 h-4 text-white" />
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
