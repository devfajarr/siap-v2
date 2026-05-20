<script setup>
import { ref, watch, computed } from 'vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Badge } from '@/Components/ui/badge'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/Components/ui/dialog'
import { Label } from '@/Components/ui/label'
import { Input } from '@/Components/ui/input'
import { Separator } from '@/Components/ui/separator'
import {
  BookOpen,
  Users,
  Calendar,
  Clock,
  MapPin,
  Upload,
  Download,
  CheckCircle2,
  AlertCircle,
  Send,
  Pencil,
  PlusCircle,
  FileText,
  FileSpreadsheet,
  GraduationCap,
  Sparkles,
  ClipboardList
} from 'lucide-vue-next'

const props = defineProps({
  jadwals: {
    type: Array,
    required: true
  },
  rekapStatuses: {
    type: Object,
    default: () => ({})
  },
  pertemuanCounts: {
    type: Object,
    default: () => ({})
  }
})

const page = usePage()

// Toast state
const showToast = ref(false)
const toastMessage = ref('')
const toastType = ref('success')

watch(() => page.props.flash, (flash) => {
  if (flash && flash.success) {
    toastMessage.value = flash.success
    toastType.value = 'success'
    showToast.value = true
    setTimeout(() => { showToast.value = false }, 3000)
  } else if (flash && flash.error) {
    toastMessage.value = flash.error
    toastType.value = 'error'
    showToast.value = true
    setTimeout(() => { showToast.value = false }, 3000)
  }
}, { deep: true, immediate: true })

// Helper to format HH:mm time
const formatTime = (timeString) => {
  if (!timeString) return '--:--'
  const parts = timeString.split(':')
  if (parts.length >= 2) {
    return `${parts[0]}:${parts[1]}`
  }
  return timeString
}

// Compute stats
const totalClasses = computed(() => props.jadwals.length)
const completedContracts = computed(() =>
  props.jadwals.filter(j => (props.pertemuanCounts[j.id] || 0) >= 14).length
)
const pendingApprovals = computed(() =>
  props.jadwals.filter(j => props.rekapStatuses[j.id] && props.rekapStatuses[j.id].status === 0).length
)
const approvedContracts = computed(() =>
  props.jadwals.filter(j => props.rekapStatuses[j.id] && props.rekapStatuses[j.id].status === 1).length
)

// Import Dialog State
const isImportDialogOpen = ref(false)
const selectedJadwal = ref(null)

const importForm = useForm({
  jadwals_id: null,
  file: null
})

const openImportDialog = (jadwal) => {
  selectedJadwal.value = jadwal
  importForm.jadwals_id = jadwal.id
  importForm.file = null
  isImportDialogOpen.value = true
}

const handleFileChange = (e) => {
  importForm.file = e.target.files[0]
}

const submitImport = () => {
  if (!importForm.file) return
  importForm.post(route('import.kontrak'), {
    forceFormData: true,
    onSuccess: () => {
      isImportDialogOpen.value = false
      importForm.reset()
    }
  })
}

// Submit Rekap Request Form
const rekapForm = useForm({
  jadwal_id: null,
  kelas_id: null,
  matkul_id: null
})

const submitRekapRequest = (jadwal) => {
  rekapForm.jadwal_id = jadwal.id
  rekapForm.kelas_id = jadwal.kelas_id || (jadwal.kelas ? jadwal.kelas.id : null)
  rekapForm.matkul_id = jadwal.matkuls_id || (jadwal.matkul ? jadwal.matkul.id : null)

  rekapForm.post(route('rekap-kontrak.store'), {
    onSuccess: () => {
      // success flash message displays toast
    }
  })
}
</script>

<template>
  <AdminLayout>
    <Head title="Kontrak Perkuliahan" />

    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
            <ClipboardList class="w-7 h-7 text-[#4B49AC]" />
            Kontrak Perkuliahan
          </h1>
          <p class="text-slate-500 text-sm mt-1">
            Kelola, isi, dan rekap kontrak perkuliahan untuk setiap kelas dan mata kuliah yang Anda ampu.
          </p>
        </div>
      </div>

      <!-- Statistics Widget -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <Card class="border-none shadow-sm bg-gradient-to-br from-indigo-50/50 to-indigo-100/30">
          <CardContent class="p-4 flex items-center gap-3">
            <div class="p-2.5 bg-indigo-500 text-white rounded-xl shadow-sm">
              <GraduationCap class="w-5 h-5" />
            </div>
            <div>
              <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Kelas</p>
              <h3 class="text-xl font-extrabold text-slate-800 mt-0.5">{{ totalClasses }}</h3>
            </div>
          </CardContent>
        </Card>

        <Card class="border-none shadow-sm bg-gradient-to-br from-emerald-50/50 to-emerald-100/30">
          <CardContent class="p-4 flex items-center gap-3">
            <div class="p-2.5 bg-emerald-500 text-white rounded-xl shadow-sm">
              <CheckCircle2 class="w-5 h-5" />
            </div>
            <div>
              <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Kontrak Selesai</p>
              <h3 class="text-xl font-extrabold text-slate-800 mt-0.5">{{ completedContracts }}</h3>
            </div>
          </CardContent>
        </Card>

        <Card class="border-none shadow-sm bg-gradient-to-br from-amber-50/50 to-amber-100/30">
          <CardContent class="p-4 flex items-center gap-3">
            <div class="p-2.5 bg-amber-500 text-white rounded-xl shadow-sm">
              <Clock class="w-5 h-5" />
            </div>
            <div>
              <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Pending</p>
              <h3 class="text-xl font-extrabold text-slate-800 mt-0.5">{{ pendingApprovals }}</h3>
            </div>
          </CardContent>
        </Card>

        <Card class="border-none shadow-sm bg-gradient-to-br from-teal-50/50 to-teal-100/30">
          <CardContent class="p-4 flex items-center gap-3">
            <div class="p-2.5 bg-teal-500 text-white rounded-xl shadow-sm">
              <FileText class="w-5 h-5" />
            </div>
            <div>
              <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Disetujui</p>
              <h3 class="text-xl font-extrabold text-slate-800 mt-0.5">{{ approvedContracts }}</h3>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Jadwal Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <Card
          v-for="jadwal in jadwals"
          :key="jadwal.id"
          class="border-none shadow-md overflow-hidden hover:shadow-lg transition-all duration-300 flex flex-col group"
        >
          <!-- Card Header Decoration & Title -->
          <CardHeader class="p-5 pb-3 bg-white border-b border-slate-50">
            <div class="flex items-start justify-between gap-3">
              <div class="relative pl-4 flex-1">
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-[#4B49AC] rounded-full group-hover:scale-y-110 transition-transform"></div>
                <CardTitle class="text-base font-bold text-slate-800 leading-snug line-clamp-2" :title="jadwal.matkul?.nama_matkul">
                  {{ jadwal.matkul?.nama_matkul || 'Mata Kuliah' }}
                </CardTitle>
                <span class="text-xs font-mono text-[#4B49AC] font-bold tracking-wider block mt-1">
                  KODE: {{ jadwal.matkul?.kode || '-' }}
                </span>
              </div>

              <!-- Status Badge -->
              <div class="flex-shrink-0">
                <Badge
                  v-if="rekapStatuses[jadwal.id]?.status === 1"
                  variant="secondary"
                  class="bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-50 shadow-none font-semibold px-2 py-0.5 rounded-full flex items-center gap-1 text-[11px]"
                >
                  <CheckCircle2 class="w-3.5 h-3.5" /> Disetujui
                </Badge>
                <Badge
                  v-else-if="rekapStatuses[jadwal.id]?.status === 0"
                  variant="outline"
                  class="bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-50 shadow-none font-semibold px-2 py-0.5 rounded-full flex items-center gap-1 text-[11px]"
                >
                  <Clock class="w-3.5 h-3.5" /> Pending
                </Badge>
                <Badge
                  v-else
                  variant="outline"
                  class="bg-slate-50 text-slate-500 border border-slate-200 hover:bg-slate-50 shadow-none font-semibold px-2 py-0.5 rounded-full flex items-center gap-1 text-[11px]"
                >
                  <AlertCircle class="w-3.5 h-3.5" /> Belum Diajukan
                </Badge>
              </div>
            </div>
          </CardHeader>

          <!-- Card Content Body -->
          <CardContent class="p-5 flex-1 flex flex-col justify-between space-y-4">
            <!-- Details List -->
            <div class="space-y-2.5">
              <div class="flex items-center text-sm text-slate-600 gap-2">
                <Users class="w-4 h-4 text-slate-400 flex-shrink-0" />
                <span class="truncate">Kelas: <strong class="text-slate-800">{{ jadwal.kelas?.nama_kelas || '-' }}</strong></span>
              </div>
              <div class="flex items-center text-sm text-slate-600 gap-2">
                <BookOpen class="w-4 h-4 text-slate-400 flex-shrink-0" />
                <span class="truncate">Prodi: <strong class="text-slate-800">{{ jadwal.kelas?.prodi?.nama_prodi || '-' }}</strong></span>
              </div>
              <div class="flex items-center text-sm text-slate-600 gap-2">
                <Calendar class="w-4 h-4 text-slate-400 flex-shrink-0" />
                <span>
                  SKS: <strong class="text-slate-800">{{ (jadwal.matkul?.praktek || 0) + (jadwal.matkul?.teori || 0) }}</strong>
                  <span class="mx-1.5 text-slate-300">|</span>
                  Hari: <strong class="text-slate-800">{{ jadwal.hari || '-' }}</strong>
                </span>
              </div>
              <div class="flex items-center text-sm text-slate-600 gap-2">
                <Clock class="w-4 h-4 text-slate-400 flex-shrink-0" />
                <span>Waktu: <strong class="text-slate-800">{{ formatTime(jadwal.waktu_mulai) }} - {{ formatTime(jadwal.waktu_selesai) }}</strong></span>
              </div>
              <div class="flex items-center text-sm text-slate-600 gap-2">
                <MapPin class="w-4 h-4 text-slate-400 flex-shrink-0" />
                <span class="truncate">Ruangan: <strong class="text-slate-800">{{ jadwal.ruangan?.nama || '-' }}</strong></span>
              </div>
            </div>

            <!-- Progress Tracker -->
            <div class="space-y-1.5 pt-3 border-t border-slate-100">
              <div class="flex justify-between text-xs font-semibold text-slate-500">
                <span class="flex items-center gap-1">
                  <Sparkles class="w-3.5 h-3.5 text-amber-500" />
                  Progress Kontrak
                </span>
                <span>{{ pertemuanCounts[jadwal.id] || 0 }}/14 Pertemuan</span>
              </div>
              <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                <div
                  class="bg-[#4B49AC] h-full rounded-full transition-all duration-500"
                  :style="{ width: `${Math.min(Math.round(((pertemuanCounts[jadwal.id] || 0) / 14) * 100), 100)}%` }"
                ></div>
              </div>
            </div>

            <!-- Actions Wrapper -->
            <div class="pt-3 border-t border-slate-100 space-y-2">
              <!-- CASE 1: 14 Pertemuan Terisi (Semua Terisi) -->
              <template v-if="(pertemuanCounts[jadwal.id] || 0) >= 14">
                <!-- Subcase 1a: Belum Diajukan -->
                <div v-if="!rekapStatuses[jadwal.id]" class="w-full">
                  <Button
                    class="w-full bg-[#4B49AC] hover:bg-[#3f3e91] text-white shadow-sm transition-colors flex items-center justify-center gap-1.5"
                    @click="submitRekapRequest(jadwal)"
                    :disabled="rekapForm.processing"
                  >
                    <Send class="w-4 h-4" />
                    {{ rekapForm.processing ? 'Mengirim...' : 'Ajukan Rekap Kontrak' }}
                  </Button>
                </div>

                <!-- Subcase 1b: Pending -->
                <div v-else-if="rekapStatuses[jadwal.id]?.status === 0" class="w-full">
                  <Button
                    variant="outline"
                    class="w-full border-amber-200 text-amber-700 bg-amber-50/50 hover:bg-amber-50/50 shadow-none cursor-not-allowed flex items-center justify-center gap-1.5"
                    disabled
                  >
                    <Clock class="w-4 h-4" />
                    Pending Persetujuan
                  </Button>
                </div>

                <!-- Subcase 1c: Disetujui (Approved) -->
                <div v-else-if="rekapStatuses[jadwal.id]?.status === 1" class="grid grid-cols-2 gap-2">
                  <a
                    :href="route('v2.dosen.kontrak.rekap', { matkul_id: jadwal.matkul?.id || jadwal.matkuls_id, kelas_id: jadwal.kelas?.id || jadwal.kelas_id, jadwal_id: jadwal.id })"
                    class="w-full"
                    target="_blank"
                  >
                    <Button
                      variant="outline"
                      class="w-full border-emerald-200 text-emerald-700 hover:bg-emerald-50 flex items-center justify-center gap-1.5"
                    >
                      <FileText class="w-4 h-4" />
                      Lihat Rekap
                    </Button>
                  </a>
                  <a :href="route('v2.dosen.kontrak.edit', jadwal.id)" class="w-full">
                    <Button
                      variant="outline"
                      class="w-full border-slate-200 text-slate-700 hover:bg-slate-50 flex items-center justify-center gap-1.5"
                    >
                      <Pencil class="w-4 h-4" />
                      Edit Kontrak
                    </Button>
                  </a>
                </div>
              </template>

              <!-- CASE 2: Pertemuan < 14 (Belum Selesai) -->
              <template v-else>
                <div class="grid grid-cols-2 gap-2">
                  <!-- Button Isi Kontrak (route depending on whether it has meetings filled) -->
                  <a
                    v-if="(pertemuanCounts[jadwal.id] || 0) > 0"
                    :href="route('v2.dosen.kontrak.edit', jadwal.id)"
                    class="w-full"
                  >
                    <Button
                      class="w-full bg-[#4B49AC] hover:bg-[#3f3e91] text-white shadow-sm flex items-center justify-center gap-1"
                    >
                      <Pencil class="w-4 h-4" />
                      Isi Kontrak
                    </Button>
                  </a>
                  <a
                    v-else
                    :href="route('v2.dosen.kontrak.create', jadwal.id)"
                    class="w-full"
                  >
                    <Button
                      class="w-full bg-[#4B49AC] hover:bg-[#3f3e91] text-white shadow-sm flex items-center justify-center gap-1"
                    >
                      <PlusCircle class="w-4 h-4" />
                      Isi Kontrak
                    </Button>
                  </a>

                  <!-- Button Import Kontrak -->
                  <Button
                    variant="outline"
                    class="w-full border-slate-200 text-slate-700 hover:bg-slate-50 flex items-center justify-center gap-1"
                    @click="openImportDialog(jadwal)"
                  >
                    <Upload class="w-4 h-4" />
                    Import
                  </Button>
                </div>
              </template>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Empty State -->
      <Card v-if="jadwals.length === 0" class="border-dashed border-2 bg-transparent">
        <CardContent class="p-12 text-center">
          <div class="flex flex-col items-center gap-3">
            <ClipboardList class="w-12 h-12 text-slate-300" />
            <h3 class="text-lg font-medium text-slate-700">Belum Ada Jadwal Mengajar</h3>
            <p class="text-slate-400 text-sm max-w-sm">
              Anda tidak memiliki jadwal mengajar terdaftar pada sistem untuk semester ini.
            </p>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Import Dialog Modal -->
    <Dialog :open="isImportDialogOpen" @update:open="isImportDialogOpen = $event">
      <DialogContent class="sm:max-w-[425px] overflow-hidden p-0">
        <div class="bg-[#4B49AC] text-white p-6">
          <DialogTitle class="text-white text-lg font-bold">Import Kontrak Perkuliahan</DialogTitle>
          <DialogDescription class="text-slate-100 text-xs mt-1">
            Unggah berkas Excel format kontrak perkuliahan untuk kelas yang dipilih.
          </DialogDescription>
        </div>

        <form @submit.prevent="submitImport" class="p-6 space-y-4">
          <div class="space-y-1.5" v-if="selectedJadwal">
            <Label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Mata Kuliah</Label>
            <p class="text-sm font-bold text-slate-800 line-clamp-1">{{ selectedJadwal.matkul?.nama_matkul }}</p>
            <p class="text-xs text-slate-400">Kelas: {{ selectedJadwal.kelas?.nama_kelas }}</p>
          </div>

          <div class="space-y-2">
            <Label for="file" class="text-xs font-bold text-slate-500 uppercase tracking-wider">Pilih File Excel</Label>
            <Input
              id="file"
              type="file"
              accept=".xls,.xlsx"
              @change="handleFileChange"
              required
              class="w-full text-slate-700 border-slate-200 focus-visible:ring-[#4B49AC]"
            />
            <p v-if="importForm.errors.file" class="text-xs text-red-500">{{ importForm.errors.file }}</p>
          </div>

          <div class="pt-2">
            <a
              :href="route('download.format.kontrak')"
              class="flex items-center justify-center gap-2 text-xs font-semibold text-[#4B49AC] hover:text-[#3f3e91] bg-indigo-50/50 hover:bg-indigo-50 border border-indigo-100 py-2.5 rounded-lg transition-colors w-full"
            >
              <Download class="w-4 h-4" />
              Download Format Template Import
            </a>
          </div>

          <Separator class="my-4" />

          <DialogFooter class="gap-2">
            <Button
              type="button"
              variant="outline"
              @click="isImportDialogOpen = false"
              :disabled="importForm.processing"
              class="border-slate-200 text-slate-700"
            >
              Batal
            </Button>
            <Button
              type="submit"
              class="bg-[#4B49AC] hover:bg-[#3f3e91] text-white flex items-center gap-1.5"
              :disabled="importForm.processing || !importForm.file"
            >
              <Upload class="w-4 h-4" />
              {{ importForm.processing ? 'Sedang Mengunggah...' : 'Unggah Kontrak' }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Toast Notification -->
    <transition name="toast">
      <div v-if="showToast" class="fixed bottom-10 right-10 z-[100]">
        <div class="bg-[#1F2937] text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-3 border border-gray-700 backdrop-blur-sm bg-opacity-95">
          <div :class="toastType === 'error' ? 'bg-red-500' : 'bg-green-500'" class="p-1.5 rounded-full">
            <CheckCircle2 v-if="toastType === 'success'" class="w-4 h-4 text-white" />
            <AlertCircle v-else class="w-4 h-4 text-white" />
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
