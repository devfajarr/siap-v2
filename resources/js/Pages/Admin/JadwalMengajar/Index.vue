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
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/Components/ui/popover'
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
  CommandList,
} from '@/Components/ui/command'
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
  DialogHeader,
  DialogTitle,
} from '@/Components/ui/dialog'
import { 
  Plus, 
  Search, 
  Pencil, 
  Trash2, 
  ChevronLeft,
  ChevronRight,
  CalendarDays,
  X,
  AlertCircle,
  Loader2,
  CheckCircle2,
  Check,
  ChevronsUpDown,
  XCircle,
  Clock,
  User,
  MapPin,
  BookOpen
} from 'lucide-vue-next'
import { Alert, AlertDescription, AlertTitle } from '@/Components/ui/alert'

const props = defineProps({
  jadwals: Object,
  filters: Object,
  dosens: Array,
  kelass: Array,
  matkuls: Array,
  ruangans: Array,
  tahun_akademik: String,
  is_tahun_akademik_active: Boolean,
  is_semester_active: Boolean,
})

const page = usePage()

// Filters & Search
const search = ref(props.filters.search || '')
const filterDosen = ref(props.filters.dosen_id || 'all')
const filterKelas = ref(props.filters.kelas_id || 'all')
const filterHari = ref(props.filters.hari || 'all')

let filterTimeout = null
const updateFilters = () => {
  if (filterTimeout) clearTimeout(filterTimeout)
  filterTimeout = setTimeout(() => {
    router.get('/v2/admin/jadwal-mengajar', {
      search: search.value,
      dosen_id: filterDosen.value === 'all' ? '' : filterDosen.value,
      kelas_id: filterKelas.value === 'all' ? '' : filterKelas.value,
      hari: filterHari.value === 'all' ? '' : filterHari.value,
    }, {
      preserveState: true,
      replace: true
    })
  }, 300)
}

watch([search, filterDosen, filterKelas, filterHari], () => {
  updateFilters()
})

const clearFilters = () => {
  search.value = ''
  filterDosen.value = 'all'
  filterKelas.value = 'all'
  filterHari.value = 'all'
}

// Modals State
const isAddModalOpen = ref(false)
const isEditModalOpen = ref(false)
const isDeleteModalOpen = ref(false)
const selectedJadwal = ref(null)

// Toast state
const showToast = ref(false)
const toastMessage = ref('')
const toastType = ref('success')

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
  kelas_id: '',
  matkul_id: '',
  dosen_id: '',
  ruangan_id: '',
  hari: '',
  jam_mulai: '',
  jam_selesai: '',
  tahun: props.tahun_akademik || ''
})

const editForm = useForm({
  id: null,
  kelas_id: '',
  matkul_id: '',
  dosen_id: '',
  ruangan_id: '',
  hari: '',
  jam_mulai: '',
  jam_selesai: '',
  tahun: ''
})


const openPopoverKelasAdd = ref(false)
const openPopoverMatkulAdd = ref(false)
const openPopoverKelasEdit = ref(false)
const openPopoverMatkulEdit = ref(false)

const getKelasLabel = (kelasId) => {
  if (!kelasId) return 'Pilih Kelas'
  const k = props.kelass.find(x => String(x.id) === String(kelasId))
  if (!k) return 'Pilih Kelas'
  return `${k.nama_kelas} (Smt ${k.semester?.semester || '-'} - ${k.prodi?.singkatan || '-'})`
}

const getMatkulLabel = (matkulId) => {
  if (!matkulId) return 'Pilih Mata Kuliah'
  const m = props.matkuls.find(x => String(x.id) === String(matkulId))
  if (!m) return 'Pilih Mata Kuliah'
  // In JadwalMengajar we might have teo/pra or SKS. Let's just output nama & kode if available
  return `${m.nama_matkul} (${m.kode || m.kode_matkul || '-'})`
}

// Dependent Dropdown Logic (Kelas -> Matkul -> SKS)
const availableMatkuls = computed(() => {
  const targetForm = isEditModalOpen.value ? editForm : form
  if (!targetForm.kelas_id) return []
  
  const selectedKelasData = props.kelass.find(k => String(k.id) === String(targetForm.kelas_id))
  if (!selectedKelasData) return []
  
  return props.matkuls.filter(m => 
    String(m.semester_id) === String(selectedKelasData.id_semester) && 
    String(m.prodi_id) === String(selectedKelasData.id_prodi)
  )
})

const selectedSks = computed(() => {
  const targetForm = isEditModalOpen.value ? editForm : form
  if (!targetForm.matkul_id) return 0
  
  const matkul = props.matkuls.find(m => String(m.id) === String(targetForm.matkul_id))
  if (!matkul) return 0
  
  return (parseInt(matkul.teori) || 0) + (parseInt(matkul.praktek) || 0)
})

watch(() => form.kelas_id, (newVal, oldVal) => {
  if (oldVal) {
    form.matkul_id = '' // Reset matkul if kelas changes
  }
})

watch(() => editForm.kelas_id, (newVal, oldVal) => {
  // Only reset if it's a real user change, not during modal open initialization
  if (oldVal && isEditModalOpen.value && oldVal !== newVal) {
    editForm.matkul_id = ''
  }
})

// Actions
const openEditModal = (jadwal) => {
  selectedJadwal.value = jadwal
  editForm.id = jadwal.id
  editForm.kelas_id = String(jadwal.kelas_id)
  
  // Need to set matkul_id after availableMatkuls updates
  setTimeout(() => {
    editForm.matkul_id = String(jadwal.matkuls_id)
  }, 50)
  
  editForm.dosen_id = String(jadwal.dosens_id)
  editForm.ruangan_id = String(jadwal.ruangans_id)
  editForm.hari = jadwal.hari
  editForm.jam_mulai = jadwal.waktu_mulai.substring(0, 5) // "08:00:00" -> "08:00"
  editForm.jam_selesai = jadwal.waktu_selesai.substring(0, 5)
  editForm.tahun = jadwal.tahun
  isEditModalOpen.value = true
}

const submitAdd = () => {
  form.post('/v2/admin/jadwal-mengajar', {
    onSuccess: () => {
      isAddModalOpen.value = false
      form.reset()
      form.tahun = props.tahun_akademik // reset back to active year
    }
  })
}

const submitUpdate = () => {
  editForm.put(`/v2/admin/jadwal-mengajar/${editForm.id}`, {
    onSuccess: () => {
      isEditModalOpen.value = false
      editForm.reset()
    }
  })
}

const confirmDelete = (jadwal) => {
  selectedJadwal.value = jadwal
  isDeleteModalOpen.value = true
}

const submitDelete = () => {
  router.delete(`/v2/admin/jadwal-mengajar/${selectedJadwal.value.id}`, {
    onSuccess: () => {
      isDeleteModalOpen.value = false
    }
  })
}

const formatTime = (time) => {
  if (!time) return '-'
  return time.substring(0, 5)
}
</script>

<template>
  <AdminLayout>
    <Head title="Jadwal Mengajar" />

    <div class="space-y-6">
      <!-- Warnings -->
      <Alert v-if="!is_tahun_akademik_active" variant="destructive" class="border-red-500 bg-red-50 text-red-700">
        <AlertCircle class="h-4 w-4" />
        <AlertTitle>Perhatian!</AlertTitle>
        <AlertDescription class="flex flex-col gap-3 mt-2">
          <span>Tidak ada Tahun Akademik yang berstatus Aktif. Anda tidak dapat menambahkan jadwal baru sampai ada Tahun Akademik yang diaktifkan.</span>
          <Button variant="outline" size="sm" class="w-fit border-red-300 bg-white text-red-700 hover:bg-red-50 hover:text-red-800" @click="router.get('/v2/admin/data-master/tahun-akademik')">
            Atur Tahun Akademik
          </Button>
        </AlertDescription>
      </Alert>

      <Alert v-if="!is_semester_active && is_tahun_akademik_active" variant="destructive" class="border-amber-500 bg-amber-50 text-amber-700">
        <AlertCircle class="h-4 w-4" />
        <AlertTitle>Peringatan!</AlertTitle>
        <AlertDescription class="flex flex-col gap-3 mt-2">
          <span>Tidak ada Semester yang berstatus Aktif. Data Kelas tidak akan muncul di form penambahan jadwal.</span>
          <Button variant="outline" size="sm" class="w-fit border-amber-300 bg-white text-amber-700 hover:bg-amber-50 hover:text-amber-800" @click="router.get('/v2/admin/data-master/data-semester')">
            Atur Semester Aktif
          </Button>
        </AlertDescription>
      </Alert>

      <!-- Header Section -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-[#1F2937]">Jadwal Mengajar</h1>
          <p class="text-[#6B7280]">Kelola alokasi jadwal perkuliahan dosen dan kelas.</p>
        </div>
        <Button 
          @click="isAddModalOpen = true" 
          :disabled="!is_tahun_akademik_active || !is_semester_active"
          class="bg-[#4B49AC] hover:bg-[#3f3d91] text-white rounded-lg shadow-sm"
        >
          <Plus class="w-4 h-4 mr-2" />
          Tambah Jadwal
        </Button>
      </div>

      <!-- Filters Card -->
      <Card class="border-none shadow-sm">
        <CardContent class="p-4">
          <div class="flex flex-col md:flex-row gap-4 items-end">
            <div class="space-y-2 flex-1 min-w-[200px]">
              <Label class="text-xs font-semibold text-[#374151]">Cari Mata Kuliah</Label>
              <div class="relative">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#9CA3AF]" />
                <Input 
                  v-model="search"
                  placeholder="Ketik nama matkul..." 
                  class="pl-10 border-gray-200 focus:ring-[#4B49AC] focus:border-[#4B49AC] rounded-lg"
                />
              </div>
            </div>

            <div class="space-y-2 w-full md:w-[200px]">
              <Label class="text-xs font-semibold text-[#374151]">Dosen</Label>
              <Select v-model="filterDosen">
                <SelectTrigger class="border-gray-200 focus:ring-[#4B49AC] rounded-lg bg-white">
                  <SelectValue placeholder="Semua Dosen" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">Semua Dosen</SelectItem>
                  <SelectItem v-for="dosen in dosens" :key="dosen.id" :value="String(dosen.id)">
                    {{ dosen.nama }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="space-y-2 w-full md:w-[200px]">
              <Label class="text-xs font-semibold text-[#374151]">Kelas</Label>
              <Select v-model="filterKelas">
                <SelectTrigger class="border-gray-200 focus:ring-[#4B49AC] rounded-lg bg-white">
                  <SelectValue placeholder="Semua Kelas" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">Semua Kelas</SelectItem>
                  <SelectItem v-for="kelas in kelass" :key="kelas.id" :value="String(kelas.id)">
                    {{ kelas.nama_kelas }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="space-y-2 w-full md:w-[150px]">
              <Label class="text-xs font-semibold text-[#374151]">Hari</Label>
              <Select v-model="filterHari">
                <SelectTrigger class="border-gray-200 focus:ring-[#4B49AC] rounded-lg bg-white">
                  <SelectValue placeholder="Semua Hari" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">Semua Hari</SelectItem>
                  <SelectItem value="Senin">Senin</SelectItem>
                  <SelectItem value="Selasa">Selasa</SelectItem>
                  <SelectItem value="Rabu">Rabu</SelectItem>
                  <SelectItem value="Kamis">Kamis</SelectItem>
                  <SelectItem value="Jumat">Jumat</SelectItem>
                  <SelectItem value="Sabtu">Sabtu</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <Button variant="outline" @click="clearFilters" class="border-gray-200 text-[#4B5563] hover:bg-gray-50 rounded-lg h-10 shrink-0">
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
              <TableHead class="font-bold text-[#374151] w-[250px]">Mata Kuliah</TableHead>
              <TableHead class="font-bold text-[#374151]">Dosen</TableHead>
              <TableHead class="font-bold text-[#374151]">Ruangan</TableHead>
              <TableHead class="font-bold text-[#374151]">Kelas</TableHead>
              <TableHead class="font-bold text-[#374151]">Waktu</TableHead>
              <TableHead class="text-right font-bold text-[#374151]">Aksi</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="jadwal in jadwals.data" :key="jadwal.id" class="hover:bg-gray-50/50 transition-colors">
              <TableCell>
                <div class="font-semibold text-[#1F2937]">{{ jadwal.matkul?.nama_matkul }}</div>
                <div class="text-xs text-gray-500 mt-1 flex items-center">
                  <BookOpen class="w-3 h-3 mr-1" />
                  SKS: {{ parseInt(jadwal.matkul?.teori || 0) + parseInt(jadwal.matkul?.praktek || 0) }} | Smt: {{ jadwal.matkul?.semester?.semester }}
                </div>
              </TableCell>
              <TableCell>
                <div class="flex items-center text-[#4B5563]">
                  <User class="w-4 h-4 mr-2 text-gray-400" />
                  {{ jadwal.dosen?.nama || '-' }}
                </div>
              </TableCell>
              <TableCell>
                <div class="flex items-center text-[#4B5563]">
                  <MapPin class="w-4 h-4 mr-2 text-gray-400" />
                  {{ jadwal.ruangan?.nama || '-' }}
                </div>
              </TableCell>
              <TableCell>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-[#4B49AC]">
                  {{ jadwal.kelas?.nama_kelas || '-' }}
                </span>
              </TableCell>
              <TableCell>
                <div class="flex flex-col gap-1">
                  <div class="font-medium text-gray-900">{{ jadwal.hari }}</div>
                  <div class="flex items-center text-xs text-gray-500">
                    <Clock class="w-3 h-3 mr-1" />
                    {{ formatTime(jadwal.waktu_mulai) }} - {{ formatTime(jadwal.waktu_selesai) }}
                  </div>
                </div>
              </TableCell>
              <TableCell class="text-right">
                <div class="flex justify-end gap-2">
                  <Button variant="ghost" size="sm" @click="openEditModal(jadwal)" class="text-blue-600 hover:text-blue-700 hover:bg-blue-50 h-8 w-8 p-0">
                    <Pencil class="w-4 h-4" />
                  </Button>
                  <Button variant="ghost" size="sm" @click="confirmDelete(jadwal)" class="text-red-600 hover:text-red-700 hover:bg-red-50 h-8 w-8 p-0">
                    <Trash2 class="w-4 h-4" />
                  </Button>
                </div>
              </TableCell>
            </TableRow>
            <TableRow v-if="jadwals.data.length === 0">
              <TableCell colspan="6" class="h-32 text-center text-[#9CA3AF]">
                <div class="flex flex-col items-center justify-center space-y-2">
                  <CalendarDays class="w-8 h-8 opacity-20" />
                  <p>Tidak ada jadwal yang ditemukan.</p>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-100 flex items-center justify-between bg-white">
          <p class="text-sm text-[#6B7280]">
            Menampilkan {{ jadwals.from || 0 }} sampai {{ jadwals.to || 0 }} dari {{ jadwals.total }} data
          </p>
          <div class="flex items-center gap-2">
            <Button 
              variant="outline" 
              size="sm" 
              :disabled="!jadwals.prev_page_url"
              @click="router.get(jadwals.prev_page_url, {}, { preserveState: true })"
              class="rounded-lg h-8 w-8 p-0"
            >
              <ChevronLeft class="w-4 h-4" />
            </Button>
            <div class="flex items-center gap-1">
              <Button 
                v-for="page in jadwals.links.slice(1, -1)" 
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
              :disabled="!jadwals.next_page_url"
              @click="router.get(jadwals.next_page_url, {}, { preserveState: true })"
              class="rounded-lg h-8 w-8 p-0"
            >
              <ChevronRight class="w-4 h-4" />
            </Button>
          </div>
        </div>
      </Card>
    </div>

    <!-- Add Drawer (Sheet) -->
    <Sheet :open="isAddModalOpen" @update:open="isAddModalOpen = $event">
      <SheetContent side="right" class="sm:max-w-[30%] w-full p-0 border-none shadow-2xl bg-white flex flex-col">
        <div class="bg-[#4B49AC] p-6 text-white shrink-0">
          <SheetHeader>
            <SheetTitle class="text-xl font-bold text-white">Tambah Jadwal Baru</SheetTitle>
            <SheetDescription class="text-indigo-100 mt-1">
              Pilih kelas, matkul, dosen, dan tentukan waktu perkuliahan.
            </SheetDescription>
          </SheetHeader>
        </div>

        <form @submit.prevent="submitAdd" class="flex flex-col h-full overflow-hidden">
          <div class="flex-1 overflow-y-auto p-6 space-y-5">
            <div class="space-y-2">
              <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Tahun Akademik</Label>
              <Input v-model="form.tahun" disabled class="bg-gray-50 border-gray-200" />
              <p class="text-[10px] text-gray-400">Tahun akademik aktif saat ini.</p>
            </div>

            <div class="space-y-2">
              <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Kelas</Label>
              <Popover :open="openPopoverKelasAdd" @update:open="openPopoverKelasAdd = $event">
                  <PopoverTrigger as-child>
                    <Button
                      variant="outline"
                      role="combobox"
                      :aria-expanded="openPopoverKelasAdd"
                      class="w-full justify-between h-11 border-gray-200 focus:border-[#4B49AC] focus:ring-[#4B49AC]/20 font-normal"
                      :class="!form.kelas_id ? 'text-gray-500' : 'text-gray-900'"
                    >
                      {{ getKelasLabel(form.kelas_id) }}
                      <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                    </Button>
                  </PopoverTrigger>
                  <PopoverContent class="w-[380px] p-0 bg-white border border-gray-200 shadow-lg z-50" align="start">
                    <Command>
                      <CommandInput placeholder="Cari kelas..." />
                      <CommandEmpty>Kelas tidak ditemukan.</CommandEmpty>
                      <CommandList>
                        <CommandGroup>
                          <CommandItem
                            v-for="kelas in kelass"
                            :key="kelas.id"
                            :value="`${kelas.nama_kelas} Smt ${kelas.semester?.semester} ${kelas.prodi?.singkatan}`"
                            @select="() => {
                              form.kelas_id = String(kelas.id)
                              openPopoverKelasAdd = false
                            }"
                          >
                            <Check
                              :class="form.kelas_id === String(kelas.id) ? 'opacity-100' : 'opacity-0'"
                              class="mr-2 h-4 w-4"
                            />
                            {{ kelas.nama_kelas }} (Smt {{ kelas.semester?.semester }} - {{ kelas.prodi?.singkatan }})
                          </CommandItem>
                        </CommandGroup>
                      </CommandList>
                    </Command>
                  </PopoverContent>
                </Popover>
              <p v-if="form.errors.kelas_id" class="text-xs text-red-500 font-medium">{{ form.errors.kelas_id }}</p>
            </div>

            <div class="space-y-2">
              <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Mata Kuliah</Label>
              <Popover :open="openPopoverMatkulAdd" @update:open="openPopoverMatkulAdd = $event">
                  <PopoverTrigger as-child>
                    <Button
                      variant="outline"
                      role="combobox"
                      :aria-expanded="openPopoverMatkulAdd"
                      :disabled="!form.kelas_id || availableMatkuls.length === 0"
                      class="w-full justify-between h-11 border-gray-200 focus:border-[#4B49AC] focus:ring-[#4B49AC]/20 font-normal"
                      :class="!form.kelas_id ? 'bg-gray-50 text-gray-400' : (!form.matkul_id ? 'text-gray-500' : 'text-gray-900')"
                    >
                      {{ !form.kelas_id ? 'Pilih Kelas Dahulu' : getMatkulLabel(form.matkul_id) }}
                      <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                    </Button>
                  </PopoverTrigger>
                  <PopoverContent class="w-[380px] p-0 bg-white border border-gray-200 shadow-lg z-50" align="start">
                    <Command>
                      <CommandInput placeholder="Cari mata kuliah..." />
                      <CommandEmpty>Mata kuliah tidak ditemukan.</CommandEmpty>
                      <CommandList>
                        <CommandGroup>
                          <CommandItem
                            v-for="matkul in availableMatkuls"
                            :key="matkul.id"
                            :value="`${matkul.nama_matkul} ${matkul.kode || matkul.kode_matkul}`"
                            @select="() => {
                              form.matkul_id = String(matkul.id)
                              openPopoverMatkulAdd = false
                            }"
                          >
                            <Check
                              :class="form.matkul_id === String(matkul.id) ? 'opacity-100' : 'opacity-0'"
                              class="mr-2 h-4 w-4"
                            />
                            {{ matkul.nama_matkul }} ({{ matkul.kode || matkul.kode_matkul }})
                          </CommandItem>
                        </CommandGroup>
                      </CommandList>
                    </Command>
                  </PopoverContent>
                </Popover>
              <p v-if="!form.kelas_id" class="text-[10px] text-amber-500">Pilih kelas terlebih dahulu.</p>
              <p v-if="form.kelas_id && availableMatkuls.length === 0" class="text-[10px] text-red-500">Tidak ada matkul untuk semester/prodi kelas ini.</p>
              <p v-if="form.errors.matkul_id" class="text-xs text-red-500 font-medium">{{ form.errors.matkul_id }}</p>
            </div>

            <div class="space-y-2">
              <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">SKS</Label>
              <Input :value="selectedSks" disabled class="bg-gray-50 border-gray-200 h-11" />
            </div>

            <div class="space-y-2">
              <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Dosen</Label>
              <Select v-model="form.dosen_id">
                <SelectTrigger class="h-11 border-gray-200 focus:ring-[#4B49AC]/20 rounded-lg">
                  <SelectValue placeholder="Pilih Dosen" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="dosen in dosens" :key="dosen.id" :value="String(dosen.id)">
                    {{ dosen.nama }}
                  </SelectItem>
                </SelectContent>
              </Select>
              <p v-if="form.errors.dosen_id" class="text-xs text-red-500 font-medium">{{ form.errors.dosen_id }}</p>
            </div>

            <div class="space-y-2">
              <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Ruangan</Label>
              <Select v-model="form.ruangan_id">
                <SelectTrigger class="h-11 border-gray-200 focus:ring-[#4B49AC]/20 rounded-lg">
                  <SelectValue placeholder="Pilih Ruangan" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="ruangan in ruangans" :key="ruangan.id" :value="String(ruangan.id)">
                    {{ ruangan.nama }}
                  </SelectItem>
                </SelectContent>
              </Select>
              <p v-if="form.errors.ruangan_id" class="text-xs text-red-500 font-medium">{{ form.errors.ruangan_id }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="col-span-2 space-y-2">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Hari</Label>
                <Select v-model="form.hari">
                  <SelectTrigger class="h-11 border-gray-200 focus:ring-[#4B49AC]/20 rounded-lg">
                    <SelectValue placeholder="Pilih Hari" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="Senin">Senin</SelectItem>
                    <SelectItem value="Selasa">Selasa</SelectItem>
                    <SelectItem value="Rabu">Rabu</SelectItem>
                    <SelectItem value="Kamis">Kamis</SelectItem>
                    <SelectItem value="Jumat">Jumat</SelectItem>
                    <SelectItem value="Sabtu">Sabtu</SelectItem>
                  </SelectContent>
                </Select>
                <p v-if="form.errors.hari" class="text-xs text-red-500 font-medium">{{ form.errors.hari }}</p>
              </div>

              <div class="space-y-2">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Jam Mulai</Label>
                <Input type="time" v-model="form.jam_mulai" class="h-11 border-gray-200 rounded-lg" required />
                <p v-if="form.errors.jam_mulai" class="text-xs text-red-500 font-medium">{{ form.errors.jam_mulai }}</p>
              </div>

              <div class="space-y-2">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Jam Selesai</Label>
                <Input type="time" v-model="form.jam_selesai" class="h-11 border-gray-200 rounded-lg" required />
                <p v-if="form.errors.jam_selesai" class="text-xs text-red-500 font-medium">{{ form.errors.jam_selesai }}</p>
              </div>
            </div>
            
            <p v-if="form.errors.tahun" class="text-xs text-red-500 font-medium text-center bg-red-50 p-2 rounded">{{ form.errors.tahun }}</p>
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

    <!-- Edit Drawer (Sheet) -->
    <Sheet :open="isEditModalOpen" @update:open="isEditModalOpen = $event">
      <SheetContent side="right" class="sm:max-w-[30%] w-full p-0 border-none shadow-2xl bg-white flex flex-col">
        <div class="bg-[#4B49AC] p-6 text-white shrink-0">
          <SheetHeader>
            <SheetTitle class="text-xl font-bold text-white">Edit Jadwal Mengajar</SheetTitle>
            <SheetDescription class="text-indigo-100 mt-1">
              Ubah data jadwal yang sudah ada.
            </SheetDescription>
          </SheetHeader>
        </div>

        <form @submit.prevent="submitUpdate" class="flex flex-col h-full overflow-hidden">
          <div class="flex-1 overflow-y-auto p-6 space-y-5">
            <div class="space-y-2">
              <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Tahun Akademik</Label>
              <Input v-model="editForm.tahun" disabled class="bg-gray-50 border-gray-200" />
            </div>

            <div class="space-y-2">
              <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Kelas</Label>
              <Popover :open="openPopoverKelasEdit" @update:open="openPopoverKelasEdit = $event">
                  <PopoverTrigger as-child>
                    <Button
                      variant="outline"
                      role="combobox"
                      :aria-expanded="openPopoverKelasEdit"
                      class="w-full justify-between h-11 border-gray-200 focus:border-[#4B49AC] focus:ring-[#4B49AC]/20 font-normal"
                      :class="!editForm.kelas_id ? 'text-gray-500' : 'text-gray-900'"
                    >
                      {{ getKelasLabel(editForm.kelas_id) }}
                      <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                    </Button>
                  </PopoverTrigger>
                  <PopoverContent class="w-[380px] p-0 bg-white border border-gray-200 shadow-lg z-50" align="start">
                    <Command>
                      <CommandInput placeholder="Cari kelas..." />
                      <CommandEmpty>Kelas tidak ditemukan.</CommandEmpty>
                      <CommandList>
                        <CommandGroup>
                          <CommandItem
                            v-for="kelas in kelass"
                            :key="kelas.id"
                            :value="`${kelas.nama_kelas} Smt ${kelas.semester?.semester} ${kelas.prodi?.singkatan}`"
                            @select="() => {
                              editForm.kelas_id = String(kelas.id)
                              openPopoverKelasEdit = false
                            }"
                          >
                            <Check
                              :class="editForm.kelas_id === String(kelas.id) ? 'opacity-100' : 'opacity-0'"
                              class="mr-2 h-4 w-4"
                            />
                            {{ kelas.nama_kelas }} (Smt {{ kelas.semester?.semester }} - {{ kelas.prodi?.singkatan }})
                          </CommandItem>
                        </CommandGroup>
                      </CommandList>
                    </Command>
                  </PopoverContent>
                </Popover>
              <p v-if="editForm.errors.kelas_id" class="text-xs text-red-500 font-medium">{{ editForm.errors.kelas_id }}</p>
            </div>

            <div class="space-y-2">
              <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Mata Kuliah</Label>
              <Popover :open="openPopoverMatkulEdit" @update:open="openPopoverMatkulEdit = $event">
                  <PopoverTrigger as-child>
                    <Button
                      variant="outline"
                      role="combobox"
                      :aria-expanded="openPopoverMatkulEdit"
                      :disabled="!editForm.kelas_id || availableMatkuls.length === 0"
                      class="w-full justify-between h-11 border-gray-200 focus:border-[#4B49AC] focus:ring-[#4B49AC]/20 font-normal"
                      :class="!editForm.kelas_id ? 'bg-gray-50 text-gray-400' : (!editForm.matkul_id ? 'text-gray-500' : 'text-gray-900')"
                    >
                      {{ !editForm.kelas_id ? 'Pilih Kelas Dahulu' : getMatkulLabel(editForm.matkul_id) }}
                      <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                    </Button>
                  </PopoverTrigger>
                  <PopoverContent class="w-[380px] p-0 bg-white border border-gray-200 shadow-lg z-50" align="start">
                    <Command>
                      <CommandInput placeholder="Cari mata kuliah..." />
                      <CommandEmpty>Mata kuliah tidak ditemukan.</CommandEmpty>
                      <CommandList>
                        <CommandGroup>
                          <CommandItem
                            v-for="matkul in availableMatkuls"
                            :key="matkul.id"
                            :value="`${matkul.nama_matkul} ${matkul.kode || matkul.kode_matkul}`"
                            @select="() => {
                              editForm.matkul_id = String(matkul.id)
                              openPopoverMatkulEdit = false
                            }"
                          >
                            <Check
                              :class="editForm.matkul_id === String(matkul.id) ? 'opacity-100' : 'opacity-0'"
                              class="mr-2 h-4 w-4"
                            />
                            {{ matkul.nama_matkul }} ({{ matkul.kode || matkul.kode_matkul }})
                          </CommandItem>
                        </CommandGroup>
                      </CommandList>
                    </Command>
                  </PopoverContent>
                </Popover>
              <p v-if="!editForm.kelas_id" class="text-[10px] text-amber-500">Pilih kelas terlebih dahulu.</p>
              <p v-if="editForm.kelas_id && availableMatkuls.length === 0" class="text-[10px] text-red-500">Tidak ada matkul untuk semester/prodi kelas ini.</p>
              <p v-if="editForm.errors.matkul_id" class="text-xs text-red-500 font-medium">{{ editForm.errors.matkul_id }}</p>
            </div>

            <div class="space-y-2">
              <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">SKS</Label>
              <Input :value="selectedSks" disabled class="bg-gray-50 border-gray-200 h-11" />
            </div>

            <div class="space-y-2">
              <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Dosen</Label>
              <Select v-model="editForm.dosen_id">
                <SelectTrigger class="h-11 border-gray-200 focus:ring-[#4B49AC]/20 rounded-lg">
                  <SelectValue placeholder="Pilih Dosen" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="dosen in dosens" :key="dosen.id" :value="String(dosen.id)">
                    {{ dosen.nama }}
                  </SelectItem>
                </SelectContent>
              </Select>
              <p v-if="editForm.errors.dosen_id" class="text-xs text-red-500 font-medium">{{ editForm.errors.dosen_id }}</p>
            </div>

            <div class="space-y-2">
              <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Ruangan</Label>
              <Select v-model="editForm.ruangan_id">
                <SelectTrigger class="h-11 border-gray-200 focus:ring-[#4B49AC]/20 rounded-lg">
                  <SelectValue placeholder="Pilih Ruangan" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="ruangan in ruangans" :key="ruangan.id" :value="String(ruangan.id)">
                    {{ ruangan.nama }}
                  </SelectItem>
                </SelectContent>
              </Select>
              <p v-if="editForm.errors.ruangan_id" class="text-xs text-red-500 font-medium">{{ editForm.errors.ruangan_id }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="col-span-2 space-y-2">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Hari</Label>
                <Select v-model="editForm.hari">
                  <SelectTrigger class="h-11 border-gray-200 focus:ring-[#4B49AC]/20 rounded-lg">
                    <SelectValue placeholder="Pilih Hari" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="Senin">Senin</SelectItem>
                    <SelectItem value="Selasa">Selasa</SelectItem>
                    <SelectItem value="Rabu">Rabu</SelectItem>
                    <SelectItem value="Kamis">Kamis</SelectItem>
                    <SelectItem value="Jumat">Jumat</SelectItem>
                    <SelectItem value="Sabtu">Sabtu</SelectItem>
                  </SelectContent>
                </Select>
                <p v-if="editForm.errors.hari" class="text-xs text-red-500 font-medium">{{ editForm.errors.hari }}</p>
              </div>

              <div class="space-y-2">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Jam Mulai</Label>
                <Input type="time" v-model="editForm.jam_mulai" class="h-11 border-gray-200 rounded-lg" required />
                <p v-if="editForm.errors.jam_mulai" class="text-xs text-red-500 font-medium">{{ editForm.errors.jam_mulai }}</p>
              </div>

              <div class="space-y-2">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Jam Selesai</Label>
                <Input type="time" v-model="editForm.jam_selesai" class="h-11 border-gray-200 rounded-lg" required />
                <p v-if="editForm.errors.jam_selesai" class="text-xs text-red-500 font-medium">{{ editForm.errors.jam_selesai }}</p>
              </div>
            </div>
            <p v-if="editForm.errors.tahun" class="text-xs text-red-500 font-medium text-center bg-red-50 p-2 rounded">{{ editForm.errors.tahun }}</p>
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
              Apakah Anda yakin ingin menghapus jadwal <span class="text-danger font-bold">"{{ selectedJadwal?.matkul?.nama_matkul }}"</span>? 
              <br><span class="text-xs mt-3 block italic text-gray-400 font-medium tracking-wide">Tindakan ini juga akan menghapus data Message/Notifikasi yang terhubung ke jadwal ini.</span>
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
