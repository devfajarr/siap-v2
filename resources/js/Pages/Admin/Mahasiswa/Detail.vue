<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import MahasiswaForm from './Components/MahasiswaForm.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/Components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger, DialogFooter } from '@/Components/ui/dialog';
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle } from '@/Components/ui/sheet';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Badge } from '@/Components/ui/badge';
import { Label } from '@/Components/ui/label';
import { 
    Plus, 
    Upload, 
    Download, 
    Trash2, 
    Search, 
    MoreHorizontal, 
    UserPlus, 
    ArrowLeft,
    GraduationCap,
    Users,
    ChevronRight,
    MoveHorizontal,
    Loader2,
    CheckCircle2,
    XCircle,
    AlertCircle,
    AlertTriangle,
    FileSpreadsheet,
    Home,
    Info,
    BookOpen,
    ArrowRight
} from 'lucide-vue-next';

const props = defineProps({
    namaKelas: Object,
    mahasiswas: Array,
    allKelas: Array,
    kelasSamProdi: Array,  // Kelas seprodi & sejenis (untuk pindah semester/rombel)
    dosens: Array,
});

const page = usePage();
const search = ref('');
const selectedIds = ref([]);
const isAddSheetOpen = ref(false);
const isEditSheetOpen = ref(false);
const isImportDialogOpen = ref(false);
const isMoveDialogOpen = ref(false);
const editingMahasiswa = ref(null);
const targetKelasId = ref('');

// Delete Confirmation state
const isDeleteSingleDialogOpen = ref(false);
const isDeleteBulkDialogOpen = ref(false);
const mahasiswaToDelete = ref(null);

// Toast state
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success');

// Watch for flash messages
watch(() => page.props.flash, (flash) => {
    if (flash?.success) {
        toastMessage.value = flash.success;
        toastType.value = 'success';
        showToast.value = true;
        setTimeout(() => { showToast.value = false }, 3000);
    } else if (flash?.error) {
        toastMessage.value = flash.error;
        toastType.value = 'error';
        showToast.value = true;
        setTimeout(() => { showToast.value = false }, 3000);
    }
}, { deep: true });

const filteredMahasiswas = computed(() => {
    return props.mahasiswas.filter(mhs => 
        mhs.nama_lengkap.toLowerCase().includes(search.value.toLowerCase()) ||
        mhs.nim.toString().includes(search.value)
    );
});

const isAllSelected = computed({
    get: () => selectedIds.value.length === filteredMahasiswas.value.length && filteredMahasiswas.value.length > 0,
    set: (val) => {
        if (val) {
            selectedIds.value = filteredMahasiswas.value.map(mhs => mhs.id);
        } else {
            selectedIds.value = [];
        }
    }
});

// Filter allKelas — exclude kelas saat ini
const filteredAllKelas = computed(() => {
    return (props.allKelas ?? []).filter(kelas => kelas.id !== props.namaKelas?.id);
});

// Kelas sama prodi & jenis — untuk pilihan utama pindah semester/rombel
const kelasSamProdiFiltered = computed(() => {
    return (props.kelasSamProdi ?? []).filter(k => k.id !== props.namaKelas?.id);
});

// Kelas lain (berbeda prodi/jenis) — pilihan lanjutan
const kelasLainnya = computed(() => {
    const samProdiIds = new Set((props.kelasSamProdi ?? []).map(k => k.id));
    return filteredAllKelas.value.filter(k => !samProdiIds.has(k.id));
});

// Preview detail kelas yang dipilih sebagai tujuan
const selectedKelasDetail = computed(() => {
    if (!targetKelasId.value) return null;
    return (props.allKelas ?? []).find(k => k.id.toString() === targetKelasId.value.toString());
});

// Apakah kelas tujuan berbeda prodi atau jenis kelas?
const isBedaProdi = computed(() => {
    if (!selectedKelasDetail.value) return false;
    return selectedKelasDetail.value.id_prodi !== props.namaKelas?.id_prodi;
});

const isBedaJenis = computed(() => {
    if (!selectedKelasDetail.value) return false;
    return selectedKelasDetail.value.jenis_kelas !== props.namaKelas?.jenis_kelas;
});

// Loading state untuk operasi delete
const isDeleting = ref(false);

const toggleSelect = (id, checked) => {
    if (checked) {
        if (!selectedIds.value.includes(id)) {
            selectedIds.value.push(id);
        }
    } else {
        selectedIds.value = selectedIds.value.filter(item => item !== id);
    }
};

const openEditSheet = (mahasiswa) => {
    editingMahasiswa.value = mahasiswa;
    isEditSheetOpen.value = true;
};

const deleteMahasiswa = (mhs) => {
    mahasiswaToDelete.value = mhs;
    isDeleteSingleDialogOpen.value = true;
};

const confirmDelete = () => {
    if (!mahasiswaToDelete.value) return;
    isDeleting.value = true;
    
    router.delete(route('v2.admin.data-mahasiswa.destroy', mahasiswaToDelete.value.id), {
        onSuccess: () => {
            isDeleteSingleDialogOpen.value = false;
            mahasiswaToDelete.value = null;
        },
        onFinish: () => {
            isDeleting.value = false;
        }
    });
};

const bulkDelete = () => {
    isDeleteBulkDialogOpen.value = true;
};

const confirmBulkDelete = () => {
    isDeleting.value = true;
    router.post(route('v2.admin.data-mahasiswa.bulk-delete'), {
        ids: selectedIds.value
    }, {
        onSuccess: () => {
            isDeleteBulkDialogOpen.value = false;
            selectedIds.value = [];
        },
        onFinish: () => {
            isDeleting.value = false;
        }
    });
};

// Loading state khusus untuk pindah kelas
const isMoving = ref(false);

const bulkMove = () => {
    if (!targetKelasId.value) return;
    isMoving.value = true;
    
    router.post(route('v2.admin.data-mahasiswa.pindah-kelas'), {
        ids: selectedIds.value,
        kelas_id: targetKelasId.value
    }, {
        onSuccess: () => {
            selectedIds.value = [];
            isMoveDialogOpen.value = false;
            targetKelasId.value = '';
        },
        onFinish: () => {
            isMoving.value = false;
        }
    });
};

const importForm = useForm({
    file: null,
    kelas_id: props.namaKelas?.id ?? null
});

const submitImport = () => {
    importForm.post(route('v2.admin.data-mahasiswa.import'), {
        onSuccess: () => {
            isImportDialogOpen.value = false;
            importForm.reset();
        }
    });
};
</script>

<template>
    <Head :title="`Detail Kelas ${namaKelas.nama_kelas}`" />

    <AdminLayout>
        <div class="space-y-6">
            <!-- Breadcrumb & Header -->
            <div class="flex flex-col gap-4">
                <div class="flex items-center gap-2 text-sm font-medium text-gray-500 mb-2">
                    <Link :href="route('v2.admin.dashboard')" class="hover:text-[#4B49AC] transition-colors flex items-center gap-1">
                        <Home class="h-4 w-4" />
                        Dashboard
                    </Link>
                    <ChevronRight class="h-4 w-4 text-gray-300" />
                    <Link :href="route('v2.admin.data-mahasiswa.index')" class="hover:text-[#4B49AC] transition-colors">Mahasiswa</Link>
                    <ChevronRight class="h-4 w-4 text-gray-300" />
                    <span class="text-gray-900 font-bold">Kelas {{ namaKelas.nama_kelas }}</span>
                </div>
                
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-3">
                            <h1 class="text-3xl font-bold tracking-tight text-gray-900">Kelas {{ namaKelas.nama_kelas }}</h1>
                            <Badge variant="outline" class="bg-indigo-50 text-[#4B49AC] border-indigo-100 px-3 font-semibold">
                                {{ namaKelas.semester?.semester ? 'Semester ' + namaKelas.semester.semester : '-' }}
                            </Badge>
                        </div>
                        <p class="text-[#6B7280] flex items-center gap-2">
                            <span class="font-medium text-gray-600">{{ namaKelas.prodi?.nama_prodi }}</span>
                            <span class="text-gray-300">|</span>
                            <span class="text-sm italic text-gray-500">{{ namaKelas.jenis_kelas }}</span>
                        </p>
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-2">
                        <Button @click="isAddSheetOpen = true" class="bg-[#4B49AC] hover:bg-[#3f3d91] text-white rounded-lg shadow-md transition-all">
                            <Plus class="mr-2 h-4 w-4" /> Tambah Mahasiswa
                        </Button>

                        <Button variant="outline" @click="isImportDialogOpen = true" class="border-gray-200 text-[#4B5563] hover:bg-gray-50 rounded-lg">
                            <Upload class="mr-2 h-4 w-4" /> Import
                        </Button>

                        <Dialog v-model:open="isImportDialogOpen">
                            <DialogContent class="sm:max-w-[500px] p-0 overflow-hidden border-none shadow-2xl bg-white rounded-lg">
                                <div class="bg-[#4B49AC] p-6 text-white shrink-0">
                                    <DialogHeader>
                                        <DialogTitle class="text-xl font-bold text-white">Import Data Mahasiswa</DialogTitle>
                                        <DialogDescription class="text-indigo-100 mt-1">
                                            Unggah file Excel untuk mengimpor data mahasiswa ke kelas ini secara massal.
                                        </DialogDescription>
                                    </DialogHeader>
                                </div>
                                
                                <div class="p-8 space-y-6">
                                    <div class="p-4 bg-indigo-50 border border-indigo-100 rounded-lg flex items-start gap-4">
                                        <div class="bg-white p-2 rounded-lg shadow-sm shrink-0">
                                            <FileSpreadsheet class="w-6 h-6 text-[#4B49AC]" />
                                        </div>
                                        <div class="space-y-1">
                                            <h4 class="text-sm font-bold text-[#4B49AC]">Format Template</h4>
                                            <p class="text-[11px] text-indigo-700 leading-relaxed">
                                                Gunakan format kolom yang sesuai agar data dapat diproses. Anda dapat mengunduh template di halaman utama data mahasiswa.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="space-y-3">
                                        <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Pilih File Excel</Label>
                                        <div class="relative group cursor-pointer border-2 border-dashed border-gray-200 hover:border-[#4B49AC] rounded-lg p-10 transition-all text-center bg-gray-50/30" @click="$refs.fileInput.click()">
                                            <input type="file" ref="fileInput" class="hidden" @change="(e) => importForm.file = e.target.files[0]" accept=".xlsx,.xls,.csv" />
                                            <div v-if="!importForm.file" class="space-y-3">
                                                <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center mx-auto shadow-sm group-hover:scale-110 transition-all">
                                                    <Upload class="w-6 h-6 text-gray-400 group-hover:text-[#4B49AC]" />
                                                </div>
                                                <div>
                                                    <p class="text-sm font-semibold text-gray-700">Klik untuk pilih file</p>
                                                    <p class="text-xs text-gray-400 mt-1">Format: .xlsx, .xls, .csv</p>
                                                </div>
                                            </div>
                                            <div v-else class="flex flex-col items-center gap-2">
                                                <div class="w-12 h-12 bg-green-50 rounded-full flex items-center justify-center mx-auto shadow-sm">
                                                    <CheckCircle2 class="w-6 h-6 text-green-500" />
                                                </div>
                                                <p class="text-sm font-bold text-gray-800">{{ importForm.file.name }}</p>
                                                <Button variant="ghost" size="sm" @click.stop="importForm.file = null" class="text-red-500 hover:text-red-600 h-auto p-0 font-bold text-xs mt-1">Ganti file</Button>
                                            </div>
                                        </div>
                                        <p v-if="importForm.errors.file" class="text-xs text-red-500 font-medium mt-1">{{ importForm.errors.file }}</p>
                                    </div>
                                </div>

                                <DialogFooter class="p-6 bg-gray-50/50 border-t border-gray-100 flex flex-row items-center justify-end gap-3">
                                    <Button type="button" variant="ghost" @click="isImportDialogOpen = false" class="h-11 px-6 rounded-lg font-semibold text-gray-500">Batal</Button>
                                    <Button @click="submitImport" :disabled="importForm.processing || !importForm.file" class="h-11 px-8 bg-[#4B49AC] hover:bg-[#3f3d91] text-white rounded-lg shadow-lg shadow-indigo-200 transition-all font-semibold">
                                        <Loader2 v-if="importForm.processing" class="w-4 h-4 mr-2 animate-spin" />
                                        Mulai Import
                                    </Button>
                                </DialogFooter>
                            </DialogContent>
                        </Dialog>

                        <Button variant="outline" as-child>
                            <a :href="route('v2.admin.data-mahasiswa.export', namaKelas.id)">
                                <Download class="mr-2 h-4 w-4" /> Export
                            </a>
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <Card class="border-none shadow-sm ring-1 ring-gray-200">
                <CardHeader class="pb-3 border-b bg-gray-50/50">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="relative w-full max-w-sm">
                                <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                <Input v-model="search" placeholder="Cari Nama atau NIM..." class="pl-10 bg-white border-gray-200 focus:ring-[#4B49AC]/20" />
                            </div>
                        </div>

                        <!-- Selection Actions Bar -->
                        <div v-if="selectedIds.length > 0" class="flex items-center gap-2 p-1 bg-indigo-50 border border-indigo-100 rounded-xl shadow-sm animate-in fade-in zoom-in-95 duration-200">
                            <div class="px-3 flex items-center gap-2 border-r border-indigo-200 mr-1">
                                <Users class="h-4 w-4 text-[#4B49AC]" />
                                <span class="text-xs font-bold text-[#4B49AC] whitespace-nowrap">
                                    {{ selectedIds.length }} Terpilih
                                </span>
                            </div>

                            <div class="flex items-center gap-1">
                                <Dialog v-model:open="isMoveDialogOpen">
                                    <DialogTrigger as-child>
                                        <Button variant="ghost" size="sm" class="h-8 text-[#4B49AC] hover:bg-white hover:text-[#3f3d91] font-bold text-xs rounded-lg">
                                            <MoveHorizontal class="mr-1.5 h-3.5 w-3.5" /> Pindah Kelas
                                        </Button>
                                    </DialogTrigger>
                                    <DialogContent class="sm:max-w-[520px] p-0 overflow-hidden border-none shadow-2xl bg-white rounded-xl">
                                        <!-- Header -->
                                        <div class="bg-[#4B49AC] p-6 text-white shrink-0">
                                            <DialogHeader>
                                                <DialogTitle class="text-xl font-bold text-white flex items-center gap-2">
                                                    <MoveHorizontal class="h-5 w-5" /> Pindah Kelas
                                                </DialogTitle>
                                                <DialogDescription class="text-indigo-100 mt-1">
                                                    Memindahkan <span class="font-bold text-white">{{ selectedIds.length }} mahasiswa</span> ke kelas lain.
                                                </DialogDescription>
                                            </DialogHeader>
                                        </div>

                                        <div class="p-6 space-y-5 max-h-[70vh] overflow-y-auto">

                                            <!-- Info kelas asal -->
                                            <div class="flex items-center gap-3 p-3 bg-indigo-50 rounded-lg border border-indigo-100">
                                                <div class="bg-[#4B49AC] p-2 rounded-lg shrink-0">
                                                    <GraduationCap class="h-4 w-4 text-white" />
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="text-[10px] font-bold text-[#4B49AC] uppercase tracking-wider">Kelas Asal</p>
                                                    <p class="text-sm font-bold text-gray-800 truncate">{{ namaKelas.nama_kelas }}</p>
                                                    <p class="text-xs text-gray-500 truncate">{{ namaKelas.prodi?.nama_prodi }} · Semester {{ namaKelas.semester?.semester }} · {{ namaKelas.jenis_kelas }}</p>
                                                </div>
                                            </div>

                                            <!-- Pilih Kelas Tujuan -->
                                            <div class="space-y-2">
                                                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Pilih Kelas Tujuan</Label>

                                                <!-- Jika ada kelas seprodi -->
                                                <template v-if="kelasSamProdiFiltered.length > 0 || kelasLainnya.length > 0">
                                                    <Select v-model="targetKelasId">
                                                        <SelectTrigger class="h-11 rounded-lg border-gray-200">
                                                            <SelectValue placeholder="Pilih kelas tujuan..." />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <!-- Grup: kelas seprodi -->
                                                            <template v-if="kelasSamProdiFiltered.length > 0">
                                                                <div class="px-2 py-1.5 text-[10px] font-bold text-[#4B49AC] uppercase tracking-wider bg-indigo-50">
                                                                    Kelas Seprodi · {{ namaKelas.jenis_kelas }}
                                                                </div>
                                                                <SelectItem v-for="kelas in kelasSamProdiFiltered" :key="kelas.id" :value="kelas.id.toString()">
                                                                    <div class="flex items-center gap-2">
                                                                        <span>{{ kelas.nama_kelas }}</span>
                                                                        <span class="text-xs text-gray-400">(Sem. {{ kelas.semester?.semester ?? '?' }})</span>
                                                                    </div>
                                                                </SelectItem>
                                                            </template>

                                                            <!-- Grup: kelas lainnya -->
                                                            <template v-if="kelasLainnya.length > 0">
                                                                <div class="px-2 py-1.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider bg-gray-50 border-t">
                                                                    Kelas Lainnya
                                                                </div>
                                                                <SelectItem v-for="kelas in kelasLainnya" :key="kelas.id" :value="kelas.id.toString()">
                                                                    <div class="flex items-center gap-2">
                                                                        <span>{{ kelas.nama_kelas }}</span>
                                                                        <span class="text-xs text-gray-400">{{ kelas.prodi?.nama_prodi }}</span>
                                                                    </div>
                                                                </SelectItem>
                                                            </template>
                                                        </SelectContent>
                                                    </Select>
                                                </template>

                                                <!-- Empty state: tidak ada kelas lain sama sekali -->
                                                <template v-else>
                                                    <div class="flex flex-col items-center gap-3 p-6 bg-amber-50 border border-amber-200 rounded-xl text-center">
                                                        <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center">
                                                            <Info class="h-5 w-5 text-amber-600" />
                                                        </div>
                                                        <div>
                                                            <p class="text-sm font-bold text-amber-800">Tidak Ada Kelas Tersedia</p>
                                                            <p class="text-xs text-amber-600 mt-1 leading-relaxed">
                                                                Belum ada kelas lain yang terdaftar di sistem.<br>
                                                                Tambahkan kelas terlebih dahulu di menu Data Master → Kelas.
                                                            </p>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>

                                            <!-- Preview kelas tujuan setelah dipilih -->
                                            <Transition name="fade-slide">
                                                <div v-if="selectedKelasDetail" class="space-y-3">
                                                    <!-- Arrow indicator -->
                                                    <div class="flex items-center gap-2">
                                                        <div class="h-px flex-1 bg-gray-200"></div>
                                                        <ArrowRight class="h-4 w-4 text-gray-400" />
                                                        <div class="h-px flex-1 bg-gray-200"></div>
                                                    </div>

                                                    <!-- Preview card kelas tujuan -->
                                                    <div :class="[
                                                        'p-4 rounded-xl border-2 transition-all',
                                                        isBedaProdi || isBedaJenis
                                                            ? 'border-amber-300 bg-amber-50'
                                                            : 'border-green-200 bg-green-50'
                                                    ]">
                                                        <div class="flex items-start gap-3">
                                                            <div :class="[
                                                                'p-2 rounded-lg shrink-0',
                                                                isBedaProdi || isBedaJenis ? 'bg-amber-200' : 'bg-green-200'
                                                            ]">
                                                                <GraduationCap :class="[
                                                                    'h-4 w-4',
                                                                    isBedaProdi || isBedaJenis ? 'text-amber-700' : 'text-green-700'
                                                                ]" />
                                                            </div>
                                                            <div class="min-w-0 flex-1">
                                                                <div class="flex items-center gap-2 flex-wrap">
                                                                    <p class="text-sm font-bold text-gray-800">{{ selectedKelasDetail.nama_kelas }}</p>
                                                                    <span v-if="isBedaProdi" class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-200 text-amber-800 text-[10px] font-bold rounded-full">
                                                                        <AlertTriangle class="h-2.5 w-2.5" /> Beda Prodi
                                                                    </span>
                                                                    <span v-if="isBedaJenis" class="inline-flex items-center gap-1 px-2 py-0.5 bg-orange-200 text-orange-800 text-[10px] font-bold rounded-full">
                                                                        <AlertTriangle class="h-2.5 w-2.5" /> Beda Jenis
                                                                    </span>
                                                                    <span v-if="!isBedaProdi && !isBedaJenis" class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-200 text-green-800 text-[10px] font-bold rounded-full">
                                                                        <CheckCircle2 class="h-2.5 w-2.5" /> Seprodi
                                                                    </span>
                                                                </div>
                                                                <p class="text-xs text-gray-500 mt-1">
                                                                    {{ selectedKelasDetail.prodi?.nama_prodi }} · Semester {{ selectedKelasDetail.semester?.semester ?? '-' }} · {{ selectedKelasDetail.jenis_kelas }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Warning jika beda prodi/jenis -->
                                                    <div v-if="isBedaProdi || isBedaJenis" class="flex items-start gap-2 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                                                        <AlertTriangle class="h-4 w-4 text-amber-600 shrink-0 mt-0.5" />
                                                        <p class="text-xs text-amber-700 leading-relaxed">
                                                            <span class="font-bold">Perhatian:</span>
                                                            Kelas tujuan berbeda {{ isBedaProdi ? 'program studi' : '' }}{{ isBedaProdi && isBedaJenis ? ' dan ' : '' }}{{ isBedaJenis ? 'jenis kelas' : '' }}.
                                                            Status KRS mahasiswa akan direset. Pastikan perpindahan ini disengaja.
                                                        </p>
                                                    </div>
                                                </div>
                                            </Transition>

                                            <!-- Info empty kelas seprodi -->
                                            <div v-if="kelasSamProdiFiltered.length === 0 && (filteredAllKelas.length > 0)" class="flex items-start gap-2 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                                <Info class="h-4 w-4 text-blue-500 shrink-0 mt-0.5" />
                                                <p class="text-xs text-blue-700 leading-relaxed">
                                                    Tidak ada kelas lain dengan prodi & jenis yang sama (<span class="font-bold">{{ namaKelas.jenis_kelas }}</span>).
                                                    Pilihan yang tersedia adalah kelas dari prodi atau jenis lain.
                                                </p>
                                            </div>
                                        </div>

                                        <DialogFooter class="px-6 pb-6 pt-4 bg-gray-50/50 border-t border-gray-100 flex flex-row items-center justify-end gap-3">
                                            <Button type="button" variant="ghost" @click="isMoveDialogOpen = false" class="h-11 px-6 rounded-lg font-semibold text-gray-500">Batal</Button>
                                            <Button
                                                @click="bulkMove"
                                                :disabled="!targetKelasId || isMoving || filteredAllKelas.length === 0"
                                                :class="[
                                                    'h-11 px-8 text-white rounded-lg shadow-lg font-semibold transition-all',
                                                    isBedaProdi || isBedaJenis ? 'bg-amber-500 hover:bg-amber-600 shadow-amber-100' : 'bg-[#4B49AC] hover:bg-[#3f3d91] shadow-indigo-100'
                                                ]"
                                            >
                                                <Loader2 v-if="isMoving" class="w-4 h-4 mr-2 animate-spin" />
                                                {{ isBedaProdi || isBedaJenis ? 'Tetap Pindahkan' : 'Konfirmasi Pindah' }}
                                            </Button>
                                        </DialogFooter>
                                    </DialogContent>
                                </Dialog>

                                <Button variant="ghost" size="sm" class="h-8 text-red-600 hover:bg-white hover:text-red-700 font-bold text-xs rounded-lg" @click="bulkDelete">
                                    <Trash2 class="mr-1.5 h-3.5 w-3.5" /> Hapus Massal
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="relative overflow-x-auto">
                        <Table>
                            <TableHeader class="bg-gray-50/50">
                                <TableRow>
                                    <TableHead class="w-[50px]">
                                        <input 
                                            type="checkbox"
                                            :checked="isAllSelected"
                                            @change="isAllSelected = $event.target.checked"
                                            class="h-4 w-4 rounded border-[#4B49AC] text-[#4B49AC] accent-[#4B49AC] cursor-pointer"
                                        />
                                    </TableHead>
                                    <TableHead>NIM</TableHead>
                                    <TableHead>Nama Lengkap</TableHead>
                                    <TableHead>Jenis Kelamin</TableHead>
                                    <TableHead>Pembimbing</TableHead>
                                    <TableHead>Kontak</TableHead>
                                    <TableHead class="text-right">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="mhs in filteredMahasiswas" :key="mhs.id" class="group hover:bg-gray-50/50 transition-colors">
                                    <TableCell>
                                        <input
                                            type="checkbox"
                                            :checked="selectedIds.includes(mhs.id)"
                                            @change="toggleSelect(mhs.id, $event.target.checked)"
                                            class="h-4 w-4 rounded border-[#4B49AC] text-[#4B49AC] accent-[#4B49AC] cursor-pointer"
                                        />
                                    </TableCell>
                                    <TableCell class="font-mono font-medium text-primary">{{ mhs.nim }}</TableCell>
                                    <TableCell>
                                        <div class="font-medium text-gray-900">{{ mhs.nama_lengkap }}</div>
                                        <div class="text-xs text-muted-foreground">{{ mhs.email }}</div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge variant="outline" :class="mhs.jenis_kelamin === 'Laki-Laki' ? 'border-blue-200 bg-blue-50 text-blue-700' : 'border-pink-200 bg-pink-50 text-pink-700'">
                                            {{ mhs.jenis_kelamin }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm">{{ mhs.pembimbing_akademik?.nama || '-' }}</div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-xs text-muted-foreground">{{ mhs.no_telephone }}</div>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button variant="ghost" size="icon" class="h-9 w-9 text-[#4B49AC] hover:text-[#3f3da0] hover:bg-indigo-50 rounded-lg transition-all" @click="openEditSheet(mhs)">
                                                <UserPlus class="h-4 w-4" />
                                            </Button>
                                            <Button variant="ghost" size="icon" class="h-9 w-9 text-[#FF4747] hover:text-[#d33d3d] hover:bg-red-50 rounded-lg transition-all" @click="deleteMahasiswa(mhs)">
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="filteredMahasiswas.length === 0">
                                    <TableCell colspan="7" class="h-64 text-center">
                                        <div class="flex flex-col items-center justify-center space-y-4">
                                            <div class="rounded-full bg-primary/10 p-4">
                                                <Users class="h-8 w-8 text-primary" />
                                            </div>
                                            <div>
                                                <p class="text-lg font-medium text-gray-900">Belum Ada Data Mahasiswa</p>
                                                <p class="text-sm text-muted-foreground">Kelas ini belum memiliki data mahasiswa terdaftar.</p>
                                            </div>
                                            <Button @click="isAddSheetOpen = true" class="bg-[#4B49AC] hover:bg-[#3f3d91]">
                                                <Plus class="mr-2 h-4 w-4" /> Tambah Mahasiswa
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Add Sheet -->
        <Sheet :open="isAddSheetOpen" @update:open="isAddSheetOpen = $event">
            <SheetContent side="right" class="sm:max-w-[35%] w-full p-0 border-none shadow-2xl bg-white flex flex-col">
                <div class="bg-[#4B49AC] p-6 text-white shrink-0">
                    <SheetHeader>
                        <SheetTitle class="text-xl font-bold text-white">Tambah Mahasiswa Baru</SheetTitle>
                        <SheetDescription class="text-indigo-100 mt-1">
                            Lengkapi data diri dan akademik mahasiswa untuk kelas {{ namaKelas.nama_kelas }}.
                        </SheetDescription>
                    </SheetHeader>
                </div>
                <MahasiswaForm 
                    class="flex-1 min-h-0"
                    :dosens="dosens" 
                    :all-kelas="allKelas" 
                    :current-kelas-id="namaKelas.id"
                    @success="isAddSheetOpen = false"
                    @cancel="isAddSheetOpen = false"
                />
            </SheetContent>
        </Sheet>

        <!-- Edit Sheet -->
        <Sheet :open="isEditSheetOpen" @update:open="isEditSheetOpen = $event">
            <SheetContent side="right" class="sm:max-w-[35%] w-full p-0 border-none shadow-2xl bg-white flex flex-col">
                <div class="bg-[#4B49AC] p-6 text-white shrink-0">
                    <SheetHeader>
                        <SheetTitle class="text-xl font-bold text-white">Edit Data Mahasiswa</SheetTitle>
                        <SheetDescription class="text-indigo-100 mt-1">
                            Perbarui informasi profil dan akademik mahasiswa.
                        </SheetDescription>
                    </SheetHeader>
                </div>
                <MahasiswaForm 
                    v-if="editingMahasiswa"
                    class="flex-1 min-h-0"
                    :mahasiswa="editingMahasiswa"
                    :dosens="dosens" 
                    :all-kelas="allKelas" 
                    @success="isEditSheetOpen = false"
                    @cancel="isEditSheetOpen = false"
                />
            </SheetContent>
        </Sheet>

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

        <!-- Delete Single Confirmation Dialog -->
        <Dialog :open="isDeleteSingleDialogOpen" @update:open="isDeleteSingleDialogOpen = $event">
            <DialogContent class="sm:max-w-[450px] p-0 overflow-hidden border-none shadow-2xl bg-white rounded-xl">
                <div class="p-8 text-center">
                    <div class="w-20 h-20 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm">
                        <AlertCircle class="w-10 h-10 text-[#FF4747]" />
                    </div>
                    <DialogHeader>
                        <DialogTitle class="text-2xl font-bold text-gray-800 text-center">Konfirmasi Hapus</DialogTitle>
                        <DialogDescription class="text-gray-500 text-center mt-3 px-2 text-base">
                            Apakah Anda yakin ingin menghapus mahasiswa <span class="text-[#FF4747] font-bold">"{{ mahasiswaToDelete?.nama_lengkap }}"</span>? 
                            <br><span class="text-xs mt-3 block italic text-gray-400 font-medium tracking-wide">Tindakan ini akan menghapus seluruh data akademik terkait secara permanen.</span>
                        </DialogDescription>
                    </DialogHeader>

                    <div class="flex items-center justify-center gap-4 mt-10">
                        <Button 
                            type="button" 
                            variant="ghost" 
                            @click="isDeleteSingleDialogOpen = false"
                            :disabled="isDeleting"
                            class="h-12 px-8 rounded-lg text-gray-500 hover:bg-gray-100 transition-all font-semibold"
                        >
                            Batal
                        </Button>
                        <Button 
                            @click="confirmDelete"
                            :disabled="isDeleting"
                            class="h-12 px-10 bg-[#FF4747] hover:bg-[#d33d3d] text-white rounded-lg shadow-lg shadow-red-100 transition-all font-semibold"
                        >
                            <Loader2 v-if="isDeleting" class="w-4 h-4 mr-2 animate-spin" />
                            Ya, Hapus Data
                        </Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Delete Bulk Confirmation Dialog -->
        <Dialog :open="isDeleteBulkDialogOpen" @update:open="isDeleteBulkDialogOpen = $event">
            <DialogContent class="sm:max-w-[450px] p-0 overflow-hidden border-none shadow-2xl bg-white rounded-xl">
                <div class="p-8 text-center">
                    <div class="w-20 h-20 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm">
                        <AlertTriangle class="w-10 h-10 text-[#FF4747]" />
                    </div>
                    <DialogHeader>
                        <DialogTitle class="text-2xl font-bold text-gray-800 text-center">Hapus Data Terpilih</DialogTitle>
                        <DialogDescription class="text-gray-500 text-center mt-3 px-2 text-base">
                            Anda akan menghapus secara permanen <span class="text-[#FF4747] font-bold">{{ selectedIds.length }}</span> data mahasiswa yang telah dipilih.
                            <br><span class="text-xs mt-3 block italic text-gray-400 font-medium tracking-wide">Data yang sudah dihapus tidak dapat dikembalikan lagi.</span>
                        </DialogDescription>
                    </DialogHeader>

                    <div class="flex items-center justify-center gap-4 mt-10">
                        <Button 
                            type="button" 
                            variant="ghost" 
                            @click="isDeleteBulkDialogOpen = false"
                            :disabled="isDeleting"
                            class="h-12 px-8 rounded-lg text-gray-500 hover:bg-gray-100 transition-all font-semibold"
                        >
                            Batal
                        </Button>
                        <Button 
                            @click="confirmBulkDelete"
                            :disabled="isDeleting"
                            class="h-12 px-10 bg-[#FF4747] hover:bg-[#d33d3d] text-white rounded-lg shadow-lg shadow-red-100 transition-all font-semibold"
                        >
                            <Loader2 v-if="isDeleting" class="w-4 h-4 mr-2 animate-spin" />
                            Hapus Terpilih
                        </Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
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

/* Fade-slide for pindah kelas preview card */
.fade-slide-enter-active,
.fade-slide-leave-active {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.fade-slide-enter-from {
    opacity: 0;
    transform: translateY(-8px);
}

.fade-slide-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}
</style>
