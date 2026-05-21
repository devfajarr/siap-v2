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
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetDescription,
    SheetFooter,
} from '@/components/ui/sheet'
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover'
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/ui/command'
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription as DialogDesc,
    DialogFooter as DialogFoot,
} from '@/components/ui/dialog'
import { 
    Mail, 
    Plus, 
    Printer, 
    Edit, 
    Trash2, 
    Clock, 
    CheckCircle2, 
    XCircle, 
    AlertTriangle, 
    FileText, 
    Send, 
    RefreshCw, 
    ChevronLeft, 
    ChevronRight,
    Building2,
    Calendar,
    User,
    Briefcase,
    MapPin,
    GraduationCap,
    Info,
    HelpCircle,
    X,
    PlusCircle,
    ChevronsUpDown,
    Check,
    Users
} from 'lucide-vue-next'

const props = defineProps({
    mahasiswa: {
        type: Object,
        required: true,
    },
    daftarMahasiswa: {
        type: Array,
        default: () => [],
    },
    tahunAkademik: {
        type: String,
        default: '2025/2026',
    },
    permohonans: {
        type: Object,
        required: true,
    },
    hasActivePkl: {
        type: Boolean,
        default: false,
    },
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

const isSheetOpen = ref(false)
const isEditMode = ref(false)
const editId = ref(null)
const openJenisCombobox = ref(false)
const openAnggotaCombobox = ref({})
const showDetailAnggotaModal = ref(false)
const selectedSuratAnggota = ref(null)

const openDetailAnggota = (item) => {
    selectedSuratAnggota.value = item
    showDetailAnggotaModal.value = true
}

const getMahasiswaLabel = (id) => {
    if (!id) return '-- Pilih Mahasiswa Anggota --'
    const found = props.daftarMahasiswa.find(m => m.id === id)
    return found ? `${found.nim} - ${found.nama_lengkap}` : '-- Pilih Mahasiswa Anggota --'
}

const getMahasiswaObj = (id) => props.daftarMahasiswa.find(m => m.id === id) || (props.mahasiswa.id === id ? props.mahasiswa : null)
const getMahasiswaName = (id) => getMahasiswaObj(id)?.nama_lengkap || 'Mahasiswa tidak diketahui'
const getMahasiswaNim = (id) => getMahasiswaObj(id)?.nim || '-'
const getMahasiswaInitials = (id) => {
    const name = getMahasiswaName(id)
    return name && name !== 'Mahasiswa tidak diketahui' ? name.charAt(0).toUpperCase() : 'A'
}

const form = useForm({
    jenis_permohonan: '',
    tahun_akademik: props.tahunAkademik,
    nama_orang_tua: '',
    alamat_orang_tua: '',
    pekerjaan: '',
    nip: '',
    pangkat_golongan: '',
    nama_instansi: '',
    alamat_instansi: '',
    keperluan: '',
    alasan_cuti: '',
    kelas_tujuan: '',
    pt_tujuan: '',
    status_akreditasi: '',
    judul_laporan: '',
    data_diminta: [''],
    anggota_tim: [],
    pimpinan: '',
    tanggal_mulai: '',
    tanggal_selesai: '',
})

const daftarJenisPermohonan = [
    'Keterangan Aktif Kuliah',
    'Cuti Kuliah',
    'Pindah Kelas',
    'Pindah PT',
    'Mengundurkan Diri',
    'Izin PKL',
    'Izin Memperoleh Data PKL',
    'Izin Memperoleh Data TA'
]

const openCreateSheet = () => {
    isEditMode.value = false
    editId.value = null
    form.reset()
    form.clearErrors()
    form.tahun_akademik = props.tahunAkademik
    form.data_diminta = ['']
    form.anggota_tim = []
    isSheetOpen.value = true
}

const openEditSheet = (item) => {
    isEditMode.value = true
    editId.value = item.id
    form.reset()
    form.clearErrors()
    form.jenis_permohonan = item.jenis_permohonan ? item.jenis_permohonan.replace('Ijin', 'Izin') : ''
    form.tahun_akademik = item.tahun_akademik || props.tahunAkademik
    form.nama_orang_tua = item.nama_orang_tua || ''
    form.alamat_orang_tua = item.alamat_orang_tua || ''
    form.pekerjaan = item.pekerjaan || ''
    form.nip = item.nip || ''
    form.pangkat_golongan = item.pangkat_golongan || ''
    form.nama_instansi = item.nama_instansi || ''
    form.alamat_instansi = item.alamat_instansi || ''
    form.keperluan = item.keperluan || ''
    form.alasan_cuti = item.alasan_cuti || ''
    form.kelas_tujuan = item.kelas_tujuan || ''
    form.pt_tujuan = item.pt_tujuan || ''
    form.status_akreditasi = item.status_akreditasi || ''
    form.judul_laporan = item.judul_laporan || ''
    form.data_diminta = (Array.isArray(item.data_diminta) && item.data_diminta.length > 0) ? [...item.data_diminta] : ['']
    form.anggota_tim = (Array.isArray(item.anggota_tim) && item.anggota_tim.length > 0) ? [...item.anggota_tim] : []
    form.pimpinan = item.pimpinan || ''
    form.tanggal_mulai = item.tanggal_mulai || ''
    form.tanggal_selesai = item.tanggal_selesai || ''
    isSheetOpen.value = true
}

const addDataDiminta = () => {
    form.data_diminta.push('')
}

const addAnggotaTim = () => {
    if (form.anggota_tim.length < 5) {
        form.anggota_tim.push('')
    }
}

const removeAnggotaTim = (index) => {
    form.anggota_tim.splice(index, 1)
}

const removeDataDiminta = (index) => {
    if (form.data_diminta.length > 1) {
        form.data_diminta.splice(index, 1)
    }
}

const submitForm = () => {
    if (isEditMode.value) {
        form.put(`/v2/mahasiswa/permohonan-surat/${editId.value}`, {
            preserveScroll: true,
            onSuccess: () => {
                isSheetOpen.value = false
            }
        })
    } else {
        form.post('/v2/mahasiswa/permohonan-surat', {
            preserveScroll: true,
            onSuccess: () => {
                isSheetOpen.value = false
            }
        })
    }
}

const showDeleteConfirm = ref(false)
const deleteConfirmId = ref(null)
const deleteForm = useForm({})

const confirmDelete = (id) => {
    deleteConfirmId.value = id
    showDeleteConfirm.value = true
}

const executeDelete = () => {
    if (deleteConfirmId.value) {
        deleteForm.delete(`/v2/mahasiswa/permohonan-surat/${deleteConfirmId.value}`, {
            preserveScroll: true,
            onSuccess: () => {
                showDeleteConfirm.value = false
                deleteConfirmId.value = null
            }
        })
    }
}

const formatDate = (dateStr) => {
    if (!dateStr) return '-'
    const date = new Date(dateStr)
    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
}

const showRejectionDetail = ref(false)
const rejectionDetail = ref(null)

const openRejectionDetail = (item) => {
    rejectionDetail.value = item
    showRejectionDetail.value = true
}
</script>

<template>
    <Head title="Permohonan Surat Mahasiswa" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center gap-2 text-sm">
                <span class="text-gray-500">Layanan Mahasiswa</span>
                <span class="text-gray-400">/</span>
                <span class="text-gray-800 font-semibold">Permohonan Surat</span>
            </div>
        </template>

        <div class="py-8 space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Student Identity Banner -->
            <div class="bg-gradient-to-r from-[#4B49AC] to-[#605ecc] p-6 sm:p-8 rounded-lg text-white shadow-lg flex flex-col md:flex-row md:items-center justify-between gap-6 relative overflow-hidden border border-indigo-500/30">
                <div class="absolute right-0 top-0 opacity-10 translate-x-8 -translate-y-8 pointer-events-none">
                    <GraduationCap class="w-80 h-80" />
                </div>
                <div class="space-y-2 relative z-10">
                    <div class="text-xs uppercase font-extrabold tracking-wider text-indigo-200">PORTAL LAYANAN AKADEMIK</div>
                    <h1 class="text-2xl sm:text-3xl font-black">Permohonan Surat Akademik</h1>
                    <p class="text-indigo-100 text-sm max-w-xl leading-relaxed">
                        Ajukan berbagai kebutuhan surat pengantar, izin penelitian, cuti, hingga keterangan aktif kuliah secara daring. Notifikasi persetujuan akan langsung diteruskan ke Ketua Program Studi.
                    </p>
                    <div class="flex flex-wrap gap-x-6 gap-y-1 text-sm font-medium text-indigo-100 pt-3 border-t border-white/20 mt-4">
                        <div><span class="text-white font-bold">Mahasiswa:</span> {{ mahasiswa.nama_lengkap }}</div>
                        <div><span class="text-white font-bold">NIM:</span> {{ mahasiswa.nim }}</div>
                        <div><span class="text-white font-bold">Prodi:</span> {{ mahasiswa.prodi }}</div>
                        <div><span class="text-white font-bold">Semester:</span> {{ mahasiswa.semester }}</div>
                    </div>
                </div>
                <div class="relative z-10 flex-shrink-0">
                    <Button 
                        @click="openCreateSheet" 
                        class="bg-white hover:bg-gray-100 text-[#4B49AC] font-bold text-sm px-6 py-6 rounded-lg shadow hover:scale-105 transition-all flex items-center gap-2.5"
                    >
                        <Plus class="w-5 h-5 stroke-[3]" /> Ajukan Surat Baru
                    </Button>
                </div>
            </div>

            <!-- Table Riwayat Permohonan Surat -->
            <Card class="shadow-sm border-gray-200/80 rounded-lg overflow-hidden">
                <div class="p-6 border-b border-gray-100 bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 rounded-lg bg-indigo-50 text-[#4B49AC]">
                            <FileText class="w-5 h-5" />
                        </div>
                        <div>
                            <h2 class="font-bold text-lg text-gray-900">Riwayat Pengajuan Surat</h2>
                            <p class="text-xs text-gray-500">Status verifikasi dan pencetakan dokumen permohonan Anda</p>
                        </div>
                    </div>
                </div>

                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow class="bg-gray-50/75 border-b border-gray-100 text-xs uppercase text-gray-500 tracking-wider">
                                    <TableHead class="w-16 font-bold py-4">No</TableHead>
                                    <TableHead class="font-bold py-4">Nomor Surat</TableHead>
                                    <TableHead class="font-bold py-4">Jenis Permohonan</TableHead>
                                    <TableHead class="font-bold py-4">Tanggal Pengajuan</TableHead>
                                    <TableHead class="font-bold text-center py-4">Persetujuan Kaprodi</TableHead>
                                    <TableHead class="font-bold text-center py-4">Status Cetak</TableHead>
                                    <TableHead class="font-bold text-center py-4">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <template v-if="permohonans.data.length > 0">
                                    <TableRow v-for="(item, index) in permohonans.data" :key="item.id" class="hover:bg-gray-50/50 transition-colors">
                                        <TableCell class="font-medium text-gray-900 py-4">
                                            {{ (permohonans.current_page - 1) * permohonans.per_page + index + 1 }}
                                        </TableCell>
                                        <TableCell class="font-mono font-bold text-gray-800 py-4">
                                            <span v-if="item.no_surat" class="bg-gray-100 px-2.5 py-1 rounded-md text-xs border border-gray-200">
                                                {{ item.no_surat }}
                                            </span>
                                            <span v-else class="text-gray-400 italic text-xs">Belum diterbitkan</span>
                                        </TableCell>
                                        <TableCell class="font-bold text-[#4B49AC] py-4">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="text-base">{{ item.jenis_permohonan ? item.jenis_permohonan.replace('Ijin', 'Izin') : '' }}</span>
                                                <Badge v-if="item.mahasiswa_id === props.mahasiswa.id" class="bg-[#4B49AC]/10 text-[#4B49AC] hover:bg-[#4B49AC]/20 border border-[#4B49AC]/20 text-[10px] font-extrabold px-2 py-0.5 rounded">
                                                    Ketua
                                                </Badge>
                                                <Badge v-else class="bg-amber-500/10 text-amber-600 hover:bg-amber-500/20 border border-amber-500/20 text-[10px] font-extrabold px-2 py-0.5 rounded">
                                                    Anggota
                                                </Badge>
                                            </div>
                                            <span v-if="item.keperluan" class="block text-xs font-normal text-gray-500 mt-0.5">
                                                Keperluan: {{ item.keperluan }}
                                            </span>
                                            <span v-else-if="item.judul_laporan" class="block text-xs font-normal text-gray-500 mt-0.5">
                                                Judul: {{ item.judul_laporan }}
                                            </span>
                                            <div v-if="item.anggota_tim && item.anggota_tim.length > 0" class="mt-2">
                                                <Button 
                                                    type="button" 
                                                    variant="ghost" 
                                                    size="sm" 
                                                    @click="openDetailAnggota(item)"
                                                    class="h-7 text-xs font-semibold text-[#4B49AC] hover:bg-indigo-50 px-2 py-1 rounded-md flex items-center gap-1 border border-indigo-100"
                                                >
                                                    <Users class="w-3.5 h-3.5" /> {{ item.anggota_tim.length }} Anggota Tim <ChevronRight class="w-3 h-3" />
                                                </Button>
                                            </div>
                                        </TableCell>
                                        <TableCell class="text-gray-600 text-sm py-4">
                                            {{ formatDate(item.created_at) }}
                                        </TableCell>
                                        <TableCell class="text-center py-4">
                                            <Badge v-if="item.setuju_kaprodi === 1" variant="secondary" class="bg-emerald-100 text-emerald-800 border border-emerald-200 font-semibold px-3 py-1 rounded-md pointer-events-none shadow-none">
                                                <CheckCircle2 class="w-3.5 h-3.5 mr-1" /> Disetujui
                                            </Badge>
                                            <div v-else-if="item.setuju_kaprodi === 2" class="flex flex-col items-center gap-1">
                                                <Badge variant="secondary" class="bg-rose-100 text-rose-800 border border-rose-200 font-semibold px-3 py-1 rounded-md pointer-events-none shadow-none">
                                                    <XCircle class="w-3.5 h-3.5 mr-1" /> Ditolak
                                                </Badge>
                                                <button 
                                                    v-if="item.keterangan_ditolak" 
                                                    @click="openRejectionDetail(item)"
                                                    class="text-[10px] text-rose-600 hover:text-rose-800 font-bold underline decoration-rose-300 decoration-1 underline-offset-2 block mt-1 max-w-[120px] text-center leading-tight transition-colors"
                                                >
                                                    Lihat Alasan Detail
                                                </button>
                                            </div>
                                            <Badge v-else variant="secondary" class="bg-amber-100 text-amber-800 border border-amber-200 font-semibold px-3 py-1 rounded-md pointer-events-none shadow-none">
                                                <Clock class="w-3.5 h-3.5 mr-1 animate-spin" /> Menunggu
                                            </Badge>
                                        </TableCell>
                                        <TableCell class="text-center py-4">
                                            <Badge v-if="item.status === 1" variant="secondary" class="bg-indigo-100 text-[#4B49AC] border border-indigo-200 font-semibold px-3 py-1 rounded-md pointer-events-none shadow-none">
                                                Selesai & Tercetak
                                            </Badge>
                                            <Badge v-else variant="secondary" class="bg-gray-100 text-gray-600 border border-gray-200 font-semibold px-3 py-1 rounded-md pointer-events-none shadow-none">
                                                Proses Admin
                                            </Badge>
                                        </TableCell>
                                        <TableCell class="text-center py-4">
                                            <div class="flex items-center justify-center gap-2">
                                                <template v-if="item.mahasiswa_id === props.mahasiswa.id">
                                                    <Button 
                                                        v-if="item.setuju_kaprodi === 0 && item.status === 0"
                                                        @click="openEditSheet(item)" 
                                                        size="sm" 
                                                        variant="outline" 
                                                        class="border-gray-300 text-gray-700 hover:bg-gray-100 rounded-lg shadow-xs flex items-center gap-1.5"
                                                    >
                                                        <Edit class="w-3.5 h-3.5 text-[#4B49AC]" /> Edit
                                                    </Button>
                                                    <Button 
                                                        v-if="item.setuju_kaprodi === 0 && item.status === 0"
                                                        @click="confirmDelete(item.id)" 
                                                        size="sm" 
                                                        variant="destructive"
                                                        class="bg-rose-600 hover:bg-rose-700 text-white rounded-lg shadow-xs flex items-center gap-1.5"
                                                    >
                                                        <Trash2 class="w-3.5 h-3.5" /> Batal
                                                    </Button>
                                                    <span v-if="item.setuju_kaprodi === 1 || item.status === 1" class="text-xs text-gray-400 italic">
                                                        Terkunci (Telah Disetujui)
                                                    </span>
                                                    <span v-else-if="item.setuju_kaprodi === 2" class="text-xs text-rose-400 italic">
                                                        Ditolak
                                                    </span>
                                                </template>
                                                <template v-else>
                                                    <span class="text-xs text-amber-600 font-semibold bg-amber-50 px-2.5 py-1 rounded-md border border-amber-200">
                                                        Hanya Ketua Tim
                                                    </span>
                                                </template>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                </template>
                                <template v-else>
                                    <TableRow>
                                        <TableCell colspan="7" class="h-72 text-center">
                                            <div class="max-w-md mx-auto py-12">
                                                <div class="w-16 h-16 bg-[#4B49AC]/10 rounded-full flex items-center justify-center mx-auto mb-4 text-[#4B49AC]">
                                                    <Mail class="w-8 h-8 text-[#4B49AC]" />
                                                </div>
                                                <p class="text-base font-semibold text-gray-900">Belum Ada Riwayat Pengajuan</p>
                                                <p class="text-sm text-gray-500 mt-1">Anda belum pernah mengajukan permohonan surat. Klik tombol "Ajukan Surat Baru" di atas untuk memulai.</p>
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

        <!-- Form Pengajuan Sheet / Drawer -->
        <Sheet v-model:open="isSheetOpen">
            <SheetContent side="right" class="sm:max-w-xl w-full bg-white p-0 overflow-y-auto flex flex-col rounded-l-lg shadow-2xl [&>button]:text-white [&>button:hover]:bg-white/20 [&>button]:rounded-lg [&>button]:p-1 [&>button]:transition-colors border-l border-gray-200">
                <SheetHeader class="bg-[#4B49AC] text-white p-6 flex-shrink-0">
                    <SheetTitle class="text-xl font-bold text-white flex items-center gap-2">
                        <FileText class="w-6 h-6" /> 
                        {{ isEditMode ? 'Edit Pengajuan Surat' : 'Pengajuan Surat Baru' }}
                    </SheetTitle>
                    <SheetDescription class="text-indigo-100 text-xs mt-1">
                        Lengkapi informasi di bawah ini dengan seksama. Pastikan ejaan nama instansi dan alamat sudah tepat.
                    </SheetDescription>
                </SheetHeader>

                <form @submit.prevent="submitForm" class="p-6 space-y-6 flex-1 flex flex-col justify-between">
                    <div class="space-y-6">
                        <!-- Pilihan Jenis Surat (Hanya saat buat baru) -->
                        <div class="space-y-2">
                            <Label class="text-sm font-semibold text-gray-800">
                                Jenis Surat Permohonan <span class="text-red-500">*</span>
                            </Label>
                            <Popover v-model:open="openJenisCombobox">
                                <PopoverTrigger as-child>
                                    <Button
                                        variant="outline"
                                        role="combobox"
                                        :disabled="isEditMode"
                                        :aria-expanded="openJenisCombobox"
                                        class="w-full justify-between px-4 py-2.5 h-11 bg-white border border-gray-300 rounded-lg text-sm font-medium focus:ring-2 focus:ring-[#4B49AC] disabled:bg-gray-100 disabled:text-gray-500"
                                        :class="!form.jenis_permohonan ? 'text-gray-500 font-normal' : 'text-gray-900 font-semibold'"
                                    >
                                        {{ form.jenis_permohonan || '-- Pilih Jenis Permohonan Surat --' }}
                                        <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent class="w-[380px] sm:w-[500px] p-0 bg-white border border-gray-200 shadow-lg rounded-lg" align="start">
                                    <Command>
                                        <CommandInput placeholder="Cari jenis permohonan surat..." class="text-sm" />
                                        <CommandEmpty class="p-4 text-sm text-gray-500 text-center">Jenis surat tidak ditemukan.</CommandEmpty>
                                        <CommandList>
                                            <CommandGroup>
                                                <CommandItem
                                                    v-for="jenis in daftarJenisPermohonan"
                                                    :key="jenis"
                                                    :value="jenis"
                                                    class="text-sm py-2.5 px-3 hover:bg-indigo-50 cursor-pointer rounded-md"
                                                    @select="() => {
                                                        form.jenis_permohonan = jenis
                                                        openJenisCombobox = false
                                                    }"
                                                >
                                                    <Check
                                                        :class="form.jenis_permohonan === jenis ? 'opacity-100 text-[#4B49AC]' : 'opacity-0'"
                                                        class="mr-2 h-4 w-4 shrink-0"
                                                    />
                                                    {{ jenis }}
                                                </CommandItem>
                                            </CommandGroup>
                                        </CommandList>
                                    </Command>
                                </PopoverContent>
                            </Popover>
                            <span v-if="form.errors.jenis_permohonan" class="text-xs text-red-500 mt-1 block">{{ form.errors.jenis_permohonan }}</span>
                        </div>

                        <!-- Peringatan jika sudah punya pengajuan PKL aktif -->
                        <div v-if="['Izin PKL', 'Izin Memperoleh Data PKL', 'Izin Memperoleh Data TA'].includes(form.jenis_permohonan) && props.hasActivePkl && !isEditMode" class="p-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-900 text-xs flex items-center gap-3">
                            <AlertTriangle class="w-5 h-5 text-rose-600 flex-shrink-0" />
                            <div>
                                <span class="font-bold">Perhatian:</span> Anda sudah terdaftar dalam permohonan PKL atau Observasi yang sedang aktif pada tahun akademik ini. Anda tidak dapat membuat pengajuan baru dengan jenis ini sebelum permohonan aktif sebelumnya dibatalkan.
                            </div>
                        </div>

                        <div v-if="form.jenis_permohonan" class="p-4 rounded-lg bg-indigo-50/50 border border-indigo-100 space-y-6">
                            <div class="text-xs font-bold text-[#4B49AC] uppercase tracking-wider flex items-center gap-1.5 pb-2 border-b border-indigo-100">
                                <Info class="w-4 h-4" /> Detail Isian: {{ form.jenis_permohonan }}
                            </div>

                            <!-- Keterangan Aktif Kuliah -->
                            <template v-if="form.jenis_permohonan === 'Keterangan Aktif Kuliah'">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <Label class="text-xs font-semibold text-gray-700">Nama Orang Tua <span class="text-red-500">*</span></Label>
                                        <Input v-model="form.nama_orang_tua" placeholder="Nama lengkap orang tua" required class="rounded-lg bg-white text-sm" />
                                        <span v-if="form.errors.nama_orang_tua" class="text-xs text-red-500">{{ form.errors.nama_orang_tua }}</span>
                                    </div>
                                    <div class="space-y-2">
                                        <Label class="text-xs font-semibold text-gray-700">Pekerjaan Orang Tua <span class="text-red-500">*</span></Label>
                                        <Input v-model="form.pekerjaan" placeholder="Contoh: PNS / Wiraswasta" required class="rounded-lg bg-white text-sm" />
                                        <span v-if="form.errors.pekerjaan" class="text-xs text-red-500">{{ form.errors.pekerjaan }}</span>
                                    </div>
                                    <div class="space-y-2 md:col-span-2">
                                        <Label class="text-xs font-semibold text-gray-700">Alamat Orang Tua <span class="text-red-500">*</span></Label>
                                        <textarea v-model="form.alamat_orang_tua" rows="2" placeholder="Alamat lengkap tempat tinggal orang tua" required class="w-full p-3 bg-white border border-gray-300 rounded-lg text-sm focus:ring-[#4B49AC]"></textarea>
                                        <span v-if="form.errors.alamat_orang_tua" class="text-xs text-red-500">{{ form.errors.alamat_orang_tua }}</span>
                                    </div>
                                    <div class="space-y-2">
                                        <Label class="text-xs font-semibold text-gray-700">NIP / NRP (Opsional)</Label>
                                        <Input v-model="form.nip" placeholder="Nomor Induk (jika ada)" class="rounded-lg bg-white text-sm" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label class="text-xs font-semibold text-gray-700">Pangkat / Golongan (Opsional)</Label>
                                        <Input v-model="form.pangkat_golongan" placeholder="Contoh: Penata Tk. I / III d" class="rounded-lg bg-white text-sm" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label class="text-xs font-semibold text-gray-700">Nama Instansi / Kantor <span class="text-red-500">*</span></Label>
                                        <Input v-model="form.nama_instansi" placeholder="Contoh: Dinas Pendidikan" required class="rounded-lg bg-white text-sm" />
                                        <span v-if="form.errors.nama_instansi" class="text-xs text-red-500">{{ form.errors.nama_instansi }}</span>
                                    </div>
                                    <div class="space-y-2">
                                        <Label class="text-xs font-semibold text-gray-700">Keperluan <span class="text-red-500">*</span></Label>
                                        <Input v-model="form.keperluan" placeholder="Contoh: Tunjangan Gaji / Beasiswa" required class="rounded-lg bg-white text-sm" />
                                        <span v-if="form.errors.keperluan" class="text-xs text-red-500">{{ form.errors.keperluan }}</span>
                                    </div>
                                    <div class="space-y-2 md:col-span-2">
                                        <Label class="text-xs font-semibold text-gray-700">Alamat Instansi / Kantor <span class="text-red-500">*</span></Label>
                                        <textarea v-model="form.alamat_instansi" rows="2" placeholder="Alamat lengkap instansi tempat bekerja" required class="w-full p-3 bg-white border border-gray-300 rounded-lg text-sm focus:ring-[#4B49AC]"></textarea>
                                        <span v-if="form.errors.alamat_instansi" class="text-xs text-red-500">{{ form.errors.alamat_instansi }}</span>
                                    </div>
                                </div>
                            </template>

                            <!-- Cuti Kuliah -->
                            <template v-else-if="form.jenis_permohonan === 'Cuti Kuliah'">
                                <div class="space-y-2">
                                    <Label class="text-xs font-semibold text-gray-700">Alasan Mengajukan Cuti Kuliah <span class="text-red-500">*</span></Label>
                                    <textarea v-model="form.alasan_cuti" rows="4" placeholder="Jelaskan alasan pengajuan cuti akademik Anda secara rinci" required class="w-full p-3 bg-white border border-gray-300 rounded-lg text-sm focus:ring-[#4B49AC]"></textarea>
                                    <span v-if="form.errors.alasan_cuti" class="text-xs text-red-500">{{ form.errors.alasan_cuti }}</span>
                                </div>
                            </template>

                            <!-- Pindah Kelas -->
                            <template v-else-if="form.jenis_permohonan === 'Pindah Kelas'">
                                <div class="space-y-2">
                                    <Label class="text-xs font-semibold text-gray-700">Kelas Tujuan Perpindahan <span class="text-red-500">*</span></Label>
                                    <Input v-model="form.kelas_tujuan" placeholder="Contoh: Kelas B / Reguler Sore" required class="rounded-lg bg-white text-sm" />
                                    <span v-if="form.errors.kelas_tujuan" class="text-xs text-red-500">{{ form.errors.kelas_tujuan }}</span>
                                </div>
                            </template>

                            <!-- Pindah PT -->
                            <template v-else-if="form.jenis_permohonan === 'Pindah PT'">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <Label class="text-xs font-semibold text-gray-700">Perguruan Tinggi Tujuan <span class="text-red-500">*</span></Label>
                                        <Input v-model="form.pt_tujuan" placeholder="Nama universitas/politeknik tujuan" required class="rounded-lg bg-white text-sm" />
                                        <span v-if="form.errors.pt_tujuan" class="text-xs text-red-500">{{ form.errors.pt_tujuan }}</span>
                                    </div>
                                    <div class="space-y-2">
                                        <Label class="text-xs font-semibold text-gray-700">Status Akreditasi PT Tujuan <span class="text-red-500">*</span></Label>
                                        <Input v-model="form.status_akreditasi" placeholder="Contoh: Baik Sekali / B / A" required class="rounded-lg bg-white text-sm" />
                                        <span v-if="form.errors.status_akreditasi" class="text-xs text-red-500">{{ form.errors.status_akreditasi }}</span>
                                    </div>
                                </div>
                            </template>

                            <!-- Mengundurkan Diri -->
                            <template v-else-if="form.jenis_permohonan === 'Mengundurkan Diri'">
                                <div class="p-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-center gap-3">
                                    <AlertTriangle class="w-5 h-5 text-amber-600 flex-shrink-0" />
                                    <div>
                                        <span class="font-bold">Informasi Penting:</span> Pengajuan surat pengunduran diri akan diteruskan langsung ke Ketua Program Studi dan Wakil Direktur untuk proses wawancara & validasi akhir.
                                    </div>
                                </div>
                            </template>

                            <!-- Izin Memperoleh Data TA / PKL -->
                            <template v-else-if="form.jenis_permohonan === 'Izin Memperoleh Data TA' || form.jenis_permohonan === 'Izin Memperoleh Data PKL'">
                                <div class="space-y-4">
                                    <div class="space-y-2">
                                        <Label class="text-xs font-semibold text-gray-700">Judul Laporan / Penelitian <span class="text-red-500">*</span></Label>
                                        <Input v-model="form.judul_laporan" placeholder="Masukkan judul lengkap laporan tugas akhir atau PKL Anda" required class="rounded-lg bg-white text-sm" />
                                        <span v-if="form.errors.judul_laporan" class="text-xs text-red-500">{{ form.errors.judul_laporan }}</span>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-2">
                                            <Label class="text-xs font-semibold text-gray-700">Nama Instansi / Perusahaan <span class="text-red-500">*</span></Label>
                                            <Input v-model="form.nama_instansi" placeholder="Nama perusahaan tempat riset" required class="rounded-lg bg-white text-sm" />
                                            <span v-if="form.errors.nama_instansi" class="text-xs text-red-500">{{ form.errors.nama_instansi }}</span>
                                        </div>
                                        <div class="space-y-2">
                                            <Label class="text-xs font-semibold text-gray-700">Alamat Instansi <span class="text-red-500">*</span></Label>
                                            <Input v-model="form.alamat_instansi" placeholder="Kota / Kabupaten instansi" required class="rounded-lg bg-white text-sm" />
                                            <span v-if="form.errors.alamat_instansi" class="text-xs text-red-500">{{ form.errors.alamat_instansi }}</span>
                                        </div>
                                    </div>

                                    <div class="space-y-3 pt-2 border-t border-gray-200">
                                        <div class="flex items-center justify-between">
                                            <Label class="text-xs font-bold text-gray-800">Daftar Data yang Diminta <span class="text-red-500">*</span></Label>
                                            <Button 
                                                type="button" 
                                                @click="addDataDiminta" 
                                                size="sm" 
                                                variant="outline" 
                                                class="h-8 text-xs font-bold text-[#4B49AC] border-indigo-200 hover:bg-indigo-50 rounded-lg"
                                            >
                                                <PlusCircle class="w-3.5 h-3.5 mr-1" /> Tambah Item
                                            </Button>
                                        </div>

                                        <div v-for="(item, idx) in form.data_diminta" :key="idx" class="flex items-center gap-2">
                                            <Input 
                                                v-model="form.data_diminta[idx]" 
                                                :placeholder="`Data #${idx + 1} (Contoh: Struktur Organisasi / Laporan)`" 
                                                required 
                                                class="rounded-lg bg-white flex-1 text-sm" 
                                            />
                                            <Button 
                                                v-if="form.data_diminta.length > 1"
                                                type="button" 
                                                @click="removeDataDiminta(idx)" 
                                                variant="ghost" 
                                                size="icon" 
                                                class="text-red-500 hover:bg-red-50 h-10 w-10 flex-shrink-0 rounded-lg"
                                            >
                                                <X class="w-4 h-4" />
                                            </Button>
                                        </div>
                                        <span v-if="form.errors.data_diminta" class="text-xs text-red-500 block">{{ form.errors.data_diminta }}</span>
                                    </div>
                                </div>
                            </template>

                            <!-- Izin PKL -->
                            <template v-else-if="form.jenis_permohonan === 'Izin PKL'">
                                <div class="space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-2">
                                            <Label class="text-xs font-semibold text-gray-700">Nama Instansi / Perusahaan <span class="text-red-500">*</span></Label>
                                            <Input v-model="form.nama_instansi" placeholder="Nama instansi tujuan pelaksaan PKL" required class="rounded-lg bg-white text-sm" />
                                            <span v-if="form.errors.nama_instansi" class="text-xs text-red-500">{{ form.errors.nama_instansi }}</span>
                                        </div>
                                        <div class="space-y-2">
                                            <Label class="text-xs font-semibold text-gray-700">Pimpinan / Penanggung Jawab <span class="text-red-500">*</span></Label>
                                            <Input v-model="form.pimpinan" placeholder="Contoh: Kepala Dinas / Manajer HRD" required class="rounded-lg bg-white text-sm" />
                                            <span v-if="form.errors.pimpinan" class="text-xs text-red-500">{{ form.errors.pimpinan }}</span>
                                        </div>
                                        <div class="space-y-2 md:col-span-2">
                                            <Label class="text-xs font-semibold text-gray-700">Alamat Lengkap Instansi <span class="text-red-500">*</span></Label>
                                            <textarea v-model="form.alamat_instansi" rows="2" placeholder="Alamat jalan, kelurahan, kecamatan, dan kota instansi" required class="w-full p-3 bg-white border border-gray-300 rounded-lg text-sm focus:ring-[#4B49AC]"></textarea>
                                            <span v-if="form.errors.alamat_instansi" class="text-xs text-red-500">{{ form.errors.alamat_instansi }}</span>
                                        </div>
                                        <div class="space-y-2">
                                            <Label class="text-xs font-semibold text-gray-700">Tanggal Mulai PKL <span class="text-red-500">*</span></Label>
                                            <Input type="date" v-model="form.tanggal_mulai" required class="rounded-lg bg-white text-sm" />
                                            <span v-if="form.errors.tanggal_mulai" class="text-xs text-red-500">{{ form.errors.tanggal_mulai }}</span>
                                        </div>
                                        <div class="space-y-2">
                                            <Label class="text-xs font-semibold text-gray-700">Tanggal Selesai PKL <span class="text-red-500">*</span></Label>
                                            <Input type="date" v-model="form.tanggal_selesai" required class="rounded-lg bg-white text-sm" />
                                            <span v-if="form.errors.tanggal_selesai" class="text-xs text-red-500">{{ form.errors.tanggal_selesai }}</span>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- Pemilihan Anggota Tim / Kelompok -->
                            <div v-if="['Izin Memperoleh Data TA', 'Izin Memperoleh Data PKL', 'Izin PKL'].includes(form.jenis_permohonan)" class="space-y-3 pt-4 border-t border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <Label class="text-xs font-bold text-gray-800">Daftar Anggota Tim / Kelompok (Opsional)</Label>
                                        <p class="text-[11px] text-gray-500">Maksimal 5 anggota (selain Anda sebagai Ketua).</p>
                                    </div>
                                    <Button 
                                        v-if="form.anggota_tim.length < 5"
                                        type="button" 
                                        @click="addAnggotaTim" 
                                        size="sm" 
                                        variant="outline" 
                                        class="h-8 text-xs font-bold text-[#4B49AC] border-indigo-200 hover:bg-indigo-50 rounded-lg"
                                    >
                                        <PlusCircle class="w-3.5 h-3.5 mr-1" /> Tambah Anggota
                                    </Button>
                                </div>

                                <div v-for="(anggotaId, idx) in form.anggota_tim" :key="idx" class="flex items-center gap-2">
                                    <Popover v-model:open="openAnggotaCombobox[idx]">
                                        <PopoverTrigger as-child>
                                            <Button
                                                variant="outline"
                                                role="combobox"
                                                :aria-expanded="openAnggotaCombobox[idx]"
                                                class="flex-1 justify-between px-4 py-2.5 h-10 bg-white border border-gray-300 rounded-lg text-xs font-medium focus:ring-2 focus:ring-[#4B49AC]"
                                            >
                                                {{ getMahasiswaLabel(form.anggota_tim[idx]) }}
                                                <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                            </Button>
                                        </PopoverTrigger>
                                        <PopoverContent class="w-[340px] sm:w-[450px] p-0 bg-white border border-gray-200 shadow-lg rounded-lg" align="start">
                                            <Command>
                                                <CommandInput placeholder="Cari NIM atau Nama Mahasiswa..." class="text-xs" />
                                                <CommandEmpty class="p-4 text-xs text-gray-500 text-center">Mahasiswa tidak ditemukan.</CommandEmpty>
                                                <CommandList>
                                                    <CommandGroup>
                                                        <CommandItem
                                                            v-for="mhs in props.daftarMahasiswa"
                                                            :key="mhs.id"
                                                            :value="`${mhs.nim} ${mhs.nama_lengkap}`"
                                                            :disabled="form.anggota_tim.includes(mhs.id) && form.anggota_tim[idx] !== mhs.id"
                                                            class="text-xs py-2 px-3 hover:bg-indigo-50 cursor-pointer rounded-md"
                                                            @select="() => {
                                                                form.anggota_tim[idx] = mhs.id
                                                                openAnggotaCombobox[idx] = false
                                                            }"
                                                        >
                                                            <Check
                                                                :class="form.anggota_tim[idx] === mhs.id ? 'opacity-100 text-[#4B49AC]' : 'opacity-0'"
                                                                class="mr-2 h-4 w-4 shrink-0"
                                                            />
                                                            {{ mhs.nim }} - {{ mhs.nama_lengkap }}
                                                        </CommandItem>
                                                    </CommandGroup>
                                                </CommandList>
                                            </Command>
                                        </PopoverContent>
                                    </Popover>
                                    <Button 
                                        type="button" 
                                        @click="removeAnggotaTim(idx)" 
                                        variant="ghost" 
                                        size="icon" 
                                        class="text-red-500 hover:bg-red-50 h-10 w-10 flex-shrink-0 rounded-lg"
                                    >
                                        <X class="w-4 h-4" />
                                    </Button>
                                </div>
                                <span v-if="form.errors.anggota_tim" class="text-xs text-red-500 block">{{ form.errors.anggota_tim }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-6 mt-6 border-t border-gray-100">
                        <Button 
                            type="button" 
                            variant="outline" 
                            @click="isSheetOpen = false" 
                            class="font-semibold px-6 rounded-lg"
                        >
                            Batal
                        </Button>
                        <Button 
                            type="submit" 
                            :disabled="form.processing || !form.jenis_permohonan || (['Izin PKL', 'Izin Memperoleh Data PKL', 'Izin Memperoleh Data TA'].includes(form.jenis_permohonan) && props.hasActivePkl && !isEditMode)"
                            class="bg-[#4B49AC] hover:bg-[#3f3d91] text-white font-bold flex items-center gap-2 px-8 shadow-md rounded-lg disabled:opacity-50"
                        >
                            <RefreshCw v-if="form.processing" class="w-4 h-4 animate-spin" />
                            <Send v-else class="w-4 h-4" />
                            {{ isEditMode ? 'Simpan Perubahan' : 'Ajukan Permohonan' }}
                        </Button>
                    </div>
                </form>
            </SheetContent>
        </Sheet>

        <!-- Dialog Detail Anggota Tim -->
        <!-- Dialog Detail Penolakan -->
        <Dialog v-model:open="showRejectionDetail">
            <DialogContent class="sm:max-w-md bg-white p-0 overflow-hidden rounded-lg border border-gray-200 shadow-xl [&>button]:rounded-lg [&>button]:top-4 [&>button]:right-4">
                <DialogHeader class="bg-rose-600 text-white p-6">
                    <DialogTitle class="text-white text-xl font-bold flex items-center gap-2">
                        <XCircle class="w-6 h-6" /> Detail Penolakan
                    </DialogTitle>
                    <DialogDesc class="text-rose-100 text-xs mt-1">
                        Informasi lengkap alasan penolakan dari Kaprodi
                    </DialogDesc>
                </DialogHeader>

                <div class="p-6 space-y-4">
                    <div class="space-y-3">
                        <div class="text-xs font-bold uppercase text-gray-500 tracking-wider">Jenis Permohonan</div>
                        <div class="font-bold text-gray-900 text-base">
                            {{ rejectionDetail?.jenis_permohonan?.replace('Ijin', 'Izin') || '-' }}
                        </div>
                    </div>

                    <div class="space-y-3 pt-2 border-t border-gray-100">
                        <div class="text-xs font-bold uppercase text-gray-500 tracking-wider">Alasan Penolakan</div>
                        <div class="p-4 bg-rose-50 border border-rose-100 rounded-lg text-rose-800 text-sm italic leading-relaxed whitespace-pre-line">
                            "{{ rejectionDetail?.keterangan_ditolak || 'Tidak ada alasan spesifik yang dicantumkan.' }}"
                        </div>
                    </div>
                </div>

                <div class="p-6 pt-0 bg-gray-50 flex justify-end">
                    <Button @click="showRejectionDetail = false" class="bg-rose-600 hover:bg-rose-700 text-white rounded-lg px-6 font-bold text-sm">
                        Tutup
                    </Button>
                </div>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="showDetailAnggotaModal">
            <DialogContent class="sm:max-w-md bg-white p-0 overflow-hidden rounded-lg border border-gray-200 shadow-xl [&>button]:rounded-lg [&>button]:top-4 [&>button]:right-4">
                <DialogHeader class="bg-[#4B49AC] text-white p-6">
                    <DialogTitle class="text-white text-xl font-bold flex items-center gap-2">
                        <Users class="w-6 h-6" /> Detail Tim / Kelompok
                    </DialogTitle>
                    <DialogDesc class="text-indigo-100 text-xs mt-1">
                        Daftar lengkap ketua dan anggota pengajuan permohonan surat
                    </DialogDesc>
                </DialogHeader>

                <div class="p-6 space-y-4">
                    <div class="space-y-3">
                        <div class="text-xs font-bold uppercase text-gray-500 tracking-wider">Ketua Pengusul</div>
                        <div class="flex items-center gap-3 p-3 bg-indigo-50/50 border border-indigo-100 rounded-lg">
                            <div class="w-9 h-9 rounded-full bg-[#4B49AC] text-white font-bold flex items-center justify-center text-sm">
                                {{ selectedSuratAnggota?.mahasiswa?.nama_lengkap ? selectedSuratAnggota.mahasiswa.nama_lengkap.charAt(0) : 'K' }}
                            </div>
                            <div>
                                <div class="font-bold text-sm text-gray-900">{{ selectedSuratAnggota?.mahasiswa?.nama_lengkap || '-' }}</div>
                                <div class="text-xs text-gray-500">NIM: {{ selectedSuratAnggota?.mahasiswa?.nim || '-' }}</div>
                            </div>
                            <span class="ml-auto bg-[#4B49AC] text-white text-[10px] font-bold px-2 py-0.5 rounded">Ketua</span>
                        </div>
                    </div>

                    <div v-if="selectedSuratAnggota?.anggota_tim && selectedSuratAnggota.anggota_tim.length > 0" class="space-y-3 pt-2 border-t border-gray-100">
                        <div class="text-xs font-bold uppercase text-gray-500 tracking-wider">Anggota Tim ({{ selectedSuratAnggota.anggota_tim.length }})</div>
                        <div v-for="idAnggota in selectedSuratAnggota.anggota_tim" :key="idAnggota" class="flex items-center gap-3 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                            <div class="w-9 h-9 rounded-full bg-gray-300 text-gray-700 font-bold flex items-center justify-center text-sm">
                                {{ getMahasiswaInitials(idAnggota) }}
                            </div>
                            <div>
                                <div class="font-bold text-sm text-gray-900">{{ getMahasiswaName(idAnggota) }}</div>
                                <div class="text-xs text-gray-500">NIM: {{ getMahasiswaNim(idAnggota) }}</div>
                            </div>
                            <span class="ml-auto text-gray-600 border border-gray-300 bg-white text-[10px] font-bold px-2 py-0.5 rounded">Anggota</span>
                        </div>
                    </div>
                </div>

                <div class="p-6 pt-0 bg-gray-50 flex justify-end">
                    <Button @click="showDetailAnggotaModal = false" class="bg-[#4B49AC] hover:bg-[#3f3d91] text-white rounded-lg px-6 font-bold text-sm">
                        Tutup
                    </Button>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Dialog Konfirmasi Pembatalan/Hapus -->
        <Dialog v-model:open="showDeleteConfirm">
            <DialogContent class="sm:max-w-md bg-white p-0 overflow-hidden rounded-lg border border-gray-200 shadow-xl">
                <DialogHeader class="bg-rose-600 text-white p-6">
                    <DialogTitle class="text-white text-xl font-bold flex items-center gap-2">
                        <AlertTriangle class="w-6 h-6" /> Konfirmasi Pembatalan Surat
                    </DialogTitle>
                    <DialogDesc class="text-rose-100 text-xs mt-1">
                        Tindakan ini tidak dapat dibatalkan
                    </DialogDesc>
                </DialogHeader>

                <div class="p-6 space-y-4 text-sm text-gray-700 leading-relaxed">
                    Apakah Anda yakin ingin membatalkan dan menghapus pengajuan permohonan surat ini dari sistem?
                </div>

                <DialogFoot class="p-6 pt-0 gap-3 flex flex-row">
                    <button 
                        type="button" 
                        @click="showDeleteConfirm = false"
                        class="px-6 py-2.5 rounded-lg border border-gray-300 font-bold text-sm text-gray-700 hover:bg-gray-100 transition-all text-center cursor-pointer flex-shrink-0"
                    >
                        Batal
                    </button>
                    <button 
                        type="button" 
                        @click="executeDelete"
                        :disabled="deleteForm.processing"
                        class="flex-1 px-6 py-2.5 rounded-lg bg-rose-600 hover:bg-rose-700 disabled:bg-gray-300 text-white font-bold text-sm shadow-md transition-all flex items-center justify-center gap-2 text-center cursor-pointer"
                    >
                        <RefreshCw v-if="deleteForm.processing" class="w-4 h-4 animate-spin" />
                        <Trash2 v-else class="w-4 h-4" /> Ya, Hapus Pengajuan
                    </button>
                </DialogFoot>
            </DialogContent>
        </Dialog>

        <!-- Toast Notification -->
        <transition name="toast">
            <div v-if="showToast" class="fixed bottom-10 right-10 z-[100]">
                <div class="bg-[#1F2937] text-white px-6 py-4 rounded-lg shadow-2xl flex items-center gap-3 border border-gray-700 backdrop-blur-sm bg-opacity-95">
                    <div :class="toastType === 'error' ? 'bg-red-500' : 'bg-green-500'" class="p-1.5 rounded-md">
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
