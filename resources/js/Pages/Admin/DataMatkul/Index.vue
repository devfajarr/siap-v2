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
import { Label } from '@/Components/ui/label'
import { Card, CardContent } from '@/Components/ui/card'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/Components/ui/dialog'
import { 
  Plus, 
  Search, 
  Pencil, 
  Trash2, 
  Filter,
  ChevronLeft,
  ChevronRight,
  BookOpen,
  X,
  AlertCircle,
  Loader2,
  CheckCircle2,
  XCircle,
  UploadCloud
} from 'lucide-vue-next'
import { useFeederSync } from '@/Composables/useFeederSync'

const props = defineProps({
  matkuls: Object,
  filters: Object,
  prodis: Array,
  semesters: Array
})

const page = usePage()
const { triggerSync } = useFeederSync()

// Filters & Search
const search = ref(props.filters.search || '')
const prodiFilter = ref(props.filters.prodi_id || 'all')
const semesterFilter = ref(props.filters.semester_id || 'all')

let filterTimeout = null
const updateFilters = () => {
  if (filterTimeout) clearTimeout(filterTimeout)
  filterTimeout = setTimeout(() => {
    router.get('/v2/admin/data-master/data-matkul', {
      search: search.value,
      prodi_id: prodiFilter.value === 'all' ? null : prodiFilter.value,
      semester_id: semesterFilter.value === 'all' ? null : semesterFilter.value
    }, {
      preserveState: true,
      replace: true
    })
  }, 300)
}

watch([search, prodiFilter, semesterFilter], () => {
  updateFilters()
})

// Modals State
const isAddModalOpen = ref(false)
const isEditModalOpen = ref(false)
const isDeleteModalOpen = ref(false)
const selectedMatkul = ref(null)

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
  nama_matkul: '',
  alias: '',
  kode: '',
  prodi_id: '',
  semester_id: '',
  teori: 0,
  praktek: 0
})

const editForm = useForm({
  id: null,
  nama_matkul: '',
  alias: '',
  kode: '',
  prodi_id: '',
  semester_id: '',
  teori: 0,
  praktek: 0
})

// Actions
const openEditModal = (matkul) => {
  selectedMatkul.value = matkul
  editForm.id = matkul.id
  editForm.nama_matkul = matkul.nama_matkul
  editForm.alias = matkul.alias
  editForm.kode = matkul.kode
  editForm.prodi_id = matkul.prodi_id.toString()
  editForm.semester_id = matkul.semester_id.toString()
  editForm.teori = matkul.teori
  editForm.praktek = matkul.praktek
  isEditModalOpen.value = true
}

const submitAdd = () => {
  form.post('/v2/admin/data-master/data-matkul', {
    onSuccess: () => {
      isAddModalOpen.value = false
      form.reset()
    }
  })
}

const submitUpdate = () => {
  editForm.put(`/v2/admin/data-master/data-matkul/${editForm.id}`, {
    onSuccess: () => {
      isEditModalOpen.value = false
      editForm.reset()
    }
  })
}

const confirmDelete = (matkul) => {
  selectedMatkul.value = matkul
  isDeleteModalOpen.value = true
}

const submitDelete = () => {
  router.delete(`/v2/admin/data-master/data-matkul/${selectedMatkul.value.id}`, {
    onSuccess: () => {
      isDeleteModalOpen.value = false
    }
  })
}

const clearFilters = () => {
  search.value = ''
  prodiFilter.value = 'all'
  semesterFilter.value = 'all'
}
</script>

<template>
  <AdminLayout>
    <Head title="Data Mata Kuliah" />

    <div class="space-y-6">
      <!-- Header Section -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-[#1F2937]">Data Mata Kuliah</h1>
          <p class="text-[#6B7280]">Kelola daftar mata kuliah, SKS, dan kurikulum.</p>
        </div>
        <div class="flex items-center gap-2">
          <Button @click="triggerSync('pull-matkuls')" class="bg-indigo-50 border border-indigo-200 text-[#4B49AC] hover:bg-indigo-100 rounded-lg shadow-sm transition-all h-10">
            <UploadCloud class="w-4 h-4 mr-2" />
            Tarik Data Matkul
          </Button>
          <Button @click="isAddModalOpen = true" class="bg-[#4B49AC] hover:bg-[#3f3d91] text-white rounded-lg shadow-sm h-10">
            <Plus class="w-4 h-4 mr-2" />
            Tambah Matkul
          </Button>
        </div>
      </div>

      <!-- Filters Card -->
      <Card class="border-none shadow-sm">
        <CardContent class="p-4">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div class="space-y-2">
              <Label class="text-xs font-semibold text-[#374151]">Cari Mata Kuliah</Label>
              <div class="relative">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#9CA3AF]" />
                <Input 
                  v-model="search"
                  placeholder="Nama atau kode..." 
                  class="pl-10 border-gray-200 focus:ring-[#4B49AC] focus:border-[#4B49AC] rounded-lg"
                />
              </div>
            </div>

            <div class="space-y-2">
              <Label class="text-xs font-semibold text-[#374151]">Program Studi</Label>
              <Select v-model="prodiFilter">
                <SelectTrigger class="border-gray-200 rounded-lg">
                  <SelectValue placeholder="Pilih Prodi" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">Semua Prodi</SelectItem>
                  <SelectItem v-for="prodi in prodis" :key="prodi.id" :value="prodi.id.toString()">
                    {{ prodi.nama_prodi }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="space-y-2">
              <Label class="text-xs font-semibold text-[#374151]">Semester</Label>
              <Select v-model="semesterFilter">
                <SelectTrigger class="border-gray-200 rounded-lg">
                  <SelectValue placeholder="Pilih Semester" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">Semua Semester</SelectItem>
                  <SelectItem v-for="sem in semesters" :key="sem.id" :value="sem.id.toString()">
                    Semester {{ sem.semester }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <Button variant="outline" @click="clearFilters" class="border-gray-200 text-[#4B5563] hover:bg-gray-50 rounded-lg">
              <X class="w-4 h-4 mr-2" />
              Reset Filter
            </Button>
          </div>
        </CardContent>
      </Card>

      <!-- Table Section -->
      <Card class="border-none shadow-sm overflow-hidden">
        <Table>
          <TableHeader class="bg-[#F9FAFB]">
            <TableRow>
              <TableHead class="w-[80px] font-bold text-[#374151]">Kode</TableHead>
              <TableHead class="font-bold text-[#374151]">Nama Mata Kuliah</TableHead>
              <TableHead class="font-bold text-[#374151]">SKS (T/P)</TableHead>
              <TableHead class="font-bold text-[#374151]">Prodi</TableHead>
              <TableHead class="font-bold text-[#374151]">Semester</TableHead>
              <TableHead class="text-right font-bold text-[#374151]">Aksi</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="matkul in matkuls.data" :key="matkul.id" class="hover:bg-gray-50/50 transition-colors">
              <TableCell class="font-medium text-[#1F2937]">{{ matkul.kode }}</TableCell>
              <TableCell>
                <div>
                  <div class="font-semibold text-[#1F2937]">{{ matkul.nama_matkul }}</div>
                  <div class="text-xs text-[#6B7280] italic">{{ matkul.alias }}</div>
                </div>
              </TableCell>
              <TableCell>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700">
                  {{ matkul.teori + matkul.praktek }} SKS ({{ matkul.teori }}/{{ matkul.praktek }})
                </span>
              </TableCell>
              <TableCell class="text-[#4B5563]">{{ matkul.prodi.nama_prodi }}</TableCell>
              <TableCell>
                <span class="text-[#4B5563]">Semester {{ matkul.semester.semester }}</span>
              </TableCell>
              <TableCell class="text-right">
                <div class="flex justify-end gap-2">
                  <Button variant="ghost" size="sm" @click="openEditModal(matkul)" class="text-blue-600 hover:text-blue-700 hover:bg-blue-50 h-8 w-8 p-0">
                    <Pencil class="w-4 h-4" />
                  </Button>
                  <Button variant="ghost" size="sm" @click="confirmDelete(matkul)" class="text-red-600 hover:text-red-700 hover:bg-red-50 h-8 w-8 p-0">
                    <Trash2 class="w-4 h-4" />
                  </Button>
                </div>
              </TableCell>
            </TableRow>
            <TableRow v-if="matkuls.data.length === 0">
              <TableCell colspan="6" class="h-32 text-center text-[#9CA3AF]">
                <div class="flex flex-col items-center justify-center space-y-2">
                  <BookOpen class="w-8 h-8 opacity-20" />
                  <p>Tidak ada mata kuliah yang ditemukan.</p>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-100 flex items-center justify-between bg-white">
          <p class="text-sm text-[#6B7280]">
            Menampilkan {{ matkuls.from || 0 }} sampai {{ matkuls.to || 0 }} dari {{ matkuls.total }} data
          </p>
          <div class="flex items-center gap-2">
            <Button 
              variant="outline" 
              size="sm" 
              :disabled="!matkuls.prev_page_url"
              @click="router.get(matkuls.prev_page_url, {}, { preserveState: true })"
              class="rounded-lg h-8 w-8 p-0"
            >
              <ChevronLeft class="w-4 h-4" />
            </Button>
            <div class="flex items-center gap-1">
              <Button 
                v-for="page in matkuls.links.slice(1, -1)" 
                :key="page.label"
                variant="ghost"
                size="sm"
                @click="router.get(page.url, {}, { preserveState: true })"
                :class="[
                  'h-8 w-8 p-0 rounded-lg font-medium',
                  page.active ? 'bg-[#4B49AC] text-white hover:bg-[#3f3d91]' : 'text-[#4B5563] hover:bg-gray-100'
                ]"
              >
                {{ page.label }}
              </Button>
            </div>
            <Button 
              variant="outline" 
              size="sm" 
              :disabled="!matkuls.next_page_url"
              @click="router.get(matkuls.next_page_url, {}, { preserveState: true })"
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
      <SheetContent side="right" class="sm:max-w-[30%] w-full p-0 border-none shadow-2xl bg-white flex flex-col">
        <div class="bg-[#4B49AC] p-6 text-white shrink-0">
          <SheetHeader>
            <SheetTitle class="text-xl font-bold text-white">Tambah Mata Kuliah</SheetTitle>
            <SheetDescription class="text-indigo-100 mt-1">
              Lengkapi rincian mata kuliah baru di bawah ini.
            </SheetDescription>
          </SheetHeader>
        </div>

        <form @submit.prevent="submitAdd" class="flex flex-col h-full overflow-hidden">
          <div class="flex-1 overflow-y-auto p-6 space-y-6">
            <div class="space-y-4">
              <div class="space-y-2">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Mata Kuliah (Indonesia)</Label>
                <Input 
                  v-model="form.nama_matkul" 
                  placeholder="Misal: Pemrograman Web" 
                  class="h-11 border-gray-200 focus:border-[#4B49AC] focus:ring-[#4B49AC]/20 rounded-lg transition-all"
                  required 
                />
                <p v-if="form.errors.nama_matkul" class="text-xs text-red-500 font-medium">{{ form.errors.nama_matkul }}</p>
              </div>

              <div class="space-y-2">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Mata Kuliah (English)</Label>
                <Input 
                  v-model="form.alias" 
                  placeholder="e.g. Web Programming" 
                  class="h-11 border-gray-200 focus:border-[#4B49AC] focus:ring-[#4B49AC]/20 rounded-lg transition-all"
                  required 
                />
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Kode Matkul</Label>
                  <Input 
                    v-model="form.kode" 
                    placeholder="MK001" 
                    class="h-11 border-gray-200 focus:border-[#4B49AC] focus:ring-[#4B49AC]/20 rounded-lg transition-all"
                    required 
                  />
                  <p v-if="form.errors.kode" class="text-xs text-red-500 font-medium">{{ form.errors.kode }}</p>
                </div>

                <div class="space-y-2">
                  <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Semester</Label>
                  <Select v-model="form.semester_id">
                    <SelectTrigger class="h-11 border-gray-200 rounded-lg focus:ring-[#4B49AC]/20 transition-all">
                      <SelectValue placeholder="Pilih" />
                    </SelectTrigger>
                    <SelectContent class="rounded-lg border-gray-100 shadow-xl">
                      <SelectItem v-for="sem in semesters" :key="sem.id" :value="sem.id.toString()">
                        Smtr {{ sem.semester }}
                      </SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>

              <div class="space-y-2">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Program Studi</Label>
                <Select v-model="form.prodi_id">
                  <SelectTrigger class="h-11 border-gray-200 rounded-lg focus:ring-[#4B49AC]/20 transition-all">
                    <SelectValue placeholder="Pilih Prodi" />
                  </SelectTrigger>
                  <SelectContent class="rounded-lg border-gray-100 shadow-xl">
                    <SelectItem v-for="prodi in prodis" :key="prodi.id" :value="prodi.id.toString()">
                      {{ prodi.nama_prodi }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div class="p-4 bg-gray-50 rounded-xl space-y-4 border border-gray-100">
                <Label class="text-xs font-bold text-[#4B49AC] uppercase tracking-widest">Informasi SKS</Label>
                <div class="grid grid-cols-2 gap-4">
                  <div class="space-y-2">
                    <Label class="text-[10px] font-bold text-gray-400 uppercase">Teori</Label>
                    <Input 
                      type="number" 
                      v-model="form.teori" 
                      class="h-11 border-gray-200 bg-white focus:border-[#4B49AC] rounded-lg text-center font-bold" 
                    />
                  </div>
                  <div class="space-y-2">
                    <Label class="text-[10px] font-bold text-gray-400 uppercase">Praktek</Label>
                    <Input 
                      type="number" 
                      v-model="form.praktek" 
                      class="h-11 border-gray-200 bg-white focus:border-[#4B49AC] rounded-lg text-center font-bold" 
                    />
                  </div>
                </div>
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
            <SheetTitle class="text-xl font-bold text-white">Edit Mata Kuliah</SheetTitle>
            <SheetDescription class="text-indigo-100 mt-1">
              Perbarui informasi mata kuliah di bawah ini.
            </SheetDescription>
          </SheetHeader>
        </div>

        <form @submit.prevent="submitUpdate" class="flex flex-col h-full overflow-hidden">
          <div class="flex-1 overflow-y-auto p-6 space-y-6">
            <div class="space-y-4">
              <div class="space-y-2">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Mata Kuliah (Indonesia)</Label>
                <Input 
                  v-model="editForm.nama_matkul" 
                  class="h-11 border-gray-200 focus:border-[#4B49AC] focus:ring-[#4B49AC]/20 rounded-lg transition-all"
                  required 
                />
                <p v-if="editForm.errors.nama_matkul" class="text-xs text-red-500 font-medium">{{ editForm.errors.nama_matkul }}</p>
              </div>

              <div class="space-y-2">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Mata Kuliah (English)</Label>
                <Input 
                  v-model="editForm.alias" 
                  class="h-11 border-gray-200 focus:border-[#4B49AC] focus:ring-[#4B49AC]/20 rounded-lg transition-all"
                  required 
                />
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Kode Matkul</Label>
                  <Input 
                    v-model="editForm.kode" 
                    class="h-11 border-gray-200 focus:border-[#4B49AC] focus:ring-[#4B49AC]/20 rounded-lg transition-all"
                    required 
                  />
                  <p v-if="editForm.errors.kode" class="text-xs text-red-500 font-medium">{{ editForm.errors.kode }}</p>
                </div>

                <div class="space-y-2">
                  <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Semester</Label>
                  <Select v-model="editForm.semester_id">
                    <SelectTrigger class="h-11 border-gray-200 rounded-lg focus:ring-[#4B49AC]/20 transition-all">
                      <SelectValue placeholder="Pilih" />
                    </SelectTrigger>
                    <SelectContent class="rounded-lg border-gray-100 shadow-xl">
                      <SelectItem v-for="sem in semesters" :key="sem.id" :value="sem.id.toString()">
                        Smtr {{ sem.semester }}
                      </SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>

              <div class="space-y-2">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Program Studi</Label>
                <Select v-model="editForm.prodi_id">
                  <SelectTrigger class="h-11 border-gray-200 rounded-lg focus:ring-[#4B49AC]/20 transition-all">
                    <SelectValue placeholder="Pilih Prodi" />
                  </SelectTrigger>
                  <SelectContent class="rounded-lg border-gray-100 shadow-xl">
                    <SelectItem v-for="prodi in prodis" :key="prodi.id" :value="prodi.id.toString()">
                      {{ prodi.nama_prodi }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div class="p-4 bg-gray-50 rounded-xl space-y-4 border border-gray-100">
                <Label class="text-xs font-bold text-[#4B49AC] uppercase tracking-widest">Informasi SKS</Label>
                <div class="grid grid-cols-2 gap-4">
                  <div class="space-y-2">
                    <Label class="text-[10px] font-bold text-gray-400 uppercase">Teori</Label>
                    <Input 
                      type="number" 
                      v-model="editForm.teori" 
                      class="h-11 border-gray-200 bg-white focus:border-[#4B49AC] rounded-lg text-center font-bold" 
                    />
                  </div>
                  <div class="space-y-2">
                    <Label class="text-[10px] font-bold text-gray-400 uppercase">Praktek</Label>
                    <Input 
                      type="number" 
                      v-model="editForm.praktek" 
                      class="h-11 border-gray-200 bg-white focus:border-[#4B49AC] rounded-lg text-center font-bold" 
                    />
                  </div>
                </div>
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

    <Dialog :open="isDeleteModalOpen" @update:open="isDeleteModalOpen = $event">
      <DialogContent class="sm:max-w-[450px] p-0 overflow-hidden border-none shadow-2xl bg-white rounded-lg">
        <div class="p-8 text-center">
          <div class="w-20 h-20 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm">
            <AlertCircle class="w-10 h-10 text-danger" />
          </div>
          <DialogHeader>
            <DialogTitle class="text-2xl font-bold text-gray-800 text-center">Konfirmasi Hapus</DialogTitle>
            <DialogDescription class="text-gray-500 text-center mt-3 px-2 text-base">
              Apakah Anda yakin ingin menghapus mata kuliah <span class="text-danger font-bold">"{{ selectedMatkul?.nama_matkul }}"</span>? 
              <br><span class="text-xs mt-3 block italic text-gray-400 font-medium tracking-wide">Tindakan ini akan menghapus semua jadwal terkait secara permanen.</span>
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
