<script setup>
import { ref, watch, onMounted } from 'vue'
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
} from '@/components/ui/dialog'
import { Search, Mail, Eye, Printer, Clock, ChevronLeft, ChevronRight, CheckCircle2, XCircle, AlertCircle, RefreshCw } from 'lucide-vue-next'

const props = defineProps({
    permohonans: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({ search: '', jenis: '' })
    }
})

const page = usePage()
const showToast = ref(false)
const toastMessage = ref('')
const toastType = ref('success')

const triggerToast = (message, type = 'success') => {
    toastMessage.value = message
    toastType.value = type
    showToast.value = true
    setTimeout(() => {
        showToast.value = false
    }, 4000)
}

watch(() => page.props.flash, (newFlash) => {
    if (newFlash?.success) {
        triggerToast(newFlash.success, 'success')
    }
    if (newFlash?.error) {
        triggerToast(newFlash.error, 'error')
    }
}, { deep: true })

onMounted(() => {
    if (page.props.flash?.success) {
        triggerToast(page.props.flash.success, 'success')
    }
    if (page.props.flash?.error) {
        triggerToast(page.props.flash.error, 'error')
    }
})

const searchQuery = ref(props.filters.search || '')
const filterJenis = ref(props.filters.jenis || '')

let debounceTimer = null
const applyFilter = () => {
    router.get('/v2/admin/permohonan-surat/cetak', {
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

const isModalOpen = ref(false)
const selectedPermohonan = ref(null)

const form = useForm({
    no_surat: '',
})

const openModal = (permohonan) => {
    selectedPermohonan.value = permohonan
    form.no_surat = ''
    form.clearErrors()
    isModalOpen.value = true
}

const submitTerbitkan = () => {
    form.put(`/v2/admin/permohonan-surat/${selectedPermohonan.value.id}/terbitkan`, {
        preserveScroll: true,
        onSuccess: () => {
            isModalOpen.value = false
            window.open(`/v2/admin/permohonan-surat/${selectedPermohonan.value.id}/cetak-dokumen`, '_blank')
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
    <Head title="Cetak Permohonan Surat" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <span class="text-gray-500">Permohonan Surat</span>
                <span class="text-gray-400">/</span>
                <span class="text-gray-800 font-semibold">Cetak Surat</span>
            </div>
        </template>

        <div class="py-12 space-y-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Title & Stats -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-[#1F2937]">Permohonan Surat Menunggu Cetak</h1>
                        <p class="text-[#6B7280]">Daftar pengajuan surat mahasiswa yang telah disetujui Kaprodi dan siap diterbitkan nomor surat.</p>
                    </div>
                    <Badge variant="outline" class="bg-indigo-50 text-[#4B49AC] border-indigo-200 font-semibold px-3.5 py-1.5 self-start md:self-center">
                        <Clock class="w-4 h-4 mr-1.5 inline" /> {{ permohonans.total }} Menunggu Penerbitan
                    </Badge>
                </div>

                <!-- Filters & Search Bar -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                        <Input 
                            v-model="searchQuery" 
                            placeholder="Cari berdasarkan NIM atau Nama Mahasiswa..." 
                            class="pl-10 bg-white border-gray-200 rounded-xl focus:ring-[#4B49AC]"
                        />
                    </div>
                    <div class="w-full sm:w-64">
                        <select 
                            v-model="filterJenis" 
                            class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#4B49AC] text-sm text-gray-700"
                        >
                            <option value="">Semua Jenis Surat</option>
                            <option v-for="jenis in daftarJenisPermohonan" :key="jenis" :value="jenis">
                                {{ jenis }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Table Card -->
                <Card class="shadow-sm border-gray-200">
                    <CardContent class="p-0">
                        <div class="overflow-x-auto rounded-t-lg">
                            <Table>
                                <TableHeader>
                                    <TableRow class="bg-gray-50/75">
                                        <TableHead class="w-[60px] font-semibold text-gray-700">#</TableHead>
                                        <TableHead class="font-semibold text-gray-700">Mahasiswa</TableHead>
                                        <TableHead class="font-semibold text-gray-700">NIM</TableHead>
                                        <TableHead class="font-semibold text-gray-700">Program Studi</TableHead>
                                        <TableHead class="font-semibold text-gray-700">Jenis Surat</TableHead>
                                        <TableHead class="font-semibold text-gray-700">Tgl Pengajuan</TableHead>
                                        <TableHead class="font-semibold text-center text-gray-700">Status</TableHead>
                                        <TableHead class="font-semibold text-center text-gray-700">Aksi</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <template v-if="permohonans.data.length > 0">
                                        <TableRow v-for="(item, index) in permohonans.data" :key="item.id" class="hover:bg-gray-50/50 transition-colors">
                                            <TableCell class="font-medium text-gray-900">
                                                {{ (permohonans.current_page - 1) * permohonans.per_page + index + 1 }}
                                            </TableCell>
                                            <TableCell class="font-semibold text-gray-900">
                                                {{ item.mahasiswa?.nama_lengkap || '-' }}
                                            </TableCell>
                                            <TableCell class="text-gray-600 font-mono">
                                                {{ item.mahasiswa?.nim || '-' }}
                                            </TableCell>
                                            <TableCell class="text-gray-700">
                                                {{ item.mahasiswa?.kelas?.prodi?.nama_prodi || '-' }}
                                                <span v-if="item.mahasiswa?.kelas?.semester?.semester" class="text-xs text-gray-500 block">
                                                    Semester {{ item.mahasiswa.kelas.semester.semester }}
                                                </span>
                                            </TableCell>
                                            <TableCell class="font-medium text-[#4B49AC]">
                                                {{ item.jenis_permohonan }}
                                            </TableCell>
                                            <TableCell class="text-gray-600">
                                                {{ formatDate(item.created_at) }}
                                            </TableCell>
                                            <TableCell class="text-center">
                                                <Badge variant="secondary" class="bg-amber-100 text-amber-800 hover:bg-amber-100 font-normal px-2.5 py-0.5">
                                                    Siap Terbit
                                                </Badge>
                                            </TableCell>
                                            <TableCell class="text-center">
                                                <Button @click="openModal(item)" size="sm" class="bg-[#4B49AC] hover:bg-[#3f3d91] text-white shadow-sm">
                                                    <Printer class="w-4 h-4 mr-1.5" /> Verifikasi & Cetak
                                                </Button>
                                            </TableCell>
                                        </TableRow>
                                    </template>
                                    <template v-else>
                                        <TableRow>
                                            <TableCell colspan="8" class="h-64 text-center">
                                                <div class="max-w-md mx-auto py-12">
                                                    <div class="w-16 h-16 bg-[#4B49AC]/10 rounded-full flex items-center justify-center mx-auto mb-4 text-[#4B49AC]">
                                                        <Mail class="w-8 h-8 text-[#4B49AC]" />
                                                    </div>
                                                    <p class="text-base font-semibold text-gray-900">Tidak ada permohonan surat</p>
                                                    <p class="text-sm text-gray-500 mt-1">Belum ada pengajuan surat baru yang membutuhkan verifikasi & pencetakan saat ini.</p>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    </template>
                                </TableBody>
                            </Table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="permohonans.total > 0" class="p-4 border-t border-gray-100 flex items-center justify-between bg-white rounded-b-lg">
                            <p class="text-sm text-gray-600">
                                Menampilkan <span class="font-semibold">{{ permohonans.from || 0 }}</span> sampai <span class="font-semibold">{{ permohonans.to || 0 }}</span> dari <span class="font-semibold">{{ permohonans.total }}</span> data
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
                                <div class="flex items-center gap-1">
                                    <Button 
                                        v-for="pageLink in permohonans.links.slice(1, -1)" 
                                        :key="pageLink.label"
                                        variant="ghost"
                                        size="sm"
                                        @click="router.get(pageLink.url, {}, { preserveState: true })"
                                        :class="[
                                            'h-8 w-8 p-0 rounded-lg font-medium text-xs',
                                            pageLink.active ? 'bg-[#4B49AC] text-white hover:bg-[#3f3d91]' : 'text-gray-600 hover:bg-gray-100'
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
        </div>

        <!-- Verification / Issue Modal -->
        <Dialog v-model:open="isModalOpen">
            <DialogContent class="max-w-2xl bg-white p-6 rounded-2xl shadow-2xl overflow-y-auto max-h-[90vh]">
                <DialogHeader class="bg-[#4B49AC] text-white p-6 -m-6 mb-6 rounded-t-2xl">
                    <DialogTitle class="text-xl font-bold text-white">Penerbitan & Cetak Surat Permohonan</DialogTitle>
                    <DialogDescription class="text-indigo-100 text-sm mt-1">
                        Verifikasi detail pengajuan dan masukkan nomor surat sebelum mencetak dokumen.
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitTerbitkan" v-if="selectedPermohonan" class="space-y-6">
                    <!-- Student Info Summary -->
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200/80 space-y-3 text-sm">
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
                                <span class="font-semibold text-[#4B49AC]">{{ selectedPermohonan.jenis_permohonan }}</span>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-gray-200/60 grid grid-cols-1 gap-2">
                            <div>
                                <span class="text-xs text-gray-500 font-medium block">Keperluan / Keterangan Pengajuan</span>
                                <span class="text-gray-800">{{ selectedPermohonan.keperluan || selectedPermohonan.alasan_cuti || selectedPermohonan.judul_laporan || 'Tidak ada keterangan' }}</span>
                            </div>
                            <div v-if="selectedPermohonan.nama_instansi">
                                <span class="text-xs text-gray-500 font-medium block">Instansi Tujuan</span>
                                <span class="text-gray-800">{{ selectedPermohonan.nama_instansi }} - {{ selectedPermohonan.alamat_instansi || '-' }}</span>
                            </div>
                            <div v-if="selectedPermohonan.data_diminta && Array.isArray(selectedPermohonan.data_diminta) && selectedPermohonan.data_diminta.length > 0">
                                <span class="text-xs text-gray-500 font-medium block mb-1">Data yang Diminta:</span>
                                <ul class="list-disc list-inside text-xs text-gray-700 space-y-0.5 bg-white p-2 rounded border border-gray-200">
                                    <li v-for="(item, idx) in selectedPermohonan.data_diminta" :key="idx">{{ item }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Input Nomor Surat -->
                    <div class="space-y-2">
                        <Label for="no_surat" class="text-sm font-semibold text-gray-800">
                            Nomor Surat <span class="text-red-500">*</span>
                        </Label>
                        <Input 
                            id="no_surat" 
                            v-model="form.no_surat" 
                            placeholder="Contoh: 335/AP.08/DIR/XI/2026" 
                            required 
                            class="border-gray-300 rounded-xl focus:ring-[#4B49AC]"
                        />
                        <span v-if="form.errors.no_surat" class="text-xs text-red-500 mt-1 block">{{ form.errors.no_surat }}</span>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <Button 
                            type="button" 
                            variant="outline" 
                            @click="isModalOpen = false" 
                            class="font-semibold"
                        >
                            Batal
                        </Button>
                        <Button 
                            type="submit" 
                            :disabled="form.processing"
                            class="bg-[#4B49AC] hover:bg-[#3f3d91] text-white font-semibold flex items-center gap-2 px-6 shadow"
                        >
                            <RefreshCw v-if="form.processing" class="w-4 h-4 animate-spin" />
                            <Printer v-else class="w-4 h-4" />
                            Simpan & Cetak
                        </Button>
                    </div>
                </form>
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
