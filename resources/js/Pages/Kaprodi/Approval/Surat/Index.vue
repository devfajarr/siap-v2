<script setup>
import { ref, watch } from 'vue'
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardContent } from '@/Components/ui/card'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table'
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
} from '@/Components/ui/dialog'
import { Badge } from '@/Components/ui/badge'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Textarea } from '@/Components/ui/textarea'
import { Label } from '@/Components/ui/label'
import { FileText, CheckCircle2, Clock, Search, ChevronLeft, ChevronRight, Mail, Printer, Eye, XCircle, RefreshCw, Loader2, AlertCircle } from 'lucide-vue-next'

const props = defineProps({
  permohonans: {
    type: Object,
    required: true
  },
  filters: {
    type: Object,
    default: () => ({ search: '', jenis: '' })
  },
  title: {
    type: String,
    required: true
  }
})

// Toast state
const showToast = ref(false)
const toastMessage = ref('')
const toastType = ref('success')

const page = usePage()

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
}, { deep: true, immediate: true })

const searchQuery = ref(props.filters.search || '')
const filterJenis = ref(props.filters.jenis || '')

let debounceTimer = null
const applyFilter = () => {
    const currentRoute = route().current('v2.kaprodi.permohonan-surat.diajukan') 
        ? route('v2.kaprodi.permohonan-surat.diajukan') 
        : route('v2.kaprodi.permohonan-surat.disetujui')

    router.get(currentRoute, {
        search: searchQuery.value,
        jenis: filterJenis.value
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    })
}

watch(searchQuery, () => {
    clearTimeout(debounceTimer)
    debounceTimer = setTimeout(() => {
        applyFilter()
    }, 400)
})

watch(filterJenis, () => {
    applyFilter()
})

// Modal Logic
const isModalOpen = ref(false)
const selectedPermohonan = ref(null)
const isRejecting = ref(false)
const isConfirmApproveModalOpen = ref(false)

const openDetailModal = (item) => {
    selectedPermohonan.value = item
    isRejecting.value = false
    formReject.keterangan_ditolak = ''
    isModalOpen.value = true
}

const openConfirmApproveModal = () => {
    isConfirmApproveModalOpen.value = true
}

const formApprove = useForm({
  id: null,
  jenis_permohonan: null
})

const formReject = useForm({
    keterangan_ditolak: ''
})

const submitApprove = () => {
    formApprove.id = selectedPermohonan.value.id
    formApprove.jenis_permohonan = selectedPermohonan.value.jenis_permohonan
    formApprove.post(route('v2.kaprodi.permohonan-surat.approve'), {
      preserveScroll: true,
      onSuccess: () => {
          isConfirmApproveModalOpen.value = false
          isModalOpen.value = false
      }
    })
}

const submitReject = () => {
    formReject.delete(route('v2.kaprodi.permohonan-surat.reject', selectedPermohonan.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            isModalOpen.value = false
        }
    })
}

const formatDate = (dateStr) => {
    if (!dateStr) return '-'
    const date = new Date(dateStr)
    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}

const daftarJenisPermohonan = [
    'Keterangan Aktif Kuliah',
    'Cuti Kuliah',
    'Pindah Kelas',
    'Pindah PT',
    'Mengundurkan Diri',
    'Ijin PKL',
    'Ijin Memperoleh Data PKL',
    'Ijin Memperoleh Data TA'
]
</script>

<template>
  <AdminLayout>
    <Head :title="title" />

    <div class="space-y-6">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div>
            <h1 class="text-2xl font-bold text-[#1F2937]">{{ title }}</h1>
            <p class="text-[#6B7280]">Kelola dan verifikasi permohonan surat mahasiswa Program Studi Anda.</p>
          </div>
          <Badge v-if="route().current('v2.kaprodi.permohonan-surat.diajukan')" variant="outline" class="bg-amber-50 text-amber-700 border-amber-200 font-semibold px-3 py-1 self-start md:self-center">
            <Clock class="w-4 h-4 mr-1.5 inline" /> {{ permohonans.total }} Menunggu Verifikasi
          </Badge>
      </div>

      <!-- Navigation Tabs -->
      <div class="flex items-center gap-4 border-b">
        <Link 
          :href="route('v2.kaprodi.permohonan-surat.diajukan')"
          class="px-4 py-2 text-sm font-medium transition-colors border-b-2"
          :class="[route().current('v2.kaprodi.permohonan-surat.diajukan') ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700']"
        >
          Diajukan
        </Link>
        <Link 
          :href="route('v2.kaprodi.permohonan-surat.disetujui')"
          class="px-4 py-2 text-sm font-medium transition-colors border-b-2"
          :class="[route().current('v2.kaprodi.permohonan-surat.disetujui') ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700']"
        >
          Riwayat Keputusan
        </Link>
      </div>

      <!-- Filters & Search Bar -->
      <div class="flex flex-col sm:flex-row gap-3">
          <div class="relative flex-1">
              <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
              <Input 
                  v-model="searchQuery" 
                  placeholder="Cari berdasarkan NIM atau Nama Mahasiswa..." 
                  class="pl-10 bg-white border-gray-200 rounded-xl focus:ring-primary"
              />
          </div>
          <div class="w-full sm:w-64">
              <select 
                  v-model="filterJenis" 
                  class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary text-sm text-gray-700"
              >
                  <option value="">Semua Jenis Surat</option>
                  <option v-for="jenis in daftarJenisPermohonan" :key="jenis" :value="jenis">
                      {{ jenis }}
                  </option>
              </select>
          </div>
      </div>

      <Card class="border-none shadow-sm overflow-hidden">
        <CardContent class="p-0">
          <div class="rounded-md border overflow-x-auto">
            <Table>
              <TableHeader class="bg-gray-50/50">
                <TableRow>
                  <TableHead class="w-12 text-center">#</TableHead>
                  <TableHead>Mahasiswa</TableHead>
                  <TableHead>Jenis Permohonan</TableHead>
                  <TableHead>Program Studi</TableHead>
                  <TableHead>Tgl Pengajuan</TableHead>
                  <TableHead class="text-center">Status Kaprodi</TableHead>
                  <TableHead class="text-center">Aksi</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="(item, index) in permohonans.data" :key="item.id" class="hover:bg-gray-50/50 transition-colors">
                  <TableCell class="text-center text-gray-500">
                    {{ (permohonans.current_page - 1) * permohonans.per_page + index + 1 }}
                  </TableCell>
                  <TableCell>
                    <div class="font-bold text-gray-900">{{ item.mahasiswa?.nama_lengkap }}</div>
                    <div class="text-xs text-gray-500 font-mono">{{ item.mahasiswa?.nim }}</div>
                  </TableCell>
                  <TableCell>
                    <div class="flex items-center gap-2">
                      <FileText class="w-4 h-4 text-primary" />
                      <span class="text-sm font-medium">{{ item.jenis_permohonan }}</span>
                    </div>
                  </TableCell>
                  <TableCell class="text-sm">
                    {{ item.mahasiswa?.kelas?.prodi?.nama_prodi }}
                    <div v-if="item.mahasiswa?.kelas?.semester?.semester" class="text-[10px] text-gray-400">
                        Semester {{ item.mahasiswa.kelas.semester.semester }}
                    </div>
                  </TableCell>
                  <TableCell class="text-sm text-gray-600">
                    {{ formatDate(item.created_at) }}
                  </TableCell>
                  <TableCell class="text-center">
                    <Badge v-if="item.setuju_kaprodi === 0" variant="warning" class="gap-1 px-2 py-0.5">
                      <Clock class="w-3 h-3" /> Menunggu
                    </Badge>
                    <Badge v-else-if="item.setuju_kaprodi === 2" variant="destructive" class="gap-1 px-2 py-0.5">
                      <XCircle class="w-3 h-3" /> Ditolak
                    </Badge>
                    <Badge v-else variant="success" class="gap-1 px-2 py-0.5">
                      <CheckCircle2 class="w-3 h-3" /> Disetujui
                    </Badge>
                  </TableCell>
                  <TableCell class="text-center">
                    <Button 
                      @click="openDetailModal(item)"
                      size="sm" 
                      variant="outline"
                      class="h-8 gap-1.5 shadow-sm hover:bg-gray-100"
                    >
                      <Eye class="w-4 h-4" /> Detail
                    </Button>
                  </TableCell>
                </TableRow>
                <TableRow v-if="permohonans.data.length === 0">
                  <TableCell colspan="7" class="h-64 text-center">
                        <div class="max-w-md mx-auto py-12">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
                                <Mail class="w-8 h-8" />
                            </div>
                            <p class="text-base font-semibold text-gray-900">Tidak ada data</p>
                            <p class="text-sm text-gray-500 mt-1">Belum ada pengajuan permohonan surat pada kategori ini.</p>
                        </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>

          <!-- Pagination -->
          <div v-if="permohonans.total > 0" class="p-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4 bg-white">
              <p class="text-sm text-gray-600">
                  Menampilkan <span class="font-semibold">{{ permohonans.from || 0 }}</span> - <span class="font-semibold">{{ permohonans.to || 0 }}</span> dari <span class="font-semibold">{{ permohonans.total }}</span> data
              </p>
              <div class="flex items-center gap-1.5">
                  <Button 
                      variant="outline" 
                      size="sm" 
                      :disabled="!permohonans.prev_page_url"
                      @click="router.get(permohonans.prev_page_url, {}, { preserveState: true })"
                      class="rounded-lg h-8 w-8 p-0"
                  >
                      <ChevronLeft class="w-4 h-4" />
                  </Button>
                  
                  <div class="hidden sm:flex items-center gap-1">
                      <Button 
                          v-for="pageLink in permohonans.links.slice(1, -1)" 
                          :key="pageLink.label"
                          variant="ghost"
                          size="sm"
                          @click="router.get(pageLink.url, {}, { preserveState: true })"
                          :class="[
                              'h-8 min-w-[32px] px-2 rounded-lg font-medium text-xs',
                              pageLink.active ? 'bg-primary text-white hover:bg-primary/90' : 'text-gray-600 hover:bg-gray-100'
                          ]"
                      >
                          {{ pageLink.label }}
                      </Button>
                  </div>

                  <Button 
                      variant="outline" 
                      size="sm" 
                      :disabled="!permohonans.next_page_url"
                      @click="router.get(permohonans.next_page_url, {}, { preserveState: true })"
                      class="rounded-lg h-8 w-8 p-0"
                  >
                      <ChevronRight class="w-4 h-4" />
                  </Button>
              </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Detail & Verification Modal -->
    <Dialog v-model:open="isModalOpen">
        <DialogContent class="max-w-2xl bg-white p-6 rounded-2xl shadow-2xl overflow-y-auto max-h-[90vh]">
            <DialogHeader class="bg-primary text-white p-6 -m-6 mb-6 rounded-t-2xl">
                <DialogTitle class="text-xl font-bold text-white">Detail Permohonan Surat</DialogTitle>
                <DialogDescription class="text-indigo-100 text-sm mt-1">
                    Tinjau detail pengajuan mahasiswa sebelum memberikan persetujuan atau penolakan.
                </DialogDescription>
            </DialogHeader>

            <div v-if="selectedPermohonan" class="space-y-6">
                <!-- Info Section -->
                <div v-if="!isRejecting" class="space-y-6">
                    <!-- Status Reject Info (If history) -->
                    <div v-if="selectedPermohonan.setuju_kaprodi === 2" class="bg-red-50 border border-red-100 p-4 rounded-xl flex items-start gap-3">
                        <XCircle class="w-5 h-5 text-red-600 mt-0.5" />
                        <div>
                            <p class="text-sm font-bold text-red-900 leading-tight">Pengajuan Ditolak</p>
                            <p class="text-xs text-red-700 mt-1 italic">"{{ selectedPermohonan.keterangan_ditolak }}"</p>
                        </div>
                    </div>

                    <!-- Student Info Summary -->
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200/80 space-y-4 text-sm">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="text-xs text-gray-500 font-medium block">Nama Lengkap</span>
                                <span class="font-bold text-gray-900">{{ selectedPermohonan.mahasiswa?.nama_lengkap || '-' }}</span>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500 font-medium block">NIM</span>
                                <span class="font-mono font-semibold text-gray-800">{{ selectedPermohonan.mahasiswa?.nim || '-' }}</span>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500 font-medium block">Prodi & Kelas</span>
                                <span class="font-semibold text-gray-800">{{ selectedPermohonan.mahasiswa?.kelas?.prodi?.nama_prodi || '-' }} • {{ selectedPermohonan.mahasiswa?.kelas?.nama_kelas || '-' }}</span>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500 font-medium block">Jenis Surat</span>
                                <span class="font-semibold text-primary">{{ selectedPermohonan.jenis_permohonan }}</span>
                            </div>
                            <div class="col-span-2 sm:col-span-1">
                                <span class="text-xs text-gray-500 font-medium block">Tempat, Tanggal Lahir</span>
                                <span class="text-gray-800">{{ selectedPermohonan.mahasiswa?.tempat_lahir || '-' }}, {{ formatDate(selectedPermohonan.mahasiswa?.tanggal_lahir) }}</span>
                            </div>
                            <div class="col-span-2 sm:col-span-1">
                                <span class="text-xs text-gray-500 font-medium block">No. Telepon / WA</span>
                                <span class="text-gray-800">{{ selectedPermohonan.mahasiswa?.no_telephone || '-' }}</span>
                            </div>
                            <div class="col-span-2">
                                <span class="text-xs text-gray-500 font-medium block">Alamat Mahasiswa</span>
                                <span class="text-gray-800">{{ selectedPermohonan.mahasiswa?.alamat || '-' }}</span>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-gray-200/60 space-y-3">
                            <div v-if="selectedPermohonan.tahun_akademik">
                                <span class="text-xs text-gray-500 font-medium block">Tahun Akademik</span>
                                <span class="text-gray-800 font-medium">{{ selectedPermohonan.tahun_akademik }}</span>
                            </div>

                            <div v-if="selectedPermohonan.nama_orang_tua" class="grid grid-cols-2 gap-4">
                                <div class="col-span-2">
                                    <span class="text-xs text-gray-500 font-medium block">Nama Orang Tua</span>
                                    <span class="text-gray-800">{{ selectedPermohonan.nama_orang_tua }}</span>
                                </div>
                                <div class="col-span-2">
                                    <span class="text-xs text-gray-500 font-medium block">Alamat Orang Tua</span>
                                    <span class="text-gray-800">{{ selectedPermohonan.alamat_orang_tua || '-' }}</span>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-500 font-medium block">Pekerjaan</span>
                                    <span class="text-gray-800">{{ selectedPermohonan.pekerjaan || '-' }}</span>
                                </div>
                                <div v-if="selectedPermohonan.nip">
                                    <span class="text-xs text-gray-500 font-medium block">NIP</span>
                                    <span class="text-gray-800">{{ selectedPermohonan.nip }}</span>
                                </div>
                            </div>

                            <div v-if="selectedPermohonan.nama_instansi">
                                <span class="text-xs text-gray-500 font-medium block">Instansi Tujuan</span>
                                <span class="text-gray-800 font-bold uppercase tracking-tight">{{ selectedPermohonan.nama_instansi }}</span>
                                <div v-if="selectedPermohonan.alamat_instansi" class="text-xs text-gray-500 mt-1 italic">
                                    "{{ selectedPermohonan.alamat_instansi }}"
                                </div>
                                <div v-if="selectedPermohonan.pimpinan" class="mt-1">
                                    <span class="text-[10px] text-gray-400 block">Pimpinan:</span>
                                    <span class="text-gray-700 font-medium">{{ selectedPermohonan.pimpinan }}</span>
                                </div>
                            </div>

                            <div v-if="selectedPermohonan.judul_laporan">
                                <span class="text-xs text-gray-500 font-medium block">Judul Laporan/TA</span>
                                <span class="text-gray-800 font-medium italic">"{{ selectedPermohonan.judul_laporan }}"</span>
                            </div>

                            <div v-if="selectedPermohonan.data_diminta && Array.isArray(selectedPermohonan.data_diminta) && selectedPermohonan.data_diminta.length > 0">
                                <span class="text-xs text-gray-500 font-medium block mb-1">Data yang Diminta:</span>
                                <ul class="list-disc list-inside text-xs text-gray-700 space-y-1 bg-white p-3 rounded-lg border border-gray-200">
                                    <li v-for="(item, idx) in selectedPermohonan.data_diminta" :key="idx">{{ item }}</li>
                                </ul>
                            </div>

                            <div v-if="selectedPermohonan.pt_asal" class="grid grid-cols-2 gap-4">
                                <div class="col-span-2">
                                    <span class="text-xs text-gray-500 font-medium block">Status Akreditasi</span>
                                    <span class="text-gray-800">{{ selectedPermohonan.status_akreditasi }}</span>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-500 font-medium block">PT Asal</span>
                                    <span class="text-gray-800">{{ selectedPermohonan.pt_asal }}</span>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-500 font-medium block">PT Tujuan</span>
                                    <span class="text-gray-800">{{ selectedPermohonan.pt_tujuan }}</span>
                                </div>
                            </div>

                            <div v-if="selectedPermohonan.alasan_cuti">
                                <span class="text-xs text-gray-500 font-medium block">Alasan Cuti</span>
                                <span class="text-gray-800">{{ selectedPermohonan.alasan_cuti }}</span>
                                <div v-if="selectedPermohonan.masa_cuti" class="text-xs text-gray-500 mt-1">
                                    Masa Cuti / Studi: {{ selectedPermohonan.masa_cuti }}
                                </div>
                            </div>

                            <div v-if="selectedPermohonan.tanggal_mulai" class="grid grid-cols-2 gap-4">
                                <div>
                                    <span class="text-xs text-gray-500 font-medium block">Tanggal Mulai PKL</span>
                                    <span class="text-gray-800">{{ formatDate(selectedPermohonan.tanggal_mulai) }}</span>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-500 font-medium block">Tanggal Selesai PKL</span>
                                    <span class="text-gray-800">{{ formatDate(selectedPermohonan.tanggal_selesai) }}</span>
                                </div>
                            </div>

                            <!-- Anggota Tim Section -->
                            <div v-if="selectedPermohonan.anggota_tim_detail && selectedPermohonan.anggota_tim_detail.length > 0" class="pt-2">
                                <span class="text-xs text-gray-500 font-bold block mb-2 uppercase tracking-wider">Anggota Tim Kelompok:</span>
                                <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                                    <Table>
                                        <TableHeader class="bg-gray-50">
                                            <TableRow>
                                                <TableHead class="h-8 text-[10px] py-0 px-3">NIM</TableHead>
                                                <TableHead class="h-8 text-[10px] py-0 px-3">Nama Mahasiswa</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <TableRow v-for="anggota in selectedPermohonan.anggota_tim_detail" :key="anggota.id" class="hover:bg-transparent">
                                                <TableCell class="py-1.5 px-3 font-mono text-xs">{{ anggota.nim }}</TableCell>
                                                <TableCell class="py-1.5 px-3 text-xs font-medium">{{ anggota.nama_lengkap }}</TableCell>
                                            </TableRow>
                                        </TableBody>
                                    </Table>
                                </div>
                            </div>
                            </div>
                    </div>

                    <!-- Action Buttons for Initial View -->
                    <div v-if="selectedPermohonan.setuju_kaprodi === 0" class="flex flex-col sm:flex-row items-center gap-3 pt-4 border-t border-gray-100">
                        <Button 
                            type="button" 
                            variant="destructive"
                            @click="isRejecting = true" 
                            class="w-full sm:w-auto font-semibold flex items-center gap-2 px-6"
                        >
                            <XCircle class="w-4 h-4" /> Tolak Pengajuan
                        </Button>
                        <div class="flex-1"></div>
                        <Button 
                            type="button" 
                            variant="outline" 
                            @click="isModalOpen = false" 
                            class="w-full sm:w-auto font-semibold"
                        >
                            Batal
                        </Button>
                        <Button 
                            type="button"
                            @click="openConfirmApproveModal" 
                            :disabled="formApprove.processing"
                            class="w-full sm:w-auto bg-primary hover:bg-primary/90 text-white font-semibold flex items-center gap-2 px-8 shadow"
                        >
                            <CheckCircle2 class="w-4 h-4" />
                            Setujui & Verifikasi
                        </Button>
                    </div>
                    <div v-else class="flex items-center justify-end pt-4 border-t border-gray-100">
                        <Button 
                            type="button" 
                            variant="outline" 
                            @click="isModalOpen = false" 
                            class="font-semibold"
                        >
                            Tutup
                        </Button>
                    </div>
                </div>

                <!-- Rejecting View (Form Alasan) -->
                <div v-else class="space-y-6 animate-in fade-in slide-in-from-right-4 duration-300">
                    <div class="bg-red-50 border border-red-100 p-4 rounded-xl flex items-start gap-3">
                        <div class="bg-red-100 p-1.5 rounded-full">
                            <AlertCircle class="w-4 h-4 text-red-600" />
                        </div>
                        <p class="text-xs text-red-800 leading-relaxed font-medium">
                            Anda akan menolak permohonan surat milik <span class="font-bold">{{ selectedPermohonan.mahasiswa?.nama_lengkap }}</span>. 
                            Mohon berikan alasan yang jelas agar mahasiswa dapat melakukan perbaikan. Alasan ini akan dikirim via WhatsApp.
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="alasan" class="text-sm font-bold text-gray-800">Alasan Penolakan <span class="text-red-500">*</span></Label>
                        <Textarea 
                            id="alasan"
                            v-model="formReject.keterangan_ditolak"
                            placeholder="Misal: Data instansi kurang lengkap, harap lampirkan alamat detail..."
                            class="min-h-[120px] rounded-xl border-gray-200 focus:ring-red-500"
                        />
                        <span v-if="formReject.errors.keterangan_ditolak" class="text-xs text-red-500">{{ formReject.errors.keterangan_ditolak }}</span>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <Button 
                            type="button" 
                            variant="ghost" 
                            @click="isRejecting = false"
                            class="font-semibold"
                        >
                            Kembali
                        </Button>
                        <Button 
                            type="button"
                            variant="destructive"
                            @click="submitReject"
                            :disabled="formReject.processing || !formReject.keterangan_ditolak"
                            class="font-semibold flex items-center gap-2 px-8"
                        >
                            <RefreshCw v-if="formReject.processing" class="w-4 h-4 animate-spin" />
                            <XCircle v-else class="w-4 h-4" />
                            Konfirmasi Tolak
                        </Button>
                    </div>
                </div>
            </div>
        </DialogContent>
    </Dialog>

    <!-- Confirm Approve Modal -->
    <Dialog :open="isConfirmApproveModalOpen" @update:open="isConfirmApproveModalOpen = $event">
      <DialogContent class="sm:max-w-[450px] p-0 overflow-hidden border-none shadow-2xl bg-white rounded-2xl">
        <div class="p-8 text-center">
          <div class="w-20 h-20 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm">
            <AlertCircle class="w-10 h-10 text-primary" />
          </div>
          <DialogHeader>
            <DialogTitle class="text-2xl font-bold text-gray-800 text-center">Konfirmasi Verifikasi</DialogTitle>
            <DialogDescription class="text-gray-500 text-center mt-3 px-2 text-base">
              Apakah Anda yakin ingin memverifikasi permohonan surat <span class="font-bold text-gray-900">"{{ selectedPermohonan?.jenis_permohonan }}"</span> untuk mahasiswa <span class="font-bold text-gray-900">"{{ selectedPermohonan?.mahasiswa?.nama_lengkap }}"</span>?
            </DialogDescription>
          </DialogHeader>

          <div class="flex items-center justify-center gap-4 mt-10">
            <Button 
              type="button" 
              variant="outline" 
              @click="isConfirmApproveModalOpen = false" 
              class="h-12 px-8 rounded-xl text-gray-500 hover:bg-gray-100 transition-all font-semibold"
            >
              Batal
            </Button>
            <Button 
              @click="submitApprove" 
              :disabled="formApprove.processing"
              class="h-12 px-10 bg-primary hover:bg-primary/90 text-white rounded-xl shadow-lg shadow-indigo-500/20 transition-all font-semibold"
            >
              <Loader2 v-if="formApprove.processing" class="w-4 h-4 mr-2 animate-spin" />
              Ya, Verifikasi
            </Button>
          </div>
        </div>
      </DialogContent>
    </Dialog>

    <!-- Toast Notification -->
    <transition name="toast">
      <div v-if="showToast" class="fixed bottom-10 right-10 z-[100]">
        <div class="bg-white px-4 py-3 rounded-lg shadow-xl border border-gray-100 flex items-center gap-3">
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
