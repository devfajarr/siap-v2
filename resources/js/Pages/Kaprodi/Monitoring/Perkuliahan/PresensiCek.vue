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
import { Badge } from '@/Components/ui/badge'
import { ChevronLeft } from 'lucide-vue-next'

const props = defineProps({
  jadwal: {
    type: Object,
    required: true
  },
  rentang: {
    type: String,
    required: true
  },
  presensi: {
    type: Object,
    required: true
  }
})

const pertemuanRange = props.rentang === '1-7' ? [1, 2, 3, 4, 5, 6, 7] : [8, 9, 10, 11, 12, 13, 14]

const getStatus = (mahasiswaPresensi, pertemuan) => {
  const p = mahasiswaPresensi.find(item => item.pertemuan === pertemuan)
  if (!p) return '-'
  return p.status
}

const getStatusVariant = (status) => {
  switch (status) {
    case 'Hadir': return 'success'
    case 'Sakit': return 'warning'
    case 'Izin': return 'indigo'
    case 'Alpa': return 'destructive'
    default: return 'outline'
  }
}
</script>

<template>
  <AdminLayout>
    <Head :title="`Cek Presensi - ${jadwal.matkul?.nama_matkul}`" />

    <div class="space-y-6">
      <div class="flex items-center gap-2">
        <Link 
          :href="route('v2.kaprodi.monitoring.perkuliahan.detail', jadwal.kelas_id)"
          class="p-1 hover:bg-gray-100 rounded-full transition-colors"
        >
          <ChevronLeft class="w-6 h-6 text-gray-500" />
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-[#1F2937]">Cek Presensi Pertemuan {{ rentang }}</h1>
          <p class="text-[#6B7280] text-sm">
            {{ jadwal.matkul?.nama_matkul }} - {{ jadwal.kelas?.nama_kelas }} ({{ jadwal.dosen?.nama }})
          </p>
        </div>
      </div>

      <Card class="border-none shadow-sm">
        <CardContent class="p-0">
          <div class="rounded-md border overflow-x-auto">
            <Table>
              <TableHeader class="bg-gray-50/50 text-[10px] uppercase tracking-wider">
                <TableRow>
                  <TableHead class="w-12 text-center">#</TableHead>
                  <TableHead class="w-48">Nama Mahasiswa</TableHead>
                  <TableHead v-for="p in pertemuanRange" :key="p" class="text-center font-bold">
                    P{{ p }}
                  </TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="(mahasiswaPresensi, mhsId, index) in presensi" :key="mhsId">
                  <TableCell class="text-center text-xs text-gray-500">{{ index + 1 }}</TableCell>
                  <TableCell class="font-medium text-xs whitespace-nowrap">
                    {{ mahasiswaPresensi[0]?.mahasiswa?.nama_lengkap }}
                    <div class="text-[10px] text-gray-400 font-normal">{{ mahasiswaPresensi[0]?.mahasiswa?.nim }}</div>
                  </TableCell>
                  <TableCell v-for="p in pertemuanRange" :key="p" class="text-center">
                    <Badge 
                      v-if="getStatus(mahasiswaPresensi, p) !== '-'"
                      :variant="getStatusVariant(getStatus(mahasiswaPresensi, p))"
                      class="text-[9px] px-1.5 py-0.5"
                    >
                      {{ getStatus(mahasiswaPresensi, p).charAt(0) }}
                    </Badge>
                    <span v-else class="text-gray-300">-</span>
                  </TableCell>
                </TableRow>
                <TableRow v-if="Object.keys(presensi).length === 0">
                  <TableCell :colspan="pertmuanRange.length + 2" class="h-32 text-center text-gray-500">
                    Belum ada data presensi yang diisi untuk rentang pertemuan ini.
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </CardContent>
      </Card>

      <!-- Legend -->
      <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500 bg-white p-4 rounded-lg shadow-sm">
        <span class="font-bold">Keterangan:</span>
        <div class="flex items-center gap-1">
          <Badge variant="success" class="text-[9px] px-1.5 py-0.5">H</Badge> Hadir
        </div>
        <div class="flex items-center gap-1">
          <Badge variant="warning" class="text-[9px] px-1.5 py-0.5">S</Badge> Sakit
        </div>
        <div class="flex items-center gap-1">
          <Badge variant="indigo" class="text-[9px] px-1.5 py-0.5">I</Badge> Izin
        </div>
        <div class="flex items-center gap-1">
          <Badge variant="destructive" class="text-[9px] px-1.5 py-0.5">A</Badge> Alpa
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
