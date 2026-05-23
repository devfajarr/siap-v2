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
  School, 
  BookOpen, 
  ClipboardCheck,
  CalendarDays
} from 'lucide-vue-next'

const props = defineProps({
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
    title: 'Total Kelas',
    value: props.stats.totalKelas,
    icon: School,
    color: 'bg-blue-500',
    description: 'Jumlah kelas yang diampu'
  },
  {
    title: 'Total Mata Kuliah',
    value: props.stats.totalMatakuliah,
    icon: BookOpen,
    color: 'bg-purple-500',
    description: 'Jumlah mata kuliah yang diampu'
  },
  {
    title: 'Presensi Hari Ini',
    value: props.stats.totalPresensiHariIni,
    icon: ClipboardCheck,
    color: 'bg-orange-500',
    description: 'Jumlah presensi yang sudah diisi hari ini'
  }
]
</script>

<template>
  <AdminLayout>
    <Head title="Dashboard Dosen" />

    <div class="space-y-6">
      <!-- Header -->
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-[#1F2937]">Dashboard Dosen</h1>
        <p class="text-[#6B7280]">Selamat datang kembali! Berikut ringkasan kegiatan mengajar Anda.</p>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Card v-for="card in cards" :key="card.title" class="border-none shadow-md overflow-hidden">
          <CardContent class="p-4 sm:p-6">
            <div class="flex items-center justify-between">
              <div class="space-y-2">
                <p class="text-sm font-medium text-primary">{{ card.title }}</p>
                <h3 class="text-3xl font-bold text-[#1F2937] tracking-tight">{{ card.value }}</h3>
                <p class="text-xs text-[#9CA3AF]">{{ card.description }}</p>
              </div>
              <div :class="[card.color, 'p-4 rounded-2xl text-white shadow-lg scale-110']">
                <component :is="card.icon" class="w-6 h-6" />
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Jadwal Hari Ini -->
      <Card class="border-none shadow-sm">
        <CardHeader class="flex flex-row items-center justify-between pb-2">
          <div class="flex items-center gap-2">
            <CalendarDays class="w-5 h-5 text-primary" />
            <CardTitle class="text-lg font-bold">Jadwal Mengajar Hari Ini</CardTitle>
          </div>
        </CardHeader>
        <CardContent>
          <div class="rounded-md border overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Kelas</TableHead>
                  <TableHead>Mata Kuliah</TableHead>
                  <TableHead>Ruangan</TableHead>
                  <TableHead>Jam</TableHead>
                  <TableHead class="text-center">Status</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="jadwal in jadwalHariIni" :key="jadwal.id">
                  <TableCell class="font-medium">{{ jadwal.kelas }}</TableCell>
                  <TableCell>{{ jadwal.matkul }}</TableCell>
                  <TableCell>{{ jadwal.ruangan }}</TableCell>
                  <TableCell>{{ jadwal.jam }}</TableCell>
                  <TableCell class="text-center">
                    <Badge :variant="jadwal.status_variant">
                      {{ jadwal.status }}
                    </Badge>
                  </TableCell>
                </TableRow>
                <TableRow v-if="jadwalHariIni.length === 0">
                  <TableCell colspan="5" class="h-24 text-center text-muted-foreground">
                    Tidak ada jadwal mengajar hari ini.
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
