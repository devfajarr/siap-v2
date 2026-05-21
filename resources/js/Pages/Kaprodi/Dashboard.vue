<script setup>
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Badge } from '@/Components/ui/badge'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table'
import { 
  Users, 
  ClipboardCheck, 
  FileText, 
  BookOpenCheck,
  Star,
  CalendarDays
} from 'lucide-vue-next'

const props = defineProps({
  prodi: {
    type: String,
    required: true
  },
  stats: {
    type: Object,
    required: true
  },
  jadwalHariIni: {
    type: Array,
    required: true
  }
})

const cards = [
  {
    title: 'Total Mahasiswa',
    value: props.stats.totalMahasiswa,
    icon: Users,
    color: 'bg-blue-500',
    description: `Mahasiswa Program Studi ${props.prodi}`
  },
  {
    title: 'Pending Presensi',
    value: props.stats.pendingPresensi,
    icon: ClipboardCheck,
    color: 'bg-orange-500',
    description: 'Menunggu persetujuan rekap presensi'
  },
  {
    title: 'Pending Kontrak',
    value: props.stats.pendingKontrak,
    icon: BookOpenCheck,
    color: 'bg-purple-500',
    description: 'Menunggu persetujuan kontrak kuliah'
  },
  {
    title: 'Pending Berita Acara',
    value: props.stats.pendingResume,
    icon: FileText,
    color: 'bg-green-500',
    description: 'Menunggu persetujuan berita acara'
  },
  {
    title: 'Pending Nilai',
    value: props.stats.pendingNilai,
    icon: Star,
    color: 'bg-yellow-500',
    description: 'Menunggu persetujuan rekap nilai'
  }
]
</script>

<template>
  <AdminLayout>
    <Head title="Dashboard Kaprodi" />

    <div class="space-y-6">
      <!-- Header -->
      <div>
        <h1 class="text-2xl font-bold text-[#1F2937]">Dashboard Kaprodi</h1>
        <p class="text-[#6B7280]">Selamat datang! Berikut ringkasan kegiatan akademik Program Studi {{ prodi }}.</p>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
        <Card v-for="card in cards" :key="card.title" class="border-none shadow-md overflow-hidden">
          <CardContent class="p-4">
            <div class="flex flex-col space-y-2">
              <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-primary uppercase tracking-wider">{{ card.title }}</p>
                <div :class="[card.color, 'p-2 rounded-lg text-white shadow-sm']">
                  <component :is="card.icon" class="w-4 h-4" />
                </div>
              </div>
              <h3 class="text-2xl font-bold text-[#1F2937]">{{ card.value }}</h3>
              <p class="text-[10px] text-[#9CA3AF] leading-tight">{{ card.description }}</p>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Jadwal Hari Ini -->
      <Card class="border-none shadow-sm">
        <CardHeader class="flex flex-row items-center justify-between pb-2">
          <div class="flex items-center gap-2">
            <CalendarDays class="w-5 h-5 text-primary" />
            <CardTitle class="text-lg font-bold">Jadwal Perkuliahan Hari Ini - {{ prodi }}</CardTitle>
          </div>
        </CardHeader>
        <CardContent>
          <div class="rounded-md border overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Kelas</TableHead>
                  <TableHead>Mata Kuliah</TableHead>
                  <TableHead>Dosen</TableHead>
                  <TableHead>Ruangan</TableHead>
                  <TableHead>Jam</TableHead>
                  <TableHead class="text-center">Status</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="jadwal in jadwalHariIni" :key="jadwal.id">
                  <TableCell class="font-medium">{{ jadwal.kelas }}</TableCell>
                  <TableCell>{{ jadwal.matkul }}</TableCell>
                  <TableCell>{{ jadwal.dosen }}</TableCell>
                  <TableCell>{{ jadwal.ruangan }}</TableCell>
                  <TableCell>{{ jadwal.jam }}</TableCell>
                  <TableCell class="text-center">
                    <Badge :variant="jadwal.status_variant">
                      {{ jadwal.status }}
                    </Badge>
                  </TableCell>
                </TableRow>
                <TableRow v-if="jadwalHariIni.length === 0">
                  <TableCell colspan="6" class="h-24 text-center text-muted-foreground">
                    Tidak ada jadwal perkuliahan hari ini.
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
