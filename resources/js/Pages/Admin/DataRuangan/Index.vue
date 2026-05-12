<script setup>
import { ref, watch } from 'vue'
import { Head, router, useForm, usePage } from '@inertiajs/vue3'

import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table'
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetFooter,
  SheetHeader,
  SheetTitle,
} from '@/Components/ui/sheet'
import { Label } from '@/Components/ui/label'
import { Card, CardContent } from '@/Components/ui/card'
import { 
  Plus, 
  Pencil, 
  Trash2, 
  DoorOpen,
  AlertCircle,
  Loader2,
  CheckCircle2,
  XCircle
} from 'lucide-vue-next'

const props = defineProps({
  ruangans: Array,
})

const page = usePage()

// Modals State
const isAddModalOpen = ref(false)
const isEditModalOpen = ref(false)
const isDeleteModalOpen = ref(false)
const selectedRuangan = ref(null)

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
}, { deep: true })

// Forms
const form = useForm({
  nama: '',
})

const editForm = useForm({
  id: null,
  nama: '',
})

// Actions
const openEditModal = (ruangan) => {
  selectedRuangan.value = ruangan
  editForm.id = ruangan.id
  editForm.nama = ruangan.nama
  isEditModalOpen.value = true
}

const submitAdd = () => {
  form.post('/v2/admin/data-master/data-ruangan', {
    onSuccess: () => {
      isAddModalOpen.value = false
      form.reset()
    }
  })
}

const submitUpdate = () => {
  editForm.put(`/v2/admin/data-master/data-ruangan/${editForm.id}`, {
    onSuccess: () => {
      isEditModalOpen.value = false
      editForm.reset()
    }
  })
}

const confirmDelete = (ruangan) => {
  selectedRuangan.value = ruangan
  isDeleteModalOpen.value = true
}

const submitDelete = () => {
  router.delete(`/v2/admin/data-master/data-ruangan/${selectedRuangan.value.id}`, {
    onSuccess: () => {
      isDeleteModalOpen.value = false
    }
  })
}
</script>

<template>
  <AdminLayout>
    <Head title="Data Ruangan" />

    <div class="space-y-6">
      <!-- Header Section -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-[#1F2937]">Data Ruangan</h1>
          <p class="text-[#6B7280]">Kelola daftar ruangan perkuliahan dan laboratorium.</p>
        </div>
        <Button @click="isAddModalOpen = true" class="bg-[#4B49AC] hover:bg-[#3f3d91] text-white rounded-lg shadow-sm font-semibold transition-all duration-300">
          <Plus class="w-4 h-4 mr-2" />
          Tambah Ruangan
        </Button>
      </div>

      <!-- Table Section -->
      <Card class="border-none shadow-sm overflow-hidden bg-white">
        <Table>
          <TableHeader class="bg-[#F9FAFB]">
            <TableRow>
              <TableHead class="w-[100px] font-bold text-[#374151]">No</TableHead>
              <TableHead class="font-bold text-[#374151]">Nama Ruangan</TableHead>
              <TableHead class="font-bold text-[#374151]">Tanggal Dibuat</TableHead>
              <TableHead class="text-right font-bold text-[#374151]">Aksi</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="(ruangan, index) in ruangans" :key="ruangan.id" class="hover:bg-gray-50/50 transition-colors group text-sm">
              <TableCell class="font-medium text-[#1F2937]">{{ index + 1 }}</TableCell>
              <TableCell>
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-[#4B49AC]">
                    <DoorOpen class="w-4 h-4" />
                  </div>
                  <div class="font-bold text-[#1F2937] uppercase tracking-wide">{{ ruangan.nama }}</div>
                </div>
              </TableCell>
              <TableCell class="text-[#6B7280]">
                {{ new Date(ruangan.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}
              </TableCell>
              <TableCell class="text-right">
                <div class="flex justify-end gap-2 transition-opacity">
                  <Button variant="ghost" size="sm" @click="openEditModal(ruangan)" class="text-blue-600 hover:text-blue-700 hover:bg-blue-50 h-8 w-8 p-0 rounded-lg">
                    <Pencil class="w-4 h-4" />
                  </Button>
                  <Button variant="ghost" size="sm" @click="confirmDelete(ruangan)" class="text-red-600 hover:text-red-700 hover:bg-red-50 h-8 w-8 p-0 rounded-lg">
                    <Trash2 class="w-4 h-4" />
                  </Button>
                </div>
              </TableCell>
            </TableRow>
            <TableRow v-if="ruangans.length === 0">
              <TableCell colspan="4" class="h-48 text-center text-[#9CA3AF]">
                <div class="flex flex-col items-center justify-center space-y-3">
                  <div class="p-4 bg-gray-50 rounded-full">
                    <DoorOpen class="w-10 h-10 opacity-20" />
                  </div>
                  <p class="font-medium text-gray-400">Belum ada data ruangan.</p>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </Card>
    </div>

    <!-- Add Drawer -->
    <Sheet :open="isAddModalOpen" @update:open="isAddModalOpen = $event">
      <SheetContent side="right" class="sm:max-w-[30%] w-full p-0 border-none shadow-2xl bg-white flex flex-col">
        <div class="bg-[#4B49AC] p-6 text-white shrink-0">
          <SheetHeader>
            <SheetTitle class="text-xl font-bold text-white">Tambah Ruangan</SheetTitle>
            <SheetDescription class="text-indigo-100 mt-1">
              Daftarkan lokasi atau ruangan perkuliahan baru.
            </SheetDescription>
          </SheetHeader>
        </div>

        <form @submit.prevent="submitAdd" class="flex flex-col h-full overflow-hidden">
          <div class="flex-1 overflow-y-auto p-6 space-y-6">
            <div class="space-y-4">
              <div class="space-y-2">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Ruangan</Label>
                <Input 
                  v-model="form.nama" 
                  placeholder="Misal: R. Teori 1 atau Lab Komputer" 
                  class="h-11 border-gray-200 focus:border-[#4B49AC] focus:ring-[#4B49AC]/20 rounded-lg transition-all"
                  required 
                />
                <p v-if="form.errors.nama" class="text-xs text-red-500 font-medium">{{ form.errors.nama }}</p>
              </div>
            </div>
          </div>

          <div class="p-6 border-t border-gray-100 bg-gray-50/50 shrink-0">
            <SheetFooter class="flex flex-row items-center justify-end gap-3">
              <Button 
                type="button" 
                variant="ghost" 
                @click="isAddModalOpen = false" 
                class="h-11 px-6 rounded-lg text-gray-500 hover:bg-gray-100 transition-all font-semibold"
              >
                Batal
              </Button>
              <Button 
                type="submit" 
                :disabled="form.processing" 
                class="h-11 px-8 bg-[#4B49AC] hover:bg-[#3f3d91] text-white rounded-lg shadow-lg shadow-indigo-100 transition-all font-semibold"
              >
                <Loader2 v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
                Simpan
              </Button>
            </SheetFooter>
          </div>
        </form>
      </SheetContent>
    </Sheet>

    <!-- Edit Drawer -->
    <Sheet :open="isEditModalOpen" @update:open="isEditModalOpen = $event">
      <SheetContent side="right" class="sm:max-w-[30%] w-full p-0 border-none shadow-2xl bg-white flex flex-col">
        <div class="bg-[#4B49AC] p-6 text-white shrink-0">
          <SheetHeader>
            <SheetTitle class="text-xl font-bold text-white">Edit Ruangan</SheetTitle>
            <SheetDescription class="text-indigo-100 mt-1">
              Perbarui rincian nama ruangan terpilih.
            </SheetDescription>
          </SheetHeader>
        </div>

        <form @submit.prevent="submitUpdate" class="flex flex-col h-full overflow-hidden">
          <div class="flex-1 overflow-y-auto p-6 space-y-6">
            <div class="space-y-4">
              <div class="space-y-2">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Ruangan</Label>
                <Input 
                  v-model="editForm.nama" 
                  class="h-11 border-gray-200 focus:border-[#4B49AC] focus:ring-[#4B49AC]/20 rounded-lg transition-all"
                  required 
                />
                <p v-if="editForm.errors.nama" class="text-xs text-red-500 font-medium">{{ editForm.errors.nama }}</p>
              </div>
            </div>
          </div>

          <div class="p-6 border-t border-gray-100 bg-gray-50/50 shrink-0">
            <SheetFooter class="flex flex-row items-center justify-end gap-3">
              <Button 
                type="button" 
                variant="ghost" 
                @click="isEditModalOpen = false" 
                class="h-11 px-6 rounded-lg text-gray-500 hover:bg-gray-100 transition-all font-semibold"
              >
                Batal
              </Button>
              <Button 
                type="submit" 
                :disabled="editForm.processing" 
                class="h-11 px-8 bg-[#4B49AC] hover:bg-[#3f3d91] text-white rounded-lg shadow-lg shadow-indigo-100 transition-all font-semibold"
              >
                <Loader2 v-if="editForm.processing" class="w-4 h-4 mr-2 animate-spin" />
                Simpan
              </Button>
            </SheetFooter>
          </div>
        </form>
      </SheetContent>
    </Sheet>

    <!-- Delete Modal -->
    <Dialog :open="isDeleteModalOpen" @update:open="isDeleteModalOpen = $event">
      <DialogContent class="sm:max-w-[400px] p-0 overflow-hidden border-none shadow-2xl bg-white rounded-2xl">
        <div class="p-8 text-center">
          <div class="w-20 h-20 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6">
            <AlertCircle class="w-10 h-10 text-red-500" />
          </div>
          <DialogHeader>
            <DialogTitle class="text-2xl font-bold text-gray-800 text-center">Konfirmasi Hapus</DialogTitle>
            <DialogDescription class="text-gray-500 text-center mt-3 px-4 leading-relaxed">
              Apakah Anda yakin ingin menghapus ruangan <span class="text-red-600 font-extrabold">{{ selectedRuangan?.nama }}</span>?
              <p class="text-[10px] mt-4 font-bold bg-red-50 text-red-500 p-2 rounded-lg italic">Menghapus ruangan dapat mempengaruhi data jadwal perkuliahan yang sudah ada.</p>
            </DialogDescription>
          </DialogHeader>

          <div class="flex items-center justify-center gap-3 mt-10">
            <Button type="button" variant="ghost" @click="isDeleteModalOpen = false" class="h-12 px-6 rounded-xl text-gray-500 font-bold hover:bg-gray-100 transition-all">
              Batal
            </Button>
            <Button @click="submitDelete" class="h-12 px-8 bg-danger hover:bg-danger/90 text-white rounded-xl shadow-lg shadow-danger/20 font-bold transition-all">
              Ya, Hapus Data
            </Button>
          </div>
        </div>
      </DialogContent>
    </Dialog>

    <!-- Toast Notification -->
    <transition name="toast">
      <div v-if="showToast" class="fixed bottom-10 right-10 z-[100]">
        <div class="bg-[#1F2937] text-white px-8 py-5 rounded-3xl shadow-2xl flex items-center gap-4 border border-gray-700 backdrop-blur-sm bg-opacity-95">
          <div :class="toastType === 'error' ? 'bg-red-500' : 'bg-green-500'" class="p-2 rounded-full ring-4 ring-white/10">
            <CheckCircle2 v-if="toastType === 'success'" class="w-5 h-5 text-white" />
            <XCircle v-else class="w-5 h-5 text-white" />
          </div>
          <div class="flex flex-col">
            <span class="text-sm font-bold tracking-tight">{{ toastMessage }}</span>
          </div>
        </div>
      </div>
    </transition>

  </AdminLayout>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100px) scale(0.8);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100px) scale(0.8);
}
</style>
