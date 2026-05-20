<script setup>
import { computed, watch, ref } from 'vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Label } from '@/Components/ui/label'
import { Input } from '@/Components/ui/input'
import { Separator } from '@/Components/ui/separator'
import { RadioGroup, RadioGroupItem } from '@/Components/ui/radio-group'
import { Alert, AlertDescription, AlertTitle } from '@/Components/ui/alert'
import { 
  ClipboardCheck, 
  ArrowLeft,
  Users,
  Calendar,
  Save,
  FileText,
  AlertCircle,
  CheckCircle2
} from 'lucide-vue-next'

const props = defineProps({
  jadwal: {
    type: Object,
    required: true
  },
  pertemuan: {
    type: Number,
    required: true
  },
  mahasiswas: {
    type: Array,
    required: true
  }
})

const page = usePage()

// Toast state
const showToast = ref(false)
const toastMessage = ref('')
const toastType = ref('success')

// Watch for flash messages to show toast
watch(() => page.props.flash, (flash) => {
  if (flash.success) {
    toastMessage.value = flash.success
    toastType.value = 'success'
    showToast.value = true
    setTimeout(() => { showToast.value = false }, 3000)
  } else if (flash.error) {
    toastMessage.value = flash.error
    toastType.value = 'error'
    showToast.value = true
    setTimeout(() => { showToast.value = false }, 3000)
  }
}, { deep: true, immediate: true })

// Initialize form
const form = useForm({
  pertemuan: props.pertemuan,
  jadwals_id: props.jadwal.id,
  matkuls_id: props.jadwal.matkuls_id,
  dosens_id: props.jadwal.dosens_id,
  prodis_id: props.jadwal.prodis_id,
  kelas_id: props.jadwal.kelas_id,
  tahun: props.jadwal.tahun_akademik,
  status: props.mahasiswas.reduce((acc, m) => {
    acc[m.id] = '' // empty initial status
    return acc
  }, {}),
  materiResume: '',
  jumlahHadir: 0,
  jumlahAbstain: 0, // This is just for local tracking
  jumlahTidakHadir: 0
})

// Attendance statistics
const stats = computed(() => {
  let hadir = 0
  let tidakHadir = 0
  let abstain = 0
  
  Object.values(form.status).forEach(s => {
    if (s === 'H' || s === 'T') {
      hadir++
    } else if (s === '') {
      abstain++
    } else {
      tidakHadir++
    }
  })
  
  return { hadir, tidakHadir, abstain }
})

// Update hidden counts whenever stats change
const submit = () => {
  form.jumlahHadir = stats.value.hadir
  form.jumlahTidakHadir = stats.value.tidakHadir
  form.post(route('v2.dosen.presensi.store'), {
    preserveScroll: true,
    onError: (errors) => {
      console.log(errors)
    }
  })
}

const markAllAsHadir = () => {
  props.mahasiswas.forEach(m => {
    form.status[m.id] = 'H'
  })
}

const statusOptions = [
  { value: 'H', label: 'Hadir', color: 'text-green-600' },
  { value: 'T', label: 'Terlambat', color: 'text-blue-600' },
  { value: 'I', label: 'Izin', color: 'text-yellow-600' },
  { value: 'S', label: 'Sakit', color: 'text-orange-600' },
  { value: 'A', label: 'Alpha', color: 'text-red-600' },
  { value: 'C', label: 'Cabut', color: 'text-gray-600' },
]
</script>

<template>
  <AdminLayout>
    <Head title="Isi Presensi" />

    <div class="space-y-6">
      <!-- Validation Errors -->
      <div v-if="Object.keys(form.errors).length > 0" class="space-y-4">
        <Alert variant="destructive" class="border-2 shadow-lg">
          <AlertCircle class="h-4 w-4" />
          <AlertTitle>Kesalahan Validasi</AlertTitle>
          <AlertDescription>
            <ul class="list-disc list-inside mt-2 text-xs">
              <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
            </ul>
          </AlertDescription>
        </Alert>
      </div>

      <!-- Header -->
      <div class="flex items-center gap-4">
        <Button 
          variant="outline" 
          size="icon" 
          class="rounded-full"
          @click="$window.history.back()"
        >
          <ArrowLeft class="w-4 h-4" />
        </Button>
        <div>
          <h1 class="text-2xl font-bold text-[#1F2937]">Input Presensi Mahasiswa</h1>
          <p class="text-[#6B7280]">Pertemuan ke-{{ pertemuan }} - {{ jadwal.matkul_nama }}</p>
        </div>
      </div>

      <!-- Info Card -->
      <Card class="border-none shadow-sm bg-[#4B49AC] text-white">
        <CardContent class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
            <div class="space-y-2">
              <div class="flex justify-between border-b border-white/20 pb-1">
                <span class="opacity-80">Mata Kuliah</span>
                <span class="font-semibold">{{ jadwal.matkul_nama }}</span>
              </div>
              <div class="flex justify-between border-b border-white/20 pb-1">
                <span class="opacity-80">Dosen</span>
                <span class="font-semibold">{{ jadwal.dosen_nama }}</span>
              </div>
              <div class="flex justify-between">
                <span class="opacity-80">Pertemuan Ke</span>
                <span class="font-semibold">{{ pertemuan }}</span>
              </div>
            </div>
            <div class="space-y-2">
              <div class="flex justify-between border-b border-white/20 pb-1">
                <span class="opacity-80">Program Studi</span>
                <span class="font-semibold">{{ jadwal.prodi_nama }}</span>
              </div>
              <div class="flex justify-between border-b border-white/20 pb-1">
                <span class="opacity-80">Kelas</span>
                <span class="font-semibold">{{ jadwal.kelas_nama }}</span>
              </div>
              <div class="flex justify-between">
                <span class="opacity-80">Tanggal</span>
                <span class="font-semibold">{{ jadwal.tanggal }}</span>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <form @submit.prevent="submit" class="space-y-6">
        <!-- Attendance List -->
        <Card class="border-none shadow-sm">
          <CardHeader class="flex flex-row items-center justify-between pb-2 border-b">
            <div class="flex items-center gap-2">
              <Users class="w-5 h-5 text-[#4B49AC]" />
              <CardTitle class="text-lg">Daftar Mahasiswa</CardTitle>
            </div>
            <Button 
              type="button" 
              variant="outline" 
              size="sm"
              class="text-[#4B49AC] border-[#4B49AC] hover:bg-[#4B49AC] hover:text-white"
              @click="markAllAsHadir"
            >
              Hadir Semua
            </Button>
          </CardHeader>
          <CardContent class="p-0">
            <div class="divide-y">
              <div v-for="mhs in mahasiswas" :key="mhs.id" class="p-4 space-y-3 hover:bg-slate-50 transition-colors">
                <div class="flex justify-between items-start">
                  <div class="space-y-1">
                    <h4 class="font-bold text-[#374151]">{{ mhs.nama_lengkap }}</h4>
                    <p class="text-xs text-[#6B7280] font-mono tracking-wider">{{ mhs.nim }}</p>
                  </div>
                </div>
                
                <RadioGroup v-model="form.status[mhs.id]" class="flex flex-wrap gap-4 md:gap-6 pt-1">
                  <div v-for="opt in statusOptions" :key="opt.value" class="flex items-center space-x-2">
                    <RadioGroupItem :id="`status-${mhs.id}-${opt.value}`" :value="opt.value" />
                    <Label :for="`status-${mhs.id}-${opt.value}`" :class="['text-sm font-medium cursor-pointer', opt.color]">
                      {{ opt.label }}
                    </Label>
                  </div>
                </RadioGroup>
              </div>
            </div>

            <div v-if="mahasiswas.length === 0" class="p-12 text-center text-[#9CA3AF]">
              <AlertCircle class="w-12 h-12 mx-auto mb-2 opacity-20" />
              <p>Belum ada mahasiswa terdaftar di kelas ini.</p>
            </div>
          </CardContent>
        </Card>

        <!-- Berita Acara -->
        <Card class="border-none shadow-sm">
          <CardHeader class="flex flex-row items-center gap-2 pb-2 border-b">
            <FileText class="w-5 h-5 text-[#4B49AC]" />
            <CardTitle class="text-lg">Berita Acara Perkuliahan</CardTitle>
          </CardHeader>
          <CardContent class="p-6 space-y-6">
            <div class="space-y-2">
              <Label for="materiResume" class="text-sm font-bold text-[#374151]">Ikhtisar Materi Kuliah</Label>
              <textarea 
                id="materiResume" 
                v-model="form.materiResume" 
                required
                placeholder="Masukkan ringkasan materi yang diajarkan hari ini..."
                class="w-full min-h-[100px] p-3 rounded-md border border-[#E5E7EB] focus:ring-2 focus:ring-[#4B49AC] focus:border-transparent outline-none transition-all"
              ></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="p-4 bg-green-50 rounded-lg border border-green-100 flex flex-col items-center">
                <span class="text-xs font-bold text-green-600 uppercase tracking-wider mb-1">Total Hadir</span>
                <span class="text-3xl font-black text-green-700">{{ stats.hadir }}</span>
              </div>
              <div class="p-4 bg-red-50 rounded-lg border border-red-100 flex flex-col items-center">
                <span class="text-xs font-bold text-red-600 uppercase tracking-wider mb-1">Tidak Hadir</span>
                <span class="text-3xl font-black text-red-700">{{ stats.tidakHadir }}</span>
              </div>
            </div>

            <Separator />

            <div class="flex justify-end pt-2">
              <Button 
                type="submit" 
                class="bg-[#4B49AC] hover:bg-[#3f3e91] text-white px-8 h-11 rounded-lg font-bold shadow-lg shadow-indigo-200"
                :disabled="form.processing"
              >
                <Save v-if="!form.processing" class="w-4 h-4 mr-2" />
                <span v-if="form.processing">Menyimpan...</span>
                <span v-else>Simpan Presensi</span>
              </Button>
            </div>
          </CardContent>
        </Card>
      </form>
    </div>

    <!-- Toast Notification -->
    <transition name="toast">
      <div v-if="showToast" class="fixed bottom-10 right-10 z-[100]">
        <div :class="[
          'flex items-center gap-3 px-6 py-4 rounded-2xl shadow-2xl border transition-all duration-300',
          toastType === 'error' ? 'bg-white border-red-100 text-red-800' : 'bg-white border-green-100 text-green-800'
        ]">
          <div :class="toastType === 'error' ? 'bg-red-500' : 'bg-green-500'" class="p-1.5 rounded-full">
            <CheckCircle2 v-if="toastType === 'success'" class="w-4 h-4 text-white" />
            <AlertCircle v-else class="w-4 h-4 text-white" />
          </div>
          <span class="text-sm font-bold tracking-tight">{{ toastMessage }}</span>
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
  transform: translateX(100px);
}
</style>
