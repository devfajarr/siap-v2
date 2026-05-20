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
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/Components/ui/select'
import { Label } from '@/Components/ui/label'
import { Separator } from '@/Components/ui/separator'
import { 
  ClipboardList, 
  BookOpen, 
  Users, 
  Calendar, 
  Clock, 
  MapPin,
  CheckCircle2,
  AlertCircle,
  Pencil,
  ChevronRight,
  Sparkles,
  PlusCircle,
  GraduationCap
} from 'lucide-vue-next'

const props = defineProps({
  jadwals: {
    type: Array,
    required: true
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

// Compute statistics
const totalClasses = computed(() => props.jadwals.length)
const completedPresensi = computed(() => 
  props.jadwals.filter(j => j.pertemuan_sekarang >= 14).length
)
const activePresensi = computed(() => 
  props.jadwals.filter(j => j.pertemuan_sekarang < 14).length
)
const approvedEditsCount = computed(() => 
  props.jadwals.filter(j => j.approved_edits && j.approved_edits.length > 0).length
)

// Request Edit state
const isDialogOpen = ref(false)
const selectedJadwal = ref(null)
const requestForm = useForm({
  jadwal_id: null,
  pertemuan: ''
})

const openRequestDialog = (jadwal) => {
  selectedJadwal.value = jadwal
  requestForm.jadwal_id = jadwal.id
  requestForm.pertemuan = ''
  isDialogOpen.value = true
}

const submitRequest = () => {
  requestForm.post(route('v2.dosen.presensi.request-edit'), {
    onSuccess: () => {
      isDialogOpen.value = false
    }
  })
}

// Generate meeting options (1 to current max)
const getMeetingOptions = (currentMax) => {
  const options = []
  for (let i = 1; i <= currentMax; i++) {
    options.push({ value: i.toString(), label: `Pertemuan ${i}` })
  }
  return options
}
</script>

<template>
  <AdminLayout>
    <Head title="Data Presensi Mahasiswa" />

    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
            <ClipboardList class="w-7 h-7 text-[#4B49AC]" />
            Presensi Mahasiswa
          </h1>
          <p class="text-slate-500 text-sm mt-1">
            Kelola daftar kehadiran mahasiswa untuk setiap mata kuliah yang Anda ampu.
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
              <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Presensi Selesai</p>
              <h3 class="text-xl font-extrabold text-slate-800 mt-0.5">{{ completedPresensi }}</h3>
            </div>
          </CardContent>
        </Card>

        <Card class="border-none shadow-sm bg-gradient-to-br from-blue-50/50 to-blue-100/30">
          <CardContent class="p-4 flex items-center gap-3">
            <div class="p-2.5 bg-blue-500 text-white rounded-xl shadow-sm">
              <BookOpen class="w-5 h-5" />
            </div>
            <div>
              <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Presensi Aktif</p>
              <h3 class="text-xl font-extrabold text-slate-800 mt-0.5">{{ activePresensi }}</h3>
            </div>
          </CardContent>
        </Card>

        <Card class="border-none shadow-sm bg-gradient-to-br from-amber-50/50 to-amber-100/30">
          <CardContent class="p-4 flex items-center gap-3">
            <div class="p-2.5 bg-amber-500 text-white rounded-xl shadow-sm">
              <Pencil class="w-5 h-5" />
            </div>
            <div>
              <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Akses Edit Aktif</p>
              <h3 class="text-xl font-extrabold text-slate-800 mt-0.5">{{ approvedEditsCount }}</h3>
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
                <CardTitle class="text-base font-bold text-slate-800 leading-snug line-clamp-2" :title="jadwal.matkul">
                  {{ jadwal.matkul }}
                </CardTitle>
                <span class="text-xs font-mono text-[#4B49AC] font-bold tracking-wider block mt-1">
                  SKS: {{ jadwal.sks || '-' }}
                </span>
              </div>

              <!-- Status Badge -->
              <div class="flex-shrink-0">
                <Badge 
                  v-if="jadwal.status_presensi === 'completed'" 
                  variant="secondary"
                  class="bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-50 shadow-none font-semibold px-2 py-0.5 rounded-full flex items-center gap-1 text-[11px]"
                >
                  <CheckCircle2 class="w-3.5 h-3.5" /> Selesai
                </Badge>
                <Badge 
                  v-else-if="jadwal.status_presensi === 'filled_today'" 
                  variant="outline"
                  class="bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-50 shadow-none font-semibold px-2 py-0.5 rounded-full flex items-center gap-1 text-[11px]"
                >
                  <CheckCircle2 class="w-3.5 h-3.5" /> Sudah Terisi
                </Badge>
                <Badge 
                  v-else 
                  variant="outline"
                  class="bg-orange-50 text-orange-700 border border-orange-200 hover:bg-orange-50 shadow-none font-semibold px-2 py-0.5 rounded-full flex items-center gap-1 text-[11px]"
                >
                  <AlertCircle class="w-3.5 h-3.5" /> Belum Diisi
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
                <span class="truncate">Kelas: <strong class="text-slate-800">{{ jadwal.kelas }}</strong></span>
              </div>
              <div class="flex items-center text-sm text-slate-600 gap-2">
                <BookOpen class="w-4 h-4 text-slate-400 flex-shrink-0" />
                <span class="truncate">Prodi: <strong class="text-slate-800">{{ jadwal.prodi }}</strong></span>
              </div>
              <div class="flex items-center text-sm text-slate-600 gap-2">
                <Calendar class="w-4 h-4 text-slate-400 flex-shrink-0" />
                <span>Hari: <strong class="text-slate-800">{{ jadwal.hari }}</strong></span>
              </div>
              <div class="flex items-center text-sm text-slate-600 gap-2">
                <Clock class="w-4 h-4 text-slate-400 flex-shrink-0" />
                <span>Waktu: <strong class="text-slate-800">{{ jadwal.waktu }}</strong></span>
              </div>
              <div class="flex items-center text-sm text-slate-600 gap-2">
                <MapPin class="w-4 h-4 text-slate-400 flex-shrink-0" />
                <span class="truncate">Ruangan: <strong class="text-slate-800">{{ jadwal.ruangan }}</strong></span>
              </div>
            </div>

            <!-- Progress Tracker -->
            <div class="space-y-1.5 pt-3 border-t border-slate-100">
              <div class="flex justify-between text-xs font-semibold text-slate-500">
                <span class="flex items-center gap-1">
                  <Sparkles class="w-3.5 h-3.5 text-amber-500" />
                  Progress Presensi
                </span>
                <span>{{ jadwal.pertemuan_sekarang }}/14 Pertemuan</span>
              </div>
              <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                <div 
                  class="bg-[#4B49AC] h-full rounded-full transition-all duration-500" 
                  :style="{ width: `${Math.min(Math.round((jadwal.pertemuan_sekarang / 14) * 100), 100)}%` }"
                ></div>
              </div>
            </div>

            <!-- Actions Wrapper -->
            <div class="pt-3 border-t border-slate-100 space-y-2">
              <div class="grid grid-cols-1 gap-2">
                <div v-if="jadwal.status_presensi === 'available'">
                  <Link :href="route('v2.dosen.presensi.create', jadwal.id)" class="w-full block">
                    <Button class="w-full bg-[#4B49AC] hover:bg-[#3f3e91] text-white shadow-sm flex items-center justify-center gap-1.5">
                      <PlusCircle class="w-4 h-4" />
                      Isi Presensi
                    </Button>
                  </Link>
                </div>
                <div v-else-if="jadwal.status_presensi === 'filled_today'">
                  <Link :href="route('v2.dosen.presensi.edit', [jadwal.id, jadwal.pertemuan_hari_ini])" class="w-full block">
                    <Button variant="outline" class="w-full border-[#4B49AC] text-[#4B49AC] hover:bg-[#4B49AC] hover:text-white flex items-center justify-center gap-1.5">
                      <Pencil class="w-4 h-4" />
                      Edit Presensi Hari Ini
                    </Button>
                  </Link>
                </div>
                <div v-else>
                  <Button variant="outline" class="w-full cursor-not-allowed bg-slate-50 text-slate-400 border-slate-200" disabled>
                    Pertemuan Selesai
                  </Button>
                </div>

                <!-- Request Edit Button -->
                <Button 
                  v-if="jadwal.pertemuan_sekarang >= 1"
                  variant="outline" 
                  size="sm"
                  class="w-full text-xs text-slate-600 border-slate-200 hover:bg-slate-50 flex items-center justify-center gap-1.5 mt-1"
                  @click="openRequestDialog(jadwal)"
                >
                  <AlertCircle class="w-3.5 h-3.5 text-slate-400" />
                  Ajukan Edit Pertemuan Lalu
                </Button>
              </div>

              <!-- Approved Edits List -->
              <div v-if="jadwal.approved_edits && jadwal.approved_edits.length > 0" class="pt-3 border-t border-slate-100">
                <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider mb-2 flex items-center gap-1">
                  <CheckCircle2 class="w-3.5 h-3.5" /> Akses Edit Terbuka:
                </p>
                <div class="flex flex-wrap gap-2">
                  <Link 
                    v-for="edit in jadwal.approved_edits" 
                    :key="edit.id"
                    :href="route('v2.dosen.presensi.edit', [jadwal.id, edit.pertemuan])"
                  >
                    <Button size="sm" class="h-8 text-[11px] bg-emerald-600 hover:bg-emerald-700 text-white flex items-center gap-1 shadow-sm">
                      Edit Pertemuan {{ edit.pertemuan }} <ChevronRight class="w-3 h-3" />
                    </Button>
                  </Link>
                </div>
              </div>
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
            <p class="text-slate-400 text-sm max-w-sm">Anda tidak memiliki jadwal mengajar terdaftar pada sistem.</p>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Request Edit Dialog -->
    <Dialog :open="isDialogOpen" @update:open="isDialogOpen = $event">
      <DialogContent class="sm:max-w-[425px] overflow-hidden p-0">
        <div class="bg-[#4B49AC] text-white p-6">
          <DialogTitle class="text-white text-lg font-bold">Ajukan Edit Presensi</DialogTitle>
          <DialogDescription class="text-slate-100 text-xs mt-1">
            Pilih nomor pertemuan yang ingin Anda perbaiki datanya. Pengajuan ini akan diteruskan ke Admin untuk disetujui.
          </DialogDescription>
        </div>

        <div class="p-6 space-y-4" v-if="selectedJadwal">
          <div class="space-y-1">
            <Label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Mata Kuliah</Label>
            <p class="text-sm font-bold text-slate-800 line-clamp-1">{{ selectedJadwal.matkul }}</p>
            <p class="text-xs text-slate-400">Kelas: {{ selectedJadwal.kelas }}</p>
          </div>

          <div class="space-y-2">
            <Label for="pertemuan" class="text-xs font-bold text-slate-500 uppercase tracking-wider">Pilih Pertemuan</Label>
            <Select v-model="requestForm.pertemuan">
              <SelectTrigger id="pertemuan" class="w-full border-slate-200 focus:ring-[#4B49AC]">
                <SelectValue placeholder="Pilih nomor pertemuan" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem 
                  v-for="opt in getMeetingOptions(selectedJadwal.pertemuan_sekarang)" 
                  :key="opt.value" 
                  :value="opt.value"
                >
                  {{ opt.label }}
                </SelectItem>
              </SelectContent>
            </Select>
            <p v-if="requestForm.errors.pertemuan" class="text-xs text-red-500">{{ requestForm.errors.pertemuan }}</p>
          </div>

          <Separator class="my-4" />

          <DialogFooter class="gap-2 sm:gap-0">
            <Button 
              type="button" 
              variant="outline" 
              @click="isDialogOpen = false"
              :disabled="requestForm.processing"
              class="border-slate-200 text-slate-700"
            >
              Batal
            </Button>
            <Button 
              type="button" 
              class="bg-[#4B49AC] hover:bg-[#3f3e91] text-white flex items-center gap-1.5"
              @click="submitRequest"
              :disabled="requestForm.processing || !requestForm.pertemuan"
            >
              <Send class="w-4 h-4" />
              {{ requestForm.processing ? 'Mengirim...' : 'Kirim Pengajuan' }}
            </Button>
          </DialogFooter>
        </div>
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
