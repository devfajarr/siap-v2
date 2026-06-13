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
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/Components/ui/popover'
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
  History,
  Briefcase,
  Check,
  ChevronsUpDown
} from 'lucide-vue-next'

const props = defineProps({
  jabatans: Object,
  dosens: Array,
  pegawais: Array,
  existingJabatanDosenIds: Array,
  existingJabatanPegawaiIds: Array,
  filters: Object,
})

const page = usePage()

// Filters & Search
const search = ref(props.filters.search || '')

let filterTimeout = null
const updateFilters = () => {
  if (filterTimeout) clearTimeout(filterTimeout)
  filterTimeout = setTimeout(() => {
    router.get('/v2/admin/data-master/data-jabatan', {
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
const selectedJabatan = ref(null)

const openPopoverDosen = ref(false)
const openPopoverPegawai = ref(false)

const searchDosen = ref('')
const searchPegawai = ref('')

const filteredDosens = computed(() => {
  if (!searchDosen.value) {
    return props.dosens
  }
  const q = searchDosen.value.toLowerCase()
  return props.dosens.filter(d => 
    d.nama.toLowerCase().includes(q) || 
    (d.nidn && d.nidn.toLowerCase().includes(q))
  )
})

const filteredPegawais = computed(() => {
  if (!searchPegawai.value) {
    return props.pegawais
  }
  const q = searchPegawai.value.toLowerCase()
  return props.pegawais.filter(p => 
    p.nama.toLowerCase().includes(q) || 
    (p.nuptk && p.nuptk.toLowerCase().includes(q))
  )
})

watch(openPopoverDosen, (val) => {
  if (!val) {
    searchDosen.value = ''
  }
})

watch(openPopoverPegawai, (val) => {
  if (!val) {
    searchPegawai.value = ''
  }
})

const getDosenLabel = (dosenId) => {
  if (!dosenId) {
    return 'Pilih Dosen'
  }
  const d = props.dosens.find(x => String(x.id) === String(dosenId))
  if (!d) {
    return 'Pilih Dosen'
  }
  return `${d.nama} (${d.nidn || 'Tanpa NIDN'})`
}

const getPegawaiLabel = (pegawaiId) => {
  if (!pegawaiId) {
    return 'Pilih Pegawai / Staff'
  }
  const p = props.pegawais.find(x => String(x.id) === String(pegawaiId))
  if (!p) {
    return 'Pilih Pegawai / Staff'
  }
  return `${p.nama} (${p.nuptk || 'Tanpa NUPTK'})`
}

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
  user_type: 'dosen',
  dosens_id: '',
  pegawais_id: '',
  nama_jabatan: '',
  password_mode: 'base',
  password: ''
})

const editForm = useForm({
  id: null,
  status: 1,
  password: ''
})

// Check if selected person already has a structural role record to allow reusing credentials
const isAlreadyAssigned = computed(() => {
  if (form.user_type === 'dosen') {
    if (!form.dosens_id) return false
    return props.existingJabatanDosenIds.includes(Number(form.dosens_id))
  } else {
    if (!form.pegawais_id) return false
    return props.existingJabatanPegawaiIds.includes(Number(form.pegawais_id))
  }
})

// Helper to translate roles into readable titles
const formatRoleName = (role) => {
  const map = {
    bpmi: 'BPMI',
    kemahasiswaan: 'Kemahasiswaan',
    perpustakaan: 'Perpustakaan',
    sarpras: 'Sarpras',
    personalia: 'Personalia'
  }
  return map[role] || role.toUpperCase()
}

// Reset form when user type changes
watch(() => form.user_type, () => {
  form.dosens_id = ''
  form.pegawais_id = ''
  form.password_mode = 'base'
  openPopoverDosen.value = false
  openPopoverPegawai.value = false
  searchDosen.value = ''
  searchPegawai.value = ''
})

// Actions
const openEditModal = (jabatan) => {
  selectedJabatan.value = jabatan
  editForm.id = jabatan.id
  editForm.status = jabatan.status
  editForm.password = ''
  isEditModalOpen.value = true
}

const submitAdd = () => {
  form.post('/v2/admin/data-master/data-jabatan', {
    onSuccess: () => {
      isAddModalOpen.value = false
      form.reset()
    }
  })
}

const submitUpdate = () => {
  editForm.put(`/v2/admin/data-master/data-jabatan/${editForm.id}`, {
    onSuccess: () => {
      isEditModalOpen.value = false
      editForm.reset()
    }
  })
}

const confirmDelete = (jabatan) => {
  selectedJabatan.value = jabatan
  isDeleteModalOpen.value = true
}

const submitDelete = () => {
  router.delete(`/v2/admin/data-master/data-jabatan/${selectedJabatan.value.id}`, {
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
    <Head title="Kelola Jabatan Struktural" />

    <div class="space-y-6">
      <!-- Header Section -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-[#1F2937]">Kelola Jabatan Struktural</h1>
          <p class="text-[#6B7280]">Manajemen penugasan dosen dan pegawai pada unit kerja/struktural (BPMI, Sarpras, dsb.).</p>
        </div>
        <div class="flex items-center gap-2">
          <Button @click="isAddModalOpen = true" class="bg-[#4B49AC] hover:bg-[#3f3d91] text-white rounded-lg shadow-sm">
            <Plus class="w-4 h-4 mr-2" />
            Tambah Jabatan
          </Button>
        </div>
      </div>

      <!-- Filters Card -->
      <Card class="border-none shadow-sm">
        <CardContent class="p-4">
          <div class="flex flex-col md:flex-row gap-4 items-end">
            <div class="space-y-2 flex-1">
              <Label class="text-xs font-semibold text-[#374151]">Cari Data</Label>
              <div class="relative">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#9CA3AF]" />
                <Input 
                  v-model="search"
                  placeholder="Cari berdasarkan nama, email, atau jabatan..." 
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
              <TableHead class="font-bold text-[#374151]">Nama Pengguna / Email</TableHead>
              <TableHead class="font-bold text-[#374151]">Tipe Akun</TableHead>
              <TableHead class="font-bold text-[#374151]">Jabatan Struktural</TableHead>
              <TableHead class="font-bold text-[#374151]">WhatsApp</TableHead>
              <TableHead class="font-bold text-[#374151]">Status</TableHead>
              <TableHead class="text-right font-bold text-[#374151]">Aksi</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="jabatan in jabatans.data" :key="jabatan.id" class="hover:bg-gray-50/50 transition-colors">
              <TableCell>
                <div>
                  <div class="font-semibold text-[#1F2937]">
                    {{ jabatan.dosen?.nama ?? jabatan.pegawai?.nama ?? '-' }}
                  </div>
                  <div class="text-xs text-[#6B7280]">{{ jabatan.email }}</div>
                </div>
              </TableCell>
              <TableCell>
                <span :class="[
                  'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold',
                  jabatan.dosens_id ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700'
                ]">
                  {{ jabatan.dosens_id ? 'Dosen' : 'Pegawai/Staff' }}
                </span>
              </TableCell>
              <TableCell>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-50 text-purple-700 font-bold uppercase">
                  {{ formatRoleName(jabatan.nama_jabatan) }}
                </span>
              </TableCell>
              <TableCell class="text-[#4B5563]">
                {{ jabatan.dosen?.no_telephone ?? jabatan.pegawai?.no_telephone ?? '-' }}
              </TableCell>
              <TableCell>
                <span v-if="jabatan.status == 1" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700">
                  Aktif
                </span>
                <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700">
                  Nonaktif
                </span>
              </TableCell>
              <TableCell class="text-right">
                <div class="flex justify-end gap-2">
                  <Button variant="ghost" size="sm" @click="openEditModal(jabatan)" class="text-blue-600 hover:text-blue-700 hover:bg-blue-50 h-8 w-8 p-0">
                    <Pencil class="w-4 h-4" />
                  </Button>
                  <Button variant="ghost" size="sm" @click="confirmDelete(jabatan)" class="text-red-600 hover:text-red-700 hover:bg-red-50 h-8 w-8 p-0">
                    <Trash2 class="w-4 h-4" />
                  </Button>
                </div>
              </TableCell>
            </TableRow>
            <TableRow v-if="jabatans.data.length === 0">
              <TableCell colspan="6" class="h-32 text-center text-[#9CA3AF]">
                <div class="flex flex-col items-center justify-center space-y-2">
                  <Briefcase class="w-8 h-8 opacity-20" />
                  <p>Tidak ada data jabatan struktural yang ditemukan.</p>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-100 flex items-center justify-between bg-white">
          <p class="text-sm text-[#6B7280]">
            Menampilkan {{ jabatans.from || 0 }} sampai {{ jabatans.to || 0 }} dari {{ jabatans.total }} data
          </p>
          <div class="flex items-center gap-2">
            <Button 
              variant="outline" 
              size="sm" 
              :disabled="!jabatans.prev_page_url"
              @click="router.get(jabatans.prev_page_url, {}, { preserveState: true })"
              class="rounded-lg h-8 w-8 p-0"
            >
              <ChevronLeft class="w-4 h-4" />
            </Button>
            <div class="flex items-center gap-1">
              <Button 
                v-for="p in jabatans.links.slice(1, -1)" 
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
              :disabled="!jabatans.next_page_url"
              @click="router.get(jabatans.next_page_url, {}, { preserveState: true })"
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
            <SheetTitle class="text-xl font-bold text-white">Tambah Jabatan Struktural</SheetTitle>
            <SheetDescription class="text-indigo-100 mt-1">
              Pilih dosen/pegawai dan tentukan jabatan struktural yang akan ditugaskan.
            </SheetDescription>
          </SheetHeader>
        </div>

        <form @submit.prevent="submitAdd" class="flex flex-col h-full overflow-hidden">
          <div class="flex-1 overflow-y-auto p-6 space-y-6">
            <div class="space-y-4">
              
              <!-- Tipe Pengguna -->
              <div class="space-y-2">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Tipe Pengguna</Label>
                <Select v-model="form.user_type" required>
                  <SelectTrigger class="h-11 rounded-lg border-gray-200">
                    <SelectValue placeholder="Pilih tipe pengguna..." />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="dosen">Dosen</SelectItem>
                    <SelectItem value="pegawai">Pegawai / Staff</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <!-- Pilih Dosen -->
              <div v-if="form.user_type === 'dosen'" class="space-y-2">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Pilih Dosen</Label>
                <Popover :open="openPopoverDosen" @update:open="openPopoverDosen = $event">
                  <PopoverTrigger as-child>
                    <Button
                      type="button"
                      variant="outline"
                      role="combobox"
                      :aria-expanded="openPopoverDosen"
                      class="w-full justify-between h-11 border-gray-200 focus:border-[#4B49AC] focus:ring-[#4B49AC]/20 font-normal rounded-lg text-left bg-white px-3"
                      :class="!form.dosens_id ? 'text-gray-500' : 'text-gray-900'"
                    >
                      <span class="truncate">{{ getDosenLabel(form.dosens_id) }}</span>
                      <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50 text-gray-500" />
                    </Button>
                  </PopoverTrigger>
                  <PopoverContent class="w-[380px] p-0 bg-white border border-gray-200 shadow-lg z-50" align="start">
                    <div class="flex flex-col">
                      <div class="flex items-center border-b px-3 py-2 sticky top-0 bg-white z-10">
                        <Search class="mr-2 h-4 w-4 shrink-0 opacity-50 text-gray-500" />
                        <input
                          v-model="searchDosen"
                          type="text"
                          placeholder="Cari nama atau NIDN dosen..."
                          class="flex h-9 w-full rounded-md bg-transparent py-3 text-sm outline-none placeholder:text-muted-foreground disabled:cursor-not-allowed disabled:opacity-50"
                        />
                        <button
                          v-if="searchDosen"
                          type="button"
                          @click="searchDosen = ''"
                          class="ml-1 text-gray-400 hover:text-gray-600"
                        >
                          <X class="w-4 h-4" />
                        </button>
                      </div>
                      
                      <div class="max-h-[300px] overflow-y-auto p-1">
                        <div v-if="filteredDosens.length === 0" class="py-6 text-center text-sm text-gray-500">
                          Dosen tidak ditemukan.
                        </div>
                        <button
                          v-else
                          v-for="dosen in filteredDosens"
                          :key="dosen.id"
                          type="button"
                          @click="() => {
                            form.dosens_id = String(dosen.id)
                            openPopoverDosen = false
                            searchDosen = ''
                          }"
                          class="w-full text-left flex items-center px-3 py-2 text-sm rounded-md hover:bg-gray-100 transition-colors"
                          :class="form.dosens_id === String(dosen.id) ? 'bg-[#4b49ac]/10 text-[#4B49AC] font-semibold' : 'text-gray-700'"
                        >
                          <Check
                            :class="form.dosens_id === String(dosen.id) ? 'opacity-100' : 'opacity-0'"
                            class="mr-2 h-4 w-4 text-[#4B49AC] shrink-0"
                          />
                          <span class="truncate">{{ dosen.nama }} ({{ dosen.nidn || 'Tanpa NIDN' }})</span>
                        </button>
                      </div>
                    </div>
                  </PopoverContent>
                </Popover>
                <p v-if="form.errors.dosens_id" class="text-xs text-red-500 font-medium">{{ form.errors.dosens_id }}</p>
              </div>

              <!-- Pilih Pegawai -->
              <div v-else class="space-y-2">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Pilih Pegawai / Staff</Label>
                <Popover :open="openPopoverPegawai" @update:open="openPopoverPegawai = $event">
                  <PopoverTrigger as-child>
                    <Button
                      type="button"
                      variant="outline"
                      role="combobox"
                      :aria-expanded="openPopoverPegawai"
                      class="w-full justify-between h-11 border-gray-200 focus:border-[#4B49AC] focus:ring-[#4B49AC]/20 font-normal rounded-lg text-left bg-white px-3"
                      :class="!form.pegawais_id ? 'text-gray-500' : 'text-gray-900'"
                    >
                      <span class="truncate">{{ getPegawaiLabel(form.pegawais_id) }}</span>
                      <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50 text-gray-500" />
                    </Button>
                  </PopoverTrigger>
                  <PopoverContent class="w-[380px] p-0 bg-white border border-gray-200 shadow-lg z-50" align="start">
                    <div class="flex flex-col">
                      <div class="flex items-center border-b px-3 py-2 sticky top-0 bg-white z-10">
                        <Search class="mr-2 h-4 w-4 shrink-0 opacity-50 text-gray-500" />
                        <input
                          v-model="searchPegawai"
                          type="text"
                          placeholder="Cari nama atau NUPTK pegawai..."
                          class="flex h-9 w-full rounded-md bg-transparent py-3 text-sm outline-none placeholder:text-muted-foreground disabled:cursor-not-allowed disabled:opacity-50"
                        />
                        <button
                          v-if="searchPegawai"
                          type="button"
                          @click="searchPegawai = ''"
                          class="ml-1 text-gray-400 hover:text-gray-600"
                        >
                          <X class="w-4 h-4" />
                        </button>
                      </div>
                      
                      <div class="max-h-[300px] overflow-y-auto p-1">
                        <div v-if="filteredPegawais.length === 0" class="py-6 text-center text-sm text-gray-500">
                          Pegawai tidak ditemukan.
                        </div>
                        <button
                          v-else
                          v-for="pegawai in filteredPegawais"
                          :key="pegawai.id"
                          type="button"
                          @click="() => {
                            form.pegawais_id = String(pegawai.id)
                            openPopoverPegawai = false
                            searchPegawai = ''
                          }"
                          class="w-full text-left flex items-center px-3 py-2 text-sm rounded-md hover:bg-gray-100 transition-colors"
                          :class="form.pegawais_id === String(pegawai.id) ? 'bg-[#4b49ac]/10 text-[#4B49AC] font-semibold' : 'text-gray-700'"
                        >
                          <Check
                            :class="form.pegawais_id === String(pegawai.id) ? 'opacity-100' : 'opacity-0'"
                            class="mr-2 h-4 w-4 text-[#4B49AC] shrink-0"
                          />
                          <span class="truncate">{{ pegawai.nama }} ({{ pegawai.nuptk || 'Tanpa NUPTK' }})</span>
                        </button>
                      </div>
                    </div>
                  </PopoverContent>
                </Popover>
                <p v-if="form.errors.pegawais_id" class="text-xs text-red-500 font-medium">{{ form.errors.pegawais_id }}</p>
              </div>

              <!-- Pilih Jabatan -->
              <div class="space-y-2">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Jabatan Struktural</Label>
                <Select v-model="form.nama_jabatan" required>
                  <SelectTrigger class="h-11 rounded-lg border-gray-200">
                    <SelectValue placeholder="Klik untuk memilih jabatan..." />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="bpmi">BPMI</SelectItem>
                    <SelectItem value="kemahasiswaan">Kemahasiswaan</SelectItem>
                    <SelectItem value="perpustakaan">Perpustakaan</SelectItem>
                    <SelectItem value="sarpras">Sarpras</SelectItem>
                    <SelectItem value="personalia">Personalia</SelectItem>
                  </SelectContent>
                </Select>
                <p v-if="form.errors.nama_jabatan" class="text-xs text-red-500 font-medium">{{ form.errors.nama_jabatan }}</p>
              </div>

              <!-- Password Mode Selection -->
              <div class="space-y-4 pt-4 border-t border-gray-100">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Keamanan & Password Login</Label>
                
                <div class="grid grid-cols-1 gap-3">
                  <!-- Mode: Base Login -->
                  <div 
                    @click="form.password_mode = 'base'"
                    class="p-3 border rounded-xl cursor-pointer transition-all flex items-center gap-3"
                    :class="form.password_mode === 'base' ? 'border-[#4B49AC] bg-indigo-50/50 ring-1 ring-[#4B49AC]' : 'border-gray-200 hover:border-indigo-200'"
                  >
                    <div class="w-8 h-8 rounded-full bg-white shadow-sm flex items-center justify-center text-[#4B49AC]">
                      <ShieldCheck class="w-4 h-4" />
                    </div>
                    <div class="flex-1">
                      <p class="text-xs font-bold text-[#1F2937]">Gunakan Password Login Akun Utama</p>
                      <p class="text-[10px] text-gray-500 italic">Sinkron dengan akun dosen/pegawai dasar.</p>
                    </div>
                    <CheckCircle2 v-if="form.password_mode === 'base'" class="w-4 h-4 text-[#4B49AC]" />
                  </div>

                  <!-- Mode: Existing Jabatan -->
                  <div 
                    v-if="isAlreadyAssigned"
                    @click="form.password_mode = 'existing'"
                    class="p-3 border rounded-xl cursor-pointer transition-all flex items-center gap-3 animate-in slide-in-from-left duration-300"
                    :class="form.password_mode === 'existing' ? 'border-[#4B49AC] bg-indigo-50/50 ring-1 ring-[#4B49AC]' : 'border-gray-200 hover:border-indigo-200'"
                  >
                    <div class="w-8 h-8 rounded-full bg-white shadow-sm flex items-center justify-center text-indigo-600">
                      <History class="w-4 h-4" />
                    </div>
                    <div class="flex-1">
                      <p class="text-xs font-bold text-[#1F2937]">Gunakan Password Jabatan Sebelumnya</p>
                      <p class="text-[10px] text-gray-500 italic">Menggunakan password struktural yang sudah didaftarkan.</p>
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
                      <p class="text-[10px] text-gray-500 italic">Buat password terpisah untuk jabatan ini.</p>
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
                :disabled="form.processing" 
                class="h-11 px-8 bg-[#4B49AC] hover:bg-[#3f3d91] text-white rounded-lg shadow-lg font-semibold transition-all disabled:opacity-50"
              >
                <Loader2 v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
                Simpan Jabatan
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
            <SheetTitle class="text-xl font-bold text-white">Edit Jabatan Struktural</SheetTitle>
            <SheetDescription class="text-indigo-100 mt-1">
              Perbarui status keaktifan atau ubah password untuk penugasan ini.
            </SheetDescription>
          </SheetHeader>
        </div>

        <form @submit.prevent="submitUpdate" class="flex flex-col h-full overflow-hidden">
          <div class="flex-1 overflow-y-auto p-6 space-y-6">
            <div class="space-y-4">
              
              <!-- Detail Penugasan (ReadOnly) -->
              <div class="p-4 bg-gray-50 rounded-xl space-y-2 border border-gray-100">
                <div class="text-xs text-gray-400 font-bold uppercase tracking-wider">Penerima Jabatan</div>
                <div class="font-bold text-[#1F2937]">
                  {{ selectedJabatan?.dosen?.nama ?? selectedJabatan?.pegawai?.nama ?? '-' }}
                </div>
                <div class="text-xs text-[#6B7280]">{{ selectedJabatan?.email }}</div>
                <div class="pt-2 flex items-center gap-2">
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-700 uppercase">
                    {{ selectedJabatan ? formatRoleName(selectedJabatan.nama_jabatan) : '' }}
                  </span>
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-200 text-gray-700">
                    {{ selectedJabatan?.dosens_id ? 'Dosen' : 'Pegawai' }}
                  </span>
                </div>
              </div>

              <!-- Status Keaktifan -->
              <div class="space-y-2">
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

              <!-- Ubah Password -->
              <div class="space-y-2 pt-4 border-t border-gray-100">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Ubah Password Login (Sinkron Semua Jabatan)</Label>
                <Input v-model="editForm.password" type="password" placeholder="Kosongkan jika tidak ingin diubah" class="h-11 rounded-lg border-gray-200" />
                <p v-if="editForm.errors.password" class="text-xs text-red-500 font-medium">{{ editForm.errors.password }}</p>
                <p class="text-[10px] text-gray-400 italic">Mengubah password di sini akan menyelaraskan password login semua jabatan struktural orang tersebut.</p>
              </div>
            </div>
          </div>

          <div class="p-6 border-t border-gray-100 bg-gray-50/50 shrink-0">
            <SheetFooter class="flex flex-row items-center justify-end gap-3">
              <Button type="button" variant="ghost" @click="isEditModalOpen = false" class="h-11 px-6 rounded-lg font-semibold text-gray-500">Batal</Button>
              <Button 
                type="submit" 
                :disabled="editForm.processing" 
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
              Apakah Anda yakin ingin menghapus penugasan struktural <span class="text-purple-600 font-bold">"{{ selectedJabatan ? formatRoleName(selectedJabatan.nama_jabatan) : '' }}"</span> untuk <span class="text-danger font-bold">"{{ selectedJabatan?.dosen?.nama ?? selectedJabatan?.pegawai?.nama }}"</span>? 
              <br><span class="text-xs mt-3 block italic text-gray-400 font-medium tracking-wide">Tindakan ini hanya menghapus akses jabatan strukturalnya, data profil dosen/pegawai dasar tetap aman.</span>
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
