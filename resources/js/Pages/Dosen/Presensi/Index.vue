<script setup>
import { ref, watch } from 'vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
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
  ChevronRight
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
    <Head title="Data Presensi" />

    <div class="space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-start">
        <div>
          <h1 class="text-2xl font-bold text-[#1F2937]">Data Presensi</h1>
          <p class="text-[#6B7280]">Kelola daftar kehadiran mahasiswa untuk setiap mata kuliah yang Anda ampu.</p>
        </div>
      </div>

      <!-- Jadwal Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <Card v-for="jadwal in jadwals" :key="jadwal.id" class="border-none shadow-md overflow-hidden flex flex-col">
          <CardHeader class="border-b p-4 bg-white">
            <div class="flex items-center gap-3">
              <div class="p-2 bg-[#4B49AC] text-white rounded-lg">
                <ClipboardList class="w-5 h-5 text-white" />
              </div>
              <CardTitle class="text-base font-bold text-[#1F2937] truncate">{{ jadwal.matkul }}</CardTitle>
            </div>
          </CardHeader>
          <CardContent class="p-5 space-y-4 flex-1">
            <div class="space-y-2">
              <div class="flex items-center text-sm text-[#6B7280]">
                <Users class="w-4 h-4 mr-2" />
                <span>Kelas: <strong>{{ jadwal.kelas }}</strong></span>
              </div>
              <div class="flex items-center text-sm text-[#6B7280]">
                <BookOpen class="w-4 h-4 mr-2" />
                <span>Prodi: <strong>{{ jadwal.prodi }}</strong></span>
              </div>
              <div class="flex items-center text-sm text-[#6B7280]">
                <Calendar class="w-4 h-4 mr-2" />
                <span>Hari: <strong>{{ jadwal.hari }}</strong></span>
              </div>
              <div class="flex items-center text-sm text-[#6B7280]">
                <Clock class="w-4 h-4 mr-2" />
                <span>Waktu: <strong>{{ jadwal.waktu }}</strong></span>
              </div>
              <div class="flex items-center text-sm text-[#6B7280]">
                <MapPin class="w-4 h-4 mr-2" />
                <span>Ruangan: <strong>{{ jadwal.ruangan }}</strong></span>
              </div>
            </div>

            <div class="pt-4 border-t space-y-3 mt-auto">
              <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-[#9CA3AF]">Pertemuan: {{ jadwal.pertemuan_sekarang }} / 14</span>
                <Badge 
                  v-if="jadwal.status_presensi === 'completed'" 
                  variant="secondary"
                  class="bg-green-100 text-green-700 hover:bg-green-100"
                >
                  <CheckCircle2 class="w-3 h-3 mr-1" /> Selesai
                </Badge>
                <Badge 
                  v-else-if="jadwal.status_presensi === 'filled_today'" 
                  variant="outline"
                  class="text-blue-600 border-blue-200 bg-blue-50"
                >
                  <CheckCircle2 class="w-3 h-3 mr-1" /> Sudah Terisi
                </Badge>
                <Badge 
                  v-else 
                  variant="outline"
                  class="text-orange-600 border-orange-200 bg-orange-50"
                >
                  <AlertCircle class="w-3 h-3 mr-1" /> Belum Diisi
                </Badge>
              </div>

              <!-- Main Actions -->
              <div class="grid grid-cols-1 gap-2">
                <div v-if="jadwal.status_presensi === 'available'">
                  <Link :href="route('v2.dosen.presensi.create', jadwal.id)">
                    <Button class="w-full bg-[#4B49AC] hover:bg-[#3f3e91] text-white">
                      Isi Presensi
                    </Button>
                  </Link>
                </div>
                <div v-else-if="jadwal.status_presensi === 'filled_today'">
                  <Link :href="route('v2.dosen.presensi.edit', [jadwal.id, jadwal.pertemuan_hari_ini])">
                    <Button variant="outline" class="w-full border-[#4B49AC] text-[#4B49AC] hover:bg-[#4B49AC] hover:text-white">
                      <Pencil class="w-4 h-4 mr-2" /> Edit Presensi Hari Ini
                    </Button>
                  </Link>
                </div>
                <div v-else>
                  <Button variant="outline" class="w-full" disabled>
                    Pertemuan Selesai
                  </Button>
                </div>

                <!-- Request Edit Button -->
                <Button 
                  v-if="jadwal.pertemuan_sekarang >= 1"
                  variant="secondary" 
                  size="sm"
                  class="w-full text-xs"
                  @click="openRequestDialog(jadwal)"
                >
                  <AlertCircle class="w-3 h-3 mr-2" /> Ajukan Edit Pertemuan Lalu
                </Button>
              </div>

              <!-- Approved Edits List -->
              <div v-if="jadwal.approved_edits && jadwal.approved_edits.length > 0" class="pt-2">
                <p class="text-[10px] font-bold text-green-600 uppercase tracking-wider mb-2 flex items-center">
                  <CheckCircle2 class="w-3 h-3 mr-1" /> Akses Edit Terbuka:
                </p>
                <div class="flex flex-wrap gap-2">
                  <Link 
                    v-for="edit in jadwal.approved_edits" 
                    :key="edit.id"
                    :href="route('v2.dosen.presensi.edit', [jadwal.id, edit.pertemuan])"
                  >
                    <Button size="sm" variant="success" class="h-7 text-[10px] bg-green-600 hover:bg-green-700 text-white">
                      Edit Pertemuan {{ edit.pertemuan }} <ChevronRight class="w-3 h-3 ml-1" />
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
          <div class="flex flex-col items-center gap-2">
            <ClipboardList class="w-12 h-12 text-[#D1D5DB]" />
            <h3 class="text-lg font-medium text-[#374151]">Belum Ada Jadwal</h3>
            <p class="text-[#6B7280]">Anda belum memiliki jadwal mengajar yang terdaftar.</p>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Request Edit Dialog -->
    <Dialog :open="isDialogOpen" @update:open="isDialogOpen = $event">
      <DialogContent class="sm:max-w-[425px]">
        <DialogHeader>
          <DialogTitle class="text-[#4B49AC]">Ajukan Edit Presensi</DialogTitle>
          <DialogDescription>
            Pilih nomor pertemuan yang ingin Anda perbaiki datanya. Pengajuan ini akan diteruskan ke Admin untuk disetujui.
          </DialogDescription>
        </DialogHeader>
        <div class="grid gap-4 py-4" v-if="selectedJadwal">
          <div class="space-y-2">
            <Label class="text-xs font-bold text-[#6B7280]">Mata Kuliah</Label>
            <p class="text-sm font-semibold">{{ selectedJadwal.matkul }}</p>
          </div>
          <div class="space-y-2">
            <Label for="pertemuan" class="text-xs font-bold text-[#6B7280]">Pilih Pertemuan</Label>
            <Select v-model="requestForm.pertemuan">
              <SelectTrigger id="pertemuan">
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
        </div>
        <DialogFooter>
          <Button 
            type="button" 
            variant="outline" 
            @click="isDialogOpen = false"
            :disabled="requestForm.processing"
          >
            Batal
          </Button>
          <Button 
            type="button" 
            class="bg-[#4B49AC] hover:bg-[#3f3e91]"
            @click="submitRequest"
            :disabled="requestForm.processing || !requestForm.pertemuan"
          >
            {{ requestForm.processing ? 'Mengirim...' : 'Kirim Pengajuan' }}
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
