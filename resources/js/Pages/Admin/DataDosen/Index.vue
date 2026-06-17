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
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/Components/ui/select'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogFooter,
} from '@/Components/ui/dialog'
import { Label } from '@/Components/ui/label'
import { Card, CardContent } from '@/Components/ui/card'
import { 
  Plus, 
  Search, 
  Pencil, 
  Trash2, 
  ChevronLeft,
  ChevronRight,
  GraduationCap,
  X,
  AlertCircle,
  Loader2,
  CheckCircle2,
  XCircle,
  Download,
  Upload,
  FileSpreadsheet,
  UploadCloud
} from 'lucide-vue-next'
import { useFeederSync } from '@/Composables/useFeederSync'

const props = defineProps({
  dosens: Object,
  filters: Object,
})

const page = usePage()
const { triggerSync } = useFeederSync()

// Filters & Search
const search = ref(props.filters.search || '')

let filterTimeout = null
const updateFilters = () => {
  if (filterTimeout) clearTimeout(filterTimeout)
  filterTimeout = setTimeout(() => {
    router.get('/v2/admin/data-master/data-dosen', {
      search: search.value,
    }, {
      preserveState: true,
      replace: true
    })
  }, 300)
}

watch(search, () => {
  updateFilters()
})

// Modals State
const isAddModalOpen = ref(false)
const isEditModalOpen = ref(false)
const isDeleteModalOpen = ref(false)
const isImportModalOpen = ref(false)
const selectedDosen = ref(null)

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
  nidn: '',
  pembimbing_akademik: '0',
  jenis_kelamin: '',
  no_telephone: '',
  agama: '',
  tanggal_lahir: '',
  tempat_lahir: '',
  email: '',
  password: ''
})

const editForm = useForm({
  id: null,
  nama: '',
  nidn: '',
  pembimbing_akademik: '0',
  jenis_kelamin: '',
  no_telephone: '',
  agama: '',
  tanggal_lahir: '',
  tempat_lahir: '',
  email: '',
  password: '',
  status: 1
})

const importForm = useForm({
  file: null
})

// Actions
const openEditModal = (dosen) => {
  selectedDosen.value = dosen
  editForm.id = dosen.id
  editForm.nama = dosen.nama
  editForm.nidn = dosen.nidn
  editForm.pembimbing_akademik = String(dosen.pembimbing_akademik)
  editForm.jenis_kelamin = dosen.jenis_kelamin
  editForm.no_telephone = dosen.no_telephone
  editForm.agama = dosen.agama
  editForm.tanggal_lahir = dosen.tanggal_lahir
  editForm.tempat_lahir = dosen.tempat_lahir
  editForm.email = dosen.email
  editForm.status = dosen.status
  editForm.password = ''
  isEditModalOpen.value = true
}

const submitAdd = () => {
  form.post('/v2/admin/data-master/data-dosen', {
    onSuccess: () => {
      isAddModalOpen.value = false
      form.reset()
    }
  })
}

const submitUpdate = () => {
  editForm.put(`/v2/admin/data-master/data-dosen/${editForm.id}`, {
    onSuccess: () => {
      isEditModalOpen.value = false
      editForm.reset()
    }
  })
}

const confirmDelete = (dosen) => {
  selectedDosen.value = dosen
  isDeleteModalOpen.value = true
}

const submitDelete = () => {
  router.delete(`/v2/admin/data-master/data-dosen/${selectedDosen.value.id}`, {
    onSuccess: () => {
      isDeleteModalOpen.value = false
    }
  })
}

const submitImport = () => {
  if (!importForm.file) return
  importForm.post('/v2/admin/data-master/data-dosen/import', {
    onSuccess: () => {
      isImportModalOpen.value = false
      importForm.reset()
    }
  })
}

const handleFileSelect = (e) => {
  importForm.file = e.target.files[0]
}

const clearFilters = () => {
  search.value = ''
}

const exportExcel = () => {
  window.location.href = '/v2/admin/data-master/data-dosen/export'
}

const downloadTemplate = () => {
  window.location.href = '/v2/admin/data-master/data-dosen/download-format'
}
</script>

<template>
  <AdminLayout>
    <Head title="Kelola Data Dosen" />

    <div class="space-y-6">
      <!-- Header Section -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-[#1F2937]">Kelola Data Dosen</h1>
          <p class="text-[#6B7280]">Manajemen data tenaga pengajar dan pembimbing akademik.</p>
        </div>
        <div class="flex items-center gap-2">
          <Button @click="triggerSync('pull-dosens')" class="bg-indigo-50 border border-indigo-200 text-[#4B49AC] hover:bg-indigo-100 rounded-lg shadow-sm transition-all">
            <UploadCloud class="w-4 h-4 mr-2" />
            Tarik Data Dosen
          </Button>
          <Button variant="outline" @click="isImportModalOpen = true" class="border-gray-200 text-[#4B5563] hover:bg-gray-50 rounded-lg">
            <Upload class="w-4 h-4 mr-2" />
            Import
          </Button>
          <Button variant="outline" @click="exportExcel" class="border-gray-200 text-[#4B5563] hover:bg-gray-50 rounded-lg">
            <Download class="w-4 h-4 mr-2" />
            Export
          </Button>
          <Button @click="isAddModalOpen = true" class="bg-[#4B49AC] hover:bg-[#3f3d91] text-white rounded-lg shadow-sm">
            <Plus class="w-4 h-4 mr-2" />
            Tambah Dosen
          </Button>
        </div>
      </div>

      <!-- Filters Card -->
      <Card class="border-none shadow-sm">
        <CardContent class="p-4">
          <div class="flex flex-col md:flex-row gap-4 items-end">
            <div class="space-y-2 flex-1">
              <Label class="text-xs font-semibold text-[#374151]">Cari Dosen</Label>
              <div class="relative">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#9CA3AF]" />
                <Input 
                  v-model="search"
                  placeholder="Nama, NIDN, Email, atau No. Telepon..." 
                  class="pl-10 border-gray-200 focus:ring-[#4B49AC] focus:border-[#4B49AC] rounded-lg"
                />
              </div>
            </div>

            <Button variant="outline" @click="clearFilters" class="border-gray-200 text-[#4B5563] hover:bg-gray-50 rounded-lg h-10">
              <X class="w-4 h-4 mr-2" />
              Reset
            </Button>
          </div>
        </CardContent>
      </Card>

      <!-- Table Section -->
      <Card class="border-none shadow-sm overflow-hidden">
        <Table>
          <TableHeader class="bg-[#F9FAFB]">
            <TableRow>
              <TableHead class="font-bold text-[#374151]">Nama / Email</TableHead>
              <TableHead class="font-bold text-[#374151]">NIDN</TableHead>
              <TableHead class="font-bold text-[#374151]">PA</TableHead>
              <TableHead class="font-bold text-[#374151]">Gender</TableHead>
              <TableHead class="font-bold text-[#374151]">WhatsApp</TableHead>
              <TableHead class="font-bold text-[#374151]">Status</TableHead>
              <TableHead class="text-right font-bold text-[#374151]">Aksi</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="dosen in dosens.data" :key="dosen.id" class="hover:bg-gray-50/50 transition-colors">
              <TableCell>
                <div>
                  <div class="font-semibold text-[#1F2937]">{{ dosen.nama }}</div>
                  <div class="text-xs text-[#6B7280]">{{ dosen.email }}</div>
                </div>
              </TableCell>
              <TableCell class="text-[#4B5563] font-medium">{{ dosen.nidn || '-' }}</TableCell>
              <TableCell>
                <span v-if="dosen.pembimbing_akademik == 1" class="inline-flex items-center px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 text-[10px] font-bold uppercase">
                  PA
                </span>
                <span v-else class="text-gray-300">-</span>
              </TableCell>
              <TableCell>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                  :class="(dosen.jenis_kelamin && dosen.jenis_kelamin.toLowerCase().includes('laki')) ? 'bg-blue-50 text-blue-700' : 'bg-pink-50 text-pink-700'">
                  {{ dosen.jenis_kelamin }}
                </span>
              </TableCell>
              <TableCell class="text-[#4B5563]">{{ dosen.no_telephone }}</TableCell>
              <TableCell>
                <span v-if="dosen.status == 1" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700">
                  Aktif
                </span>
                <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700">
                  Nonaktif
                </span>
              </TableCell>
              <TableCell class="text-right">
                <div class="flex justify-end gap-2">
                  <Button variant="ghost" size="sm" @click="openEditModal(dosen)" class="text-blue-600 hover:text-blue-700 hover:bg-blue-50 h-8 w-8 p-0">
                    <Pencil class="w-4 h-4" />
                  </Button>
                  <Button variant="ghost" size="sm" @click="confirmDelete(dosen)" class="text-red-600 hover:text-red-700 hover:bg-red-50 h-8 w-8 p-0">
                    <Trash2 class="w-4 h-4" />
                  </Button>
                </div>
              </TableCell>
            </TableRow>
            <TableRow v-if="dosens.data.length === 0">
              <TableCell colspan="7" class="h-32 text-center text-[#9CA3AF]">
                <div class="flex flex-col items-center justify-center space-y-2">
                  <GraduationCap class="w-8 h-8 opacity-20" />
                  <p>Tidak ada data dosen yang ditemukan.</p>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-100 flex items-center justify-between bg-white">
          <p class="text-sm text-[#6B7280]">
            Menampilkan {{ dosens.from || 0 }} sampai {{ dosens.to || 0 }} dari {{ dosens.total }} data
          </p>
          <div class="flex items-center gap-2">
            <Button 
              variant="outline" 
              size="sm" 
              :disabled="!dosens.prev_page_url"
              @click="router.get(dosens.prev_page_url, {}, { preserveState: true })"
              class="rounded-lg h-8 w-8 p-0"
            >
              <ChevronLeft class="w-4 h-4" />
            </Button>
            <div class="flex items-center gap-1">
              <Button 
                v-for="p in dosens.links.slice(1, -1)" 
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
              :disabled="!dosens.next_page_url"
              @click="router.get(dosens.next_page_url, {}, { preserveState: true })"
              class="rounded-lg h-8 w-8 p-0"
            >
              <ChevronRight class="w-4 h-4" />
            </Button>
          </div>
        </div>
      </Card>
    </div>

    <!-- Add Drawer -->
    <Sheet :open="isAddModalOpen" @update:open="isAddModalOpen = $event">
      <SheetContent side="right" class="sm:max-w-[35%] w-full p-0 border-none shadow-2xl bg-white flex flex-col">
        <div class="bg-[#4B49AC] p-6 text-white shrink-0">
          <SheetHeader>
            <SheetTitle class="text-xl font-bold text-white">Tambah Dosen Baru</SheetTitle>
            <SheetDescription class="text-indigo-100 mt-1">
              Daftarkan tenaga pengajar baru ke dalam sistem.
            </SheetDescription>
          </SheetHeader>
        </div>

        <form @submit.prevent="submitAdd" class="flex flex-col h-full overflow-hidden">
          <div class="flex-1 overflow-y-auto p-6 space-y-6">
            <div class="space-y-4">
              <div class="space-y-2">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Lengkap</Label>
                <Input v-model="form.nama" placeholder="Masukkan nama lengkap dengan gelar..." class="h-11 rounded-lg" required />
                <p v-if="form.errors.nama" class="text-xs text-red-500 font-medium">{{ form.errors.nama }}</p>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">NIDN</Label>
                  <Input v-model="form.nidn" placeholder="12 digit angka" class="h-11 rounded-lg" />
                  <p v-if="form.errors.nidn" class="text-xs text-red-500 font-medium">{{ form.errors.nidn }}</p>
                </div>
                <div class="space-y-2">
                  <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Status PA</Label>
                  <Select v-model="form.pembimbing_akademik" required>
                    <SelectTrigger class="h-11 rounded-lg">
                      <SelectValue placeholder="Pilih..." />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="1">Ya (Aktif PA)</SelectItem>
                      <SelectItem value="0">Tidak</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Jenis Kelamin</Label>
                  <Select v-model="form.jenis_kelamin" required>
                    <SelectTrigger class="h-11 rounded-lg">
                      <SelectValue placeholder="Pilih..." />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="Laki-Laki">Laki-Laki</SelectItem>
                      <SelectItem value="Perempuan">Perempuan</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
                <div class="space-y-2">
                  <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Agama</Label>
                  <Select v-model="form.agama" required>
                    <SelectTrigger class="h-11 rounded-lg">
                      <SelectValue placeholder="Pilih..." />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="Islam">Islam</SelectItem>
                      <SelectItem value="Kristen">Kristen</SelectItem>
                      <SelectItem value="Katolik">Katolik</SelectItem>
                      <SelectItem value="Hindu">Hindu</SelectItem>
                      <SelectItem value="Budha">Budha</SelectItem>
                      <SelectItem value="Konghucu">Konghucu</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">WhatsApp</Label>
                  <Input v-model="form.no_telephone" placeholder="08xxxxxxxx" class="h-11 rounded-lg" required />
                  <p v-if="form.errors.no_telephone" class="text-xs text-red-500 font-medium">{{ form.errors.no_telephone }}</p>
                </div>
                <div class="space-y-2">
                  <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Tempat Lahir</Label>
                  <Input v-model="form.tempat_lahir" placeholder="Kota..." class="h-11 rounded-lg" required />
                </div>
              </div>

              <div class="space-y-2">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal Lahir</Label>
                <Input v-model="form.tanggal_lahir" type="date" class="h-11 rounded-lg" required />
              </div>

              <div class="space-y-4 pt-4 border-t border-gray-100">
                <div class="space-y-2">
                  <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Email (Akun Login)</Label>
                  <Input v-model="form.email" type="email" placeholder="email@example.com" class="h-11 rounded-lg" required />
                  <p v-if="form.errors.email" class="text-xs text-red-500 font-medium">{{ form.errors.email }}</p>
                </div>
                <div class="space-y-2">
                  <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Password</Label>
                  <Input v-model="form.password" type="password" placeholder="Min. 6 karakter" class="h-11 rounded-lg" required />
                  <p v-if="form.errors.password" class="text-xs text-red-500 font-medium">{{ form.errors.password }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="p-6 border-t border-gray-100 bg-gray-50/50 shrink-0">
            <SheetFooter class="flex flex-row items-center justify-end gap-3">
              <Button type="button" variant="ghost" @click="isAddModalOpen = false" class="h-11 px-6 rounded-lg font-semibold">Batal</Button>
              <Button type="submit" :disabled="form.processing" class="h-11 px-8 bg-[#4B49AC] hover:bg-[#3f3d91] text-white rounded-lg shadow-lg font-semibold">
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
      <SheetContent side="right" class="sm:max-w-[35%] w-full p-0 border-none shadow-2xl bg-white flex flex-col">
        <div class="bg-[#4B49AC] p-6 text-white shrink-0">
          <SheetHeader>
            <SheetTitle class="text-xl font-bold text-white">Edit Data Dosen</SheetTitle>
            <SheetDescription class="text-indigo-100 mt-1">
              Perbarui informasi profil dan status jabatan dosen.
            </SheetDescription>
          </SheetHeader>
        </div>

        <form @submit.prevent="submitUpdate" class="flex flex-col h-full overflow-hidden">
          <div class="flex-1 overflow-y-auto p-6 space-y-6">
            <div class="space-y-4">
              <div class="space-y-2">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Lengkap</Label>
                <Input v-model="editForm.nama" class="h-11 rounded-lg" required />
                <p v-if="editForm.errors.nama" class="text-xs text-red-500 font-medium">{{ editForm.errors.nama }}</p>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">NIDN</Label>
                  <Input v-model="editForm.nidn" class="h-11 rounded-lg" />
                  <p v-if="editForm.errors.nidn" class="text-xs text-red-500 font-medium">{{ editForm.errors.nidn }}</p>
                </div>
                <div class="space-y-2">
                  <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Status PA</Label>
                  <Select v-model="editForm.pembimbing_akademik" required>
                    <SelectTrigger class="h-11 rounded-lg">
                      <SelectValue placeholder="Pilih..." />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="1">Ya (Aktif PA)</SelectItem>
                      <SelectItem value="0">Tidak</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Jenis Kelamin</Label>
                  <Select v-model="editForm.jenis_kelamin" required>
                    <SelectTrigger class="h-11 rounded-lg">
                      <SelectValue placeholder="Pilih..." />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="Laki-Laki">Laki-Laki</SelectItem>
                      <SelectItem value="Perempuan">Perempuan</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
                <div class="space-y-2">
                  <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Agama</Label>
                  <Select v-model="editForm.agama" required>
                    <SelectTrigger class="h-11 rounded-lg">
                      <SelectValue placeholder="Pilih..." />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="Islam">Islam</SelectItem>
                      <SelectItem value="Kristen">Kristen</SelectItem>
                      <SelectItem value="Katolik">Katolik</SelectItem>
                      <SelectItem value="Hindu">Hindu</SelectItem>
                      <SelectItem value="Budha">Budha</SelectItem>
                      <SelectItem value="Konghucu">Konghucu</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">WhatsApp</Label>
                  <Input v-model="editForm.no_telephone" class="h-11 rounded-lg" required />
                  <p v-if="editForm.errors.no_telephone" class="text-xs text-red-500 font-medium">{{ editForm.errors.no_telephone }}</p>
                </div>
                <div class="space-y-2">
                  <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Tempat Lahir</Label>
                  <Input v-model="editForm.tempat_lahir" class="h-11 rounded-lg" required />
                </div>
              </div>

              <div class="space-y-2">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal Lahir</Label>
                <Input v-model="editForm.tanggal_lahir" type="date" class="h-11 rounded-lg" required />
              </div>

              <div class="space-y-2 pt-4 border-t border-gray-100">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Status Dosen</Label>
                <Select v-model="editForm.status" required>
                  <SelectTrigger class="h-11 rounded-lg">
                    <SelectValue placeholder="Pilih Status..." />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem :value="1">Aktif</SelectItem>
                    <SelectItem :value="0">Nonaktif</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div class="space-y-4 pt-4 border-t border-gray-100">
                <div class="space-y-2">
                  <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Email (Akun Login)</Label>
                  <Input v-model="editForm.email" type="email" class="h-11 rounded-lg" required />
                  <p v-if="editForm.errors.email" class="text-xs text-red-500 font-medium">{{ editForm.errors.email }}</p>
                </div>
                <div class="space-y-2">
                  <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Ubah Password</Label>
                  <Input v-model="editForm.password" type="password" placeholder="Kosongkan jika tidak ingin diubah" class="h-11 rounded-lg" />
                  <p v-if="editForm.errors.password" class="text-xs text-red-500 font-medium">{{ editForm.errors.password }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="p-6 border-t border-gray-100 bg-gray-50/50 shrink-0">
            <SheetFooter class="flex flex-row items-center justify-end gap-3">
              <Button type="button" variant="ghost" @click="isEditModalOpen = false" class="h-11 px-6 rounded-lg font-semibold">Batal</Button>
              <Button type="submit" :disabled="editForm.processing" class="h-11 px-8 bg-[#4B49AC] hover:bg-[#3f3d91] text-white rounded-lg shadow-lg font-semibold">
                <Loader2 v-if="editForm.processing" class="w-4 h-4 mr-2 animate-spin" />
                Simpan Perubahan
              </Button>
            </SheetFooter>
          </div>
        </form>
      </SheetContent>
    </Sheet>

    <!-- Delete Modal -->
    <Dialog :open="isDeleteModalOpen" @update:open="isDeleteModalOpen = $event">
      <DialogContent class="sm:max-w-[450px] p-0 overflow-hidden border-none shadow-2xl bg-white rounded-lg">
        <div class="p-8 text-center">
          <div class="w-20 h-20 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm">
            <AlertCircle class="w-10 h-10 text-danger" />
          </div>
          <DialogHeader>
            <DialogTitle class="text-2xl font-bold text-gray-800 text-center">Konfirmasi Hapus</DialogTitle>
            <DialogDescription class="text-gray-500 text-center mt-3 px-2 text-base">
              Apakah Anda yakin ingin menghapus data dosen <span class="text-danger font-bold">"{{ selectedDosen?.nama }}"</span>? 
              <br><span class="text-xs mt-3 block italic text-gray-400 font-medium tracking-wide">Tindakan ini juga akan menghapus data jabatan terkait (jika ada).</span>
            </DialogDescription>
          </DialogHeader>

          <div class="flex items-center justify-center gap-4 mt-10">
            <Button 
              type="button" 
              variant="ghost" 
              @click="isDeleteModalOpen = false" 
              class="h-12 px-8 rounded-lg text-gray-500 hover:bg-gray-100 transition-all font-semibold"
            >
              Batal
            </Button>
            <Button 
              @click="submitDelete" 
              class="h-12 px-10 bg-danger hover:bg-danger/90 text-white rounded-lg shadow-lg shadow-danger/20 transition-all font-semibold"
            >
              Ya, Hapus Data
            </Button>
          </div>
        </div>
      </DialogContent>
    </Dialog>

    <!-- Import Modal -->
    <Dialog :open="isImportModalOpen" @update:open="isImportModalOpen = $event">
      <DialogContent class="sm:max-w-[500px] p-0 overflow-hidden border-none shadow-2xl bg-white rounded-lg">
        <div class="bg-[#4B49AC] p-6 text-white shrink-0">
          <DialogHeader>
            <DialogTitle class="text-xl font-bold text-white">Import Data Dosen</DialogTitle>
            <DialogDescription class="text-indigo-100 mt-1">
              Unggah file Excel untuk mengimpor data dosen secara massal.
            </DialogDescription>
          </DialogHeader>
        </div>
        
        <div class="p-8 space-y-6">
          <div class="p-4 bg-indigo-50 border border-indigo-100 rounded-lg flex items-start gap-4">
            <div class="bg-white p-2 rounded-lg shadow-sm shrink-0">
              <FileSpreadsheet class="w-6 h-6 text-[#4B49AC]" />
            </div>
            <div class="space-y-1">
              <h4 class="text-sm font-bold text-[#4B49AC]">Gunakan Template Standard</h4>
              <p class="text-[11px] text-indigo-700 leading-relaxed">
                Pastikan file Anda mengikuti format kolom yang telah ditentukan agar sistem dapat memproses data dengan benar.
              </p>
              <Button variant="link" @click="downloadTemplate" class="p-0 h-auto text-xs font-bold text-[#4B49AC] hover:text-[#3f3d91]">
                Unduh Template Excel
              </Button>
            </div>
          </div>

          <div class="space-y-3">
            <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Pilih File Excel</Label>
            <div class="relative group cursor-pointer border-2 border-dashed border-gray-200 hover:border-[#4B49AC] rounded-lg p-10 transition-all text-center bg-gray-50/30" @click="$refs.fileInput.click()">
              <input type="file" ref="fileInput" class="hidden" @change="handleFileSelect" accept=".xlsx,.xls,.csv" />
              <div v-if="!importForm.file" class="space-y-3">
                <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center mx-auto shadow-sm group-hover:scale-110 transition-all">
                  <Upload class="w-6 h-6 text-gray-400 group-hover:text-[#4B49AC]" />
                </div>
                <div>
                  <p class="text-sm font-semibold text-gray-700">Klik atau seret file ke sini</p>
                  <p class="text-xs text-gray-400 mt-1">Format yang didukung: .xlsx, .xls, .csv</p>
                </div>
              </div>
              <div v-else class="flex flex-col items-center gap-2">
                <div class="w-12 h-12 bg-green-50 rounded-full flex items-center justify-center mx-auto shadow-sm">
                  <CheckCircle2 class="w-6 h-6 text-green-500" />
                </div>
                <p class="text-sm font-bold text-gray-800">{{ importForm.file.name }}</p>
                <Button variant="ghost" size="sm" @click.stop="importForm.file = null" class="text-red-500 hover:text-red-600 h-auto p-0 font-bold text-xs mt-1">Ganti file</Button>
              </div>
            </div>
            <p v-if="importForm.errors.file" class="text-xs text-red-500 font-medium mt-1">{{ importForm.errors.file }}</p>
          </div>
        </div>

        <DialogFooter class="p-6 bg-gray-50/50 border-t border-gray-100 flex flex-row items-center justify-end gap-3">
          <Button type="button" variant="ghost" @click="isImportModalOpen = false" class="h-11 px-6 rounded-lg font-semibold text-gray-500">Batal</Button>
          <Button @click="submitImport" :disabled="importForm.processing || !importForm.file" class="h-11 px-8 bg-[#4B49AC] hover:bg-[#3f3d91] text-white rounded-lg shadow-lg shadow-indigo-200 transition-all font-semibold">
            <Loader2 v-if="importForm.processing" class="w-4 h-4 mr-2 animate-spin" />
            Mulai Import
          </Button>
        </DialogFooter>
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
