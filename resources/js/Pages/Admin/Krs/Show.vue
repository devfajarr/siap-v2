<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import { 
    Search, 
    ArrowLeft,
    GraduationCap,
    Users,
    Printer,
    CheckCircle2,
    AlertCircle,
    FileText,
    RefreshCw,
    XCircle
} from 'lucide-vue-next';

const props = defineProps({
    namaKelas: Object,
    mahasiswas: Array,
});

const search = ref('');
const isSyncing = ref(false);
const page = usePage();

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
        setTimeout(() => { showToast.value = false; }, 3000);
    } else if (flash?.error) {
        toastMessage.value = flash.error;
        toastType.value = 'error';
        showToast.value = true;
        setTimeout(() => { showToast.value = false; }, 3000);
    }
}, { deep: true });

const syncFeeder = () => {
    isSyncing.value = true;
    router.post(route('v2.admin.krs.sync-class', props.namaKelas.id), {}, {
        onFinish: () => {
            isSyncing.value = false;
        }
    });
};

const filteredMahasiswas = computed(() => {
    if (!props.mahasiswas) return [];
    if (!search.value) return props.mahasiswas;
    
    const query = search.value.toLowerCase();
    return props.mahasiswas.filter(m => 
        (m.nama_lengkap && m.nama_lengkap.toLowerCase().includes(query)) ||
        (m.nim && m.nim.toLowerCase().includes(query))
    );
});

const totalMahasiswa = computed(() => props.mahasiswas?.length || 0);
const totalSudahKrs = computed(() => props.mahasiswas?.filter(m => m.status_krs === 1).length || 0);
const totalBelumKrs = computed(() => totalMahasiswa.value - totalSudahKrs.value);
</script>

<template>
    <Head :title="`KRS Kelas - ${namaKelas.nama_kelas}`" />

    <AdminLayout>
        <div class="space-y-6">

            <!-- Top Navigation & Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <Button variant="outline" size="icon" as-child class="h-10 w-10 rounded-xl border-gray-200">
                        <Link :href="route('v2.admin.krs.kategori')">
                            <ArrowLeft class="h-5 w-5 text-gray-600" />
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight text-gray-900 flex items-center gap-3">
                            KRS Kelas: {{ namaKelas.nama_kelas }}
                        </h1>
                        <p class="text-muted-foreground mt-1 flex items-center gap-2">
                            <span>{{ namaKelas.prodi?.nama_prodi }}</span>
                            <span class="text-gray-300">|</span>
                            <span>Semester {{ namaKelas.semester?.semester }}</span>
                            <span class="text-gray-300">|</span>
                            <span class="text-sm italic text-[#4B49AC] font-medium">{{ namaKelas.jenis_kelas }}</span>
                        </p>
                    </div>
                </div>

                <!-- Stats Badges -->
                <div class="flex flex-wrap items-center gap-2">
                    <Button 
                        @click="syncFeeder" 
                        :disabled="isSyncing" 
                        class="bg-[#4B49AC] hover:bg-[#3f3d91] text-white rounded-xl shadow-sm flex items-center gap-2 px-4 h-10 transition-all font-bold border-0"
                    >
                        <RefreshCw :class="['h-4 w-4 shrink-0', isSyncing ? 'animate-spin' : '']" />
                        {{ isSyncing ? 'Sinkronisasi...' : 'Sinkron Feeder' }}
                    </Button>
                    <div class="flex items-center gap-2 px-4 py-2 bg-indigo-50 rounded-xl border border-indigo-100 h-10">
                        <Users class="h-4 w-4 text-[#4B49AC]" />
                        <span class="text-sm font-bold text-[#4B49AC]">{{ totalMahasiswa }} Total Mahasiswa</span>
                    </div>
                    <div class="flex items-center gap-2 px-4 py-2 bg-green-50 rounded-xl border border-green-200 h-10">
                        <CheckCircle2 class="h-4 w-4 text-green-600" />
                        <span class="text-sm font-bold text-green-700">{{ totalSudahKrs }} Sudah KRS</span>
                    </div>
                    <div class="flex items-center gap-2 px-4 py-2 bg-amber-50 rounded-xl border border-amber-200 h-10">
                        <AlertCircle class="h-4 w-4 text-amber-600" />
                        <span class="text-sm font-bold text-amber-700">{{ totalBelumKrs }} Belum KRS</span>
                    </div>
                </div>
            </div>

            <!-- Content Card -->
            <Card class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <CardHeader class="border-b border-gray-100 bg-gray-50/50 px-6 py-4">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <CardTitle class="text-lg font-bold text-gray-900">Daftar Mahasiswa & Status KRS</CardTitle>
                            <CardDescription class="text-xs mt-0.5">Pantau status pengajuan dan lakukan pencetakan rekapitulasi KRS</CardDescription>
                        </div>
                        
                        <!-- Search Input -->
                        <div class="relative w-full md:w-72">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                            <Input
                                v-model="search"
                                placeholder="Cari NIM atau nama mahasiswa..."
                                class="pl-9 h-10 rounded-xl border-gray-200 focus:border-[#4B49AC]"
                            />
                        </div>
                    </div>
                </CardHeader>

                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow class="bg-gray-50/30">
                                    <TableHead class="w-[120px] pl-6 font-bold text-gray-600 uppercase tracking-wider text-xs">NIM</TableHead>
                                    <TableHead class="font-bold text-gray-600 uppercase tracking-wider text-xs">Nama Lengkap</TableHead>
                                    <TableHead class="font-bold text-gray-600 uppercase tracking-wider text-xs">Jenis Kelamin</TableHead>
                                    <TableHead class="font-bold text-gray-600 uppercase tracking-wider text-xs">Pembimbing Akademik</TableHead>
                                    <TableHead class="font-bold text-gray-600 uppercase tracking-wider text-xs">Status KRS</TableHead>
                                    <TableHead class="text-right pr-6 font-bold text-gray-600 uppercase tracking-wider text-xs">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="mhs in filteredMahasiswas" :key="mhs.id" class="group hover:bg-gray-50/50 transition-colors">
                                    <TableCell class="pl-6 font-mono font-bold text-[#4B49AC]">{{ mhs.nim }}</TableCell>
                                    <TableCell>
                                        <div class="font-bold text-gray-900">{{ mhs.nama_lengkap }}</div>
                                        <div class="text-xs text-muted-foreground">{{ mhs.email || '-' }}</div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge variant="outline" :class="(mhs.jenis_kelamin && mhs.jenis_kelamin.toLowerCase().includes('laki')) ? 'border-blue-200 bg-blue-50 text-blue-700 font-semibold' : 'border-pink-200 bg-pink-50 text-pink-700 font-semibold'">
                                            {{ mhs.jenis_kelamin }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm font-medium text-gray-700">{{ mhs.pembimbing_akademik?.nama || '-' }}</div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge v-if="mhs.status_krs === 1" variant="outline" class="border font-bold text-xs px-2.5 py-1 bg-green-50 hover:bg-green-50 text-green-700 hover:text-green-700 border-green-200 hover:border-green-200 flex items-center gap-1.5 w-fit shadow-none pointer-events-none cursor-default">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>
                                            Sudah Diajukan
                                        </Badge>
                                        <Badge v-else variant="outline" class="border font-bold text-xs px-2.5 py-1 bg-amber-50 hover:bg-amber-50 text-amber-700 hover:text-amber-700 border-amber-200 hover:border-amber-200 flex items-center gap-1.5 w-fit shadow-none pointer-events-none cursor-default">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 inline-block"></span>
                                            Belum Diajukan
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-right pr-6">
                                        <Button 
                                            v-if="mhs.status_krs === 1"
                                            as-child
                                            size="sm" 
                                            class="bg-[#4B49AC] hover:bg-[#3f3d91] text-white rounded-lg shadow-sm transition-all"
                                        >
                                            <a :href="route('v2.admin.krs.cetak', mhs.id)" target="_blank">
                                                <Printer class="mr-2 h-4 w-4" /> Cetak KRS
                                            </a>
                                        </Button>
                                        <Button 
                                            v-else
                                            disabled
                                            variant="outline"
                                            size="sm" 
                                            class="border-gray-200 text-gray-400 rounded-lg cursor-not-allowed"
                                            title="KRS belum diajukan oleh mahasiswa"
                                        >
                                            <Printer class="mr-2 h-4 w-4 opacity-50" /> Belum Tersedia
                                        </Button>
                                    </TableCell>
                                </TableRow>

                                <TableRow v-if="filteredMahasiswas.length === 0">
                                    <TableCell colspan="6" class="h-64 text-center">
                                        <div class="flex flex-col items-center justify-center space-y-3">
                                            <div class="rounded-full bg-indigo-50 p-4 border border-indigo-100">
                                                <Users class="h-8 w-8 text-[#4B49AC]" />
                                            </div>
                                            <div>
                                                <p class="text-lg font-bold text-gray-900">Data Mahasiswa Tidak Ditemukan</p>
                                                <p class="text-sm text-muted-foreground mt-1">Belum ada mahasiswa yang cocok dengan pencarian atau terdaftar di kelas ini.</p>
                                            </div>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>

            <!-- Toast Notification -->
            <transition name="toast">
                <div v-if="showToast" class="fixed bottom-10 right-10 z-[100]">
                    <div class="bg-[#1F2937] text-white px-8 py-5 rounded-3xl shadow-2xl flex items-center gap-4 border border-gray-700 backdrop-blur-sm bg-opacity-95">
                        <div :class="toastType === 'error' ? 'bg-red-500' : 'bg-green-500'" class="p-2 rounded-full ring-4 ring-white/10">
                            <CheckCircle2 v-if="toastType === 'success'" class="w-5 h-5 text-white" />
                            <XCircle v-else class="w-5 h-5 text-white" />
                        </div>
                        <div class="flex flex-col">
                            <span class="text-sm font-bold tracking-tight">{{ toastMessage }}</span>
                        </div>
                    </div>
                </div>
            </transition>
        </div>
    </AdminLayout>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.toast-enter-from {
    opacity: 0;
    transform: translateX(100px) scale(0.8);
}

.toast-leave-to {
    opacity: 0;
    transform: translateX(100px) scale(0.8);
}
</style>
