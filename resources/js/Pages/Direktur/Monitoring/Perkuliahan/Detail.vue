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
import { ChevronLeft } from 'lucide-vue-next'

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
    <Head :title="`Detail Perkuliahan - ${kelas.nama_kelas}`" />

    <div class="space-y-6">
      <div class="flex items-center gap-2">
        <Link 
          :href="route('v2.direktur.monitoring.perkuliahan.index')"
          class="p-1 hover:bg-gray-100 rounded-full transition-colors"
        >
          <ChevronLeft class="w-6 h-6 text-gray-500" />
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-[#1F2937] font-nunito">{{ kelas.nama_kelas }}</h1>
          <p class="text-[#6B7280] text-sm">
            Monitoring kehadiran mahasiswa per mata kuliah. Prodi: {{ kelas.prodi?.nama_prodi ?? '-' }}
          </p>
        </div>
      </div>

      <Card class="border-none shadow-sm rounded-lg">
        <CardHeader>
          <CardTitle class="text-lg font-nunito">Daftar Mata Kuliah & Jadwal</CardTitle>
        </CardHeader>
        <CardContent class="p-0">
          <div class="rounded-md border overflow-x-auto mx-4 mb-4">
            <Table>
              <TableHeader class="bg-gray-50/50">
                <TableRow>
                  <TableHead>Mata Kuliah</TableHead>
                  <TableHead>Dosen</TableHead>
                  <TableHead>Hari / Waktu</TableHead>
                  <TableHead>Ruangan</TableHead>
                  <TableHead class="text-center">Cek Presensi</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="jadwal in jadwals" :key="jadwal.id">
                  <TableCell class="font-medium text-gray-800">{{ jadwal.matkul?.nama_matkul }}</TableCell>
                  <TableCell>{{ jadwal.dosen?.nama }}</TableCell>
                  <TableCell>
                    {{ jadwal.hari }}, {{ jadwal.waktu_mulai.substring(0, 5) }} - {{ jadwal.waktu_selesai.substring(0, 5) }}
                  </TableCell>
                  <TableCell>{{ jadwal.ruangan?.nama }}</TableCell>
                  <TableCell class="text-center">
                    <div class="flex items-center justify-center gap-2">
                      <Link 
                        :href="route('v2.direktur.monitoring.perkuliahan.presensi-cek', {
                          matkul_id: jadwal.matkuls_id,
                          kelas_id: kelas.id,
                          jadwal_id: jadwal.id,
                          rentang: '1-7'
                        })"
                      >
                        <Button variant="outline" size="sm" class="h-8 text-xs bg-blue-50 text-blue-600 border-blue-200 hover:bg-blue-100 font-medium">
                          P 1-7
                        </Button>
                      </Link>
                      <Link 
                        :href="route('v2.direktur.monitoring.perkuliahan.presensi-cek', {
                          matkul_id: jadwal.matkuls_id,
                          kelas_id: kelas.id,
                          jadwal_id: jadwal.id,
                          rentang: '8-14'
                        })"
                      >
                        <Button variant="outline" size="sm" class="h-8 text-xs bg-indigo-50 text-indigo-600 border-indigo-200 hover:bg-indigo-100 font-medium">
                          P 8-14
                        </Button>
                      </Link>
                    </div>
                  </TableCell>
                </TableRow>
                <TableRow v-if="jadwals.length === 0">
                  <TableCell colspan="5" class="h-32 text-center text-gray-500">
                    Tidak ada jadwal perkuliahan untuk kelas ini.
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

<style scoped>
.font-nunito {
  font-family: 'Nunito', sans-serif;
}
</style>
