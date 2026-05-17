<script setup>
import { ref, watch, onMounted } from 'vue'
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
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
    DialogFooter
} from '@/components/ui/dialog'
import { Eye, FolderSearch, Clock, ChevronLeft, ChevronRight, CheckCircle2, XCircle, AlertCircle } from 'lucide-vue-next'

const props = defineProps({
    pembayarans: {
        type: Object,
        required: true,
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

const isModalOpen = ref(false)
const selectedPembayaran = ref(null)

const form = useForm({
    status_pembayaran: 1,
    keterangan: 'Sudah',
})

const openModal = (pembayaran) => {
    selectedPembayaran.value = pembayaran
    isModalOpen.value = true
}

const submitVerifikasi = (status, ket) => {
    form.status_pembayaran = status
    form.keterangan = ket
    form.put(`/v2/admin/pembayaran/${selectedPembayaran.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            isModalOpen.value = false
        }
    })
}
</script>

<template>
    <Head title="Pembayaran Diajukan" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <span class="text-gray-500">Pembayaran</span>
                <span class="text-gray-400">/</span>
                <span class="text-gray-800 font-semibold">Pembayaran Diajukan</span>
            </div>
        </template>

        <div class="py-12 space-y-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Header Title -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-[#1F2937]">Pembayaran Menunggu Verifikasi</h1>
                        <p class="text-[#6B7280]">Periksa dan verifikasi bukti pembayaran KRS yang diajukan oleh mahasiswa.</p>
                    </div>
                    <Badge variant="outline" class="bg-amber-50 text-amber-700 border-amber-300 font-medium px-3 py-1.5 self-start md:self-center">
                        <Clock class="w-4 h-4 mr-1.5 inline" /> {{ pembayarans.total }} Menunggu Verifikasi
                    </Badge>
                </div>

                <Card class="shadow-sm border-gray-200">
                    <CardContent class="p-0">
                        <div class="overflow-x-auto rounded-t-lg">
                            <Table>
                                <TableHeader>
                                    <TableRow class="bg-gray-50/75">
                                        <TableHead class="w-[60px] font-semibold">#</TableHead>
                                        <TableHead class="font-semibold">Nama Mahasiswa</TableHead>
                                        <TableHead class="font-semibold">NIM</TableHead>
                                        <TableHead class="font-semibold">Program Studi</TableHead>
                                        <TableHead class="font-semibold">Semester</TableHead>
                                        <TableHead class="font-semibold">Kelas</TableHead>
                                        <TableHead class="font-semibold text-center">Status</TableHead>
                                        <TableHead class="font-semibold text-center">Opsi</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <template v-if="pembayarans.data.length > 0">
                                        <TableRow v-for="(pembayaran, index) in pembayarans.data" :key="pembayaran.id" class="hover:bg-gray-50/50 transition-colors">
                                            <TableCell class="font-medium text-gray-900">
                                                {{ (pembayarans.current_page - 1) * pembayarans.per_page + index + 1 }}
                                            </TableCell>
                                            <TableCell class="font-semibold text-gray-900">
                                                {{ pembayaran.mahasiswa?.nama_lengkap || '-' }}
                                            </TableCell>
                                            <TableCell class="text-gray-600 font-mono">
                                                {{ pembayaran.mahasiswa?.nim || '-' }}
                                            </TableCell>
                                            <TableCell class="text-gray-700">
                                                {{ pembayaran.prodi?.nama_prodi || '-' }}
                                            </TableCell>
                                            <TableCell class="text-gray-700">
                                                Semester {{ pembayaran.semester?.semester || '-' }}
                                            </TableCell>
                                            <TableCell class="text-gray-700">
                                                {{ pembayaran.kelas?.nama_kelas || '-' }}
                                            </TableCell>
                                            <TableCell class="text-center">
                                                <Badge variant="secondary" class="bg-amber-100 text-amber-800 hover:bg-amber-100 font-normal px-2.5 py-0.5">
                                                    Belum Verifikasi
                                                </Badge>
                                            </TableCell>
                                            <TableCell class="text-center">
                                                <Button @click="openModal(pembayaran)" size="sm" variant="outline" class="bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100 hover:text-amber-800">
                                                    <Eye class="w-4 h-4 mr-1.5" /> Lihat & Verifikasi
                                                </Button>
                                            </TableCell>
                                        </TableRow>
                                    </template>
                                    <template v-else>
                                        <TableRow>
                                            <TableCell colspan="8" class="h-64 text-center">
                                                <div class="max-w-md mx-auto py-12">
                                                    <div class="w-16 h-16 bg-[#4B49AC]/10 rounded-full flex items-center justify-center mx-auto mb-4 text-[#4B49AC]">
                                                        <FolderSearch class="w-8 h-8 text-[#4B49AC]" />
                                                    </div>
                                                    <p class="text-base font-semibold text-gray-900">Belum ada pengajuan pembayaran</p>
                                                    <p class="text-sm text-gray-500 mt-1">Daftar pengajuan bukti pembayaran KRS dari mahasiswa akan muncul di sini.</p>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    </template>
                                </TableBody>
                            </Table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="pembayarans.total > 0" class="p-4 border-t border-gray-100 flex items-center justify-between bg-white rounded-b-lg">
                            <p class="text-sm text-gray-600">
                                Menampilkan <span class="font-semibold">{{ pembayarans.from || 0 }}</span> sampai <span class="font-semibold">{{ pembayarans.to || 0 }}</span> dari <span class="font-semibold">{{ pembayarans.total }}</span> data
                            </p>
                            <div class="flex items-center gap-1.5">
                                <Button 
                                    variant="outline" 
                                    size="sm" 
                                    :disabled="!pembayarans.prev_page_url"
                                    @click="router.get(pembayarans.prev_page_url, {}, { preserveState: true })"
                                    class="rounded-lg h-8 w-8 p-0"
                                >
                                    <ChevronLeft class="w-4 h-4" />
                                </Button>
                                <div class="flex items-center gap-1">
                                    <Button 
                                        v-for="pageLink in pembayarans.links.slice(1, -1)" 
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
                                    :disabled="!pembayarans.next_page_url"
                                    @click="router.get(pembayarans.next_page_url, {}, { preserveState: true })"
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

        <!-- Verification Modal -->
        <Dialog v-model:open="isModalOpen">
            <DialogContent class="max-w-2xl bg-white p-6 rounded-2xl shadow-2xl">
                <DialogHeader class="bg-[#4B49AC] text-white p-6 -m-6 mb-6 rounded-t-2xl">
                    <DialogTitle class="text-xl font-bold text-white">Verifikasi Bukti Pembayaran KRS</DialogTitle>
                    <DialogDescription class="text-indigo-100 text-sm mt-1">
                        Periksa kelengkapan dan kesesuaian bukti transfer sebelum menyetujui KRS.
                    </DialogDescription>
                </DialogHeader>

                <div v-if="selectedPembayaran" class="space-y-6">
                    <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-xl border border-gray-200/80 text-sm">
                        <div>
                            <span class="text-xs text-gray-500 font-medium block">Nama Mahasiswa</span>
                            <span class="font-bold text-gray-900">{{ selectedPembayaran.mahasiswa?.nama_lengkap || '-' }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-500 font-medium block">NIM</span>
                            <span class="font-mono font-semibold text-gray-800">{{ selectedPembayaran.mahasiswa?.nim || '-' }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-500 font-medium block">Kelas & Prodi</span>
                            <span class="font-semibold text-gray-800">{{ selectedPembayaran.kelas?.nama_kelas || '-' }} • {{ selectedPembayaran.prodi?.nama_prodi || '-' }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-500 font-medium block">Semester</span>
                            <span class="font-semibold text-gray-800">Semester {{ selectedPembayaran.semester?.semester || '-' }}</span>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <span class="text-sm font-semibold text-gray-800 block">Bukti Transfer / Pembayaran:</span>
                        <div class="border-2 border-dashed border-gray-200 rounded-xl p-2 bg-gray-50 flex justify-center max-h-96 overflow-auto">
                            <img 
                                v-if="selectedPembayaran.bukti_pembayaran"
                                :src="`/storage/${selectedPembayaran.bukti_pembayaran}`" 
                                alt="Bukti Pembayaran" 
                                class="max-w-full rounded-lg object-contain shadow-sm hover:scale-105 transition-transform duration-300"
                            />
                            <div v-else class="py-12 text-center text-gray-400">
                                <AlertCircle class="w-12 h-12 mx-auto mb-2 opacity-50" />
                                <span>Bukti pembayaran tidak tersedia</span>
                            </div>
                        </div>
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
                            type="button" 
                            variant="destructive" 
                            :disabled="form.processing"
                            @click="submitVerifikasi(0, 'Belum')"
                            class="bg-amber-600 hover:bg-amber-700 font-semibold"
                        >
                            Tandai Belum Lunas
                        </Button>
                        <Button 
                            type="button" 
                            variant="default" 
                            :disabled="form.processing"
                            @click="submitVerifikasi(1, 'Sudah')"
                            class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold flex items-center gap-2 px-6"
                        >
                            <CheckCircle2 class="w-4 h-4" />
                            Verifikasi Lunas
                        </Button>
                    </div>
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
