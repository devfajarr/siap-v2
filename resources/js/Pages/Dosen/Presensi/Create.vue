<script setup>
import { computed, watch, ref } from 'vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Label } from '@/Components/ui/label'
import { Separator } from '@/Components/ui/separator'
import { Alert, AlertDescription, AlertTitle } from '@/Components/ui/alert'
import { 
  ClipboardCheck, 
  ArrowLeft,
  Users,
  Calendar,
  Save,
  FileText,
  AlertCircle,
  CheckCircle2,
  BookOpen,
  MapPin,
  Clock,
  Sparkles
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
  jumlahTidakHadir: 0
})

// Attendance statistics
const stats = computed(() => {
  let hadir = 0
  let tidakHadir = 0
  let belumDiisi = 0
  
  props.mahasiswas.forEach(m => {
    const s = form.status[m.id]
    if (s === 'H' || s === 'T') {
      hadir++
    } else if (s === '') {
      belumDiisi++
    } else {
      tidakHadir++
    }
  })
  
  return { hadir, tidakHadir, belumDiisi }
})

// Submit store
const submit = () => {
  form.jumlahHadir = stats.value.hadir
  form.jumlahTidakHadir = stats.value.tidakHadir
  form.post(route('v2.dosen.presensi.store'), {
    preserveScroll: true,
    onSuccess: () => {
      // redirected to index
    }
  })
}

const markAllAsHadir = () => {
  props.mahasiswas.forEach(m => {
    form.status[m.id] = 'H'
  })
}

const statusOptions = [
  { 
    value: 'H', 
    label: 'Hadir', 
    activeClass: 'bg-emerald-600 text-white border-emerald-600 shadow-sm', 
    inactiveClass: 'bg-white text-slate-600 border-slate-200 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-300' 
  },
  { 
    value: 'T', 
    label: 'Terlambat', 
    activeClass: 'bg-blue-600 text-white border-blue-600 shadow-sm', 
    inactiveClass: 'bg-white text-slate-600 border-slate-200 hover:bg-blue-50 hover:text-blue-700 hover:border-blue-300' 
  },
  { 
    value: 'I', 
    label: 'Izin', 
    activeClass: 'bg-amber-500 text-white border-amber-500 shadow-sm', 
    inactiveClass: 'bg-white text-slate-600 border-slate-200 hover:bg-amber-50 hover:text-amber-700 hover:border-amber-200' 
  },
  { 
    value: 'S', 
    label: 'Sakit', 
    activeClass: 'bg-orange-500 text-white border-orange-500 shadow-sm', 
    inactiveClass: 'bg-white text-slate-600 border-slate-200 hover:bg-orange-50 hover:text-orange-700 hover:border-orange-200' 
  },
  { 
    value: 'A', 
    label: 'Alpha', 
    activeClass: 'bg-rose-600 text-white border-rose-600 shadow-sm', 
    inactiveClass: 'bg-white text-slate-600 border-slate-200 hover:bg-rose-50 hover:text-rose-700 hover:border-rose-300' 
  },
  { 
    value: 'C', 
    label: 'Cabut', 
    activeClass: 'bg-slate-600 text-white border-slate-600 shadow-sm', 
    inactiveClass: 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50 hover:text-slate-700 hover:border-slate-300' 
  },
]
</script>

<template>
  <AdminLayout>
    <Head title="Input Presensi Mahasiswa" />

    <div class="space-y-6 max-w-6xl mx-auto">
      <!-- Validation Errors -->
      <div v-if="Object.keys(form.errors).length > 0" class="space-y-4">
        <Alert variant="destructive" class="border-2 shadow-sm">
          <AlertCircle class="h-4 w-4" />
          <AlertTitle>Kesalahan Validasi</AlertTitle>
          <AlertDescription>
            <ul class="list-disc list-inside mt-2 text-xs">
              <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
            </ul>
          </AlertDescription>
        </Alert>
      </div>

      <!-- Header Navigation -->
      <div class="flex items-center gap-3">
        <Link :href="route('v2.dosen.presensi.index')">
          <Button variant="outline" size="icon" class="rounded-full shadow-sm hover:bg-slate-100">
            <ArrowLeft class="w-4 h-4 text-slate-700" />
          </Button>
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
            Input Presensi Mahasiswa
          </h1>
          <p class="text-slate-500 text-sm mt-0.5">
            Buat data presensi baru untuk Pertemuan ke-{{ pertemuan }} - {{ jadwal.matkul_nama }}
          </p>
        </div>
      </div>

      <!-- Course Info Summary Header -->
      <Card class="border-none shadow-sm bg-gradient-to-br from-indigo-50/50 to-indigo-100/30">
        <CardContent class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-3">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-[#4B49AC] text-white rounded-lg shadow-sm">
                  <BookOpen class="w-5 h-5" />
                </div>
                <div>
                  <h3 class="text-base font-bold text-slate-800">{{ jadwal.matkul_nama }}</h3>
                  <p class="text-xs font-mono text-[#4B49AC] font-bold">PERTEMUAN KE-{{ pertemuan }}</p>
                </div>
              </div>

              <div class="text-sm text-slate-600 space-y-1.5 pl-1">
                <p>Dosen Pengampu: <strong class="text-slate-800">{{ jadwal.dosen_nama }}</strong></p>
                <p>Program Studi: <strong class="text-slate-800">{{ jadwal.prodi_nama }}</strong></p>
              </div>
            </div>

            <div class="flex flex-col justify-between md:items-end text-sm text-slate-600 space-y-2 md:space-y-0">
              <div class="md:text-right">
                <p>Kelas: <strong class="text-slate-800">{{ jadwal.kelas_nama }}</strong></p>
                <p>Tahun Akademik: <strong class="text-[#4B49AC]">{{ jadwal.tahun_akademik }}</strong></p>
              </div>

              <div class="flex gap-2">
                <Badge variant="secondary" class="bg-indigo-50 text-[#4B49AC] border border-indigo-100 font-semibold">
                  Tanggal: {{ jadwal.tanggal }}
                </Badge>
                <Badge variant="secondary" class="bg-slate-50 text-slate-700 border border-slate-200 font-semibold">
                  Total Mahasiswa: {{ mahasiswas.length }}
                </Badge>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Main Columns -->
      <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        <!-- Left Side: Student List Table (2 Columns wide) -->
        <Card class="border-none shadow-sm lg:col-span-2 overflow-hidden">
          <CardHeader class="flex flex-row items-center justify-between pb-4 border-b border-slate-100 bg-slate-50/50">
            <div class="flex items-center gap-2">
              <Users class="w-5 h-5 text-[#4B49AC]" />
              <div>
                <CardTitle class="text-base font-bold text-slate-800">Daftar Kehadiran</CardTitle>
                <CardDescription class="text-xs">Tentukan status kehadiran untuk masing-masing mahasiswa.</CardDescription>
              </div>
            </div>
            <Button 
              type="button" 
              variant="outline" 
              size="sm"
              class="text-[#4B49AC] border-[#4B49AC] hover:bg-[#4B49AC] hover:text-white font-semibold transition-all"
              @click="markAllAsHadir"
            >
              Hadir Semua
            </Button>
          </CardHeader>
          
          <CardContent class="p-0">
            <div v-if="mahasiswas.length > 0" class="overflow-x-auto">
              <table class="w-full text-sm text-left text-slate-500 min-w-[550px]">
                <thead class="text-xs text-slate-400 uppercase bg-slate-50/50 border-b border-slate-100">
                  <tr>
                    <th scope="col" class="px-4 py-3 text-center w-12">No</th>
                    <th scope="col" class="px-4 py-3">Mahasiswa</th>
                    <th scope="col" class="px-4 py-3 text-center w-60">Status Kehadiran</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <tr v-for="(mhs, index) in mahasiswas" :key="mhs.id" class="hover:bg-slate-50/30 transition-colors">
                    <td class="px-4 py-4 text-center font-mono text-xs text-slate-400">{{ index + 1 }}</td>
                    <td class="px-4 py-4">
                      <div class="font-bold text-slate-800 leading-snug">{{ mhs.nama_lengkap }}</div>
                      <div class="text-xs font-mono text-slate-400 mt-0.5 tracking-wider">{{ mhs.nim }}</div>
                    </td>
                    <td class="px-4 py-4">
                      <div class="flex justify-center">
                        <!-- Segmented Button Choices -->
                        <div class="flex gap-1 bg-slate-100 p-1 rounded-lg border border-slate-200">
                          <button
                            v-for="opt in statusOptions"
                            :key="opt.value"
                            type="button"
                            @click="form.status[mhs.id] = opt.value"
                            class="px-2.5 py-1 text-xs font-bold rounded-md border border-transparent transition-all duration-150"
                            :class="form.status[mhs.id] === opt.value ? opt.activeClass : opt.inactiveClass"
                          >
                            {{ opt.label }}
                          </button>
                        </div>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Empty State -->
            <div v-else class="p-12 text-center text-slate-400">
              <AlertCircle class="w-12 h-12 mx-auto mb-2 opacity-20 text-[#4B49AC]" />
              <h3 class="text-base font-bold text-slate-700">Belum Ada Mahasiswa</h3>
              <p class="text-sm text-slate-400">Belum ada mahasiswa yang terdaftar atau melunasi KRS di kelas ini.</p>
            </div>
          </CardContent>
        </Card>

        <!-- Right Side: Berita Acara & Submit (1 Column wide) -->
        <div class="space-y-6">
          
          <!-- Statistics Widget -->
          <Card class="border-none shadow-sm">
            <CardHeader class="pb-3 border-b border-slate-100">
              <CardTitle class="text-sm font-bold text-slate-800 flex items-center gap-1.5">
                <Sparkles class="w-4 h-4 text-amber-500" />
                Ringkasan Kehadiran
              </CardTitle>
            </CardHeader>
            <CardContent class="p-4 grid grid-cols-3 gap-3 text-center">
              <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-100">
                <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider block">Hadir</span>
                <span class="text-xl font-extrabold text-emerald-700 block mt-0.5">{{ stats.hadir }}</span>
              </div>
              <div class="p-3 bg-rose-50 rounded-xl border border-rose-100">
                <span class="text-[10px] font-bold text-rose-600 uppercase tracking-wider block">Absen</span>
                <span class="text-xl font-extrabold text-rose-700 block mt-0.5">{{ stats.tidakHadir }}</span>
              </div>
              <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Belum</span>
                <span class="text-xl font-extrabold text-slate-600 block mt-0.5">{{ stats.belumDiisi }}</span>
              </div>
            </CardContent>
          </Card>

          <!-- Lecture Resume (Berita Acara) Form -->
          <Card class="border-none shadow-sm">
            <CardHeader class="pb-3 border-b border-slate-100">
              <div class="flex items-center gap-2">
                <FileText class="w-4 h-4 text-[#4B49AC]" />
                <CardTitle class="text-base font-bold text-slate-800">Berita Acara</CardTitle>
              </div>
            </CardHeader>
            <CardContent class="p-6 space-y-4">
              <div class="space-y-2">
                <Label for="materiResume" class="text-xs font-bold text-slate-500">Ikhtisar Materi Kuliah</Label>
                <textarea 
                  id="materiResume" 
                  v-model="form.materiResume" 
                  required
                  placeholder="Masukkan ringkasan pokok bahasan / materi yang diajarkan pada pertemuan ini..."
                  class="w-full min-h-[140px] p-3 rounded-lg border border-slate-200 text-sm text-slate-700 focus:ring-2 focus:ring-[#4B49AC] focus:border-transparent outline-none transition-all resize-none"
                ></textarea>
              </div>

              <Separator class="bg-slate-100" />

              <Button 
                type="submit" 
                class="w-full bg-[#4B49AC] hover:bg-[#3f3e91] text-white h-11 rounded-lg font-bold shadow-md transition-all flex items-center justify-center gap-2"
                :disabled="form.processing || stats.belumDiisi > 0"
              >
                <Save class="w-4 h-4" />
                {{ form.processing ? 'Menyimpan...' : 'Simpan Presensi' }}
              </Button>
              <p v-if="stats.belumDiisi > 0" class="text-[10px] text-rose-500 text-center font-medium">
                * Harap isi kehadiran seluruh mahasiswa sebelum menyimpan
              </p>
            </CardContent>
          </Card>
        </div>

      </form>
    </div>

    <!-- Toast Notification -->
    <transition name="toast">
      <div v-if="showToast" class="fixed bottom-10 right-10 z-[100]">
        <div :class="[
          'flex items-center gap-3 px-6 py-4 rounded-2xl shadow-2xl border transition-all duration-300',
          toastType === 'error' ? 'bg-white border-red-100 text-red-800' : 'bg-white border-green-100 text-green-800'
        ]">
          <div :class="toastType === 'error' ? 'bg-[#FF4747]' : 'bg-emerald-500'" class="p-1.5 rounded-full">
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
