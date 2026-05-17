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
import { Eye, AlertCircle } from 'lucide-vue-next'

const props = defineProps({
    dosen: {
        type: Object,
        required: true,
    },
    jadwals: {
        type: Array,
        required: true,
    }
})
</script>

<template>
    <Head :title="`Data Nilai - ${dosen.nama}`" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <Link :href="route('v2.admin.data-nilai.index')" class="text-gray-500 hover:text-gray-700">
                    Data Nilai
                </Link>
                <span class="text-gray-400">/</span>
                <span class="text-gray-800 font-semibold">{{ dosen.nama }}</span>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <Card class="shadow-sm">
                    <CardHeader>
                        <CardTitle>Daftar Mata Kuliah</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="rounded-md border overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead class="w-[50px]">#</TableHead>
                                        <TableHead>Kode</TableHead>
                                        <TableHead>Nama Mata Kuliah</TableHead>
                                        <TableHead class="text-center">SKS</TableHead>
                                        <TableHead>Prodi</TableHead>
                                        <TableHead>Semester</TableHead>
                                        <TableHead class="text-center">Rekap Nilai</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <template v-if="jadwals.length">
                                        <TableRow v-for="(jadwal, index) in jadwals" :key="jadwal.id">
                                            <TableCell class="font-medium">{{ index + 1 }}</TableCell>
                                            <TableCell>{{ jadwal.kode }}</TableCell>
                                            <TableCell>{{ jadwal.nama_matkul }}</TableCell>
                                            <TableCell class="text-center">{{ jadwal.sks }}</TableCell>
                                            <TableCell>{{ jadwal.prodi }}</TableCell>
                                            <TableCell>Semester {{ jadwal.semester }}</TableCell>
                                            
                                            <!-- Rekap Nilai -->
                                            <TableCell class="text-center">
                                                <div class="flex items-center justify-center gap-2">
                                                    <Button 
                                                        v-if="jadwal.pertemuan_max >= 1" 
                                                        as-child 
                                                        size="sm" 
                                                        variant="outline"
                                                        class="bg-yellow-50 text-yellow-700 border-yellow-200 hover:bg-yellow-100 hover:text-yellow-800"
                                                    >
                                                        <a :href="`/presensi/data/value/${jadwal.kelas_id}/${jadwal.matkuls_id}/${jadwal.id}/cek`" target="_blank">
                                                            <Eye class="w-4 h-4 mr-2" /> Lihat Rekap
                                                        </a>
                                                    </Button>
                                                    <div v-else class="flex items-center gap-2">
                                                        <Button disabled size="sm" variant="secondary" class="cursor-not-allowed">
                                                            <Eye class="w-4 h-4 mr-2" /> Lihat Rekap
                                                        </Button>
                                                        <Badge variant="destructive" class="text-[11px] py-0.5 px-2 font-normal flex items-center gap-1 shrink-0">
                                                            <AlertCircle class="w-3 h-3" /> Belum ada presensi
                                                        </Badge>
                                                    </div>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    </template>
                                    <template v-else>
                                        <TableRow>
                                            <TableCell colspan="7" class="h-24 text-center text-gray-500">
                                                Matkul belum ditambahkan.
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
