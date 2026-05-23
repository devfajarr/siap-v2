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
  CalendarDays,
  MessageSquare,
  ChevronRight,
  MessageCircle,
} from 'lucide-vue-next'

const props = defineProps({
  stats: {
    type: Object,
    required: true
  },
  jadwalHariIni: {
    type: Array,
    required: true
  },
  bimbinganUnread: {
    type: Array,
    default: () => []
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

      <!-- Pesan Bimbingan -->
      <Card class="border-none shadow-sm">
        <CardHeader class="flex flex-row items-center justify-between pb-2">
          <div class="flex items-center gap-2">
            <MessageSquare class="w-5 h-5 text-primary" />
            <CardTitle class="text-lg font-bold">Pesan Bimbingan Baru</CardTitle>
          </div>
          <Link
            href="/v2/dosen/bimbingan"
            class="text-sm font-semibold text-primary flex items-center gap-1 hover:underline"
          >
            Lihat Semua <ChevronRight class="w-4 h-4" />
          </Link>
        </CardHeader>
        <CardContent>
          <div v-if="bimbinganUnread.length === 0" class="flex flex-col items-center justify-center p-6 text-center text-slate-400 bg-slate-50/50 rounded-xl border border-slate-100">
            <MessageCircle class="w-10 h-10 mb-3 text-slate-300" />
            <p class="text-sm font-semibold">Tidak ada pesan baru</p>
            <p class="text-xs mt-1">Semua pesan konsultasi bimbingan akademik sudah Anda baca.</p>
          </div>
          <div v-else class="space-y-3 mt-2">
            <Link
              v-for="student in bimbinganUnread"
              :key="student.id"
              href="/v2/dosen/bimbingan"
              class="flex items-center justify-between p-4 rounded-xl border border-slate-100 bg-white hover:bg-slate-50 transition-colors group shadow-sm"
            >
              <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-[#4B49AC]/10 text-[#4B49AC] flex items-center justify-center font-bold text-sm">
                  {{ student.nama.substring(0, 2).toUpperCase() }}
                </div>
                <div>
                  <p class="text-sm font-bold text-slate-800">{{ student.nama }}</p>
                  <p class="text-xs text-slate-500 mt-0.5">{{ student.nim }} · {{ student.kelas }}</p>
                </div>
              </div>
              <div class="flex items-center gap-3">
                <span class="bg-red-500 text-white text-[11px] font-bold px-2 py-0.5 rounded-full flex items-center justify-center min-w-[24px]">
                  {{ student.unread_count }}
                </span>
                <div class="text-[#4B49AC] opacity-0 group-hover:opacity-100 transition-opacity">
                  <ChevronRight class="w-5 h-5" />
                </div>
              </div>
            </Link>
          </div>
        </CardContent>
      </Card>
    </div>
  </AdminLayout>
</template>
