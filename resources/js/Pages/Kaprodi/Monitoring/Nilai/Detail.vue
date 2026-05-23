<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table'
import { Button } from '@/Components/ui/button'
import { ChevronLeft, Eye } from 'lucide-vue-next'

defineProps({
  kelas: {
    type: Object,
    required: true
  },
  jadwals: {
    type: Array,
    required: true
  }
})
</script>

<template>
  <AdminLayout>
    <Head :title="`Detail Nilai - ${kelas.nama_kelas}`" />

    <div class="space-y-6">
      <div class="flex items-center gap-2">
        <Link 
          :href="route('v2.kaprodi.monitoring.nilai.index')"
          class="p-1 hover:bg-gray-100 rounded-full transition-colors"
        >
          <ChevronLeft class="w-6 h-6 text-gray-500" />
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-[#1F2937]">{{ kelas.nama_kelas }}</h1>
          <p class="text-[#6B7280] text-sm">Monitoring nilai mahasiswa per mata kuliah.</p>
        </div>
      </div>

      <Card class="border-none shadow-sm">
        <CardHeader>
          <CardTitle class="text-lg">Daftar Mata Kuliah</CardTitle>
        </CardHeader>
        <CardContent class="p-0">
          <div class="rounded-md border overflow-x-auto">
            <Table>
              <TableHeader class="bg-gray-50/50">
                <TableRow>
                  <TableHead>Mata Kuliah</TableHead>
                  <TableHead>Dosen Pengampu</TableHead>
                  <TableHead class="text-center">Aksi</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="jadwal in jadwals" :key="jadwal.id">
                  <TableCell class="font-medium">{{ jadwal.matkul?.nama_matkul }}</TableCell>
                  <TableCell>{{ jadwal.dosen?.nama }}</TableCell>
                  <TableCell class="text-center">
                    <Link
                      v-if="jadwal.absen_max_pertemuan > 0"
                      :href="route('v2.kaprodi.monitoring.nilai.cek', { matkul_id: jadwal.matkuls_id, kelas_id: kelas.id, jadwal_id: jadwal.id })"
                    >
                      <Button variant="outline" size="sm" class="h-8 gap-1.5 hover:bg-[#4B49AC] hover:text-white transition-all duration-200 shadow-sm">
                        <Eye class="w-4 h-4" />
                        Lihat Nilai
                      </Button>
                    </Link>
                    <div v-else>
                      <Button variant="outline" size="sm" class="h-8 gap-1.5 cursor-not-allowed opacity-50" disabled>
                        <Eye class="w-4 h-4" />
                        Lihat Nilai
                      </Button>
                      <div class="text-[10px] text-gray-400 mt-1 italic">Belum ada data presensi</div>
                    </div>
                  </TableCell>
                </TableRow>
                <TableRow v-if="jadwals.length === 0">
                  <TableCell colspan="3" class="h-32 text-center text-gray-500">
                    Tidak ada data mata kuliah untuk kelas ini.
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </CardContent>
      </Card>
    </div>
  </AdminLayout>
</template>
