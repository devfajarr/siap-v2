<script setup>
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { 
  Users, 
  ClipboardList,
  Bell,
  CalendarDays,
} from 'lucide-vue-next'

const props = defineProps({
  user: {
    type: Object,
    required: true
  },
  stats: {
    type: Object,
    required: true
  }
})

const cards = [
  {
    title: 'Total Tugas',
    value: props.stats.totalTugas,
    icon: ClipboardList,
    color: 'bg-blue-500',
    description: 'Tugas administratif Anda'
  },
  {
    title: 'Notifikasi',
    value: props.stats.totalNotifikasi,
    icon: Bell,
    color: 'bg-purple-500',
    description: 'Pemberitahuan terbaru'
  }
]
</script>

<template>
  <AdminLayout>
    <Head title="Dashboard Pegawai" />

    <div class="space-y-6">
      <!-- Header -->
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-[#1F2937]">Dashboard Pegawai</h1>
        <p class="text-[#6B7280]">Selamat datang kembali, {{ user.nama }}! Berikut ringkasan aktivitas Anda.</p>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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

      <!-- Agenda & Informasi -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card class="border-none shadow-sm">
          <CardHeader class="flex flex-row items-center justify-between pb-2">
            <div class="flex items-center gap-2">
              <CalendarDays class="w-5 h-5 text-primary" />
              <CardTitle class="text-lg font-bold">Agenda Hari Ini</CardTitle>
            </div>
          </CardHeader>
          <CardContent>
            <div class="flex flex-col items-center justify-center p-6 text-center text-slate-400 bg-slate-50/50 rounded-xl border border-slate-100">
              <p class="text-sm font-semibold">Belum ada agenda khusus</p>
              <p class="text-xs mt-1">Agenda harian Anda akan muncul di sini.</p>
            </div>
          </CardContent>
        </Card>

        <Card class="border-none shadow-sm">
          <CardHeader class="flex flex-row items-center justify-between pb-2">
            <div class="flex items-center gap-2">
              <Users class="w-5 h-5 text-primary" />
              <CardTitle class="text-lg font-bold">Informasi Kepegawaian</CardTitle>
            </div>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              <div class="flex justify-between items-center pb-2 border-b border-slate-100">
                <span class="text-sm text-slate-500">NUPTK / NIDN</span>
                <span class="text-sm font-bold text-slate-800">{{ user.nuptk || '-' }}</span>
              </div>
              <div class="flex justify-between items-center pb-2 border-b border-slate-100">
                <span class="text-sm text-slate-500">Status</span>
                <span class="text-sm font-bold text-slate-800">{{ user.status === 1 ? 'Aktif' : 'Non-Aktif' }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-sm text-slate-500">Email</span>
                <span class="text-sm font-bold text-slate-800">{{ user.email }}</span>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AdminLayout>
</template>
