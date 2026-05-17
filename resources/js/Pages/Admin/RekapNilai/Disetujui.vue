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
import { FileText, FolderSearch, CheckCircle2 } from 'lucide-vue-next'

defineProps({
    pengajuans: {
        type: Array,
        required: true,
    }
})
</script>

<template>
    <Head title="Nilai Disetujui" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <span class="text-gray-500">Rekap Nilai</span>
                <span class="text-gray-400">/</span>
                <span class="text-gray-800 font-semibold">Nilai Disetujui</span>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <Card class="shadow-sm">
                    <CardHeader class="flex flex-row items-center justify-between pb-4">
                        <CardTitle>Daftar Nilai Disetujui</CardTitle>
                        <Badge variant="outline" class="bg-emerald-50 text-emerald-700 border-emerald-300 font-medium px-3 py-1">
                            <CheckCircle2 class="w-3.5 h-3.5 mr-1.5 inline" /> {{ pengajuans.length }} Rekap Disetujui
                        </Badge>
                    </CardHeader>
                    <CardContent>
                        <div class="rounded-md border overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead class="w-[50px]">#</TableHead>
                                        <TableHead>Tahun Akademik</TableHead>
                                        <TableHead>Kelas</TableHead>
                                        <TableHead>Program Studi</TableHead>
                                        <TableHead>Dosen</TableHead>
                                        <TableHead>Mata Kuliah</TableHead>
                                        <TableHead class="text-center">Status</TableHead>
                                        <TableHead class="text-center">Opsi</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <template v-if="pengajuans.length > 0">
                                        <TableRow v-for="(pengajuan, index) in pengajuans" :key="pengajuan.id">
                                            <TableCell class="font-medium">{{ index + 1 }}</TableCell>
                                            <TableCell>{{ pengajuan.tahun }}</TableCell>
                                            <TableCell>{{ pengajuan.kelas?.nama_kelas || '-' }}</TableCell>
                                            <TableCell>{{ pengajuan.kelas?.prodi?.nama_prodi || '-' }}</TableCell>
                                            <TableCell>{{ pengajuan.jadwal?.dosen?.nama || '-' }}</TableCell>
                                            <TableCell>{{ pengajuan.matkul?.nama_matkul || '-' }}</TableCell>
                                            <TableCell class="text-center">
                                                <Badge variant="secondary" class="bg-emerald-100 text-emerald-800 hover:bg-emerald-100/80 font-normal">
                                                    Disetujui
                                                </Badge>
                                            </TableCell>
                                            <TableCell class="text-center">
                                                <Button as-child size="sm" variant="default" class="bg-emerald-600 hover:bg-emerald-700 text-white">
                                                    <a :href="`/presensi/data-nilai/rekap/${pengajuan.kelas_id}/${pengajuan.matkul_id}/${pengajuan.jadwal_id}`" target="_blank">
                                                        <FileText class="w-4 h-4 mr-1.5" /> Rekap
                                                    </a>
                                                </Button>
                                            </TableCell>
                                        </TableRow>
                                    </template>
                                    <template v-else>
                                        <TableRow>
                                            <TableCell colspan="8" class="h-48 text-center">
                                                <div class="max-w-md mx-auto py-8">
                                                    <div class="w-14 h-14 bg-[#4B49AC]/10 rounded-full flex items-center justify-center mx-auto mb-3 text-[#4B49AC]">
                                                        <FolderSearch class="w-7 h-7 text-[#4B49AC]" />
                                                    </div>
                                                    <p class="text-sm font-medium text-gray-900">Belum ada rekap nilai yang disetujui</p>
                                                    <p class="text-xs text-gray-500 mt-1">Daftar rekapitulasi akhir yang telah diverifikasi akan ditampilkan di sini.</p>
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
        </div>
    </AdminLayout>
</template>
