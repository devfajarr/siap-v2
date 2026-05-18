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
import { Calendar, FileText, Image as ImageIcon, Plus, Upload, Trash2, Eye, Download, Clock, CheckCircle2, XCircle, RefreshCw, AlertCircle, ExternalLink, ChevronLeft, ChevronRight } from 'lucide-vue-next'

const props = defineProps({
    kalender: {
        type: Object,
        default: null,
    },
    brosurs: {
        type: Object,
        required: true,
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

// Helper URL Asset
const getAssetUrl = (nama) => {
    if (!nama) return '#'
    if (nama === 'kalender.pdf') return '/images/kalender/kalender.pdf'
    if (nama.startsWith('storage/')) return '/' + nama
    return '/' + nama
}

// Modal & Form Kalender
const isKalenderModalOpen = ref(false)
const kalenderFilePreviewName = ref('')
const kalenderFilePreviewSize = ref('')

const formKalender = useForm({
    pdf_file: null,
})

const onKalenderFileChange = (e) => {
    const file = e.target.files[0]
    if (file) {
        if (file.type !== 'application/pdf') {
            triggerToast('Hanya file dokumen PDF yang diperbolehkan.', 'error')
            e.target.value = ''
            formKalender.pdf_file = null
            kalenderFilePreviewName.value = ''
            return
        }
        if (file.size > 5 * 1024 * 1024) { // 5MB
            triggerToast('Ukuran file maksimal 5MB.', 'error')
            e.target.value = ''
            formKalender.pdf_file = null
            kalenderFilePreviewName.value = ''
            return
        }
        formKalender.pdf_file = file
        kalenderFilePreviewName.value = file.name
        kalenderFilePreviewSize.value = (file.size / (1024 * 1024)).toFixed(2) + ' MB'
    } else {
        formKalender.pdf_file = null
        kalenderFilePreviewName.value = ''
        kalenderFilePreviewSize.value = ''
    }
}

const submitKalender = () => {
    if (!formKalender.pdf_file) {
        triggerToast('Silakan pilih file PDF terlebih dahulu.', 'error')
        return
    }
    formKalender.post('/v2/admin/informasi-tambahan/kalender', {
        preserveScroll: true,
        onSuccess: () => {
            isKalenderModalOpen.value = false
            formKalender.reset()
            kalenderFilePreviewName.value = ''
            kalenderFilePreviewSize.value = ''
        }
    })
}

// Modal & Form Brosur
const isBrosurModalOpen = ref(false)
const brosurImagePreviewUrl = ref(null)
const brosurFilePreviewName = ref('')
const brosurFilePreviewSize = ref('')

const formBrosur = useForm({
    gambar_brosur: null,
    keterangan_brosur: '',
})

const onBrosurFileChange = (e) => {
    const file = e.target.files[0]
    if (file) {
        if (!file.type.startsWith('image/')) {
            triggerToast('Hanya file gambar yang diperbolehkan.', 'error')
            e.target.value = ''
            formBrosur.gambar_brosur = null
            brosurImagePreviewUrl.value = null
            brosurFilePreviewName.value = ''
            return
        }
        if (file.size > 5 * 1024 * 1024) { // 5MB
            triggerToast('Ukuran gambar maksimal 5MB.', 'error')
            e.target.value = ''
            formBrosur.gambar_brosur = null
            brosurImagePreviewUrl.value = null
            brosurFilePreviewName.value = ''
            return
        }
        formBrosur.gambar_brosur = file
        brosurImagePreviewUrl.value = URL.createObjectURL(file)
        brosurFilePreviewName.value = file.name
        brosurFilePreviewSize.value = (file.size / (1024 * 1024)).toFixed(2) + ' MB'
    } else {
        formBrosur.gambar_brosur = null
        brosurImagePreviewUrl.value = null
        brosurFilePreviewName.value = ''
        brosurFilePreviewSize.value = ''
    }
}

const submitBrosur = () => {
    if (!formBrosur.gambar_brosur) {
        triggerToast('Silakan pilih file gambar brosur terlebih dahulu.', 'error')
        return
    }
    formBrosur.post('/v2/admin/informasi-tambahan/brosur', {
        preserveScroll: true,
        onSuccess: () => {
            isBrosurModalOpen.value = false
            formBrosur.reset()
            brosurImagePreviewUrl.value = null
            brosurFilePreviewName.value = ''
            brosurFilePreviewSize.value = ''
        }
    })
}

// Confirm Delete Dialog
const isDeleteDialogOpen = ref(false)
const itemToDelete = ref(null)

const confirmDelete = (item) => {
    itemToDelete.value = item
    isDeleteDialogOpen.value = true
}

const executeDelete = () => {
    if (itemToDelete.value) {
        router.delete(`/v2/admin/informasi-tambahan/${itemToDelete.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                isDeleteDialogOpen.value = false
                itemToDelete.value = null
            }
        })
    }
}

const formatDate = (dateStr) => {
    if (!dateStr) return '-'
    const date = new Date(dateStr)
    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

const openKalenderPreview = () => {
    if (props.kalender) {
        const url = getAssetUrl(props.kalender.nama) + '?v=' + new Date(props.kalender.updated_at || Date.now()).getTime()
        window.open(url, '_blank')
    }
}
</script>

<template>
    <Head title="Informasi Tambahan" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <span class="text-gray-500">Data Master</span>
                <span class="text-gray-400">/</span>
                <span class="text-gray-800 font-semibold">Informasi Tambahan</span>
            </div>
        </template>

        <div class="py-12 space-y-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-8">
                <!-- Title Section -->
                <div>
                    <h1 class="text-2xl font-bold text-[#1F2937]">Kelola Informasi Tambahan</h1>
                    <p class="text-[#6B7280]">Manajemen file Kalender Akademik dan Galeri Brosur informasi untuk ditampilkan di halaman utama.</p>
                </div>

                <!-- Bagian 1: Kalender Akademik -->
                <Card class="shadow-sm border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-[#4B49AC] to-[#3f3d91] p-6 text-white flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-white/10 rounded-2xl backdrop-blur-sm flex items-center justify-center">
                                <Calendar class="w-8 h-8 text-white" />
                            </div>
                            <div>
                                <h2 class="text-xl font-bold">Kalender Akademik</h2>
                                <p class="text-indigo-100 text-sm mt-0.5">Dokumen panduan jadwal perkuliahan aktif untuk seluruh civitas akademika.</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <Button v-if="kalender" @click="openKalenderPreview" variant="secondary" class="bg-white hover:bg-gray-100 text-[#4B49AC] font-semibold flex items-center gap-2 shadow-sm">
                                <ExternalLink class="w-4 h-4" /> Buka Pratinjau
                            </Button>
                            <Button @click="isKalenderModalOpen = true" class="bg-white/20 hover:bg-white/30 border border-white/30 text-white font-semibold flex items-center gap-2 backdrop-blur-sm shadow-sm">
                                <Upload class="w-4 h-4" /> Perbarui Kalender
                            </Button>
                        </div>
                    </div>
                    <CardContent class="p-6 bg-white">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 bg-gray-50/75 rounded-xl border border-gray-200/80 gap-4">
                            <div class="flex items-center gap-3">
                                <FileText class="w-6 h-6 text-[#4B49AC]" />
                                <div>
                                    <span class="text-sm font-semibold text-gray-900 block">Status Dokumen:</span>
                                    <div class="flex items-center gap-2 mt-1">
                                        <Badge v-if="kalender" variant="secondary" class="bg-emerald-100 text-emerald-800 font-medium px-2.5 py-0.5">
                                            <CheckCircle2 class="w-3.5 h-3.5 mr-1 inline" /> Tersedia
                                        </Badge>
                                        <Badge v-else variant="secondary" class="bg-rose-100 text-rose-800 font-medium px-2.5 py-0.5">
                                            <XCircle class="w-3.5 h-3.5 mr-1 inline" /> Belum Diunggah
                                        </Badge>
                                        <span v-if="kalender" class="text-xs text-gray-500 font-medium flex items-center gap-1">
                                            <Clock class="w-3.5 h-3.5 ml-1" /> Diperbarui: {{ formatDate(kalender.updated_at) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div v-if="kalender" class="text-sm text-gray-600 font-mono text-right w-full sm:w-auto">
                                File: <span class="font-semibold text-gray-800">{{ kalender.nama.split('/').pop() }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Bagian 2: Galeri Brosur -->
                <Card class="shadow-sm border-gray-200">
                    <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-indigo-50 rounded-xl text-[#4B49AC]">
                                <ImageIcon class="w-6 h-6" />
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">Galeri Brosur Informasi</h2>
                                <p class="text-sm text-gray-500">Manajemen gambar banner dan brosur pada halaman beranda.</p>
                            </div>
                        </div>
                        <Button @click="isBrosurModalOpen = true" class="bg-[#4B49AC] hover:bg-[#3f3d91] text-white font-semibold flex items-center gap-2 shadow-sm">
                            <Plus class="w-4 h-4" /> Tambah Brosur
                        </Button>
                    </div>

                    <CardContent class="p-0">
                        <div class="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow class="bg-gray-50/75">
                                        <TableHead class="w-[60px] font-semibold text-gray-700">#</TableHead>
                                        <TableHead class="w-[200px] font-semibold text-gray-700">Pratinjau</TableHead>
                                        <TableHead class="font-semibold text-gray-700">Keterangan / Judul</TableHead>
                                        <TableHead class="w-[180px] font-semibold text-gray-700">Tgl Diunggah</TableHead>
                                        <TableHead class="w-[120px] font-semibold text-center text-gray-700">Aksi</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <template v-if="brosurs.data.length > 0">
                                        <TableRow v-for="(item, index) in brosurs.data" :key="item.id" class="hover:bg-gray-50/50 transition-colors">
                                            <TableCell class="font-medium text-gray-900">
                                                {{ (brosurs.current_page - 1) * brosurs.per_page + index + 1 }}
                                            </TableCell>
                                            <TableCell>
                                                <a :href="getAssetUrl(item.nama)" target="_blank" class="block overflow-hidden rounded-lg border border-gray-200/80 hover:opacity-90 transition-opacity max-h-24 bg-gray-100">
                                                    <img :src="getAssetUrl(item.nama)" alt="Brosur" class="w-full h-full object-cover" />
                                                </a>
                                            </TableCell>
                                            <TableCell class="text-gray-800 font-medium">
                                                {{ item.keterangan || '-' }}
                                            </TableCell>
                                            <TableCell class="text-gray-600 text-xs">
                                                {{ formatDate(item.created_at) }}
                                            </TableCell>
                                            <TableCell class="text-center">
                                                <Button @click="confirmDelete(item)" size="sm" variant="destructive" class="bg-red-500 hover:bg-red-600 text-white shadow-sm flex items-center gap-1.5 mx-auto">
                                                    <Trash2 class="w-4 h-4" /> Hapus
                                                </Button>
                                            </TableCell>
                                        </TableRow>
                                    </template>
                                    <template v-else>
                                        <TableRow>
                                            <TableCell colspan="5" class="h-64 text-center">
                                                <div class="max-w-md mx-auto py-12">
                                                    <div class="w-16 h-16 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-4 text-[#4B49AC]">
                                                        <ImageIcon class="w-8 h-8 text-[#4B49AC]" />
                                                    </div>
                                                    <p class="text-base font-semibold text-gray-900">Belum ada brosur yang diunggah</p>
                                                    <p class="text-sm text-gray-500 mt-1">Klik tombol "Tambah Brosur" di atas untuk mulai mengunggah aset gambar informasi.</p>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    </template>
                                </TableBody>
                            </Table>
                        </div>

                        <!-- Pagination Brosur -->
                        <div v-if="brosurs.total > 0" class="p-4 border-t border-gray-100 flex items-center justify-between bg-white rounded-b-lg">
                            <p class="text-sm text-gray-600">
                                Menampilkan <span class="font-semibold">{{ brosurs.from || 0 }}</span> sampai <span class="font-semibold">{{ brosurs.to || 0 }}</span> dari <span class="font-semibold">{{ brosurs.total }}</span> data
                            </p>
                            <div class="flex items-center gap-1.5">
                                <Button 
                                    variant="outline" 
                                    size="sm" 
                                    :disabled="!brosurs.prev_page_url"
                                    @click="router.get(brosurs.prev_page_url, {}, { preserveState: true })"
                                    class="rounded-lg h-8 w-8 p-0"
                                >
                                    <ChevronLeft class="w-4 h-4" />
                                </Button>
                                <div class="flex items-center gap-1">
                                    <Button 
                                        v-for="pageLink in brosurs.links.slice(1, -1)" 
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
                                    :disabled="!brosurs.next_page_url"
                                    @click="router.get(brosurs.next_page_url, {}, { preserveState: true })"
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

        <!-- Modal Upload Kalender -->
        <Dialog v-model:open="isKalenderModalOpen">
            <DialogContent class="max-w-lg bg-white p-6 rounded-2xl shadow-2xl">
                <DialogHeader class="bg-[#4B49AC] text-white p-6 -m-6 mb-6 rounded-t-2xl">
                    <DialogTitle class="text-xl font-bold text-white flex items-center gap-2">
                        <Upload class="w-5 h-5" /> Perbarui Kalender Akademik
                    </DialogTitle>
                    <DialogDescription class="text-indigo-100 text-sm mt-1">
                        Unggah file dokumen PDF kalender akademik terbaru. File lama akan digantikan secara otomatis.
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitKalender" class="space-y-6">
                    <div class="space-y-3">
                        <Label for="kalender_file" class="text-sm font-semibold text-gray-800">
                            Pilih File PDF <span class="text-red-500">*</span>
                        </Label>
                        
                        <div class="relative border-2 border-dashed border-gray-300 hover:border-[#4B49AC] rounded-2xl p-6 text-center transition-colors bg-gray-50/50 cursor-pointer">
                            <input 
                                id="kalender_file" 
                                type="file" 
                                accept="application/pdf" 
                                @change="onKalenderFileChange" 
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" 
                            />
                            
                            <div v-if="!kalenderFilePreviewName" class="space-y-2">
                                <div class="w-12 h-12 bg-[#4B49AC]/10 rounded-full flex items-center justify-center mx-auto text-[#4B49AC]">
                                    <FileText class="w-6 h-6" />
                                </div>
                                <p class="text-sm font-semibold text-gray-800">Klik untuk memilih atau seret file PDF ke sini</p>
                                <p class="text-xs text-gray-500">Maksimal 5MB (Format: .pdf)</p>
                            </div>
                            
                            <div v-else class="flex items-center justify-between p-3 bg-white rounded-xl border border-gray-200 shadow-sm relative z-20">
                                <div class="flex items-center gap-3 overflow-hidden">
                                    <FileText class="w-8 h-8 text-rose-500 flex-shrink-0" />
                                    <div class="text-left overflow-hidden">
                                        <p class="text-sm font-semibold text-gray-900 truncate">{{ kalenderFilePreviewName }}</p>
                                        <p class="text-xs text-gray-500 font-mono">{{ kalenderFilePreviewSize }}</p>
                                    </div>
                                </div>
                                <Button type="button" variant="ghost" size="sm" @click.stop="formKalender.pdf_file = null; kalenderFilePreviewName = ''" class="text-gray-400 hover:text-red-500 h-8 w-8 p-0 rounded-lg">
                                    <XCircle class="w-5 h-5" />
                                </Button>
                            </div>
                        </div>
                        <span v-if="formKalender.errors.pdf_file" class="text-xs text-red-500 mt-1 block">{{ formKalender.errors.pdf_file }}</span>
                    </div>

                    <!-- Progress Bar Upload Kalender -->
                    <div v-if="formKalender.progress" class="space-y-1">
                        <div class="flex justify-between text-xs font-semibold text-gray-600">
                            <span>Mengunggah file...</span>
                            <span>{{ formKalender.progress.percentage }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                            <div class="bg-[#4B49AC] h-full transition-all duration-300" :style="{ width: formKalender.progress.percentage + '%' }"></div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <Button type="button" variant="outline" @click="isKalenderModalOpen = false" class="font-semibold">
                            Batal
                        </Button>
                        <Button type="submit" :disabled="formKalender.processing || !formKalender.pdf_file" class="bg-[#4B49AC] hover:bg-[#3f3d91] text-white font-semibold flex items-center gap-2 px-6 shadow">
                            <RefreshCw v-if="formKalender.processing" class="w-4 h-4 animate-spin" />
                            <Upload v-else class="w-4 h-4" />
                            Simpan Dokumen
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Modal Tambah Brosur -->
        <Dialog v-model:open="isBrosurModalOpen">
            <DialogContent class="max-w-lg bg-white p-6 rounded-2xl shadow-2xl">
                <DialogHeader class="bg-[#4B49AC] text-white p-6 -m-6 mb-6 rounded-t-2xl">
                    <DialogTitle class="text-xl font-bold text-white flex items-center gap-2">
                        <Plus class="w-5 h-5" /> Tambah Brosur Informasi
                    </DialogTitle>
                    <DialogDescription class="text-indigo-100 text-sm mt-1">
                        Unggah gambar brosur baru beserta keterangan singkat untuk ditampilkan di beranda.
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitBrosur" class="space-y-6">
                    <!-- File Input Gambar -->
                    <div class="space-y-3">
                        <Label for="brosur_file" class="text-sm font-semibold text-gray-800">
                            Pilih Gambar Brosur <span class="text-red-500">*</span>
                        </Label>
                        
                        <div class="relative border-2 border-dashed border-gray-300 hover:border-[#4B49AC] rounded-2xl p-6 text-center transition-colors bg-gray-50/50 cursor-pointer overflow-hidden">
                            <input 
                                id="brosur_file" 
                                type="file" 
                                accept="image/*" 
                                @change="onBrosurFileChange" 
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" 
                            />
                            
                            <div v-if="!brosurImagePreviewUrl" class="space-y-2">
                                <div class="w-12 h-12 bg-[#4B49AC]/10 rounded-full flex items-center justify-center mx-auto text-[#4B49AC]">
                                    <ImageIcon class="w-6 h-6" />
                                </div>
                                <p class="text-sm font-semibold text-gray-800">Klik untuk memilih atau seret file gambar ke sini</p>
                                <p class="text-xs text-gray-500">Maksimal 5MB (Format: .jpg, .png, .webp)</p>
                            </div>
                            
                            <div v-else class="space-y-3 relative z-20">
                                <div class="max-h-48 overflow-hidden rounded-xl border border-gray-200 shadow-inner bg-gray-100 flex items-center justify-center">
                                    <img :src="brosurImagePreviewUrl" alt="Preview" class="max-h-48 object-contain" />
                                </div>
                                <div class="flex items-center justify-between p-2.5 bg-white rounded-xl border border-gray-200 shadow-sm">
                                    <div class="text-left overflow-hidden">
                                        <p class="text-sm font-semibold text-gray-900 truncate">{{ brosurFilePreviewName }}</p>
                                        <p class="text-xs text-gray-500 font-mono">{{ brosurFilePreviewSize }}</p>
                                    </div>
                                    <Button type="button" variant="ghost" size="sm" @click.stop="formBrosur.gambar_brosur = null; brosurImagePreviewUrl = null" class="text-gray-400 hover:text-red-500 h-8 w-8 p-0 rounded-lg">
                                        <XCircle class="w-5 h-5" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                        <span v-if="formBrosur.errors.gambar_brosur" class="text-xs text-red-500 mt-1 block">{{ formBrosur.errors.gambar_brosur }}</span>
                    </div>

                    <!-- Input Keterangan -->
                    <div class="space-y-2">
                        <Label for="keterangan" class="text-sm font-semibold text-gray-800">
                            Keterangan / Judul Brosur <span class="text-red-500">*</span>
                        </Label>
                        <textarea 
                            id="keterangan" 
                            v-model="formBrosur.keterangan_brosur" 
                            rows="3" 
                            placeholder="Masukkan keterangan singkat brosur..." 
                            required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#4B49AC] text-sm text-gray-800 bg-white"
                        ></textarea>
                        <span v-if="formBrosur.errors.keterangan_brosur" class="text-xs text-red-500 mt-1 block">{{ formBrosur.errors.keterangan_brosur }}</span>
                    </div>

                    <!-- Progress Bar Upload Brosur -->
                    <div v-if="formBrosur.progress" class="space-y-1">
                        <div class="flex justify-between text-xs font-semibold text-gray-600">
                            <span>Mengunggah gambar...</span>
                            <span>{{ formBrosur.progress.percentage }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                            <div class="bg-[#4B49AC] h-full transition-all duration-300" :style="{ width: formBrosur.progress.percentage + '%' }"></div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <Button type="button" variant="outline" @click="isBrosurModalOpen = false" class="font-semibold">
                            Batal
                        </Button>
                        <Button type="submit" :disabled="formBrosur.processing || !formBrosur.gambar_brosur" class="bg-[#4B49AC] hover:bg-[#3f3d91] text-white font-semibold flex items-center gap-2 px-6 shadow">
                            <RefreshCw v-if="formBrosur.processing" class="w-4 h-4 animate-spin" />
                            <Upload v-else class="w-4 h-4" />
                            Simpan Brosur
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Konfirmasi Penghapusan Dialog -->
        <Dialog v-model:open="isDeleteDialogOpen">
            <DialogContent class="max-w-md bg-white p-6 rounded-2xl shadow-2xl">
                <DialogHeader class="bg-rose-500 text-white p-6 -m-6 mb-6 rounded-t-2xl">
                    <DialogTitle class="text-xl font-bold text-white flex items-center gap-2">
                        <AlertCircle class="w-5 h-5" /> Konfirmasi Hapus Brosur
                    </DialogTitle>
                    <DialogDescription class="text-rose-100 text-sm mt-1">
                        Tindakan ini tidak dapat dibatalkan. File fisik dan data brosur akan dihapus secara permanen.
                    </DialogDescription>
                </DialogHeader>

                <div class="py-4 text-center">
                    <p class="text-sm text-gray-700 font-medium">Apakah Anda yakin ingin menghapus brosur ini?</p>
                    <p v-if="itemToDelete" class="text-xs text-gray-500 font-semibold mt-1 font-mono">"{{ itemToDelete.keterangan || itemToDelete.nama.split('/').pop() }}"</p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                    <Button type="button" variant="outline" @click="isDeleteDialogOpen = false" class="font-semibold">
                        Batal
                    </Button>
                    <Button type="button" @click="executeDelete" class="bg-red-500 hover:bg-red-600 text-white font-semibold flex items-center gap-2 px-6 shadow">
                        <Trash2 class="w-4 h-4" /> Ya, Hapus Permanen
                    </Button>
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
