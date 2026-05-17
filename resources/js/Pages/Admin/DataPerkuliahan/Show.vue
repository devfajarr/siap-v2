<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { ChevronDown, Download, Eye } from 'lucide-vue-next'

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
    <Head :title="`Mata Kuliah - ${dosen.nama}`" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <Link :href="route('v2.admin.data-perkuliahan.index')" class="text-gray-500 hover:text-gray-700">
                    Data Perkuliahan
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
                        <div class="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead class="w-[50px]">#</TableHead>
                                        <TableHead>Kode</TableHead>
                                        <TableHead>Nama Mata Kuliah</TableHead>
                                        <TableHead class="text-center">SKS</TableHead>
                                        <TableHead>Prodi</TableHead>
                                        <TableHead>Semester</TableHead>
                                        <TableHead class="text-center">Lembar Monitoring</TableHead>
                                        <TableHead class="text-center">Presensi</TableHead>
                                        <TableHead class="text-center">Berita Acara</TableHead>
                                        <TableHead class="text-center">Kontrak Perkuliahan</TableHead>
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
                                            
                                            <!-- Lembar Monitoring -->
                                            <TableCell class="text-center">
                                                <Button 
                                                    v-if="jadwal.has_message" 
                                                    as-child 
                                                    size="sm" 
                                                    variant="default"
                                                    class="bg-emerald-600 hover:bg-emerald-700"
                                                >
                                                    <a :href="`/presensi/lembar-monitoring/${jadwal.id}`" target="_blank">
                                                        <Download class="w-4 h-4 mr-2" /> Download
                                                    </a>
                                                </Button>
                                                <Button v-else disabled size="sm" variant="secondary">
                                                    <Download class="w-4 h-4 mr-2" /> Download
                                                </Button>
                                            </TableCell>

                                            <!-- Presensi -->
                                            <TableCell class="text-center">
                                                <DropdownMenu v-if="jadwal.pertemuan_max >= 1">
                                                    <DropdownMenuTrigger as-child>
                                                        <Button size="sm" variant="outline" class="bg-yellow-50 text-yellow-700 border-yellow-200 hover:bg-yellow-100 hover:text-yellow-800">
                                                            Pilih Pertemuan <ChevronDown class="w-4 h-4 ml-2" />
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent align="end">
                                                        <DropdownMenuItem as-child v-if="jadwal.pertemuan_max >= 1">
                                                            <a :href="`/presensi/data/presence/${jadwal.matkuls_id}/${jadwal.kelas_id}/${jadwal.id}/1-7`" target="_blank">Pertemuan 1-7</a>
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem as-child v-if="jadwal.pertemuan_max >= 8">
                                                            <a :href="`/presensi/data/presence/${jadwal.matkuls_id}/${jadwal.kelas_id}/${jadwal.id}/8-14`" target="_blank">Pertemuan 8-14</a>
                                                        </DropdownMenuItem>
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                                <Button v-else disabled size="sm" variant="secondary">
                                                    Pilih Pertemuan <ChevronDown class="w-4 h-4 ml-2" />
                                                </Button>
                                            </TableCell>

                                            <!-- Berita Acara -->
                                            <TableCell class="text-center">
                                                <DropdownMenu v-if="jadwal.berita_max >= 1">
                                                    <DropdownMenuTrigger as-child>
                                                        <Button size="sm" variant="outline" class="bg-yellow-50 text-yellow-700 border-yellow-200 hover:bg-yellow-100 hover:text-yellow-800">
                                                            Pilih Pertemuan <ChevronDown class="w-4 h-4 ml-2" />
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent align="end">
                                                        <DropdownMenuItem as-child v-if="jadwal.berita_max >= 1">
                                                            <a :href="`/presensi/data/resume/${jadwal.matkuls_id}/${jadwal.kelas_id}/${jadwal.id}/1-7`" target="_blank">Pertemuan 1-7</a>
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem as-child v-if="jadwal.berita_max >= 8">
                                                            <a :href="`/presensi/data/resume/${jadwal.matkuls_id}/${jadwal.kelas_id}/${jadwal.id}/8-14`" target="_blank">Pertemuan 8-14</a>
                                                        </DropdownMenuItem>
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                                <Button v-else disabled size="sm" variant="secondary">
                                                    Pilih Pertemuan <ChevronDown class="w-4 h-4 ml-2" />
                                                </Button>
                                            </TableCell>

                                            <!-- Kontrak Perkuliahan -->
                                            <TableCell class="text-center">
                                                <Button 
                                                    v-if="jadwal.kontrak_max >= 1" 
                                                    as-child 
                                                    size="sm" 
                                                    variant="outline"
                                                    class="bg-yellow-50 text-yellow-700 border-yellow-200 hover:bg-yellow-100 hover:text-yellow-800"
                                                >
                                                    <a :href="`/presensi/data/contract/${jadwal.matkuls_id}/${jadwal.kelas_id}/${jadwal.id}/cek`" target="_blank">
                                                        <Eye class="w-4 h-4 mr-2" /> Lihat
                                                    </a>
                                                </Button>
                                                <Button v-else disabled size="sm" variant="secondary">
                                                    <Eye class="w-4 h-4 mr-2" /> Lihat
                                                </Button>
                                            </TableCell>

                                        </TableRow>
                                    </template>
                                    <template v-else>
                                        <TableRow>
                                            <TableCell colspan="10" class="h-24 text-center">
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
