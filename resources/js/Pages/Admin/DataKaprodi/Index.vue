<script setup>
import { ref, watch, computed } from 'vue'
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
  UserCheck,
  X,
  AlertCircle,
  Loader2,
  CheckCircle2,
  XCircle,
  KeyRound,
  ShieldCheck,
  History
} from 'lucide-vue-next'

const props = defineProps({
  kaprodis: Object,
  dosens: Array,
  prodis: Array,
  existingKaprodiDosenIds: Array,
  filters: Object,
})

const page = usePage()

// Filters & Search
const search = ref(props.filters.search || '')

let filterTimeout = null
const updateFilters = () => {
  if (filterTimeout) clearTimeout(filterTimeout)
  filterTimeout = setTimeout(() => {
    router.get('/v2/admin/data-master/data-kaprodi', {
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
const selectedKaprodi = ref(null)

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
  dosens_id: '',
  prodis_id: '',
  password_mode: 'dosen',
  password: ''
})

const editForm = useForm({
  id: null,
  dosens_id: '',
  prodis_id: '',
  status: 1,
  password: ''
})

// Duplicate Validation Logic
const isDuplicateAdd = computed(() => {
  if (!form.dosens_id || !form.prodis_id) return false
  return props.kaprodis.data.some(k => 
    String(k.dosens_id) === String(form.dosens_id) && 
    k.prodis && k.prodis.some(p => String(p.id) === String(form.prodis_id))
  )
})

const isDuplicateEdit = computed(() => {
  if (!editForm.dosens_id || !editForm.prodis_id) return false
  return props.kaprodis.data.some(k => 
    String(k.id) !== String(editForm.id) &&
    String(k.dosens_id) === String(editForm.dosens_id) && 
    k.prodis && k.prodis.some(p => String(p.id) === String(editForm.prodis_id))
  )
})

// Check if selected dosen is already a Kaprodi
const isAlreadyKaprodi = computed(() => {
  if (!form.dosens_id) return false
  return props.existingKaprodiDosenIds.includes(Number(form.dosens_id))
})

// Actions
const openEditModal = (kaprodi) => {
  selectedKaprodi.value = kaprodi
  editForm.id = kaprodi.id
  editForm.dosens_id = String(kaprodi.dosens_id)
  editForm.prodis_id = kaprodi.prodis && kaprodi.prodis.length > 0 ? String(kaprodi.prodis[0].id) : ''
  editForm.status = kaprodi.status
  editForm.password = ''
  isEditModalOpen.value = true
}

const submitAdd = () => {
  if (isDuplicateAdd.value) return
  form.post('/v2/admin/data-master/data-kaprodi', {
    onSuccess: () => {
      isAddModalOpen.value = false
      form.reset()
    }
  })
}

const submitUpdate = () => {
  if (isDuplicateEdit.value) return
  editForm.put(`/v2/admin/data-master/data-kaprodi/${editForm.id}`, {
    onSuccess: () => {
      isEditModalOpen.value = false
      editForm.reset()
    }
  })
}

const confirmDelete = (kaprodi) => {
  selectedKaprodi.value = kaprodi
  isDeleteModalOpen.value = true
}

const submitDelete = () => {
  router.delete(`/v2/admin/data-master/data-kaprodi/${selectedKaprodi.value.id}`, {
    onSuccess: () => {
      isDeleteModalOpen.value = false
    }
  })
}

const clearFilters = () => {
  search.value = ''
}
</script>

<template>
  <AdminLayout>
    <Head title="Kelola Data Kaprodi" />

    <div class="space-y-6">
      <!-- Header Section -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-[#1F2937]">Kelola Data Kaprodi</h1>
          <p class="text-[#6B7280]">Manajemen Ketua Program Studi untuk setiap departemen.</p>
        </div>
        <div class="flex items-center gap-2">
          <Button @click="isAddModalOpen = true" class="bg-[#4B49AC] hover:bg-[#3f3d91] text-white rounded-lg shadow-sm">
            <Plus class="w-4 h-4 mr-2" />
            Tambah Kaprodi
          </Button>
        </div>
      </div>

      <!-- Filters Card -->
      <Card class="border-none shadow-sm">
        <CardContent class="p-4">
          <div class="flex flex-col md:flex-row gap-4 items-end">
            <div class="space-y-2 flex-1">
              <Label class="text-xs font-semibold text-[#374151]">Cari Kaprodi</Label>
              <div class="relative">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#9CA3AF]" />
                <Input 
                  v-model="search"
                  placeholder="Cari berdasarkan nama, email, atau program studi..." 
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
              <TableHead class="font-bold text-[#374151]">Nama Kaprodi / Email</TableHead>
              <TableHead class="font-bold text-[#374151]">Program Studi</TableHead>
              <TableHead class="font-bold text-[#374151]">WhatsApp</TableHead>
              <TableHead class="font-bold text-[#374151]">Status</TableHead>
              <TableHead class="text-right font-bold text-[#374151]">Aksi</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="kaprodi in kaprodis.data" :key="kaprodi.id" class="hover:bg-gray-50/50 transition-colors">
              <TableCell>
                <div>
                  <div class="font-semibold text-[#1F2937]">{{ kaprodi.nama }}</div>
                  <div class="text-xs text-[#6B7280]">{{ kaprodi.email }}</div>
                </div>
              </TableCell>
              <TableCell>
                <div class="flex flex-wrap gap-1">
                  <span v-for="prodi in kaprodi.prodis" :key="prodi.id" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700">
                    {{ prodi.nama_prodi }}
                  </span>
                  <span v-if="!kaprodi.prodis || kaprodi.prodis.length === 0" class="text-gray-400">-</span>
                </div>
              </TableCell>
              <TableCell class="text-[#4B5563]">{{ kaprodi.no_telephone }}</TableCell>
              <TableCell>
                <span v-if="kaprodi.status == 1" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700">
                  Aktif
                </span>
                <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700">
                  Nonaktif
                </span>
              </TableCell>
              <TableCell class="text-right">
                <div class="flex justify-end gap-2">
                  <Button variant="ghost" size="sm" @click="openEditModal(kaprodi)" class="text-blue-600 hover:text-blue-700 hover:bg-blue-50 h-8 w-8 p-0">
                    <Pencil class="w-4 h-4" />
                  </Button>
                  <Button variant="ghost" size="sm" @click="confirmDelete(kaprodi)" class="text-red-600 hover:text-red-700 hover:bg-red-50 h-8 w-8 p-0">
                    <Trash2 class="w-4 h-4" />
                  </Button>
                </div>
              </TableCell>
            </TableRow>
            <TableRow v-if="kaprodis.data.length === 0">
              <TableCell colspan="5" class="h-32 text-center text-[#9CA3AF]">
                <div class="flex flex-col items-center justify-center space-y-2">
                  <UserCheck class="w-8 h-8 opacity-20" />
                  <p>Tidak ada data Kaprodi yang ditemukan.</p>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-100 flex items-center justify-between bg-white">
          <p class="text-sm text-[#6B7280]">
            Menampilkan {{ kaprodis.from || 0 }} sampai {{ kaprodis.to || 0 }} dari {{ kaprodis.total }} data
          </p>
          <div class="flex items-center gap-2">
            <Button 
              variant="outline" 
              size="sm" 
              :disabled="!kaprodis.prev_page_url"
              @click="router.get(kaprodis.prev_page_url, {}, { preserveState: true })"
              class="rounded-lg h-8 w-8 p-0"
            >
              <ChevronLeft class="w-4 h-4" />
            </Button>
            <div class="flex items-center gap-1">
              <Button 
                v-for="p in kaprodis.links.slice(1, -1)" 
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
              :disabled="!kaprodis.next_page_url"
              @click="router.get(kaprodis.next_page_url, {}, { preserveState: true })"
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
            <SheetTitle class="text-xl font-bold text-white">Tambah Kaprodi Baru</SheetTitle>
            <SheetDescription class="text-indigo-100 mt-1">
              Pilih dosen dan program studi yang akan dikepalai.
            </SheetDescription>
          </SheetHeader>
        </div>

        <form @submit.prevent="submitAdd" class="flex flex-col h-full overflow-hidden">
          <div class="flex-1 overflow-y-auto p-6 space-y-6">
            <div class="space-y-4">
              <!-- Duplicate Warning -->
              <div v-if="isDuplicateAdd" class="p-3 bg-red-50 border border-red-100 rounded-lg flex items-start gap-3 animate-in fade-in zoom-in duration-300">
                <AlertCircle class="w-5 h-5 text-red-500 shrink-0" />
                <div class="text-xs text-red-700 leading-relaxed font-medium">
                  Dosen ini sudah menjabat sebagai Kaprodi pada Program Studi yang dipilih.
                </div>
              </div>

              <div class="space-y-2">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Pilih Dosen</Label>
                <Select v-model="form.dosens_id" required>
                  <SelectTrigger class="h-11 rounded-lg border-gray-200">
                    <SelectValue placeholder="Klik untuk mencari dosen..." />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="dosen in dosens" :key="dosen.id" :value="String(dosen.id)">
                      {{ dosen.nama }} ({{ dosen.nidn || 'Tanpa NIDN' }})
                    </SelectItem>
                  </SelectContent>
                </Select>
                <p v-if="form.errors.dosens_id" class="text-xs text-red-500 font-medium">{{ form.errors.dosens_id }}</p>
              </div>

              <div class="space-y-2">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Pilih Program Studi</Label>
                <Select v-model="form.prodis_id" required>
                  <SelectTrigger class="h-11 rounded-lg border-gray-200">
                    <SelectValue placeholder="Klik untuk memilih prodi..." />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="prodi in prodis" :key="prodi.id" :value="String(prodi.id)">
                      {{ prodi.nama_prodi }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                <p v-if="form.errors.prodis_id" class="text-xs text-red-500 font-medium">{{ form.errors.prodis_id }}</p>
              </div>

              <!-- Password Mode Selection -->
              <div class="space-y-4 pt-4 border-t border-gray-100">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Keamanan & Password Login</Label>
                
                <div class="grid grid-cols-1 gap-3">
                  <!-- Mode: Dosen Login -->
                  <div 
                    @click="form.password_mode = 'dosen'"
                    class="p-3 border rounded-xl cursor-pointer transition-all flex items-center gap-3"
                    :class="form.password_mode === 'dosen' ? 'border-[#4B49AC] bg-indigo-50/50 ring-1 ring-[#4B49AC]' : 'border-gray-200 hover:border-indigo-200'"
                  >
                    <div class="w-8 h-8 rounded-full bg-white shadow-sm flex items-center justify-center text-[#4B49AC]">
                      <ShieldCheck class="w-4 h-4" />
                    </div>
                    <div class="flex-1">
                      <p class="text-xs font-bold text-[#1F2937]">Gunakan Password Login Dosen</p>
                      <p class="text-[10px] text-gray-500 italic">Sinkron dengan akun dosen saat ini.</p>
                    </div>
                    <CheckCircle2 v-if="form.password_mode === 'dosen'" class="w-4 h-4 text-[#4B49AC]" />
                  </div>

                  <!-- Mode: Existing Kaprodi -->
                  <div 
                    v-if="isAlreadyKaprodi"
                    @click="form.password_mode = 'existing'"
                    class="p-3 border rounded-xl cursor-pointer transition-all flex items-center gap-3 animate-in slide-in-from-left duration-300"
                    :class="form.password_mode === 'existing' ? 'border-[#4B49AC] bg-indigo-50/50 ring-1 ring-[#4B49AC]' : 'border-gray-200 hover:border-indigo-200'"
                  >
                    <div class="w-8 h-8 rounded-full bg-white shadow-sm flex items-center justify-center text-indigo-600">
                      <History class="w-4 h-4" />
                    </div>
                    <div class="flex-1">
                      <p class="text-xs font-bold text-[#1F2937]">Gunakan Password Kaprodi Sebelumnya</p>
                      <p class="text-[10px] text-gray-500 italic">Menggunakan password jabatan sebelumnya.</p>
                    </div>
                    <CheckCircle2 v-if="form.password_mode === 'existing'" class="w-4 h-4 text-[#4B49AC]" />
                  </div>

                  <!-- Mode: Custom -->
                  <div 
                    @click="form.password_mode = 'custom'"
                    class="p-3 border rounded-xl cursor-pointer transition-all flex items-center gap-3"
                    :class="form.password_mode === 'custom' ? 'border-[#4B49AC] bg-indigo-50/50 ring-1 ring-[#4B49AC]' : 'border-gray-200 hover:border-indigo-200'"
                  >
                    <div class="w-8 h-8 rounded-full bg-white shadow-sm flex items-center justify-center text-orange-500">
                      <KeyRound class="w-4 h-4" />
                    </div>
                    <div class="flex-1">
                      <p class="text-xs font-bold text-[#1F2937]">Input Password Baru</p>
                      <p class="text-[10px] text-gray-500 italic">Buat password berbeda untuk jabatan ini.</p>
                    </div>
                    <CheckCircle2 v-if="form.password_mode === 'custom'" class="w-4 h-4 text-[#4B49AC]" />
                  </div>
                </div>

                <!-- Custom Password Input -->
                <div v-if="form.password_mode === 'custom'" class="space-y-2 animate-in slide-in-from-top duration-300">
                  <Label class="text-[10px] font-bold text-orange-600 uppercase">Input Password Baru</Label>
                  <Input v-model="form.password" type="password" placeholder="Masukkan password baru..." class="h-11 rounded-lg border-orange-200 focus:ring-orange-500" required />
                  <p v-if="form.errors.password" class="text-xs text-red-500 font-medium">{{ form.errors.password }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="p-6 border-t border-gray-100 bg-gray-50/50 shrink-0">
            <SheetFooter class="flex flex-row items-center justify-end gap-3">
              <Button type="button" variant="ghost" @click="isAddModalOpen = false" class="h-11 px-6 rounded-lg font-semibold text-gray-500">Batal</Button>
              <Button 
                type="submit" 
                :disabled="form.processing || isDuplicateAdd" 
                class="h-11 px-8 bg-[#4B49AC] hover:bg-[#3f3d91] text-white rounded-lg shadow-lg font-semibold transition-all disabled:opacity-50"
              >
                <Loader2 v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
                Simpan Kaprodi
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
            <SheetTitle class="text-xl font-bold text-white">Edit Data Kaprodi</SheetTitle>
            <SheetDescription class="text-indigo-100 mt-1">
              Perbarui informasi jabatan atau status Kaprodi.
            </SheetDescription>
          </SheetHeader>
        </div>

        <form @submit.prevent="submitUpdate" class="flex flex-col h-full overflow-hidden">
          <div class="flex-1 overflow-y-auto p-6 space-y-6">
            <div class="space-y-4">
              <!-- Duplicate Warning -->
              <div v-if="isDuplicateEdit" class="p-3 bg-red-50 border border-red-100 rounded-lg flex items-start gap-3 animate-in fade-in zoom-in duration-300">
                <AlertCircle class="w-5 h-5 text-red-500 shrink-0" />
                <div class="text-xs text-red-700 leading-relaxed font-medium">
                  Kombinasi Dosen dan Prodi ini sudah terdaftar pada record lain.
                </div>
              </div>

              <div class="space-y-2">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Pilih Dosen</Label>
                <Select v-model="editForm.dosens_id" required>
                  <SelectTrigger class="h-11 rounded-lg border-gray-200">
                    <SelectValue placeholder="Pilih Dosen..." />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="dosen in dosens" :key="dosen.id" :value="String(dosen.id)">
                      {{ dosen.nama }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                <p v-if="editForm.errors.dosens_id" class="text-xs text-red-500 font-medium">{{ editForm.errors.dosens_id }}</p>
              </div>

              <div class="space-y-2">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Pilih Program Studi</Label>
                <Select v-model="editForm.prodis_id" required>
                  <SelectTrigger class="h-11 rounded-lg border-gray-200">
                    <SelectValue placeholder="Pilih Prodi..." />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="prodi in prodis" :key="prodi.id" :value="String(prodi.id)">
                      {{ prodi.nama_prodi }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                <p v-if="editForm.errors.prodis_id" class="text-xs text-red-500 font-medium">{{ editForm.errors.prodis_id }}</p>
              </div>

              <div class="space-y-2 pt-4 border-t border-gray-100">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Status Jabatan</Label>
                <Select v-model="editForm.status" required>
                  <SelectTrigger class="h-11 rounded-lg border-gray-200">
                    <SelectValue placeholder="Pilih Status..." />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem :value="1">Aktif</SelectItem>
                    <SelectItem :value="0">Nonaktif</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div class="space-y-2 pt-4 border-t border-gray-100">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Ubah Password Login (Sinkron Semua Jabatan)</Label>
                <Input v-model="editForm.password" type="password" placeholder="Kosongkan jika tidak ingin diubah" class="h-11 rounded-lg border-gray-200" />
                <p v-if="editForm.errors.password" class="text-xs text-red-500 font-medium">{{ editForm.errors.password }}</p>
                <p class="text-[10px] text-gray-400 italic">Mengubah password di sini akan memperbarui password untuk semua jabatan prodi dosen ini.</p>
              </div>
            </div>
          </div>

          <div class="p-6 border-t border-gray-100 bg-gray-50/50 shrink-0">
            <SheetFooter class="flex flex-row items-center justify-end gap-3">
              <Button type="button" variant="ghost" @click="isEditModalOpen = false" class="h-11 px-6 rounded-lg font-semibold text-gray-500">Batal</Button>
              <Button 
                type="submit" 
                :disabled="editForm.processing || isDuplicateEdit" 
                class="h-11 px-8 bg-[#4B49AC] hover:bg-[#3f3d91] text-white rounded-lg shadow-lg font-semibold transition-all"
              >
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
              Apakah Anda yakin ingin menghapus jabatan Kaprodi untuk <span class="text-danger font-bold">"{{ selectedKaprodi?.dosen?.nama }}"</span>? 
              <br><span class="text-xs mt-3 block italic text-gray-400 font-medium tracking-wide">Tindakan ini hanya menghapus jabatan strukturalnya, data dosen tetap ada.</span>
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
