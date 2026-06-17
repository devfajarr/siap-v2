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
    Users, 
    ChevronRight,
    Loader2,
    CheckCircle2,
    XCircle,
    AlertCircle,
    FileSpreadsheet,
    Home,
    GraduationCap,
    BookOpen,
    FilterX,
    UserCheck,
    UserX,
    Briefcase,
    ChevronLeft,
    UserPlus,
    UploadCloud
} from 'lucide-vue-next';
import { useFeederSync } from '@/Composables/useFeederSync';

const props = defineProps({
    mahasiswas: Object, // Paginated
    prodis: Array,
    angkatanList: Array,
    filters: Object,
    stats: Object,
    allKelas: Array,
    dosens: Array,
    agamas: Array,
});

const page = usePage();
const { triggerSync } = useFeederSync();

// Filter states
const search = ref(props.filters.search || '');
const status = ref(props.filters.status || 'all');
const prodi_id = ref(props.filters.prodi_id || 'all');
const angkatan = ref(props.filters.angkatan || 'all');

// UI States
const isAddSheetOpen = ref(false);
const isEditSheetOpen = ref(false);
const isParentSheetOpen = ref(false);
const editingMahasiswa = ref(null);
const managingStudent = ref(null);

// Parent Form setup
const parentForm = useForm({
    nama: '',
    username: '',
    password: '',
    no_telephone: '',
    alamat: '',
    relationship_type: 'Ayah',
});

// Delete Confirmation
const isDeleteSingleDialogOpen = ref(false);
const mahasiswaToDelete = ref(null);
const isDeleting = ref(false);

// Toast notification
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success');

// Active filter check
const hasActiveFilters = computed(() => {
    return search.value !== '' || status.value !== 'all' || prodi_id.value !== 'all' || angkatan.value !== 'all';
});

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

// Debounced filtering
let filterTimeout = null;
watch([search, status, prodi_id, angkatan], () => {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(() => {
        router.get(route('v2.admin.data-mahasiswa.all'), {
            search: search.value || undefined,
            status: status.value !== 'all' ? status.value : undefined,
            prodi_id: prodi_id.value !== 'all' ? prodi_id.value : undefined,
            angkatan: angkatan.value !== 'all' ? angkatan.value : undefined,
        }, {
            preserveState: true,
            replace: true
        });
    }, 400);
});

const resetFilters = () => {
    search.value = '';
    status.value = 'all';
    prodi_id.value = 'all';
    angkatan.value = 'all';
};

// Parent links methods
const openParentSheet = (mahasiswa) => {
    managingStudent.value = mahasiswa;
    isParentSheetOpen.value = true;
    parentForm.reset();
};

const submitParent = () => {
    parentForm.post(route('v2.admin.mahasiswa.orang-tua.store', managingStudent.value.id), {
        onSuccess: () => {
            parentForm.reset();
            // Reload page to get fresh relationships
            router.reload({ only: ['mahasiswas'] });
        }
    });
};

const deleteParentLink = (parentId) => {
    router.delete(route('v2.admin.mahasiswa.orang-tua.destroy', [managingStudent.value.id, parentId]), {
        onSuccess: () => {
            router.reload({ only: ['mahasiswas'] });
        }
    });
};

// Edit student
const openEditSheet = (mahasiswa) => {
    editingMahasiswa.value = mahasiswa;
    isEditSheetOpen.value = true;
};

// Delete student
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

// Color mapper for student status
const getStatusBadgeClass = (statusStr) => {
    const s = statusStr.toLowerCase();
    if (s.includes('aktif')) return 'border-green-200 bg-green-50 text-green-700';
    if (s.includes('lulus')) return 'border-indigo-200 bg-indigo-50 text-[#4B49AC]';
    if (s.includes('cuti')) return 'border-amber-200 bg-amber-50 text-amber-700';
    if (s.includes('keluar') || s.includes('undur')) return 'border-red-200 bg-red-50 text-red-700';
    return 'border-gray-200 bg-gray-50 text-gray-500';
};
</script>

<template>
    <Head title="Direktori Semua Mahasiswa" />

    <AdminLayout>
        <div class="space-y-6 font-sans">
            <!-- Breadcrumb & Header -->
            <div class="flex flex-col gap-4">
                <div class="flex items-center gap-2 text-sm font-medium text-gray-500 mb-2">
                    <Link :href="route('v2.admin.dashboard')" class="hover:text-[#4B49AC] transition-colors flex items-center gap-1">
                        <Home class="h-4 w-4" />
                        Dashboard
                    </Link>
                    <ChevronRight class="h-4 w-4 text-gray-300" />
                    <span class="text-gray-900 font-bold">Semua Mahasiswa</span>
                </div>
                
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <h1 class="text-3xl font-bold tracking-tight text-gray-900">Direktori Mahasiswa</h1>
                        <p class="text-muted-foreground mt-1">
                            Manajemen database semua data mahasiswa (aktif, alumni, lulus, atau non-aktif).
                        </p>
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-2">
                        <Button @click="triggerSync('pull-mahasiswas')" class="bg-indigo-50 border border-indigo-200 text-[#4B49AC] hover:bg-indigo-100 rounded-lg shadow-sm transition-all">
                            <UploadCloud class="mr-2 h-4 w-4" /> Tarik Semua Mahasiswa
                        </Button>

                        <Button @click="isAddSheetOpen = true" class="bg-[#4B49AC] hover:bg-[#3f3d91] text-white rounded-lg shadow-md transition-all">
                            <Plus class="mr-2 h-4 w-4" /> Tambah Mahasiswa
                        </Button>
                        <Button variant="outline" as-child class="rounded-lg border-gray-200 text-[#4B5563]">
                            <a :href="route('v2.admin.data-mahasiswa.export-all')">
                                <Download class="mr-2 h-4 w-4" /> Export Semua
                            </a>
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Stats Overview Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <Card class="border-none shadow-sm ring-1 ring-gray-100 hover:ring-indigo-100 transition-all">
                    <CardContent class="p-5 flex items-center justify-between">
                        <div class="space-y-1">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Database</p>
                            <h3 class="text-2xl font-bold text-gray-900">{{ stats.total }}</h3>
                        </div>
                        <div class="w-10 h-10 bg-indigo-50 text-[#4B49AC] rounded-xl flex items-center justify-center shrink-0">
                            <Users class="w-5 h-5" />
                        </div>
                    </CardContent>
                </Card>

                <Card class="border-none shadow-sm ring-1 ring-gray-100 hover:ring-green-100 transition-all">
                    <CardContent class="p-5 flex items-center justify-between">
                        <div class="space-y-1">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Mahasiswa Aktif</p>
                            <h3 class="text-2xl font-bold text-gray-900">{{ stats.active }}</h3>
                        </div>
                        <div class="w-10 h-10 bg-green-50 text-green-600 rounded-xl flex items-center justify-center shrink-0">
                            <UserCheck class="w-5 h-5" />
                        </div>
                    </CardContent>
                </Card>

                <Card class="border-none shadow-sm ring-1 ring-gray-100 hover:ring-purple-100 transition-all">
                    <CardContent class="p-5 flex items-center justify-between">
                        <div class="space-y-1">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Lulus / Alumni</p>
                            <h3 class="text-2xl font-bold text-gray-900">{{ stats.graduated }}</h3>
                        </div>
                        <div class="w-10 h-10 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center shrink-0">
                            <GraduationCap class="w-5 h-5" />
                        </div>
                    </CardContent>
                </Card>

                <Card class="border-none shadow-sm ring-1 ring-gray-100 hover:ring-red-100 transition-all">
                    <CardContent class="p-5 flex items-center justify-between">
                        <div class="space-y-1">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Non-Aktif Lainnya</p>
                            <h3 class="text-2xl font-bold text-gray-900">{{ stats.other }}</h3>
                        </div>
                        <div class="w-10 h-10 bg-red-50 text-red-500 rounded-xl flex items-center justify-center shrink-0">
                            <UserX class="w-5 h-5" />
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Navigation Tabs -->
            <div class="flex border-b border-gray-200 pb-px">
                <Link 
                    :href="route('v2.admin.data-mahasiswa.index')"
                    class="px-5 py-3 border-b-2 font-semibold text-sm transition-all flex items-center gap-2"
                    :class="[
                        route().current('v2.admin.data-mahasiswa.index')
                            ? 'border-[#4B49AC] text-[#4B49AC]'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                    ]"
                >
                    <BookOpen class="h-4 w-4" />
                    Berdasarkan Kelas
                </Link>
                <Link 
                    :href="route('v2.admin.data-mahasiswa.all')"
                    class="px-5 py-3 border-b-2 font-semibold text-sm transition-all flex items-center gap-2"
                    :class="[
                        route().current('v2.admin.data-mahasiswa.all')
                            ? 'border-[#4B49AC] text-[#4B49AC]'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                    ]"
                >
                    <Users class="h-4 w-4" />
                    Semua Mahasiswa
                </Link>
            </div>

            <!-- Main Listing Card -->
            <Card class="border-none shadow-sm ring-1 ring-gray-200">
                <CardHeader class="pb-4 border-b bg-gray-50/50">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <!-- Filters Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 w-full">
                            <div class="relative">
                                <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                                <Input v-model="search" placeholder="Cari Nama atau NIM..." class="pl-10 bg-white border-gray-250 focus:ring-[#4B49AC]/20 h-10 rounded-lg text-sm" />
                            </div>

                            <Select v-model="status">
                                <SelectTrigger class="bg-white h-10 border-gray-250 rounded-lg text-sm text-gray-650">
                                    <SelectValue placeholder="Status Keaktifan" />
                                </SelectTrigger>
                                <SelectContent class="bg-white rounded-lg">
                                    <SelectItem value="all">Semua Status</SelectItem>
                                    <SelectItem value="Aktif">Aktif</SelectItem>
                                    <SelectItem value="Lulus">Lulus</SelectItem>
                                    <SelectItem value="Mutasi">Mutasi</SelectItem>
                                    <SelectItem value="Cuti">Cuti</SelectItem>
                                    <SelectItem value="Dikeluarkan">Dikeluarkan</SelectItem>
                                    <SelectItem value="Mengajukan pengunduran diri">Mengajukan Pengunduran Diri</SelectItem>
                                </SelectContent>
                            </Select>

                            <Select v-model="prodi_id">
                                <SelectTrigger class="bg-white h-10 border-gray-250 rounded-lg text-sm text-gray-650">
                                    <SelectValue placeholder="Program Studi" />
                                </SelectTrigger>
                                <SelectContent class="bg-white rounded-lg">
                                    <SelectItem value="all">Semua Program Studi</SelectItem>
                                    <SelectItem v-for="prodi in prodis" :key="prodi.id" :value="prodi.id.toString()">
                                        {{ prodi.nama_prodi }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>

                            <Select v-model="angkatan">
                                <SelectTrigger class="bg-white h-10 border-gray-250 rounded-lg text-sm text-gray-650">
                                    <SelectValue placeholder="Pilih Angkatan" />
                                </SelectTrigger>
                                <SelectContent class="bg-white rounded-lg">
                                    <SelectItem value="all">Semua Angkatan</SelectItem>
                                    <SelectItem v-for="year in angkatanList" :key="year" :value="year.toString()">
                                        Angkatan {{ year }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        
                        <!-- Reset Button -->
                        <div v-if="hasActiveFilters" class="shrink-0 animate-in fade-in zoom-in-95 duration-200">
                            <Button @click="resetFilters" variant="ghost" class="text-gray-500 hover:text-gray-700 text-xs font-bold flex items-center gap-1.5 h-10">
                                <FilterX class="h-4 w-4" /> Reset Filter
                            </Button>
                        </div>
                    </div>
                </CardHeader>

                <CardContent class="p-0">
                    <div class="relative overflow-x-auto">
                        <Table>
                            <TableHeader class="bg-gray-50/50">
                                <TableRow>
                                    <TableHead class="w-[120px]">NIM</TableHead>
                                    <TableHead>Nama Lengkap</TableHead>
                                    <TableHead>Program Studi</TableHead>
                                    <TableHead>Kelas</TableHead>
                                    <TableHead>Angkatan</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Status Feeder</TableHead>
                                    <TableHead class="text-right">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="mhs in mahasiswas.data" :key="mhs.id" class="group hover:bg-gray-50/50 transition-colors">
                                    <TableCell class="font-mono font-bold text-[#4B49AC] text-sm">{{ mhs.nim }}</TableCell>
                                    <TableCell>
                                        <div class="font-bold text-gray-900">{{ mhs.nama_lengkap }}</div>
                                        <div class="text-xs text-gray-400 font-semibold mt-0.5">{{ mhs.email }}</div>
                                    </TableCell>
                                    <TableCell class="text-sm font-medium text-gray-600">
                                        {{ mhs.kelas?.prodi?.nama_prodi || '-' }}
                                    </TableCell>
                                    <TableCell>
                                        <Badge v-if="mhs.kelas" variant="outline" class="border-indigo-100 bg-indigo-50/40 text-indigo-700 font-bold px-2 py-0.5 rounded text-[11px]">
                                            {{ mhs.kelas.nama_kelas }}
                                        </Badge>
                                        <Badge v-else variant="outline" class="border-gray-200 bg-gray-50 text-gray-400 font-bold px-2 py-0.5 rounded text-[11px] italic">
                                            Tanpa Kelas
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-sm text-gray-650 font-semibold">{{ mhs.tahun_masuk }}</TableCell>
                                    <TableCell>
                                        <Badge variant="outline" :class="[getStatusBadgeClass(mhs.status_mahasiswa), 'font-bold px-2.5 py-0.5 rounded-full text-xs']">
                                            {{ mhs.status_mahasiswa }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex items-center gap-1.5">
                                            <Badge v-if="mhs.feeder_id_registrasi" variant="outline" class="border-emerald-200 bg-emerald-50 text-emerald-700 font-semibold text-xs shrink-0">
                                                Sinkron
                                            </Badge>
                                            <Badge v-else variant="outline" class="border-gray-200 bg-gray-50 text-gray-500 font-semibold text-xs shrink-0">
                                                Belum Sinkron
                                            </Badge>
                                            <Button v-if="!mhs.feeder_id_registrasi" variant="ghost" size="sm" class="h-6 px-2 text-[10px] text-[#4B49AC] hover:bg-indigo-50 border border-indigo-200 rounded font-semibold transition-all shrink-0" @click="triggerSync('push-mahasiswa', { id: mhs.id })" title="Push data ke Feeder">
                                                Push
                                            </Button>
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-1.5 opacity-80 group-hover:opacity-100 transition-opacity">
                                            <Button variant="ghost" size="icon" class="h-8 w-8 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition-all" @click="openParentSheet(mhs)" title="Kelola Orang Tua">
                                                <Users class="h-4 w-4" />
                                            </Button>
                                            <Button variant="ghost" size="icon" class="h-8 w-8 text-[#4B49AC] hover:text-[#3f3da0] hover:bg-indigo-50 rounded-lg transition-all" @click="openEditSheet(mhs)">
                                                <UserPlus class="h-4 w-4" />
                                            </Button>
                                            <Button variant="ghost" size="icon" class="h-8 w-8 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-all" @click="deleteMahasiswa(mhs)">
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-slot:empty v-if="mahasiswas.data.length === 0">
                                    <TableCell colspan="8" class="h-64 text-center">
                                        <div class="flex flex-col items-center justify-center space-y-3">
                                            <div class="rounded-full bg-gray-100 p-4">
                                                <Users class="h-8 w-8 text-gray-400" />
                                            </div>
                                            <div>
                                                <p class="text-lg font-bold text-gray-800">Tidak Ada Mahasiswa Ditemukan</p>
                                                <p class="text-sm text-gray-400">Tidak ada mahasiswa yang cocok dengan kriteria filter saat ini.</p>
                                            </div>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Pagination Controls -->
                    <div class="p-4 border-t border-gray-100 flex items-center justify-between bg-white rounded-b-xl">
                        <p class="text-sm text-gray-500 font-medium">
                            Menampilkan {{ mahasiswas.from || 0 }} sampai {{ mahasiswas.to || 0 }} dari {{ mahasiswas.total }} data
                        </p>
                        <div class="flex items-center gap-2">
                            <Button 
                                variant="outline" 
                                size="sm" 
                                :disabled="!mahasiswas.prev_page_url"
                                @click="router.get(mahasiswas.prev_page_url, {}, { preserveState: true })"
                                class="rounded-lg h-8 w-8 p-0 border-gray-200"
                            >
                                <ChevronLeft class="w-4 h-4 text-gray-600" />
                            </Button>
                            
                            <div class="flex items-center gap-1">
                                <Button 
                                    v-for="page in mahasiswas.links.slice(1, -1)" 
                                    :key="page.label"
                                    variant="ghost"
                                    size="sm"
                                    @click="page.url ? router.get(page.url, {}, { preserveState: true }) : null"
                                    :disabled="!page.url"
                                    :class="[
                                        'h-8 w-8 p-0 rounded-lg font-bold text-xs transition-all',
                                        page.active ? 'bg-[#4B49AC] text-white hover:bg-[#3f3d91]' : 'text-gray-650 hover:bg-gray-100'
                                    ]"
                                >
                                    {{ page.label }}
                                </Button>
                            </div>

                            <Button 
                                variant="outline" 
                                size="sm" 
                                :disabled="!mahasiswas.next_page_url"
                                @click="router.get(mahasiswas.next_page_url, {}, { preserveState: true })"
                                class="rounded-lg h-8 w-8 p-0 border-gray-200"
                            >
                                <ChevronRight class="w-4 h-4 text-gray-600" />
                            </Button>
                        </div>
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
                            Lengkapi data profil dan data akademik mahasiswa baru.
                        </SheetDescription>
                    </SheetHeader>
                </div>
                <MahasiswaForm 
                    class="flex-1 min-h-0"
                    :dosens="dosens" 
                    :all-kelas="allKelas" 
                    :agamas="agamas"
                    @success="isAddSheetOpen = false; router.reload();"
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
                            Perbarui rincian data profil dan akademik mahasiswa.
                        </SheetDescription>
                    </SheetHeader>
                </div>
                <MahasiswaForm 
                    v-if="editingMahasiswa"
                    class="flex-1 min-h-0"
                    :mahasiswa="editingMahasiswa"
                    :dosens="dosens" 
                    :all-kelas="allKelas" 
                    :agamas="agamas"
                    @success="isEditSheetOpen = false; router.reload();"
                    @cancel="isEditSheetOpen = false"
                />
            </SheetContent>
        </Sheet>

        <!-- Parent/Wali link sheet -->
        <Sheet :open="isParentSheetOpen" @update:open="isParentSheetOpen = $event">
            <SheetContent side="right" class="sm:max-w-[40%] w-full p-0 border-none shadow-2xl bg-white flex flex-col">
                <div class="bg-[#4B49AC] p-6 text-white shrink-0">
                    <SheetHeader>
                        <SheetTitle class="text-xl font-bold text-white">Kelola Orang Tua / Wali</SheetTitle>
                        <SheetDescription class="text-indigo-100 mt-1">
                            Hubungkan akun orang tua untuk mahasiswa <span class="font-bold text-white">"{{ managingStudent?.nama_lengkap }}"</span>.
                        </SheetDescription>
                    </SheetHeader>
                </div>
                
                <div class="flex-1 min-h-0 overflow-y-auto p-6 space-y-6">
                    <!-- Linked Parents -->
                    <div class="space-y-4">
                        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Orang Tua Terhubung</h3>
                        <div v-if="!managingStudent?.orang_tuas || managingStudent.orang_tuas.length === 0" class="p-4 bg-gray-50 border border-dashed border-gray-250 rounded-lg text-center text-sm text-gray-500">
                            Belum ada akun orang tua yang terhubung dengan mahasiswa ini.
                        </div>
                        <div v-else class="space-y-3">
                            <div 
                                v-for="ortu in managingStudent.orang_tuas" 
                                :key="ortu.id"
                                class="p-4 border border-gray-200 rounded-xl flex items-start justify-between bg-white shadow-xs"
                            >
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-bold text-gray-905 text-sm">{{ ortu.nama }}</h4>
                                        <Badge variant="outline" class="bg-indigo-50 text-[#4B49AC] border-indigo-100 font-bold px-2 py-0.5 text-[10px]">
                                            {{ ortu.pivot?.relationship_type || 'Wali' }}
                                        </Badge>
                                    </div>
                                    <p class="text-xs text-gray-400">Username: <span class="font-mono text-gray-600 font-bold">{{ ortu.username }}</span></p>
                                    <p class="text-xs text-gray-400" v-if="ortu.no_telephone">No. HP: {{ ortu.no_telephone }}</p>
                                </div>
                                <Button 
                                    variant="ghost" 
                                    size="icon" 
                                    class="text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg h-8 w-8"
                                    @click="deleteParentLink(ortu.id)"
                                >
                                    <Trash2 class="w-4 h-4" />
                                </Button>
                            </div>
                        </div>
                    </div>

                    <hr class="border-gray-200" />

                    <!-- Link form -->
                    <form @submit.prevent="submitParent" class="space-y-4">
                        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Tautkan Orang Tua Baru</h3>
                        
                        <div class="space-y-1.5">
                            <Label for="parent_relation" class="text-xs font-bold text-gray-500 uppercase tracking-wider">Hubungan</Label>
                            <Select v-model="parentForm.relationship_type">
                                <SelectTrigger id="parent_relation" class="h-10 rounded-lg border-gray-200">
                                    <SelectValue placeholder="Pilih hubungan..." />
                                </SelectTrigger>
                                <SelectContent class="bg-white">
                                    <SelectItem value="Ayah">Ayah</SelectItem>
                                    <SelectItem value="Ibu">Ibu</SelectItem>
                                    <SelectItem value="Wali">Wali</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-1.5">
                            <Label for="parent_name" class="text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Lengkap</Label>
                            <Input id="parent_name" v-model="parentForm.nama" placeholder="Nama Lengkap..." class="h-10 rounded-lg border-gray-200" required />
                        </div>

                        <div class="space-y-1.5">
                            <Label for="parent_username" class="text-xs font-bold text-gray-500 uppercase tracking-wider">Username</Label>
                            <Input id="parent_username" v-model="parentForm.username" placeholder="Username untuk login..." class="h-10 rounded-lg border-gray-200" required />
                        </div>

                        <div class="space-y-1.5">
                            <Label for="parent_password" class="text-xs font-bold text-gray-500 uppercase tracking-wider">Password</Label>
                            <Input id="parent_password" type="password" v-model="parentForm.password" placeholder="Password (min 8 karakter)..." class="h-10 rounded-lg border-gray-200" />
                            <p class="text-[10px] text-gray-400">*) Kosongkan password jika menautkan akun yang sudah pernah dibuat.</p>
                        </div>

                        <div class="space-y-1.5">
                            <Label for="parent_phone" class="text-xs font-bold text-gray-500 uppercase tracking-wider">No. Telepon / HP</Label>
                            <Input id="parent_phone" v-model="parentForm.no_telephone" placeholder="Contoh: 08123456789..." class="h-10 rounded-lg border-gray-200" />
                        </div>

                        <div class="space-y-1.5">
                            <Label for="parent_address" class="text-xs font-bold text-gray-500 uppercase tracking-wider">Alamat</Label>
                            <textarea id="parent_address" v-model="parentForm.alamat" placeholder="Alamat tinggal orang tua..." class="w-full min-h-[80px] p-3 text-sm rounded-lg border border-gray-200 focus:outline-none focus:ring-1 focus:ring-[#4B49AC] font-sans" />
                        </div>

                        <div class="flex gap-3 pt-2">
                            <Button type="button" variant="ghost" @click="isParentSheetOpen = false" class="flex-1 h-10 rounded-lg text-gray-500 font-semibold">Batal</Button>
                            <Button type="submit" :disabled="parentForm.processing" class="flex-1 h-10 bg-[#4B49AC] hover:bg-[#3f3d91] text-white rounded-lg font-semibold shadow-md">
                                <Loader2 v-if="parentForm.processing" class="w-4 h-4 mr-2 animate-spin" />
                                Hubungkan
                            </Button>
                        </div>
                    </form>
                </div>
            </SheetContent>
        </Sheet>

        <!-- Delete Single Dialog -->
        <Dialog :open="isDeleteSingleDialogOpen" @update:open="isDeleteSingleDialogOpen = $event">
            <DialogContent class="sm:max-w-[450px] p-0 overflow-hidden border-none shadow-2xl bg-white rounded-xl">
                <div class="p-8 text-center">
                    <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <AlertCircle class="w-9 h-9 text-red-500" />
                    </div>
                    <DialogHeader>
                        <DialogTitle class="text-xl font-bold text-gray-800 text-center">Konfirmasi Hapus</DialogTitle>
                        <DialogDescription class="text-gray-500 text-center mt-2 px-2 text-sm">
                            Apakah Anda yakin ingin menghapus data mahasiswa <span class="text-red-600 font-bold">"{{ mahasiswaToDelete?.nama_lengkap }}"</span> (NIM: {{ mahasiswaToDelete?.nim }})?
                            <br><span class="text-xs mt-3 block italic text-gray-400">Tindakan ini tidak dapat dibatalkan.</span>
                        </DialogDescription>
                    </DialogHeader>

                    <div class="flex items-center justify-center gap-3 mt-8">
                        <Button type="button" variant="ghost" @click="isDeleteSingleDialogOpen = false" class="h-11 px-6 rounded-lg text-gray-500 hover:bg-gray-100 font-semibold">Batal</Button>
                        <Button @click="confirmDelete" :disabled="isDeleting" class="h-11 px-8 bg-red-600 hover:bg-red-700 text-white rounded-lg shadow-lg shadow-red-200 transition-all font-semibold">
                            <Loader2 v-if="isDeleting" class="w-4 h-4 mr-2 animate-spin" />
                            Ya, Hapus Data
                        </Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Toast Notifications -->
        <transition name="toast">
            <div v-if="showToast" class="fixed bottom-10 right-10 z-[100]">
                <div class="bg-[#1F2937] text-white px-5 py-3.5 rounded-xl shadow-2xl flex items-center gap-3 border border-gray-700 backdrop-blur-sm bg-opacity-95 animate-in slide-in-from-bottom-5">
                    <div :class="toastType === 'error' ? 'bg-red-500' : 'bg-green-500'" class="p-1 rounded-full shrink-0">
                        <CheckCircle2 v-if="toastType === 'success'" class="w-4 h-4 text-white" />
                        <XCircle v-else class="w-4 h-4 text-white" />
                    </div>
                    <span class="text-sm font-bold">{{ toastMessage }}</span>
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
