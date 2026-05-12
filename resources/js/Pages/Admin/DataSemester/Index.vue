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
import { Switch } from '@/Components/ui/switch'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/Components/ui/dialog'
import { Label } from '@/Components/ui/label'
import { Card, CardContent } from '@/Components/ui/card'
import { 
  Plus, 
  Trash2, 
  GraduationCap,
  AlertCircle,
  Loader2,
  CheckCircle2,
  XCircle,
  ToggleLeft,
  ToggleRight
} from 'lucide-vue-next'

const props = defineProps({
  semesters: Array,
  ganjilActive: Boolean,
  genapActive: Boolean,
})

const page = usePage()

// Modals State
const isAddModalOpen = ref(false)
const isDeleteModalOpen = ref(false)
const isStatusModalOpen = ref(false)
const selectedSemester = ref(null)
const selectedType = ref('')

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
  semester: '',
})

// Actions
const submitAdd = () => {
  form.post('/v2/admin/data-master/data-semester', {
    onSuccess: () => {
      isAddModalOpen.value = false
      form.reset()
    }
  })
}

const confirmDelete = (semester) => {
  selectedSemester.value = semester
  isDeleteModalOpen.value = true
}

const submitDelete = () => {
  router.delete(`/v2/admin/data-master/data-semester/${selectedSemester.value.id}`, {
    onSuccess: () => {
      isDeleteModalOpen.value = false
    }
  })
}

const confirmToggleStatus = (type) => {
  selectedType.value = type
  isStatusModalOpen.value = true
}

const submitToggleStatus = () => {
  router.post('/v2/admin/data-master/data-semester/ganti-status', {
    type: selectedType.value
  }, {
    onSuccess: () => {
      isStatusModalOpen.value = false
    }
  })
}
</script>

<template>
  <AdminLayout>
    <Head title="Data Semester" />

    <div class="space-y-6">
      <!-- Header Section -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-[#1F2937]">Data Semester</h1>
          <p class="text-[#6B7280]">Kelola daftar semester dan kontrol status aktif ganjil/genap.</p>
        </div>
        <div class="flex flex-wrap gap-3">
          <Button @click="isAddModalOpen = true" class="bg-[#4B49AC] hover:bg-[#3f3d91] text-white rounded-lg shadow-sm">
            <Plus class="w-4 h-4 mr-2" />
            Tambah Semester
          </Button>
        </div>
      </div>

      <!-- Status Info Card -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <Card :class="['border-none shadow-md transition-all duration-300', ganjilActive ? 'bg-success text-white' : 'bg-danger text-white']">
          <CardContent class="p-6 flex items-center justify-between">
            <div class="flex items-center gap-4">
              <div :class="['p-3 rounded-2xl group-hover:scale-110 transition-transform duration-300', ganjilActive ? 'bg-white/20' : 'bg-white/10']">
                <GraduationCap class="w-8 h-8 text-white" />
              </div>
              <div>
                <p class="text-sm font-medium opacity-90">Status Semester</p>
                <h3 class="text-2xl font-bold uppercase tracking-tight">Ganjil</h3>
              </div>
            </div>
            <div class="flex flex-col items-end gap-2">
              <span class="text-[10px] font-bold uppercase tracking-widest opacity-80">{{ ganjilActive ? 'Aktif' : 'Non-Aktif' }}</span>
              <Switch 
                :checked="ganjilActive" 
                @update:checked="() => confirmToggleStatus('ganjil')"
                class="data-[state=checked]:bg-white/40 data-[state=unchecked]:bg-black/20"
              />
            </div>
          </CardContent>
        </Card>

        <Card :class="['border-none shadow-md transition-all duration-300', genapActive ? 'bg-success text-white' : 'bg-danger text-white']">
          <CardContent class="p-6 flex items-center justify-between">
            <div class="flex items-center gap-4">
              <div :class="['p-3 rounded-2xl group-hover:scale-110 transition-transform duration-300', genapActive ? 'bg-white/20' : 'bg-white/10']">
                <GraduationCap class="w-8 h-8 text-white" />
              </div>
              <div>
                <p class="text-sm font-medium opacity-90">Status Semester</p>
                <h3 class="text-2xl font-bold uppercase tracking-tight">Genap</h3>
              </div>
            </div>
            <div class="flex flex-col items-end gap-2">
              <span class="text-[10px] font-bold uppercase tracking-widest opacity-80">{{ genapActive ? 'Aktif' : 'Non-Aktif' }}</span>
              <Switch 
                :checked="genapActive" 
                @update:checked="() => confirmToggleStatus('genap')"
                class="data-[state=checked]:bg-white/40 data-[state=unchecked]:bg-black/20"
              />
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Table Section -->
      <Card class="border-none shadow-sm overflow-hidden">
        <Table>
          <TableHeader class="bg-[#F9FAFB]">
            <TableRow>
              <TableHead class="w-[100px] font-bold text-[#374151]">No</TableHead>
              <TableHead class="font-bold text-[#374151]">Semester</TableHead>
              <TableHead class="font-bold text-[#374151]">Tipe</TableHead>
              <TableHead class="font-bold text-[#374151]">Status</TableHead>
              <TableHead class="text-right font-bold text-[#374151]">Aksi</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="(sem, index) in semesters" :key="sem.id" class="hover:bg-gray-50/50 transition-colors">
              <TableCell class="font-medium text-[#1F2937]">{{ index + 1 }}</TableCell>
              <TableCell>
                <div class="font-semibold text-[#1F2937]">Semester {{ sem.semester }}</div>
              </TableCell>
              <TableCell>
                <span :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                  sem.semester % 2 === 0 ? 'bg-blue-50 text-blue-700' : 'bg-orange-50 text-orange-700'
                ]">
                  {{ sem.semester % 2 === 0 ? 'Genap' : 'Ganjil' }}
                </span>
              </TableCell>
              <TableCell>
                <div v-if="sem.status == 1" class="flex items-center text-green-600 gap-1.5">
                  <CheckCircle2 class="w-4 h-4" />
                  <span class="text-xs font-bold uppercase">Aktif</span>
                </div>
                <div v-else class="flex items-center text-gray-400 gap-1.5">
                  <XCircle class="w-4 h-4" />
                  <span class="text-xs font-medium uppercase">Tidak Aktif</span>
                </div>
              </TableCell>
              <TableCell class="text-right">
                <div class="flex justify-end gap-2">
                  <Button variant="ghost" size="sm" @click="confirmDelete(sem)" class="text-red-600 hover:text-red-700 hover:bg-red-50 h-8 w-8 p-0">
                    <Trash2 class="w-4 h-4" />
                  </Button>
                </div>
              </TableCell>
            </TableRow>
            <TableRow v-if="semesters.length === 0">
              <TableCell colspan="5" class="h-32 text-center text-[#9CA3AF]">
                <div class="flex flex-col items-center justify-center space-y-2">
                  <GraduationCap class="w-8 h-8 opacity-20" />
                  <p>Tidak ada data semester yang ditemukan.</p>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </Card>
    </div>

    <!-- Add Modal -->
    <Dialog :open="isAddModalOpen" @update:open="isAddModalOpen = $event">
      <DialogContent class="sm:max-w-[450px] p-0 overflow-hidden border-none shadow-2xl bg-white rounded-xl">
        <div class="bg-[#4B49AC] p-6 text-white relative">
          <DialogHeader>
            <DialogTitle class="text-xl font-bold text-white">Tambah Semester</DialogTitle>
            <DialogDescription class="text-indigo-100 mt-1">
              Masukkan nomor semester baru.
            </DialogDescription>
          </DialogHeader>
        </div>

        <form @submit.prevent="submitAdd" class="p-6 space-y-6">
          <div class="space-y-2">
            <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Nomor Semester</Label>
            <Input 
              v-model="form.semester" 
              type="number"
              placeholder="Contoh: 1" 
              class="h-11 border-gray-200 focus:border-[#4B49AC] focus:ring-[#4B49AC]/20 rounded-lg transition-all"
              required 
            />
            <p v-if="form.errors.semester" class="text-xs text-red-500 font-medium">{{ form.errors.semester }}</p>
          </div>

          <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-50">
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
          </div>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Delete Modal -->
    <Dialog :open="isDeleteModalOpen" @update:open="isDeleteModalOpen = $event">
      <DialogContent class="sm:max-w-[400px] p-0 overflow-hidden border-none shadow-2xl bg-white rounded-xl">
        <div class="p-6 text-center">
          <div class="w-20 h-20 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4">
            <AlertCircle class="w-10 h-10 text-red-500" />
          </div>
          <DialogHeader>
            <DialogTitle class="text-2xl font-bold text-gray-800 text-center">Konfirmasi Hapus</DialogTitle>
            <DialogDescription class="text-gray-500 text-center mt-2 px-4">
              Apakah Anda yakin ingin menghapus <span class="text-red-600 font-bold">Semester {{ selectedSemester?.semester }}</span>? 
              <br><span class="text-xs mt-2 block italic text-red-400 font-medium">Semua data kelas terkait akan dihapus secara permanen.</span>
            </DialogDescription>
          </DialogHeader>

          <div class="flex items-center justify-center gap-3 mt-8">
            <Button 
              type="button" 
              variant="ghost" 
              @click="isDeleteModalOpen = false" 
              class="h-11 px-6 rounded-lg text-gray-500 hover:bg-gray-100 transition-all font-semibold"
            >
              Batal
            </Button>
            <Button 
              @click="submitDelete" 
              class="h-11 px-8 bg-danger hover:bg-danger/90 text-white rounded-lg shadow-lg shadow-danger/20 transition-all font-semibold"
            >
              Ya, Hapus
            </Button>
          </div>
        </div>
      </DialogContent>
    </Dialog>

    <!-- Status Change Confirmation -->
    <Dialog :open="isStatusModalOpen" @update:open="isStatusModalOpen = $event">
      <DialogContent class="sm:max-w-[400px] p-0 overflow-hidden border-none shadow-2xl bg-white rounded-xl">
        <div class="p-6 text-center">
          <div class="w-20 h-20 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-4">
            <ToggleRight class="w-10 h-10 text-[#4B49AC]" />
          </div>
          <DialogHeader>
            <DialogTitle class="text-2xl font-bold text-gray-800 text-center">Ubah Status Aktif</DialogTitle>
            <DialogDescription class="text-gray-500 text-center mt-2 px-4">
              Anda akan mengaktifkan semua <span class="text-[#4B49AC] font-bold uppercase">Semester {{ selectedType }}</span> dan menonaktifkan yang lainnya. 
              Lanjutkan?
            </DialogDescription>
          </DialogHeader>

          <div class="flex items-center justify-center gap-3 mt-8">
            <Button 
              type="button" 
              variant="ghost" 
              @click="isStatusModalOpen = false" 
              class="h-11 px-6 rounded-lg text-gray-500 hover:bg-gray-100 transition-all font-semibold"
            >
              Batal
            </Button>
            <Button 
              @click="submitToggleStatus" 
              class="h-11 px-8 bg-[#4B49AC] hover:bg-[#3f3d91] text-white rounded-lg shadow-lg shadow-indigo-100 transition-all font-semibold"
            >
              Ya, Aktifkan
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
