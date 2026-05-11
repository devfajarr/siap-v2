<script setup>
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardHeader, CardTitle, CardContent } from '@/Components/ui/card'
import { Users, BookOpen, Layers, CheckCircle, XCircle } from 'lucide-vue-next'

defineProps({
  stats: Object,
  rekapPresensi: Array
})
</script>

<template>
  <AdminLayout>
    <Head title="Dashboard Admin" />

    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-[#1F1F1F]">Dashboard Admin - Presensi Hari Ini</h2>
        <p class="text-[#6C7383]">Tanggal: 11 May 2026</p>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Card class="bg-primary text-white border-none">
          <CardContent class="p-6 flex items-center justify-between">
            <div>
              <p class="text-sm opacity-80">Total Mahasiswa</p>
              <h3 class="text-3xl font-bold mt-1">{{ stats.totalMahasiswa }}</h3>
            </div>
            <Users class="w-10 h-10 opacity-30" />
          </CardContent>
        </Card>

        <Card class="bg-success text-white border-none">
          <CardContent class="p-6 flex items-center justify-between">
            <div>
              <p class="text-sm opacity-80">Total Dosen</p>
              <h3 class="text-3xl font-bold mt-1">{{ stats.totalDosen }}</h3>
            </div>
            <BookOpen class="w-10 h-10 opacity-30" />
          </CardContent>
        </Card>

        <Card class="bg-warning text-white border-none">
          <CardContent class="p-6 flex items-center justify-between">
            <div>
              <p class="text-sm opacity-80">Total Kelas</p>
              <h3 class="text-3xl font-bold mt-1">{{ stats.totalKelas }}</h3>
            </div>
            <Layers class="w-10 h-10 opacity-30" />
          </CardContent>
        </Card>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <Card class="bg-white border-[#CDD1E1]">
          <CardContent class="p-6 flex items-center gap-4">
            <div class="p-3 bg-success/10 rounded-full">
              <CheckCircle class="w-6 h-6 text-success" />
            </div>
            <div>
              <p class="text-sm text-[#6C7383]">Mahasiswa Hadir</p>
              <h3 class="text-2xl font-bold text-[#1F1F1F]">{{ stats.totalHadir }}</h3>
            </div>
          </CardContent>
        </Card>

        <Card class="bg-white border-[#CDD1E1]">
          <CardContent class="p-6 flex items-center gap-4">
            <div class="p-3 bg-danger/10 rounded-full">
              <XCircle class="w-6 h-6 text-danger" />
            </div>
            <div>
              <p class="text-sm text-[#6C7383]">Mahasiswa Tidak Hadir</p>
              <h3 class="text-2xl font-bold text-[#1F1F1F]">{{ stats.totalTidakHadir }}</h3>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Presence Table -->
      <Card class="bg-white border-[#CDD1E1]">
        <CardHeader>
          <CardTitle>Rekapitulasi Presensi per Program Studi</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="border-b border-[#F5F7FF] bg-[#F5F7FF]">
                  <th class="px-6 py-4 text-sm font-semibold text-[#1F1F1F]">Program Studi</th>
                  <th class="px-6 py-4 text-sm font-semibold text-[#1F1F1F]">Hadir</th>
                  <th class="px-6 py-4 text-sm font-semibold text-[#1F1F1F]">Tidak Hadir</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in rekapPresensi" :key="item.prodi" class="border-b border-[#F5F7FF] hover:bg-gray-50 transition-colors">
                  <td class="px-6 py-4 text-sm text-[#6C7383]">{{ item.prodi }}</td>
                  <td class="px-6 py-4 text-sm">
                    <span class="px-2 py-1 bg-success/10 text-success rounded-full font-medium">
                      {{ item.hadir }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm">
                    <span class="px-2 py-1 bg-danger/10 text-danger rounded-full font-medium">
                      {{ item.absen }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>
    </div>
  </AdminLayout>
</template>
