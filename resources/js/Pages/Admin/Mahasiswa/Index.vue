<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import {
    Users,
    GraduationCap,
    ChevronDown,
    ChevronRight,
    LayoutGrid,
    BookOpen,
    School,
} from 'lucide-vue-next';

const props = defineProps({
    kelass: Array,
});

// ── State collapse per semester (semua terbuka by default) ────────────────────
const openSemesters = ref({});

// ── Computed: grouping → Semester → Prodi → Jenis Kelas → Kelas[] ────────────
const groupedBySemester = computed(() => {
    const sorted = [...props.kelass].sort((a, b) => {
        if (a.semester_status !== b.semester_status) return (b.semester_status ?? 0) - (a.semester_status ?? 0);
        if (a.id_semester !== b.id_semester) return (a.id_semester ?? 0) - (b.id_semester ?? 0);
        if (a.id_prodi !== b.id_prodi) return (a.id_prodi ?? 0) - (b.id_prodi ?? 0);
        if (a.jenis_kelas !== b.jenis_kelas) return (a.jenis_kelas ?? '').localeCompare(b.jenis_kelas ?? '');
        return (a.nama_kelas ?? '').localeCompare(b.nama_kelas ?? '');
    });

    const semMap = {};

    for (const kelas of sorted) {
        const semKey = kelas.id_semester ?? 'unknown';

        if (!semMap[semKey]) {
            semMap[semKey] = {
                id_semester: kelas.id_semester,
                semester: kelas.semester,
                semester_status: kelas.semester_status,
                prodis: {},
            };
            // Aktif → terbuka, Non-Aktif → tertutup by default
            if (openSemesters.value[semKey] === undefined) {
                openSemesters.value[semKey] = kelas.semester_status === 1;
            }
        }

        const prodiKey = kelas.id_prodi ?? 'unknown';
        if (!semMap[semKey].prodis[prodiKey]) {
            semMap[semKey].prodis[prodiKey] = {
                id_prodi: kelas.id_prodi,
                nama_prodi: kelas.prodi,
                singkatan: kelas.prodi_singkatan,
                jenisGroups: {},
            };
        }

        const jenisKey = kelas.jenis_kelas ?? '-';
        if (!semMap[semKey].prodis[prodiKey].jenisGroups[jenisKey]) {
            semMap[semKey].prodis[prodiKey].jenisGroups[jenisKey] = {
                jenis_kelas: kelas.jenis_kelas,
                kelass: [],
            };
        }

        semMap[semKey].prodis[prodiKey].jenisGroups[jenisKey].kelass.push(kelas);
    }

    return Object.values(semMap).map(sem => ({
        ...sem,
        prodis: Object.values(sem.prodis).map(prodi => ({
            ...prodi,
            jenisGroups: Object.values(prodi.jenisGroups),
        })),
    }));
});

const toggleSemester = (id) => {
    openSemesters.value[id] = !openSemesters.value[id];
};

const isOpen = (id) => openSemesters.value[id] ?? false;

// ── Stats ─────────────────────────────────────────────────────────────────────
const totalKelas = computed(() => props.kelass.length);
const totalMahasiswa = computed(() => props.kelass.reduce((s, k) => s + k.mahasiswa_count, 0));

const kelasCountInSem = (semGroup) =>
    semGroup.prodis.reduce((s, p) => s + p.jenisGroups.reduce((ss, j) => ss + j.kelass.length, 0), 0);
</script>

<template>
    <Head title="Data Mahasiswa" />

    <AdminLayout>
        <div class="space-y-6">

            <!-- ── Page Header ── -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900">Data Mahasiswa</h1>
                    <p class="text-muted-foreground mt-1">
                        Daftar kelas dikelompokkan per semester dan program studi.
                    </p>
                </div>

                <div v-if="kelass.length > 0" class="flex flex-wrap items-center gap-2">
                    <div class="flex items-center gap-2 px-4 py-2 bg-indigo-50 rounded-xl border border-indigo-100">
                        <BookOpen class="h-4 w-4 text-[#4B49AC]" />
                        <span class="text-sm font-bold text-[#4B49AC]">{{ totalKelas }} Kelas</span>
                    </div>
                    <div class="flex items-center gap-2 px-4 py-2 bg-indigo-50 rounded-xl border border-indigo-100">
                        <Users class="h-4 w-4 text-[#4B49AC]" />
                        <span class="text-sm font-bold text-[#4B49AC]">{{ totalMahasiswa }} Mahasiswa</span>
                    </div>
                    <Button variant="outline" as-child class="rounded-lg border-gray-200">
                        <a :href="route('v2.admin.data-mahasiswa.export-all')">
                            <GraduationCap class="mr-2 h-4 w-4" />
                            Export Semua
                        </a>
                    </Button>
                </div>
            </div>

            <!-- ── Empty State ── -->
            <div
                v-if="kelass.length === 0"
                class="flex flex-col items-center justify-center min-h-[400px] p-8 text-center bg-white rounded-xl border-2 border-dashed border-gray-200"
            >
                <div class="rounded-full bg-primary/10 p-6 mb-6">
                    <LayoutGrid class="h-12 w-12 text-primary" />
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Data Kelas Belum Tersedia</h3>
                <p class="text-muted-foreground max-w-sm mb-8">
                    Anda perlu membuat data kelas terlebih dahulu sebelum dapat mengelola data mahasiswa.
                </p>
                <Button as-child size="lg" class="shadow-lg hover:shadow-xl transition-all px-8">
                    <Link :href="route('v2.admin.data-kelas.index')">
                        <LayoutGrid class="mr-2 h-5 w-5" />
                        Kelola Data Kelas
                    </Link>
                </Button>
            </div>

            <!-- ── Grouped View ── -->
            <div v-else class="space-y-4">
                <div
                    v-for="semGroup in groupedBySemester"
                    :key="semGroup.id_semester"
                    class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden"
                >
                    <!-- Semester Header (Collapsible Toggle) -->
                    <button
                        @click="toggleSemester(semGroup.id_semester)"
                        class="w-full flex items-center justify-between px-5 py-4 bg-gradient-to-r from-indigo-50 to-transparent hover:from-indigo-100/70 transition-all text-left group"
                    >
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-xl bg-[#4B49AC] flex items-center justify-center shadow-md shadow-indigo-200 shrink-0">
                                <span class="text-white font-black text-base leading-none">{{ semGroup.semester }}</span>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-gray-900">Semester {{ semGroup.semester }}</h2>
                                <p class="text-xs text-gray-500">{{ semGroup.prodis.length }} Program Studi</p>
                            </div>
                            <Badge class="ml-1 bg-indigo-100 text-[#4B49AC] hover:bg-indigo-100 border-0 font-bold text-xs px-2.5">
                                {{ kelasCountInSem(semGroup) }} Kelas
                            </Badge>
                            <!-- Status Badge -->
                            <Badge
                                v-if="semGroup.semester_status === 1"
                                class="border font-bold text-xs px-2.5 bg-green-50 text-green-700 border-green-200 hover:bg-green-50 flex items-center gap-1.5"
                            >
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>
                                Aktif
                            </Badge>
                            <Badge
                                v-else
                                class="border font-bold text-xs px-2.5 bg-gray-100 text-gray-400 border-gray-200 hover:bg-gray-100 flex items-center gap-1.5"
                            >
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400 inline-block"></span>
                                Non-Aktif
                            </Badge>
                        </div>
                        <div class="text-gray-400 group-hover:text-[#4B49AC] transition-colors">
                            <ChevronDown v-if="isOpen(semGroup.id_semester)" class="h-5 w-5" />
                            <ChevronRight v-else class="h-5 w-5" />
                        </div>
                    </button>

                    <!-- Semester Content — satu baris horizontal, separator per prodi -->
                    <div v-show="isOpen(semGroup.id_semester)" class="border-t border-gray-200">
                        <div class="flex items-stretch flex-wrap px-5 py-4 gap-y-4">
                            <template
                                v-for="(prodiGroup, pi) in semGroup.prodis"
                                :key="prodiGroup.id_prodi"
                            >
                                <!-- Separator vertikal antar prodi -->
                                <div v-if="pi > 0" class="w-px bg-gray-200 self-stretch mx-5 shrink-0"></div>

                                <!-- Blok satu prodi: label + cards -->
                                <div class="flex items-start gap-4">
                                    <!-- Label prodi (singkatan + nama) -->
                                    <div class="flex flex-col items-center gap-1 pt-1 shrink-0 w-10">
                                        <div class="w-9 h-9 rounded-lg bg-indigo-50 border border-indigo-100 shadow-sm flex items-center justify-center">
                                            <span class="text-[10px] font-black text-[#4B49AC] leading-none">
                                                {{ prodiGroup.singkatan ?? '?' }}
                                            </span>
                                        </div>
                                        <span class="text-[9px] font-semibold text-gray-400 text-center leading-tight w-full break-words">
                                            {{ prodiGroup.singkatan ?? '?' }}
                                        </span>
                                    </div>

                                    <!-- Cards semua jenis dalam prodi ini, inline -->
                                    <div class="flex flex-wrap gap-3">
                                        <template
                                            v-for="jenisGroup in prodiGroup.jenisGroups"
                                            :key="jenisGroup.jenis_kelas"
                                        >
                                            <Link
                                                v-for="kelas in jenisGroup.kelass"
                                                :key="kelas.id"
                                                :href="route('v2.admin.data-mahasiswa.show', kelas.id)"
                                                class="group flex flex-col gap-1.5 p-3.5 bg-white rounded-xl border border-gray-200 hover:border-[#4B49AC] hover:shadow-md hover:shadow-indigo-100/60 hover:-translate-y-0.5 transition-all cursor-pointer w-28"
                                            >
                                                <span class="text-sm font-black text-gray-900 group-hover:text-[#4B49AC] transition-colors leading-tight">
                                                    {{ kelas.nama_kelas }}
                                                </span>
                                                <span class="text-[11px] text-gray-400 flex items-center gap-1">
                                                    <Users class="h-3 w-3 shrink-0" />
                                                    {{ kelas.mahasiswa_count }}
                                                </span>
                                                <span
                                                    :class="[
                                                        'text-[9px] font-bold px-1.5 py-0.5 rounded self-start mt-0.5',
                                                        jenisGroup.jenis_kelas === 'Reguler'
                                                            ? 'bg-blue-50 text-blue-500'
                                                            : 'bg-orange-50 text-orange-500'
                                                    ]"
                                                >
                                                    {{ jenisGroup.jenis_kelas }}
                                                </span>
                                            </Link>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AdminLayout>
</template>

<style scoped>
/* Smooth collapse animation via v-show + CSS transition */
[v-show] {
    transition: opacity 0.2s ease;
}
</style>
