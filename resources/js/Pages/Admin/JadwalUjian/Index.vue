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
  pegawais: Array,
  kelass: Array,
  matkuls: Array,
  ruangans: Array,
  tahun_akademik: String,
  is_tahun_akademik_active: Boolean,
})

const page = usePage()

// Filters & Search
const search = ref(props.filters.search || '')
const filterPengawas = ref(props.filters.pengawas_value || 'all')
const filterKelas = ref(props.filters.kelas_id || 'all')
const filterJenis = ref(props.filters.jenis || 'all')

const allPengawas = computed(() => {
  const list = []
  if (props.dosens) {
    props.dosens.forEach(d => {
      list.push({
        value: `dosen-${d.id}`,
        label: `${d.nama} (Dosen)`
      })
    })
  }
  if (props.pegawais) {
    props.pegawais.forEach(p => {
      list.push({
        value: `pegawai-${p.id}`,
        label: `${p.nama} (Pegawai/Staf)`
      })
    })
  }
  return list.sort((a, b) => a.label.localeCompare(b.label))
})

let filterTimeout = null
const updateFilters = () => {
  if (filterTimeout) clearTimeout(filterTimeout)
  filterTimeout = setTimeout(() => {
    router.get('/v2/admin/jadwal-ujian', {
      search: search.value,
      pengawas_value: filterPengawas.value === 'all' ? '' : filterPengawas.value,
      kelas_id: filterKelas.value === 'all' ? '' : filterKelas.value,
      jenis: filterJenis.value === 'all' ? '' : filterJenis.value,
    }, {
      preserveState: true,
      replace: true
    })
  }, 300)
}

watch([search, filterPengawas, filterKelas, filterJenis], () => {
  updateFilters()
})

const clearFilters = () => {
  search.value = ''
  filterPengawas.value = 'all'
  filterKelas.value = 'all'
  filterJenis.value = 'all'
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
  matkuls_id: '',
  pengawas_value: '',
  ruangans_id: '',
  jenis_ujian: '',
  tanggal: '',
  waktu_mulai: '',
  waktu_selesai: '',
  tahun: props.tahun_akademik || ''
})

const editForm = useForm({
  id: null,
  kelas_id: '',
  matkuls_id: '',
  pengawas_value: '',
  ruangans_id: '',
  jenis_ujian: '',
  tanggal: '',
  waktu_mulai: '',
  waktu_selesai: '',
  tahun: ''
})


const openPopoverKelasAdd = ref(false)
const openPopoverMatkulAdd = ref(false)
const openPopoverKelasEdit = ref(false)
const openPopoverMatkulEdit = ref(false)
const openPopoverPengawasFilter = ref(false)
const openPopoverPengawasAdd = ref(false)
const openPopoverPengawasEdit = ref(false)

const getKelasLabel = (kelasId) => {
  if (!kelasId) return 'Pilih Kelas'
  const k = props.kelass.find(x => String(x.id) === String(kelasId))
  if (!k) return 'Pilih Kelas'
  return `${k.nama_kelas} (Smt ${k.semester?.semester || '-'} - ${k.prodi?.singkatan || '-'})`
}

const getPengawasLabel = (value) => {
  if (!value) return 'Pilih Pengawas'
  const p = allPengawas.value.find(x => x.value === String(value))
  return p ? p.label : 'Pilih Pengawas'
}

const getMatkulLabel = (matkulId) => {
  if (!matkulId) return 'Pilih Mata Kuliah'
  const m = props.matkuls.find(x => String(x.id) === String(matkulId))
  if (!m) return 'Pilih Mata Kuliah'
  // In JadwalMengajar we might have teo/pra or SKS. Let's just output nama & kode if available
  return `${m.nama_matkul} (${m.kode || m.kode_matkul || '-'})`
}

// Dependent Dropdown Logic (Kelas -> Matkul)
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

watch(() => form.kelas_id, (newVal, oldVal) => {
  if (oldVal) {
    form.matkuls_id = '' // Reset matkul if kelas changes
  }
})

watch(() => editForm.kelas_id, (newVal, oldVal) => {
  if (oldVal && isEditModalOpen.value && oldVal !== newVal) {
    editForm.matkuls_id = ''
  }
})

// Actions
const openEditModal = (jadwal) => {
  selectedJadwal.value = jadwal
  editForm.id = jadwal.id
  editForm.kelas_id = String(jadwal.kelas_id)
  
  // Need to set matkul_id after availableMatkuls updates
  setTimeout(() => {
    editForm.matkuls_id = String(jadwal.matkuls_id)
  }, 50)
  
  editForm.pengawas_value = `${jadwal.pengawas_type === 'App\\Models\\Dosen' ? 'dosen' : 'pegawai'}-${jadwal.pengawas_id}`
  editForm.ruangans_id = String(jadwal.ruangans_id)
  editForm.jenis_ujian = jadwal.jenis_ujian
  editForm.tanggal = jadwal.tanggal
  editForm.waktu_mulai = jadwal.waktu_mulai.substring(0, 5) // "08:00:00" -> "08:00"
  editForm.waktu_selesai = jadwal.waktu_selesai.substring(0, 5)
  editForm.tahun = jadwal.tahun
  isEditModalOpen.value = true
}

const submitAdd = () => {
  form.post('/v2/admin/jadwal-ujian', {
    onSuccess: () => {
      isAddModalOpen.value = false
      form.reset()
      form.tahun = props.tahun_akademik // reset back to active year
    }
  })
}

const submitUpdate = () => {
  editForm.put(`/v2/admin/jadwal-ujian/${editForm.id}`, {
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
  router.delete(`/v2/admin/jadwal-ujian/${selectedJadwal.value.id}`, {
    onSuccess: () => {
      isDeleteModalOpen.value = false
    }
  })
}

const formatTime = (time) => {
  if (!time) return '-'
  return time.substring(0, 5)
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  }).format(date)
}
</script>

<template>
  <AdminLayout>
    <Head title="Jadwal Ujian" />

    <div class="space-y-6">
      <!-- Alerts -->
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

      <Alert v-if="kelass.length === 0 && is_tahun_akademik_active" variant="warning" class="border-amber-500 bg-amber-50 text-amber-700">
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
          <h1 class="text-2xl font-bold text-[#1F2937]">Jadwal Ujian</h1>
          <p class="text-[#6B7280]">Kelola jadwal ujian mahasiswa berdasarkan kelas dan mata kuliah.</p>
        </div>
        <div class="flex items-center gap-3">
          <Button 
            @click="isAddModalOpen = true" 
            :disabled="!is_tahun_akademik_active"
            :class="!is_tahun_akademik_active ? 'bg-gray-300 cursor-not-allowed' : 'bg-[#4B49AC] hover:bg-[#3f3d91] text-white shadow-sm'"
            class="rounded-lg font-semibold transition-all duration-300"
          >
            <Plus class="w-4 h-4 mr-2" />
            Tambah Jadwal Ujian
          </Button>
        </div>
      </div>

      <!-- Filters & Search -->
      <Card class="border-none shadow-sm bg-white">
        <CardContent class="p-4">
          <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
            <div class="flex flex-wrap items-center gap-3 flex-1">
              <div class="w-full md:w-[200px]">
                <Popover :open="openPopoverPengawasFilter" @update:open="openPopoverPengawasFilter = $event">
                  <PopoverTrigger as-child>
                    <Button
                      variant="outline"
                      role="combobox"
                      :aria-expanded="openPopoverPengawasFilter"
                      class="w-full justify-between bg-gray-50 border-gray-200 text-left font-normal"
                      :class="filterPengawas === 'all' ? 'text-gray-500' : 'text-gray-900'"
                    >
                      {{ filterPengawas === 'all' ? 'Semua Pengawas' : getPengawasLabel(filterPengawas) }}
                      <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                    </Button>
                  </PopoverTrigger>
                  <PopoverContent class="w-[250px] p-0 bg-white border border-gray-200 shadow-lg" align="start">
                    <Command>
                      <CommandInput placeholder="Cari pengawas..." />
                      <CommandEmpty>Pengawas tidak ditemukan.</CommandEmpty>
                      <CommandList>
                        <CommandGroup>
                          <CommandItem
                            value="all"
                            @select="() => {
                              filterPengawas = 'all'
                              openPopoverPengawasFilter = false
                            }"
                          >
                            <Check
                              :class="filterPengawas === 'all' ? 'opacity-100' : 'opacity-0'"
                              class="mr-2 h-4 w-4"
                            />
                            Semua Pengawas
                          </CommandItem>
                          <CommandItem
                            v-for="pengawas in allPengawas"
                            :key="pengawas.value"
                            :value="pengawas.label"
                            @select="() => {
                              filterPengawas = pengawas.value
                              openPopoverPengawasFilter = false
                            }"
                          >
                            <Check
                              :class="filterPengawas === pengawas.value ? 'opacity-100' : 'opacity-0'"
                              class="mr-2 h-4 w-4"
                            />
                            {{ pengawas.label }}
                          </CommandItem>
                        </CommandGroup>
                      </CommandList>
                    </Command>
                  </PopoverContent>
                </Popover>
              </div>

              <div class="w-full md:w-[200px]">
                <Select v-model="filterKelas">
                  <SelectTrigger class="bg-gray-50 border-gray-200">
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

              <div class="w-full md:w-[200px]">
                <Select v-model="filterJenis">
                  <SelectTrigger class="bg-gray-50 border-gray-200">
                    <SelectValue placeholder="Semua Jenis Ujian" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">Semua Jenis Ujian</SelectItem>
                    <SelectItem value="uts">Ujian Tengah Semester (UTS)</SelectItem>
                    <SelectItem value="uas">Ujian Akhir Semester (UAS)</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <Button 
                v-if="filterPengawas || filterKelas || filterJenis || search" 
                variant="ghost" 
                @click="clearFilters"
                class="text-red-500 hover:text-red-600 hover:bg-red-50 px-3"
              >
                <X class="w-4 h-4 mr-2" /> Reset
              </Button>
            </div>

            <div class="relative w-full md:w-[300px]">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <Search class="h-4 w-4 text-gray-400" />
              </div>
              <Input 
                v-model="search" 
                placeholder="Cari mata kuliah..." 
                class="pl-10 bg-gray-50 border-gray-200 focus:border-[#4B49AC] focus:ring-[#4B49AC]/20 transition-all rounded-lg"
              />
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Table Section -->
      <Card class="border-none shadow-sm overflow-hidden bg-white">
        <div class="overflow-x-auto">
          <Table>
            <TableHeader class="bg-[#F9FAFB]">
              <TableRow>
                <TableHead class="w-[60px] font-bold text-[#374151]">No</TableHead>
                <TableHead class="font-bold text-[#374151] min-w-[200px]">Mata Kuliah</TableHead>
                <TableHead class="font-bold text-[#374151] min-w-[150px]">Pengawas</TableHead>
                <TableHead class="font-bold text-[#374151] min-w-[120px]">Ruangan & Kelas</TableHead>
                <TableHead class="font-bold text-[#374151] min-w-[180px]">Waktu Ujian</TableHead>
                <TableHead class="font-bold text-[#374151] min-w-[100px]">Jenis</TableHead>
                <TableHead class="text-right font-bold text-[#374151]">Opsi</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="(jadwal, index) in jadwals.data" :key="jadwal.id" class="hover:bg-gray-50/50 transition-colors group">
                <TableCell class="font-medium text-[#1F2937]">{{ (jadwals.current_page - 1) * jadwals.per_page + index + 1 }}</TableCell>
                
                <TableCell>
                  <div class="flex items-start gap-3">
                    <div class="mt-1 w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center shrink-0">
                      <BookOpen class="w-4 h-4 text-[#4B49AC]" />
                    </div>
                    <div>
                      <div class="font-bold text-[#1F2937]">{{ jadwal.matkul?.nama_matkul }}</div>
                      <div class="text-xs text-[#6B7280] font-medium mt-0.5">Kode: {{ jadwal.matkul?.kode }}</div>
                    </div>
                  </div>
                </TableCell>

                <TableCell>
                  <div class="flex items-center gap-2">
                    <User class="w-4 h-4 text-gray-400 shrink-0" />
                    <span class="font-medium text-[#374151]">
                      {{ jadwal.pengawas?.nama }}
                      <span class="text-[10px] font-bold text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded-md ml-1 shrink-0">
                        {{ jadwal.pengawas_type === 'App\\Models\\Dosen' ? 'Dosen' : 'Staff' }}
                      </span>
                    </span>
                  </div>
                </TableCell>

                <TableCell>
                  <div class="flex flex-col gap-1.5">
                    <div class="inline-flex items-center gap-1.5 text-sm font-medium text-[#374151]">
                      <MapPin class="w-3.5 h-3.5 text-blue-500" />
                      {{ jadwal.ruangan?.nama }}
                    </div>
                    <div class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-700 bg-indigo-50 px-2.5 py-0.5 rounded-md w-fit">
                      Kelas {{ jadwal.kelas?.nama_kelas }}
                    </div>
                  </div>
                </TableCell>

                <TableCell>
                  <div class="flex flex-col gap-1.5">
                    <div class="inline-flex items-center gap-1.5 text-sm font-bold text-[#1F2937]">
                      <CalendarDays class="w-4 h-4 text-orange-500" />
                      {{ formatDate(jadwal.tanggal) }}
                    </div>
                    <div class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-600">
                      <Clock class="w-3.5 h-3.5 text-gray-400" />
                      {{ formatTime(jadwal.waktu_mulai) }} - {{ formatTime(jadwal.waktu_selesai) }}
                    </div>
                  </div>
                </TableCell>

                <TableCell>
                  <span 
                    :class="jadwal.jenis_ujian === 'uts' ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-purple-100 text-purple-800 border-purple-200'"
                    class="px-2.5 py-1 text-xs font-bold rounded-md border"
                  >
                    {{ jadwal.jenis_ujian === 'uts' ? 'UTS' : 'UAS' }}
                  </span>
                </TableCell>

                <TableCell class="text-right">
                  <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                    <Button variant="ghost" size="sm" @click="openEditModal(jadwal)" class="text-blue-600 hover:text-blue-700 hover:bg-blue-50 h-8 w-8 p-0 rounded-lg">
                      <Pencil class="w-4 h-4" />
                    </Button>
                    <Button variant="ghost" size="sm" @click="confirmDelete(jadwal)" class="text-red-600 hover:text-red-700 hover:bg-red-50 h-8 w-8 p-0 rounded-lg">
                      <Trash2 class="w-4 h-4" />
                    </Button>
                  </div>
                </TableCell>
              </TableRow>
              
              <TableRow v-if="jadwals.data.length === 0">
                <TableCell colspan="7" class="h-48 text-center text-[#9CA3AF]">
                  <div class="flex flex-col items-center justify-center space-y-3">
                    <div class="p-4 bg-gray-50 rounded-full">
                      <CalendarDays class="w-10 h-10 opacity-20" />
                    </div>
                    <p class="font-medium text-gray-400">Tidak ada data jadwal ujian yang ditemukan.</p>
                  </div>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>

        <!-- Pagination -->
        <div class="border-t border-gray-100 p-4 flex items-center justify-between bg-gray-50/50" v-if="jadwals.data.length > 0">
          <div class="text-sm text-gray-500 font-medium">
            Menampilkan <span class="font-bold text-gray-900">{{ jadwals.from }}</span> hingga <span class="font-bold text-gray-900">{{ jadwals.to }}</span> dari <span class="font-bold text-gray-900">{{ jadwals.total }}</span> jadwal
          </div>
          <div class="flex gap-2">
            <Button
              v-for="(link, index) in jadwals.links"
              :key="index"
              @click="link.url ? router.get(link.url, {}, { preserveState: true }) : null"
              :disabled="!link.url"
              :variant="link.active ? 'default' : 'outline'"
              class="h-8 min-w-[32px] px-3 font-medium transition-all"
              :class="link.active ? 'bg-[#4B49AC] text-white hover:bg-[#3f3d91] border-transparent' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 border-gray-200'"
              v-html="link.label"
            />
          </div>
        </div>
      </Card>
    </div>

    <!-- Add Drawer -->
    <Sheet :open="isAddModalOpen" @update:open="isAddModalOpen = $event">
      <SheetContent side="right" class="sm:max-w-[450px] w-full p-0 border-none shadow-2xl bg-white flex flex-col">
        <div class="bg-[#4B49AC] p-6 text-white shrink-0">
          <SheetHeader>
            <SheetTitle class="text-xl font-bold text-white">Tambah Jadwal Ujian</SheetTitle>
            <SheetDescription class="text-indigo-100 mt-1">
              Tambahkan jadwal ujian baru untuk kelas dan mata kuliah.
            </SheetDescription>
          </SheetHeader>
        </div>

        <form @submit.prevent="submitAdd" class="flex flex-col h-full overflow-hidden">
          <div class="flex-1 overflow-y-auto p-6 space-y-6">
            <!-- Akademik Section -->
            <div class="space-y-4">
              <h3 class="text-sm font-bold text-gray-900 border-b pb-2 flex items-center gap-2">
                <BookOpen class="w-4 h-4 text-[#4B49AC]" /> Informasi Akademik
              </h3>
              
              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Tahun Akademik</Label>
                  <Input v-model="form.tahun" disabled class="bg-gray-50 border-gray-200 text-gray-600 font-semibold" />
                </div>
                <div class="space-y-2">
                  <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Jenis Ujian</Label>
                  <Select v-model="form.jenis_ujian" required>
                    <SelectTrigger class="h-11 border-gray-200 focus:border-[#4B49AC] focus:ring-[#4B49AC]/20">
                      <SelectValue placeholder="Pilih Jenis" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="uts">UTS</SelectItem>
                      <SelectItem value="uas">UAS</SelectItem>
                    </SelectContent>
                  </Select>
                  <p v-if="form.errors.jenis_ujian" class="text-xs text-red-500 font-medium mt-1">{{ form.errors.jenis_ujian }}</p>
                </div>
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
                  <PopoverContent class="w-[380px] p-0 bg-white border border-gray-200 shadow-lg" align="start">
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
                <p v-if="form.errors.kelas_id" class="text-xs text-red-500 font-medium mt-1">{{ form.errors.kelas_id }}</p>
              </div>

              <div class="space-y-2">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Mata Kuliah</Label>
                <Popover :open="openPopoverMatkulAdd" @update:open="openPopoverMatkulAdd = $event">
                  <PopoverTrigger as-child>
                    <Button
                      variant="outline"
                      role="combobox"
                      :aria-expanded="openPopoverMatkulAdd"
                      :disabled="!form.kelas_id"
                      class="w-full justify-between h-11 border-gray-200 focus:border-[#4B49AC] focus:ring-[#4B49AC]/20 font-normal"
                      :class="!form.kelas_id ? 'bg-gray-50 text-gray-400' : (!form.matkuls_id ? 'text-gray-500' : 'text-gray-900')"
                    >
                      {{ !form.kelas_id ? 'Pilih Kelas Dahulu' : getMatkulLabel(form.matkuls_id) }}
                      <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                    </Button>
                  </PopoverTrigger>
                  <PopoverContent class="w-[380px] p-0 bg-white border border-gray-200 shadow-lg" align="start">
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
                              form.matkuls_id = String(matkul.id)
                              openPopoverMatkulAdd = false
                            }"
                          >
                            <Check
                              :class="form.matkuls_id === String(matkul.id) ? 'opacity-100' : 'opacity-0'"
                              class="mr-2 h-4 w-4"
                            />
                            {{ matkul.nama_matkul }} ({{ matkul.kode || matkul.kode_matkul }})
                          </CommandItem>
                        </CommandGroup>
                      </CommandList>
                    </Command>
                  </PopoverContent>
                </Popover>
                <p v-if="availableMatkuls.length === 0 && form.kelas_id" class="text-xs text-amber-500 font-medium flex items-center gap-1 mt-1">
                  <AlertCircle class="w-3 h-3" /> Tidak ada mata kuliah untuk program studi & semester kelas ini.
                </p>
                <p v-if="form.errors.matkuls_id" class="text-xs text-red-500 font-medium mt-1">{{ form.errors.matkuls_id }}</p>
              </div>
            </div>

            <!-- Pelaksanaan Section -->
            <div class="space-y-4">
              <h3 class="text-sm font-bold text-gray-900 border-b pb-2 flex items-center gap-2 mt-6">
                <CalendarDays class="w-4 h-4 text-[#4B49AC]" /> Informasi Pelaksanaan
              </h3>

              <div class="space-y-2">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Pengawas Ujian</Label>
                <Popover :open="openPopoverPengawasAdd" @update:open="openPopoverPengawasAdd = $event">
                  <PopoverTrigger as-child>
                    <Button
                      type="button"
                      variant="outline"
                      role="combobox"
                      :aria-expanded="openPopoverPengawasAdd"
                      class="w-full justify-between h-11 border-gray-200 focus:border-[#4B49AC] focus:ring-[#4B49AC]/20 font-normal"
                      :class="!form.pengawas_value ? 'text-gray-500' : 'text-gray-900'"
                    >
                      {{ getPengawasLabel(form.pengawas_value) }}
                      <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                    </Button>
                  </PopoverTrigger>
                  <PopoverContent class="w-[380px] p-0 bg-white border border-gray-200 shadow-lg" align="start">
                    <Command>
                      <CommandInput placeholder="Cari pengawas..." />
                      <CommandEmpty>Pengawas tidak ditemukan.</CommandEmpty>
                      <CommandList>
                        <CommandGroup>
                          <CommandItem
                            v-for="pengawas in allPengawas"
                            :key="pengawas.value"
                            :value="pengawas.label"
                            @select="() => {
                              form.pengawas_value = pengawas.value
                              openPopoverPengawasAdd = false
                            }"
                          >
                            <Check
                              :class="form.pengawas_value === pengawas.value ? 'opacity-100' : 'opacity-0'"
                              class="mr-2 h-4 w-4"
                            />
                            {{ pengawas.label }}
                          </CommandItem>
                        </CommandGroup>
                      </CommandList>
                    </Command>
                  </PopoverContent>
                </Popover>
                <p v-if="form.errors.pengawas_value" class="text-xs text-red-500 font-medium mt-1">{{ form.errors.pengawas_value }}</p>
              </div>

              <div class="space-y-2">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Ruangan</Label>
                <Select v-model="form.ruangans_id" required>
                  <SelectTrigger class="h-11 border-gray-200 focus:border-[#4B49AC] focus:ring-[#4B49AC]/20">
                    <SelectValue placeholder="Pilih Ruangan" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="ruangan in ruangans" :key="ruangan.id" :value="String(ruangan.id)">
                      {{ ruangan.nama }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                <p v-if="form.errors.ruangans_id" class="text-xs text-red-500 font-medium mt-1">{{ form.errors.ruangans_id }}</p>
              </div>

              <div class="space-y-2">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal Ujian</Label>
                <Input type="date" v-model="form.tanggal" required class="h-11 border-gray-200 focus:border-[#4B49AC]" />
                <p v-if="form.errors.tanggal" class="text-xs text-red-500 font-medium mt-1">{{ form.errors.tanggal }}</p>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Jam Mulai</Label>
                  <Input type="time" v-model="form.waktu_mulai" required class="h-11 border-gray-200 focus:border-[#4B49AC]" />
                  <p v-if="form.errors.waktu_mulai" class="text-xs text-red-500 font-medium mt-1">{{ form.errors.waktu_mulai }}</p>
                </div>
                <div class="space-y-2">
                  <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Jam Selesai</Label>
                  <Input type="time" v-model="form.waktu_selesai" required class="h-11 border-gray-200 focus:border-[#4B49AC]" />
                  <p v-if="form.errors.waktu_selesai" class="text-xs text-red-500 font-medium mt-1">{{ form.errors.waktu_selesai }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="p-6 border-t border-gray-100 bg-gray-50/50 shrink-0">
            <SheetFooter class="flex flex-row items-center justify-end gap-3">
              <Button type="button" variant="ghost" @click="isAddModalOpen = false" class="h-11 px-6 rounded-lg font-semibold">
                Batal
              </Button>
              <Button type="submit" :disabled="form.processing" class="h-11 px-8 bg-[#4B49AC] hover:bg-[#3f3d91] text-white rounded-lg shadow-lg font-semibold">
                <Loader2 v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
                Simpan Jadwal
              </Button>
            </SheetFooter>
          </div>
        </form>
      </SheetContent>
    </Sheet>

    <!-- Edit Drawer -->
    <Sheet :open="isEditModalOpen" @update:open="isEditModalOpen = $event">
      <SheetContent side="right" class="sm:max-w-[450px] w-full p-0 border-none shadow-2xl bg-white flex flex-col">
        <div class="bg-[#4B49AC] p-6 text-white shrink-0">
          <SheetHeader>
            <SheetTitle class="text-xl font-bold text-white">Edit Jadwal Ujian</SheetTitle>
            <SheetDescription class="text-indigo-100 mt-1">
              Perbarui rincian jadwal ujian yang telah ada.
            </SheetDescription>
          </SheetHeader>
        </div>

        <form @submit.prevent="submitUpdate" class="flex flex-col h-full overflow-hidden">
          <div class="flex-1 overflow-y-auto p-6 space-y-6">
            <!-- Akademik Section -->
            <div class="space-y-4">
              <h3 class="text-sm font-bold text-gray-900 border-b pb-2 flex items-center gap-2">
                <BookOpen class="w-4 h-4 text-[#4B49AC]" /> Informasi Akademik
              </h3>
              
              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Tahun Akademik</Label>
                  <Input v-model="editForm.tahun" disabled class="bg-gray-50 border-gray-200 text-gray-600 font-semibold" />
                </div>
                <div class="space-y-2">
                  <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Jenis Ujian</Label>
                  <Select v-model="editForm.jenis_ujian" required>
                    <SelectTrigger class="h-11 border-gray-200 focus:border-[#4B49AC] focus:ring-[#4B49AC]/20">
                      <SelectValue placeholder="Pilih Jenis" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="uts">UTS</SelectItem>
                      <SelectItem value="uas">UAS</SelectItem>
                    </SelectContent>
                  </Select>
                  <p v-if="editForm.errors.jenis_ujian" class="text-xs text-red-500 font-medium mt-1">{{ editForm.errors.jenis_ujian }}</p>
                </div>
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
                  <PopoverContent class="w-[380px] p-0 bg-white border border-gray-200 shadow-lg" align="start">
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
                <p v-if="editForm.errors.kelas_id" class="text-xs text-red-500 font-medium mt-1">{{ editForm.errors.kelas_id }}</p>
              </div>

              <div class="space-y-2">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Mata Kuliah</Label>
                <Popover :open="openPopoverMatkulEdit" @update:open="openPopoverMatkulEdit = $event">
                  <PopoverTrigger as-child>
                    <Button
                      variant="outline"
                      role="combobox"
                      :aria-expanded="openPopoverMatkulEdit"
                      :disabled="!editForm.kelas_id"
                      class="w-full justify-between h-11 border-gray-200 focus:border-[#4B49AC] focus:ring-[#4B49AC]/20 font-normal"
                      :class="!editForm.kelas_id ? 'bg-gray-50 text-gray-400' : (!editForm.matkuls_id ? 'text-gray-500' : 'text-gray-900')"
                    >
                      {{ !editForm.kelas_id ? 'Pilih Kelas Dahulu' : getMatkulLabel(editForm.matkuls_id) }}
                      <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                    </Button>
                  </PopoverTrigger>
                  <PopoverContent class="w-[380px] p-0 bg-white border border-gray-200 shadow-lg" align="start">
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
                              editForm.matkuls_id = String(matkul.id)
                              openPopoverMatkulEdit = false
                            }"
                          >
                            <Check
                              :class="editForm.matkuls_id === String(matkul.id) ? 'opacity-100' : 'opacity-0'"
                              class="mr-2 h-4 w-4"
                            />
                            {{ matkul.nama_matkul }} ({{ matkul.kode || matkul.kode_matkul }})
                          </CommandItem>
                        </CommandGroup>
                      </CommandList>
                    </Command>
                  </PopoverContent>
                </Popover>
                <p v-if="availableMatkuls.length === 0 && editForm.kelas_id" class="text-xs text-amber-500 font-medium flex items-center gap-1 mt-1">
                  <AlertCircle class="w-3 h-3" /> Tidak ada mata kuliah untuk program studi & semester kelas ini.
                </p>
                <p v-if="editForm.errors.matkuls_id" class="text-xs text-red-500 font-medium mt-1">{{ editForm.errors.matkuls_id }}</p>
              </div>
            </div>

            <!-- Pelaksanaan Section -->
            <div class="space-y-4">
              <h3 class="text-sm font-bold text-gray-900 border-b pb-2 flex items-center gap-2 mt-6">
                <CalendarDays class="w-4 h-4 text-[#4B49AC]" /> Informasi Pelaksanaan
              </h3>

              <div class="space-y-2">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Pengawas Ujian</Label>
                <Popover :open="openPopoverPengawasEdit" @update:open="openPopoverPengawasEdit = $event">
                  <PopoverTrigger as-child>
                    <Button
                      type="button"
                      variant="outline"
                      role="combobox"
                      :aria-expanded="openPopoverPengawasEdit"
                      class="w-full justify-between h-11 border-gray-200 focus:border-[#4B49AC] focus:ring-[#4B49AC]/20 font-normal"
                      :class="!editForm.pengawas_value ? 'text-gray-500' : 'text-gray-900'"
                    >
                      {{ getPengawasLabel(editForm.pengawas_value) }}
                      <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                    </Button>
                  </PopoverTrigger>
                  <PopoverContent class="w-[380px] p-0 bg-white border border-gray-200 shadow-lg" align="start">
                    <Command>
                      <CommandInput placeholder="Cari pengawas..." />
                      <CommandEmpty>Pengawas tidak ditemukan.</CommandEmpty>
                      <CommandList>
                        <CommandGroup>
                          <CommandItem
                            v-for="pengawas in allPengawas"
                            :key="pengawas.value"
                            :value="pengawas.label"
                            @select="() => {
                              editForm.pengawas_value = pengawas.value
                              openPopoverPengawasEdit = false
                            }"
                          >
                            <Check
                              :class="editForm.pengawas_value === pengawas.value ? 'opacity-100' : 'opacity-0'"
                              class="mr-2 h-4 w-4"
                            />
                            {{ pengawas.label }}
                          </CommandItem>
                        </CommandGroup>
                      </CommandList>
                    </Command>
                  </PopoverContent>
                </Popover>
                <p v-if="editForm.errors.pengawas_value" class="text-xs text-red-500 font-medium mt-1">{{ editForm.errors.pengawas_value }}</p>
              </div>

              <div class="space-y-2">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Ruangan</Label>
                <Select v-model="editForm.ruangans_id" required>
                  <SelectTrigger class="h-11 border-gray-200 focus:border-[#4B49AC] focus:ring-[#4B49AC]/20">
                    <SelectValue placeholder="Pilih Ruangan" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="ruangan in ruangans" :key="ruangan.id" :value="String(ruangan.id)">
                      {{ ruangan.nama }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                <p v-if="editForm.errors.ruangans_id" class="text-xs text-red-500 font-medium mt-1">{{ editForm.errors.ruangans_id }}</p>
              </div>

              <div class="space-y-2">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal Ujian</Label>
                <Input type="date" v-model="editForm.tanggal" required class="h-11 border-gray-200 focus:border-[#4B49AC]" />
                <p v-if="editForm.errors.tanggal" class="text-xs text-red-500 font-medium mt-1">{{ editForm.errors.tanggal }}</p>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Jam Mulai</Label>
                  <Input type="time" v-model="editForm.waktu_mulai" required class="h-11 border-gray-200 focus:border-[#4B49AC]" />
                  <p v-if="editForm.errors.waktu_mulai" class="text-xs text-red-500 font-medium mt-1">{{ editForm.errors.waktu_mulai }}</p>
                </div>
                <div class="space-y-2">
                  <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Jam Selesai</Label>
                  <Input type="time" v-model="editForm.waktu_selesai" required class="h-11 border-gray-200 focus:border-[#4B49AC]" />
                  <p v-if="editForm.errors.waktu_selesai" class="text-xs text-red-500 font-medium mt-1">{{ editForm.errors.waktu_selesai }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="p-6 border-t border-gray-100 bg-gray-50/50 shrink-0">
            <SheetFooter class="flex flex-row items-center justify-end gap-3">
              <Button type="button" variant="ghost" @click="isEditModalOpen = false" class="h-11 px-6 rounded-lg font-semibold">
                Batal
              </Button>
              <Button type="submit" :disabled="editForm.processing" class="h-11 px-8 bg-[#4B49AC] hover:bg-[#3f3d91] text-white rounded-lg shadow-lg font-semibold">
                <Loader2 v-if="editForm.processing" class="w-4 h-4 mr-2 animate-spin" />
                Perbarui Jadwal
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
            <DialogDescription class="text-gray-500 text-center mt-3 px-2 text-base leading-relaxed">
              Apakah Anda yakin ingin menghapus jadwal ujian <span class="text-danger font-extrabold">{{ selectedJadwal?.matkul?.nama_matkul }}</span> untuk kelas <span class="text-[#1F2937] font-bold">{{ selectedJadwal?.kelas?.nama_kelas }}</span>?
            </DialogDescription>
          </DialogHeader>

          <div class="flex items-center justify-center gap-4 mt-10">
            <Button type="button" variant="ghost" @click="isDeleteModalOpen = false" class="h-12 px-8 rounded-lg text-gray-500 font-bold hover:bg-gray-100 transition-all">
              Batal
            </Button>
            <Button @click="submitDelete" class="h-12 px-10 bg-danger hover:bg-danger/90 text-white rounded-lg shadow-lg shadow-danger/20 font-bold transition-all">
              Ya, Hapus
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
