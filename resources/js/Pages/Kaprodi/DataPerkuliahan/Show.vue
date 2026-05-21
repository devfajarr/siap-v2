<script setup>
import { Head, Link } from '@inertiajs/vue3'
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
    ChevronLeft, 
    Download, 
    MessageCircle, 
    BookOpen, 
    User, 
    Calendar,
    CheckCircle2,
    FileText,
    History
} from 'lucide-vue-next'

const props = defineProps({
    dosen: {
        type: Object,
        required: true,
    },
    jadwals: {
        type: Array,
        required: true,
    },
    tahunAkademik: {
        type: String,
        default: '-'
    }
})

const getProgressVariant = (current, total = 14) => {
    const percentage = (current / total) * 100
    if (percentage >= 100) return 'success'
    if (percentage >= 50) return 'warning'
    return 'destructive'
}
</script>

<template>
    <Head :title="`Detail Jadwal - ${dosen.nama}`" />

    <AdminLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <Button as-child variant="outline" size="icon" class="rounded-xl h-10 w-10 border-gray-200 shadow-sm">
                        <Link :href="route('v2.kaprodi.data-perkuliahan.index')">
                            <ChevronLeft class="w-5 h-5 text-gray-600" />
                        </Link>
                    </Button>
                    <div>
                        <div class="flex items-center gap-2 mb-0.5">
                            <h2 class="text-xl font-bold text-gray-900">{{ dosen.nama }}</h2>
                            <Badge variant="outline" class="bg-indigo-50 text-[#4B49AC] border-indigo-100 font-bold text-[10px] uppercase">Dosen</Badge>
                        </div>
                        <p class="text-xs text-gray-500 flex items-center gap-1">
                            <Calendar class="w-3 h-3" /> Monitoring Jadwal - TA {{ tahunAkademik }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="hidden md:flex flex-col items-end">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Mata Kuliah</span>
                        <span class="text-lg font-black text-[#4B49AC] leading-none">{{ jadwals.length }}</span>
                    </div>
                    <div class="w-px h-8 bg-gray-200 hidden md:block mx-1"></div>
                    <Badge variant="secondary" class="bg-indigo-600 text-white border-none px-4 py-1.5 font-bold shadow-lg shadow-indigo-100">
                        Semester Aktif
                    </Badge>
                </div>
            </div>
        </template>

        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <Card class="border-none shadow-sm bg-white overflow-hidden relative">
                    <div class="absolute right-0 top-0 p-4 opacity-10 pointer-events-none">
                        <CheckCircle2 class="w-16 h-16 text-emerald-600" />
                    </div>
                    <CardContent class="p-6">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                <CheckCircle2 class="w-6 h-6" />
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Rata-rata Presensi</p>
                                <h3 class="text-2xl font-black text-gray-900">
                                    {{ jadwals.length > 0 ? (jadwals.reduce((acc, curr) => acc + curr.pertemuan_max, 0) / jadwals.length).toFixed(1) : 0 }} 
                                    <span class="text-sm font-medium text-gray-400">/ 14</span>
                                </h3>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="border-none shadow-sm bg-white overflow-hidden relative">
                    <div class="absolute right-0 top-0 p-4 opacity-10 pointer-events-none">
                        <FileText class="w-16 h-16 text-blue-600" />
                    </div>
                    <CardContent class="p-6">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                                <FileText class="w-6 h-6" />
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Rata-rata Berita Acara</p>
                                <h3 class="text-2xl font-black text-gray-900">
                                    {{ jadwals.length > 0 ? (jadwals.reduce((acc, curr) => acc + curr.berita_max, 0) / jadwals.length).toFixed(1) : 0 }}
                                    <span class="text-sm font-medium text-gray-400">/ 14</span>
                                </h3>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="border-none shadow-sm bg-white overflow-hidden relative">
                    <div class="absolute right-0 top-0 p-4 opacity-10 pointer-events-none">
                        <History class="w-16 h-16 text-amber-600" />
                    </div>
                    <CardContent class="p-6">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                                <History class="w-6 h-6" />
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Kontrak Perkuliahan</p>
                                <h3 class="text-2xl font-black text-gray-900">
                                    {{ jadwals.filter(j => j.kontrak_max > 0).length }}
                                    <span class="text-sm font-medium text-gray-400">Terisi / {{ jadwals.length }} MK</span>
                                </h3>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Table Card -->
            <Card class="border-none shadow-sm bg-white overflow-hidden rounded-xl">
                <CardHeader class="p-6 border-b border-gray-50 flex flex-row items-center justify-between">
                    <div>
                        <CardTitle class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <BookOpen class="w-5 h-5 text-[#4B49AC]" /> Daftar Mata Kuliah Diampu
                        </CardTitle>
                        <p class="text-xs text-gray-500 mt-1">Gunakan tabel di bawah untuk memantau progres perkuliahan secara detail</p>
                    </div>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow class="bg-gray-50/50 hover:bg-gray-50/50">
                                    <TableHead class="w-[60px] font-bold text-xs uppercase tracking-wider text-gray-500 text-center py-4">No</TableHead>
                                    <TableHead class="font-bold text-xs uppercase tracking-wider text-gray-500 py-4">Mata Kuliah</TableHead>
                                    <TableHead class="font-bold text-xs uppercase tracking-wider text-gray-500 text-center py-4">SKS</TableHead>
                                    <TableHead class="font-bold text-xs uppercase tracking-wider text-gray-500 py-4">Kelas / Prodi</TableHead>
                                    <TableHead class="font-bold text-xs uppercase tracking-wider text-gray-500 text-center py-4">Smstr</TableHead>
                                    <TableHead class="font-bold text-xs uppercase tracking-wider text-gray-500 text-center py-4">Presensi</TableHead>
                                    <TableHead class="font-bold text-xs uppercase tracking-wider text-gray-500 text-center py-4">BAP</TableHead>
                                    <TableHead class="font-bold text-xs uppercase tracking-wider text-gray-500 text-center py-4">Kontrak</TableHead>
                                    <TableHead class="font-bold text-xs uppercase tracking-wider text-gray-500 text-center py-4">Pesan</TableHead>
                                    <TableHead class="font-bold text-xs uppercase tracking-wider text-gray-500 text-center py-4">Monitoring</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <template v-if="jadwals.length > 0">
                                    <TableRow v-for="(jadwal, index) in jadwals" :key="jadwal.id" class="group hover:bg-indigo-50/30 transition-colors">
                                        <TableCell class="text-center font-medium text-gray-500">{{ index + 1 }}</TableCell>
                                        <TableCell>
                                            <div class="flex flex-col">
                                                <span class="font-bold text-gray-900 group-hover:text-[#4B49AC] transition-colors leading-tight">{{ jadwal.nama_matkul }}</span>
                                                <span class="text-[10px] font-mono font-semibold text-gray-400 mt-1 uppercase tracking-tighter">{{ jadwal.kode }}</span>
                                            </div>
                                        </TableCell>
                                        <TableCell class="text-center">
                                            <Badge variant="outline" class="font-bold text-xs px-2 py-0 border-gray-200 text-gray-600 bg-gray-50">{{ jadwal.sks }}</Badge>
                                        </TableCell>
                                        <TableCell>
                                            <div class="flex flex-col">
                                                <span class="font-bold text-gray-800 text-xs">{{ jadwal.nama_kelas || '-' }}</span>
                                                <span class="text-[10px] text-gray-500 truncate max-w-[150px]">{{ jadwal.prodi }}</span>
                                            </div>
                                        </TableCell>
                                        <TableCell class="text-center">
                                            <span class="text-xs font-bold text-gray-600">Smt {{ jadwal.semester }}</span>
                                        </TableCell>
                                        
                                        <!-- Pertemuan Max (Presensi) -->
                                        <TableCell class="text-center">
                                            <div class="flex flex-col items-center gap-1">
                                                <span class="text-sm font-black" :class="jadwal.pertemuan_max >= 14 ? 'text-emerald-600' : 'text-gray-900'">
                                                    {{ jadwal.pertemuan_max }}
                                                </span>
                                                <div class="w-12 h-1 bg-gray-100 rounded-full overflow-hidden">
                                                    <div class="h-full rounded-full transition-all duration-500" 
                                                        :class="jadwal.pertemuan_max >= 14 ? 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.4)]' : (jadwal.pertemuan_max >= 7 ? 'bg-amber-400' : 'bg-rose-500')"
                                                        :style="{ width: `${Math.min((jadwal.pertemuan_max / 14) * 100, 100)}%` }">
                                                    </div>
                                                </div>
                                            </div>
                                        </TableCell>

                                        <!-- Berita Max (BAP) -->
                                        <TableCell class="text-center">
                                            <div class="flex flex-col items-center gap-1">
                                                <span class="text-sm font-black" :class="jadwal.berita_max >= 14 ? 'text-blue-600' : 'text-gray-900'">
                                                    {{ jadwal.berita_max }}
                                                </span>
                                                <div class="w-12 h-1 bg-gray-100 rounded-full overflow-hidden">
                                                    <div class="h-full rounded-full transition-all duration-500" 
                                                        :class="jadwal.berita_max >= 14 ? 'bg-blue-500' : (jadwal.berita_max >= 7 ? 'bg-indigo-400' : 'bg-rose-400')"
                                                        :style="{ width: `${Math.min((jadwal.berita_max / 14) * 100, 100)}%` }">
                                                    </div>
                                                </div>
                                            </div>
                                        </TableCell>

                                        <!-- Kontrak Max -->
                                        <TableCell class="text-center">
                                            <div v-if="jadwal.kontrak_max > 0" class="flex items-center justify-center text-emerald-500" title="Kontrak Tersedia">
                                                <CheckCircle2 class="w-5 h-5" />
                                            </div>
                                            <div v-else class="flex items-center justify-center text-gray-300" title="Belum Terisi">
                                                <History class="w-5 h-5" />
                                            </div>
                                        </TableCell>

                                        <!-- Pesan / Chat Indikator -->
                                        <TableCell class="text-center">
                                            <div v-if="jadwal.has_message" class="relative inline-flex items-center justify-center">
                                                <MessageCircle class="w-5 h-5 text-indigo-500 fill-indigo-50 animate-pulse" />
                                                <span class="absolute -top-1 -right-1 flex h-2.5 w-2.5">
                                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500"></span>
                                                </span>
                                            </div>
                                            <div v-else class="text-gray-200">
                                                <MessageCircle class="w-5 h-5" />
                                            </div>
                                        </TableCell>

                                        <!-- Download Monitoring -->
                                        <TableCell class="text-center py-4">
                                            <Button 
                                                as-child 
                                                size="sm" 
                                                variant="outline" 
                                                class="rounded-lg h-8 px-3 border-gray-200 text-gray-600 hover:bg-[#4B49AC] hover:text-white hover:border-[#4B49AC] transition-all group-hover:shadow-md"
                                            >
                                                <a :href="`/presensi/lembar-monitoring/${jadwal.id}`" target="_blank">
                                                    <Download class="w-3.5 h-3.5 mr-1.5" /> Cetak
                                                </a>
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                </template>
                                <template v-else>
                                    <TableRow>
                                        <TableCell colspan="10" class="h-64 text-center">
                                            <div class="flex flex-col items-center justify-center py-10">
                                                <BookOpen class="w-12 h-12 text-gray-200 mb-3" />
                                                <p class="text-gray-500 font-medium">Tidak ada jadwal mata kuliah ditemukan.</p>
                                                <p class="text-xs text-gray-400 mt-1">Dosen ini mungkin tidak memiliki jadwal aktif di Prodi Anda semester ini.</p>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                </template>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>
